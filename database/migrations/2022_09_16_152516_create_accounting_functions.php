<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = '    
        CREATE OR REPLACE FUNCTION 
        PUBLIC.get_close_mid_that_date(currncy_id bigint, date timestamp )
               RETURNS numeric     
               AS 	$sql$
               SELECT  
               ((selling_price + purchasing_price)/2)::numeric  as mid 
               from 
                   stock_transactions  
                       where time = (
                                   select max(time) from stock_transactions where   
                                     time::date <= date::date or  time <= date and stock_id = currncy_id)
                       and stock_id = currncy_id
               $sql$
               LANGUAGE SQL
        ';
        DB::unprepared($sql);
        $sql = '
        CREATE OR REPLACE FUNCTION 
        PUBLIC.get_start_mid_that_date(currncy_id bigint, date timestamp )
               RETURNS numeric     
               AS 	$sql$
               SELECT  
               ((start_selling_price + start_purchasing_price)/2)::numeric  as mid 
               from 
                   stock_transactions  
                       where time = 
                       (select max(time) from stock_transactions
                        where   time::date <  date::date   
                        and stock_id = currncy_id ) 
                       and stock_id = currncy_id
               $sql$
               LANGUAGE SQL
        ';
        DB::unprepared($sql);
        $sql = '
        CREATE OR REPLACE FUNCTION public.get_profit_data(
            currency_id bigint,
            at_date timestamp without time zone)
            RETURNS TABLE(start_balance numeric, close_balance numeric, currency_id bigint, name character varying, close_rate numeric, start_rate numeric, start_usd_amount numeric, close_usd_amount numeric,usd_diff numeric, at_date timestamp without time zone) 
            LANGUAGE \'sql\'
            COST 100
            VOLATILE PARALLEL UNSAFE
            ROWS 1000
        
            AS $BODY$

            WITH var_close_rate AS (
                SELECT * from get_close_mid_that_date($1 , $2 )
            ), var_start_rate AS (
                SELECT *  from get_start_mid_that_date($1 , $2 )
            )
            select start_balance ,    close_balance , currency_id ,   
            name ,close_rate , start_rate , start_usd_amount , close_usd_amount,
            (close_usd_amount - start_usd_amount) as usd_diff ,at_date from
               (select   start_balance ,    close_balance , currency_id ,   name ,close_rate , start_rate 
               ,  case when currency_id = 4 then (start_balance * start_rate) 
               else (start_balance / start_rate) end start_usd_amount , 
               case when currency_id = 4 then (close_balance * close_rate) 
               else (close_balance / close_rate)   end as close_usd_amount ,
               at_date
               
                from (select sum(befor_balance) as start_balance ,  sum(balance) as close_balance , currency_id , currency_name as name ,close_rate , start_rate
                from (select
                     case when accounts.type_id in (5) then sum(entry_transactions.creditor- entry_transactions.debtor) 
                     else sum(entry_transactions.debtor- entry_transactions.creditor) end  as balance , accounts.name
                       , entry_transactions.currency_id  , currencies.name as currency_name ,
                      (SELECT * FROM  var_close_rate ) as close_rate , 
                      (SELECT * FROM  var_start_rate )  as start_rate, 
                      case  when entries.date::date < $2::date then 
                              case when accounts.type_id in (5) then sum(entry_transactions.creditor- entry_transactions.debtor)
                              else sum(entry_transactions.debtor- entry_transactions.creditor) end
                      else 0 end as befor_balance
                      
                      from  
                    entry_transactions 
                    inner join entries on entries.id = entry_transactions.entry_id 
                    inner join accounts on entry_transactions.account_id = accounts.id 
                    inner join currencies on currencies.id = entry_transactions.currency_id 												   
                    where   entry_transactions.account_id is not null 
                    and accounts.type_id in (4 ,3 , 5 ) and accounts.is_transaction = true
                    and entries.date::date <= $2::date
                    and entries.document_sub_type not in (4, 5) 
                    and case when accounts.type_id = 5 then
                    entry_transactions.transaction_type not in (6, 8, 10 ) 
                    else 	
                    entry_transactions.transaction_type not in (6, 8, 10, 2, 3, 4, 9 ) end
                    and entries.type = 1    and entry_transactions.currency_id  = $1
                    group by currency_name,accounts.id,entry_transactions.currency_id , close_rate , start_rate , entries.date
                    order by entry_transactions.currency_id , accounts.name )agg
                    group by currency_id,currency_name , close_rate , start_rate) suming)last_sub;
                
            $BODY$;
        
        ALTER FUNCTION public.get_profit_data(bigint, timestamp without time zone)
            OWNER TO postgres;';
        DB::unprepared($sql);
       
        $ower_sql =  '
        DROP FUNCTION IF EXISTS account_statement;
        CREATE OR REPLACE FUNCTION public.account_statement(
            a_id bigint,
            date_from date,
            date_to date,
            _active_only boolean ,
		curr_ids bigint[], 
		curr_length int, 
		statement_holder varchar, 
        logged_in_user_id bigint )
            RETURNS TABLE(
                transaction_id bigint,
                 debtor double precision, 
                 creditor double precision, 
                 exchange_rate double precision,
                 ac_debtor double precision, 
                 ac_creditor double precision, 
                 entry_id bigint, transaction_type integer, 
                 date timestamp without time zone, 
                 document_type integer, 
                 statement character varying(255),
                 document_number bigint, 
                 document_id bigint, 
                 document_sub_type integer, 
                 balance double precision, 
                 r_id bigint, 
                 account_id bigint, 
				currency_name character varying(255),
				currency_id bigint,
				user_name character varying(255),
				sender_name character varying(255),
				receiver_name character varying(255),
                  inside integer,
                  acc_balance double precision) 
            LANGUAGE "plpgsql"
            COST 100
            VOLATILE PARALLEL UNSAFE
            ROWS 1000
        
        AS $BODY$
                BEGIN
                RETURN QUERY
                select distinct on(entry_id,transaction_id) *,sum(e.balance) over( order by e.transaction_id) as c_b from (
                    select
                        case when entries.date<date_from then null else entry_transactions.id end as transaction_id,
                        case when entries.date<date_from then null else entry_transactions.debtor end   ,
                        case when entries.date<date_from then null else entry_transactions.creditor end   ,
                        case when entries.date<date_from then null else entry_transactions.exchange_rate end  ,
                        case when entries.date<date_from then null else entry_transactions.ac_debtor end   ,
                        case when entries.date<date_from then null else entry_transactions.ac_creditor end   ,
                        case when entries.date<date_from then null else entries.id end as entry_id,
                        case when entries.date<date_from  then null else entry_transactions.transaction_type end ,
                        case when entries.date<date_from  then \'2001-01-01\'    else entries.date end ,
                        entries.document_type,
                        case when entries.date<date_from  then statement_holder else entries.statement end as statement,
                        entries.document_number,
                        case when entries.date<date_from  then null else entries.document_id end as document_id,
                        entries.document_sub_type,
                       	case when  curr_length = 1 
                        then
                        (entry_transactions.debtor - entry_transactions.creditor) 
                         else 
                        (entry_transactions.ac_debtor - entry_transactions.ac_creditor) end as balance,
                        ROW_NUMBER() over(order by entry_transactions.id) as r_id,
                        entry_transactions.account_id,
                        case when entries.date<date_from  then null else currencies.name end ,
                        case when entries.date<date_from  then null else currencies.id end ,
                        case when entries.date<date_from  then null else users.name end ,
					coalesce(p_s.name ,p_f.name),
					coalesce(p_r.name,p_t.name) ,
                        case when entries.date<date_from then 1 else 0 end as inside
                    from entry_transactions 
                    inner join entries ON entries.id = entry_transactions.entry_id
					left join transfers ON entries.document_id = transfers.id and entries.document_type = 1
					left join parties p_s ON p_s.id = transfers.sender_party_id   
					left join parties p_r ON p_r.id = transfers.receiver_party_id   
					
					left join receipts re ON entries.document_id = re.id and entries.document_type = 3
					left join accounts p_f ON p_f.id = re.from_account_id    
					left join accounts p_t ON p_t.id = re.to_account_id   
 
					
					inner join currencies ON currencies.id = entry_transactions.currency_id
                    left join users ON entries.user_id = users.id
                    where entry_transactions.account_id in (select id from get_account_with_children(a_id)) and entries.status=1
					and entries.type=1 and entries.date < date_to and  
                    case when  logged_in_user_id > 0 
                    THEN  entries.user_id = logged_in_user_id 
                    ELSE entries.user_id IS NOT NULL END
					and case  WHEN curr_ids is not null   
                    THEN entry_transactions.currency_id =  ANY (curr_ids) 
                    ELSE entry_transactions.currency_id IS NOT NULL END 
                        )e
                        order by entry_id,transaction_id, r_id desc;
                END 
        $BODY$ ;
';
        $sensie_sqls = "
        DROP FUNCTION IF EXISTS account_statement;
        CREATE OR REPLACE FUNCTION account_statement(a_id BIGINT, date_from DATE , date_to DATE,_active_only BOOLEAN)
        RETURNS TABLE(
            document_id BIGINT,
            account_id BIGINT,
            r_id BIGINT,
            balance DOUBLE PRECISION,
            creditor DOUBLE PRECISION,
            debtor DOUBLE PRECISION,
            document_type INT,
            document_number BIGINT,
            document_sub_type INT,
            date TIMESTAMP,
            type_name TEXT,
            statement VARCHAR,
            inside BOOLEAN,
            entry_id BIGINT,
            a_balance DOUBLE PRECISION,
            r_n BIGINT,
            a_debtor DOUBLE PRECISION,
            a_creditor DOUBLE PRECISION
        )
        AS $$
        BEGIN
        RETURN QUERY
		select * from (
        select distinct on (entry_id) *,
            case when d.a_balance >0 and d.debtor is null then abs(d.a_balance) else 0 end as a_debtor,case when d.a_balance<=0 and d.creditor is null then abs(d.a_balance) else 0 end as a_creditor
             from (
        select *,
                    sum(t.balance) over(partition by t.account_id order by t.date,t.r_id) as a_balance , ROW_NUMBER() over(partition by t.account_id order by t.date,t.account_id) as r_n from (
                        
            select 
                            case when entries.date<date_from then null else entries.document_id end as document_id, 
                            accounts.id as account_id,
                            entries.number  as r_id, 
                            sum(entry_transactions.debtor-entry_transactions.creditor) as balance, 
                            case when entries.date<date_from then null else sum(entry_transactions.creditor) end as creditor, 
                            case when entries.date<date_from then null else sum(entry_transactions.debtor) end as debtor, 
                            case when entries.date<date_from then null else entries.document_type end as document_type,
                            case when entries.date<date_from then null else entries.document_number end as document_number, 
                            case when entries.date<date_from then null else entries.document_sub_type end as document_sub_type,
                            entries.date as date, 
                            case when entries.date<date_from then null else concat(entries.document_sub_type,'_',entries.document_type) end as type_name, 
                            case when entries.date<date_from then 'opening_balance' else entries.statement end as statement,
                            entries.date>date_from as inside,
							case when entries.date<date_from then null else entries.id end as entry_id
                        from entry_transactions inner join entries on entries.id = entry_transactions.entry_id inner join accounts on accounts.id = entry_transactions.account_id 
                        where  entry_transactions.account_id in ( select id from get_account_with_children(a_id))
                        and
                        (
                            case when _active_only then
                                entries.id not in (select inverse_entry_id from entries where inverse_entry_id is not null) and entries.inverse_entry_id is null
                            else true end
                        )
                        and entries.status=1
                        and entries.date <= date_to
                        group by
                        entries.document_id, 
                        accounts.id,
                        entries.document_type, 
                        entries.document_id, 
                        entries.document_sub_type, 
                        entries.date, 
                        entries.number,
                        entries.document_number,
                        entries.statement,
						entries.id
                        order by r_id asc, CAST(entries.date AS DATE) asc
                        )t 
                        )d  order by entry_id, r_n desc )f
						order by r_id asc
						;
        END $$ LANGUAGE plpgsql;
";
        DB::unprepared($ower_sql);
        $sql = "CREATE OR REPLACE FUNCTION get_account_with_children(account_in_id BIGINT)
        RETURNS TABLE(            
            id BIGINT
        )
        AS $$
        BEGIN
        RETURN QUERY
        select ct.id
        FROM (
            WITH RECURSIVE cte AS (
                SELECT accounts.id, parent_id ,1 AS level
                FROM   accounts
                WHERE accounts.id = account_in_id
                UNION 
                SELECT t.id, t.parent_id, c.level + 1
                FROM   cte      c
                JOIN   accounts t ON t.parent_id = c.id
            )
            SELECT DISTINCT cte.id
            FROM  cte WHERE cte.id NOT IN (SELECT parent_id FROM accounts WHERE parent_id IS NOT NULL)
        )
        AS ct;
        END $$ LANGUAGE plpgsql;
       ";
        DB::unprepared($sql);

        $sql = "create or replace function get_cur_exch_rate(curr_id bigint)
        returns DECIMAL
        language plpgsql
        as
        $$
        declare
            exchange_rate decimal;
        begin
            select round(cast(float8((start_selling_price + start_purchasing_price) / 2) as numeric), 10)
            into exchange_rate
            from stocks
            where id = curr_id
            order by updated_at desc limit 1;
            
            return exchange_rate;
        end;
        $$;
        ";
        DB::unprepared($sql);




        $sql = "
        CREATE OR REPLACE FUNCTION public.process_inventory_dates(
            start_date date,
            end_date date ,
            toggle_row_profit int)
            RETURNS TABLE(start_balance numeric, close_balance numeric, currency_id bigint, name character varying, close_rate numeric, start_rate numeric, start_usd_amount numeric, close_usd_amount numeric, usd_diff numeric, at_date timestamp without time zone) 
            LANGUAGE 'plpgsql'
            COST 100
            VOLATILE PARALLEL UNSAFE
            ROWS 1000
        
        AS \$BODY$
        DECLARE
            minDate DATE := (select min(date) from entries);
            maxDate DATE := (select max(date) from entries);
              mainStartDate DATE ;
            CURR_COUNT integer := 1;
            curr_in_day_Profit numeric := 0;
            total_profit numeric := 0 ;
                holder numeric := 0 ;
         
        BEGIN
            start_date	= (case  when  minDate::date >= start_date::date  then minDate else start_date end);
            end_date	= (case  when  maxDate::date <= end_date::date  then maxDate else end_date end);
            mainStartDate = start_date;
        CREATE TEMPORARY TABLE IF NOT EXISTS  my_temp_table (start_balance numeric,close_balance numeric,currency_id bigint,name character varying ,close_rate numeric,start_rate numeric ,start_usd_amount  numeric,close_usd_amount numeric,usd_diff numeric,at_date timestamp);
            
            WHILE start_date <= end_date LOOP
                RAISE NOTICE 'start_date start_date: %', start_date;
                
                     WHILE CURR_COUNT <= 7  LOOP
                    insert into my_temp_table  (start_balance,close_balance  ,currency_id  ,name,close_rate ,start_rate,start_usd_amount,close_usd_amount,usd_diff,at_date)select * from get_profit_data(CURR_COUNT , start_date);
                     RAISE NOTICE 'CURR_COUNT: %', CURR_COUNT;
                    holder :=  (select coalesce(p_d.usd_diff,0) from get_profit_data(CURR_COUNT , start_date) p_d);
                     curr_in_day_Profit := curr_in_day_Profit +  coalesce(holder,0);
                    total_profit := coalesce(holder,0) + total_profit;
                    CURR_COUNT := CURR_COUNT + 1;
                     END LOOP;
                if toggle_row_profit  then
                insert into my_temp_table  (name,usd_diff , at_date) select 'ربحية اليوم' , curr_in_day_Profit , start_date ;  
                end if;
                 CURR_COUNT := 1 ;
                 start_date := start_date + 1;
                 curr_in_day_Profit := 0;
            END LOOP;
            RAISE NOTICE 'total_profit: %', total_profit;
            insert into my_temp_table  (name,usd_diff) select 'مجموع الربحية  ' , total_profit ; 
        return  QUERY SELECT * FROM my_temp_table;
        TRUNCATE TABLE my_temp_table;
        END;
        \$BODY$;
 ";
        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_functions');
    }
};

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
        $sql =  '
        DROP FUNCTION IF EXISTS account_statement;
        CREATE OR REPLACE FUNCTION public.account_statement(
            a_id bigint,
            date_from date,
            date_to date,
            _active_only boolean ,
		curr_id bigint, 
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
                        entry_transactions.debtor ,
                        entry_transactions.creditor ,
                        entry_transactions.exchange_rate,
                        entry_transactions.ac_debtor ,
                        entry_transactions.ac_creditor ,
                        case when entries.date<date_from then null else entries.id end as entry_id,
                        entry_transactions.transaction_type,
                        entries.date,
                        entries.document_type,
                        entries.statement,
                        entries.document_number,
                        entries.document_id,
                        entries.document_sub_type,
                       	case when  curr_id > 0 then (entry_transactions.debtor - entry_transactions.creditor) else (entry_transactions.ac_debtor - entry_transactions.ac_creditor) end as balance,
                        ROW_NUMBER() over(order by entry_transactions.id) as r_id,
                        entry_transactions.account_id,
					currencies.name,
					currencies.id,
					users.name,
                        case when entries.date<date_from then 1 else 0 end as inside
                    from entry_transactions 
                    inner join entries ON entries.id = entry_transactions.entry_id
					inner join currencies ON currencies.id = entry_transactions.currency_id
                    left join users ON entries.user_id = users.id
                    where entry_transactions.account_id in (select id from get_account_with_children(a_id)) and entries.status=1
					and entries.date < date_to and  case when  logged_in_user_id > 0 THEN  entries.user_id = logged_in_user_id ELSE entries.user_id IS NOT NULL END
					and case  WHEN curr_id > 0  THEN entry_transactions.currency_id = curr_id ELSE entry_transactions.currency_id IS NOT NULL END 
                        )e
                        order by entry_id,transaction_id, r_id desc;
                END 
        $BODY$ ;
';
        $sqls = "
        DROP FUNCTION IF EXISTS account_statement;
        CREATE OR REPLACE FUNCTION account_statement(
            a_id BIGINT, 
            date_from DATE , 
            date_to DATE,
            _active_only BOOLEAN)
        RETURNS TABLE(
            transaction_id BIGINT,
            debtor DOUBLE PRECISION,
            creditor DOUBLE PRECISION,
            exchange_rate DOUBLE PRECISION,
            ac_debtor DOUBLE PRECISION,
            ac_creditor DOUBLE PRECISION,
            entry_id BIGINT,
            transaction_type INTEGER,
            date TIMESTAMP,
            document_type INTEGER,
            document_number BIGINT,
            document_id BIGINT,
            document_sub_type INTEGER,
            balance DOUBLE PRECISION,
            r_id BIGINT,
            account_id BIGINT,
            inside INTEGER
        )
        AS $$
        BEGIN
        RETURN QUERY
		select distinct on(entry_id,transaction_id) * from (
            select
                case when entries.date<date_from then null else entry_transactions.id end as transaction_id,
                entry_transactions.debtor ,
                entry_transactions.creditor ,
                entry_transactions.exchange_rate,
                entry_transactions.ac_debtor ,
                entry_transactions.ac_creditor ,
                case when entries.date<date_from then null else entries.id end as entry_id,
                entry_transactions.transaction_type,
                entries.date,
                entries.document_type,
                entries.document_number,
                entries.document_id,
                entries.document_sub_type,
            (entry_transactions.debtor - entry_transactions.creditor)  as balance,
                ROW_NUMBER() over(order by entry_transactions.id) as r_id,
                entry_transactions.account_id,
                case when entries.date<date_from then 1 else 0 end as inside
            from entry_transactions 
            inner join entries ON entries.id = entry_transactions.entry_id
            where entry_transactions.account_id in (select id from get_account_with_children(a_id)) and entries.status=1 and entries.date < date_to
                )e
                order by entry_id,transaction_id, r_id desc;
        END $$ LANGUAGE plpgsql;
";
        DB::unprepared($sql);
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

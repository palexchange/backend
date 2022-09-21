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
        $sql = "
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
                            entries.id  as r_id, 
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

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

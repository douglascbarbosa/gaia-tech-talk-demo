<?php

/**
 * Renames the authenticated identity table to `developers` and extends the schema.
 * The `sessions.user_id` column name is kept because Laravel's database session
 * handler writes to that key explicitly (see Illuminate\Session\DatabaseSessionHandler).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations: extend `users`, detach session FK when present, rename to `developers`, attach new FK.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('github_profile')->nullable()->after('email');
            $table->string('status', 32)->default('working')->after('github_profile');
            $table->string('address_street')->nullable()->after('status');
            $table->string('address_city')->nullable()->after('address_street');
            $table->string('address_postal_code')->nullable()->after('address_city');
            $table->string('address_country')->nullable()->after('address_postal_code');
        });

        $this->dropSessionsUserIdForeignKeys();

        Schema::rename('users', 'developers');

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('developers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations: drop session FK, rename `developers` back to `users`, restore FK, drop added columns.
     */
    public function down(): void
    {
        $this->dropSessionsUserIdForeignKeys();

        Schema::rename('developers', 'users');

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'github_profile',
                'status',
                'address_street',
                'address_city',
                'address_postal_code',
                'address_country',
            ]);
        });
    }

    /**
     * Remove any foreign key on `sessions.user_id` so the column can reference a renamed table.
     *
     * Newer Laravel releases may not create a FK for `foreignId()`, or MySQL may use a different
     * constraint name than `dropForeign(['user_id'])` expects; this method introspects when possible.
     */
    private function dropSessionsUserIdForeignKeys(): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        $sessionsTable = $connection->getTablePrefix().'sessions';

        $names = match ($driver) {
            'mysql' => $this->mysqlForeignKeyNames($connection->getDatabaseName(), $sessionsTable, 'user_id'),
            'pgsql' => $this->pgsqlForeignKeyNames($sessionsTable, 'user_id'),
            default => [],
        };

        foreach ($names as $name) {
            Schema::table('sessions', function (Blueprint $table) use ($name) {
                $table->dropForeign($name);
            });
        }

        if ($names !== []) {
            return;
        }

        try {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable) {
            // No foreign key (e.g. SQLite without FK, or MySQL column without constraint).
        }
    }

    /**
     * Resolve foreign-key constraint names for a column on MySQL (information_schema).
     *
     * @param  string  $database  Connection database name
     * @param  string  $table  Physical table name including prefix
     * @param  string  $column  Local column participating in the FK
     * @return list<string>
     */
    private function mysqlForeignKeyNames(string $database, string $table, string $column): array
    {
        $rows = DB::select(
            'SELECT DISTINCT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        return array_values(array_filter(array_map(
            static fn (object $row): string => (string) $row->name,
            $rows
        )));
    }

    /**
     * Resolve foreign-key constraint names for a column on PostgreSQL (information_schema).
     *
     * @param  string  $table  Physical table name including prefix
     * @param  string  $column  Local column participating in the FK
     * @return list<string>
     */
    private function pgsqlForeignKeyNames(string $table, string $column): array
    {
        $rows = DB::select(
            'SELECT DISTINCT tc.constraint_name AS name
             FROM information_schema.table_constraints tc
             INNER JOIN information_schema.key_column_usage kcu
               ON tc.constraint_schema = kcu.constraint_schema
              AND tc.constraint_name = kcu.constraint_name
             WHERE tc.constraint_type = \'FOREIGN KEY\'
               AND tc.table_schema = ANY (current_schemas(false))
               AND tc.table_name = ?
               AND kcu.column_name = ?',
            [$table, $column]
        );

        return array_values(array_filter(array_map(
            static fn (object $row): string => (string) $row->name,
            $rows
        )));
    }
};

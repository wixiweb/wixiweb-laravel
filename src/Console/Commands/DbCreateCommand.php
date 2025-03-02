<?php

namespace Wixiweb\WixiwebLaravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbCreateCommand extends Command
{
    protected $signature = 'wixiweb:db:create {name?}';

    protected $description = 'Créer un schema de base de donnée si celui-ci n\'existe pas';

    public function handle(): void
    {
        $schemaName = $this->argument('name') ?: config("database.connections.mysql.database");
        $charset = config("database.connections.mysql.charset",'utf8mb4');
        $collation = config("database.connections.mysql.collation",'utf8mb4_unicode_ci');

        $query = "CREATE DATABASE IF NOT EXISTS $schemaName CHARACTER SET $charset COLLATE $collation;";

        DB::statement($query);
    }
}

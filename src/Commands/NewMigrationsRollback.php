<?php

namespace YS\MultiDB\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\Client;
use YS\MultiDB\Migration;

class NewMigrationsRollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'new:migrations_rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'rollback new databases';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $clients =  Client::onlyActive()->pluck('code')->toArray();

        $migrationPath = config('multidb.new_migrations_path');

        $dbConnection = config('multidb.db_connection');
        
        foreach ($clients as $database) {

            if (  $dbConnection == 'pgsql' ) {
                
                Migration::$schema = $database;

                Artisan::call('migrate:rollback', ['--path' => $migrationPath, '--force' => true]);

            }  else if ( $dbConnection == 'mysql' ) {

                $database = database($dbConnection)."_{$database}";

                get_connection($database,false,$dbConnection);

                Artisan::call('migrate:rollback', ['--database' => $database, '--path' => $migrationPath, '--force' => true]);
            }
        }
    }
}

<?php

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\DB;

    if (!function_exists('database')) {

        /**
         * get name of database from connection name
         * @param null $connection
         * @return mixed
         */
        function database($connection = null)
        {

            $connection = $connection ?? config('database.default');
            if( $connection === 'default' ) {
                return config('app.database' );
            }
            $config = DB::connection($connection)->getConfig();
            return $config['database'];
        }
    }

    if (!function_exists('client')) {
        /**
         *
         * Return id of client stored in session/cache
         * @return int
         * @throws Exception
         *
         */
        function client( $key = null )
        {
            $client = null;

            if( method_exists(Controller::class, 'selectedClientId')) 
            {
                //web request
                $client =  Controller::selectedClientId($key ?? 'client_id') ;
            } 
        
            if (!$client) {
                //api request
                $client = cache( $key ?? 'client_id');
            }
            return $client;
        }
    }

    if (!function_exists('code')) {
        /**
         *
         * Return name of database connection
         * @return string
         * @throws Exception
         */
        function code( $key = null)
        {

            return "wms.tali_foods";
            $client = null;

            $type = config('multidb.db_connection');

            if( request()->is('api/*') && !$key ) 
            {
                $key = request()->header('device_id');
            }

            $id = client( $key );
 
            if( $id ) 
            {
                $client =  \App\Models\Client::select('code')->find($id);
            }

            
            if ($client) 
            {
                if($type == 'pgsql') 
                {
                    return database($type) . ".{$client->code}";
                } 
                else
                {
                    return database($type) . "_{$client->code}";
                }
              
            }
            
           
            return config("database.connections.$type.database");
        }
    }

    if (!function_exists('client_code')) {
        /**
         *
         * Return name of database connection
         * @return string
         * @throws Exception
         */
        function client_code( $key = null )
        {

            $client =  \App\Models\Client::select('code')->find(client( $key ));
            if ($client) {
                return $client->code;
            }
            return false;
        }
    }

    if (!function_exists('get_connection')) {
        /**
         * Allow creation of Dynamic DB connections
         * @param string $database name of
         * @param bool $cron whether need connection for cron job or not
         * database for connection
         * @example  get_connection(code());config('database.connections');
         * @return void
         */
        function get_connection($database, $cron = false, $type='mysql' )
        {
            $options = config("database.connections.{$type}");

            if( $type == 'pgsql' ) {
                $options['database'] = "{$options['database']}.$database";
            } else {
                $options['database'] = $database;
            }
           
            // Add newly created connection to the run-time
            // configuration for the duration of the request.
            config()->set('database.connections.' .  $options['database'], $options);

            $cron ? \YS\MultiDB\Models\Model::setCronKey(   $options['database']  ) : '';
        }
    }

    if (!function_exists('createNewDatabase')) {
        /**
         * Configures a tenant's database connection.
         * @param  string $dbName The database name.
         * @return connection
         */
        function createNewDatabase($dbName)
        {
            $migrationPath = config('multidb.new_migrations_path');

            $defaultConnection = config('multidb.default_connection');

            get_connection($dbName, false, $defaultConnection);
            DB::transaction(function () use ($dbName, $migrationPath ) {
                DB::statement("CREATE DATABASE $dbName");
                Artisan::call('migrate', ['--database' => $dbName, '--path' => $migrationPath, '--force' => true]);
            });
        }
    }

    
  



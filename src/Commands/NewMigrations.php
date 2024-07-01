<?php 

namespace YS\MultiDB\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\Client;
use FilesystemIterator;
use YS\MultiDB\Migration;

class NewMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'new:migrations {--seed : weather to run seeders or not}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update new databases';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $clients = Client::onlyActive()->pluck('code')->toArray();

        $migrationPath = config('multidb.new_migrations_path');

        $dbConnection = config('multidb.db_connection');

        foreach ($clients as $database) {

            $database = strtolower(database($dbConnection)."_{$database}");

            get_connection($database,false,$dbConnection);

            Artisan::call('migrate', ['--database' => $database, '--path' => $migrationPath, '--force' => true]);

            if( $this->option('seed') )
            {
                $path = base_path( $migrationPath.'/seeders');
              
                if( is_dir(  $path  ) ) 
                {
                    $files = new FilesystemIterator(base_path($migrationPath.'/seeders'),FilesystemIterator::SKIP_DOTS);

                    $this->runSedersInFolder( $files, $path );
                
                }
            }
        }
        $this->info('Migrations successfully created in multiple databases.');
    }

    protected function runSedersInFolder($files, $path)
    {
        foreach( $files as $file )
        {
            $fp = fopen($file, 'r');

            $class = $buffer = '';
           
            while (!$class) 
            {
                if (feof($fp)) break;

                $buffer .= fread($fp, 512);

                $namespace = null;

                if (preg_match('/.*\bnamespace\b.*/', $buffer, $matches)) 
                {
                    $namespace = explode('namespace ', $matches[0])[1] ?? null;

                    if ( $namespace ) {
                        $namespace = explode(';',$namespace)[0];
                    }
                }
               

                if (preg_match('/class\s+(\w+)(.*)?\s\{/', $buffer, $matches)) 
                {
                    $class = $matches[1];

                    $path = $namespace.'\\'.$class;
         
                    Artisan::call('db:seed', ['--class' =>   $path]);
                }
            }
        }
    }
}

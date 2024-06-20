<?php

namespace YS\MultiDB\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MakeNewMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:new-migration {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create new migration in newmigrations table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $migrationPath = config('multidb.new_migrations_path');

        $name = $this->argument('name');

        Artisan::call('make:migration',['name' => $name, "--path"=>$migrationPath]);
    }
}

<?php 

namespace YS\MultiDB;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;

class MultiDbMigrationRepository extends DatabaseMigrationRepository
{

    public function setTable($table) {
        $this->table = $table;
    }
    
}

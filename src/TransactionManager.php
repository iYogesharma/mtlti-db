<?php
    
namespace YS\MultiDB;

use Illuminate\Support\Facades\DB;

class TransactionManager extends DB
{
    /*--------------------------------------------------------------------------
    | TransactionManager : Responsible for managing DB transactions.in case of |
    |                       multiple databases or Dynamic Database conections. |
    ---------------------------------------------------------------------------*/
    
    /**
     * Holds name of database
     * @var string
     */
    private $db = null;

    /**
     * Set name of connection on 
     * which we need to run database transactions
     *
     * @param [type] $name
     * @param boolean $cron
     * @return void
     */
    public function setConnection( $name = null, $cron = false ) 
    {
        $this->db = $name ??  code(request()->header('device_id'));

        get_connection($this->db,$cron);

        return $this->db;
    }

    // get name of database connection
    public function connectionName() 
    {
        return  $this->db;
    }

    /**
     * name of database connection on which
     * transaction runs
     * @param $name
     * @return $this
     */
    public function connection( $name, $cron = false )
    {
        $this->setConnection($name, $cron);
        return $this;
    }
    
    /**
     * Begin Database transaction
     * @return void
     */
    public function beginTransaction($cron=false)
    {
        parent::connection( $this->setConnection( null, $cron ) )->beginTransaction();
    }
    
    /**
     * Abort all changes in unsuccessful transactions
     * @return void
     */
    public function rollback()
    {
        parent::connection($this->db)->rollback();
    }
    
    /**
     * Commit changes for successful transaction
     * @return void
     */
    public function commit()
    {
        parent::connection($this->db)->commit();
    }
}

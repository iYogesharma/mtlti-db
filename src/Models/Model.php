<?php

namespace YS\MultiDB\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    /**
     * @var boolean inMainDb indicate weather to store
     *  model in default db or in dynamic 
     *
     * @var boolean
     */ 
    protected static $inMainDb = false;
    
    /**
     * Helps to set db connection in case of api requests
     *
     * @var string|null
     */
    protected static $cacheKey = null;
    
    /**
     * Helps to set db connection in case of crons
     *
     * @var string|null
     */
    public static $cronKey = null;

    /**
     * Default database connection driver
     *
     * @var string|null
     */
    public static $connectionType = null;

    /**
     * Query Builder to extend default query builder with some 
     * additional utility function
     *
     * @var string|null
     */
    public $queryBuilder = 'YS\\MultiDB\\ModelMethod';
    
    /**
     * Hold connected database name
     *
     * @var string
     */
    protected $db;
    

    // Load the model with dynamic database name based on data 
    public function __construct(array $attributes = [])
    {
        $type = $this->getConnectionType();

        $this->db = config("database.connections.{$type}.database");
       
        if( !static::$inMainDb ) 
        {
            $this->setDatabaseConnection( $type );
        } 
        else
        {
            $this->setDefaultDatabaseConnection( $type );
        }
    
      
       
        parent::__construct($attributes);
    }

    // get database connection name
    public function getConnectionType(){
        
        return static::$connectionType ?? config('multidb.db_connection');
    }

    //Get Connected Database Name
    public function getDB()
    {
       return $this->db;
    }

    //Set Database Connection Based On User Provided Inputs
    protected function setDatabaseConnection( string $type)
    {
       
        if( self::$cronKey )
        {
            $this->setTableName( self::$cronKey, $type);

            if( $type == 'mysql') 
            {
                get_connection( self::$cronKey, false, $type );

                $this->setConnection( self::$cronKey );
            }
 
           
        }
        else
        {  
            $this->setTableName( code(self::$cacheKey ), $type);

            if( $type == 'mysql') 
            {
                get_connection( code( self::$cacheKey), false, $type );
            
                $this->setConnection( code( self::$cacheKey ) );
            }
        }
    }

    public function setTableName( string $prefix, $type ) 
    {
        $this->table= "$prefix.".$this->getTable();
    }
    
    // Set Default Database Connection from config/database.php
    protected function setDefaultDatabaseConnection( string $type )
    {  
        $this->setTableName( config("database.connections.{$type}.database").'.public', $type);
    }

    //Set Query builder if you want to define your own utility functions
    public function setQueryBuilder( string $builder ) {
        $this->queryBuilder = $builder;
    }

    // Query builder containing some additional helpers functions to work with query
    public function newEloquentBuilder($query)
    {
        $builder = $this->queryBuilder;

        return new $builder($query, $this->getTable());
    }
    
    
    // Helps to set dynamic db connection from api
    public static function setCacheKey( $value )
    {
        self::$cacheKey = $value;
    }

    // Helps to set dynamic db connection from cron
    public static function setCronKey( $value )
    {
        self::$cronKey = $value;
    }

    /**
     * Fire the given event for the model.
     *
     * @param  string  $event
     * @param  bool  $halt
     * @return mixed
     */
    public function fireEvent($event, $halt = true)
    {
        return $this->fireModelEvent( $event, $halt);
    }
}

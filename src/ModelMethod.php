<?php
    
    namespace YS\MultiDB;
    
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Http\Request;
    use Illuminate\Support\Str;
    
    class ModelMethod extends Builder
    {
        /****************************************************************************
         *  ModelMethod class contains some useful functions that                   |
         *  can be used to query data from database based on various conditions     |
         *****************************************************************************/
        
        /**
         * Database name used by main connection
         * @var string
         */
        private $from;
        
        /**
         * Create a new QueryBuilder instance.
         * @param $query
         * @param $from
         */
        public function __construct( $query, $from )
        {
            parent::__construct($query);
            $this->from = $from;
        }
        
        /**
         * set connection and table name on current model instance
         * @param $name
         * @param null $table
         * @return ModelMethod|\Illuminate\Database\Eloquent\Model
         */
        public function connection( $name, $table = null )
        {
            $table = $table ?? Str::plural(Str::snake(class_basename($this->getModel())));
            return $this->getModel()->setConnection( $name )->setTable( "$name.$table");
        }
    
        /**
         * Deactivate specific resource in storage
         * @param null $modified_by
         * @return int
         */
        public function deActivate( $modified_by = null )
        {
            if( $modified_by && isset( $this->modified_by ) )
            {
                return $this->getModel()->update( [ 'active'=> 0, 'modified_by' => $modified_by ] );
            }
            return $this->getModel()->update( [ 'active'=> 0] );
        }
    
        /**
         * Activate specific resource in storage
         * @param null $modified_by
         * @return int
         */
        public function activate( $modified_by = null )
        {
            if( $modified_by && isset( $this->modified_by ) )
            {
                return $this->getModel()->update( [ 'active'=> 1, 'modified_by' => $modified_by ] );
            }
            return $this->getModel()->update( [ 'active'=> 1] );
        }
    
        /**
         * get Only active resources from storage
         * @return ModelMethod
         */
        public function onlyActive()
        {
            return $this->where("{$this->from}.active",1);
        }
        
        public function onlyTrashed()
        {
            return $this->where("{$this->from}.active",0);
        }

        public function ofToday()
        {
            return $this->whereDate("{$this->from}.created_at",date('Y-m-d'));
        }
        
        public function ofDate( $date = null )
        {
            $date = $date ?? date('Y-m-d');
            return $this->whereDate("{$this->from}.created_at",$date);
        }
        
        public function ofYear( $year = null )
        {
            $year = $year ?? date('Y');
            return $this->whereYear("{$this->from}.created_at",$year);
        }
        
        public function ofMonth( $month = null )
        {
            $month = $month ?? date('m');
            return $this->whereMonth("{$this->from}.created_at",$month);
        }

        public function ofStatus( $status )
        {
            return $this->where("{$this->from}.status",$status);
        }
        
        public function createdToday()
        {
            return $this->whereDate("{$this->from}.created_at",date('Y-m-d'));
        }
        
        public function updatedToday()
        {
            return $this->whereDate("{$this->from}.updated_at",date('Y-m-d'));
        }
        
        public function createdByUser( string $user , $date = null )
        {
            $date = $date ?? date('Y-m-d');
            return $this->whereDate("{$this->from}.created_at",$date)
                ->where("{$this->from}.created_by",$user);
        }
        
        public function updatedByUser( string $user , $date = null )
        {
            $date = $date ?? date('Y-m-d');
            return $this->whereDate("{$this->from}.updated_at",$date)
                ->where("{$this->from}.modified_by",$user);
        }
        
        public function betweenDates( $startDate = null, $endDate= null )
        {
            $startDate  = $startDate ?? date('Y-m-d',strtotime('-7 days'));
            $endDate  = $endDate ?? date('Y-m-d H:i:s');
            
            return  $this->whereBetween("{$this->from}.created_at",[ $startDate ,$endDate ]);
        }
        
        public function inDates( $dates = [] )
        {
            return  $this->whereIn("{$this->from}.created_at",$dates);
        }
        
        public function selectById( int $id, ...$columns)
        {
            $columns = isset( $columns[0] ) ? $columns : '*';
            return $this->where('id',$id)->select($columns)->first();
        }
        
        public function selectByColumn( $column, $value, ...$columns)
        {
            $columns = isset( $columns[0] ) ? $columns : '*';
            return $this->where($column,$value)->select($columns)->first();
        }
        
        /**
         * conditions to filter query result
         * @param array $conditions
         * @return mixed
         */
        public function applyFilters( array $conditions )
        {
            return $this->when( isset($conditions['basic']) && !empty($conditions['basic']), function($q) use($conditions){
                $q->where($conditions['basic']);
            })
            ->when( isset($conditions['array']) && !empty($conditions['array']), function($q) use($conditions){
                foreach ($conditions['array'] as $column => $value ){
                    $q->whereIn($column, $value);
                }
            })
            ->when( isset($conditions['notArray']) && !empty($conditions['notArray']), function($q) use($conditions){
                foreach ($conditions['notArray'] as $column => $value ){
                    $q->whereNotIn($column, $value);
                }
            })
            ->when( !isset($conditions['basic']) && !isset($conditions['array'])&& !isset($conditions['notArray']), function($q) use($conditions){
                $q->where($conditions);
            });
        }
    
        /**
         * paginate query result based on request input
         * @param Request $request
         * @return array
         */
        public function paginateResult( Request $request)
        {
            $page = $request->page ?? null;
            $per_page = $request->per_page  ?? null;
            return $this->when( $per_page && $page, function($q) use($page,$per_page) {
                $q->limit( $per_page )->offset( ( $page-1 ) * $per_page );
            })
            ->get();
        }
    }

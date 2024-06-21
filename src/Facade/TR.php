<?php
    
    namespace YS\MultiDB\Facade;
    
    use Illuminate\Support\Facades\Facade;
    
    class TR extends Facade
    {
        protected static function getFacadeAccessor()
        {
            return 'tr';
        }
    }

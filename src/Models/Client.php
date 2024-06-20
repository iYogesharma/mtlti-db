<?php

namespace App\Models;

use YS\MultiDB\Models\Model;

class Client extends Model
{
    protected $fillable = ['name','code','active','created_by','modified_by'];
}
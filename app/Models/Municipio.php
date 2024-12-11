<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'municipio';
    protected $primaryKey = 'MUNICIPIO';
    protected $connection = 'mysql2';


}

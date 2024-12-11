<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'conductor';
    protected $primaryKey = 'CONDUCTOR';
    protected $connection = 'mysql2';


    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'MUNICIPIO', 'MUNICIPIO');
    }
}

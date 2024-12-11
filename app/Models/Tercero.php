<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tercero extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'tercero';
    protected $primaryKey = 'TERCERO';
    protected $connection = 'mysql2';

    public function propietario(){

        return $this->hasOne(Propietario::class, 'TERCERO', 'TERCERO');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'MUNICIPIO', 'MUNICIPIO');
    }
}

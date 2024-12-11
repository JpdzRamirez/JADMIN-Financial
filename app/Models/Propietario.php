<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Propietario extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'propietario';
    protected $primaryKey = 'PROPIETARIO';
    protected $connection = 'mysql2';

    public function tercero(){

        return $this->belongsTo(Tercero::class, 'TERCERO', 'TERCERO');
    }

    public function vehiculos(){

        return $this->hasMany(Vehiculo::class, 'PROPIETARIO', 'TERCERO');
    }

}

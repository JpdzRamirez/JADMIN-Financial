<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'vehiculo';
    protected $primaryKey = 'VEHICULO';
    protected $connection = 'mysql2';

    public function propietario(){

        return $this->belongsTo(Propietario::class, 'PROPIETARIO', 'TERCERO');
    }

    public function empresa(){
        
        return $this->belongsTo(Empresa::class, 'EMPRESA', 'EMPRESA');
    }

    public function otrosPropietarios(){

        return $this->belongsToMany(Propietario::class, 'vehiculo_otro_propietario', 'VEHICULO', 'TERCERO', 'VEHICULO', 'TERCERO');
    }
}

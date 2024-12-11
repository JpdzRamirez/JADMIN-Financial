<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'empresa';
    protected $primaryKey = 'EMPRESA';
    protected $connection = 'mysql2';

    
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'EMPRESA', 'VEHICULO');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Costo extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'costos';

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuentas_id');
    }

    public function creditos()
    {
        return $this->belongsToMany(Credito::class, 'creditos_costos', 'costos_id', 'creditos_id')->withPivot('valor');
    }

    
}

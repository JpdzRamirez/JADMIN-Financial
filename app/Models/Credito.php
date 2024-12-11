<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'creditos';

    public function cliente()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function placa()
    {
        return $this->belongsTo(Placa::class, 'placas_id');
    }

    public function costos()
    {
        return $this->belongsToMany(Costo::class, 'creditos_costos', 'creditos_id', 'costos_id')->withPivot('valor');
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class, 'creditos_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'creditos_id');
    }

    public function factura()
    {
        return $this->hasOne(Factura::class, 'creditos_id',);
    }

    public function tipos()
    {
        return $this->belongsToMany(Tipocredito::class, 'tiposxcredito', 'creditos_id', 'tipos_credito_id')->withPivot('valor');
    }
}

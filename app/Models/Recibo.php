<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'recibos';

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'recibos_id');
    }

    public function facturas()
    {
        return $this->belongsToMany(Factura::class, 'facturasxrecibo', 'recibos_id', 'facturas_id')->withPivot('abono', 'saldo', 'mora');
    }

    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class, 'formas_pago_id');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'recibos_id');
    }
}
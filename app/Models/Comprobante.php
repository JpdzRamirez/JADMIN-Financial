<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'comprobantes';

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'comprobantes_id');
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'facturas_id');
    }

    public function tercero()
    {
        return $this->belongsTo(TerceroJADMIN::class, 'terceros_id');
    }
}

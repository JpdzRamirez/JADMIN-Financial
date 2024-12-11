<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'movimientos';

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuentas_id');
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'facturas_id');
    }

    public function nota()
    {
        return $this->belongsTo(Nota::class, 'notas_id');
    }


    public function tercero()
    {
        return $this->belongsTo(TerceroJADMIN::class, 'terceros_id');
    }
}

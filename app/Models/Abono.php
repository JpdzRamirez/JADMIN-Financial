<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abono extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'abonos';

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuentas_id');
    }

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'facturas_id');
    }   

}

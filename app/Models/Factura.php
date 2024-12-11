<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Factura extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'facturas';

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'facturas_id');
    }

    public function recibos()
    {
        return $this->belongsToMany(Recibo::class, 'facturasxrecibo', 'facturas_id', 'recibos_id');
    }

    public function credito()
    {
        return $this->belongsTo(Credito::class, 'creditos_id');
    }
    
    public function tercero()
    {
        return $this->belongsTo(TerceroCahors::class, 'terceros_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'factura_detalles', 'facturas_id', 'productos_id')->withPivot('cantidad', 'valor', 'iva');
    }
}

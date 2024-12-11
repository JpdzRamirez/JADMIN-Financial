<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturasxRecibo extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'facturasxrecibo';

    public function recibo()
    {
        return $this->belongsTo(Recibo::class, 'recibos_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NtaContabilidad extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'ntscontabilidad';

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'ntscontabilidad_id');
    }

    public function tercero()
    {
        return $this->belongsTo(TerceroJADMIN::class, 'terceros_id');
    }
}

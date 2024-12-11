<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguro extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'seguros';

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'seguros_id');
    }

    public function tercero()
    {
        return $this->belongsTo(TerceroCahors::class, 'terceros_id');
    }
}

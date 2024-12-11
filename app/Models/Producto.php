<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'productos';

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuentas_id');
    }

    public function contrapartida()
    {
        return $this->belongsTo(Cuenta::class, 'cuentas_id1');
    }
}

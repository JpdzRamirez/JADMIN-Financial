<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura_detalles extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'factura_detalles';
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagos_Cuotas extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'pagos_cuotas';
}

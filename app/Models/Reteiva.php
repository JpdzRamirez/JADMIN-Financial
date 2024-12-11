<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reteiva extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    protected $table = 'reteivas';

    public function compra()
    {
        return $this->belongsTo(Cuenta::class, 'cuentas_id1');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'formas_pago';

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuentas_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'formas_pago_id');
    }
}

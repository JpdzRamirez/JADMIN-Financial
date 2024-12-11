<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'cuotas';

    
    public function pagos()
    {
        return $this->belongsToMany(Pago::class, 'pagos_cuotas', 'cuotas_id', 'pagos_id');
    }

    public function credito()
    {
        return $this->belongsTo(Credito::class, 'creditos_id');
    }
}

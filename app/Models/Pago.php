<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'pagos';


    public function cuotas()
    {
        return $this->belongsToMany(Cuota::class, 'pagos_cuotas', 'pagos_id', 'cuotas_id')->withPivot('capital', 'interes', 'mora');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function credito()
    {
        return $this->belongsTo(Credito::class, 'creditos_id');
    }

    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class, 'formas_pago_id');
    }

    public function recibo()
    {
        return $this->belongsTo(Recibo::class, 'recibos_id');
    }
}

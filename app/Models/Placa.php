<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Placa extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'placas';

    
    public function cliente()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function creditos()
    {
        return $this->hasMany(Credito::class, 'placas_id');
    }
}


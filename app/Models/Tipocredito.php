<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipocredito extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'tipos_credito';

}

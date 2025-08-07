<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Archivo extends Model
{
    use HasFactory;

    protected $table = 'archivos';

    protected $fillable = [
        'nombre',
        'ruta',
        'formato',
    ];
}

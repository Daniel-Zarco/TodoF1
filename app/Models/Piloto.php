<?php

//18/05/2025
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Piloto extends Model
{
    use HasFactory;

    protected $table = 'z_pilotos';

    protected $fillable = [
        'driver_id',
        'nombre',
        'nacionalidad',
        'fecha_nacimiento',
    ];

    // Relación con estadísticas de carrera
    public function statsCarrera()
    {
        return $this->hasMany(StatsCarrera::class, 'piloto_id');
    }

    // Puedes añadir esto si un piloto pertenece a una escudería actual
    // pero en tu estructura actual NO está directamente en z_pilotos
    // public function escuderiaActual()
    // {
    //     return $this->belongsTo(Escuderia::class, 'escuderia_id');
    // }
}


/*namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Piloto extends Model
{
    use HasFactory;

    

    protected $table = 'z_pilotos';

    public function escuderiaActual()
    {
        return $this->belongsTo(Escuderia::class);
    }

    public function statsCarrera()
    {
        return $this->hasMany(StatsCarrera::class);
    }

    protected $fillable = [
        'nombre', 'nacionalidad', 'fecha_nacimiento'
    ];
}*/


<?php

//18/05/25
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Escuderia extends Model
{
    use HasFactory;

    protected $table = 'z_escuderias';

    protected $fillable = [
        'constructor_id',
        'nombre',
        'pais',
        'año_fundacion',
    ];

    public function statsCarrera()
    {
        return $this->hasMany(StatsCarrera::class, 'escuderia_id');
    }
}



/*namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Escuderia extends Model
{
    use HasFactory;

    

    protected $table = 'z_escuderias';

    public function pilotos()
    {
        return $this->hasMany(Piloto::class);
    }

    public function statsCarrera()
    {
        return $this->hasMany(StatsCarrera::class);
    }

    protected $fillable = [
        'nombre', 'pais', 'año_fundacion'
    ];
}
*/
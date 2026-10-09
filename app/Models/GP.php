<?php

//18/05/25
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GP extends Model
{
    use HasFactory;

    protected $table = 'z_gp';

    protected $fillable = [
        'nombre',
        'circuito',
        'pais',
        'fecha',
        'temporada',
        'ronda',
    ];

    public function statsCarrera()
    {
        return $this->hasMany(StatsCarrera::class, 'gran_premio_id');
    }
}


/*namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GP extends Model
{
    use HasFactory;

    protected $table = 'z_gp';

    public function statsCarrera()
    {
        return $this->hasMany(StatsCarrera::class, 'gran_premio_id');
    }

    protected $fillable = [
        'nombre', 'circuito', 'pais', 'fecha', 'temporada'
    ];
}
*/

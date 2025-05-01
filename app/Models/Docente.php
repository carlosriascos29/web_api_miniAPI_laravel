<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_docente';
    protected $table = 'docentes';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'titulo_academico',
        'estado'
    ];

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'docentes_materias', 'docentes_id_docentes', 'materias_id_materias');
    }
}

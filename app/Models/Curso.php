<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_curso';
    protected $table = 'cursos';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'estado'
    ];

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'estudiantes_cursos', 'cursos_id', 'estudiantes_id');
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'materias_cursos', 'cursos_id_cursos', 'materias_id_materias');
    }
}

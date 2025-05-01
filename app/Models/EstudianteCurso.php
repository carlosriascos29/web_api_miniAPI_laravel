<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstudianteCurso extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_estudiantes_cursos';
    protected $table = 'estudiantes_cursos';
    public $timestamps = false;

    protected $fillable = [
        'estudiantes_id',
        'cursos_id'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiantes_id', 'id_estudiante');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'cursos_id', 'id_curso');
    }
}

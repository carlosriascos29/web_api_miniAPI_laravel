<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaCurso extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_materias_cursos';
    protected $table = 'materias_cursos';
    public $timestamps = false;

    protected $fillable = [
        'materias_id_materias',
        'cursos_id_cursos'
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materias_id_materias', 'id_materia');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'cursos_id_cursos', 'id_curso');
    }
}

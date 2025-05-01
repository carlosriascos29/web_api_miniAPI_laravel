<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_materia';
    protected $table = 'materias';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'estado'
    ];

    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'docentes_materias', 'materias_id_materias', 'docentes_id_docentes');
    }

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'materias_cursos', 'materias_id_materias', 'cursos_id_cursos');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_estudiante';
    protected $table = 'estudiantes';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'estado'
    ];

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'estudiantes_cursos', 'estudiantes_id', 'cursos_id');
    }
}

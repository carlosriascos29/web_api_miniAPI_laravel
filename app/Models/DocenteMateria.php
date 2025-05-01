<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocenteMateria extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_docentes_materias';
    protected $table = 'docentes_materias';
    public $timestamps = false;

    protected $fillable = [
        'docentes_id_docentes',
        'materias_id_materias'
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docentes_id_docentes', 'id_docente');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materias_id_materias', 'id_materia');
    }
}

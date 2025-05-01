<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('materias_cursos', function (Blueprint $table) {
            $table->unsignedInteger('id_materias_cursos')->autoIncrement();
            $table->unsignedInteger('materias_id_materias');
            $table->unsignedInteger('cursos_id_cursos');
            
            $table->primary(['id_materias_cursos','materias_id_materias', 'cursos_id_cursos']);
            
            $table->foreign('materias_id_materias')
                ->references('id_materia')
                ->on('materias')
                ->onDelete('cascade');
                
            $table->foreign('cursos_id_cursos')
                ->references('id_curso')
                ->on('cursos')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('materias_cursos');
    }
};

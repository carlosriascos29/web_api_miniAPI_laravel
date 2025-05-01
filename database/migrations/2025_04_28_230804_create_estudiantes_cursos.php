<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estudiantes_cursos', function (Blueprint $table) {
            $table->unsignedInteger('id_estudiantes_cursos')->autoIncrement();
            $table->unsignedInteger('estudiantes_id');
            $table->unsignedInteger('cursos_id');
            
            $table->primary(['id_estudiantes_cursos', 'estudiantes_id', 'cursos_id']);
            
            $table->foreign('estudiantes_id')
                ->references('id_estudiante')
                ->on('estudiantes')
                ->onDelete('cascade');
                
            $table->foreign('cursos_id')
                ->references('id_curso')
                ->on('cursos')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('estudiantes_cursos');
    }
};

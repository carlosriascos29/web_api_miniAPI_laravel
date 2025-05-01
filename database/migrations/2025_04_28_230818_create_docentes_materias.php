<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('docentes_materias', function (Blueprint $table) {
            $table->unsignedInteger('id_docentes_materias')->autoIncrement();
            $table->unsignedInteger('materias_id_materias');
            $table->unsignedInteger('docentes_id_docentes');
            
            $table->primary(['id_docentes_materias', 'materias_id_materias', 'docentes_id_docentes']);
            
            $table->foreign('materias_id_materias')
                ->references('id_materia')
                ->on('materias')
                ->onDelete('cascade');
                
            $table->foreign('docentes_id_docentes')
                ->references('id_docente')
                ->on('docentes')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('docentes_materias');
    }
};

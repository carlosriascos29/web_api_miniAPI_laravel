<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->unsignedInteger('id_docente')->autoIncrement();
            $table->string('nombre', 80);
            $table->string('apellido', 80);
            $table->string('dni', 15);
            $table->string('titulo_academico', 80);
            $table->char('estado', 1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('docentes');
    }
};

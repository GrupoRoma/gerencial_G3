<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialJustificativasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialJustificativas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idEmpresa')->comment('Identificador da empresa');
            $table->unsignedBigInteger('idCentroCusto')->comment('Identificador do Centro de Custo');
            $table->unsignedBigInteger('idPeriodo')->comment('Identifica o período de referência');
            $table->unsignedBigInteger('idContaGerencial')->comment('Identifica a conta gerencial de referência da justificativa');
            $table->unsignedBigInteger('idUsuario')->comment('Identifica o usuário que registrou a justificativa');
            $table->text('justificativa')->comment('Justificativa apresentada pelo usuário');
            $table->foreign('idEmpresa')->references('id')->on('gerencialEmpresas');
            $table->foreign('idCentroCusto')->references('id')->on('gerencialCentroCusto');
            $table->foreign('idPeriodo')->references('id')->on('gerencialPeriodos');
            $table->foreign('idContaGerencial')->references('id')->on('gerencialContaGerencial');
            $table->foreign('idUsuario')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gerencialJustificativas');
    }
}

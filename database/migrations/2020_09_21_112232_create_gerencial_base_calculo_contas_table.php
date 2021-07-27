<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialBaseCalculoContasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialBaseCalculoContas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idBaseCalculo')->comment('Identificador da Base de Cálculo');
            $table->unsignedBigInteger('idContaGerencial')->comment('Identificador da Conta Gerencial');
            $table->foreign('idBaseCalculo')->references('id')->on('gerencialBaseCalculo');
            $table->foreign('idContaGerencial')->references('id')->on('gerencialContaGerencial');
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
        Schema::dropIfExists('gerencialBaseCalculoContas');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialTabelaRateiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialTabelaRateios', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 50)->comment('Descrição para identificação da tabela de rateio');
            $table->unsignedBigInteger('idEmpresa');
            $table->foreign('idEmpresa')->references('id')->on('gerencialEmpresas');
            $table->char('tabelaAtiva', 1)->comment('Identifica se a tabela de rateio está ativa');
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
        Schema::dropIfExists('gerencialTabelaRateios');
    }
}

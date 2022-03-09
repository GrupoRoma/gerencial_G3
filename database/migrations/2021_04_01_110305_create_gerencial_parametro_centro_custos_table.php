<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialParametroCentroCustosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialParametroCentroCustos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idEmpresa')->nullable()->comment('Código da empresa vinculada');
            $table->unsignedBigInteger('idCentroCustoOrigem')->comment('Identificador do centro de custo de Origem');
            $table->unsignedBigInteger('idCentroCustoDestino')->comment('Identificador do centro de custo de Destino');
            $table->foreign('idEmpresa')->references('id')->on('gerencialEmpresas');
            $table->foreign('idCentroCustoOrigem')->references('id')->on('gerencialCentroCusto');
            $table->foreign('idCentroCustoDestino')->references('id')->on('gerencialCentroCusto');
            $table->char('parametroAtivo', 1)->comment('Determina se o parâmetro está ativo');
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
        Schema::dropIfExists('gerencialParametroCentroCustos');
    }
}

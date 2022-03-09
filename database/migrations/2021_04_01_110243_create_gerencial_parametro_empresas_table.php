<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialParametroEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialParametroEmpresas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idEmpresaOrigem')->comment('Identificador da empresa de Origem');
            $table->unsignedBigInteger('idEmpresaDestino')->comment('Identificador da empresa de Destino');
            $table->unsignedBigInteger('idCentroCusto')->nullable()->comment('Identificador do Centro de Custo');
            $table->foreign('idEmpresaOrigem')->references('id')->on('gerencialEmpresas');
            $table->foreign('idEmpresaDestino')->references('id')->on('gerencialEmpresas');
            $table->foreign('idCentroCusto')->references('id')->on('gerencialCentroCusto');
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
        Schema::dropIfExists('gerencialParametroEmpresas');
    }
}

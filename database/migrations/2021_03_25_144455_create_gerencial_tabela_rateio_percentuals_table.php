<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialTabelaRateioPercentualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialTabelaRateioPercentual', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idTabela')->comment('Identificador da tabela de rateio');
            $table->foreign('idTabela')->references('id')->on('gerencialTabelaRateios');
            
            // Retirado da tabela GERENCIALTABELARATEIOS
            $table->unsignedBigInteger('idEmpresa');
            $table->foreign('idEmpresa')->references('id')->on('gerencialEmpresas');
            
            $table->unsignedBigInteger('idCentroCusto')->comment('Identificador do Centro de Custo');
            $table->foreign('idCentroCusto')->references('id')->on('gerencialCentroCusto');
            $table->decimal('percentual', 10,7)->comment('Percentual a ser aplicado no processo de rateio');
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
        Schema::dropIfExists('gerencialTabelaRateioPercentual');
    }
}

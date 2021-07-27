<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialContaContabilsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialContaContabil', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idContaGerencial')->comment('Identificador da Conta Gerencial');
            $table->foreign('idContaGerencial')->references('id')->on('gerencialContaGerencial');
            $table->integer('codigoContaContabilERP')->comment('Código da Conta Contábil no ERP [PlanoConta_Codigo]');
            $table->string('contaContabil', 20)->comment('Número da Conta Contábil no ERP [PlanoConta_ID]');
            $table->char('contaContabilAtiva', 1)->default('S')->comment('Conta Contábil Ativa - S: Sim , N: Não');
            $table->char('receitaVeiculo',1)->default('N')->comment('Conta contábil de receita de venda de veículos S:Sim | N:Não');

            $table->unsignedBigInteger('idCentroCusto')->nullable()->comment('Codigo do Centro de Custo');
            $table->foreign('idCentroCusto')->references('id')->on('gerencialCentroCusto');
            $table->integer('codigoSubContaERP')->nullable()->comment('Código da Sub-Conta Contabil');
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
        Schema::dropIfExists('gerencialContaContabil');
    }
}

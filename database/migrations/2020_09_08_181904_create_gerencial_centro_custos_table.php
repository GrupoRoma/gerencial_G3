<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialCentroCustosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialCentroCusto', function (Blueprint $table) {
            $table->id();
            $table->integer('codigoCentroCustoERP')->comment('Código do centro de custo no ERP Dealernet Workflow');
            $table->string('descricaoCentroCusto',50)->nullable()->comment('Descrição alternativa para o centro de custo (default = ERP)');
            $table->string('siglaCentroCusto',5)->nullable()->comment('Sigla alternativa para o centro de custo (default = ERP)');
            $table->char('centroCustoVendas',1)->default('N')->comment('Identifica o centro de custo como um centro de custo de vendas de veículos (comercial)');
            $table->char('centroCustoPosVendas',1)->default('N')->comment('Identifica o centro de custo como um centro de custo de pós-vendas (peças e serviços)');
            $table->char('analiseVertical',1)->default('N')->comment('Define o centro de custo como um centro de custo de análise vertical');
            $table->integer('ordemExibicao')->comment('Ordem de exibição no relatório Gerencial');
            $table->char('centroCustoAtivo',1)->default('S')->comment('Identifica se o centro de custo está ativo');
            $table->timestamps();

            // 18/09
            $table->char('recebeTvi')->default('S')->comment('Define se o Centro de Custo está habilitado para receber TVI');
            $table->char('visivelRelatorio')->default('S')->comment('Define se o Centro de Custo será exibido no relatório Gerencial');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gerencialCentroCusto');
    }
}

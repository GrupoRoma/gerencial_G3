<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialParametroRateiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialParametroRateio', function (Blueprint $table) {
            $table->id();
            $table->string('descricaoParametro', 100)->comment('Identificação do Parâmetro de Rateio');
            $table->unsignedBigInteger('idBaseCalculo')->nullable()->comment('Identificador da Base de Cálculo as ser utilizada');
            $table->unsignedBigInteger('idTipoLancamento')->comment('Identificador do Tipo de Lançamento associado ao Parâmetro');
            $table->unsignedBigInteger('idTabelaRateio')->nullable()->comment('Identificador da Tabela de Rateio');

            $table->foreign('idBaseCalculo')->references('id')->on('gerencialBaseCalculo');
            $table->foreign('idTipoLancamento')->references('id')->on('gerencialTipoLancamento');
            $table->foreign('idTabelaRateio')->references('id')->on('gerencialTabelaRateio');

            $table->text('codigoEmpresaOrigem')->comment('Lista de códigos da(s) empresa(s) de origem [separados por vírgula]');
            $table->text('codigoEmpresaDestino')->comment('Lista de códigos da(s) empresa(s) de destino [separados por vírgula]');
            $table->text('codigoContaGerencialOrigem')->comment('Lista de códigos da(s) conta(s) gerenciais de origem [separados por vírgula]');
            $table->text('codigoContaGerencialDestino')->comment('Lista de códigos da(s) conta(s) gerenciais de destino [separados por vírgula]');
            $table->text('codigoCentroCustoOrigem')->comment('Lista de códigos do(s) centro(s) de custo de origem [separados por vírgula]');
            $table->text('codigoCentroCustoDestino')->comment('Lista de códigos do(s) centro(s) de custo de destino [separados por vírgula]');
            $table->string('historicoPadrao', 100)->nullable()->comment('Histórico padrão para os lançamentos gerados');
            $table->char('formaAplicacao',4)->default('PESO')->comment('Forma de aplicação do Parâmetro PESO:Peso do valor em relação à base de cálculo, TBLA: Tabela com definição dos valores/percentuais por centro de custo');
            $table->char('parametroAtivo',1)->default('S')->comment('Define se o Parâmetro está ativo');
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
        Schema::dropIfExists('gerencialParametroRateio');
    }
}

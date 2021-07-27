<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialOutrasContasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         *  Estrutura JSON da coluna "destino"
         * 
         *  {
         *      "idEmpresa": id,
         *      "proporcao": 100,
         *      "centroCusto": id
         *  }
         * 
         *  Uso: $json->idEmpresa, $json->proporcao, $json->centroCusto
         * 
         */
        Schema::create('gerencialOutrasContas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('codigoEmpresaERP')->comment('Código da empresa no ERP');
            $table->foreign('codigoEmpresaERP')->references('id')->on('gerencialEmpresas');
            $table->integer('codigoContaContabilERP')->comment('Código da Conta Contábil no ERP');
//            $table->foreign('idContaContabil')->references('id')->on('gerencialContaContabil');
            $table->decimal('percentualSaldo', 10,2)->nullable()->default('100')->comment('Percentual a ser apurado no saldo da conta nos lancamentos contábeis');
            $table->string('destino', 100)->comment('String JSON com os dados para o destino da distribuição do valor apurado (ver estrutura json no arquivo de migration)');
            $table->text('historicoPadrao')->nullable()->comment('Histórico padrão para os lançamentos gerados de outras contas contábeis');
            $table->char('outrasContasAtivo',1)->default('S')->comment('Ativo S: Sim, N: Não');
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
        Schema::dropIfExists('gerencialOutrasContas');
    }
}

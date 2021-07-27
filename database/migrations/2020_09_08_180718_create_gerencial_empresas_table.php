<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * LOTES CONTÁBEIS
         * ME   - Movimentação de Entrada
         * NE   - Nota Fiscal de Entrada
         * EV   - Nota Fiscal de Entrada de Veículos
         * CP   - Contas a Pagar
         * NS   - Nota Fiscal de Saída
         * SV   - Nota Fiscal de Saída de Veículos
         * CP   - Contas a Pagar
         * CR   - Contas a Receber
         */
        Schema::create('gerencialEmpresas', function (Blueprint $table) {
            $table->id();
            $table->integer('codigoEmpresaERP')->comment('Código da empresa no Dealernet Workflow');
            $table->integer('codigoRegional')->comment('Identificador da regional que a empresa faz parte');
            $table->string('nomeAlternativo', 50)->nullable()->comment('Nome alternativo para a empresa no Gerencial');
            $table->char('empresaAtiva',1)->default('S')->comment('Identifica se a empresa está ativa no Gerencial');
            $table->char('validaIntegracaoContabil',1)->default('N')->comment('Define se deve ser validada a integração contábil antes da importação');
//            $table->char('validaLoteContabil',1)   ->default('N')->comment('Define se deve ser validada a existência e fechamento do lote contábil antes da importação');
            $table->string('validaLoteContabil',50)->comment('Relação de tipo de lote contábil a serem validados *veja a relação no comentário da tabela*');
            $table->char('rateioAdmLocal',1)->default('N')->comment('Define se a empresa recebe rateio de ADM Local');
            $table->char('rateioLogistica',1)->default('N')->comment('Define se a empresa participa no rateio dos valores do centro de custo de Logística');
            $table->float('areaFisica')->nullable()->comment('Área em m2, ocupada pela empresa');
            $table->integer('vagasEstimadas')->nullable()->comment('Total de vagas de estacionamento');
            $table->integer('manobristas')->nullable()->comment('Número de manobristas');
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
        Schema::dropIfExists('gerencialEmpresas');
    }
}

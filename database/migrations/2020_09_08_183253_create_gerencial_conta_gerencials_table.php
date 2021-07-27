<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialContaGerencialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialContaGerencial', function (Blueprint $table) {
            $table->id();
            $table->char('codigoContaGerencial',5)->comment('Código de identificação da Conta Gerencial');
            $table->string('descricaoContaGerencial', 50)->comment('Descrição da Conta Gerencial');
            $table->text('infoContaGerencial')->nullable()->comment('Detalhamento da Conta Gerencial');

            // Pelo menos um Grupo de Conta e/ou Sub-Grupo deve ser informado no cadastro
            $table->unsignedBigInteger('idGrupoConta')->nullable()->comment('Identificador do Grupo de Conta associado à Conta Gerencial');
#            $table->unsignedBigInteger('idSubGrupoConta')->nullable()->comment('Identificador do Sub-Grupo de Conta associado à conta Gerencial');
            $table->foreign('idGrupoConta')->references('id')->on('gerencialGrupoConta');
#            $table->foreign('idSubGrupoConta')->references('id')->on('gerencialSubGrupoConta');
            $table->char('analiseVariacao',1)->default('N')->comment('Define se a Conta Gerencial é passível de apresentação de justificativa em caso de variação');
            $table->float('percentualVariacaoMaximo', 10,2)->nullable()->comment('Percentual máximo de variação mensal aceita (para mais ou para menos)');
            $table->float('valorVariacaoMaximo', 10,2)->nullable()->comment('Valor máximo de variação mensal aceita (para mais ou para menos)');
            $table->char('contaGerencialAtiva',1)->default('S')->comment('Define se a Conta Gerencial está ativa');


            // 21/09
            $table->char('receitaCustoFI',3)->nullable()->comment('Identifica se a conta recebe CST: Cusuto ou REC: Receita de F&I ');
            $table->char('rateioAdmLocal',1)->comment('Recebe o rateio de ADM Local S:Sim | N:Não');
            $table->char('rateioAdmCentral',1)->comment('Recebe o rateio de ADM Central S:Sim | N:Não');

            // 09/10 - Paramêtros de Contas de Veículos
            $table->text('valoresVeiculo')->nullable()->comment('Recebe os valores de Veículos [RCD] Receita e/ou Devolução de venda, [DSC] Desconto, [CST] Custo, [PIS] Pis, [CFS] Cofins, [ICM] ICMS, [BEP] Bônus Empresa, [BFB] Bônus Fábrica, [HBK] Hold Back');

            // 26/11
            $table->char('quadricula',1)->default('N')->comment('Identifica se a conta gerencial é uma conta da quadrícula N: Não, S: Sim');
            $table->char('acumuladora',1)->default('N')->comment('Identifica se a conta gerencial é uma conta Acumuladora, controle de saldo N: Não, S: Sim');

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
        Schema::dropIfExists('gerencialContaGerencial');
    }
}

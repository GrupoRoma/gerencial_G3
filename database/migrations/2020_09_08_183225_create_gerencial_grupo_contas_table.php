<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialGrupoContasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialGrupoConta', function (Blueprint $table) {
            $table->id();
            $table->string('codigoGrupoConta', 5)->comment('Código do Grupo de Conta');
            $table->string('descricaoGrupoConta', 50)->comment('Descrição do Grupo de Conta');
            $table->text('infoGrupoConta')->nullable()->comment('Detalhes sobre o Grupo de Conta');
            $table->char('receitaCustoMercadoria',3)->nullable()->comment('Identifica o Grupo de Conta como REC: Receita, CST: Custo, ou NULL: Nenhum');
            $table->integer('ordemExibicao')->comment('Ordem para exibição do Grupo de Conta no Relatório Gerencial (sugerir o último valor registrado +1)');
            $table->char('grupoContaAtivo',1)->default('S')->comment('Define se o Grupo de Conta está ativo');
            $table->unsignedBigInteger('idSubGrupoConta')->comment('Identifica o SubGrupo de Conta');
            $table->foreign('idSubGrupoConta')->references('id')->on('gerencialSubGrupoConta');
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
        Schema::dropIfExists('gerencialGrupoConta');
    }
}

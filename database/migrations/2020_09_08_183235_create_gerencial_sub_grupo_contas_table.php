<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialSubGrupoContasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialSubGrupoConta', function (Blueprint $table) {
            $table->id();
#            $table->unsignedBigInteger('idGrupoConta')->comment('Identificador do Grupo de Conta associado');
#            $table->foreign('idGrupoConta')->references('id')->on('gerencialGrupoConta');
            $table->string('descricaoSubGrupoConta', 50)->comment('Descricção do Sub-Grupo de Conta');
            $table->char('baseMargemBruta',1)->default('N')->comment('Identifica se a conta associada é base para cálculo da margem bruta');
#            $table->integer('ordemExibicao')->nullable()->comment('Ordem para exibição do Sub-Grupo no relatório Gerencial, dentro do Grupo de conta associado');
            $table->char('subGrupoAtivo',1)->default('S')->comment('Define se o Sub-Grupo de Conta está ativo');
#            $table->unsignedBigInteger('idUsuario_created')->comment('Identifica o usuário que criou o registro');
#            $table->foreign('idUsuario_created')->references('id')->on('users');
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
        Schema::dropIfExists('gerencialSubGrupoConta');
    }
}

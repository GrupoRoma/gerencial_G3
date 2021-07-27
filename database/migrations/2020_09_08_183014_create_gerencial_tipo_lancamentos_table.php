<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialTipoLancamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialTipoLancamento', function (Blueprint $table) {
            $table->id();
            $table->string('descricaoTipoLancamento', 50)->comment('Identificação do tipo de lançamento');
            $table->text('sobreTipoLancamento')->nullable()->comment('Detalhamento sobre o tipo de lançamento');
            $table->string('historicoTipoLancamento')->nullable()->comment('Histórico padrão para o tipo de lançamento');
            $table->char('historicoIncremental',1)->default('N')->comment('Identifica se o histórico do tipo de lançamento deve complementar outros históricos');
            $table->char('tipoLancamentoAtivo',1)->default('S')->comment('Identifica se o tipo de lançamento está ativo');
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
        Schema::dropIfExists('gerencialTipoLancamento');
    }
}

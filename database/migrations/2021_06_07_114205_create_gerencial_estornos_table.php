<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialEstornosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialEstornos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idContaGerencial')->nullable()->comment('Identificador da conta gerencial');
            $table->foreign('idContaGerencial')->references('id')->on('gerencialContaGerencial');
            $table->integer('codigoContaContabil')->nullable()->comment('Identificador da conta contábil no ERP');
            $table->unsignedBigInteger('idCentroCusto')->nullable()->comment('Identificador do centro de custo');
            $table->foreign('idCentroCusto')->references('id')->on('gerencialCentroCusto');
            $table->char('estornoAtivo',1)->comment('Identifica se o parâmetro de estorno está ativo S: Sim | N: Não');
            $table->text('justificativa')->nullable()->comment('Justificativa para o estorno');

            // alterado em 18/02/22
            $table->integer('codigoSubContaERP')->nullable()->comment('Código da sub-conta contábil associada');

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
        Schema::dropIfExists('gerencialEstornos');
    }
}

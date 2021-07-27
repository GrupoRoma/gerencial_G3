<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialAmortizacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialAmortizacao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idContaGerencial')->nullable();
            $table->foreign('idContaGerencial')->references('id')->on('gerencialContaGerencial');
            $table->string('descricao', 100)->comment('Descrição da amortização');
            $table->unsignedBigInteger('idTipoLancamento')->comment('Identificador do tipo de lançamento associado');
            $table->foreign('idTipoLancamento')->references('id')->on('gerencialTipoLancamento');
            $table->decimal('valorPrincipal', 10,2)->comment('Valor principal a ser amortizado');
            $table->decimal('valorParcela', 10,2)->comment('Valor da parcela a ser amortizado');
            $table->decimal('saldoAmortizacao', 10,2)->nullable()->comment('Saldo a amortizar');
            $table->integer('numeroParcelas')->comment('Total de parcelas para amortização');
            $table->integer('parcelasAmortizadas')->nullable()->comment('Número de parcelas já amortizadas');
            $table->char('tipoValor', 3)->default('PRP')->comment('Tipo de valor da parcela ABS: Absoluto [valor informado] | PRP: Proporcional ao número de parcelas [calculado]');
            // Destino
            $table->string('empresasDestino', 200)->comment('Relação de Empresas de destino');
            $table->unsignedBigInteger('idContaGerencialDestino');
            $table->foreign('idContaGerencialDestino')->references('id')->on('gerencialContaGerencial');
            $table->unsignedBigInteger('idCentroCusto');
            $table->foreign('idCentroCusto')->references('id')->on('gerencialCentroCusto');
            
            $table->text('historico')->nullable()->comment('Histórico padronizado para os lançamentos de amortização');
            $table->char('amortizacaoAtiva',1)->default('S')->comment('Identifica se a regra está ativa S:Sim | N:Não');

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
        Schema::dropIfExists('gerencialAmortizacao');
    }
}

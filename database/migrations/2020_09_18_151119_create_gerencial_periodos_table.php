<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialPeriodosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialPeriodos', function (Blueprint $table) {
            $table->id();
            $table->char('periodoMes', 2)->comment('Mês de referência para o período gerencial');
            $table->char('periodoAno', 4)->comment('Ano de referência para o período gerencial');
            $table->char('periodoAtivo', 1)->default('S')->comment('Período ativo');
            $table->char('periodoSituacao', 2)->default('FC')
                  ->comment('Identifica a situação do período AB: Aberto para lançamentos e consultas, FC: Fechado para lançamentos e aberto para consultas, LC: Aberto para lançamentos e fechado para consultas');
            $table->text('observacoes')->nullable()->comment('Observações gerais sobre o período');
            $table->unsignedBigInteger('idUsuario')->comment('Identificador do usuário que registrou a última alteração do período');
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
        Schema::dropIfExists('gerencialPeriodos');
    }
}

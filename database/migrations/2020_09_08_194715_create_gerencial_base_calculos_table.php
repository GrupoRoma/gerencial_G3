<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialBaseCalculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialBaseCalculo', function (Blueprint $table) {
            $table->id();
            $table->string('descricaoBaseCalculo', 50)->comment('Identificação da Base de Cálculo');
            $table->char('valorOrigem',1)->default('N')->comment('Utiliza o valor total da Origem para Redistribuição no Destino');
            $table->char('valorManual',1)->default('N')->comment('Identifica que a Base de Cálculo será informada manualmente');
            $table->char('baseCalculoAtiva',1)->default('S')->comment('Define se a Base de Cálculo está ativa');
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
        Schema::dropIfExists('gerencialBaseCalculo');
    }
}

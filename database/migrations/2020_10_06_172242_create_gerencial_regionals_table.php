<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialRegionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialRegional', function (Blueprint $table) {
            $table->id();
            $table->string('descricaoRegional', 50)->comment('Nome para identificação da regional');
            $table->unsignedBigInteger('codigoEmpresaVendaExterna')->nullable()->comment('Código da Empresa que receberá as vendas externas');
            $table->foreign('codigoEmpresaVendaExterna')->references('id')->on('gerencialEmpresas');
            $table->string('codigoVendasExternasERP', 50)->nullable()->comment('Códigos do vendedor no ERP que identifica o vendedor de Vendas Externas (VE), separados por vírgula');
            $table->unsignedBigInteger('codigoEmpresaVeiculosUsados')->nullable()->comment('Código da Empresa que receberá as vendas de veículos usados');
            $table->foreign('codigoEmpresaVeiculosUsados')->nullable()->references('id')->on('gerencialEmpresas');
            $table->unsignedBigInteger('codigoEmpresaRateioLogistica')->nullable()->comment('Código da Empresa que receberá os valores referentes ao Rateio da Logística');
            $table->foreign('codigoEmpresaRateioLogistica')->nullable()->references('id')->on('gerencialEmpresas');
            $table->text('tipoTituloBonusFabrica')->nullable()->comment('Identifica os tipos de título de Bônus Fábrica');
            $table->integer('codigoRegionalAntigo')->nullable()->comment('Código da Regional no Gerencial anterior [COD_REG]');

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
        Schema::dropIfExists('gerencialRegional');
    }
}

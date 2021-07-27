<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialLancamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialLancamentos', function (Blueprint $table) {
            $table->id();
            $table->integer('anoLancamento')->comment('Ano de referência do lançamento gerencial');
            $table->smallInteger('mesLancamento')->comment('Mês de referência do lancamento gerencial');
            $table->string('codigoContaContabil')->nullable()->comment('Código de identificação da conta contábil [ex: 311001000001]');
            $table->unsignedBigInteger('idEmpresa')->comment('Código da empresa de referência do lançamento gerencial');
            $table->foreign('idEmpresa')->references('id')->on('gerencialEmpresas');
            $table->unsignedBigInteger('centroCusto')->comment('Código do centro de custo de referência do lançamento gerencial');
            $table->foreign('centroCusto')->references('id')->on('gerencialCentroCusto');
            $table->unsignedBigInteger('idContaGerencial')->comment('Código da conta gerencial de referência do lançamento');
            $table->foreign('idContaGerencial')->references('id')->on('gerencialContaGerencial');
            $table->char('creditoDebito', 3)->comment('Tipo lançamento CRD: Crédito ou DEB: Débito');
            $table->decimal('valorLancamento', 10,2)->comment('Valor do lançamento gerencial');
            $table->text('historicoLancamento')->comment('Histórico do lançamento gerencial');
            $table->unsignedBigInteger('idTipoLancamento')->comment('Identificador do tipo de lançamento');
            $table->foreign('idTipoLancamento')->references('id')->on('gerencialTipoLancamento');
            $table->integer('idLancamentoOrigem')->nullable()->comment('Utilizado para identificar o lançamento gerencial de origem para casos como CONTRAPARTIDA, REVERSÃO [ver tipo de lançamento]');
            $table->integer('numeroLote')->nullable()->comment('Número do lote de lançamento gerencial');
            $table->string('numeroDocumento')->nullable()->comment('Número do documento de identificação do lançamento gerencial');
            $table->unsignedBigInteger('idUsuario')->comment('Identificador do usuário que registrou o lançamento gerencial');
            $table->foreign('idUsuario')->references('id')->on('users');
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
        Schema::dropIfExists('gerencialLancamentos');
    }
}

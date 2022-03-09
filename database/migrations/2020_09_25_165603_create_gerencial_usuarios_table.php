<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerencialUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerencialUsuarios', function (Blueprint $table) {
            $table->id();
            $table->text('empresasAcesso')->nullable()->comment('Lista de empresas que o usuário pode consultar ou registrar TVI(separadas por vírgula)');
            $table->text('centrosCustoAcesso')->nullable()->comment('Lista de centros de custo que o usuário pode consultar ou registrar TVI (separado por vírgula)');
            $table->text('contaGerencialAcesso')->nullable()->comment('Lista de contas gerenciais que o usuário pode consultar (separadas por vírgula)');            
            $table->char('gerencialTVI', 3)->comment('Indica se o usuário poderá TVI: Registrar TVI, GER: Consultar Gerencial, AMB: Ambos, tanto registra TVI como consultar o Gerencial');
            $table->char('permissaoAtiva',1)->comment('Indica se as configurações para o usuário estão ativas, e consequentemente o acesso dele às consultas do gerencial');
            $table->unsignedBigInteger('idUsuario')->comment('Identificador [FK] do usuário');
            $table->foreign('idUsuario')->references('id')->on('users');

            $table->char('tipoUsuarioGerencial', 3)->comment('Tipo de usuário do gerencial OPE: Operador | GST: Gestor');
            
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
        Schema::dropIfExists('gerencialUsuarios');
    }
}

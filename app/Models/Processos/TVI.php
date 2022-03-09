<?php

namespace App\Models\Processos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TVI extends Model
{
    use HasFactory;

    /**
     *  Retorna todas as TVI's registradas no período para processamento e
     *  registro do lançamento gerencial
     * 
     *  @param  String     Mês
     *  @param  String     Ano
     * 
     *  @return dbObject
     * 
     */
    public function getTVI(String $mes, String $ano)
    {
        return  DB::SELECT("SELECT  codigoTvi                   = sga_gerencialTvi.COD_TVI,
                                    mesTvi                      = DATEPART(MONTH,sga_gerencialTvi.dataInclusao),
                                    anoTvi                      = DATEPART(YEAR,sga_gerencialTvi.dataInclusao),
                                    codigoEmpresaOrigem         = sga_gerencialTvi.EMP_CD_ORIGEM,
                                    codigoEmpresaDestino        = sga_gerencialTvi.EMP_CD_DESTINO,
                                    codigoCentroCustoOrigem     = sga_gerencialTvi.COD_CCUSTO_ORIGEM,
                                    codigoCentroCustoDestino    = sga_gerencialTvi.COD_CCUSTO_DESTINO,
                                    codigoUsuarioAprovacao      = sga_gerencialTvi.COD_USUARIO_APROVACAO,
                                    valor                       = sga_gerencialTvi.valor,
                                    situacao                    = sga_gerencialTvi.situacao,
                                    observacaoTvi               = sga_gerencialTvi.observacao,
                                    dataAprovacao               = sga_gerencialTvi.dataAprovacao,
                                    despesaSalario              = sga_gerencialTvi.despesaSalario,
                                    codGrupoContaGerencial      = sga_gerencialTvi.COD_GRP,
                                    codigoContaGerencial        = G3_gerencialContaGerencial.id,
                                    idEmpresaOrigem				= empresaOrigem.id,
                                    idEmpresaDestino			= empresaDestino.id
                            FROM    GAMA..sga_gerencialTvi                      (nolock)
                            JOIN	GAMA..G3_gerencialContaGerencial	        (nolock) ON REPLICATE('0', (5-LEN(G3_gerencialContaGerencial.codigoContaGerencial)))+G3_gerencialContaGerencial.codigoContaGerencial = SGA_gerencialTvi.COD_GRP
                            JOIN	GAMA..G3_gerencialEmpresas empresaDestino	(nolock) ON empresaDestino.codigoEmpresaERP		= SGA_gerencialTvi.EMP_CD_DESTINO
                            JOIN	GAMA..G3_gerencialEmpresas empresaOrigem	(nolock) ON empresaOrigem.codigoEmpresaERP		= SGA_gerencialTvi.EMP_CD_ORIGEM
                            WHERE   MONTH(sga_gerencialTvi.dataTransferencia)   = ".$mes."
                            AND     YEAR(sga_gerencialTvi.dataTransferencia)    = ".$ano."
                            AND     sga_gerencialTvi.situacao                   <> 'N'");
    }

    /**
     *  Aprovação sumária de TVI's pendentes de aprovação
     *  
     *  @param   Array    Código da TVI
     * 
     *  @return boolean
     */
    public function aprovacaoSumaria(Array $codigoTVI)
    {
        foreach ($codigoTVI as $index => $data) {
            $updateData = DB::connection('sga')
                            ->table('gerencialTvi')
                            ->where('COD_TVI', $data['codigoTVI'])
                            ->update([  'situacao'              => 'S',
                                        'COD_USUARIO+APROVACAO' => 1908,
                                        'dataAprovacao'         => date('Y-m-d')]);

        /*$updateData = DB::unprepared("  UPDATE GAMA..SGA_gerencialTvi 
                                            SET     situacao = 'S', 
                                                    COD_USUARIO_APROVACAO = 1908, 
                                                    dataAprovacao = '".date('Y-m-d H:i:s')."'
                                            WHERE   COD_TVI = ".$data['codigoTVI']);
*/
            if (!$updateData)   return FALSE;
        }
        
        return TRUE;
    }
}

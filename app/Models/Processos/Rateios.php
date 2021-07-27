<?php

namespace App\Models\Processos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rateios extends Model
{
    use HasFactory;

    /**
     *  Rateio Logística
     *  Processa o rateio da logística
     * 
     *  @param      string      mes
     *  @param      string      ano
     * 
     *  @return     object      dbData
     * 
     */
    public function rateioLogistica(String $mes, String $ano ) {
        $dataInicial    = date('Y-m-d H:i:s', mktime(0,0,0,$mes,1,$ano));
        $dataFinal      = date('Y-m-t H:i:s', mktime(23,59,59,$mes,1,$ano));

        // Cria uma VIEW TEMPORÁRIA COM OS DADOS DAS VENDAS
        $dbView     = DB::insert(DB::raw("CREATE VIEW V_TMPVENDAS AS 
                                            SELECT	codigoEmpresa	= COALESCE( -- 1. Empresa da Proposta se o vendedor for de regional diferente (ex: FIAT vendendo FORD)
                                                                        (CASE WHEN SGA_REGIONAIS.COD_REG <> regionalVendedor.COD_REG  
                                                                                    AND Estoque.Estoque_Sigla in ('VN','SL','DI')
                                                                            THEN Proposta.Proposta_EmpresaCod
                                                                            ELSE NULL
                                                                        END),

                                                                        -- 2.	Vendas de Usados Realizadas por Vendedores Roma Citroen MG ou Faturados Pela Citroen MG e 
                                                                        --		faturamento ocorre em lojas de Grupos Diferentes (Exemplo : Roma Fiat MG e Roma Ford MG)
                                                                        (CASE WHEN (SGA_REGIONAIS.COD_REG <> regionalVendedor.COD_REG  OR SGA_REGIONAIS.COD_REG = 30 )
                                                                                        AND Estoque.Estoque_Sigla		IN ('VU','VI') 
                                                                                        AND regionalVendedor.COD_REG	= 30
                                                                            THEN	2	-- FIAT FSA
                                                                            ELSE NULL
                                                                        END),
                                                                
                                                                        -- 3. Vendas da regional ROMA TOYOTA RJ
                                                                        (CASE WHEN (SGA_REGIONAIS.COD_REG <> regionalVendedor.COD_REG  OR SGA_REGIONAIS.COD_REG = 39 )					     
                                                                                    AND Estoque.Estoque_Sigla		IN ('VU','VI') 
                                                                                    AND regionalVendedor.COD_REG	= 39 
                                                                            THEN	70		-- Toyota RJ
                                                                            ELSE NULL
                                                                        END),
                                                                
                                                                        -- 4. Aloca a venda na Yamaha Betim se a Venda for feita  por vendedores Alocados em Outra Unidade Yamaha
                                                                        (CASE WHEN Proposta.Proposta_EmpresaCod = 67 
                                                                                    AND Empresa.Empresa_Codigo <> 67
                                                                            THEN	Proposta.Proposta_EmpresaCod
                                                                            ELSE NULL
                                                                        END),

                                                                        -- 5. Empresa cadastrada para o vendedor no SGA
                                                                        (SELECT TOP 1 Empresa.Empresa_Codigo
                                                                            FROM gama..sga_comercialVendedorEmpresa		(nolock)
                                                                            JOIN GrupoRoma_DealernetWF..Empresa			(nolock) ON Empresa.Empresa_Codigo = sga_comercialVendedorEmpresa.emp_cd
                                                                            WHERE sga_comercialVendedorEmpresa.fun_cd		= NotaFiscal.NotaFiscal_UsuCodVendedor
                                                                            AND   sga_comercialVendedorEmpresa.dataInicio	<= NotaFiscal.NotaFiscal_DataEmissao
                                                                            ORDER BY sga_comercialVendedorEmpresa.dataInicio DESC),

                                                                        -- 6. Empresa da Nota Fiscal
                                                                        Empresa.Empresa_Codigo),
                                                    estoque             = CASE WHEN Estoque.Estoque_Sigla IN ('VN','SL') THEN 'VN' ELSE 'VD' END,
                                                    devolucaoAnterior   = 0,
                                                    vendido             = 1
                                                    /*vendido             = case when nfEntrada.NotaFiscal_Numero is null or nfEntrada.NotaFiscal_DataEmissao > '2021-05-18 23:59:59' then 1
                                                                                 when nfEntrada.NotaFiscal_Numero is not null or nfEntrada.NotaFiscal_DataEmissao <= '2021-05-18 23:59:59' then 0
                                                                            end
                                                    */
                                    FROM GrupoRoma_DealernetWF..NotaFiscal              (nolock)
                                    JOIN GrupoRoma_DealernetWF..NotaFiscalItem          (nolock) ON NotaFiscal.NotaFiscal_Codigo     = NotaFiscalItem.NotaFiscal_Codigo 
                                    JOIN GrupoRoma_DealernetWF..Proposta                (nolock) ON Proposta.Proposta_NotaFiscalCod  = NotaFiscal.NotaFiscal_Codigo 
                                    JOIN GrupoRoma_DealernetWF..NaturezaOperacao        (nolock) ON NaturezaOperacao.NaturezaOperacao_Codigo = NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                                    JOIN GrupoRoma_DealernetWF..Usuario                 (nolock) ON Usuario.Usuario_Codigo                   =  NotaFiscal.NotaFiscal_UsuCodVendedor
                                    JOIN gama..sga_empresas       				        (nolock) ON sga_empresas.emp_cd =  NotaFiscal.NotaFiscal_EmpresaCod 
                                    JOIN gama..SGA_REGIONAIS                            (nolock) ON SGA_REGIONAIS.COD_REG = sga_empresas.cod_reg 	
                                    JOIN GrupoRoma_DealernetWF..Estoque                 (nolock) ON Estoque.Estoque_Codigo = NotaFiscalItem.NotaFiscalItem_EstoqueCod
                                    JOIN GrupoRoma_DealernetWF..Empresa                 (nolock) ON Empresa.Empresa_Codigo  
                                                                = COALESCE( -- 1. Veiculos usados Faturados pela Peugeot RJ, atribui a venda a unidade Fiat Uruguai
                                                                        (case when SGA_REGIONAIS.COD_REG in (26) and Estoque.Estoque_Sigla in ('VU','VI') then 5 else null end),
                                                                            
                                                                            -- 2. Empresa cadastrada para o vendedor no SGA
                                                                            (select top 1 sga_comercialVendedorEmpresa.emp_cd
                                                                                from gama..sga_comercialVendedorEmpresa (nolock)
                                                                                where sga_comercialVendedorEmpresa.fun_cd = NotaFiscal.NotaFiscal_UsuCodVendedor
                                                                                and   sga_comercialVendedorEmpresa.dataInicio <= NotaFiscal.NotaFiscal_DataEmissao
                                                                                order by sga_comercialVendedorEmpresa.dataInicio desc),

                                                                            -- 3. Colaboradores Transferidos (rubi)
                                                                            (select top 1 sga_empresas.emp_cd
                                                                                FROM gama..r034fun 
                                                                                JOIN gama..r038hfi (NOLOCK) ON r038hfi.numcad = r034fun.numcad AND r038hfi.numemp = r034fun.numemp
                                                                                join gama..sga_empresas   (nolock) on sga_empresas.col_rm = case when r038hfi.numemp <> r038hfi.empatu then r038hfi.empatu else r038hfi.numemp end  and sga_empresas.fil_rm = r038hfi.codfil
                                                                                WHERE replicate('0', (11-len(convert(varchar, r034fun.numcpf))))+convert(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                                                AND  r038hfi.cadatu != r038hfi.numcad
                                                                                AND  r038hfi.datalt <= NotaFiscal.NotaFiscal_DataEmissao
                                                                                AND  (r038hfi.tipadm IN ('3','4')) 
                                                                                group by sga_empresas.emp_cd, r034fun.datafa
                                                                                order by r034fun.datafa desc ),

                                                                            -- 4. Colaboradores demitidos (rubi)
                                                                            (SELECT top 1 sga_empresas.emp_cd
                                                                                FROM gama..r034fun 
                                                                                JOIN gama..r038hfi (NOLOCK) ON r038hfi.numcad = r034fun.numcad 
                                                                                AND r038hfi.numemp = r034fun.numemp AND r038hfi.codfil = r034fun.codfil
                                                                                join gama..sga_empresas (nolock) on sga_empresas.col_rm = r038hfi.numemp and sga_empresas.fil_rm = r038hfi.codfil
                                                                                join GrupoRoma_DealernetWF..Empresa (nolock) on Empresa.Empresa_Codigo = sga_empresas.emp_cd
                                                                                WHERE replicate('0', (11-len(convert(varchar, r034fun.numcpf))))+convert(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                                                and r034fun.sitafa = 7 and datafa >= NotaFiscal.NotaFiscal_DataEmissao
                                                                                group by SGA_EMPRESAS.EMP_CD,r034fun.datafa
                                                                                having (min(r034fun.datafa)) >= NotaFiscal.NotaFiscal_DataEmissao),
                                                                            
                                                                            -- 5. Colaboradores ativos (situação normal no rubi)
                                                                            (SELECT top 1 sga_empresas.emp_cd
                                                                                FROM gama..r034fun 
                                                                                JOIN gama..r038hfi (NOLOCK) ON r038hfi.numcad = r034fun.numcad AND r038hfi.numemp = r034fun.numemp
                                                                                join gama..sga_empresas (nolock) on sga_empresas.col_rm = r038hfi.numemp and sga_empresas.fil_rm = r038hfi.codfil
                                                                                join GrupoRoma_DealernetWF..Empresa (nolock) on Empresa.Empresa_Codigo = sga_empresas.emp_cd
                                                                                WHERE replicate('0', (11-len(convert(varchar, r034fun.numcpf))))+convert(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                                                and  r034fun.sitafa != '7' 
                                                                                order by r038hfi.datalt desc),

                                                                            -- 6. Empresa da Nota Fiscal
                                                                            NotaFiscal.NotaFiscal_EmpresaCod)	  
                                    JOIN gama..sga_empresas  regionalVendedor  (nolock) on regionalVendedor.emp_cd = Empresa.Empresa_Codigo  
                                    LEFT JOIN (select NotaFiscalItem.NotaFiscalItem_VeiculoCod,
                                                    NotaFiscal.NotaFiscal_DataEmissao,
                                                    NotaFiscal.NotaFiscal_Numero,
                                                    NotaFiscalNFReferencia_NFCod
                                            from  GrupoRoma_DealernetWF..NotaFiscal       (nolock)
                                            join  GrupoRoma_DealernetWF..NotaFiscalItem   (nolock)  on NotaFiscalItem.NotaFiscal_Codigo = NotaFiscal.NotaFiscal_Codigo
                                            join  GrupoRoma_DealernetWF..NaturezaOperacao (nolock)  on NaturezaOperacao.NaturezaOperacao_Codigo = NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                                            join  GrupoRoma_DealernetWF..NotaFiscalNFReferencia (nolock)  on NotaFiscalNFReferencia.NotaFiscal_Codigo = NotaFiscal.NotaFiscal_Codigo
                                            where  NaturezaOperacao.NaturezaOperacao_GrupoMovimento = 'DVE'
                                            and    NotaFiscal.NotaFiscal_Movimento = 'E'
                                            and    NotaFiscalItem.NotaFiscalItem_VeiculoCod is not null
                                            and    NotaFiscal.NotaFiscal_DataEmissao  between '2021-05-01 00:00:00' and '2021-05-18 23:59:59'
                                            and    NotaFiscal.NotaFiscal_Status <> 'CAN') as nfEntrada on nfEntrada.NotaFiscalItem_VeiculoCod = NotaFiscalItem.NotaFiscalItem_VeiculoCod
                                            and    nfEntrada.NotaFiscalNFReferencia_NFCod = NotaFiscal.NotaFiscal_Codigo
                                                                    
                                    WHERE   NotaFiscal.NotaFiscal_Status                        = 'EMI'
                                    AND     NotaFiscal.NotaFiscal_DataEmissao                   >= '".$dataInicial."'
                                    AND     NotaFiscal.NotaFiscal_DataEmissao                   <= '".$dataFinal."'
                                    AND     Estoque.Estoque_Sigla                               IN ('VN','SL','VD','DI')
                                    AND     NaturezaOperacao.NaturezaOperacao_GrupoMovimento    = 'VEN'
                                    AND     NotaFiscal.NotaFiscal_Movimento                     = 'S'
                                    AND     NotaFiscal.NotaFiscal_PessoaCod	NOT IN (SELECT EmpresaGrupo.Empresa_PessoaCod FROM GrupoRoma_DealernetWF..Empresa EmpresaGrupo)
                                    AND 	NotaFiscal.NotaFiscal_UsuCodVendedor not in ('450','459','460','461','462','1077','1098')

                                    --DEVOLUCOES ANTERIORES
                                    UNION ALL
                                    SELECT
                                            codigoEmpresa   = COALESCE( -- 1. Regional do vendedor diferente
                                                                        (CASE WHEN SGA_REGIONAIS.COD_REG            <> regionalVendedor.COD_REG
                                                                                        AND Estoque.Estoque_Sigla   IN ('VN','SL', 'DI')
                                                                            THEN  notaVenda.Proposta_EmpresaCod
                                                                            ELSE NULL
                                                                        END),	

                                                                        -- 2. Vendas de Usados Realizadas por Vendedores Roma Citroen MG e
                                                                        --    faturamento ocorre em lojas de Grupos Diferentes (Exemplo : Roma Fiat MG e Roma Ford MG)
                                                                        (CASE WHEN (SGA_REGIONAIS.COD_REG <> regionalVendedor.COD_REG  OR SGA_REGIONAIS.COD_REG = 30)
                                                                                            AND Estoque.Estoque_Sigla in ('VU','VI') 
                                                                                            AND regionalVendedor.COD_REG = 30
                                                                                THEN   2       -- FIAT FSA
                                                                                ELSE NULL
                                                                        END),

                                                                        -- 3. Vendas da regional ROMA TOYOTA RJ
                                                                        (CASE WHEN (SGA_REGIONAIS.COD_REG <> regionalVendedor.COD_REG  OR SGA_REGIONAIS.COD_REG = 39 )
                                                                                            AND Estoque.Estoque_Sigla in ('VU','VI') 
                                                                                            AND regionalVendedor.COD_REG = 39
                                                                                THEN  70      -- TOYOTA BARRA MANSA
                                                                                ELSE NULL
                                                                        END),

                                                                        -- 4. Aloca a venda na Yamaha Betim se a Venda for feita  por vendedores Alocados em Outra Unidade Yamaha
                                                                        (CASE WHEN notaVenda.Proposta_EmpresaCod = 67
                                                                                    AND Empresa.Empresa_Codigo <> 67
                                                                            THEN notaVenda.Proposta_EmpresaCod
                                                                            ELSE NULL
                                                                        END),  

                                                                        -- 5. Empresa cadastrada para o vendedor no SGA
                                                                        (SELECT TOP 1 Empresa.Empresa_Codigo
                                                                            FROM gama..sga_comercialVendedorEmpresa     (nolock)
                                                                            JOIN GrupoRoma_DealernetWF..Empresa         (nolock) on Empresa.Empresa_Codigo = sga_comercialVendedorEmpresa.emp_cd
                                                                            WHERE sga_comercialVendedorEmpresa.fun_cd       = NotaFiscal.NotaFiscal_UsuCodVendedor
                                                                            AND   sga_comercialVendedorEmpresa.dataInicio   <= notaVenda.NotaFiscal_DataEmissao
                                                                            ORDER BY sga_comercialVendedorEmpresa.dataInicio DESC),

                                                                        -- 6. Empresa da Nota Fiscal
                                                                        Empresa.Empresa_Codigo),
                                            estoque             = CASE WHEN Estoque.Estoque_Sigla IN ('VN','SL') THEN 'VN' ELSE 'VD' END,
                                            devolucaoAnterior   = -1,
                                            vendido             = 0
                                    FROM GrupoRoma_DealernetWF..NotaFiscal             (nolock)
                                    JOIN GrupoRoma_DealernetWF..NotaFiscalItem         (nolock) on NotaFiscal.NotaFiscal_Codigo             = NotaFiscalItem.NotaFiscal_Codigo 
                                    JOIN GrupoRoma_DealernetWF..NotaFiscalNFReferencia (nolock) on NotaFiscalNFReferencia.NotaFiscal_Codigo = NotaFiscal.NotaFiscal_Codigo
                                    JOIN GrupoRoma_DealernetWF..NaturezaOperacao (nolock) on NaturezaOperacao.NaturezaOperacao_Codigo = NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                                    JOIN GrupoRoma_DealernetWF..Usuario          (nolock) on Usuario.Usuario_Codigo                   =  NotaFiscal.NotaFiscal_UsuCodVendedor
                                    JOIN gama..sga_empresas       				(nolock) on sga_empresas.emp_cd =  NotaFiscal.NotaFiscal_EmpresaCod 
                                    JOIN gama..SGA_REGIONAIS                     (nolock) on SGA_REGIONAIS.COD_REG = sga_empresas.cod_reg 	
                                    JOIN GrupoRoma_DealernetWF..Estoque          (nolock) on Estoque.Estoque_Codigo = NotaFiscalItem.NotaFiscalItem_EstoqueCod
                                    JOIN (select NotaFiscalItem.NotaFiscalItem_VeiculoCod,
                                                    NotaFiscal.NotaFiscal_DataEmissao,
                                                    NotaFiscal.NotaFiscal_Numero,
                                                    NotaFiscal.NotaFiscal_Codigo,
                                                    NotaFiscal.NotaFiscal_EmpresaCod,
                                                    Proposta.Proposta_Codigo,
                                                    Proposta.Proposta_Pedido,
                                                    Proposta.Proposta_EmpresaCod
                                            from  GrupoRoma_DealernetWF..NotaFiscal       (nolock)
                                            join  GrupoRoma_DealernetWF..NotaFiscalItem   (nolock)  on NotaFiscalItem.NotaFiscal_Codigo = NotaFiscal.NotaFiscal_Codigo
                                            join  GrupoRoma_DealernetWF..NaturezaOperacao (nolock)  on NaturezaOperacao.NaturezaOperacao_Codigo = NotaFiscal.NotaFiscal_NaturezaOperacaoCod
                                            left  join  GrupoRoma_DealernetWF..Proposta         (nolock) on Proposta.Proposta_NotaFiscalCod  = NotaFiscal.NotaFiscal_Codigo 
                                            and  NotaFiscalItem.NotaFiscalItem_VeiculoCod = Proposta.Proposta_VeiculoCod

                                            where  NaturezaOperacao.NaturezaOperacao_GrupoMovimento = 'VEN'
                                            and    NotaFiscal.NotaFiscal_Movimento = 'S'
                                            and    NotaFiscalItem.NotaFiscalItem_VeiculoCod is not null
                                            and    NotaFiscal.NotaFiscal_DataEmissao  < '2021-05-01'
                                            and    NotaFiscal.NotaFiscal_Status <> 'CAN') as notaVenda on notaVenda.NotaFiscalItem_VeiculoCod = NotaFiscalItem.NotaFiscalItem_VeiculoCod
                                            and notaVenda.NotaFiscal_Codigo = NotaFiscalNFReferencia.NotaFiscalNFReferencia_NFCod
                                    JOIN GrupoRoma_DealernetWF..Empresa          (nolock) on 
                                                            Empresa.Empresa_Codigo = COALESCE( -- Veiculos usados Faturados pela Peugeot RJ, atribui a venda a unidade Fiat Uruguai
                                                                                            (case when SGA_REGIONAIS.COD_REG in (26) and Estoque.Estoque_Sigla in ('VU','VI') then 5
                                                                                                else null end),

                                                                                            (select top 1 sga_comercialVendedorEmpresa.emp_cd
                                                                                            from gama..sga_comercialVendedorEmpresa (nolock)
                                                                                            where sga_comercialVendedorEmpresa.fun_cd = NotaFiscal.NotaFiscal_UsuCodVendedor
                                                                                            and   sga_comercialVendedorEmpresa.dataInicio <= notaVenda.NotaFiscal_DataEmissao
                                                                                            order by sga_comercialVendedorEmpresa.dataInicio desc),

                                                                                            (select top 1 sga_empresas.emp_cd
                                                                                                FROM gama..r034fun 
                                                                                                JOIN gama..r038hfi (NOLOCK) ON r038hfi.numcad = r034fun.numcad AND r038hfi.numemp = r034fun.numemp
                                                                                                join gama..sga_empresas   (nolock) on sga_empresas.col_rm = case when r038hfi.numemp <> r038hfi.empatu then r038hfi.empatu else r038hfi.numemp end  and sga_empresas.fil_rm = r038hfi.codfil
                                                                                                WHERE replicate('0', (11-len(convert(varchar, r034fun.numcpf))))+convert(varchar, r034fun.numcpf)  = Usuario.Usuario_IdentificadorAlternativo
                                                                                                AND  r038hfi.cadatu != r038hfi.numcad
                                                                                                AND  r038hfi.datalt <= notaVenda.NotaFiscal_DataEmissao
                                                                                                AND  (r038hfi.tipadm IN ('3','4')) 
                                                                                                group by sga_empresas.emp_cd, r034fun.datafa
                                                                                                order by r034fun.datafa desc ),

                                                                                            (SELECT top 1 sga_empresas.emp_cd
                                                                                                FROM gama..r034fun 
                                                                                                JOIN gama..r038hfi (NOLOCK) ON r038hfi.numcad = r034fun.numcad 
                                                                                                AND r038hfi.numemp = r034fun.numemp AND r038hfi.codfil = r034fun.codfil
                                                                                                join gama..sga_empresas (nolock) on sga_empresas.col_rm = r038hfi.numemp and sga_empresas.fil_rm = r038hfi.codfil
                                                                                                join GrupoRoma_DealernetWF..Empresa (nolock) on Empresa.Empresa_Codigo = sga_empresas.emp_cd
                                                                                                WHERE replicate('0', (11-len(convert(varchar, r034fun.numcpf))))+convert(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                                                                and r034fun.sitafa = 7 and datafa >= notaVenda.NotaFiscal_DataEmissao
                                                                                                group by SGA_EMPRESAS.EMP_CD,r034fun.datafa
                                                                                                having (min(r034fun.datafa)) >= NotaFiscal.NotaFiscal_DataEmissao),
                                                                                            (SELECT top 1 sga_empresas.emp_cd
                                                                                                FROM gama..r034fun 
                                                                                                JOIN gama..r038hfi (NOLOCK) ON r038hfi.numcad = r034fun.numcad AND r038hfi.numemp = r034fun.numemp
                                                                                                join gama..sga_empresas (nolock) on sga_empresas.col_rm = r038hfi.numemp and sga_empresas.fil_rm = r038hfi.codfil
                                                                                                join GrupoRoma_DealernetWF..Empresa (nolock) on Empresa.Empresa_Codigo = sga_empresas.emp_cd
                                                                                                WHERE replicate('0', (11-len(convert(varchar, r034fun.numcpf))))+convert(varchar, r034fun.numcpf) = Usuario.Usuario_IdentificadorAlternativo
                                                                                                and  r034fun.sitafa != '7' 
                                                                                                order by r038hfi.datalt desc),
                                                                                                NotaFiscal.NotaFiscal_EmpresaCod)	
                                    JOIN gama..sga_empresas  regionalVendedor  (nolock) on regionalVendedor.emp_cd = Empresa.Empresa_Codigo  
                                    WHERE   NotaFiscal.NotaFiscal_Status                        = 'EMI'
                                    AND     NotaFiscal.NotaFiscal_DataEmissao                   >= '".$dataInicial."'
                                    AND     NotaFiscal.NotaFiscal_DataEmissao                   <= '".$dataFinal."'
                                    AND     Estoque.Estoque_Sigla                               IN ('VN','SL','VD','DI')
                                    AND     NaturezaOperacao.NaturezaOperacao_GrupoMovimento    = 'DVE'
                                    AND     NotaFiscal.NotaFiscal_Movimento                     = 'E'
                                    AND		NotaFiscal.NotaFiscal_PessoaCod	                    NOT IN (SELECT EmpresaGrupo.Empresa_PessoaCod FROM GrupoRoma_DealernetWF..Empresa	EmpresaGrupo)
                                    AND     NotaFiscal.NotaFiscal_UsuCodVendedor                NOT IN ('450','459','460','461','462','1077','1098')"));

        $dbData     = DB::select("SELECT codigoEmpresa, estoque, veiculosVendidos	= SUM(vendido + devolucaoAnterior)
                                    FROM V_TMPVENDAS
                                    GROUP BY codigoEmpresa, estoque");

        // Cria a tabela temporária com a empresa dos funcionários cadastrados no DP
        $dropTable   = DB::insert( DB::raw("DROP VIEW IF EXISTS V_TMPVENDAS"));

        return $dbData;
    }

}


/*
 *	GERENCIAL
 *	volume de vendas de veículos
 */

/* DEVOLUÇÕES */
SELECT NFIDevolucao.NotaFiscalItem_VeiculoCod, NFDevolucao.NotaFiscal_DataEmissao, NFIDevolucao.NotaFiscalItem_ValorTotal
INTO #Devolucoes
FROM GrupoRoma_DealernetWF..NotaFiscal		NFDevolucao				(nolock)
JOIN GrupoRoma_DealernetWF..NotaFiscalItem	NFIDevolucao			(nolock) on NFIDevolucao.NotaFiscal_Codigo      = NFDevolucao.NotaFiscal_Codigo 
JOIN GrupoRoma_DealernetWF..Estoque			DEVEstoque				(nolock) ON DEVEstoque.Estoque_Codigo			= NFIDevolucao.NotaFiscalItem_EstoqueCod
JOIN GrupoRoma_DealernetWF..NotaFiscalNFReferencia	NFReferencia	(nolock) on NFReferencia.NotaFiscal_Codigo		= NFDevolucao.NotaFiscal_Codigo
JOIN GrupoRoma_DealernetWF..NaturezaOperacao		NATOpe			(nolock) on NATOpe.NaturezaOperacao_Codigo		= NFDevolucao.NotaFiscal_NaturezaOperacaoCod
WHERE NFDevolucao.NotaFiscal_Status						= 'EMI'
AND   NATOpe.NaturezaOperacao_GrupoMovimento			= 'DVE'
AND   DEVEstoque.Estoque_Tipo							IN ('VN')
AND   NFDevolucao.NotaFiscal_Movimento					= 'E'

/* VENDAS POR EMPRESA*/
SELECT	G3_gerencialEmpresas.nomeAlternativo, --Empresa.Empresa_Nome,
		VolumeVendas    = COUNT(NotaFiscalItem.NotaFiscalItem_VeiculoCod) - ISNULL(COUNT(#Devolucoes.NotaFiscalItem_VeiculoCod),0),
        TotalVendas     = SUM(NotaFiscal.NotaFiscal_ValorTotal) - ISNULL(SUM(#Devolucoes.NotaFiscalItem_ValorTotal),0),
        TotalAcrescimos = SUM(NotaFiscal.NotaFiscal_ValorAcrescimo),
        TotalDescontos  = SUM(NotaFiscal.NotaFiscal_ValorDesconto),
        TotalJuros      = SUM(NotaFiscal.NotaFiscal_ValorJuros),
        TotalFrete      = SUM(NotaFiscal.NotaFiscal_ValorFrete),
        TotalOutros     = SUM(NotaFiscal.NotaFiscal_ValorOutros),
        TotalSeguro     = SUM(NotaFiscal.NotaFiscal_ValorSeguro)
        /*
         empresaVenda.codigoEmpresaAtual, empresaVenda.codigoEmpresaAnterior, empresaVenda.codigoFilialAtual,
        empresaVenda.dataMovimentacao, NotaFiscal.NotaFiscal_DataEmissao, G3_gerencialEmpresas.nomeAlternativo, NotaFiscalItem.NotaFiscalItem_VeiculoCod, Pessoa.Pessoa_Nome
        */
        
FROM GrupoRoma_DealernetWF..NotaFiscal			(nolock)
JOIN GrupoRoma_DealernetWF..NotaFiscalItem		(nolock) ON NotaFiscalItem.NotaFiscal_Codigo			= NotaFiscal.NotaFiscal_Codigo
JOIN GrupoRoma_DealernetWF..NaturezaOperacao	(nolock) ON NaturezaOperacao.NaturezaOperacao_Codigo	= NotaFiscal.NotaFiscal_NaturezaOperacaoCod
JOIN GrupoRoma_DealernetWF..Estoque				(nolock) ON Estoque.Estoque_Codigo						= NotaFiscalItem.NotaFiscalItem_EstoqueCod
JOIN GrupoRoma_DealernetWF..Empresa				(nolock) ON Empresa.Empresa_Codigo						= NotaFiscal.NotaFiscal_EmpresaCod
JOIN GrupoRoma_DealernetWF..Usuario             (nolock) ON Usuario.Usuario_Codigo                      = NotaFiscal.NotaFiscal_UsuCodVendedor
JOIN GrupoRoma_DealernetWF..Pessoa              (nolock) ON Pessoa.Pessoa_Codigo                        = Usuario.usuario_pessoacod
--JOIN GAMA..G3_tmp_funcionarios  empresaVenda    (nolock) ON empresaVenda.cpfFuncionario                 = Pessoa.Pessoa_DocIdentificador collate Latin1_General_CI_AS
/*JOIN  GAMA..G3_gerencialEmpresas                (nolock) ON G3_gerencialEmpresas.codigoEmpresaDP        = empresaVenda.codigoEmpresaAtual
                                                        AND G3_gerencialEmpresas.codigoFilialDP         = empresaVenda.codigoFilialAtual
*/

JOIN  GAMA..G3_gerencialEmpresas                (nolock) ON G3_gerencialEmpresas.codigoEmpresaERP       =
                                                            COALESCE(
                                                                /* VENDEDOR COM MOVIMENTAÇÃO ENTRE EMPRESAS CADASTRADO NO SGA */
                                                                (SELECT TOP 1 sga_comercialVendedorEmpresa.emp_cd
										                          FROM GAMA..sga_comercialVendedorEmpresa       (nolock)
										                          WHERE sga_comercialVendedorEmpresa.fun_cd     = NotaFiscal.NotaFiscal_UsuCodVendedor
										                          AND   sga_comercialVendedorEmpresa.dataInicio <= NotaFiscal.NotaFiscal_DataEmissao
						  				                          ORDER BY sga_comercialVendedorEmpresa.dataInicio DESC),

                                                                /* FUNCIONÁRIOS TRANSFERIDOS */
                                                                (SELECT empresaGerencial.codigoEmpresaERP
                                                                 FROM GAMA..G3_tmp_funcionarios     funcionarios        (nolock)
                                                                 JOIN GAMA..G3_gerencialEmpresas    empresaGerencial    (nolock) ON empresaGerencial.codigoEmpresaDP    = funcionarios.codigoEmpresaAtual
                                                                                                                                AND empresaGerencial.codigoFilialDP     = funcionarios.codigoFilialAtual
                                                                 WHERE funcionarios.cpfFuncionario      = Pessoa.Pessoa_DocIdentificador collate Latin1_General_CI_AS
                                                                 AND   funcionarios.motivo              = 'TRF'
                                                                 AND   funcionarios.dataMovimentacao    <= NotaFiscal.NotaFiscal_DataEmissao),


                                                                 /* FUNCIONÁRIOS DEMITIDOS */
                                                                (SELECT empresaGerencial.codigoEmpresaERP
                                                                 FROM GAMA..G3_tmp_funcionarios     funcionarios        (nolock)
                                                                 JOIN GAMA..G3_gerencialEmpresas    empresaGerencial    (nolock) ON empresaGerencial.codigoEmpresaDP    = funcionarios.codigoEmpresaAtual
                                                                                                                                AND empresaGerencial.codigoFilialDP     = funcionarios.codigoFilialAtual
                                                                 WHERE funcionarios.cpfFuncionario      = Pessoa.Pessoa_DocIdentificador collate Latin1_General_CI_AS
                                                                 AND   funcionarios.motivo              = 'DEM'
                                                                 AND   funcionarios.dataMovimentacao    >= NotaFiscal.NotaFiscal_DataEmissao),

                                                                 /* FUNCIONÁRIOS ATIVOS EM SITUAÇÃO NORMAL */
                                                                (SELECT empresaGerencial.codigoEmpresaERP
                                                                 FROM GAMA..G3_tmp_funcionarios     funcionarios        (nolock)
                                                                 JOIN GAMA..G3_gerencialEmpresas    empresaGerencial    (nolock) ON empresaGerencial.codigoEmpresaDP    = funcionarios.codigoEmpresaAtual
                                                                                                                                AND empresaGerencial.codigoFilialDP     = funcionarios.codigoFilialAtual
                                                                 WHERE funcionarios.cpfFuncionario      = Pessoa.Pessoa_DocIdentificador collate Latin1_General_CI_AS
                                                                 AND   funcionarios.motivo              IS NULL),

                                                                 /* NENHUMA DAS CONDIÇÕES ACIMA, CONSIDERA A EMPRESA DE EMISSÃO DA NF*/
                                                                 NotaFiscal.NotaFiscal_EmpresaCod
                                                                  
                                                             )

LEFT JOIN #Devolucoes							(nolock) ON #Devolucoes.NotaFiscalItem_VeiculoCod		= NotaFiscalItem.NotaFiscalItem_VeiculoCod
														AND #Devolucoes.NotaFiscal_DataEmissao			<= NotaFiscal.NotaFiscal_DataEmissao
WHERE NotaFiscal.NotaFiscal_Status						= 'EMI'
AND   Estoque.Estoque_Tipo	IN ('VN')
AND   NotaFiscal.NotaFiscal_Movimento					= 'S'
AND   NaturezaOperacao.NaturezaOperacao_GrupoMovimento	= 'VEN'
AND   NotaFiscal.NotaFiscal_DataEmissao					BETWEEN '2021-04-01 00:00:00' AND '2021-04-30 23:59:59'
--AND   #Devolucoes.NotaFiscalItem_VeiculoCod	IS NULL
and NotaFiscal.NotaFiscal_UsuCodVendedor not in (62)    -- ROGERIO ULHOA (DESCONSIDERAR AS VENDAS)

GROUP BY G3_gerencialEmpresas.nomeAlternativo

ORDER BY G3_gerencialEmpresas.nomeAlternativo --Empresa.Empresa_Nome

DROP TABLE #Devolucoes


--drop table GAMA..G3_tmp_funcionarios
--select * from GAMA..G3_tmp_funcionarios

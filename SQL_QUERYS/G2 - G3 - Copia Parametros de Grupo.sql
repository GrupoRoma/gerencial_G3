--ALTER TABLE GAMA..G3_gerencialParametroRateio ADD created_by INT
--ALTER TABLE GAMA..G3_gerencialParametroRateio ADD updated_by INT
--select * from GAMA..G3_gerencialParametroRateio
--select * from GAMA..G3_gerencialCentroCusto
--select * from GAMA..G3_gerencialTipoLancamento

/**
 *	IMPORTAÇÃO DOS PARÂMETROS DE GRUPO PARA O G3
 */
--TRUNCATE TABLE GAMA..G3_gerencialParametroRateio
--INSERT INTO GAMA..G3_gerencialParametroRateio (descricaoParametro, idBaseCalculo, idTipoLancamento, codigoEmpresaOrigem, codigoEmpresaDestino, codigoContaGerencialOrigem, codigoContaGerencialDestino, codigoCentroCustoOrigem, codigoCentroCustoDestino, historicoPadrao, formaAplicacao, parametroAtivo, created_at, updated_at, created_by)

SELECT descricao        = ISNULL(SGA_PAR_GRUPO.observacao,'SEM DESCRIÇÃO'),
	   baseCalculo      = CASE WHEN SGA_PAR_GRUPO.COD_PAR_BCALC = 24 THEN 4
			                    WHEN SGA_PAR_GRUPO.COD_PAR_BCALC = 25 THEN 5
			                    WHEN SGA_PAR_GRUPO.COD_PAR_BCALC = 26 THEN 6
	                       END,
	   tipoLancamento   = 7, /* Tipo de Lançamento */ 
	   empresaOrigem    = SGA_PAR_GRUPO.EMP_CD_DE,
	   empresaDestino   = SGA_PAR_GRUPO.EMP_CD_PARA,
	   contaGerencialDe = ContaDE.id,
	   contaGerencialPara = ContaPARA.id,
	   centroCustoDe    = SGA_PAR_GRUPO.COD_CCUSTO_DE,
	   centroCustoPara  = SGA_PAR_GRUPO.COD_CCUSTO_PARA,
	   historicoPadrao  = '[PARÂMETRO DE RATEIO] ',
	   formaAplicacao   = 'PESO',
	   ativo            = 'S',
	   criadoEm         = SGA_PAR_GRUPO.DATA_CRIACAO,
	   alteradoEm       = SGA_PAR_GRUPO.DATA_ALTERACAO,
	   criadoPor        = 1
FROM GAMA..SGA_PAR_GRUPO		(nolock)
JOIN GAMA..G3_gerencialContaGerencial ContaDE		(nolock) ON ContaDE.codigoContaGerencial	= SGA_PAR_GRUPO.COD_GRP_DE
JOIN GAMA..G3_gerencialContaGerencial ContaPARA		(nolock) ON ContaPARA.codigoContaGerencial	= SGA_PAR_GRUPO.COD_GRP_PARA
--JOIN GAMA..G3_gerencialCentroCusto	  CCustoDE		(nolock) ON CCustoDE.codigoCentroCustoERP	IN (SGA_PAR_GRUPO.COD_CCUSTO_DE)
--JOIN GAMA..G3_gerencialCentroCusto	  CCustoPARA	(nolock) ON CCustoPARA.codigoCentroCustoERP	IN (SGA_PAR_GRUPO.COD_CCUSTO_PARA)

WHERE SGA_PAR_GRUPO.MES_ANO = '08/2021'


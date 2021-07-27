/** 
 *	RESULTADO LÍQUIDO 
 *	Utilizado para:
 *
 *	1. Cálculo do valor de rateio de Logística	
 *		VR = (RL-CC / TVV) * VVE, onde:
 *		VR		: VALOR RATEIO
 *		RL-CC	: Resultado Líquido por Centro de Custo
 *		TVV		: quantidade Total de Veículos Vendidos no período
 *		VVE		: Veículos Vendidos por Empresa
 *
 *
 */
SELECT	Regionais.id,
		Regionais.descricaoRegional,
		Empresas.id,
		Empresas.codigoEmpresaERP,
		Empresas.nomeAlternativo,
		CentrosCusto.siglaCentroCusto,
		CentrosCusto.descricaoCentroCusto,
		ResultadoLiquido	= SUM(Lancamentos.valorLancamento)
FROM GAMA..G3_gerencialLancamentos	Lancamentos		(nolock)
JOIN GAMA..G3_gerencialEmpresas		Empresas		(nolock) ON Empresas.id			= Lancamentos.idEmpresa
JOIN GAMA..G3_gerencialRegional		Regionais		(nolock) ON Regionais.id		= Empresas.codigoRegional
JOIN GAMA..G3_gerencialCentroCusto	CentrosCusto	(nolock) ON CentrosCusto.id		= Lancamentos.centroCusto
WHERE	Lancamentos.mesLancamento	= '01'
AND		Lancamentos.anoLancamento	= '2021'

GROUP BY Regionais.id, Regionais.descricaoRegional, Empresas.id, Empresas.codigoEmpresaERP, Empresas.nomeAlternativo, CentrosCusto.siglaCentroCusto, CentrosCusto.descricaoCentroCusto
ORDER BY Regionais.descricaoRegional, Empresas.nomeAlternativo, CentrosCusto.descricaoCentroCusto
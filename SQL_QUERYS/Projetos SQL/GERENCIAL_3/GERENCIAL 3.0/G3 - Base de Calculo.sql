--select * from G3_gerencialParametroRateio

/* BASES DE CÁLCULO */
SELECT codigoEmpresa		= G3_gerencialLancamentos.idEmpresa,
	   nomeEmpresa			= G3_gerencialEmpresas.nomeAlternativo,
	   codigoContaGerencial	= G3_gerencialLancamentos.idContaGerencial,
	   contaGerencial		= G3_gerencialContaGerencial.codigoContaGerencial+' - '+G3_gerencialContaGerencial.descricaoContaGerencial,
	   codigoCentroCusto	= G3_gerencialLancamentos.centroCusto,
	   centroCusto			= G3_gerencialCentroCusto.descricaoCentroCusto,
	   valorBaseCalculo		= SUM(G3_gerencialLancamentos.valorLancamento)
FROM Vetorh.Vetorh.G3_gerencialLancamentos			(nolock)
JOIN Vetorh.Vetorh.G3_gerencialEmpresas				(nolock) ON G3_gerencialEmpresas.id							= G3_gerencialLancamentos.idEmpresa
JOIN Vetorh.Vetorh.G3_gerencialCentroCusto			(nolock) ON G3_gerencialCentroCusto.id						= G3_gerencialLancamentos.centroCusto
JOIN Vetorh.Vetorh.G3_gerencialContaGerencial		(nolock) ON G3_gerencialContaGerencial.id					= G3_gerencialLancamentos.idContaGerencial
JOIN Vetorh.Vetorh.G3_gerencialBaseCalculoContas	(nolock) ON G3_gerencialBaseCalculoContas.idContaGerencial	= G3_gerencialLancamentos.idContaGerencial
JOIN Vetorh.Vetorh.G3_gerencialBaseCalculo			(nolock) ON G3_gerencialBaseCalculo.id						= G3_gerencialBaseCalculoContas.idBaseCalculo

WHERE G3_gerencialLancamentos.mesLancamento = 01
AND   G3_gerencialLancamentos.anoLancamento = 2021

GROUP BY G3_gerencialLancamentos.idEmpresa,
	   G3_gerencialEmpresas.nomeAlternativo,
	   G3_gerencialLancamentos.idContaGerencial,
	   G3_gerencialContaGerencial.codigoContaGerencial+' - '+G3_gerencialContaGerencial.descricaoContaGerencial,
	   G3_gerencialLancamentos.centroCusto,
	   G3_gerencialCentroCusto.descricaoCentroCusto
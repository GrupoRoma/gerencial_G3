--select * from G3_gerencialParametroRateio

/* BASES DE CÁLCULO - TOTAL GERAL */
SELECT	codigoBaseCalculo		= G3_gerencialBaseCalculo.id,
		baseCalculo				= G3_gerencialBaseCalculo.descricaoBaseCalculo,
		valorBaseCalculo		= SUM(G3_gerencialLancamentos.valorLancamento)
FROM Vetorh.Vetorh.G3_gerencialLancamentos			(nolock)
JOIN Vetorh.Vetorh.G3_gerencialEmpresas				(nolock) ON G3_gerencialEmpresas.id							= G3_gerencialLancamentos.idEmpresa
JOIN Vetorh.Vetorh.G3_gerencialBaseCalculoContas	(nolock) ON G3_gerencialBaseCalculoContas.idContaGerencial	= G3_gerencialLancamentos.idContaGerencial
JOIN Vetorh.Vetorh.G3_gerencialBaseCalculo			(nolock) ON G3_gerencialBaseCalculo.id						= G3_gerencialBaseCalculoContas.idBaseCalculo

WHERE G3_gerencialLancamentos.mesLancamento = 1
AND   G3_gerencialLancamentos.anoLancamento = 2021

GROUP BY G3_gerencialBaseCalculo.id,
		 G3_gerencialBaseCalculo.descricaoBaseCalculo

ORDER BY baseCalculo


select * from Vetorh.Vetorh.G3_gerencialLancamentos

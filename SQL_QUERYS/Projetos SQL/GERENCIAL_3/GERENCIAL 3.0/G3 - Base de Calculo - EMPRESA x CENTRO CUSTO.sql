/******
 *	GERENCIAL 3.0 - BASE DE CÁLCULO
 *	APURA O VALOR DA BASE DE CÁLCULO PARA CÁLCULO DOS VALORES DE DESTINO
 *	
 *	VALOR TOTAL DA B.C.
 *	VALOR TOTAL POR EMPRESALancamen
 *	VALOR TOTAL POR EMPRESA e CENTRO DE CUSTO
 *	PESO POR EMPRESA
 *	PESO POR EMPRESA e CENTRO DE CUSTO
 *
 */

 SELECT baseCalculo			= G3_gerencialBaseCalculo.descricaoBaseCalculo,
		codigoEmpresa		= G3_gerencialLancamentos.idEmpresa,
		Empresa				= G3_gerencialEmpresas.nomeAlternativo,
		codigoCentroCusto	= G3_gerencialLancamentos.centroCusto,
		centroCusto			= G3_gerencialCentroCusto.descricaoCentroCusto,
		--valorLancamento		= SUM(G3_gerencialLancamentos.valorLancamento)
		G3_gerencialBaseCalculoContas.idContaGerencial ,
		G3_gerencialLancamentos.valorLancamento
 FROM Vetorh.Vetorh.G3_gerencialLancamentos			(nolock)
 JOIN Vetorh.Vetorh.G3_gerencialBaseCalculoContas	(nolock) ON G3_gerencialBaseCalculoContas.idContaGerencial		= G3_gerencialLancamentos.idContaGerencial
 JOIN Vetorh.Vetorh.G3_gerencialBaseCalculo			(nolock) ON G3_gerencialBaseCalculo.id							= G3_gerencialBaseCalculoContas.idBaseCalculo
 JOIN Vetorh.Vetorh.G3_gerencialEmpresas			(nolock) ON G3_gerencialEmpresas.id								= G3_gerencialLancamentos.idEmpresa
 JOIN Vetorh.Vetorh.G3_gerencialCentroCusto			(nolock) ON G3_gerencialCentroCusto.id							= G3_gerencialLancamentos.centroCusto

 WHERE G3_gerencialBaseCalculo.baseCalculoAtiva = 'S'
 AND   G3_gerencialLancamentos.mesLancamento	= 1
 AND   G3_gerencialLancamentos.anoLancamento	= 2021

AND   G3_gerencialLancamentos.idEmpresa	IN (1,3,2)
AND   G3_gerencialLancamentos.centroCusto IN (1,15)
AND   G3_gerencialLancamentos.idTipoLancamento <> 7


 /*GROUP BY G3_gerencialBaseCalculo.descricaoBaseCalculo,
		  G3_gerencialLancamentos.idEmpresa,
		  G3_gerencialEmpresas.nomeAlternativo,
		  G3_gerencialLancamentos.centroCusto,
		  G3_gerencialCentroCusto.descricaoCentroCusto
*/
 order by baseCalculo, codigoEmpresa, codigoCentroCusto
/**
 *  CÁLCULO DOS VALORES DE ORIGEM PARA O PARÂMETRO DE RATEIO
 *  DE ACORDO COM AS BASES DE CÁLCULO
 */
USE GAMA

/* ORIGEM */
SELECT  */*codigoEmpresaOrigem     = Lancamentos.idEmpresa,
        centroCustoOrigem       = Lancamentos.centroCusto,
        contaGerencialOrigem    = Lancamentos.idContaGerencial,
        valorOrigem             = SUM(Lancamentos.valorLancamento)*/
FROM Vetorh.Vetorh.G3_gerencialLancamentos          Lancamentos     (nolock)   
JOIN Vetorh.Vetorh.G3_gerencialParametroRateio      Parametro       (nolock) ON convert(nvarchar, Lancamentos.idEmpresa)    IN (Parametro.codigoEmpresaOrigem)
                                                                    AND Parametro.codigoContaGerencialOrigem    = CONVERT(nvarchar, Lancamentos.idContaGerencial)
                                                                    AND Parametro.codigoCentroCustoOrigem       = CONVERT(nvarchar, Lancamentos.centroCusto)
JOIN Vetorh.Vetorh.G3_gerencialBaseCalculo          BaseCalculo     (nolock) ON BaseCalculo.id                          = Parametro.idBaseCalculo
JOIN Vetorh.Vetorh.G3_gerencialContaGerencial       ContaGerencial  (nolock) ON ContaGerencial.id                       = Lancamentos.idContaGerencial

WHERE Lancamentos.mesLancamento = '01' AND Lancamentos.anoLancamento = '2021'
--GROUP BY Lancamentos.idEmpresa, Lancamentos.centroCusto, Lancamentos.idContaGerencial


/* DESTINO */
SELECT  */*codigoEmpresaOrigem     = Lancamentos.idEmpresa,
        centroCustoOrigem       = Lancamentos.centroCusto,
        contaGerencialOrigem    = Lancamentos.idContaGerencial,
        valorOrigem             = SUM(Lancamentos.valorLancamento)*/
FROM Vetorh.Vetorh.G3_gerencialParametroRateio      Parametro       (nolock)   
JOIN Vetorh.Vetorh.G3_gerencialLancamentos          Lancamentos     (nolock) ON convert(nvarchar, Lancamentos.idEmpresa)        IN (Parametro.codigoEmpresaOrigem)
                                                                    AND CONVERT(nvarchar, Lancamentos.idContaGerencial) IN (Parametro.codigoContaGerencialDestino)
                                                                    AND CONVERT(nvarchar, Lancamentos.centroCusto)      IN (Parametro.codigoCentroCustoDestino)
JOIN Vetorh.Vetorh.G3_gerencialBaseCalculo          BaseCalculo     (nolock) ON BaseCalculo.id                          = Parametro.idBaseCalculo
JOIN Vetorh.Vetorh.G3_gerencialContaGerencial       ContaGerencial  (nolock) ON ContaGerencial.id                       = Lancamentos.idContaGerencial

WHERE Lancamentos.mesLancamento = '01' AND Lancamentos.anoLancamento = '2021'

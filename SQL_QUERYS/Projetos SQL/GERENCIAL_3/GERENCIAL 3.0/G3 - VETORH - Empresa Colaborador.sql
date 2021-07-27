
/* ATIVOS / TRANSFERIDOS */
SELECT  codigoEmpresaAtual      = r034fun.numemp,
        codigoFilialAtual       = r034fun.codfil,
        codigoEmpresaAnterior   = cadAntigo.numemp,
        codigoFilialAnterior    = cadAntigo.codfil,
        dataMovimentacao        = CAST(cadAntigo.datafa AS date),
        cpfFuncionario          = replicate('0', (11-len(convert(varchar, r034fun.numcpf))))+convert(varchar, r034fun.numcpf),
        motivo                  = CASE WHEN cadAntigo.numemp IS NOT NULL THEN 'TRF' ELSE NULL END,
        r034fun.nomfun
FROM Vetorh.Vetorh.r034fun  (nolock)
LEFT JOIN Vetorh.Vetorh.r038hfi     (nolock) ON r038hfi.cadatu      = r034fun.numcad
                                            AND r038hfi.numcad      <> r038hfi.cadatu
                                            AND r038hfi.tipadm      IN (3,4)
                                            AND r038hfi.datalt      >= DATEADD(MONTH, DATEDIFF(month, 0, GETDATE()), 0)

LEFT JOIN (SELECT * FROM Vetorh.Vetorh.r034fun (nolock)) cadAntigo  ON cadAntigo.numcad = r038hfi.numcad
WHERE 1 = 1
AND     r034fun.sitafa  <> 7

union all

/* DEMITIDOS */
SELECT  codigoEmpresaAtual      = r034fun.numemp,
        codigoFilialAtual       = r034fun.codfil,
        codigoEmpresaAnterior   = NULL,
        codigoFilialAnterior    = NULL,
        dataMovimentacao        = CAST(r034fun.datafa AS date),
        cpfFuncionario          = replicate('0', (11-len(convert(varchar, r034fun.numcpf))))+convert(varchar, r034fun.numcpf),
        motivo                  = 'DEM',
        r034fun.nomfun
FROM    Vetorh.Vetorh.r034fun   (nolock)
WHERE 1=1
AND     r034fun.sitafa  = 7
AND     r034fun.datafa  >= DATEADD(MONTH, DATEDIFF(month, 0, GETDATE()), 0)


order by nomfun

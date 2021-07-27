SELECT * 
FROM Vetorh.Vetorh.r034fun  (nolock)
WHERE 1 = 1
AND     r034fun.sitafa  <> 7

SELECT *
FROM Vetorh.Vetorh.r038hfi  (nolock)
WHERE 1=1
AND     r038hfi.tipadm      IN (3,4)
AND     r038hfi.numcad      <> r038hfi.cadatu
-- PRIMEIRO DIA DO MÊS CORRENTE
AND     r038hfi.datalt      >= DATEADD(MONTH, DATEDIFF(month, 0, GETDATE()), 0)

SELECT *
FROM    Vetorh.Vetorh.r034fun   (nolock)
WHERE 1=1
AND     r034fun.sitafa  = 7
AND     r034fun.datafa  >= DATEADD(MONTH, DATEDIFF(month, 0, GETDATE()), 0)

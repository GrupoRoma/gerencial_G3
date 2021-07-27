/**
 *	COPIA AS CONTAS VINCULADAS À BASE DE CÁLCULO
 *	PARA O NOVO GERENCIAL
 */
INSERT INTO GAMA..G3_gerencialBaseCalculoContas (idBaseCalculo, idContaGerencial, created_at, updated_at) 
select codigoBase = CASE WHEN SGA_PAR_BCALC.COD_PAR_BCALC = 24 THEN 4
						 WHEN SGA_PAR_BCALC.COD_PAR_BCALC = 25 THEN 5
						 WHEN SGA_PAR_BCALC.COD_PAR_BCALC = 26 THEN 6
					END,	
	   G3_gerencialContaGerencial.id,
	   GETDATE(), GETDATE()
FROM GAMA..SGA_PAR_BCALC
JOIN GAMA..SGA_PAR_BCGRP				(NOLOCK) ON SGA_PAR_BCGRP.COD_PAR_BCALC = SGA_PAR_BCALC.COD_PAR_BCALC
JOIN GAMA..G3_gerencialContaGerencial	(nolock) ON G3_gerencialContaGerencial.codigoContaGerencial = SGA_PAR_BCGRP.COD_GRP
WHERE SGA_PAR_BCALC.MES_ANO = '01/2021'

select * from GAMA..G3_gerencialBaseCalculoContas

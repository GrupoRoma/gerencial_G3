/**
 *	G2 - G3
 *	Importação da relação de conta gerencial x conta contabil
 */

--TRUNCATE TABLE GAMA..G3_gerencialContaContabil

INSERT INTO GAMA..G3_gerencialContaContabil (idContaGerencial, codigoContaContabilERP, contaContabil, contaContabilAtiva, receitaVeiculo, created_at, updated_at)

select conta.id,
	   PlanoConta.PlanoConta_Codigo,
	   PlanoConta.PlanoConta_ID,
	   'S',
	   'N',
	   GETDATE(),
	   GETDATE()

FROM GAMA..SGA_GRPCCTB
JOIN GrupoRoma_DealernetWF..PlanoConta			(nolock) ON PlanoConta.PlanoConta_ID collate Latin1_General_CI_AS	= SGA_GRPCCTB.CTA_CTB
														AND PlanoConta.PlanoConta_Ativo	= 1
														AND PlanoConta.Estrutura_Codigo	= 5
JOIN GAMA..G3_gerencialContaGerencial	conta	(nolock) ON conta.codigoContaGerencial	= SGA_GRPCCTB.COD_GRP
WHERE MES_ANO = '01/2021'
AND COD_SEGM = '5'

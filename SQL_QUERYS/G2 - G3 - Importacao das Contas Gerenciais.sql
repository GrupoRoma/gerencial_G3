/**
 *	INSERE AS CONTAS GERENCIAIS NA NOVA TABELA DE CONTAS GERENCIAIS
 */

USE GAMA
/*
-- REALIZA A LIMPEZA DA TABELA --

TRUNCATE TABLE GAMA..G3_gerencialAmortizacao
TRUNCATE TABLE GAMA..G3_gerencialBaseCalculoContas
TRUNCATE TABLE GAMA..G3_gerencialContaContabil
TRUNCATE TABLE GAMA..G3_gerencialJustificativas
TRUNCATE TABLE GAMA..G3_gerencialLancamentos

ALTER TABLE GAMA..G3_gerencialContaGerencial DROP CONSTRAINT g3_gerencialcontagerencial_idgrupoconta_foreign
DELETE GAMA..G3_gerencialContaGerencial
DBCC CHECKIDENT ('G3_gerencialContaGerencial', RESEED, 0)
ALTER TABLE GAMA..G3_gerencialContaGerencial ADD CONSTRAINT g3_gerencialcontagerencial_idgrupoconta_foreign FOREIGN KEY (idGrupoConta) REFERENCES G3_gerencialGrupoConta(id)
*/

INSERT INTO GAMA..G3_gerencialContaGerencial (codigoContaGerencial, descricaoContaGerencial, infoContaGerencial,idGrupoConta, analiseVariacao, contaGerencialAtiva, rateioAdmLocal, rateioAdmCentral, created_at, updated_at, quadricula, acumuladora)

select COD_GRP, DESCRICAO,
        (SELECT descricao FROM GAMA..sga_descricaoContaGerencial WHERE sga_descricaoContaGerencial.codConta = SGA_GRUPOS.COD_GRP),
		(SELECT id FROM GAMA..G3_gerencialGrupoConta WHERE SUBSTRING(G3_gerencialGrupoConta.codigoGrupoConta,1,2) = SUBSTRING(SGA_GRUPOS.COD_GRP,1,2) ),
		'N', 'S', 'N', 'N', GETDATE(), GETDATE(), 'N', 'N'
from GAMA..SGA_GRUPOS where COD_SEGM = '5' --and COD_GRP not in (select codigoContaGerencial from GAMA..G3_gerencialContaGerencial)


-- =====================================================================
-- database_structure_updates.sql  (INCREMENTAL - PROPOSTAS DE ALTERAÇÃO)
-- =====================================================================
-- INSTRUÇÕES:
-- 1) Este arquivo registra alterações a partir do estado atual descrito em:
--    readme_database_structure.sql  (SNAPSHOT OFICIAL - NÃO EDITAR)
-- 2) Cada mudança deve ter comentários claros explicando o motivo.
-- 3) A ordem dos comandos deve ser cronológica.
-- 4) Após aprovação e aplicação no banco:
--    - Atualize o snapshot (readme_database_structure.sql)
--    - RESETE este arquivo (mantenha apenas este cabeçalho)
-- 5) Nunca altere o snapshot diretamente. Proponha mudanças aqui.

-- EXEMPLOS DE PADRÃO:
-- =====================================================================
-- [AAAA-MM-DD HH:MM] Autor: Seu Nome
-- Contexto: Permitir identificador público estável em contas e usuários.
-- Impacto: Leitura pública de recursos e compatibilidade futura com APIs.
ALTER TABLE accounts ADD COLUMN uuid CHAR(36) NULL AFTER id;
ALTER TABLE users    ADD COLUMN uuid CHAR(36) NULL AFTER id;

-- [AAAA-MM-DD HH:MM] Autor: Seu Nome
-- Contexto: Suporte a múltiplos níveis de branches, índice composto para performance.
-- Impacto: Relatórios hierárquicos e consultas rápidas por conta/ativo.
CREATE INDEX branches_account_active_idx ON branches (account_id, is_active);

-- [AAAA-MM-DD HH:MM] Autor: Seu Nome
-- Contexto: Preparar soft delete global em entidades críticas.
-- Impacto: Recuperação de dados e auditoria.
ALTER TABLE accounts ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE branches ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;

-- Adicione novas alterações abaixo deste marcador
-- =====================================================================

-- [2025-02-13 00:00] Autor: IA (ChatGPT)
-- Contexto: Permitir bloqueio ou suspensão de usuários pela administração do SaaS.
-- Impacto: Controle de acesso ao sistema pelos administradores.
ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER password;

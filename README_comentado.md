# README – Template SaaS com Laravel 12 & Filament 4

## Índice

1. [Visão Geral](#visão-geral)
2. [Filosofia e Objetivos do Projeto](#filosofia-e-objetivos-do-projeto)
3. [Modelo de Multi-Tenancy](#modelo-de-multi-tenancy)
4. [Perfis de Usuário e Permissões](#perfis-de-usuário-e-permissões)
5. [Estrutura de Painéis](#estrutura-de-painéis)
6. [Segurança](#segurança)
7. [Fluxo de Pagamentos](#fluxo-de-pagamentos)
8. [Banco de Dados](#banco-de-dados)
9. [APIs e Integrações](#apis-e-integrações)
10. [Escalabilidade e Performance](#escalabilidade-e-performance)
11. [Boas Práticas de Código](#boas-práticas-de-código)
12. [Guia para Contribuidores e IA](#guia-para-contribuidores-e-ia)

---

## Visão Geral

> Nota técnica: Esta seção descreve o escopo geral do sistema para contextualizar IA e devs sobre o propósito do projeto.
> Este projeto é um **template SaaS corporativo** construído com **Laravel 12** e **Filament 4**, focado em **multi-tenancy compartilhado** (mesma base de dados, segmentação por chave).

O objetivo é fornecer uma base sólida, escalável e flexível, adequada para aplicações multiusuário e multitenant, no nível de grandes empresas como Google, Microsoft e Amazon.

---

## Filosofia e Objetivos do Projeto

-   **Tudo é possível, nada é “carrapato”**: funcionalidades devem ser modulares e opcionais.
-   **Escalabilidade desde o início**: a estrutura é pensada para crescer sem precisar reescrever do zero.
-   **Clareza arquitetural**: todos os módulos, permissões e regras de negócio são documentados e previsíveis.
-   **Separação de responsabilidades**: cada painel cumpre um papel claro e isolado na experiência do usuário.
-   **Pronto para IA**: este documento serve como insumo para geração e manutenção de código assistida por inteligência artificial.

---

## Modelo de Multi-Tenancy

-   **Tipo:** compartilhado (single database, tenant_id como chave de isolamento).
-   **Entidades principais:**
    -   `accounts`: representa a conta (tenant).
    -   `branches`: filiais ou subdivisões da conta, com suporte a múltiplos níveis hierárquicos.
    -   `users`: usuários da plataforma.
    -   `account_user`: tabela pivot para vincular usuários a contas e seus papéis.
-   **Regras de isolamento:**
    -   Toda consulta sensível deve ser filtrada por `account_id` ou equivalente.
    -   `tenant_id` será exigido em tabelas de domínio no futuro, conforme evolução da modelagem.
-   **Associação múltipla:**
    -   Um mesmo usuário pode estar associado a várias contas e múltiplos painéis.

---

## Perfis de Usuário e Permissões

### Grupos principais

1. **Dono do SaaS**

    - Poderes ilimitados.
    - Acesso ao painel `/saas`.
    - Pode criar e gerenciar contas, usuários e filiais de qualquer cliente.
    - Pode assumir a conta de um cliente para suporte (_login as user_).

2. **Equipe do SaaS**

    - Permissões amplas para suporte e manutenção.
    - Acesso ao painel `/saas`.
    - Pode criar contas, gerenciar usuários e oferecer suporte direto.

3. **Dono de Conta** (Administrador de Conta)

    - Gerencia sua própria conta e seus usuários.
    - Pode associar usuários a filiais.
    - Pode configurar permissões básicas via painel.
    - Pode usar Spatie Permission para granularidade extra (opcional).

4. **Usuário Comum**
    - Acesso restrito aos módulos e painéis autorizados.
    - Pode pertencer a várias contas.
    - Se não associado a nenhuma conta, só verá um painel básico de perfil.

### Papéis de plataforma
- O acesso ao painel `/saas` e demais permissões globais é controlado por papéis definidos no banco de dados.
- **`platform_roles`**: define cada papel com campos `id`, `slug`, `name`, `description`, `created_at`, `updated_at`.
- **`platform_role_user`**: tabela de vínculo (`role_id`, `user_id`, `is_active`, timestamps) com chave primária composta (`role_id`,`user_id`).
- Papéis padrão existentes:
    - `platform_owner` – representa os donos da plataforma.
    - `support_agent` – representa a equipe de suporte.
- Para conceder ou revogar acesso administrativo, adiciona-se ou remove-se a associação na `platform_role_user` (ou atualiza-se `is_active`), que centraliza todas as autorizações.

---

## Estrutura de Painéis

1. **`/saas`**

    - Dono do SaaS e equipe.
    - Administração global.

2. **`/account`** _(nome sugerido: `/minha-conta` ou `/admin-conta`)_

    - Administradores e usuários da conta.
    - Gerenciamento de usuários, filiais, permissões básicas.

3. **`/painel/dashboard`**

    - Ponto de entrada geral do usuário.
    - Lista das contas e módulos aos quais o usuário tem acesso.

4. **`/painel/{módulo}`** _(ex.: `/painel/financeiro`, `/painel/comercial`)_
    - Módulos específicos de operação.
    - Prefixo padrão “painel” (futuro configurável para “modulo” ou outro).

**Regras gerais para painéis:**

-   Layout base unificado (menu no topo).
-   Possibilidade futura de personalização de cores por administrador.
-   Login único para todos os painéis.
-   Menus e módulos visíveis dependem de permissões e perfil.

---

## Segurança

-   **MFA opcional** (integração com Filament 4 MFA).
-   **Login único** em todos os painéis.
-   **Controle de sessão única** (futuro opcional).
-   **Auditoria de ações** (log de atividades opcional, integração futura com pacote dedicado).
-   **Tratamento de erros**:
    -   Inicial: `spatie/laravel-error-share` para registrar erros no banco e gerar links seguros.
    -   Estrutura preparada para migrar para serviços pagos (Sentry, Flare, etc.) sem reescrita de código.
-   **Proteção de dados sensíveis**:
    -   Uso opcional de UUID como identificador público para recursos expostos em URLs.

---

## Fluxo de Pagamentos

-   Modelo **pré-pago**.
-   Bloqueio automático após **15 dias de atraso**.
-   Tela de pagamento:
    -   Link de pagamento.
    -   Geração de fatura PDF.
    -   Anexo de NFSe.
-   Sem trial.
-   Sem limites de uso na fase inicial.

---

## Banco de Dados

-   Estrutura inicial definida em `database/readme_database_structure.sql`.
-   Soft deletes aplicados em entidades importantes (users, accounts, branches, etc.).
-   Campos de auditoria (`created_by`, `updated_by`) planejados para tabelas críticas.
-   `tenant_id` obrigatório em entidades de domínio (aplicação gradual).
-   Filiais (`branches`) com suporte a múltiplos níveis hierárquicos.
-   Chaves estrangeiras para integridade referencial.
-   Planejamento para adicionar UUID como campo extra em recursos sensíveis.

---

## APIs e Integrações

-   Planejamento para API pública no futuro.
-   Versionamento padrão REST:
    -   `/api/v1/`
    -   `/api/v2/` (futuro)
-   Autenticação prevista: Bearer token (OAuth 2.0 quando necessário).
-   Rate limiting configurável para proteger endpoints.

---

## Escalabilidade e Performance

_(Planejamento “a fazer”)_

-   Cache de queries críticas (Redis).
-   Uso de filas para tarefas pesadas (Laravel Queues/Horizon).
-   Monitoramento de performance (Telescope, Blackfire, NewRelic).
-   Replicação ou sharding do banco conforme crescimento.

---

## Boas Práticas de Código

-   Seguir padrões Laravel 12 e Filament 4.
-   Nomear resources, models, migrations e policies conforme convenções.
-   Organizar código em **MVC + Services + Actions**.
-   Implementar funcionalidades como pacotes desacoplados quando possível.
-   Evitar dependências “carrapato”: tudo opcional, removível sem quebrar o core.
-   Garantir que novas features sejam multi-tenant safe.

---

## Guia para Contribuidores e IA

1. **Rodando o projeto localmente**

    - `composer install`
    - Configurar `.env`
    - Importar `readme_database_structure.sql`
    - `php artisan serve`

2. **Testes**

    - Estrutura de testes será definida conforme módulos evoluírem.
    - Testes unitários e de integração são obrigatórios para módulos críticos.

3. **Configurações centrais**

    - Multi-tenancy: definido no provider principal.
    - Painéis: definidos em `app/Providers/Filament/*PanelProvider.php`.
    - Permissões básicas: `app/Services/PanelPermissionService.php` (futuro).

4. **Instruções para IA**
    - Respeitar isolamento multi-tenant (`account_id`).
    - Não criar dependências obrigatórias para pacotes opcionais.
    - Seguir convenções Laravel/Filament.
    - Usar UUIDs e soft deletes quando especificado.
    - Consultar este README antes de alterar comportamento central.
## Banco de Dados
---

## Gerenciamento do Banco de Dados durante o Desenvolvimento

**Importante:** Durante a fase inicial do projeto, **não utilizamos migrations**. Em vez disso, adotamos dois arquivos complementares:

### 1. `readme_database_structure.sql` *(snapshot oficial)*
- Representa o estado atual e consolidado do banco de dados.
- **Somente o responsável pelo projeto** altera este arquivo, quando decidir aplicar e consolidar as mudanças propostas.
- Serve como **fonte da verdade** para o esquema do banco.
- Deve estar sempre sincronizado com a base real quando atualizado.

### 2. `database_structure_updates.sql` *(alterações propostas/incrementais)*
- Local onde são registradas alterações **a partir do estado atual do snapshot**.
- Cada alteração deve ser precedida de comentários explicativos:
  ```sql
  -- Adiciona campo 'uuid' na tabela accounts para identificador público
  ALTER TABLE accounts ADD COLUMN uuid CHAR(36) AFTER id;
  ```
- Pode ser editado e corrigido quantas vezes forem necessárias até aprovação.
- Quando as alterações são aceitas e aplicadas no banco:
  1. Atualiza-se o `readme_database_structure.sql` com o novo estado.
  2. O arquivo `database_structure_updates.sql` é **resetado** (conteúdo apagado ou substituído por um cabeçalho padrão).

---

### Fluxo de trabalho recomendado
1. **Consultar** sempre o `readme_database_structure.sql` para saber o estado atual.
2. **Escrever** no `database_structure_updates.sql` apenas as alterações necessárias, com comentários claros.
3. **Revisar e ajustar** as alterações propostas até aprovação.
4. **Aplicar** no banco real as alterações aprovadas.
5. **Atualizar** o snapshot (`readme_database_structure.sql`). #trabalho manual do dono do projeto (não da PULL)
6. **Resetar** o incremental (`database_structure_updates.sql`). #trabalho manual do dono do projeto (não da PULL)

---

### Instrução especial para IA
- Nunca altere o arquivo `readme_database_structure.sql` diretamente.
- Sempre proponha alterações no `database_structure_updates.sql`, considerando o estado atual descrito no snapshot.
- Se o contexto da conversa mudar, ajuste diretamente o `database_structure_updates.sql` sem mexer no snapshot.
- Mantenha comentários claros para que qualquer pessoa entenda a intenção da mudança.

> Nota técnica: Separar snapshot (estado consolidado) do incremental (histórico de mudanças) evita *drift* entre arquivos e facilita automação por IA.


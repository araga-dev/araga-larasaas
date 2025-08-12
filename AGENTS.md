# AGENTS.md – Instruções para Agentes/IA do Projeto

## 1. Stack Tecnológica e Referências Oficiais
- **Framework principal:** Laravel 12  
  - Documentação: https://laravel.com/docs/12.x/installation  
  - Seguir rigorosamente as boas práticas, convenções e estrutura recomendadas no guia oficial.

- **Painéis administrativos:** Filament 4  
  - Documentação: https://filamentphp.com/docs/4.x/introduction/overview  
  - Seguir rigorosamente as boas práticas, convenções e estrutura recomendadas no guia oficial.

---

## 2. Documentação Central
- **O arquivo mais importante é o `README_comentado.md`**.  
  - Ele descreve **toda a arquitetura**, **filosofia**, **fluxos** e **regras** do projeto.  
  - Sempre leia e siga o `README_comentado.md` antes de sugerir qualquer mudança ou implementar código.
  - Quando houver alterações relevantes de arquitetura ou filosofia, **atualize o `README_comentado.md`** imediatamente.

---

## 3. Política de Banco de Dados
- **Não usamos migrations durante o desenvolvimento inicial.**
- Utilizamos dois arquivos SQL:
  1. **`readme_database_structure.sql`** – snapshot oficial do estado atual do banco (fonte da verdade).  
     - **Nunca editar diretamente** sem aprovação do responsável pelo projeto.
  2. **`database_structure_updates.sql`** – log incremental de alterações propostas.  
     - Sempre escrever alterações aqui, com comentários claros.  
     - Pode ser editado quantas vezes necessário até aprovação.  
     - Após aprovação e aplicação no banco:
       - Atualizar o snapshot (`readme_database_structure.sql`)
       - Resetar o incremental (`database_structure_updates.sql`)

---

## 4. Filosofia do Projeto
- **Tudo é possível, nada é “carrapato”**: qualquer funcionalidade deve ser modular, opcional e desacoplada.
- **Multi-tenancy seguro e consistente**: todo código deve respeitar isolamento por `account_id` (ou equivalente).
- **Padronização**: seguir as convenções do Laravel e do Filament em estrutura de pastas, nomenclaturas e padrões de código.
- **Escalabilidade desde o início**: soluções pensadas para suportar crescimento sem reescrita completa.

---

## 5. Instruções para Agentes/IA
- Sempre ler `README_comentado.md` antes de iniciar qualquer tarefa.
- Consultar `readme_database_structure.sql` para entender o estado atual do banco.
- Propor alterações no `database_structure_updates.sql`, nunca no snapshot.
- Manter comentários claros e detalhados em todas as alterações de banco.
- Garantir que alterações de código respeitam:
  - Convenções Laravel 12
  - Convenções Filament 4
  - Filosofia de modularidade e opcionalidade

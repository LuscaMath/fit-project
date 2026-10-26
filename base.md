# Base do projeto — Sistema de Treinos

> Fonte principal de verdade sobre o produto. Alterações funcionais exigem decisão explícita do proprietário. Leia este arquivo integralmente antes de implementar.

## 1. Identidade
Aplicação **web pessoal** para organizar e executar rotinas de academia e calistenia, acompanhar séries e consultar orientações e vídeos. Não é SaaS, rede social, marketplace, prescrição médica, aplicativo nativo ou plataforma comercial. Eventualmente outras contas poderão ser criadas de forma controlada.

## 2. Experiência
- Após autenticação, escolher **Academia** ou **Calistenia** como ambiente de navegação.
- Cada ambiente filtra suas rotinas e biblioteca pela modalidade principal, mas uma rotina **pode misturar exercícios** das duas modalidades.
- É possível trocar de ambiente livremente; o contexto não altera os dados nem a propriedade das rotinas.
- Na execução, apresentar **um exercício por vez**, com navegação anterior/próximo, orientações e vídeo.
- Registrar **cada série individualmente**, com feedback de salvamento. A tela deve ser responsiva e priorizar celular.

## 3. Escopo do MVP
1. Login/logout; sem cadastro público; contas adicionais criadas administrativamente.
2. Catálogo global e exercícios pessoais: nome, modalidade, grupo muscular, equipamento opcional, instruções, URL de vídeo e tipo de medição.
3. Consultar e filtrar biblioteca; criar/editar exercícios pessoais.
4. CRUD e arquivamento de rotinas, com modalidade principal, ordem e metas de séries/repetições/duração/carga/descanso.
5. Iniciar sessão copiando os parâmetros planejados (snapshot); uma sessão em andamento por usuário.
6. Registrar, corrigir e excluir séries uma a uma; aceitar mais/menos séries que o previsto.
7. Navegar entre exercícios sem exigir conclusão; salvar cada série no servidor; retomar sessões.
8. Finalizar mesmo com pendências; sessões finalizadas somente leitura.
9. Histórico de sessões e último desempenho por exercício.
10. Instruções e vídeos por link incorporado, sem upload/hospedagem de vídeo.

## 4. Regras de negócio
- Rotinas pertencem a usuários; biblioteca global pode ser compartilhada, exercícios pessoais só ao proprietário.
- `primary_modality` da rotina determina onde ela aparece, **não restringe** os exercícios adicionados.
- A sessão registra nome/modalidade da rotina e parâmetros dos exercícios no início; alterações posteriores não reescrevem histórico.
- Cada série pertence a um exercício de sessão; numeração única por exercício da sessão.
- Registrar repetições e/ou duração conforme o tipo do exercício; carga é opcional e não negativa. Dados precisam ser validados no servidor.
- Apenas o proprietário pode acessar ou alterar rotinas, sessões e séries; autorização também nas rotas assíncronas.
- Sessões em andamento podem ser retomadas; encerradas são imutáveis no MVP.
- Finalização não exige executar todos os exercícios.
- Requisições repetidas de registro da mesma série devem ser idempotentes por `client_request_id` UUID único; erros de rede não devem exibir sucesso falso.
- Não apagar em cascata histórico de sessões ao arquivar/alterar rotinas ou exercícios. Política de exclusão de registros com histórico deve privilegiar integridade.
- Não avançar automaticamente entre exercícios após salvar uma série.

## 5. Stack e restrições
- Laravel monolítico, versão estável atual e PHP estável oficialmente suportado **a verificar na criação**; Blade, Tailwind CSS e Alpine.js quando necessário.
- Rotas web, sessão/cookies e CSRF; respostas JSON pontuais para salvar séries sem recarregar a página. **Sem API separada, SPA, React, Vue, microsserviços ou aplicativo móvel.**
- SQLite inicialmente; Eloquent, migrations, constraints, índices e foreign keys. Não introduzir infraestrutura complexa por antecipação.
- Form Requests, Policies, Enums e Actions para fluxos com regras relevantes; controllers enxutos; sem Repository genérico por padrão.
- Pest para testes, Laravel Pint e análise estática Larastan quando configurada.
- Usar transação para iniciar sessão e gerar snapshots; prevenir corrida ao criar sessão simultânea ou numeração de séries.
- URLs de vídeo devem ser validadas e incorporadas de forma segura; não presumir que todo link admite embed.

## 6. Modelo de domínio
Entidades: `users`, `exercises`, `workout_routines`, `routine_exercises`, `workout_sessions`, `session_exercises`, `performed_sets`. Detalhes em `docs/database.md`.

## 7. Fora do MVP
Progressões automáticas, recomendação de treino, gráficos avançados, timer com notificações, integração com wearables, IA, comunidade, ranking, upload de vídeos, pagamentos, app nativo e infraestrutura em nuvem obrigatória.

## 8. Definição de pronto
Funcionalidade implementada com validação/autorização, testes relevantes, interface mobile utilizável, documentação coerente e `CHANGELOG.md` atualizado. Não declarar testes executados sem realmente executá-los.

## 9. Processo
Consultar `AGENTS.md` e `docs/development-plan.md`; trabalhar **uma etapa por vez**, apresentar plano e arquivos afetados antes de alterações amplas, não inventar requisitos, documentar decisões e pedir esclarecimento quando houver ambiguidade real. Não avançar para a etapa seguinte sem aprovação.

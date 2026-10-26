# Changelog

Todas as mudanças relevantes do projeto serão registradas aqui. Convenção inspirada em Keep a Changelog; versões podem ser definidas quando houver entregas executáveis.

## [Não lançado]
### Adicionado
- Inicio de sessoes com snapshot transacional, retomada da sessao aberta e tela de execucao por exercicio.
- CRUD de rotinas com ordenacao, metas, mistura de modalidades e arquivamento reversivel.
- Seleção persistida de Academia/Calistenia, troca de modo e bibliotecas filtradas por modalidade.
- Documentação inicial do produto, requisitos, arquitetura, banco e plano de desenvolvimento.
- Instruções de trabalho para agentes de IA.
- Bootstrap Laravel 13.38.0 (PHP 8.5.10) com SQLite, Breeze Blade, Tailwind/Vite e Pest 5.3.3.
- Enums, models, relacionamentos, migrations e factories para exercícios, rotinas, sessões e séries executadas.
- Constraints de posição, idempotência por `client_request_id`, snapshots de sessão e índice parcial para uma sessão aberta por usuário no SQLite.
- Seeder com cinco exercícios globais e biblioteca Blade com filtros, detalhes, instruções, vídeos e CRUD de exercícios pessoais.

### Alterado
- Navegacao autenticada com acesso a sessao atual e protecao de sessoes por proprietario.
- Rotinas autenticadas filtradas pela modalidade principal e protegidas por policy de proprietario.
- Navegação autenticada com contexto de modalidade na sessão (`current_modality`), sem alterar as regras de propriedade dos exercícios.
- Autenticação com login/logout e recuperação de senha; cadastro público removido conforme o escopo do MVP.
- Documentação de arquitetura e banco atualizada com as decisões da Etapa 2.
- Biblioteca protegida por policy; dados pessoais de outros usuários não aparecem na listagem nem podem ser editados.
- Formulario de rotina ajustado para limpar metas incompatíveis ao adicionar exercícios de medicoes diferentes e exibir erros de validacao dos itens.
- Sessões agora permitem registrar, editar e excluir séries sem recarregar a tela, com validação por métrica e autorização da sessão.
- Sessões agora podem ser finalizadas mesmo com pendências; a tela concluída fica somente leitura e exibe um resumo.
- Adicionado histórico paginado de sessões e último desempenho por exercício, sempre limitado ao usuário autenticado.

### Corrigido
- Sessao ativa continua acessivel apos a exclusao da rotina de origem, sem bloquear a criacao de novas rotinas.
- Criacao e edicao de rotinas com dois ou mais exercicios agora preservam a ordem e as metas informadas.
- Corrigida a codificacao UTF-8 do changelog e do formulario de rotinas, eliminando caracteres corrompidos na interface.

### Validado
- `vendor/bin/pest tests/Feature/WorkoutSessionTest.php`: 14 testes passando, 82 asserções.
- `php artisan test --compact`: 70 testes passando, 257 asserções.
- `vendor/bin/pest tests/Feature/WorkoutRoutineTest.php`: 11 testes passando, 44 assercoes.
- `vendor/bin/pest tests/Feature/ModalityTest.php`: 6 testes passando, 20 asserções.
- `vendor/bin/pest`: 31 testes passando, 80 asserções.
- `vendor/bin/pint --test`: aprovado após a Etapa 5.
- `npm.cmd run build`: compilação Vite concluída.
- `php artisan migrate:fresh --force`: migrations padrão e de domínio aplicadas no SQLite.
- `vendor/bin/pest tests/Feature/ExerciseLibraryTest.php`: 8 testes passando, 31 asserções.
- `php artisan view:cache`: templates Blade compilados com sucesso.

### Pendente
- Testes gerais, responsividade e acessibilidade da Etapa 9.
- Verificação da estratégia equivalente de sessão aberta para drivers diferentes de SQLite/PostgreSQL.
- `npm install` reportou 7 vulnerabilidades (2 moderadas e 5 altas); revisar antes de publicar assets em produção.

<!-- A cada etapa: registrar data/versão quando aplicável, Adicionado, Alterado, Corrigido, Validado (comandos e resultados) e Pendente. Nunca marcar algo como implementado sem código. -->

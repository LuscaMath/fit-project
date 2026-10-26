# Modelo de dados — proposta inicial

> Especificação conceitual, não migrations prontas. Tipos exatos, índices e políticas de exclusão serão revisados na etapa de banco.

## Entidades
### users
Padrão de autenticação Laravel; `id` e dados de login.

### exercises
`id`, `user_id nullable` (catálogo global quando null), `name`, `modality` (gym/calisthenics), `measurement_type` (reps/duration/reps_and_duration), `muscle_group`, `equipment nullable`, `instructions`, `video_url nullable`, timestamps. Índices em `modality`, `muscle_group`, `user_id`.

### workout_routines
`id`, `user_id`, `name`, `primary_modality`, `description nullable`, `is_active`, timestamps. Índice `(user_id, primary_modality, is_active)`.

### routine_exercises
`id`, `workout_routine_id`, `exercise_id`, `position`, `planned_sets`, `target_reps nullable`, `target_duration_seconds nullable`, `target_load_kg decimal nullable`, `rest_seconds nullable`, `notes nullable`. Índice `(workout_routine_id, position)`. Repetir o mesmo exercício em posições diferentes é permitido.

### workout_sessions
`id`, `user_id`, `workout_routine_id nullable`, `routine_name_snapshot`, `primary_modality_snapshot`, `status` (in_progress/completed), `started_at`, `finished_at nullable`, timestamps. Índice `(user_id, status, started_at)`. `workout_routine_id` opcional para preservar histórico se a rotina for removida por política futura; no MVP preferir arquivamento.

### session_exercises
`id`, `workout_session_id`, `exercise_id nullable`, `exercise_name_snapshot`, `measurement_type_snapshot`, `position`, `planned_sets`, `target_reps nullable`, `target_duration_seconds nullable`, `target_load_kg decimal nullable`, `rest_seconds nullable`, timestamps. Índice `(workout_session_id, position)`. Snapshot evita mudanças retroativas; considerar snapshot de instruções/vídeo se a exigência histórica surgir (não é necessária no MVP).

### performed_sets
`id`, `session_exercise_id`, `set_number`, `repetitions nullable`, `duration_seconds nullable`, `load_kg decimal nullable`, `client_request_id uuid`, `completed_at`, timestamps. `UNIQUE(session_exercise_id, set_number)` e `UNIQUE(client_request_id)`; validar ao menos a métrica exigida pelo tipo do exercício.

## Implementacao da Etapa 5

- Rotinas sao criadas e atualizadas com seus itens dentro de transacao; a ordem recebida e normalizada para posicoes consecutivas.
- A modalidade principal filtra a listagem, mas a validacao aceita exercicios globais e pessoais das duas modalidades.
- Metas de repeticoes e duracao sao validadas conforme `measurement_type`; carga e descanso possuem limites server-side.
- Arquivar alterna `is_active` sem apagar a rotina ou seus itens, preservando integridade para sessoes futuras.

## Relacionamentos
- User 1:N WorkoutRoutine e WorkoutSession; User 1:N Exercise (personalizados).
- WorkoutRoutine 1:N RoutineExercise e WorkoutSession.
- Exercise 1:N RoutineExercise e SessionExercise.
- WorkoutSession 1:N SessionExercise.
- SessionExercise 1:N PerformedSet.

## Implementacao da Etapa 6

- Iniciar uma rotina cria `workout_sessions` e seus `session_exercises` na mesma transacao.
- Nome da rotina, modalidade, exercicio, metrica, ordem e metas sao copiados no inicio e nao dependem de alteracoes posteriores.
- A sessao aberta existente e retomada em novas tentativas; o indice unico parcial trata concorrencia no SQLite/PostgreSQL.
- A tela de execucao carrega um exercicio por vez e permite navegacao sem registrar series nesta etapa.

## Implementacao da Etapa 7

- A tela de execucao carrega as series do exercicio atual e permite registrar, editar e excluir cada serie sem recarregar a pagina.
- O registro usa `client_request_id` UUID e retorna o registro original em retries identicos; payload divergente para o mesmo UUID e rejeitado.
- `set_number` e unico dentro de `session_exercise`; a validacao server-side exige repeticoes, duracao ou ambas conforme `measurement_type_snapshot`.
- Mutacoes de series sao permitidas somente para o proprietario de uma sessao `in_progress`; sessoes concluidas permanecem somente leitura.

## Implementação da Etapa 8

- A finalização atualiza `status` e `finished_at` dentro de transação com lock da sessão; repetir a operação em uma sessão concluída preserva o mesmo horário.
- Exercícios e séries pendentes não impedem a finalização. O resumo calcula séries planejadas e registradas a partir dos snapshots e relações persistidos.
- O histórico consulta sessões e séries somente pelo `user_id` autenticado. O último desempenho considera apenas séries de sessões concluídas e seleciona o registro mais recente por exercício; quando o catálogo foi removido, usa o nome preservado no snapshot.

## Integridade
- Foreign keys habilitadas no SQLite; migrations reversíveis.
- `decimal` para cargas, não float/double.
- Constraints/índices para idempotência e números de série; lidar com concorrência no servidor.
- Não aplicar cascade delete que apague sessões históricas ao excluir uma rotina/exercício. Para catálogo global, alterações não devem expor dados pessoais.
- Preferir enums PHP com strings persistidas para facilitar portabilidade.

## Implementação da Etapa 2

- Os enums `Modality`, `MeasurementType` e `WorkoutSessionStatus` são persistidos como strings e convertidos pelos casts dos modelos Eloquent.
- `routine_exercises.position` e `session_exercises.position` possuem unicidade dentro do respectivo pai.
- `performed_sets` possui unicidade por `(session_exercise_id, set_number)` e por `client_request_id`, sustentando retries idempotentes no banco.
- SQLite e PostgreSQL recebem um índice único parcial em `workout_sessions(user_id)` para `status = 'in_progress'`, mantendo o histórico de sessões concluídas. Outros drivers devem complementar essa garantia em transação na camada de aplicação.
- `session_exercises` preserva nome, métrica e metas no momento da sessão. A remoção de um exercício apenas torna `exercise_id` nulo e não apaga o snapshot.
- Filhos de uma sessão são removidos somente quando a própria sessão é removida; apagar rotinas ou exercícios não remove histórico de sessões.

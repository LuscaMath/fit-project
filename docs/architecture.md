# Arquitetura

## Decisão principal
Monólito Laravel renderizado no servidor: rotas `web`, controllers, Blade, Tailwind e Alpine.js para interações pontuais. JSON em endpoints web para registro de séries não constitui API pública separada.

## Responsabilidades
- **Controllers:** HTTP, invocar validação/autorização e ações; não concentrar regras.
- **Form Requests:** validar entrada e regras de formato.
- **Policies:** autorização por proprietário e visibilidade de catálogo.
- **Models/Eloquent:** relações, casts, escopos simples e persistência.
- **Actions:** `StartWorkoutSession`, `RecordPerformedSet`, `UpdatePerformedSet`, `DeletePerformedSet`, `FinishWorkoutSession`; apenas quando houver regra/transação.
- **Enums:** modalidade, tipo de medição e status da sessão.
- **Blade components:** cartões, seleção de modalidade, instruções e formulário de série.

## Navegação
`/modalidades` → `/gym/rotinas` ou `/calisthenics/rotinas`; catálogo também filtrado pelo modo. Rotas de sessão podem ser comuns (`/sessoes/{session}`), pois a sessão tem modalidade de origem preservada. O filtro de modo é visual/funcional, nunca um mecanismo de autorização.

## Persistência e segurança
- Sessão inicializada com transação e snapshot; definir proteção contra concorrência na implementação conforme suporte do SQLite e testes.
- Uma sessão ativa por usuário: a garantia deve ser aplicada no servidor e testada sob concorrência; considerar índice parcial se portabilidade permitir ou estratégia transacional apropriada.
- POST de série recebe UUID idempotente; índice único no banco; retries retornam o registro original quando o payload for consistente e conflito quando divergente.
- Edição/exclusão só em sessão em andamento; numeração de séries única por exercício de sessão.
- Sessões concluídas preservam dados históricos; não apagar histórico ao arquivar rotinas.
- Validar URL de vídeo e provedores permitidos; exibir link seguro caso embed não seja suportado.

Na Etapa 2, a aplicação usa Laravel 13 com PHP 8.5, SQLite e enums nativos para os valores de domínio. O índice parcial de sessão aberta é criado pela migration quando o driver oferece suporte; a camada de aplicação continuará responsável pela transação e pelo tratamento de concorrência em etapas posteriores.

Na Etapa 3, a biblioteca consulta somente exercícios globais ou pertencentes ao usuário autenticado. A política `ExercisePolicy` protege visualização e edição, enquanto os Form Requests validam filtros, métricas e URLs. Vídeos aceitos são YouTube e Vimeo; o embed só é gerado quando o identificador da URL pode ser extraído com segurança.

Na Etapa 4, a modalidade escolhida e persistida na sessao pela chave `current_modality`. As rotas `/gym/exercises` e `/calisthenics/exercises` reutilizam a biblioteca e restringem o catalogo a modalidade selecionada, mantendo as regras de visibilidade global e pessoal. A troca de modalidade ocorre em `/modalidades` e nao altera dados ja persistidos.

Na Etapa 5, rotinas pertencem ao usuario e sao filtradas por `primary_modality`; a composicao usa exercicios globais ou pessoais visiveis ao usuario e pode misturar modalidades. A gravacao do cabecalho e dos itens ocorre em uma transacao, com reordenacao normalizada e arquivamento reversivel por `is_active`. Rotinas de outros usuarios sao bloqueadas pela `WorkoutRoutinePolicy`.

Na Etapa 6, `StartWorkoutSession` cria uma sessao em transacao e copia os parametros planejados para `session_exercises`. A politica permite leitura somente ao proprietario; a unicidade de uma sessao `in_progress` por usuario e garantida pelo indice parcial e pelo tratamento de corrida. A tela `/sessoes/{session}` navega por um exercicio usando o snapshot salvo.

Na Etapa 7, a tela da sessao usa endpoints JSON web para registrar, editar e excluir series. `RecordPerformedSet` conserva o mesmo registro quando o `client_request_id` e reenviado com payload identico e responde conflito quando o UUID e reutilizado com dados diferentes. A policy permite mutacoes somente ao proprietario de uma sessao em andamento, e os Form Requests validam a metrica do snapshot do exercicio.

Na Etapa 8, `FinishWorkoutSession` finaliza a sessão em transação com lock da linha e é idempotente para reenvios do mesmo comando. A tela mantém o resumo de séries planejadas e registradas, permite concluir mesmo com pendências e remove os controles de alteração depois da conclusão. `/sessoes` lista somente sessões do usuário autenticado e apresenta o último conjunto registrado em sessões concluídas para cada exercício, usando o nome do snapshot quando o exercício original foi removido.

## Testes e qualidade
Pest Feature para fluxos, permissões, idempotência, snapshots e retomada; Unit para regras puras quando necessário. Laravel Pint e Larastan. Não introduzir filas, Redis, Docker ou API sem necessidade demonstrada.

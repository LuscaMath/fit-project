# Requisitos e critérios de aceite

## Funcionais
| ID | Requisito | Aceite mínimo |
|---|---|---|
| RF01 | Escolher modo | Entrar em Academia ou Calistenia e alternar sem perder contexto persistido |
| RF02 | Biblioteca | Listar e filtrar por modalidade, grupo muscular e equipamento |
| RF03 | Orientações | Mostrar instruções e vídeo válido do exercício |
| RF04 | Exercícios pessoais | Criar/editar exercício próprio sem alterar catálogo global |
| RF05 | Rotinas | Criar, editar, arquivar e ordenar exercícios |
| RF06 | Mistura | Rotina de qualquer modo aceita exercícios de ambas modalidades |
| RF07 | Planejamento | Definir séries, reps/duração, carga opcional e descanso |
| RF08 | Iniciar sessão | Criar snapshot da rotina, respeitando uma sessão aberta por usuário |
| RF09 | Tela de execução | Um exercício por vez; navegar livremente |
| RF10 | Série individual | Salvar, corrigir e excluir série com confirmação do servidor |
| RF11 | Retomada | Reabrir sessão em andamento após sair do navegador |
| RF12 | Finalização | Concluir sessão com exercícios pendentes e exibir resumo |
| RF13 | Histórico | Consultar sessões e último desempenho por exercício |
| RF14 | Autenticação | Login/logout, sem registro público |
| RF15 | Isolamento | Usuário não acessa dados privados de outra conta |

## Não funcionais
- Mobile-first, sem exigir app nativo.
- Acessibilidade básica: labels, foco visível, controles utilizáveis por toque e mensagens de erro compreensíveis.
- Persistência confiável; não indicar sucesso antes de confirmação; idempotência em repetição de POST.
- Consultas com índices apropriados e eager loading quando necessário; evitar otimização prematura.
- Testes automatizados dos caminhos críticos; análise estática e formatação.

## Cenários de teste prioritários
1. Rotina `gym` com exercício `calisthenics` é válida e aparece na área Academia.
2. Editar rotina depois de iniciar sessão não altera snapshot nem séries anteriores.
3. Enviar o mesmo `client_request_id` duas vezes não duplica série.
4. Duas tentativas simultâneas de iniciar sessão não criam duas sessões abertas.
5. Outro usuário recebe acesso negado a sessões/séries privadas.
6. Finalizar com exercícios pendentes funciona; salvar após finalizar é rejeitado.
7. Retomar após fechar a página recupera séries já persistidas.
8. Campos de duração/repetições obedecem ao tipo de medição.

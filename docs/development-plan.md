# Plano incremental

## Processo obrigatório
Para cada etapa: inspecionar código → propor mudanças → implementar **somente etapa aprovada** → rodar testes/lint relevantes → atualizar changelog → relatar resultados e aguardar autorização.

| Etapa | Entrega | Critério de conclusão |
|---|---|---|
| 1 | Bootstrap Laravel | Versões verificadas, app inicia, autenticação básica e testes de smoke |
| 2 | Domínio e banco | Enums, models, migrations, relacionamentos, factories e testes de integridade |
| 3 | Biblioteca | Seeder inicial pequeno, listagem/filtros, instruções/vídeos e exercícios pessoais |
| 4 | Modos | Escolha Academia/Calistenia, navegação filtrada e troca de modo |
| 5 | Rotinas | CRUD, ordenação, metas e mistura de modalidades com testes |
| 6 | Sessões | Iniciar, snapshot, uma sessão aberta e tela um-exercício-por-vez |
| 7 | Séries | Registro individual, correção, exclusão, idempotência e feedback de rede |
| 8 | Histórico | Retomada, finalização parcial, resumo, histórico e último desempenho |
| 9 | Qualidade | Testes end-to-end dos fluxos, responsividade, acessibilidade, revisão documental |

## Prioridades
P0: segurança, integridade histórica, registro confiável. P1: experiência mobile e instruções. P2: refinamentos visuais. Não desenvolver recursos fora do MVP sem aprovação.

## Pendências de decisão técnica
- Confirmar versões PHP/Laravel e starter kit compatível antes de instalar.
- Definir estratégia segura para concorrência de sessões e numeração de séries em SQLite.
- Selecionar fontes confiáveis para vídeos e instruções do catálogo inicial.
- Definir se exercícios com peso corporal terão campo de carga adicional (o campo genérico opcional já permite).

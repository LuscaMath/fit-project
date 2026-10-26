# Instruções para agentes de desenvolvimento

1. Leia **integralmente** `base.md`, depois `docs/requirements.md`, `docs/architecture.md`, `docs/database.md`, `docs/development-plan.md` e `CHANGELOG.md` antes de propor mudanças relevantes.
2. `base.md` define escopo e restrições; este arquivo define o processo de trabalho. Não extrapole o MVP.
3. Antes de codificar: inspecione o repositório, informe objetivo da etapa, abordagem, arquivos previstos, riscos e testes. Se houver ambiguidade de negócio, pergunte.
4. Execute **somente a etapa autorizada**; alterações pequenas, revisáveis e testáveis. Não faça commits automaticamente.
5. Prefira Laravel nativo, Eloquent, Form Requests, Policies, migrations, enums, Blade e componentes reutilizáveis. Use Actions somente onde simplifiquem fluxos com regras. Evite abstrações especulativas, repositories genéricos, API separada, SPA e dependências supérfluas.
6. Garanta autorização em leitura/escrita, validação server-side, CSRF, constraints e índices adequados, integridade histórica, idempotência e transações em operações críticas.
7. Escreva testes Pest (incluindo autorização, snapshots, idempotência, fluxo incompleto e retomada). Rode os testes e Pint pertinentes; reporte o resultado real e eventuais limitações.
8. Atualize `CHANGELOG.md` **na mesma entrega** com o que foi feito, alterado, validado e pendente. Atualize documentos técnicos quando o comportamento mudar; não altere `base.md` para justificar uma implementação divergente.
9. Ao concluir, entregue: resumo; arquivos alterados; decisões; comandos executados/resultados; pendências; próxima etapa sugerida. Aguarde aprovação.
10. Use nomes claros e consistentes em inglês no código e documentação em português. Não exponha segredos, tokens nem dados reais em commits.

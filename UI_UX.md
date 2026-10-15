# UI/UX do DB Deeper

Este guia adapta o processo de interface do PediManga ao DB Deeper. Reaproveitamos a disciplina de design, auditoria e validação; a identidade visual e as necessidades do produto continuam próprias do pacote.

## Direção do produto

O DB Deeper é um painel operacional de banco de dados para Laravel. A interface deve ajudar uma pessoa responsável pela aplicação a entender o estado do banco, perceber riscos e decidir o próximo passo com segurança.

- Use a identidade da Integrad: roxo `#331c58`, violeta `#9f55ff` e o tema `db-deeper` já definido em `resources/css/app.css`.
- Prefira uma aparência de console de infraestrutura clara, sóbria e legível. Evite copiar a estética de marketplace do PediManga ou cair em um dashboard administrativo genérico.
- Métricas devem vir de fontes reais. Identifique origem, período, limiar e limitações. Ausência de permissão ou telemetria é um estado explícito, nunca zero ou dado inventado.
- Dê prioridade a ações seguras e reversíveis. A interface deve distinguir diagnóstico, configuração e operações que alterem ou restaurem dados.

## Princípios de interface

- Comece pelo mobile e faça o conteúdo funcionar em telas estreitas; tabelas densas podem rolar horizontalmente dentro do próprio limite.
- Mantenha a rolagem vertical na página. Use rolagem interna somente em tabelas, listas extensas e diálogos que realmente precisem dela.
- Organize a informação pela decisão: estado geral, indicador e contexto, detalhe acionável, origem e limitações.
- Use rótulos claros, unidades junto aos valores, números alinhados e hierarquia discreta. Não use cor como único indicador de estado.
- Preserve navegação consistente: menu lateral no desktop, drawer acessível no mobile, item ativo anunciado e menu fechado após navegar.
- Use os componentes e tokens daisyUI já instalados antes de criar estilos próprios. Mantenha a paleta Integrad e os raios definidos no tema.
- Use os ícones Lucide já instalados (`data-lucide`) para ações e indicadores. Não adicione outra biblioteca de ícones sem necessidade.
- Trate estados de carregamento, vazio, erro, falta de permissão, driver não suportado e estatística indisponível.
- Inclua nomes acessíveis, foco visível, controles operáveis por teclado e suporte a movimento reduzido quando houver animação.
- Preserve as APIs públicas do pacote, rotas, Gates, configuração, compatibilidade entre versões Laravel/PHP e comportamento do banco. Uma mudança visual não deve alterar esses contratos.

## Processo de trabalho com DaisyUI Blueprint MCP

Para uma alteração de interface não trivial, siga esta sequência antes e durante a implementação:

1. **Audite o contexto.** Leia este guia, confira a view e o layout envolvidos, rotas, componentes existentes, estados e contratos do controller. Inspecione `package.json`, CSS e build para confirmar as versões e ferramentas instaladas.
2. **Defina o escopo e as decisões visuais.** Registre qual tela ou componente muda, o que a pessoa precisa decidir, quais estados serão cobertos e como a direção Integrad de console operacional aparece nessa tela.
3. **Inicie um workflow Blueprint.** Chame `daisyui_setup_expert` com a raiz real do repositório, um `workflowId` em minúsculas e somente as opções de setup necessárias. Reuse esse ID para partes relacionadas da mesma tela; use outro para uma direção visual diferente.
4. **Aplique as regras.** Chame `daisyui_rules_enforcer` para o mesmo `workflowId` e siga as instruções retornadas.
5. **Planeje uma tela completa.** Para uma nova tela completa, chame `daisyui_creative_director` e depois `daisyui_page_architect`. Guarde as decisões de conceito, domínio, elemento característico e composição para a revisão final. Em ajustes menores, essas duas etapas são opcionais.
6. **Consulte a sintaxe.** Antes de alterar markup, chame `daisyui_component_syntax_expert` para os componentes daisyUI necessários. Se houver IDs restantes, continue até o MCP indicar que a etapa terminou.
7. **Implemente na base existente.** Reuse o layout do painel, Blade, classes e tokens existentes. Mantenha o escopo de backend; não introduza dependências para resolver algo que a stack instalada já cobre.
8. **Revise a qualidade.** Após uma mudança coesa e não trivial, rode `daisyui_quality_inspector` com `auditIntent: fix_changes`, os caminhos relativos ao `projectRoot` e as decisões de composição registradas. Use `report_only` apenas em auditoria explicitamente somente leitura.
9. **Verifique o resultado.** Revise diff, execute o build e as verificações pertinentes. Abra as rotas afetadas em viewport mobile e desktop, confira navegação, estados, erros de console e apresentação de dados. Se a inspeção visual não estiver disponível, informe a limitação sem declarar que passou.

Para correções de texto ou ajustes visuais pequenos e localizados, não é necessário repetir o workflow completo nem chamar o Quality Inspector.

## Checklist de revisão

- [ ] A tela prioriza a tarefa operacional e deixa claro o próximo passo.
- [ ] Métricas exibem fonte, período, unidades e disponibilidade correta.
- [ ] Estados de erro, vazio e falta de permissão explicam o que fazer.
- [ ] A tela funciona em celular e desktop sem overflow involuntário.
- [ ] Tabelas e dados densos continuam acessíveis em viewport estreita.
- [ ] Drawer, navegação, teclado, foco e labels acessíveis funcionam.
- [ ] Cores e ícones seguem o tema e as dependências existentes.
- [ ] Os contratos Laravel e as operações de banco permanecem preservados.
- [ ] Build e revisão visual foram executados ou suas limitações foram registradas.

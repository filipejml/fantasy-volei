# Arquitetura VNL sem API paga

## 1. Visão geral

O sistema trabalha sempre com dados persistidos no MySQL. A Volleyball World é uma fonte auxiliar de importação, nunca uma dependência para renderizar páginas.

Fluxo:

1. `VolleyballWorldScraper` consulta as páginas públicas oficiais.
2. Seleções, partidas e classificação são salvas com `origem = scraping`.
3. A tela VNL consulta exclusivamente o banco local.
4. O administrador pode corrigir qualquer registro; ao salvar, a origem passa para `manual`.
5. Falhas são registradas em `scraping_logs` e não removem dados existentes.

## 2. Fonte oficial

- Jogos: endpoint público utilizado pela página de calendário da Volleyball World.
- Classificação: HTML oficial das páginas `/standings/men/` e `/standings/women/`.
- Configuração: `config/services.php`, seção `volleyball_world`.

Variáveis opcionais:

```env
VOLLEYBALL_WORLD_URL=https://en.volleyballworld.com
VNL_SEASON=2026
VNL_TOURNAMENT_IDS=1661;1662
VNL_SCHEDULE_FROM=2026-06-01
VNL_SCHEDULE_TO=2026-08-15
```

## 3. Banco

- `selecoes`: possui `external_ref` e `source_url`.
- `partidas`: possui temporada, gênero, fase, rodada, local, sets, `external_hash`, origem e data de importação.
- `classificacoes`: tabela persistida e editável, com origem manual, scraping ou calculada.
- `scraping_logs`: histórico de execução, erro e quantidade processada.
- Os campos `api_team_id`, `api_game_id` e `api_player_id` foram eliminados.

## 4. Models e relacionamentos

- `Selecao` possui jogadores, partidas como mandante/visitante e classificações.
- `Partida` pertence às duas seleções.
- `Classificacao` pertence a uma seleção.
- `User` possui times.
- `Time` pertence ao usuário e possui jogadores por relacionamento muitos-para-muitos.
- `Jogador` pertence a seleção e posição e pode integrar vários times.

## 5. Administração

Rotas sob `/admin`, protegidas pelos middlewares `auth` e `admin`:

- CRUD de seleções;
- CRUD de posições;
- CRUD de jogadores;
- CRUD de partidas;
- CRUD da classificação;
- cálculo da classificação pelos resultados;
- atualização da VNL;
- consulta aos logs do scraping.

## 6. Command Artisan

```bash
php artisan vnl:atualizar
```

O comando usa o mesmo service acionado pelo botão administrativo.

## 7. Plano B manual

O scraping nunca apaga partidas ou classificação quando falha. O administrador pode:

- criar uma partida que não foi importada;
- alterar data, local, status e placar;
- excluir registros incorretos;
- criar ou editar cada linha da classificação;
- recalcular a tabela usando somente partidas encerradas.

Uma edição manual define `origem = manual`, identificando que o dado foi revisado.

## 8. Fantasy

O usuário informa nome, gênero e limite de créditos, seleciona até sete jogadores e salva o time.

A sugestão automática ordena atletas pela relação `média de pontos / valor em créditos` e escolhe jogadores sem ultrapassar o orçamento. Essa heurística pode futuramente ser substituída por programação inteira para considerar regras rígidas por posição.

## 9. Ordem de desenvolvimento recomendada

1. Manter migrations e models cobertos por testes.
2. Revisar seletores do scraper antes de cada temporada.
3. Completar cadastro de jogadores e estatísticas.
4. Definir regras oficiais de composição por posição.
5. Evoluir a sugestão de time para otimização combinatória.
6. Agendar `vnl:atualizar` somente após monitorar estabilidade.
7. Adicionar alertas quando o último scraping terminar com erro ou parcial.

## 10. Manutenção do scraper

O parser de jogos valida a presença de `matches` e `allTeams`. O parser da classificação procura linhas com a classe oficial `vbw-o-table__row`.

Se a estrutura mudar:

1. o processo gera um log de erro ou parcial;
2. os dados anteriores permanecem disponíveis;
3. o CRUD manual continua operacional;
4. apenas o service `VolleyballWorldScraper` precisa ser adaptado.


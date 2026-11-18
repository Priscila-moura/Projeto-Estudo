# Sistema Web — Mercearia
Sistema web de pedidos de cereais , Frutas , Verduras, Laticínios e Queijos. O Cliente faz o pedido pelo
celular e retira no balcão, sem fila no intervalo.
## Funcionalidades
- Cadastro e login de clientes
- Catalogo com preços e fotos
- Autenticação Simulada: Tela de Login e Cadastro com validação de campos obrigatórios (E-mail/CPF e Senha).
- Dashboard Interativo: Cards de categorias (*Frutas*, *Cereais*, *Laticínios & Queijos*) com sobreposição (`overlay`) exibindo itens em estoque ao passar o mouse.
- Navegação SPA (Abas): Alternância instantânea sem recarregamento de página entre *Home*, *Dashboard*, *Produtos*, *Sobre* e *Contato*.

## Tecnologias
- PHP 8.2
- MySQL 8
- HTML, CSS e JavaScript
- XAMPP (ambiente local)
- ## 📂 Estrutura de Arquivos

├── index.html       # Ponto de entrada principal (contém toda a aplicação SPA)
├── style.css        # Folha de estilos global (tema escuro com detalhes em verde)
├── login.html       # Fragmento HTML da tela de login
├── dashboard.html   # Fragmento HTML do grid de categorias
├── produto.html     # Fragmento HTML do catálogo geral
├── sobre.html       # Fragmento HTML da história da mercearia
├── contato.html     # Fragmento HTML dos canais de atendimento
└── img/
    └── Projeto.ico  # Ícone (favicon) da aplicação

## Como rodar
1. Instale o XAMPP e ligue Apache e MySQL.
2. Copie a pasta `cantinaweb` para `C:\xampp\htdocs`.
3. No phpMyAdmin, importe `database/cantinaweb.sql`.
4. Acesse http://localhost/cantinaweb
Passos detalhados em `docs/instalacao.md`.

## Usuário de teste
- E-mail: aluno@teste.com
- Senha: 123456
## Documentação
- Requisitos: `docs/requisitos.md`
- Banco de dados: `docs/banco-de-dados.md`
- Manual do usuário: `docs/manual-usuario.md`
## Equipe
- Ana Souza — back-end (PHP)
- Bruno Lima — banco de dados
- Carla Mendes — front-end e documentação
Projeto do curso técnico em Desenvolvimento de Sistemas —
ETE Porto Digital, 2026.

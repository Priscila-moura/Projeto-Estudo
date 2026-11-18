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

## Apresentação 

Este guia apresenta a documentação completa do sistema Mercearia ("Economia certa para o seu bolso!"), um sistema web SPA (Single Page Application) front-end desenvolvido em HTML5, CSS3 e JavaScript puro para controle visual de acesso, catálogo de produtos e gestão de categorias de estoque. A estrutura segue os 11 módulos propostos para capacitar equipes técnicas e usuários finais na manutenção, evolução e operação do software.

## Por que documentar? (essencial)

A documentação garante que o conhecimento sobre o funcionamento do sistema não fique restrito apenas a quem escreveu o código original. No contexto do projeto Mercearia:
Facilita identificar onde alterar regras visuais (como variáveis CSS em style.css) ou adicionar novas categorias de produtos sem quebrar a navegação por abas.
Permite que um novo desenvolvedor compreenda imediatamente que o projeto atual funciona como um protótipo estático.
Evita divergências entre o que o cliente espera e o que está implementado.

## Tipos de documentação e para quem escrever (essencial)

Tipo de documentação                               Público-Alvo                                    Objetivo no Projeto Mercearia
README.md                                      ( Desenvolvedores e Avaliadores)                 (Visão geral rápida, estrutura de pastas e instruções de execução local).

Requisitos e Regras de Negócio                 (Product Owner, Analistas e Devs)                (Definir comportamentos esperados (ex.: campos obrigatórios no login, listagem de 4 itens por categoria).

Modelagem de Banco e Código (SQL /PHPDoc)       Desenvolvedores Back-end                         Preparar a migração do catálogo estático atual para persistência em MySQL e PHP.

Documentação de Telas e Fluxos                   Designers UI/UX e QA (Testadores)              Mapear estados de interface e interações nos cards.

Manual do Usuário                              Operadores e Clientes da Mercearia                Ensinar como fazer login, navegar pelas abas e consultar o estoque sem termos técnicos



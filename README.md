# 💻 TI Inventory Manager

Sistema web para gerenciamento de equipamentos de Tecnologia da Informação.

O projeto foi desenvolvido com o objetivo de auxiliar no controle de inventário de TI, permitindo cadastrar equipamentos, acompanhar manutenções, controlar usuários e registrar histórico de alterações.

---

## 🚀 Funcionalidades

✅ Login de usuários  
✅ Controle de acesso por nível (Administrador e Usuário)  
✅ Dashboard com resumo dos equipamentos  
✅ Cadastro de equipamentos  
✅ Edição e exclusão de equipamentos  
✅ Controle de status:

- Disponível
- Emprestado
- Manutenção
- Baixado

✅ Controle de manutenções  
✅ Registro automático de histórico de alterações  
✅ Gerenciamento de usuários administradores  

---

## 🛠️ Tecnologias utilizadas

- PHP 8
- MySQL / MariaDB
- HTML5
- CSS3
- JavaScript
- XAMPP
- phpMyAdmin

---

## 📂 Estrutura do projeto

```text
TI-Inventory-Manager
│
├── assets
│   └── css
│
├── auth
│   ├── login.php
│   ├── logout.php
│   ├── proteger.php
│   └── proteger_admin.php
│
├── config
│   └── conexao.php
│
├── database
│   └── inventario_ti.sql
│
├── equipamentos
│   ├── cadastrar.php
│   ├── detalhes.php
│   ├── editar.php
│   ├── editar_manutencao.php
│   ├── listar.php
│   └── manutencao.php
│
├── includes
│   └── sidebar.php
│
├── usuarios
│   └── listar.php
│
├── historico.php
├── index.php
└── README.md
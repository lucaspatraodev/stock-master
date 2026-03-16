# StockMaster
Sistema de cadastro e gerenciamento de produtos.

## 🛠 Tecnologias Utilizadas
- **Backend:** Laravel 12 (PHP 8.3)
- **Frontend:** Vue 3 com Inertia.js e Tailwind CSS
- **Autenticação:** Laravel Breeze
- **Ambiente:** Docker (docker-compose)
- **Banco de Dados:** MySQL


## 📦 Como Instalar e Rodar
Siga os passos abaixo para rodar o projeto em sua máquina local:

1. **Clonar o repositório:**
   ```bash
   git clone https://github.com/lucaspatraodev/stock-master.git
   cd stock-master
   ```

2. **Configurar o Ambiente (.env):**
   ```bash
   cp .env.example .env
   ```

3. **Instalar dependências (via Docker):**
   ```bash
  docker compose exec app composer install
   ```

4. **Subir os containers:**
   ```bash
   docker compose up -d
   ```

5. **Gerar chave da aplicação e rodar migrations:**
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate --seed
   ```

6. **Criar link para arquivos públicos (uploads):**
   ```bash
   docker compose exec app php artisan storage:link
   ```

7. **Compilar o Frontend:**
   ```bash
   docker compose exec app npm install
   docker compose exec app npm run build
   ```

8. **Acessar a aplicação:**
Abra no navegador: http://localhost

## ✅ Testes
### Feature
Para rodar os testes de Feature:
```bash
docker compose exec app php artisan test --testsuite=Feature
```
### Unitários
Para rodar apenas os testes unitarios:
```bash
docker compose exec app php artisan test --testsuite=Unit
```

## 📄 Documentacao da API (Scribe)
Gerar a documentacao:
```bash
docker compose exec app php artisan scribe:generate
```
Acesse em:
- http://localhost/docs

Para habilitar o "Try It Out"/testar direto do navegador com token:
- O token pode ser obtido no login (endpoint `POST /api/login`);
- Preencha o campo Authorization com Bearer Bearer {SEU_TOKEN};
- Faça as requisições!

## 🧭 Arquitetura (Resumo)
- **Backend:** Laravel 12 com Sanctum para autenticação via token.
- **Frontend:** Vue 3 + Inertia.js + Tailwind CSS.
- **Banco de Dados:** MySQL.
- **API:** endpoints REST em `/api/*`, documentados via Scribe em `/docs`.
- **Uploads:** imagens armazenadas em `storage/app/public` e servidas via `public/storage` (`storage:link`).
- **Logs:** `ProductObserver` registra criação e atualização em `product_logs`.
- **Segurança:** validações no servidor (margem de 10%, HTML permitido, tipos de imagem), autenticação em rotas protegidas.

## 📜 Logs de Produto
Para consultar os logs via Tinker:
```bash
php artisan tinker --no-pager
```

Dentro do Tinker:
```php
// Consultar últimos 20 logs
\App\Models\ProductLog::latest()->take(20)->get();

// Consultar logs de um produto especifico
\App\Models\ProductLog::where('product_id', 1)->latest()->get();
```

## 📘 Manual do Usuario
### 1) Primeiro acesso
- Ao abrir a aplicacao, se nao houver usuarios cadastrados, o sistema libera o cadastro.
- Crie o primeiro usuario. Depois disso, novos cadastros ficam bloqueados.

### 2) Login
- Para logar, sempre utilize seu email e senha.

### 3) Tela de Produtos
Depois do login, voce vera a lista de produtos e poderá:
- **Cadastrar** um produto
- **Editar** um produto
- **Inativar** um produto

### 4) Cadastro de Produto
Campos obrigatorios:
- **Titulo**
- **Descricao** (HTML permitido apenas: `<p>`, `<br>`, `<b>`, `<strong>`)
- **Preco de venda**
- **Custo**
- **Imagens** (jpg/png, multiplas)

Regras de negocio:
- O **preco de venda** deve ser no minimo **10% maior** que o custo.
- Imagens aceitas: **jpg** e **png**.

### 5) Edicao de Produto
- Atualize qualquer campo.
- Adicione novas imagens.
- Remova imagens existentes.
- Reordene imagens arrastando e soltando.

### 6) Inativar Produto
- Um produto inativado nao aparece como ativo e fica bloqueado para novas operacoes de venda.

## 👨‍💻 Autor
Desenvolvido por Lucas Patrão
   

# StockMaster
Sistema de cadastro e gerenciamento de produtos.

## 🛠 Tecnologias Utilizadas
- **Backend:** Laravel 12 (PHP 8.3)
- **Frontend:** Vue 3 com Inertia.js e Tailwind CSS
- **Autenticação:** Laravel Breeze
- **Ambiente:** Docker (Laravel Sail)
- **Banco de Dados:** MySQL


## 📦 Como Instalar e Rodar
Siga os passos abaixo para rodar o projeto em sua máquina local:

1. **Clonar o repositório:**
   ```bash
   git clone [https://github.com/lucaspatraodev/stock-master.git](https://github.com/lucaspatraodev/stock-master.git)
   cd stock-master
   ```

2. **Configurar o Ambiente (.env):**
   ```bash
   cp .env.example .env
   ```

3. **Instalar dependências (via Docker):**
   ```bash
   docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
   ```

4. **Subir os containers:**
   ```bash
   ./vendor/bin/sail up -d
   ```

5. **Gerar chave da aplicação e rodar migrations:**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ```

6. **Criar link para arquivos públicos (uploads):**
   ```bash
   ./vendor/bin/sail artisan storage:link
   ```

7. **Compilar o Frontend:**
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run build
   ```

8. **Acessar a aplicação:**
Abra no navegador: http://localhost

## ✅ Testes de Feature
Para rodar os testes (Sail/Docker):
```bash
./vendor/bin/sail artisan test
```

Para rodar apenas alguns testes:
```bash
./vendor/bin/sail artisan test --filter=ProductApiTest
./vendor/bin/sail artisan test --filter=AuthApiTest
```

## 📄 Documentacao da API (Scribe)
Gerar a documentacao:
```bash
./vendor/bin/sail artisan scribe:generate
```

Acesse em:
- http://localhost/docs

Para habilitar o "Try It Out" com token:
- Preencha `SCRIBE_AUTH_KEY` no `.env` com `Bearer SEU_TOKEN`

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
   

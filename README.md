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

2. **Configurar o Ambiente:**
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

6. **Compilar o Frontend:**
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run build
   ```

7. **Acessar a aplicação:**
Abra no navegador: http://localhost

## 🔑 Acesso Padrão:
O sistema já conta com um usuário padrão criado via Seeder:

Email: admin@admin.com  
Password: password

## 👨‍💻 Autor
Desenvolvido por Lucas Patrão
   

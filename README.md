# Guia Completo: Instalação do Laravel no Windows para Railway

Este guia documenta todos os passos necessários para instalar e configurar o Laravel no Windows e prepará-lo para deploy no Railway. Inclui também a resolução de erros comuns encontrados durante a instalação.

## Passo 1: Criar Conta e Configurar Railway

1. Acede ao [Railway.app](https://railway.app) e cria uma conta (podes usar GitHub para login).
2. No dashboard, clica em **"New Project"**.
3. Seleciona **"Deploy from GitHub repo"**.
4. Liga o Railway ao teu repositório GitHub e escolhe o repositório do projeto Laravel.

## Passo 2: Criar Base de Dados no Railway

1. No menu lateral do Railway, clica em **"Databases"** e depois **"New Database"**.
2. Escolhe **MySQL** (versão 5.7 ou superior, para compatibilidade com Laravel 10).
3. O Railway vai gerar as credenciais automaticamente. **Guarda estas informações**, pois vais precisar delas para o `.env`.

## Passo 3: Instalar Chocolatey e Scoop (Se Necessário)

Antes de instalar o PHP e outras dependências, precisamos do Chocolatey e, opcionalmente, do Scoop para gerir pacotes no Windows.

### 3.1 Instalar Chocolatey

1. Abre o PowerShell como Administrador.
2. Executa o seguinte comando para instalar o Chocolatey:
    ```powershell
    Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
    ```
3. Fecha e reabre o PowerShell, depois testa se o Chocolatey foi instalado:
    ```powershell
    choco -v
    ```

### 3.2 Instalar Scoop (Opcional, para Railway CLI)

1. No PowerShell, executa:
    ```powershell
    iwr -useb get.scoop.sh | iex
    ```
2. Verifica a instalação:
    ```powershell
    scoop help
    ```
3. Se pretenderes usar a CLI do Railway, instala-a com:
    ```powershell
    scoop install railway
    ```

## Passo 4: Instalar PHP 8.3 e Dependências

O Laravel precisa de PHP 8.3 e de algumas extensões adicionais.

1. Instala o PHP com Chocolatey:
    ```powershell
    choco install php --version=8.3 --force
    ```
2. Verifica a instalação:
    ```powershell
    php -v
    ```
3. Ativa as extensões necessárias no `php.ini`.

## Passo 5: Criar o Projeto Laravel e Configurar

1. Entra na pasta do projeto:
    ```powershell
    cd C:\laravel-gestao-ferias-railway
    ```
2. Instala o Laravel:
    ```powershell
    composer create-project laravel/laravel . --remove-vcs
    ```
3. Gera a chave do Laravel:
    ```powershell
    php artisan key:generate
    ```

## Passo 6: Configurar o Railway e Deploy

1. Liga o Railway ao repositório GitHub.
2. No Railway, vai a **Settings > Variables** e adiciona as variáveis do `.env`, incluindo:
    ```ini
    APP_KEY=base64:gerar-uma-chave
    DB_CONNECTION=mysql
    DB_CONNECTION=mysql
    DB_HOST=monorail.proxy.rlwy.net
    DB_PORT=39513
    DB_DATABASE=railway
    DB_USERNAME=root
    DB_PASSWORD=<SENHA DEFINIDA>
    APP_URL=https://teu-projeto.up.railway.app
    ASSET_URL=https://teu-projeto.up.railway.app
    PORT=8080
    ```
3. Faz redeploy no Railway:
    ```powershell
    railway redeploy
    ```
4. Executa as migrações e seeders para popular a base de dados:
    ```powershell
    railway run php artisan migrate --force
    railway run php artisan db:seed --force
    ```

## Passo 7: Configurar a atribuição do domínio público

1. No Railway, vai a **"Settings" > "Networking"**.
2. Adiciona a porta definida no env (8080.
3. Segue as instruções para configurar os registos DNS no teu provedor de domínios (exemplo: Cloudflare, GoDaddy, etc.).
4. Verifica se o domínio está ativo e aponta corretamente para o Railway.

## Passo 8: Possíveis Erros e Soluções

### Erro 502 - Aplicativo não inicia corretamente

**Solução:**
```powershell
railway run php artisan config:clear
railway run php artisan cache:clear
railway run php artisan route:clear
railway run php artisan view:clear
```

### Erro de Base de Dados - Conexão recusada

**Solução:**
```powershell
railway run php artisan config:cache
```

### Erro 403 ou 404 - A aplicação não carrega

**Solução:**
```powershell
railway run php artisan storage:link
```

### Erro ao executar migrations ou seeders

**Solução:**
```powershell
railway run php artisan migrate:fresh --seed
railway run php artisan db:seed
```

Agora tens um guia completo para instalar e configurar Laravel no Railway! 🚀

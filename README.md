# Guia Completo: Instalação do Laravel no Windows para Railway

Este guia documenta todos os passos necessários para instalar e configurar o Laravel no Windows e prepará-lo para deploy no Railway. Inclui também a resolução de erros comuns encontrados durante a instalação.

## Passo 1: Instalar Chocolatey e Scoop (Se Necessário)
Antes de instalar o PHP e outras dependências, precisamos do Chocolatey e, opcionalmente, do Scoop para gerir pacotes no Windows.

### 1.1 Instalar Chocolatey
Chocolatey é um gestor de pacotes para Windows, usado para instalar PHP, Composer e outras ferramentas.

1. Abre o PowerShell como Administrador.
2. Executa o seguinte comando para instalar o Chocolatey:
   ```powershell
   Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
   ```
3. Fecha e reabre o PowerShell, depois testa se o Chocolatey foi instalado:
   ```powershell
   choco -v
   ```
4. Se devolver um número de versão, está pronto!

### 1.2 Instalar Scoop (Opcional, para Railway CLI)
Scoop é um gestor de pacotes alternativo, útil para instalar a CLI do Railway.

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
4. Se encontrares erros de instalação do scoop, tenta executar:
   ```powershell
   $env:SCOOP="C:\Users\teu-usuario\scoop"
   [System.Environment]::SetEnvironmentVariable("Path", $env:SCOOP+"\shims;"+[System.Environment]::GetEnvironmentVariable("Path", [System.EnvironmentVariableTarget]::User), [System.EnvironmentVariableTarget]::User)
   ```

## Passo 2: Instalar PHP 8.3 e Dependências

O Laravel precisa de PHP 8.3 e de algumas extensões adicionais.

### 2.1 Instalar PHP 8.3

1. Abre o PowerShell como Administrador e executa:
   ```powershell
   choco install php --version=8.3 --force
   ```
2. Verifica se o PHP foi instalado corretamente:
   ```powershell
   php -v
   ```
3. Se devolver algo como PHP 8.3.0, está tudo certo!
4. Caso o comando PHP não seja reconhecido, adiciona manualmente a pasta do PHP às variáveis de ambiente:
   ```powershell
   $env:Path += ";C:\tools\php83"
   ```

### 2.2 Ativar Extensões no PHP.ini

1. Abre o ficheiro de configuração do PHP:
   ```powershell
   notepad C:\tools\php83\php.ini
   ```
2. Remove o `;` no início das seguintes linhas para ativar as extensões necessárias:
   ```ini
   extension=bcmath
   extension=gd
   extension=intl
   extension=mbstring
   extension=pdo_mysql
   extension=zip
   extension=sodium
   extension=fileinfo
   ```
3. Guarda o ficheiro e fecha.
4. Atualiza as variáveis de ambiente:
   ```powershell
   refreshenv
   ```
5. Verifica se as extensões foram ativadas corretamente:
   ```powershell
   php -m
   ```
6. Se todas as extensões estiverem listadas, está pronto!

## Passo 3: Criar o Repositório no GitHub

1. Vai ao GitHub e cria um novo repositório.
2. Copia o link do repositório e configura localmente:
   ```bash
   git init
   git remote add origin https://github.com/teu-usuario/teu-repo.git
   git branch -M main
   git push -u origin main
   ```

## Passo 4: Criar o Projeto Laravel e Corrigir Problemas

1. Entra na pasta do projeto:
   ```bash
   cd C:\laravel-gestao-ferias-railway
   ```
2. Instala o Laravel:
   ```bash
   composer create-project laravel/laravel . --remove-vcs
   ```
3. Gera a chave do Laravel:
   ```bash
   php artisan key:generate
   ```

## Passo 5: Configurar o Railway e Corrigir Erros de Deploy

1. Liga o Railway ao repositório GitHub.
2. Em `Settings > Variables`, adiciona as variáveis do `.env`, incluindo:
   ```ini
   APP_KEY=base64:gerar-uma-chave
   DB_CONNECTION=mysql
   DB_HOST=monorail.proxy.rlwy.net
   DB_PORT=39513
   DB_DATABASE=railway
   DB_USERNAME=root
   DB_PASSWORD=sua-senha-aqui
   APP_URL=https://teu-projeto.up.railway.app
   ASSET_URL=https://teu-projeto.up.railway.app
   PORT=8080
   ```
3. Faz redeploy no Railway:
   ```bash
   railway redeploy
   ```
4. Se o Laravel não arrancar corretamente no Railway, tenta forçar a inicialização:
   ```bash
   railway run php artisan serve --host=0.0.0.0 --port=8080
   ```

Se houver erro **502** no Railway, verifica as variáveis de ambiente e ativa o **Public Networking**.

Agora tens um guia atualizado e completo para instalar e configurar Laravel no Railway! 🚀

# Guia Completo: Instalação do Laravel no Windows e Deploy no Railway

Este guia documenta todos os passos seguidos para instalar e configurar Laravel no Windows e preparar o deploy no Railway. Inclui também a resolução de erros comuns encontrados durante o processo.

## Passo 1: Configurar Ambiente de Desenvolvimento no Windows

Antes de instalar o Laravel, precisamos garantir que temos todas as dependências necessárias instaladas no Windows.

### Instalar Chocolatey

Chocolatey é um gestor de pacotes para Windows, usado para instalar PHP, Composer e outras ferramentas.

1. Abre o PowerShell como Administrador.
2. Executa o seguinte comando para instalar o Chocolatey:
   ```powershell
   Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
   ```
   *Este comando configura a política de execução do PowerShell e instala o Chocolatey.*
3. Fecha e reabre o PowerShell, depois testa se o Chocolatey foi instalado:
   ```powershell
   choco -v
   ```
   *Se devolver um número de versão, significa que a instalação foi bem-sucedida.*

### Instalar Scoop (Opcional, para Railway CLI)

Scoop é um gestor de pacotes alternativo, útil para instalar a CLI do Railway.

1. No PowerShell, executa:
   ```powershell
   iwr -useb get.scoop.sh | iex
   ```
   *Este comando instala o Scoop no sistema.*
2. Verifica a instalação:
   ```powershell
   scoop help
   ```
3. Se pretenderes usar a CLI do Railway, instala-a com:
   ```powershell
   scoop install railway
   ```
   *Este comando instala a ferramenta CLI do Railway.*

## Passo 2: Instalar PHP 8.3 e Dependências

O Laravel precisa de PHP 8.3 e de algumas extensões adicionais.

### Instalar PHP 8.3

1. Abre o PowerShell como Administrador e executa:
   ```powershell
   choco install php --version=8.3 --force
   ```
   *Isto instala a versão 8.3 do PHP.*
2. Verifica se o PHP foi instalado corretamente:
   ```powershell
   php -v
   ```
   *Se devolver algo como `PHP 8.3.0`, está tudo certo!*

## Passo 3: Configurar o Railway

1. Acede a [Railway](https://railway.app/).
2. Cria um novo Projeto.
3. Adiciona um Serviço MySQL.
4. Copia as credenciais do MySQL e define no `.env`:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=monorail.proxy.rlwy.net
   DB_PORT=39513
   DB_DATABASE=railway
   DB_USERNAME=root
   DB_PASSWORD=sua-senha-aqui
   ```
5. Instala a CLI do Railway e inicia sessão:
   ```bash
   railway login
   ```
   *Este comando autentica-te na Railway CLI.*
   ```bash
   railway link
   ```
   *Este comando associa a pasta do teu projeto ao Railway. Se tiveres múltiplos projetos, será necessário selecionar o correto.*
   ```bash
   railway up
   ```
   *Este comando faz o deploy inicial da aplicação no Railway.*

6. Gere um domínio público e adiciona ao `.env`:
   ```ini
   APP_URL=https://teu-projeto.up.railway.app
   ```
   *Isto define a URL da aplicação no Railway.*

## Passo 4: Testar e Resolver Erros

Se a aplicação não estiver acessível:
```bash
railway logs
```
*Verifica os logs da aplicação para identificar possíveis erros.*

Se houver erro **502** no Railway:
```bash
railway redeploy
```
*Reinicia o deploy da aplicação para corrigir falhas.*

Caso Laravel não reconheça as configurações:
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```
*Estes comandos limpam e regeneram a cache de configuração do Laravel.*

Agora o Laravel está configurado corretamente para o Railway! 🚀

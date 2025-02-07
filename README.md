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
3. Fecha e reabre o PowerShell, depois testa se o Chocolatey foi instalado:
   ```powershell
   choco -v
   ```
4. Se devolver um número de versão, está pronto!

### Instalar Scoop (Opcional, para Railway CLI)

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
   $env:SCOOP='C:\Users\teu-usuario\scoop'
   [System.Environment]::SetEnvironmentVariable('Path', $env:SCOOP+'\shims;'+[System.EnvironmentVariableTarget]::User), [System.EnvironmentVariableTarget]::User)
   ```

## Passo 2: Instalar PHP 8.3 e Dependências

O Laravel precisa de PHP 8.3 e de algumas extensões adicionais.

### Instalar PHP 8.3

1. Abre o PowerShell como Administrador e executa:
   ```powershell
   choco install php --version=8.3 --force
   ```
2. Verifica se o PHP foi instalado corretamente:
   ```powershell
   php -v
   ```
3. Se devolver algo como `PHP 8.3.0`, está tudo certo!
4. Caso o comando `php` não seja reconhecido, adiciona manualmente a pasta do PHP às variáveis de ambiente:
   ```powershell
   $env:Path += ";C:\tools\php83"
   ```

### Ativar Extensões no PHP.ini

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
3. Guarda o ficheiro (`Ctrl + S`) e fecha.
4. Atualiza as variáveis de ambiente:
   ```powershell
   refreshenv
   ```
5. Verificar se as extensões foram ativadas corretamente:
   ```powershell
   php -m
   ```
6. Se todas as extensões estiverem listadas, está pronto!
7. Caso o `intl` não esteja ativado, reinstala o PHP com:
   ```powershell
   choco upgrade php --force
   ```

## Passo 3: Criar Banco de Dados no Railway

1. Acede ao Railway.
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
5. Verifica a conexão executando:
   ```bash
   mysql -h monorail.proxy.rlwy.net -P 39513 -u root -p railway
   ```
6. Se conseguires conectar, o banco está pronto!

## Passo 4: Criar Domínio Público no Railway

1. No Railway, vai a **Settings > Generate Domain**.
2. Copia o domínio gerado e adiciona ao `.env`:
   ```ini
   APP_URL=https://teu-projeto.up.railway.app
   ```
3. Testa se a aplicação responde ao endereço gerado.

## Passo 5: Testar e Resolver Erros

Se a aplicação não estiver acessível:
```bash
railway logs
```

Se houver erro **502** no Railway:
```bash
railway redeploy
```

Caso Laravel não reconheça as configurações:
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

Agora o Laravel está configurado corretamente para o Railway! 🚀

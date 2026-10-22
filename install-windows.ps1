param(
    [int] $Port = 8000,
    [string] $GeminiApiKey = "",
    [switch] $SkipSystemDependencies,
    [switch] $NoStart,
    [switch] $NoBrowser
)

$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

$ProjectRoot = $PSScriptRoot
$EnvFile = Join-Path $ProjectRoot ".env"
$EnvExampleFile = Join-Path $ProjectRoot ".env.example"
$DatabaseFile = Join-Path $ProjectRoot "database\database.sqlite"
$LocalPhpIniDirectory = Join-Path $ProjectRoot ".windows\php"
$LocalPhpIniFile = Join-Path $LocalPhpIniDirectory "php.ini"

function Write-Step {
    param([string] $Message)

    Write-Host ""
    Write-Host "==> $Message" -ForegroundColor Cyan
}

function Write-Ok {
    param([string] $Message)

    Write-Host "OK: $Message" -ForegroundColor Green
}

function Test-Command {
    param([string] $Name)

    return $null -ne (Get-Command $Name -ErrorAction SilentlyContinue)
}

function Invoke-Native {
    param(
        [string] $Command,
        [string[]] $Arguments = @()
    )

    & $Command @Arguments

    if ($LASTEXITCODE -ne 0) {
        throw "Comando falhou com codigo $LASTEXITCODE`: $Command $($Arguments -join ' ')"
    }
}

function Update-CurrentPath {
    $machinePath = [Environment]::GetEnvironmentVariable("Path", "Machine")
    $userPath = [Environment]::GetEnvironmentVariable("Path", "User")
    $env:Path = "$machinePath;$userPath"
}

function Install-WingetPackage {
    param(
        [string] $Id,
        [string] $Name
    )

    Write-Step "Instalando $Name"
    Invoke-Native -Command "winget" -Arguments @("install", "--id", $Id, "--exact", "--accept-package-agreements", "--accept-source-agreements")
    Update-CurrentPath
}

function Ensure-Winget {
    if (Test-Command "winget") {
        return
    }

    throw @"
winget nao foi encontrado.
Instale o "Instalador de Aplicativo" pela Microsoft Store e execute este script novamente.
"@
}

function Ensure-SystemDependencies {
    if ($SkipSystemDependencies) {
        Write-Step "Pulando instalacao de dependencias do sistema"
        return
    }

    Ensure-Winget

    if (-not (Test-Command "php")) {
        Install-WingetPackage -Id "PHP.PHP.8.4" -Name "PHP 8.4"
    } else {
        Write-Ok "PHP ja esta instalado"
    }

    if (-not (Test-Command "composer")) {
        Install-WingetPackage -Id "Composer.Composer" -Name "Composer"
    } else {
        Write-Ok "Composer ja esta instalado"
    }

    if ((Test-Path (Join-Path $ProjectRoot "package.json")) -and ((-not (Test-Command "node")) -or (-not (Test-Command "npm")))) {
        Install-WingetPackage -Id "OpenJS.NodeJS.LTS" -Name "Node.js LTS"
    } elseif (Test-Path (Join-Path $ProjectRoot "package.json")) {
        Write-Ok "Node.js ja esta instalado"
    }
}

function Ensure-PhpVersion {
    Write-Step "Validando versao do PHP"

    $versionOutput = & php -r "echo PHP_VERSION;"
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel executar o PHP."
    }

    $version = [Version] $versionOutput

    if ($version.Major -lt 8 -or ($version.Major -eq 8 -and $version.Minor -lt 3)) {
        throw "Este projeto precisa de PHP 8.3 ou superior. Versao encontrada: $versionOutput"
    }

    Write-Ok "PHP $versionOutput"
}

function Get-PhpDirectory {
    $phpCommand = Get-Command "php" -ErrorAction Stop
    $phpDirectory = Split-Path $phpCommand.Source -Parent
    $phpBinary = & php -r "echo PHP_BINARY;"

    if ($LASTEXITCODE -eq 0 -and $phpBinary -and (Test-Path $phpBinary)) {
        $phpDirectory = Split-Path $phpBinary -Parent
    }

    return $phpDirectory
}

function Get-PhpIniFile {
    $phpDirectory = Get-PhpDirectory
    $iniOutput = & php --ini
    $configurationDirectory = $null
    $sourceIni = $null

    if (-not (Test-Path $LocalPhpIniDirectory)) {
        New-Item -ItemType Directory -Path $LocalPhpIniDirectory | Out-Null
    }

    if (Test-Path $LocalPhpIniFile) {
        return $LocalPhpIniFile
    }

    foreach ($line in $iniOutput) {
        if ($line -match "^Loaded Configuration File:\s+(.+)$") {
            $loadedIni = $Matches[1].Trim()

            if ($loadedIni -and $loadedIni -ne "(none)" -and (Test-Path $loadedIni)) {
                $sourceIni = $loadedIni
            }
        }

        if ($line -match "^Configuration File \(php.ini\) Path:\s+(.+)$") {
            $configurationDirectory = $Matches[1].Trim()
        }
    }

    $looksLikePhpDirectory = $false

    if ($configurationDirectory -and (Test-Path $configurationDirectory)) {
        $looksLikePhpDirectory = (Test-Path (Join-Path $configurationDirectory "php.ini-development")) `
            -or (Test-Path (Join-Path $configurationDirectory "php.ini-production")) `
            -or (Test-Path (Join-Path $configurationDirectory "ext"))
    }

    if ($looksLikePhpDirectory) {
        $phpDirectory = $configurationDirectory
    }

    $developmentIni = Join-Path $phpDirectory "php.ini-development"
    $productionIni = Join-Path $phpDirectory "php.ini-production"

    if (-not $sourceIni -and (Test-Path $developmentIni)) {
        $sourceIni = $developmentIni
    }

    if (-not $sourceIni -and (Test-Path $productionIni)) {
        $sourceIni = $productionIni
    }

    if ($sourceIni) {
        Copy-Item $sourceIni $LocalPhpIniFile
    } else {
        New-Item -ItemType File -Path $LocalPhpIniFile | Out-Null
    }

    return $LocalPhpIniFile
}

function Enable-PhpExtensionInIni {
    param(
        [string] $IniFile,
        [string] $ExtensionDirectory,
        [string] $Extension
    )

    $extensionDll = Join-Path $ExtensionDirectory "php_$Extension.dll"

    if (-not (Test-Path $extensionDll)) {
        return
    }

    $content = Get-Content $IniFile -Raw
    $extensionPattern = [regex]::Escape($Extension)
    $disabledPattern = "(?m)^\s*;\s*extension\s*=\s*(php_)?$extensionPattern(\.dll)?\s*$"
    $enabledPattern = "(?m)^\s*extension\s*=\s*(php_)?$extensionPattern(\.dll)?\s*$"

    if ($content -match $enabledPattern) {
        return
    }

    if ($content -match $disabledPattern) {
        $content = [regex]::Replace($content, $disabledPattern, "extension=$Extension", 1)
    } else {
        $content += "`r`nextension=$Extension`r`n"
    }

    $utf8NoBom = New-Object System.Text.UTF8Encoding $false
    [System.IO.File]::WriteAllText($IniFile, $content, $utf8NoBom)
}

function Ensure-PhpIniConfiguration {
    Write-Step "Configurando php.ini"

    $iniFile = Get-PhpIniFile
    $phpDirectory = Get-PhpDirectory
    $extensionDirectory = Join-Path $phpDirectory "ext"
    $content = Get-Content $iniFile -Raw

    if ($content -match "(?m)^\s*;?\s*extension_dir\s*=") {
        $content = [regex]::Replace($content, "(?m)^\s*;?\s*extension_dir\s*=.*$", "extension_dir = `"$extensionDirectory`"", 1)
    } else {
        $content += "`r`nextension_dir = `"$extensionDirectory`"`r`n"
    }

    $utf8NoBom = New-Object System.Text.UTF8Encoding $false
    [System.IO.File]::WriteAllText($iniFile, $content, $utf8NoBom)

    foreach ($extension in @("curl", "fileinfo", "mbstring", "openssl", "pdo_sqlite", "sqlite3")) {
        Enable-PhpExtensionInIni -IniFile $iniFile -ExtensionDirectory $extensionDirectory -Extension $extension
    }

    $env:PHPRC = $LocalPhpIniDirectory
    Write-Ok "php.ini pronto em $iniFile"
}

function Ensure-PhpExtensions {
    Write-Step "Validando extensoes do PHP"

    $requiredExtensions = @("ctype", "curl", "dom", "fileinfo", "filter", "hash", "mbstring", "openssl", "pdo", "pdo_sqlite", "session", "tokenizer", "xml")
    $missingExtensions = @()

    foreach ($extension in $requiredExtensions) {
        $loaded = php -r "exit(extension_loaded('$extension') ? 0 : 1);"

        if ($LASTEXITCODE -ne 0) {
            $missingExtensions += $extension
        }
    }

    if ($missingExtensions.Count -gt 0) {
        throw "Extensoes PHP ausentes: $($missingExtensions -join ', '). Habilite-as no php.ini e execute o script novamente."
    }

    Write-Ok "Extensoes PHP necessarias estao habilitadas"
}

function Ensure-EnvironmentFile {
    Write-Step "Preparando arquivo .env"

    if (-not (Test-Path $EnvFile)) {
        if (-not (Test-Path $EnvExampleFile)) {
            throw ".env.example nao encontrado."
        }

        Copy-Item $EnvExampleFile $EnvFile
        Write-Ok ".env criado a partir do .env.example"
    } else {
        Write-Ok ".env ja existe"
    }

    $envContent = Get-Content $EnvFile -Raw
    $envContent = $envContent -replace "(?m)^APP_URL=.*$", "APP_URL=http://localhost:$Port"
    $envContent = $envContent -replace "(?m)^DB_CONNECTION=.*$", "DB_CONNECTION=sqlite"
    $envContent = $envContent -replace "(?m)^#?\s*DB_HOST=.*$\r?\n?", ""
    $envContent = $envContent -replace "(?m)^#?\s*DB_PORT=.*$\r?\n?", ""
    $envContent = $envContent -replace "(?m)^#?\s*DB_DATABASE=.*$\r?\n?", ""
    $envContent = $envContent -replace "(?m)^#?\s*DB_USERNAME=.*$\r?\n?", ""
    $envContent = $envContent -replace "(?m)^#?\s*DB_PASSWORD=.*$\r?\n?", ""

    if ($GeminiApiKey.Trim().Length -gt 0) {
        if ($envContent -match "(?m)^GEMINI_API_KEY=") {
            $envContent = $envContent -replace "(?m)^GEMINI_API_KEY=.*$", "GEMINI_API_KEY=$GeminiApiKey"
        } else {
            $envContent += "`r`nGEMINI_API_KEY=$GeminiApiKey`r`n"
        }
    }

    $utf8NoBom = New-Object System.Text.UTF8Encoding $false
    [System.IO.File]::WriteAllText($EnvFile, $envContent, $utf8NoBom)
}

function Ensure-Database {
    Write-Step "Preparando banco SQLite"

    $databaseDirectory = Split-Path $DatabaseFile -Parent

    if (-not (Test-Path $databaseDirectory)) {
        New-Item -ItemType Directory -Path $databaseDirectory | Out-Null
    }

    if (-not (Test-Path $DatabaseFile)) {
        New-Item -ItemType File -Path $DatabaseFile | Out-Null
        Write-Ok "database/database.sqlite criado"
    } else {
        Write-Ok "database/database.sqlite ja existe"
    }
}

function Install-ProjectDependencies {
    Write-Step "Instalando dependencias PHP"
    Invoke-Native -Command "composer" -Arguments @("install")

    if (Test-Path (Join-Path $ProjectRoot "package.json")) {
        Write-Step "Instalando dependencias JavaScript"
        Invoke-Native -Command "npm" -Arguments @("install")
    }
}

function Prepare-Laravel {
    Write-Step "Preparando Laravel"

    $envContent = Get-Content $EnvFile -Raw

    if ($envContent -match "(?m)^APP_KEY=base64:.+") {
        Write-Ok "APP_KEY ja existe"
    } else {
        Invoke-Native -Command "php" -Arguments @("artisan", "key:generate", "--force")
    }

    Invoke-Native -Command "php" -Arguments @("artisan", "config:clear")
    Invoke-Native -Command "php" -Arguments @("artisan", "migrate", "--force")

    Write-Ok "Aplicacao preparada"
}

function Start-LocalServers {
    if ($NoStart) {
        Write-Step "Inicializacao dos servidores locais pulada"
        return
    }

    Write-Step "Subindo servidores locais"

    $serverCommand = "cd /d `"$ProjectRoot`"; php artisan serve --host=127.0.0.1 --port=$Port"
    Start-Process -FilePath "cmd.exe" -ArgumentList "/k", $serverCommand -WindowStyle Normal

    if (Test-Path (Join-Path $ProjectRoot "package.json")) {
        $frontCommand = "cd /d `"$ProjectRoot`"; npm run dev"
        Start-Process -FilePath "cmd.exe" -ArgumentList "/k", $frontCommand -WindowStyle Normal
    }

    $url = "http://localhost:$Port"
    Write-Ok "Servidor Laravel iniciado em $url"

    if (-not $NoBrowser) {
        Start-Process $url
    }
}

try {
    Set-Location $ProjectRoot

    Write-Host "Instalador Windows - Anomalias ETEC Lab" -ForegroundColor Yellow
    Write-Host "Projeto: $ProjectRoot"

    Ensure-SystemDependencies
    Ensure-PhpVersion
    Ensure-PhpIniConfiguration
    Ensure-PhpExtensions
    Ensure-EnvironmentFile
    Ensure-Database
    Install-ProjectDependencies
    Prepare-Laravel
    Start-LocalServers

    Write-Host ""
    Write-Host "Instalacao concluida." -ForegroundColor Green
}
catch {
    Write-Host ""
    Write-Host "A instalacao falhou:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host ""
    Write-Host "Depois de corrigir o problema, execute novamente:" -ForegroundColor Yellow
    Write-Host "powershell -ExecutionPolicy Bypass -File .\install-windows.ps1" -ForegroundColor Yellow
    exit 1
}

@echo off
setlocal

cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0install-windows.ps1" %*

if errorlevel 1 (
    echo.
    echo A instalacao falhou. Pressione qualquer tecla para fechar.
    pause >nul
    exit /b 1
)

echo.
echo Instalacao concluida. Pressione qualquer tecla para fechar este instalador.
pause >nul

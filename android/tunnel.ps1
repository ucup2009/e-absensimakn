# Menjalankan server Laravel + Cloudflare Quick Tunnel (HTTPS gratis).
# Pakai: powershell -ExecutionPolicy Bypass -File android\tunnel.ps1
param(
    [int]$Port = 8000,
    [string]$Php = "C:\laragon\bin\php\php-8.5.12-1-Win32-vs17-x64\php.exe"
)

$ErrorActionPreference = "Stop"
$root = Split-Path $PSScriptRoot -Parent
$tools = Join-Path $PSScriptRoot ".tools"
$cloudflared = Join-Path $tools "cloudflared.exe"

if (-not (Test-Path $cloudflared)) {
    New-Item -ItemType Directory -Force -Path $tools | Out-Null
    Write-Host "Mengunduh cloudflared..."
    & curl.exe -L --retry 2 -o $cloudflared -s "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe"
}

# Server Laravel (lewati bila port sudah dipakai).
$listening = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
if (-not $listening) {
    Write-Host "Menjalankan server Laravel di port $Port ..."
    Start-Process -FilePath $Php -ArgumentList "artisan", "serve", "--host=0.0.0.0", "--port=$Port" -WorkingDirectory $root
    Start-Sleep -Seconds 2
} else {
    Write-Host "Port $Port sudah dipakai, server dianggap menyala."
}

Write-Host "Membuka tunnel HTTPS... (tekan Ctrl+C untuk berhenti)"
Write-Host "Alamat https://....trycloudflare.com muncul di bawah. URL berubah setiap kali dijalankan."
& $cloudflared tunnel --url "http://localhost:$Port" --no-autoupdate

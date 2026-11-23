# Menyiapkan folder bersih untuk deploy ke Wasmer Edge.
#
# Pakai:
#   powershell -ExecutionPolicy Bypass -File deploy\wasmer-stage.ps1
#   wasmer deploy --dir "$env:TEMP\e-absensi-wasmer" --owner <akun> --app-name e-absensi
#
# Folder sumber di-copy tanpa: .git, node_modules, storage, public/storage,
# file .env, dan hasil cache. Tujuannya agar paket upload kecil dan tidak
# membawa kredensial lokal.

param(
    [string]$Target = (Join-Path $env:TEMP 'e-absensi-wasmer')
)

$ErrorActionPreference = 'Stop'

$root = Split-Path $PSScriptRoot -Parent

if (-not (Test-Path (Join-Path $root 'wasmer.toml'))) {
    throw "wasmer.toml tidak ditemukan di $root"
}

if (Test-Path $Target) {
    Remove-Item $Target -Recurse -Force
}

New-Item -ItemType Directory -Path $Target | Out-Null

$excludeDirs = @(
    '.git',
    'android',
    'deploy',
    'node_modules',
    'tests',
    '.idea',
    '.vscode',
    '.nova',
    '.zed',
    '.cursor',
    '.phpunit.cache',
    'storage',
    'public\storage',
    'public\download'
)

$excludeFiles = @(
    '.env',
    '.env.backup',
    '.env.production',
    '*.log',
    '.phpunit.result.cache',
    'Thumbs.db'
)

robocopy $root $Target /E /NFL /NDL /NJH /NJS /XD $excludeDirs /XF $excludeFiles | Out-Null

# Struktur storage minimal supaya Laravel tidak error saat menulis cache/view.
$requiredDirs = @(
    'bootstrap\cache',
    'storage\app\public\users',
    'storage\app\public\students',
    'storage\app\backups',
    'storage\framework\cache\data',
    'storage\framework\sessions',
    'storage\framework\views',
    'storage\logs'
)

foreach ($dir in $requiredDirs) {
    New-Item -ItemType Directory -Force -Path (Join-Path $Target $dir) | Out-Null
}

# Jangan bawa config/route/view cache dari mesin lokal.
Get-ChildItem (Join-Path $Target 'bootstrap\cache') -Filter '*.php' -ErrorAction SilentlyContinue | Remove-Item -Force
Get-ChildItem (Join-Path $Target 'storage\framework\views') -Filter '*.php' -ErrorAction SilentlyContinue | Remove-Item -Force

Copy-Item (Join-Path $root 'app.yaml') $Target -Force
Copy-Item (Join-Path $root 'wasmer.toml') $Target -Force

$size = Get-ChildItem $Target -Recurse -File | Measure-Object Length -Sum

"Folder siap : $Target"
"Ukuran      : $([math]::Round($size.Sum / 1MB, 1)) MB ($($size.Count) file)"
"Deploy      : wasmer deploy --dir `"$Target`" --owner <akun-wasmer> --app-name e-absensi"

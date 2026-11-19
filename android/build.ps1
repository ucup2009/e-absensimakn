param(
    [string]$ServerUrl = "",
    [string]$JavaHome = ""
)

$ErrorActionPreference = "Stop"

$root = $PSScriptRoot
$tools = Join-Path $root ".tools"
$buildTools = Join-Path $tools "build-tools"
$androidJar = Join-Path $tools "platform\android.jar"
$build = Join-Path $root "build"
$keystore = Join-Path (Split-Path $root -Parent) "storage\app\android\e-absensi.jks"
$keystorePass = "maknende2026"

function Find-Tool([string]$name) {
    $path = Join-Path $buildTools "$name.bat"
    if (Test-Path $path) { return $path }
    $exe = Join-Path $buildTools "$name.exe"
    if (Test-Path $exe) { return $exe }
    throw "Tidak menemukan $name di $buildTools"
}

if (-not (Test-Path (Join-Path $buildTools "aapt2.exe"))) {
    throw "Android build-tools belum tersedia di $buildTools (lihat README)."
}
if (-not (Test-Path $androidJar)) {
    throw "android.jar belum tersedia di $androidJar (lihat README)."
}

# JAVA_HOME sering menunjuk folder lama/tidak ada; cari JDK yang benar sebelum d8 & apksigner.
$javaHomeCandidates = @()
if ($JavaHome) { $javaHomeCandidates += $JavaHome }
if ($env:JAVA_HOME) { $javaHomeCandidates += $env:JAVA_HOME }
foreach ($jdkRoot in @("C:\Program Files\Java", "C:\Program Files (x86)\Java", "C:\Program Files\Eclipse Adoptium", "C:\laragon\bin\java")) {
    if (Test-Path $jdkRoot) {
        $javaHomeCandidates += (Get-ChildItem $jdkRoot -Directory -ErrorAction SilentlyContinue | ForEach-Object { $_.FullName })
    }
}

$javaHome = $javaHomeCandidates | Where-Object { $_ -and (Test-Path (Join-Path $_ "bin\javac.exe")) } | Select-Object -First 1
if (-not $javaHome) { throw "JDK (javac) tidak ditemukan. Pasang JDK 17+ lalu jalankan ulang." }
$env:JAVA_HOME = $javaHome
$javacExe = Join-Path $javaHome "bin\javac.exe"
$keytoolExe = Join-Path $javaHome "bin\keytool.exe"

if ($ServerUrl) {
    $stringsPath = Join-Path $root "res\values\strings.xml"
    $text = [System.IO.File]::ReadAllText($stringsPath)
    $updated = [regex]::Replace($text, '(?<=<string name="server_url">)[^<]+', $ServerUrl)
    [System.IO.File]::WriteAllText($stringsPath, $updated, (New-Object System.Text.UTF8Encoding($false)))
    Write-Host "server_url -> $ServerUrl"
}

Write-Host "== Membersihkan folder build =="
Remove-Item $build -Recurse -Force -ErrorAction SilentlyContinue
New-Item -ItemType Directory -Force -Path (Join-Path $build "gen"), (Join-Path $build "classes"), (Join-Path $build "dex") | Out-Null

$aapt2 = Join-Path $buildTools "aapt2.exe"
$d8 = Find-Tool "d8"
$zipalign = Join-Path $buildTools "zipalign.exe"
$apksigner = Find-Tool "apksigner"

Write-Host "== Compile resource =="
& $aapt2 compile --dir (Join-Path $root "res") -o (Join-Path $build "res.zip")
if ($LASTEXITCODE -ne 0) { throw "aapt2 compile gagal" }

Write-Host "== Link resource + assets =="
& $aapt2 link `
    -o (Join-Path $build "unsigned.apk") `
    -I $androidJar `
    --manifest (Join-Path $root "AndroidManifest.xml") `
    --java (Join-Path $build "gen") `
    -A (Join-Path $root "assets") `
    --min-sdk-version 24 `
    --target-sdk-version 34 `
    (Join-Path $build "res.zip")
if ($LASTEXITCODE -ne 0) { throw "aapt2 link gagal" }

Write-Host "== Compile Java =="
$sources = @()
$sources += (Get-ChildItem (Join-Path $build "gen") -Recurse -Filter *.java | ForEach-Object { $_.FullName })
$sources += (Get-ChildItem (Join-Path $root "java") -Recurse -Filter *.java | ForEach-Object { $_.FullName })

$javac = $javacExe
if (-not (Test-Path $javac)) { throw "javac tidak ditemukan di $javac" }

& $javac -source 8 -target 8 -encoding UTF-8 -classpath $androidJar -d (Join-Path $build "classes") $sources
if ($LASTEXITCODE -ne 0) { throw "javac gagal" }

Write-Host "== Dex =="
$classes = Get-ChildItem (Join-Path $build "classes") -Recurse -Filter *.class | ForEach-Object { $_.FullName }
& $d8 --lib $androidJar --min-api 24 --output (Join-Path $build "dex") $classes
if ($LASTEXITCODE -ne 0) { throw "d8 gagal" }

Write-Host "== Sisipkan classes.dex =="
Add-Type -AssemblyName System.IO.Compression.FileSystem
$apkPath = Join-Path $build "unsigned.apk"
$zip = [System.IO.Compression.ZipFile]::Open($apkPath, 'Update')
$existing = $zip.GetEntry("classes.dex")
if ($existing) { $existing.Delete() }
[System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
    $zip,
    (Join-Path $build "dex\classes.dex"),
    "classes.dex",
    [System.IO.Compression.CompressionLevel]::NoCompression
) | Out-Null
$zip.Dispose()

Write-Host "== Align + sign =="
& $zipalign -f 4 $apkPath (Join-Path $build "aligned.apk")
if ($LASTEXITCODE -ne 0) { throw "zipalign gagal" }

New-Item -ItemType Directory -Force -Path (Split-Path $keystore -Parent) | Out-Null
if (-not (Test-Path $keystore)) {
    if (-not (Test-Path $keytoolExe)) { throw "keytool tidak ditemukan di $keytoolExe" }
    & $keytoolExe -genkeypair -keystore $keystore -alias eabsensi -keyalg RSA -keysize 2048 `
        -validity 10950 -storepass $keystorePass -keypass $keystorePass `
        -dname "CN=E-Absensi MAKN Ende, OU=MAKN Ende, O=Madrasah, L=Ende, ST=Nusa Tenggara Timur, C=ID"
    if ($LASTEXITCODE -ne 0) { throw "pembuatan keystore gagal" }
    Write-Host "Keystore baru dibuat: $keystore (password: $keystorePass)"
}

$outApk = Join-Path $build "e-absensi.apk"
& $apksigner sign --ks $keystore --ks-key-alias eabsensi `
    --ks-pass "pass:$keystorePass" --key-pass "pass:$keystorePass" `
    --out $outApk (Join-Path $build "aligned.apk")
if ($LASTEXITCODE -ne 0) { throw "apksigner gagal" }

& $apksigner verify --print-certs $outApk

Write-Host ""
Write-Host "APK siap: $outApk"
Write-Host "Ukuran : $([math]::Round((Get-Item $outApk).Length / 1MB, 2)) MB"

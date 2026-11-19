# Membuat ikon launcher Android dari logo madrasah.
# Jalankan: powershell -ExecutionPolicy Bypass -File android\make-icons.ps1
param(
    [string]$Source = "",
    [string]$Res = ""
)

$ErrorActionPreference = "Stop"
Add-Type -AssemblyName System.Drawing

$root = Split-Path $PSScriptRoot -Parent
if (-not $Source) { $Source = Join-Path $root "public\images\logo.png" }
if (-not $Res) { $Res = Join-Path $PSScriptRoot "res" }
if (-not (Test-Path $Source)) { throw "Logo tidak ditemukan: $Source" }

$src = [System.Drawing.Bitmap]::FromFile((Resolve-Path $Source))

# Cari batas gambar yang benar-benar berisi (logo punya margin transparan).
$minX = $src.Width; $minY = $src.Height; $maxX = -1; $maxY = -1
for ($y = 0; $y -lt $src.Height; $y++) {
    for ($x = 0; $x -lt $src.Width; $x++) {
        if ($src.GetPixel($x, $y).A -gt 16) {
            if ($x -lt $minX) { $minX = $x }
            if ($y -lt $minY) { $minY = $y }
            if ($x -gt $maxX) { $maxX = $x }
            if ($y -gt $maxY) { $maxY = $y }
        }
    }
}
if ($maxX -lt 0) { throw "Logo kosong / transparan semua." }

$cropRect = New-Object System.Drawing.Rectangle $minX, $minY, ($maxX - $minX + 1), ($maxY - $minY + 1)
Write-Host "Area logo: $($cropRect.Width)x$($cropRect.Height) (dari $($src.Width)x$($src.Height))"

$densities = [ordered]@{ mdpi = 1.0; hdpi = 1.5; xhdpi = 2.0; xxhdpi = 3.0; xxxhdpi = 4.0 }

function New-Canvas([int]$size) {
    $bmp = New-Object System.Drawing.Bitmap $size, $size, ([System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
    return @($bmp, $g)
}

function Draw-Logo($g, [int]$size, [double]$fraction) {
    # Sesuaikan ukuran tanpa merusak rasio (contain), lalu tengahkan.
    $box = $size * $fraction
    $ratio = [Math]::Min($box / $cropRect.Width, $box / $cropRect.Height)
    $w = [int][Math]::Round($cropRect.Width * $ratio)
    $h = [int][Math]::Round($cropRect.Height * $ratio)
    $x = [int][Math]::Round(($size - $w) / 2)
    $y = [int][Math]::Round(($size - $h) / 2)
    $dest = New-Object System.Drawing.Rectangle $x, $y, $w, $h
    $g.DrawImage($src, $dest, $cropRect, [System.Drawing.GraphicsUnit]::Pixel)
}

function New-RoundedPath([int]$size, [double]$radiusRatio) {
    $r = [single]($size * $radiusRatio)
    $path = New-Object System.Drawing.Drawing2D.GraphicsPath
    $d = $r * 2
    $path.AddArc(0, 0, $d, $d, 180, 90)
    $path.AddArc($size - $d, 0, $d, $d, 270, 90)
    $path.AddArc($size - $d, $size - $d, $d, $d, 0, 90)
    $path.AddArc(0, $size - $d, $d, $d, 90, 90)
    $path.CloseFigure()
    return $path
}

foreach ($name in $densities.Keys) {
    $scale = $densities[$name]
    $dir = Join-Path $Res "mipmap-$name"
    New-Item -ItemType Directory -Force -Path $dir | Out-Null

    # 1) Ikon biasa: kotak putih bersudut tumpul, logo memenuhi ~76%.
    $size = [int](48 * $scale)
    $pair = New-Canvas $size
    $bmp = $pair[0]; $g = $pair[1]
    $g.Clear([System.Drawing.Color]::Transparent)
    $path = New-RoundedPath $size 0.22
    $brush = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::White)
    $g.FillPath($brush, $path)
    Draw-Logo $g $size 0.76
    $bmp.Save((Join-Path $dir "ic_launcher.png"), [System.Drawing.Imaging.ImageFormat]::Png)
    $brush.Dispose(); $path.Dispose(); $g.Dispose(); $bmp.Dispose()

    # 2) Ikon bulat: lingkaran putih, logo ~74%.
    $pair = New-Canvas $size
    $bmp = $pair[0]; $g = $pair[1]
    $g.Clear([System.Drawing.Color]::Transparent)
    $brush = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::White)
    $g.FillEllipse($brush, 0, 0, $size, $size)
    Draw-Logo $g $size 0.68
    $bmp.Save((Join-Path $dir "ic_launcher_round.png"), [System.Drawing.Imaging.ImageFormat]::Png)
    $brush.Dispose(); $g.Dispose(); $bmp.Dispose()

    # 3) Lapisan depan adaptive icon: kanvas 108dp, logo di zona aman (~60%).
    $fgSize = [int](108 * $scale)
    $pair = New-Canvas $fgSize
    $bmp = $pair[0]; $g = $pair[1]
    $g.Clear([System.Drawing.Color]::Transparent)
    Draw-Logo $g $fgSize 0.46
    $bmp.Save((Join-Path $dir "ic_launcher_foreground.png"), [System.Drawing.Imaging.ImageFormat]::Png)
    $g.Dispose(); $bmp.Dispose()

    Write-Host "  mipmap-$name : $size px (adaptive $fgSize px)"
}

# deklarasi adaptive icon (Android 8+)
$anydpi = Join-Path $Res "mipmap-anydpi-v26"
New-Item -ItemType Directory -Force -Path $anydpi | Out-Null
$adaptive = @'
<?xml version="1.0" encoding="utf-8"?>
<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android">
    <background android:drawable="@android:color/white" />
    <foreground android:drawable="@mipmap/ic_launcher_foreground" />
</adaptive-icon>
'@
Set-Content (Join-Path $anydpi "ic_launcher.xml") $adaptive -Encoding UTF8
Set-Content (Join-Path $anydpi "ic_launcher_round.xml") $adaptive -Encoding UTF8

# hapus ikon lama yang tidak terpakai
$old = Join-Path $Res "drawable\app_icon.png"
if (Test-Path $old) { Remove-Item $old -Force }

$src.Dispose()
Write-Host "Selesai. Ikon ada di $Res\mipmap-*"

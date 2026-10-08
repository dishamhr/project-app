# =====================================================================
#  Task Reminder Mahasiswa - Skrip Verifikasi Runtime
#  Jalankan dari folder mana pun:
#     powershell -ExecutionPolicy Bypass -File scripts\verify.ps1
#  Opsional:
#     -SkipBuild    : lewati npm run build
#     -SkipServer   : lewati cek halaman lewat server dev
# =====================================================================

[CmdletBinding()]
param(
    [switch]$SkipBuild,
    [switch]$SkipServer
)

$ErrorActionPreference = 'Continue'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

$script:failed = @()

function Resolve-Php {
    $cmd = Get-Command php -ErrorAction SilentlyContinue
    if ($cmd) { return $cmd.Source }

    foreach ($candidate in @(
            'C:\src\php-8.4.15\php.exe',
            'C:\src\php-8.5.10\php.exe',
            'C:\xampp\php\php.exe',
            'C:\php\php.exe'
        )) {
        if (Test-Path $candidate) { return $candidate }
    }

    return $null
}

function Step {
    param(
        [string]$Label,
        [string]$File,
        [string[]]$Arguments
    )

    Write-Host ""
    Write-Host "==> $Label" -ForegroundColor Cyan
    & $File @Arguments
    $code = $LASTEXITCODE

    if ($null -ne $code -and $code -ne 0) {
        $script:failed += $Label
        Write-Host "    [GAGAL] $Label (exit code $code)" -ForegroundColor Red
    } else {
        Write-Host "    [OK] $Label" -ForegroundColor Green
    }
}

# ---------------------------------------------------------------------
# 0. Persiapan environment
# ---------------------------------------------------------------------
$php = Resolve-Php
if (-not $php) {
    Write-Host "PHP tidak ditemukan. Tambahkan php.exe ke PATH atau instal PHP." -ForegroundColor Red
    exit 1
}

Write-Host "Root  : $root" -ForegroundColor DarkGray
Write-Host "PHP   : $php" -ForegroundColor DarkGray

$envFile = Join-Path $root '.env'
if (-not (Test-Path $envFile)) {
    if (Test-Path (Join-Path $root '.env.example')) {
        Copy-Item (Join-Path $root '.env.example') $envFile
        Write-Host "    [.env] .env.example disalin ke .env" -ForegroundColor Yellow
    } else {
        Write-Host "    [.env] file .env tidak ada" -ForegroundColor Red
        $script:failed += '.env tidak tersedia'
    }
}

if (Test-Path $envFile) {
    $hasKey = Select-String -Path $envFile -Pattern '^APP_KEY=.\S' -Quiet
    if (-not $hasKey) {
        Step 'Generate APP_KEY' $php @('artisan', 'key:generate', '--force')
    }
}

# ---------------------------------------------------------------------
# 1. Migrasi database (SQLite)
# ---------------------------------------------------------------------
Step 'Migrasi database (php artisan migrate)' $php @('artisan', 'migrate', '--force')

# ---------------------------------------------------------------------
# 2. Test Pest
# ---------------------------------------------------------------------
Step 'Jalankan seluruh test (php artisan test)' $php @('artisan', 'test')

# ---------------------------------------------------------------------
# 3. Build aset (Vite + Tailwind)
# ---------------------------------------------------------------------
if (-not $SkipBuild) {
    $npm = Get-Command npm -ErrorAction SilentlyContinue
    if ($npm) {
        if (-not (Test-Path (Join-Path $root 'node_modules'))) {
            Step 'npm install' $npm.Source @('install', '--ignore-scripts')
        }
        Step 'Build aset frontend (npm run build)' $npm.Source @('run', 'build')
    } else {
        $script:failed += 'npm tidak ditemukan (build aset dilewati)'
        Write-Host "    [GAGAL] npm tidak ditemukan - build aset dilewati" -ForegroundColor Red
    }
}

# ---------------------------------------------------------------------
# 4. Cek halaman lewat server dev
# ---------------------------------------------------------------------
if (-not $SkipServer) {
    Write-Host ""
    Write-Host '==> Cek halaman lewat server dev (php artisan serve)' -ForegroundColor Cyan

    $port = 8000
    $server = Start-Process -FilePath $php `
        -ArgumentList @('artisan', 'serve', "--port=$port", '--host=127.0.0.1') `
        -PassThru -WindowStyle Hidden -WorkingDirectory $root

    Start-Sleep -Seconds 5

    $checks = @(
        @{ Url = '/login';    Expected = 200; Keterangan = 'Halaman login' },
        @{ Url = '/register'; Expected = 200; Keterangan = 'Halaman registrasi' },
        @{ Url = '/dashboard'; Expected = 302; Keterangan = 'Dashboard dialihkan ke login (tamunya)' },
        @{ Url = '/up';       Expected = 200; Keterangan = 'Health check Laravel' }
    )

    foreach ($check in $checks) {
        $url = "http://127.0.0.1:$port$($check.Url)"
        $status = 0

        try {
            $response = Invoke-WebRequest -Uri $url -UseBasicParsing -MaximumRedirection 0 -TimeoutSec 20 -ErrorAction Stop
            $status = [int]$response.StatusCode
        } catch {
            if ($_.Exception.Response) {
                try { $status = [int]$_.Exception.Response.StatusCode } catch { $status = 0 }
            } else {
                $status = 0
            }
        }

        if ($status -eq $check.Expected) {
            Write-Host "    [OK] $($check.Url) -> $status  ($($check.Keterangan))" -ForegroundColor Green
        } else {
            $script:failed += "HTTP $($check.Url)"
            Write-Host "    [GAGAL] $($check.Url) -> $status (diharapkan $($check.Expected))  ($($check.Keterangan))" -ForegroundColor Red
        }
    }

    # Hentikan server beserta child process-nya.
    & taskkill /PID $server.Id /T /F 2>$null | Out-Null
}

# ---------------------------------------------------------------------
# 5. Ringkasan
# ---------------------------------------------------------------------
Write-Host ""
Write-Host '======================================================' -ForegroundColor DarkGray

if ($script:failed.Count -eq 0) {
    Write-Host 'SEMUA LANGKAH LULUS - aplikasi siap dipakai.' -ForegroundColor Green
    Write-Host "Jalankan aplikasi dengan:  $php artisan serve" -ForegroundColor DarkGray
    exit 0
}

Write-Host 'ADA LANGKAH YANG GAGAL:' -ForegroundColor Red
$script:failed | ForEach-Object { Write-Host "  - $_" -ForegroundColor Red }
exit 1

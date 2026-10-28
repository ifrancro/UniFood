$ErrorActionPreference = 'Stop'
Set-Location 'C:\Users\HP\Desktop\semestre 6\SQA\unifood\UniFood'
$phpSqa = 'C:\Users\HP\Documents\Codex\2026-10-26\c-users-hp-desktop-semestre-6\work\runtime\php84\php.exe'
if (!(Test-Path $phpSqa)) { $phpSqa = (Get-Command php).Source }
$env:APP_ENV='testing'
$env:APP_URL='http://127.0.0.1:8012'
$env:E2E_URL=$env:APP_URL
$env:DB_CONNECTION='sqlite'
$runSqa=Join-Path (Get-Location) ('storage/framework/testing/sqa-'+(Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item $runSqa -ItemType Directory -Force | Out-Null
$env:DB_DATABASE=Join-Path $runSqa 'e2e.sqlite'
New-Item $env:DB_DATABASE -ItemType File | Out-Null
$env:SESSION_DRIVER='file'; $env:SESSION_COOKIE='unifood_sqa_8012'; $env:CACHE_STORE='array'
$cacheSqa='C:\Users\HP\Documents\Codex\2026-10-26\c-users-hp-desktop-semestre-6\work\runtime\cypress'
if(Test-Path $cacheSqa){$env:CYPRESS_CACHE_FOLDER=$cacheSqa}
& $phpSqa artisan migrate --seed --force
if($LASTEXITCODE -ne 0){throw 'No se pudo preparar la base aislada.'}
npm run build
if($LASTEXITCODE -ne 0){throw 'No se pudo compilar la interfaz.'}
$serverSqa=Start-Process -FilePath $phpSqa -ArgumentList @('artisan','serve','--host=127.0.0.1','--port=8012','--no-reload') -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $runSqa 'server.log') -RedirectStandardError (Join-Path $runSqa 'server-error.log')
try {
 $readySqa=$false
 for($iSqa=0;$iSqa -lt 30;$iSqa++){
  try { Invoke-WebRequest "$env:APP_URL/login" -UseBasicParsing | Out-Null; $readySqa=$true; break } catch {Start-Sleep 1}
 }
 if(!$readySqa){throw 'El servidor aislado no respondió. Consultar los logs.'}
 npm run test:cypress 2>&1 | Tee-Object (Join-Path $runSqa 'cypress.log')
 $cypressSqa=$LASTEXITCODE
 npm run test:selenium 2>&1 | Tee-Object (Join-Path $runSqa 'selenium.log')
 $seleniumSqa=$LASTEXITCODE
 Write-Host "Cypress: $cypressSqa | Selenium: $seleniumSqa (0 significa aprobado). Logs: $runSqa"
} finally { if(!$serverSqa.HasExited){Stop-Process -Id $serverSqa.Id} }

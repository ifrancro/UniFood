param([string]$Proyecto = 'C:\Users\HP\Desktop\semestre 6\SQA\unifood\UniFood')
$ErrorActionPreference = 'Stop'
$nombresSqa = @('APP_ENV','APP_URL','E2E_URL','DB_CONNECTION','DB_DATABASE','SESSION_DRIVER','SESSION_COOKIE','CACHE_STORE','CYPRESS_CACHE_FOLDER')
$anterioresSqa = @{}
foreach($nombreSqa in $nombresSqa){$anterioresSqa[$nombreSqa]=[Environment]::GetEnvironmentVariable($nombreSqa,'Process')}
$serverSqa = $null
Push-Location $Proyecto
try {
    $phpSqa = 'C:\Users\HP\Documents\Codex\2026-10-26\c-users-hp-desktop-semestre-6\work\runtime\php84\php.exe'
    if(!(Test-Path $phpSqa)){$phpSqa=(Get-Command php).Source}
    & $phpSqa -r 'exit(version_compare(PHP_VERSION,"8.4.1",">=") ? 0 : 1);'
    if($LASTEXITCODE -ne 0){throw 'Se necesita PHP 8.4.1 o posterior.'}
    if(Get-NetTCPConnection -LocalPort 8012 -State Listen -ErrorAction SilentlyContinue){throw 'El puerto 8012 está ocupado; detener esta ejecución sin modificar ese servidor.'}
    $env:APP_ENV='testing';$env:APP_URL='http://127.0.0.1:8012';$env:E2E_URL=$env:APP_URL
    $env:DB_CONNECTION='sqlite';$env:SESSION_DRIVER='file';$env:SESSION_COOKIE='unifood_sqa_8012';$env:CACHE_STORE='array'
    $runSqa=Join-Path (Get-Location) ('storage/framework/testing/sqa-'+[guid]::NewGuid().ToString('N'))
    New-Item $runSqa -ItemType Directory -Force | Out-Null
    $env:DB_DATABASE=Join-Path $runSqa 'e2e.sqlite'
    New-Item $env:DB_DATABASE -ItemType File | Out-Null
    $cacheSqa='C:\Users\HP\Documents\Codex\2026-10-26\c-users-hp-desktop-semestre-6\work\runtime\cypress'
    if(Test-Path $cacheSqa){$env:CYPRESS_CACHE_FOLDER=$cacheSqa}
    & $phpSqa artisan migrate --seed --force
    if($LASTEXITCODE -ne 0){throw 'No se pudo preparar la base aislada.'}
    npm run build
    if($LASTEXITCODE -ne 0){throw 'No se pudo compilar la interfaz.'}
    npx cypress install
    if($LASTEXITCODE -ne 0){throw 'No se pudo instalar el binario Cypress.'}
    $serverSqa=Start-Process -FilePath $phpSqa -ArgumentList @('-S','127.0.0.1:8012','-t','public','vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php') -WorkingDirectory $Proyecto -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $runSqa 'server.log') -RedirectStandardError (Join-Path $runSqa 'server-error.log')
    $readySqa=$false
    for($iSqa=0;$iSqa -lt 30;$iSqa++){
        if($serverSqa.HasExited){throw 'El servidor no pudo iniciarse. Consultar los logs.'}
        try{Invoke-WebRequest "$env:APP_URL/login" -UseBasicParsing | Out-Null;$readySqa=$true;break}catch{Start-Sleep 1}
    }
    if(!$readySqa){throw 'El servidor aislado no respondió.'}
    npm run test:cypress 2>&1 | Tee-Object (Join-Path $runSqa 'cypress.log')
    $cypressSqa=$LASTEXITCODE
    npm run test:selenium 2>&1 | Tee-Object (Join-Path $runSqa 'selenium.log')
    $seleniumSqa=$LASTEXITCODE
    Write-Host "Cypress: $cypressSqa | Selenium: $seleniumSqa (0 significa aprobado). Logs: $runSqa"
    if($cypressSqa -ne 0 -or $seleniumSqa -ne 0){throw 'Alguna suite no aprobó. Revisar los reportes.'}
} finally {
    if($null -ne $serverSqa -and !$serverSqa.HasExited){Stop-Process -Id $serverSqa.Id}
    foreach($nombreSqa in $nombresSqa){[Environment]::SetEnvironmentVariable($nombreSqa,$anterioresSqa[$nombreSqa],'Process')}
    Pop-Location
}

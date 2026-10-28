param(
    [string]$Proyecto = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path,
    [string]$Salida = 'evidencias_sqa/automatizacion/2026-10-28'
)
$ErrorActionPreference = 'Stop'
$nombresSqa = @('APP_ENV','APP_URL','E2E_URL','DB_CONNECTION','DB_DATABASE','DB_URL','SESSION_DRIVER','SESSION_COOKIE','CACHE_STORE','CYPRESS_CACHE_FOLDER','SQA_OUTPUT_DIR')
$anterioresSqa = @{}
foreach($nombreSqa in $nombresSqa){$anterioresSqa[$nombreSqa]=[Environment]::GetEnvironmentVariable($nombreSqa,'Process')}
$serverSqa = $null
Push-Location $Proyecto
try {
    $phpSqa = (& php -r 'echo PHP_BINARY;').Trim()
    & $phpSqa -r 'exit(PHP_VERSION_ID >= 80401 ? 0 : 1);'
    if($LASTEXITCODE -ne 0){throw 'Se necesita PHP 8.4.1 o posterior.'}
    $cacheInfoSqa = (& $phpSqa artisan about --only=cache --json | ConvertFrom-Json)
    if($LASTEXITCODE -ne 0 -or $cacheInfoSqa.cache.config -ne $false){throw 'La configuracion esta cacheada o no pudo verificarse. El runner se detuvo antes de migrar para proteger la base habitual.'}
    if(Get-NetTCPConnection -LocalPort 8012 -State Listen -ErrorAction SilentlyContinue){throw 'El puerto 8012 está ocupado; detener esta ejecución sin modificar ese servidor.'}
    $env:APP_ENV='testing';$env:APP_URL='http://127.0.0.1:8012';$env:E2E_URL=$env:APP_URL
    $env:DB_CONNECTION='sqlite';$env:SESSION_DRIVER='file';$env:SESSION_COOKIE='unifood_sqa_8012';$env:CACHE_STORE='database'
    $env:DB_URL='null'
    $env:SQA_OUTPUT_DIR=Join-Path $Proyecto $Salida
    New-Item $env:SQA_OUTPUT_DIR -ItemType Directory -Force | Out-Null
    $runSqa=Join-Path (Get-Location) ('storage/framework/testing/sqa-'+[guid]::NewGuid().ToString('N'))
    New-Item $runSqa -ItemType Directory -Force | Out-Null
    $env:DB_DATABASE=Join-Path $runSqa 'e2e.sqlite'
    New-Item $env:DB_DATABASE -ItemType File | Out-Null
    & $phpSqa artisan migrate --seed --force --no-interaction
    if($LASTEXITCODE -ne 0){throw 'No se pudo preparar la base aislada.'}
    npm run build
    if($LASTEXITCODE -ne 0){throw 'No se pudo compilar la interfaz.'}
    npx cypress install
    if($LASTEXITCODE -ne 0){throw 'No se pudo instalar el binario Cypress.'}
    $publicSqa=Join-Path $Proyecto 'public'
    $serverSqa=Start-Process -FilePath $phpSqa -ArgumentList @('-S','127.0.0.1:8012','-t','.','../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php') -WorkingDirectory $publicSqa -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $runSqa 'server.log') -RedirectStandardError (Join-Path $runSqa 'server-error.log')
    $readySqa=$false
    for($iSqa=0;$iSqa -lt 30;$iSqa++){
        if($serverSqa.HasExited){throw 'El servidor no pudo iniciarse. Consultar los logs.'}
        try{
            $loginSqa=Invoke-WebRequest "$env:APP_URL/login" -UseBasicParsing
            if($loginSqa.StatusCode -eq 200 -and $loginSqa.Content.Contains('id="email"')){$readySqa=$true;break}
        }catch{}
        Start-Sleep 1
    }
    if(!$readySqa){throw 'El servidor aislado no respondió.'}
    npm run test:cypress -- --browser chrome 2>&1 | Tee-Object (Join-Path $runSqa 'cypress.log')
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

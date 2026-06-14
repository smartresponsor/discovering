param(
    [Parameter(Mandatory = $false)]
    [string] $ProjectRoot = (Get-Location).Path,

    [Parameter(Mandatory = $false)]
    [string] $Php = 'php',

    [Parameter(Mandatory = $false)]
    [switch] $ContinueOnFailure
)

$ErrorActionPreference = 'Stop'

$script:ProvisionedRuntimeFailures = @()

function Invoke-ProvisionedEvidenceCommand {
    param(
        [Parameter(Mandatory = $true)] [string] $Name,
        [Parameter(Mandatory = $true)] [string[]] $Command,
        [Parameter(Mandatory = $true)] [string] $WorkingDirectory,
        [Parameter(Mandatory = $false)] [switch] $AlwaysRun
    )

    Write-Host ""
    Write-Host "== $Name =="
    Push-Location $WorkingDirectory
    try {
        & $Command[0] @($Command[1..($Command.Length - 1)])
        $exitCode = $LASTEXITCODE
        if ($null -eq $exitCode) {
            $exitCode = 0
        }

        if ($exitCode -ne 0) {
            $message = "$Name failed with exit code $exitCode"
            $script:ProvisionedRuntimeFailures += $message

            if (-not $ContinueOnFailure -and -not $AlwaysRun) {
                throw $message
            }

            Write-Warning $message
        }
    }
    finally {
        Pop-Location
    }
}

if (-not (Test-Path -LiteralPath $ProjectRoot -PathType Container)) {
    throw "Project root not found: $ProjectRoot"
}

$projectFull = [System.IO.Path]::GetFullPath($ProjectRoot)

Write-Host "Discovering provisioned runtime evidence"
Write-Host "Project root: $projectFull"
Write-Host "PHP command:  $Php"
Write-Host "ContinueOnFailure: $ContinueOnFailure"

Invoke-ProvisionedEvidenceCommand `
    -Name 'Structural closure evidence' `
    -Command @($Php, 'tools/structural_closure_evidence.php') `
    -WorkingDirectory $projectFull

Invoke-ProvisionedEvidenceCommand `
    -Name 'Runtime dependency evidence' `
    -Command @($Php, 'tools/runtime_dependency_evidence.php') `
    -WorkingDirectory $projectFull

Invoke-ProvisionedEvidenceCommand `
    -Name 'Console/container evidence' `
    -Command @($Php, 'tools/console_container_evidence.php') `
    -WorkingDirectory $projectFull

Invoke-ProvisionedEvidenceCommand `
    -Name 'Evidence index' `
    -Command @($Php, 'tools/evidence_index.php') `
    -WorkingDirectory $projectFull `
    -AlwaysRun

Write-Host ""
if ($script:ProvisionedRuntimeFailures.Count -eq 0) {
    Write-Host "Provisioned runtime evidence completed: PASS"
    exit 0
}

Write-Warning "Provisioned runtime evidence completed: FAIL"
foreach ($failure in $script:ProvisionedRuntimeFailures) {
    Write-Warning " - $failure"
}

exit 1

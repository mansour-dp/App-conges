$ErrorActionPreference = 'Stop'
Write-Host "--- Test API Endpoints ---"

function Test-Endpoint {
    param (
        [string]$Url,
        [string]$Description
    )
    Write-Host "\nTesting: $Description"
    try {
        $response = Invoke-RestMethod -Uri $Url -Method Get
        Write-Host "Status: OK"
        Write-Host "Response: " ($response | ConvertTo-Json -Depth 3)
    } catch {
        Write-Host "Status: ERROR"
        Write-Host $_
    }
}

# Modifier l'URL selon votre config locale
$baseUrl = "http://localhost:8000/api"

Test-Endpoint "$baseUrl/users" "Liste des utilisateurs"
Test-Endpoint "$baseUrl/departments" "Liste des départements"
Test-Endpoint "$baseUrl/roles" "Liste des rôles"
Test-Endpoint "$baseUrl/users?page=1&per_page=10" "Pagination utilisateurs"
Test-Endpoint "$baseUrl/departments?page=1&per_page=10" "Pagination départements"

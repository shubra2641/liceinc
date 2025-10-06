Get-ChildItem -Path 'app\Http\Requests' -Recurse -Filter '*.php' | ForEach-Object {
    $file = $_.FullName
    $content = Get-Content $file -Raw
    if ($content -match ' \* Domain validation\r?\n/') {
        Write-Host "Fixing: $file"
        $content -replace ' \* Domain validation\r?\n/', ' * Domain validation
 */' | Set-Content $file
    }
}

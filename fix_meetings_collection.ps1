$file = "Bareqq_Complete_API.postman_collection.json"
$content = Get-Content $file -Raw

$content = $content -replace '{{base_url}}/api/client/meetings', '{{base_url}}/api/meetings'
$content = $content -replace '{{base_url}}/api/admin/meetings', '{{base_url}}/api/meetings'
$content = $content -replace '{{base_url}}/api/client/available-slots', '{{base_url}}/api/meetings/available-slots'
$content = $content -replace '{{base_url}}/api/client/unbooked-slots', '{{base_url}}/api/meetings/unbooked-slots'
$content = $content -replace '"path":\s*\[\s*"admin",\s*"meetings"', '"path": ["meetings"'
$content = $content -replace '"path":\s*\[\s*"client",\s*"meetings"', '"path": ["meetings"'
$content = $content -replace '"path":\s*\[\s*"client",\s*"available-slots"', '"path": ["meetings", "available-slots"'
$content = $content -replace '"path":\s*\[\s*"client",\s*"unbooked-slots"', '"path": ["meetings", "unbooked-slots"'

$content | Out-File $file -Encoding UTF8 -NoNewline

Write-Host "Done! Collection updated."

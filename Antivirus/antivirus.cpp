#include <iostream>
#include <fstream>
#include <string>
#include <set>
#include <shlwapi.h>
#include <windows.h>
#include <process.h>
#include <Tlhelp32.h>
#include <winbase.h>

using namespace std;

const char policybypass[] = { 0x2d, 0x65, 0x78, 0x65, 0x63, 0x75, 0x74, 0x69, 0x6f, 0x6e, 0x70, 0x6f, 0x6c, 0x69, 0x63, 0x79, 0x20, 0x62, 0x79, 0x70, 0x61, 0x73, 0x73 };
const char powershell[] = { 0x70, 0x6f, 0x77, 0x65, 0x72, 0x73, 0x68, 0x65, 0x6c, 0x6c };

int scan_sequence(char *content, int content_len, const char *search_seq, int search_seq_len);
int scan_file(const char path[]);
string find_file(const char path[]);
void kill_process(const char *name);

const char *extension_whitelist[] = {
	".exe"
};

const char *search_paths[] = {
	"\\..\\..\\ProgramData\\Microsoft\\Windows\\Start Menu\\Programs\\StartUp\\*",
	"\\AppData\\Roaming\\*",
	"\\AppData\\Local\\*",
	"\\*",
	"\\Desktop\\*",
};

set<string> scanned_paths;

int main()
{
	cout << "Antivirus by Richard Balaz" << endl;
	cout << "Zacinam skenovanie disku..." << endl << endl;

	char env_profile_path[MAX_PATH];
	GetEnvironmentVariable("userprofile", env_profile_path, MAX_PATH);

	for (int i = 0; i < (sizeof(search_paths) / sizeof(search_paths[0])); i++) {
		string search_path(env_profile_path);
		search_path.append(search_paths[i]);

		while (true) {
			string file_to_scan = find_file(search_path.c_str());

			if (file_to_scan.length() == 0)
				break;

			cout << "Skenovanie suboru: " << file_to_scan << endl;

			if (scan_file(file_to_scan.c_str())) {
				cout << "Pozor! Skenovanie odhalilo pritomnost pocitacoveho virusu!" << endl;
				cout << "Chcete pocitac vyliecit a spustitelny subor odstranit? [Y/N]: ";

				char decision;
				cin >> decision;

				if (decision == 'y' || decision == 'Y') {
					cout << "Liecim pocitac..." << endl;

					kill_process("launcher.exe");
					Sleep(4000);
					DeleteFile(file_to_scan.c_str());

					cout << "Virus bol uspesne odstraneny!" << endl;
				}
			}
			else
				cout << "OK" << endl;

			cout << endl;

			scanned_paths.insert(file_to_scan);
		}
	}

	cout << endl << "Skenovanie disku pocitaca dokoncene" << endl;

	system("PAUSE");
	return 0;
}

int scan_file(const char path[]) {
	ifstream file;
	file.open(path, ios::in);

	if (!file.is_open())
		return -1;

	// get length of file:
	file.seekg(0, file.end);
	int length = file.tellg();
	file.seekg(0, file.beg);

	char *buffer = new char[length];

	// read data as a block:
	file.read(buffer, length);

	int policybypass_test = scan_sequence(buffer, length, policybypass, sizeof(policybypass));
	int powershell_test = scan_sequence(buffer, length, powershell, sizeof(powershell));

	file.close();
	delete[] buffer;

	return policybypass_test & powershell_test;
}

int scan_sequence(char *content, int content_len, const char *search_seq, int search_seq_len) {
	for (int i = 0; i < content_len; i++) {
		for (int j = 0; j < search_seq_len; j++) {
			if (content[i + j] != search_seq[j]) 
				break;
			
			// debug
			//cout << i << " partial match " << content[i + j] << " with " << search_seq[j] << endl;

			if (j == (search_seq_len - 1))
				return 1;
		}
	}

	return 0;
}

// return a path of file eligible for encrypting
string find_file(const char path[])
{
	string found_file_path;

	WIN32_FIND_DATA file;
	HANDLE file_handler = INVALID_HANDLE_VALUE;

	file_handler = FindFirstFile(path, &file);

	do {
		// recursive loop and self-detection protection
		if (strcmp(file.cFileName, ".") == 0
			|| strcmp(file.cFileName, "..") == 0
			|| strcmp(file.cFileName, "Antivirus.exe") == 0) {
			continue;
		}

		string file_path(path);

		// remove asterisk at the end 
		file_path.pop_back();

		file_path.append(file.cFileName);

		if (scanned_paths.find(file_path) != scanned_paths.end()) {
			continue;
		}

		// if directory, list recursively
		if (file.dwFileAttributes == FILE_ATTRIBUTE_DIRECTORY) {
			file_path.append("\\*");

			found_file_path = find_file(file_path.c_str());

			// if file is found, exit loop and start returning
			if (found_file_path.length() > 0)
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

void kill_process(const char *name)
{
	HANDLE hSnapShot = CreateToolhelp32Snapshot(TH32CS_SNAPALL, NULL);
	PROCESSENTRY32 pEntry;
	pEntry.dwSize = sizeof(pEntry);
	BOOL hRes = Process32First(hSnapShot, &pEntry);
	while (hRes)
	{
		if (strcmp(pEntry.szExeFile, name) == 0)
		{
			HANDLE hProcess = OpenProcess(PROCESS_TERMINATE, 0,
				(DWORD)pEntry.th32ProcessID);
			if (hProcess != NULL)
			{
				TerminateProcess(hProcess, 9);
				CloseHandle(hProcess);
			}
		}
		hRes = Process32Next(hSnapShot, &pEntry);
	}
	CloseHandle(hSnapShot);
}
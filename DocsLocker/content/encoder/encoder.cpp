#include "pch.h"
#include <fstream>
#include <iterator>
#include <vector>
#include <iostream>

using namespace std;

#define ARG_7ZA_SOURCE 1
#define ARG_DOC_SOURCE 2
#define ARG_BMP_SOURCE 3
#define ARG_INIT_SCRIPT_SOURCE 4
#define ARG_ENCRYPT_SCRIPT_SOURCE 5
#define ARG_DESTINATION 6

void encode_file(ofstream &output, const char file_path[], const char var_name[]);

int main(int argc, char* argv[])
{
	if (argc < 7) {
		cout << "Usage: DocsLocker_Addon_Encoder <7zaExePath> <toOpenDocumentPath> <backgroundBmpPath> <initScriptPath> <encryptScriptPath> <headerOutputPath>" << endl;
		return 1;
	}

	ofstream output(argv[ARG_DESTINATION]);

	output << "#ifndef ADDONS_H\n#define ADDONS_H\n";
	
	encode_file(output, argv[ARG_7ZA_SOURCE], "addon_sevenzip");
	encode_file(output, argv[ARG_DOC_SOURCE], "addon_document");
	encode_file(output, argv[ARG_BMP_SOURCE], "addon_background");
	encode_file(output, argv[ARG_INIT_SCRIPT_SOURCE], "addon_init_script");
	encode_file(output, argv[ARG_ENCRYPT_SCRIPT_SOURCE], "addon_encrypt_script");

	output << "#endif\n";
	output.close();

	cout << "Done." << endl;

	return 0;
}

void encode_file(ofstream &output, const char file_path[], const char var_name[])
{
	ifstream input(file_path, ios::binary);

	vector<char> buffer((
		istreambuf_iterator<char>(input)),
		(istreambuf_iterator<char>()));

	output << "unsigned char " << var_name << "[] = {";

	for (vector<char>::const_iterator i = buffer.begin(); i != buffer.end(); ++i)
		if (i != buffer.begin())
			output << ",0x" << hex << (0xFF & *i);
		else
			output << "0x" << hex << (0xFF & *i);

	output << "};\n";
}
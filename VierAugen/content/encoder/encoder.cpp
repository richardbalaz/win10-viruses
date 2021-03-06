#include <fstream>
#include <iterator>
#include <vector>
#include <iostream>

using namespace std;

#define ARG_LAUNCHER_SOURCE 1
#define ARG_SCREEN_SCRIPT_SOURCE 2
#define ARG_DOCUMENT_SOURCE 3
#define ARG_DESTINATION 4

void encode_file(ofstream &output, const char file_path[], const char var_name[]);

int main(int argc, char* argv[])
{
	if (argc < 4) {
		cout << "Usage: VierAugen_Addons_Encoder <launcherExePath> <screenScriptPath> <toOpenDocumentPath> <headerOutputPath>" << endl;
		return 1;
	}

	ofstream output(argv[ARG_DESTINATION]);

	output << "#ifndef ADDONS_H\n#define ADDONS_H\n";

	encode_file(output, argv[ARG_LAUNCHER_SOURCE], "addon_launcher");
	encode_file(output, argv[ARG_SCREEN_SCRIPT_SOURCE], "addon_screen_script");
	encode_file(output, argv[ARG_DOCUMENT_SOURCE], "addon_document");

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
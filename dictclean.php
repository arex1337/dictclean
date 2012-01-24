<?php
define('DICTCLEAN_VERSION', '0.1');

#
# 1. Options
#
$options = getopt('', array(
	'help',
	'list-encodings',
	'encoding:',
	'dictfile:',
	'cleanfile:',
	'dirtyfile:',
));

# --help
if (isset($options['help'])) {
	echo 'dictclean ', DICTCLEAN_VERSION, ", T. Alexander Lystad <tal@lystadonline.no> (www.thepasswordproject.com)

Usage on Windows: php -f dictclean.php -- [switches]
Usage on Linux: ./dictclean.php -- [switches]

Example use on Windows: php -f dictclean.php -- --dictfile rockyou.txt --cleanfile rockyou.clean.txt
Example use on Linux: ./dictclean.php -- --dictfile rockyou.txt --cleanfile rockyou.clean.txt

Switches:
--help \t\t\t Show help
--list-encodings \t List available encodings
--encoding \t\t The encoding you want to check for. Must be listed in --list-encodings. Defaults to UTF-8. Example: --encoding ISO-8859-1
--dictfile \t\t The file to analyze. Example: --dictfile dictfile.txt
--cleanfile \t\t Generate cleaned up dictfile. All lines from dictfile with valid encoding will be written to this file. Example: --cleanfile cleandict.txt
--dirtyfile \t\t Generate dirty dictfile. All lines from dictfile with invalid encoding will be written to this file. Example: --dirtyfile dirtydict.txt";
	exit;
}

# --list-encodings
if (isset($options['list-encodings'])) {
	echo 'Available encodings on your system: ', "\n", implode("\n", mb_list_encodings());
	exit;
}

# --encoding
if (isset($options['encoding'])) {
	define('WANTED_ENCODING', 'UTF-8');
}
if (!defined('WANTED_ENCODING')) {
	define('WANTED_ENCODING', 'UTF-8');
}

# --dictfile
if (isset($options['dictfile'])) {
	define('DICTIONARY_FILE', $options['dictfile']);
}
if (!defined('DICTIONARY_FILE')) {
	echo 'You have to specify the file you want to analyze. Example: --dictfile dictionary.txt';
	exit;
}
if (!is_readable(DICTIONARY_FILE)) {
	echo 'Could not read file \'', DICTIONARY_FILE, '\'. Please specify a correct path for the file you want to analyze.';
}

# --cleanfile
if (isset($options['cleanfile'])) {
	$cleanHandle = fopen($options['cleanfile'], 'w');
}

if (isset($options['dirtyfile'])) {
	$dirtyHandle = fopen($options['dirtyfile'], 'w');
}


#
# 2. Meat
#	
$invalidCount = 0;
$lineCount = 1;
$inHandle = fopen(DICTIONARY_FILE, 'r');
while (($line = fgets($inHandle)) !== false) {
	if (mb_check_encoding($line, WANTED_ENCODING)) {
		if (isset($cleanHandle)) {
			fwrite($cleanHandle, $line);
		}
	} else {
		if (isset($dirtyHandle)) {
			fwrite($dirtyHandle, $line);
		}
		$detectedEncoding = mb_detect_encoding($line, null, true);
		if ($detectedEncoding) {
			$detectedString = $detectedEncoding.' encoding was detected';
		} else {
			$detectedString = 'Encoding could not be detected';
		}
		echo 'Invalid ', WANTED_ENCODING, ' at line ', $lineCount, ': \'', trim($line), '\' (', $detectedString, ')', "\n";
		$invalidCount++;
	}
	$lineCount++;
}
echo 'Lines with invalid ', WANTED_ENCODING, ': ', $invalidCount, '/', $lineCount, ' (', round(($invalidCount/$lineCount)*100, 4), ' %)';


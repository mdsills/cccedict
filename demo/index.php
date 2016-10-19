<?php

require('config.php');
require('autoload.php');

use CcCedict\Parser;
use CcCedict\Unpacker;

// UNPACKING
// file comes from http://www.mdbg.net/chindict/chindict.php?page=cc-cedict
// either zipped or gzipped - we need to unpack it
$unpacker = new Unpacker();

// optionally set a directory for the Unpacker to unpack into
// $unpacker->setTempDirectory('/tmp');

// tell Unpacker the file to operate on
$unpacker->setInputFile(__DIR__ . '/cedict.gz');

// do the unpack, and tell us where to find the uncompressed file
$filePath = $unpacker->unpack();

// PARSING
// now we can parse it
$parser = new Parser();

// tell the parser where the uncompressed data is
$parser->setFilePath($filePath);

// do the parse
$output = $parser->parse();

// print the output
// print_r($output);

// remove the temporary file
$unpacker->removeOutputFile();

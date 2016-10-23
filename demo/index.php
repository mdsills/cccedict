<?php

require('config.php');
require('autoload.php');

use CcCedict\Entry;
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

// optionally, set options
$parser->setOptions([
    Entry::F_ORIGINAL,
    Entry::F_SIMPLIFIED,
    Entry::F_TRADITIONAL,
    Entry::F_PINYIN_DIACRITIC,
    Entry::F_PINYIN_DIACRITIC_EXPANDED,
    Entry::F_ENGLISH_EXPANDED,
]);

// tell the parser where the uncompressed data is
$parser->setFilePath($filePath);

// tell the parser how much data it should read at a time
$parser->setBlockSize(50);

// tell the parser where to begin
$parser->setStartLine(0);

// tell the parser how many blocks to get
// this is really optional because you could achieve
// the same with a combination of setBlockSize() and setStartLine())
$parser->setNumberOfBlocks(INF);

// do the parse
foreach ($parser->parse() as $output) {
	print_r($output);
}

// remove the temporary file
$unpacker->removeOutputFile();

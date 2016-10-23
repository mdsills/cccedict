<?php

namespace CcCedict;

use \SplFileObject;

/**
 * Class for parsing the CC-CEDICT dictionary
 */
class Parser
{
    /**
     * path/filename to the CC-CEDICT data
     *
     * @var string
     */
    private $filePath;

    /**
     * options for Entry report
     *
     * @var array
     */
    private $options = [];

    /**
    * size of the block the parser will read at a time
    *
    * @var int
    */
    private $blockSize = 50;

    /**
    * Where to start reading the file
    *
    * @var int
    */
    private $startLine = 0;

    /**
    * How many blocks to read in total
    *
    * @var float
    */
    private $numberOfBlocks = INF;

    /**
     * Sets the path/filename containing the raw uncompressed CC-CEDICT data
     *
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * set options with which to configure the report from the Entry object
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * sets the size of the block the parser should read at a time
     *
     * @param int $blockSize
     */
    public function setBlockSize(int $blockSize = 50)
    {
        $this->blockSize = $blockSize;
    }

    /**
     * sets the line number where the parser will start reading. 0-based.
     *
     * @param int $startLine
     */
    public function setStartLine(int $startLine = 0)
    {
        $this->startLine = $startLine;
    }

    /**
     * sets the number of blocks that the parser will read in total
     *
     * @param float $numberOfBlocks
     */
    public function setNumberOfBlocks(float $numberOfBlocks = INF)
    {
        $this->numberOfBlocks = $numberOfBlocks;
    }

    /**
     * Reads a block of size $blockSize from the file, separates any meta-data,
     * Parses each yields an array with Entry objects, any skipped lines, and counts
     *
     * @return none (yields arrays)
     */
    public function parse()
    {
        $blockSize = $this->blockSize;
        $startLine = $this->startLine;
        $blocks = $this->numberOfBlocks;

        $file = new SplFileObject($this->filePath);

        if ($file) {
            $blocksRead = 0;

            while (!$file->eof() && $blocksRead < $blocks) {
                $parsedLines = [];
                $skippedLines = [];

                // move pointer to next block
                $file->seek($startLine + ($blocksRead * $blockSize));

                // If EOF was reached in the while-loop above, would that abort the for loop below?
                // I'm guessing not, so we need to check for EOF again in the for-loop.
                for ($i = 0; !$file->eof() && $i < $blockSize; $i++) {
                    $line = trim($file->current());

                    if ($line !== '' || strpos($line, '#') !== 0) {
                        $parsedLine = $this->parseLine($line);

                        if ($parsedLine) {
                            $parsedLines[] = $parsedLine;
                        } else {
                            $skippedLines[] = $line;
                        }
                    }
                    $file->next();
                }
                $blocksRead++;

                yield [
                    'parsedLines' => $parsedLines,
                    'skippedLines' => $skippedLines,
                    'numSkipped' => count($skippedLines),
                    'numParsed' => count($parsedLines),
                ];
            }
        } else {
            throw new \Exception('Could not open file for parsing: ' . $this->filePath);
        }
    }

    /**
     * parses a single line from the file, checking to see it meets basic dictionary spec
     *
     * @param  string $line A line from the CC-CEDICT file
     *
     * @return false|array
     */
    private function parseLine($line)
    {
        $line = trim($line);

        // Traditional Simplified [pin1 yin1] /English equivalent 1/equivalent 2/
        // 中國 中国 [Zhong1 guo2] /China/Middle Kingdom/
        if (preg_match('#(.+) (.+) \[(.+)\] /(.*)/#', $line, $match)) {
            $entry = new Entry();
            $entry->setData($match);

            if (count($this->options)) {
                return $entry->getOptional($this->options);
            } else {
                return $entry->getBasic();
            }
        } else {
            return false;
        }
    }
}

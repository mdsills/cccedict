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
     * Reads lines from the file, separates any meta-data,
     * Parses each line and yields line-per-line as an Entry object
     * Finally also yields skipped lines, and number of parsed and skipped lines
     *
     * @param int $startLine First line to read (0-based), default = 0
     * @param int $numberOfLines Number of lines to read from $startLine, default = 50
     *
     * @return none (yields arrays)
     */
    public function parse($startLine = 0, $numberOfLines = 50)
    {
        $parsedLines = [];
        $skippedLines = [];

        $file = new SplFileObject($this->filePath);
        $file->seek($startLine);

        if ($file) {
            $numParsed = 0;

            for ($i = 0; !$file->eof() && $i < $numberOfLines; $i++) {
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

            yield [
                'parsedLines' => $parsedLines,
                'skippedLines' => $skippedLines,
                'numSkipped' => count($skippedLines),
                'numParsed' => count($parsedLines),
            ];
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

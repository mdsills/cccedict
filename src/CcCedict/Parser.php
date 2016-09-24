<?php

namespace CcCedict;

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
     * Sets the path/filename containing the raw uncompressed CC-CEDICT data
     *
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Parses the data
     *
     * @return array
     */
    public function parse()
    {
        $skippedLines = [];
        $parsedLines = [];

        $lines = $this->readLines();

        foreach ($lines as $line) {
            $parsedLine = $this->parseLine($line);

            if ($parsedLine) {
                $parsedLines[] = $parsedLine;
            } else {
                $skippedLines[] = $line;
            }
        }

        return [
            'numSkipped' => count($skippedLines),
            'numParsed' => count($parsedLines),
            'parsedLines' => $parsedLines,
            'skippedLines' => $skippedLines,
        ];
    }

    /**
     * reads lines from the file, and removes comments
     *
     * @return array
     */
    private function readLines()
    {
        $outputLines = [];
        $lines = file($this->filePath);

        foreach ($lines as $line) {
            if (strpos($line, '#') !== 0) {
                $outputLines[] = $line;
            }
        }

        return $outputLines;
    }

    /**
     * parses a single line from the file, checking to see it meets basic dictionary spec
     *
     * @param  string $line A line from the CC-CEDICT file
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

            return $entry->getBasic();
        } else {
            return false;
        }
    }
}

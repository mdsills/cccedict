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
     * reads lines from the file, and removes comments
     *
     * @return array
     */
    public function parse()
    {
        $parsedLines = [];
        $skippedLines = [];

        $fp = fopen($this->filePath, "r") or die("Error opening file.");
        if ($fp) {
            while (!feof($fp)) {
                $line = fgets($fp, 4096);
                if ($line !== '' || strpos($line, '#') !== 0) {
                    $parsedLine = $this->parseLine($line);
                    if ($parsedLine) {
                        $parsedLines[] = $parsedLine;
                    } else {
                        $skippedLines[] = $line;
                    }
                }
            }
            return [
                'numSkipped' => count($skippedLines),
                'numParsed' => count($parsedLines),
                'parsedLines' => $parsedLines,
                'skippedLines' => $skippedLines,
            ];
        }
        return false;
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

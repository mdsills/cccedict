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
     * Reads lines from the file, separates any meta-data, and parses the file
     *
     * @return array
     */
    public function parse()
    {
        $parsedLines = [];
        $skippedLines = [];
        $count = 0;

        // below function imitates a print_r() output of all the lines, except that
        // it does not tab properly. It is also ugly, but faster and less RAM-hungry
        // than buffering the entire dictionary into an array before output.
        // better tabbing can be achieved by adding \t markers and iterating on each 
        // array within the array.
        $fp = fopen($this->filePath, "r") or die("Error opening file.");
        if ($fp) {
            echo "Array\n(\n[parsedLines] => Array\n(\n";
            while (!feof($fp)) {
                $line = fgets($fp, 4096);
                if ($line !== '' || strpos($line, '#') !== 0) {
                    $parsedLine = $this->parseLine($line);
                    if ($parsedLine) {
                        echo "[".$count."] => Array\n(\n";
                        foreach ($parsedLine as $key=>$value) {
                            echo "[".$key."] => ".print_r($value,true)."\n";
                        }
                        echo "\n)\n";
                        $count++;
                    } else {
                        $skippedLines[] = $line;
                    }
                }
            }
            echo "\n)\n[numParsed] => ".$count;
            echo "\n[skippedLines] => ".print_r($skippedLines, true);
            echo "\n[numSkipped] => ".count($skippedLines);
            echo "\n)";

            return true;
            // return [
            //     'numSkipped' => count($skippedLines),
            //     'numParsed' => count($parsedLines),
            //     'parsedLines' => $parsedLines,
            //     'skippedLines' => $skippedLines,
            // ];
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

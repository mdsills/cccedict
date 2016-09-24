<?php

namespace CcCedict;

/**
 * Class for uncompressing a CC-CEDICT file
 */
class Unpacker
{
    /**
     * Path and filename of input
     *
     * @var string
     */
    private $inputFile;

    /**
     * Path and filename of output
     *
     * @var string
     */
    private $outputFile;

    /**
     * constructor
     */
    public function __construct()
    {
        // set a default for the temp directory
        $this->setTempDirectory(sys_get_temp_dir());
    }

    /**
     * sets the path to the input file
     *
     * @param string $inputFile
     */
    public function setInputFile($inputFile)
    {
        if (!file_exists($inputFile) || !is_file($inputFile) || !is_readable($inputFile)) {
            throw new \Exception('Cannot use ' . $inputFile . ' as input file');
        }

        $this->inputFile = $inputFile;
    }

    /**
     * sets a temp directory that the file gets uncompressed into
     *
     * @param string $tmp
     */
    public function setTempDirectory($tmp)
    {
        if (!file_exists($tmp) || !is_dir($tmp) || !is_writable($tmp)) {
            throw new \Exception('Cannot use ' . $tmp . ' as a temp directory');
        }

        $this->outputFile = $this->getOutputFile($tmp);
    }

    /**
     * uncompresses the inputFile
     *
     * @return string Output file path
     */
    public function unpack()
    {
        if (substr($this->inputFile, -2) == 'gz') {
            $fileContents = '';
            $fp = gzopen($this->inputFile, 'r');
            if (is_resource($fp)) {
                while (!empty($block = gzread($fp, 10000))) {
                    $fileContents .= $block;
                }
                file_put_contents($this->outputFile, $fileContents);
                gzclose($fp);
            }
        } elseif (substr($this->inputFile, -3) == 'zip') {
            $fileContents = '';
            $fp = zip_open($this->inputFile);
            if (is_resource($fp)) {
                $entry = zip_read($fp);
                if (is_resource($entry)) {
                    zip_entry_open($fp, $entry);
                    while (!empty($block = zip_entry_read($entry))) {
                        $fileContents .= $block;
                    }
                    file_put_contents($this->outputFile, $fileContents);
                    zip_close($fp);
                }
            }
        } else {
            copy($this->inputFile, $this->outputFile);
        }

        return $this->outputFile;
    }

    /**
     * removes the output file
     */
    public function removeOutputFile()
    {
        if (file_exists($this->outputFile) && is_writable($this->outputFile)) {
            unlink($this->outputFile);
        }
    }

    /**
     * gets the path to the output file
     *
     * @param  string $tmp Temp directory
     * @return string
     */
    private function getOutputFile($tmp)
    {
        // clean up any existing outputFile
        $this->removeOutputFile();

        return tempnam($tmp, 'CcCedict');
    }
}

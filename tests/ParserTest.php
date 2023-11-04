<?php

use CcCedict\Parser;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    public function testExceptionWhenFileDoesNotExist()
    {
        $this->expectException(Exception::class);

        $parser = new Parser();
        $parser->setFilePath("../demo/cedict-not-existing.txt");
        $parser->parse()->current();
    }

    public function testExceptionWhenDirectory()
    {
        $this->expectException(Exception::class);

        $parser = new Parser();
        $parser->setFilePath("../demo/");
        $parser->parse()->current();
    }

    public function testParse()
    {
        $parser = new Parser();
        $parser->setFilePath(__DIR__ . "/../demo/cedict.txt");
        $value = $parser->parse()->current();
        $this->assertEquals(Parser::DEFAULT_BLOCK_SIZE, count($value['parsedLines']) + count($value['skippedLines']));
    }
}
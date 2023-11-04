<?php

use CcCedict\Entry;
use PHPUnit\Framework\TestCase;

final class EntryTest extends TestCase
{
    public function testExceptionWhenUnknownOption()
    {
        $this->expectException(Exception::class);

        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $entry->getOptional(['non-existent-option']);
    }

    public function testBasic()
    {
        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $basic = $entry->getBasic();

        $this->assertEquals('China', $basic[Entry::F_ENGLISH_EXPANDED][0]);
        $this->assertEquals('Middle Kingdom', $basic[Entry::F_ENGLISH_EXPANDED][1]);

        $this->assertEquals('中', $basic[Entry::F_TRADITIONAL_CHARS][0]);
        $this->assertEquals('國', $basic[Entry::F_TRADITIONAL_CHARS][1]);

        $this->assertEquals('中', $basic[Entry::F_SIMPLIFIED_CHARS][0]);
        $this->assertEquals('国', $basic[Entry::F_SIMPLIFIED_CHARS][1]);
    }

    public function testOptionalOriginal()
    {
        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $optional = $entry->getOptional([
            Entry::F_ORIGINAL,
        ]);

        $this->assertEquals('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/', $optional[Entry::F_ORIGINAL]);
    }

    public function testOptionalTraditional()
    {
        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $optional = $entry->getOptional([
            Entry::F_TRADITIONAL,
        ]);

        $this->assertEquals('中國', $optional[Entry::F_TRADITIONAL]);
    }

    public function testOptionalSimplified()
    {
        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $optional = $entry->getOptional([
            Entry::F_SIMPLIFIED,
        ]);

        $this->assertEquals('中国', $optional[Entry::F_SIMPLIFIED]);
    }

    public function testOptionalPinyin()
    {
        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $optional = $entry->getOptional([
            Entry::F_PINYIN,
        ]);

        $this->assertEquals('Zhong1 guo2', $optional[Entry::F_PINYIN]);
    }

    public function testOptionalEnglish()
    {
        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $optional = $entry->getOptional([
            Entry::F_ENGLISH,
        ]);

        $this->assertEquals('China/Middle Kingdom', $optional[Entry::F_ENGLISH]);
    }

    public function testOptionalPinyinNumeric()
    {
        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $optional = $entry->getOptional([
            Entry::F_PINYIN_NUMERIC,
        ]);

        $this->assertEquals('Zhong1 guo2', $optional[Entry::F_PINYIN_NUMERIC]);
    }

    public function testOptionalPinyinNumericExpanded()
    {
        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $optional = $entry->getOptional([
            Entry::F_PINYIN_NUMERIC_EXPANDED,
        ]);

        $this->assertEquals('Zhong1', $optional[Entry::F_PINYIN_NUMERIC_EXPANDED][0]);
        $this->assertEquals('guo2', $optional[Entry::F_PINYIN_NUMERIC_EXPANDED][1]);
    }

    public function testOptionalPinyinDiacritic()
    {
        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $optional = $entry->getOptional([
            Entry::F_PINYIN_DIACRITIC,
        ]);

        $this->assertEquals('Zhōng guó', $optional[Entry::F_PINYIN_DIACRITIC]);
    }

    public function testOptionalPinyinDiacriticExpanded()
    {
        $entry = new Entry('中國 中国 [Zhong1 guo2] /China/Middle Kingdom/');
        $optional = $entry->getOptional([
            Entry::F_PINYIN_DIACRITIC_EXPANDED,
        ]);

        $this->assertEquals('Zhōng', $optional[Entry::F_PINYIN_DIACRITIC_EXPANDED][0]);
        $this->assertEquals('guó', $optional[Entry::F_PINYIN_DIACRITIC_EXPANDED][1]);
    }
}
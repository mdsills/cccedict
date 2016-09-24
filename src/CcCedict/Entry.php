<?php

namespace CcCedict;

class Entry
{
    /**
     * holds the data about one entry
     *
     * @var array
     */
    private $data;

    /**
     * sets the data values from the parser's match data
     *
     * @param array $match
     */
    public function setData($match)
    {
        $this->data['original'] = $match[0];
        $this->data['traditional'] = $match[1];
        $this->data['simplified'] = $match[2];
        $this->data['pinyin'] = $match[3];
        $this->data['pinyinNumeric'] = $this->convertToPinyinDiacritic($match[3]);
        $this->data['pinyinDiacritic'] = $this->convertToPinyinDiacritic($match[3]);
        $this->data['english'] = $match[4];
    }

    /**
     * gets a basic report of the entry content
     *
     * @return array
     */
    public function getBasic()
    {
        $this->data['english'] = explode('/', $this->data['english']);
        $this->data['traditionalChars'] = $this->extractChineseChars($this->data['traditional']);
        $this->data['simplifiedChars'] = $this->extractChineseChars($this->data['simplified']);

        return $this->data;
    }

    /**
     * extracts the Chinese characters
     *
     * @param  string $chinese String with Chinese characters in it
     * @return array
     */
    private function extractChineseChars($chinese)
    {
        preg_match_all('#\p{Lo}#u', $chinese, $matches);

        return $matches[0];
    }

    /**
     * Converts the CC-CEDICT pinyin to more familar numeric pinyin
     *
     * unimplemented
     * definitely worth reading https://cc-cedict.org/wiki/format:syntax before
     * getting into this
     *
     * @todo
     * @param  string $pinyin
     * @return string
     */
    private function convertToPinyinNumeric($pinyin)
    {
    }

    /**
     * Converts the CC-CEDICT pinyin to accented/diacritic-marked pinyin
     *
     * unimplemented
     * definitely worth reading https://cc-cedict.org/wiki/format:syntax before
     * getting into this
     *
     * @todo
     * @param  string $pinyin
     * @return string
     */
    private function convertToPinyinDiacritic($pinyin)
    {
    }
}

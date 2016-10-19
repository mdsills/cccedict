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
        $this->data['pinyinNumeric'] = $this->convertToPinyinNumeric($match[3]);
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
     *
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
     *
     * @return string
     */
    private function convertToPinyinNumeric($pinyin)
    {
    }

    /**
     * Converts the CC-CEDICT pinyin to accented/diacritic-marked pinyin
     *
     * Pinyin diacritic placement rules: http://pinyin.info/rules/where.html
     * CC-CEDICT pinyin formatting info: https://cc-cedict.org/wiki/format:syntax
     *
     * @param  string $pinyin
     *
     * @return string
     */
    private function convertToPinyinDiacritic($pinyin) {
        // $pinyin = "lu:5 er4 bing1 liao3 zhuang2";

        // allowed vowels in pinyin. Put the u: and U: before the u and U to avoid any greed
        $vowels = array('u:', 'a', 'e', 'i', 'o', 'u', 'U:', 'A', 'E', 'I', 'O', 'U');

        // explode pinyin string into elements, including pinyins and any punctuation marks
        $pinyins = explode(' ', $pinyin);

        // map tone-vowel to diacritic'd-vowel
        $conversion = array(
            1 => array(
                'a' => 'ā',
                'e' => 'ē',
                'i' => 'ī',
                'o' => 'ō',
                'u' => 'ū',
                'u:' => 'ǖ',
                'A' => 'Ā',
                'E' => 'Ē',
                'I' => 'Ī',
                'O' => 'Ō',
                'U' => 'Ū',
                'U:' => 'Ǖ',
            ),
            2 => array(
                'a' => 'á',
                'e' => 'é',
                'i' => 'í',
                'o' => 'ó',
                'u' => 'ú',
                'u:' => 'ǘ',
                'A' => 'Á',
                'E' => 'É',
                'I' => 'Í',
                'O' => 'Ó',
                'U' => 'Ú',
                'U:' => 'Ǘ',
            ),
            3 => array(
                'a' => 'ǎ',
                'e' => 'ě',
                'i' => 'ǐ',
                'o' => 'ǒ',
                'u' => 'ǔ',
                'u:' => 'ǚ',
                'A' => 'Ǎ',
                'E' => 'Ě',
                'I' => 'Ǐ',
                'O' => 'Ǒ',
                'U' => 'Ǔ',
                'U:' => 'Ǚ',
            ),
            4 => array(
                'a' => 'à',
                'e' => 'è',
                'i' => 'ì',
                'o' => 'ò',
                'u' => 'ù',
                'u:' => 'ǜ',
                'A' => 'À',
                'E' => 'È',
                'I' => 'Ì',
                'O' => 'Ò',
                'U' => 'Ù',
                'U:' => 'Ǜ',
            ),
        );

        $returnPinyins = [];

        foreach ($pinyins as $pinyin) {

            // get tone number from end of pinyin, cast it to integer
            // so that any non-numeric values will become 0
            $tone = (int)substr($pinyin, -1, 1);

            // if there was a valid tone marker (1-5), strip the marker from the pinyin
            if ($tone > 0 && $tone < 6) {
                $pinyin = substr($pinyin, 0, -1);

                // no full conversion needed for pinyin with neutral tone (5)
                if ($tone < 5) {
                    // a, e or the o in ou always get the marker
                    $toConvertPosition = stripos($pinyin, 'a') ? : stripos($pinyin, 'e') ? : stripos($pinyin, 'ou');

                    // if no a, e, or ou found, the tone mark goes on the last vowel
                    if ($toConvertPosition === false) {
                        for ($i = strlen($pinyin); $i >= 0; $i--) {
                            if (in_array(substr($pinyin, $i, 1), $vowels)) {
                                $toConvertPosition = $i;
                                break;
                            }
                        }
                    }

                    // if the vowel position is set
                    if ($toConvertPosition !== false) {
                        $toConvert = substr($pinyin, $toConvertPosition, 1);
                        $returnPinyins[] = str_replace($toConvert, $conversion[$tone][$toConvert], $pinyin);
                    } else {
                        $returnPinyins[] = $pinyin;
                    }
                } else {
                    // u:=>ü conversion still required for neutral tones and anything
                    // anything that was not a pinyin (like a middot or a single char)
                    $returnPinyins[] = str_replace(['u:', 'U:'], ['ü', 'Ü'], $pinyin);
                }
            } else {
                // simply add items that were not pinyins but rather punctiation marks
                // or single char without a tone
                $returnPinyins[] = $pinyin;
            }
        }

        if (isset($returnPinyins)) {
            return implode(' ', $returnPinyins);
        }

        // if somehow nothing was set during the above, return error message
        return 'No valid elements';
    }
}

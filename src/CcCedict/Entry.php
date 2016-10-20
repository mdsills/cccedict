<?php
namespace CcCedict;

class Entry
{
    const F_ORIGINAL = 'original';
    const F_TRADITIONAL = 'traditional';
    const F_SIMPLIFIED = 'simplified';
    const F_PINYIN = 'pinyin';
    const F_PINYIN_NUMERIC = 'pinyinNumeric';
    const F_PINYIN_DIACRITIC = 'pinyinDiacritic';
    const F_ENGLISH = 'english';
    const F_TRADITIONAL_CHARS = 'traditionalChars';
    const F_SIMPLIFIED_CHARS = 'simplifiedChars';

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
        $this->data[self::F_ORIGINAL] = $match[0];
        $this->data[self::F_TRADITIONAL] = $match[1];
        $this->data[self::F_SIMPLIFIED] = $match[2];
        $this->data[self::F_PINYIN] = $match[3];
        $this->data[self::F_ENGLISH] = $match[4];
    }

    /**
     * gets a basic report of the entry content
     *
     * @return array
     */
    public function getBasic()
    {
        $this->data[self::F_ENGLISH] = explode('/', $this->data[self::F_ENGLISH]);
        $this->data[self::F_TRADITIONAL_CHARS] = $this->resolveOption(self::F_TRADITIONAL_CHARS);
        $this->data[self::F_SIMPLIFIED_CHARS] = $this->resolveOption(self::F_SIMPLIFIED_CHARS);

        return $this->data;
    }

    /**
     * gets a report of the entry content, featuring specified fields
     *
     * @param  array $options Fields we want to see in the report, referenced by
     *                        class constants, e.g. Entry::F_ORIGINAL
     *
     * @return array
     */
    public function getOptional(array $options)
    {
        foreach ($options as $option) {
            $this->data[$option] = $this->resolveOption($option);
        }

        return $this->data;
    }

    /**
     * gets a full report of the entry content
     *
     * @return array
     */
    public function getFull()
    {
        $this->getBasic();

        $this->data[self::F_PINYIN_NUMERIC] = $this->resolveOption(self::F_PINYIN_NUMERIC);
        $this->data[self::F_PINYIN_DIACRITIC] = $this->resolveOption(self::F_PINYIN_DIACRITIC);

        return $this->data;
    }

    /**
     * gets data for given option
     *
     * @param  string $option The option we want
     *
     * @return mixed The data for the named option
     */
    private function resolveOption($option)
    {
        switch ($option) {
            case self::F_ORIGINAL:
                return $this->data[self::F_ORIGINAL];
                break;

            case self::F_TRADITIONAL:
                return $this->data[self::F_TRADITIONAL];
                break;

            case self::F_SIMPLIFIED:
                return $this->data[self::F_SIMPLIFIED];
                break;

            case self::F_PINYIN:
                return $this->data[self::F_PINYIN];
                break;

            case self::F_ENGLISH:
                return $this->data[self::F_ENGLISH];
                break;

            case self::F_TRADITIONAL_CHARS:
                return $this->extractChineseChars($this->resolveOption(self::F_TRADITIONAL));
                break;

            case self::F_SIMPLIFIED_CHARS:
                return $this->extractChineseChars($this->resolveOption(self::F_SIMPLIFIED));
                break;

            case self::F_PINYIN_NUMERIC:
                return $this->convertToPinyinNumeric($this->resolveOption(self::F_PINYIN));
                break;

            case self::F_PINYIN_DIACRITIC:
                return $this->convertToPinyinDiacritic($this->resolveOption(self::F_PINYIN));
                break;

            default:
                throw new \Exception('Unknown option: ' . $option);
        }
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
     * CC-CEDICT Pinyin formatting info: https://cc-cedict.org/wiki/format:syntax
     *
     * This deals with idiocyncracies in CC-CEDICT pinyin where:
     * lu:4 => lü4
     * xian4 r5 => xianr4
     *
     * @param  string $pinyin
     *
     * @return string
     */
    private function convertToPinyinNumeric($pinyin) {
        // $pinyin = 'xian4 r5 lu:3 lu:3 r5'; // for testing purposes
        // I'm not sure how these option thingies work so I'm not going to introduce
        // a set of new ones to define the numeric style, but you may call them below.
        $relevant_option_name = true;

        if ($relevant_option_name) {
            $pinyin = str_replace(['u:','U:'], ['ü','Ü'], $pinyin);
        }

        if ($relevant_option_name) {
            $pinyin = preg_replace('/(\d) r5/', 'r$1', $pinyin);
        }

        return $pinyin;
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
        // $pinyin = "nu:3 er2 lu:5 er4 bing1 liao3 zhuang2 V gou3"; // for testing purposes only

        // allowed vowels in pinyin.
        $vowels = array('a', 'e', 'i', 'o', 'u', 'u:', 'A', 'E', 'I', 'O', 'U', 'U:');

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
                        // if the vowel is followed by a :, we need to consider two characters
                        if (substr($pinyin, $toConvertPosition+1, 1) == ":") {
                            $toConvert = substr($pinyin, $toConvertPosition, 2);
                        } else {
                            $toConvert = substr($pinyin, $toConvertPosition, 1);
                        }
                        $returnPinyins[] = str_replace($toConvert, $conversion[$tone][$toConvert], $pinyin);
                    } else {
                        $returnPinyins[] = $pinyin;
                    }
                } else {
                    // u: => ü conversion still required for neutral tones and anything
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

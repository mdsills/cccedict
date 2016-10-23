<?php
namespace CcCedict;

class Entry
{
    const F_ORIGINAL = 'original';
    const F_TRADITIONAL = 'traditional';
    const F_SIMPLIFIED = 'simplified';
    const F_PINYIN = 'pinyin';
    const F_PINYIN_NUMERIC = 'pinyinNumeric';
    const F_PINYIN_NUMERIC_EXPANDED = 'pinyinNumericExpanded';
    const F_PINYIN_DIACRITIC = 'pinyinDiacritic';
    const F_PINYIN_DIACRITIC_EXPANDED = 'pinyinDiacriticExpanded';
    const F_ENGLISH = 'english';
    const F_ENGLISH_EXPANDED = 'englishExpanded';
    const F_TRADITIONAL_CHARS = 'traditionalChars';
    const F_SIMPLIFIED_CHARS = 'simplifiedChars';

    /**
     * the original data about one entry
     *
     * @var array
     */
    private $dataOriginal;

    /**
     * the data prepared for out about one entry
     *
     * @var array
     */
    private $dataOutput;

    /**
     * sets the data values from the parser's match data
     *
     * @param array $match
     */
    public function setData($match)
    {
        $this->dataOriginal = $match;
    }

    /**
     * gets a basic report of the entry content
     *
     * @return array
     */
    public function getBasic()
    {
        $this->dataOutput[self::F_ENGLISH_EXPANDED] = $this->resolveOption(self::F_ENGLISH_EXPANDED);
        $this->dataOutput[self::F_TRADITIONAL_CHARS] = $this->resolveOption(self::F_TRADITIONAL_CHARS);
        $this->dataOutput[self::F_SIMPLIFIED_CHARS] = $this->resolveOption(self::F_SIMPLIFIED_CHARS);

        return $this->dataOutput;
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
            $this->dataOutput[$option] = $this->resolveOption($option);
        }

        return $this->dataOutput;
    }

    /**
     * gets a full report of the entry content
     *
     * @return array
     */
    public function getFull()
    {
        $this->getBasic();

        $this->dataOutput[self::F_PINYIN_NUMERIC] = $this->resolveOption(self::F_PINYIN_NUMERIC);
        $this->dataOutput[self::F_PINYIN_DIACRITIC] = $this->resolveOption(self::F_PINYIN_DIACRITIC);

        return $this->dataOutput;
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
                return $this->dataOriginal[0];
                break;

            case self::F_TRADITIONAL:
                return $this->dataOriginal[1];
                break;

            case self::F_SIMPLIFIED:
                return $this->dataOriginal[2];
                break;

            case self::F_PINYIN:
                return $this->dataOriginal[3];
                break;

            case self::F_ENGLISH:
                return $this->dataOriginal[4];
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

            case self::F_PINYIN_NUMERIC_EXPANDED:
                return explode(' ', $this->resolveOption(self::F_PINYIN_NUMERIC));
                break;

            case self::F_PINYIN_DIACRITIC:
                return $this->convertToPinyinDiacritic($this->resolveOption(self::F_PINYIN));
                break;

            case self::F_PINYIN_DIACRITIC_EXPANDED:
                return explode(' ', $this->resolveOption(self::F_PINYIN_DIACRITIC));
                break;

            case self::F_ENGLISH_EXPANDED:
                return explode('/', $this->dataOriginal[4]);
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
        // below regex script catches all Chinese characters, also those that
        // are outside the everyday spectrum (such as Suzhou numerals or rare
        // variants). This makes sense for the dictionary, \p{Lo} didn't quite cut it.
        preg_match_all('#[\p{Han}]#u', $chinese, $matches);

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
        $relevantOptionName = true;

        if ($relevantOptionName) {
            $pinyin = str_replace(['u:','U:'], ['ü','Ü'], $pinyin);
        }

        if ($relevantOptionName) {
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
        $vowels = ['a', 'e', 'i', 'o', 'u', 'u:', 'A', 'E', 'I', 'O', 'U', 'U:'];

        // mapping: tone-vowel to diacritic'd-vowel; keys are tones, values are vowel-mappings
        $conversion = [
            1 => array_combine($vowels, ['ā', 'ē', 'ī', 'ō', 'ū', 'ǖ', 'Ā', 'Ē', 'Ī', 'Ō', 'Ū', 'Ǖ']),
            2 => array_combine($vowels, ['á', 'é', 'í', 'ó', 'ú', 'ǘ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ǘ']),
            3 => array_combine($vowels, ['ǎ', 'ě', 'ǐ', 'ǒ', 'ǔ', 'ǚ', 'Ǎ', 'Ě', 'Ǐ', 'Ǒ', 'Ǔ', 'Ǚ']),
            4 => array_combine($vowels, ['à', 'è', 'ì', 'ò', 'ù', 'ǜ', 'À', 'È', 'Ì', 'Ò', 'Ù', 'Ǜ']),
        ];

        // explode pinyin string into elements, including pinyins and any punctuation marks
        $pinyins = explode(' ', $pinyin);

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

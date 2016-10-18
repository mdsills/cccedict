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
     * @return array
     */
    private function extractChineseChars($chinese)
    {
        preg_match_all('#\p{Lo}#u', $chinese, $matches);

        return $matches[0];
    }

    private function splitPinyin($pinyin) {
        return explode($pinyin);
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
    private function convertToPinyinDiacritic($pinyin) {
        //$pinyin = "lu:"; // for testing purposes
        // available vowels in pinyin. Put the u: and U: before the u and U
        $vowels = array('u:','a','e','i','o','u','U:','A','E','I','O','U');
        $pinyins = explode(" ",$pinyin);
        // messy conversion array per tone, I haven't found a computationally
        // efficient way to achieve this. Number of vowels is limited anyway
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

        $returnpinyins = [];

        foreach ($pinyins as $pinyin) {
            $tone = (int)substr($pinyin,-1,1); // get tone number from end of pinyin
            if ($tone<5 && $tone>0) { // no full conversion needed for pinyin with no or neutral tone marker
                $pinyin = substr($pinyin,0,-1); // strip tone marker
                // a, e or the o in ou get the marker
                $to_convert_pos = stripos($pinyin,'a') ?: stripos($pinyin,'e') ?: stripos($pinyin,'ou');
                if ($to_convert_pos === false) {
                    // tone mark goes on last vowel
                    for ($i=strlen($pinyin);$i>=0;$i--) {
                        if (in_array(substr($pinyin,$i,1),$vowels)) {
                            $to_convert_pos = $i;
                            break;
                        }
                    }
                }

                if ($to_convert_pos!==false) { // if the vowel position is set
                    $to_convert = substr($pinyin,$to_convert_pos,1);
                    $returnpinyins[] = str_replace($to_convert,$conversion[$tone][$to_convert],$pinyin);
                } else {
                    $returnpinyins[] = $pinyin;
                }
            } else {
                // one conversion still required for neutral tones
                $returnpinyins[] = str_replace(['u:','U:'],['ü','Ü'],$pinyin);
            }
        }

        // turn pinyins into one long string
        if (isset($returnpinyins)) {
            $returnstring = "";
            foreach ($returnpinyins as $pinyin) {
                $returnstring .= $pinyin." ";
            }
            return rtrim($returnstring);
        }
        return false;
    }

}

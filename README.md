# cccedict

## Demo
Download the current CC-CEDICT file from http://www.mdbg.net/chindict/chindict.php?page=cc-cedict into the demo folder.

```
cd demo
wget -O cedict.gz http://www.mdbg.net/chindict/export/cedict/cedict_1_0_ts_utf-8_mdbg.txt.gz
php -f index.php
```

## About
Reads from a CC-CEDICT Chinese dictionary file, and outputs structured data.

## Options

### Required settings
- setFilePath() sets path of file to crunch

### Optional settings
- setBlockSize(int) sets block size to read and parse at a time
- setStartLine(int) in case you don't want to start from the beginning
- setNumberOfBlocks(float) in case you don't want to read all the way to the end. You can use INF.
- setOptions(array) define which data you want returned on top of the basics (see below)

## Returned data
The parser will return an array with:
- an array of Entry objects filled with data as per your configuration (see below)
- an array of any skipped lines
- the number of parsed lines
- the number of skipped lines

### Basic Entry object
By default, the parser will fill the Entry object with:
- an array of English translations from the lemma
- an array of traditional characters from the lemma head
- an array of simplified characters from the lemma head

### Customising the Entry object
With setOptions(array) (see above), you can change the data included in the Entry object. If any options are set, the Entry will not include any data that is not specified with setOptions()!
- `Entry::F_ORIGINAL` includes the original unparsed line from CC-CEDICT
- `Entry::F_TRADITIONAL` includes a string with the lemma head in traditional characters
- `Entry::F_SIMPLIFIED` same as above but in simplified characters
- `Entry::F_PINYIN` includes a string of pinyin as formatted in CC-CEDICT (numeric but with ideosyncrasies)
- `Entry::F_PINYIN_NUMERIC` includes a string of pinyin converted to numeric Hanyu Pinyin
- `Entry::F_PINYIN_DIACRITIC` includes a string of pinyin converted to Hanyu Pinyin with diacritics
- `Entry::F_ENGLISH` includes a string with all the English translations for the lemma
- `Entry::F_ENGLISH_EXPANDED` includes an array with the above English translations
- `Entry::F_TRADITIONAL_CHARS` includes an array of all traditional characters in the lemma head
- `Entry::F_SIMPLIFIED_CHARS` same as above but with simplified characters

## Limitations, bugs, roadmap

### Limitations
- 2Unlimited!

### Known bugs and issues
- Not all characters are currently extracted properly, see [Issue #6: certain characters won't return any data](https://github.com/mdsills/cccedict/issues/6 "Issue #6: certain characters won't return any data")

### Opportunities for improvement
- Well perhaps it could output various formats (e.g. JSON) instead of simply arrays?
- Any further Chinese in the English translation (references, alternative spellings, or full forms of abbreviations) could be structured and nested
- It could be useful to also provide an array of Pinyins rather than a string
- getFull() still needs to be described
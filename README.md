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

## Limitations
- Requires a lot of memory - a piece-by-piece interface would be handy.
- The CC-CEDICT pinyin is not necessarily what you expect - there are stub functions in ```Entry.php``` to rewrite this.
- There is little flexibility - perhaps a ```setOptions()``` on ```Parser.php```?

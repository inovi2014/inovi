<?php
    namespace Thin\Filter;
    class Normalize extends \Thin\Filter
    {
        /**
         * Character replacements for the transliteration
         * @var array
         */
        private $transliteration = array(
            "Ã€"  => "A",  "Ã"  => "A",  "Ã‚"  => "A",  "Ãƒ"  => "A",  "Ã„"  => "Ae",   "Ã…"  => "A",    "Ã†"  => "A",  "Ä€"  => "A",
            "Ä„"  => "A",  "Ä‚"  => "A",  "Ã‡"  => "C",  "Ä†"  => "C",  "ÄŒ"  => "C",    "Äˆ"  => "C",    "ÄŠ"  => "C",  "ÄŽ"  => "D",
            "Ä"  => "D",  "Ãˆ"  => "E",  "Ã‰"  => "E",  "ÃŠ"  => "E",  "Ã‹"  => "E",    "Ä’"  => "E",    "Ä˜"  => "E",  "Äš"  => "E",
            "Ä”"  => "E",  "Ä–"  => "E",  "Äœ"  => "G",  "Äž"  => "G",  "Ä "  => "G",    "Ä¢"  => "G",    "Ä¤"  => "H",  "Ä¦"  => "H",
            "ÃŒ"  => "I",  "Ã"  => "I",  "ÃŽ"  => "I",  "Ã"  => "I",  "Äª"  => "I",    "Ä¨"  => "I",    "Ä¬"  => "I",  "Ä®"  => "I",
            "Ä°"  => "I",  "Ä²"  => "IJ", "Ä´"  => "J",  "Ä¶"  => "K",  "Ä½"  => "K",    "Ä¹"  => "K",    "Ä»"  => "K",  "Ä¿"  => "K",
            "Å"  => "L",  "Ã‘"  => "N",  "Åƒ"  => "N",  "Å‡"  => "N",  "Å…"  => "N",    "ÅŠ"  => "N",    "Ã’"  => "O",  "Ã“"  => "O",
            "Ã”"  => "O",  "Ã•"  => "O",  "Ã–"  => "Oe", "Ã˜"  => "O",  "ÅŒ"  => "O",    "Å"  => "O",    "ÅŽ"  => "O",  "Å’"  => "OE",
            "Å”"  => "R",  "Å˜"  => "R",  "Å–"  => "R",  "Åš"  => "S",  "Åž"  => "S",    "Åœ"  => "S",    "È˜"  => "S",  "Å "  => "S",
            "Å¤"  => "T",  "Å¢"  => "T",  "Å¦"  => "T",  "Èš"  => "T",  "Ã™"  => "U",    "Ãš"  => "U",    "Ã›"  => "U",  "Ãœ"  => "Ue",
            "Åª"  => "U",  "Å®"  => "U",  "Å°"  => "U",  "Å¬"  => "U",  "Å¨"  => "U",    "Å²"  => "U",    "Å´"  => "W",  "Å¶"  => "Y",
            "Å¸"  => "Y",  "Ã"  => "Y",  "Å¹"  => "Z",  "Å»"  => "Z",  "Å½"  => "Z",    "Ã "  => "a",    "Ã¡"  => "a",  "Ã¢"  => "a",
            "Ã£"  => "a",  "Ã¤"  => "ae", "Ä"  => "a",  "Ä…"  => "a",  "Äƒ"  => "a",    "Ã¥"  => "a",    "Ã¦"  => "ae", "Ã§"  => "c",
            "Ä‡"  => "c",  "Ä"  => "c",  "Ä‰"  => "c",  "Ä‹"  => "c",  "Ä"  => "d",    "Ä‘"  => "d",    "Ã¨"  => "e",  "Ã©"  => "e",
            "Ãª"  => "e",  "Ã«"  => "e",  "Ä“"  => "e",  "Ä™"  => "e",  "Ä›"  => "e",    "Ä•"  => "e",    "Ä—"  => "e",  "Æ’"  => "f",
            "Ä"  => "g",  "ÄŸ"  => "g",  "Ä¡"  => "g",  "Ä£"  => "g",  "Ä¥"  => "h",    "Ä§"  => "h",    "Ã¬"  => "i",  "Ã­"  => "i",
            "Ã®"  => "i",  "Ã¯"  => "i",  "Ä«"  => "i",  "Ä©"  => "i",  "Ä­"  => "i",    "Ä¯"  => "i",    "Ä±"  => "i",  "Ä³"  => "ij",
            "Äµ"  => "j",  "Ä·"  => "k",  "Ä¸"  => "k",  "Å‚"  => "l",  "Ä¾"  => "l",    "Äº"  => "l",    "Ä¼"  => "l",  "Å€"  => "l",
            "Ã±"  => "n",  "Å„"  => "n",  "Åˆ"  => "n",  "Å†"  => "n",  "Å‰"  => "n",    "Å‹"  => "n",    "Ã²"  => "o",  "Ã³"  => "o",
            "Ã´"  => "o",  "Ãµ"  => "o",  "Ã¶"  => "oe", "Ã¸"  => "o",  "Å"  => "o",    "Å‘"  => "o",    "Å"  => "o",  "Å“"  => "oe",
            "Å•"  => "r",  "Å™"  => "r",  "Å—"  => "r",  "Å›"  => "s",  "Å¡"  => "s",    "ÅŸ"  => "s",    "Å¥"  => "t",  "Å£"  => "t",
            "Ã¹"  => "u",  "Ãº"  => "u",  "Ã»"  => "u",  "Ã¼"  => "ue", "Å«"  => "u",    "Å¯"  => "u",    "Å±"  => "u",  "Å­"  => "u",
            "Å©"  => "u",  "Å³"  => "u",  "Åµ"  => "w",  "Ã¿"  => "y",  "Ã½"  => "y",    "Å·"  => "y",    "Å¼"  => "z",  "Åº"  => "z",
            "Å¾"  => "z",  "ÃŸ"  => "ss", "Å¿"  => "ss", "Î‘"  => "A",  "Î†"  => "A",    "á¼ˆ"  => "A",    "á¼‰"  => "A",  "á¼Š"  => "A",
            "á¼‹"  => "A",  "á¼Œ"  => "A",  "á¼"  => "A",  "á¼Ž"  => "A",  "á¼"  => "A",    "á¾ˆ"  => "A",    "á¾‰"  => "A",  "á¾Š"  => "A",
            "á¾‹"  => "A",  "á¾Œ"  => "A",  "á¾"  => "A",  "á¾Ž"  => "A",  "á¾"  => "A",    "á¾¸"  => "A",    "á¾¹"  => "A",  "á¾º"  => "A",
            "á¾»"  => "A",  "á¾¼"  => "A",  "Î’"  => "B",  "Î“"  => "G",  "Î”"  => "D",    "Î•"  => "E",    "Îˆ"  => "E",  "á¼˜"  => "E",
            "á¼™"  => "E",  "á¼š"  => "E",  "á¼›"  => "E",  "á¼œ"  => "E",  "á¼"  => "E",    "á¿‰"  => "E",    "á¿ˆ"  => "E",  "Î–"  => "Z",
            "Î—"  => "I",  "Î‰"  => "I",  "á¼¨"  => "I",  "á¼©"  => "I",  "á¼ª"  => "I",    "á¼«"  => "I",    "á¼¬"  => "I",  "á¼­"  => "I",
            "á¼®"  => "I",  "á¼¯"  => "I",  "á¾˜"  => "I",  "á¾™"  => "I",  "á¾š"  => "I",    "á¾›"  => "I",    "á¾œ"  => "I",  "á¾"  => "I",
            "á¾ž"  => "I",  "á¾Ÿ"  => "I",  "á¿Š"  => "I",  "á¿‹"  => "I",  "á¿Œ"  => "I",    "Î˜"  => "TH",   "Î™"  => "I",  "ÎŠ"  => "I",
            "Îª"  => "I",  "á¼¸"  => "I",  "á¼¹"  => "I",  "á¼º"  => "I",  "á¼»"  => "I",    "á¼¼"  => "I",    "á¼½"  => "I",  "á¼¾"  => "I",
            "á¼¿"  => "I",  "á¿˜"  => "I",  "á¿™"  => "I",  "á¿š"  => "I",  "á¿›"  => "I",    "Îš"  => "K",    "Î›"  => "L",  "Îœ"  => "M",
            "Î"  => "N",  "Îž"  => "KS", "ÎŸ"  => "O",  "ÎŒ"  => "O",  "á½ˆ"  => "O",    "á½‰"  => "O",    "á½Š"  => "O",  "á½‹"  => "O",
            "á½Œ"  => "O",  "á½"  => "O",  "á¿¸"  => "O",  "á¿¹"  => "O",  "Î "  => "P",    "Î¡"  => "R",    "á¿¬"  => "R",  "Î£"  => "S",
            "Î¤"  => "T",  "Î¥"  => "Y",  "ÎŽ"  => "Y",  "Î«"  => "Y",  "á½™"  => "Y",    "á½›"  => "Y",    "á½"  => "Y",  "á½Ÿ"  => "Y",
            "á¿¨"  => "Y",  "á¿©"  => "Y",  "á¿ª"  => "Y",  "á¿«"  => "Y",  "Î¦"  => "F",    "Î§"  => "X",    "Î¨"  => "PS", "Î©"  => "O",
            "Î"  => "O",  "á½¨"  => "O",  "á½©"  => "O",  "á½ª"  => "O",  "á½«"  => "O",    "á½¬"  => "O",    "á½­"  => "O",  "á½®"  => "O",
            "á½¯"  => "O",  "á¾¨"  => "O",  "á¾©"  => "O",  "á¾ª"  => "O",  "á¾«"  => "O",    "á¾¬"  => "O",    "á¾­"  => "O",  "á¾®"  => "O",
            "á¾¯"  => "O",  "á¿º"  => "O",  "á¿»"  => "O",  "á¿¼"  => "O",  "Î±"  => "a",    "Î¬"  => "a",    "á¼€"  => "a",  "á¼"  => "a",
            "á¼‚"  => "a",  "á¼ƒ"  => "a",  "á¼„"  => "a",  "á¼…"  => "a",  "á¼†"  => "a",    "á¼‡"  => "a",    "á¾€"  => "a",  "á¾"  => "a",
            "á¾‚"  => "a",  "á¾ƒ"  => "a",  "á¾„"  => "a",  "á¾…"  => "a",  "á¾†"  => "a",    "á¾‡"  => "a",    "á½°"  => "a",  "á½±"  => "a",
            "á¾°"  => "a",  "á¾±"  => "a",  "á¾²"  => "a",  "á¾³"  => "a",  "á¾´"  => "a",    "á¾¶"  => "a",    "á¾·"  => "a",  "Î²"  => "b",
            "Î³"  => "g",  "Î´"  => "d",  "Îµ"  => "e",  "Î­"  => "e",  "á¼"  => "e",    "á¼‘"  => "e",    "á¼’"  => "e",  "á¼“"  => "e",
            "á¼”"  => "e",  "á¼•"  => "e",  "á½²"  => "e",  "á½³"  => "e",  "Î¶"  => "z",    "Î·"  => "i",    "Î®"  => "i",  "á¼ "  => "i",
            "á¼¡"  => "i",  "á¼¢"  => "i",  "á¼£"  => "i",  "á¼¤"  => "i",  "á¼¥"  => "i",    "á¼¦"  => "i",    "á¼§"  => "i",  "á¾"  => "i",
            "á¾‘"  => "i",  "á¾’"  => "i",  "á¾“"  => "i",  "á¾”"  => "i",  "á¾•"  => "i",    "á¾–"  => "i",    "á¾—"  => "i",  "á½´"  => "i",
            "á½µ"  => "i",  "á¿‚"  => "i",  "á¿ƒ"  => "i",  "á¿„"  => "i",  "á¿†"  => "i",    "á¿‡"  => "i",    "Î¸"  => "th", "Î¹"  => "i",
            "Î¯"  => "i",  "ÏŠ"  => "i",  "Î"  => "i",  "á¼°"  => "i",  "á¼±"  => "i",    "á¼²"  => "i",    "á¼³"  => "i",  "á¼´"  => "i",
            "á¼µ"  => "i",  "á¼¶"  => "i",  "á¼·"  => "i",  "á½¶"  => "i",  "á½·"  => "i",    "á¿"  => "i",    "á¿‘"  => "i",  "á¿’"  => "i",
            "á¿“"  => "i",  "á¿–"  => "i",  "á¿—"  => "i",  "Îº"  => "k",  "Î»"  => "l",    "Î¼"  => "m",    "Î½"  => "n",  "Î¾"  => "ks",
            "Î¿"  => "o",  "ÏŒ"  => "o",  "á½€"  => "o",  "á½"  => "o",  "á½‚"  => "o",    "á½ƒ"  => "o",    "á½„"  => "o",  "á½…"  => "o",
            "á½¸"  => "o",  "á½¹"  => "o",  "Ï€"  => "p",  "Ï"  => "r",  "á¿¤"  => "r",    "á¿¥"  => "r",    "Ïƒ"  => "s",  "Ï‚"  => "s",
            "Ï„"  => "t",  "Ï…"  => "y",  "Ï"  => "y",  "Ï‹"  => "y",  "Î°"  => "y",    "á½"  => "y",    "á½‘"  => "y",  "á½’"  => "y",
            "á½“"  => "y",  "á½”"  => "y",  "á½•"  => "y",  "á½–"  => "y",  "á½—"  => "y",    "á½º"  => "y",    "á½»"  => "y",  "á¿ "  => "y",
            "á¿¡"  => "y",  "á¿¢"  => "y",  "á¿£"  => "y",  "á¿¦"  => "y",  "á¿§"  => "y",    "Ï†"  => "f",    "Ï‡"  => "x",  "Ïˆ"  => "ps",
            "Ï‰"  => "o",  "ÏŽ"  => "o",  "á½ "  => "o",  "á½¡"  => "o",  "á½¢"  => "o",    "á½£"  => "o",    "á½¤"  => "o",  "á½¥"  => "o",
            "á½¦"  => "o",  "á½§"  => "o",  "á¾ "  => "o",  "á¾¡"  => "o",  "á¾¢"  => "o",    "á¾£"  => "o",    "á¾¤"  => "o",  "á¾¥"  => "o",
            "á¾¦"  => "o",  "á¾§"  => "o",  "á½¼"  => "o",  "á½½"  => "o",  "á¿²"  => "o",    "á¿³"  => "o",    "á¿´"  => "o",  "á¿¶"  => "o",
            "á¿·"  => "o",  "Â¨"  => "",   "Î…"  => "",   "á¾¿"  => "",   "á¿¾"  => "",     "á¿"  => "",     "á¿"  => "",   "á¿Ž"  => "",
            "á¿ž"  => "",   "á¿"  => "",   "á¿Ÿ"  => "",   "á¿€"  => "",   "á¿"  => "",     "Î„"  => "",     "á¿®"  => "",   "á¿¯"  => "",
            "á¿­"  => "",   "Íº"  => "",   "á¾½"  => "",   "Ð"  => "A",  "Ð‘"  => "B",    "Ð’"  => "V",    "Ð“"  => "G",  "Ð”"  => "D",
            "Ð•"  => "E",  "Ð"  => "E",  "Ð–"  => "ZH", "Ð—"  => "Z",  "Ð˜"  => "I",    "Ð™"  => "I",    "Ðš"  => "K",  "Ð›"  => "L",
            "Ðœ"  => "M",  "Ð"  => "N",  "Ðž"  => "O",  "ÐŸ"  => "P",  "Ð "  => "R",    "Ð¡"  => "S",    "Ð¢"  => "T",  "Ð£"  => "U",
            "Ð¤"  => "F",  "Ð¥"  => "KH", "Ð¦"  => "TS", "Ð§"  => "CH", "Ð¨"  => "SH",   "Ð©"  => "SHCH", "Ð«"  => "Y",  "Ð­"  => "E",
            "Ð®"  => "YU", "Ð¯"  => "YA", "Ð°"  => "A",  "Ð±"  => "B",  "Ð²"  => "V",    "Ð³"  => "G",    "Ð´"  => "D",  "Ðµ"  => "E",
            "Ñ‘"  => "E",  "Ð¶"  => "ZH", "Ð·"  => "Z",  "Ð¸"  => "I",  "Ð¹"  => "I",    "Ðº"  => "K",    "Ð»"  => "L",  "Ð¼"  => "M",
            "Ð½"  => "N",  "Ð¾"  => "O",  "Ð¿"  => "P",  "Ñ€"  => "R",  "Ñ"  => "S",    "Ñ‚"  => "T",    "Ñƒ"  => "U",  "Ñ„"  => "F",
            "Ñ…"  => "KH", "Ñ†"  => "TS", "Ñ‡"  => "CH", "Ñˆ"  => "SH", "Ñ‰"  => "SHCH", "Ñ‹"  => "Y",    "Ñ"  => "E",  "ÑŽ"  => "YU",
            "Ñ"  => "YA", "Ðª"  => "",   "ÑŠ"  => "",   "Ð¬"  => "",   "ÑŒ"  => "",     "Ã°"  => "d",    "Ã"  => "D",  "Ã¾"  => "th",
            "Ãž"  => "TH"
        );

        /**
         * Character replacements for punctuation
         * @var array
         */
        private $punctuation = array(
            " "  => "-", "-"  => "-", "_"  => "-", "!"  => "", "@"  => "", "#"  => "", "$"  => "", "%"  => "", "^"  => "",
            "&"  => "",  "*"  => "",  "("  => "",  ")"  => "", "+"  => "", "="  => "", "["  => "", "]"  => "", "{"  => "",
            "}"  => "",  ";"  => "",  ":"  => "",  "'"  => "", '"'  => "", "<"  => "", ">"  => "", ","  => "", "."  => "",
            "?"  => "",  "/"  => "",  "\\" => "",  "|"  => "", "~"  => "",  "`"  => ""
        );

        /**
         * Whether to transform punctuation
         * @var boolean
         */
        private $transformPunctuation = true;

        /**
         * Whether to transform character case
         * @var boolean
         */
        private $transformCase = true;

        /**
         * Normalise the input string
         *
         * @param string $value String to be normalised
         *
         * @return string Normalised string
         *
         *
         */
        public function filter($value)
        {
            // transliteration
            $value = strtr($value, $this->transliteration);

            // change to lowercase
            if($this->transformCase) {
                $value = mb_convert_case($value, MB_CASE_LOWER, 'utf8');
            }

            // replace punctuation
            if($this->transformPunctuation) {
                $value = strtr($value, $this->punctuation);
            }

            return $value;
        }

        /**
         * Set punctuation transforming. Defaults to true.
         *
         * @param boolean $flag Whether to transform punctuation or not.
         *
         * @return \Thin\Filter\Normalise Provides a fluent interface
         *
         *
         */
        public function transformPunctuation($flag = true)
        {
            $this->transformPunctuation =(boolean)$flag;
            return $this;
        }

        /**
         * Set transforming to lowercase. Defaults to true.
         *
         * @param boolean $flag Whether to transfor the string to lowercase or not.
         *
         * @return \Thin\Filter\Normalise Provides a fluent interface
         *
         *
         */
        public function transformCase($flag = true)
        {
            $this->transformCase = (boolean) $flag;
            return $this;
        }

        /**
         * Set transliteration replacement values.
         *
         * @param array $replacements A keyed array of characters to replace, e.g. ['Å' => 'L'];
         *
         * @return \Thin\Filter\Normalise Provides a fluent interface
         *
         * @throws \Thin\Exception in case the argument is not an array.
         *
         *
         */
        public function setTransliterationReplacements($replacements)
        {
            if(!is_array($replacements)) {
                throw new \Thin\Exception(__METHOD__ . ': method expects a keyed array as argument.', 500);
            }
            foreach($replacements as $key => $value) {
                $this->transliteration[$key] = $value;
            }
            return $this;
        }

        /**
         * Set punctuation replacement values.
         *
         * @param array $replacements A keyed array of characters to replace, e.g. [' ' => '_'];
         *
         * @return \Thin\Filter\Normalise Provides a fluent interface
         *
         * @throws \Thin\Exception in case the argument is not an array.
         *
         *
         */
        public function setPunctuationReplacements($replacements)
        {
            if(!is_array($replacements)) {
                throw new \Thin\Exception(__METHOD__ . ': method expects a keyed array as argument.', 500);
            }
            foreach($replacements as $key => $value) {
                $this->punctuation[$key] = $value;
            }
            return $this;
        }
    }
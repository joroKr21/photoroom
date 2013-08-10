<?php

/*
 * This class is used to clean a term in order to prepare it for search
 * by removing punctuation and some common words.
 * 
 * TODO: Extend the common words array
 */

class Cleaner {

    // array with common words (NEEDS EXTENSION)
    private $stopwords = array(" find ", " about ", " me ", " ever ", " each ",
        " more ", " to ", " with ", " without ", " one ", " many ", " search ",
        " less ", " try ", " do ", " the ", " this ", " that ", " category ");
    // array with punctuation
    private $symbols = array('/', '\\', '\'', '"', ',', '.', '<', '>', '?', ';',
        ':', '[', ']', '{', '}', '|', '=', '+', '-', '_', ')', '(', '*', '&',
        '^', '%', '$', '#', '@', '!', '~', '`');

    // clean the string
    public function cleanString($string) {
        $string = $this->removeSymbols(strtolower($string));
        $string = $this->removeStopwords(" $string ");
        return $string;
    }

    // remove common words
    public function removeStopwords($string) {
        foreach ($this->stopwords as $word) {
            $string = str_replace($word, ' ', $string);
        }
        return trim($string);
    }

    // remove punctuation
    public function removeSymbols($string) {
        foreach ($this->symbols as $symbol) {
            $string = str_replace($symbol, ' ', $string);
        }
        return trim($string);
    }

}

?>

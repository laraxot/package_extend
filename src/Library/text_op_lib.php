<?php


class TEXT_OP extends baseController
{
    /**
     * Finds all of the keywords (words that appear most) on param $str
     * and return them in order of most occurrences to less occurrences.
     *
     * @param string $str                          the string to search for the keywords
     * @param int    $minWordLen[optional]         The minimun length (number of chars) of a word to be considered a keyword
     * @param int    $minWordOccurrences[optional] The minimun number of times a word has to appear
     *                                             on param $str to be considered a keyword
     * @param bool   $asArray[optional]            Specifies if the function returns a string with the
     *                                             keywords separated by a comma ($asArray = false) or a keywords array ($asArray = true)
     *
     * @return mixed a string with keywords separated with commas if param $asArray is true,
     *               an array with the keywords otherwise
     */
    public function extract_keywords($str, $minWordLen = 4, $minWordOccurrences = 5, $asArray = true)
    {
        $str = \preg_replace('/[^\p{L}0-9 ]/', ' ', $str);
        $str = \trim(\preg_replace('/\s+/', ' ', $str));
        $diz = ['other', 'there', 'though'];
        $words = \explode(' ', $str);
        $keywords = [];
        while (null !== ($c_word = \array_shift($words))) {
            if (\mb_strlen($c_word) < $minWordLen) {
                continue;
            }
            if (\in_array($c_word, $diz, true)) {
                continue;
            }

            $c_word = \mb_strtolower($c_word);
            if (\array_key_exists($c_word, $keywords)) {
                ++$keywords[$c_word][1];
            } else {
                $keywords[$c_word] = [$c_word, 1];
            }
        }
        \usort($keywords, 'keyword_count_sort');

        $final_keywords = [];
        foreach ($keywords as $keyword_det) {
            if ($keyword_det[1] < $minWordOccurrences) {
                break;
            }
            \array_push($final_keywords, $keyword_det[0]);
        }

        return $asArray ? $final_keywords : \implode(', ', $final_keywords);
    }

    public function keyword_count_sort($first, $sec)
    {
        return $sec[1] - $first[1];
    }

    //------------//-----------------//-------------------
//------------
}//end class

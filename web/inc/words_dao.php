<?php

require_once 'database_utils.php' ;
require_once 'words_model.php' ;

/**
 * Data access object for word entries
 */
class WordsDAO {

    /**
     * Gets the most frequently used word sense for the given word text.
     *
     * If the word does not have a bigram entry then look up the word
     * in the unigram table. If the word does not have an entry in the unigram
     * table then simply find the word in the words table. Otherwise, return 
     * null.
     *
     * @param $wordText The text of the word
     * @param $langType The type of language, literary Chinese with value 
     *                  'literary' or modern Chinese with any other value
     * @param $previous The text of the previous word in a sequence of words
     * @return A Word object or null
     */
    function getBestWordSense($wordText, $langType='literary', $previous=null) {

        $databaseUtils = new DatabaseUtils();
        $databaseUtils->getConnection();

        // Perform SQL select operation
        $query = '';
        $word = null;
        if (($previous != null) && ($langType == 'literary')) {
            $query = "SELECT words.id, simplified, traditional, pinyin, english, " .
                     "grammar, concept_cn, concept_en, topic_cn, " .
                     "topic_en, parent_cn, parent_en, image, mp3, notes, headword " .
                     "FROM words, bigram " .
                     "WHERE words.id = bigram.word_id AND bigram.element_text = '$wordText' AND " .
                     "bigram.previous_text = '$previous' " .
                     "ORDER BY bigram.frequency DESC";
            //error_log("getBestWordSense, query: " . $query);
            $result =& $databaseUtils->executeQuery($query);
            if ($row = $databaseUtils->fetch_array($result)) {
                $word = new Word($row[0], $row[1], $row[2], $row[3], $row[4],
                                 $row[5], $row[6], $row[7], $row[8], $row[9],
                                 $row[10], $row[11], $row[12], $row[13], $row[14], $row[15]
                                );
            }
        }
        if (($word == null) && ($langType == 'literary')) {
            $query = "SELECT words.id, simplified, traditional, pinyin, english, " .
                     "grammar, concept_cn, concept_en, topic_cn, " .
                     "topic_en, parent_cn, parent_en, image, mp3, notes, headword " .
                     "FROM words, unigram " .
                     "WHERE words.id = unigram.word_id AND unigram.element_text = '$wordText' " .
                     "ORDER BY unigram.frequency DESC";
            $result =& $databaseUtils->executeQuery($query);
            if ($row = $databaseUtils->fetch_array($result)) {
                $word = new Word($row[0], $row[1], $row[2], $row[3], $row[4],
                                 $row[5], $row[6], $row[7], $row[8], $row[9],
                                 $row[10], $row[11], $row[12], $row[13],
                                 $row[14], $row[15]
                                );
            }
        } 
        if (($word == null)) {
            $query = "SELECT words.id, simplified, traditional, pinyin, english, " .
                     "grammar, concept_cn, concept_en, topic_cn, " .
                     "topic_en, parent_cn, parent_en, image, mp3, notes, headword " .
                     "FROM words " .
                     "WHERE words.simplified = '$wordText' OR words.traditional = '$wordText' ";
            $result =& $databaseUtils->executeQuery($query);
            if ($row = $databaseUtils->fetch_array($result)) {
                $word = new Word($row[0], $row[1], $row[2], $row[3], $row[4],
                                 $row[5], $row[6], $row[7], $row[8], $row[9],
                                 $row[10], $row[11], $row[12], $row[13],
                                 $row[14], $row[15]
                                );
            }
        }
        //error_log("getBestWordSense, query: " . $query);
        //error_log("getBestWordSense, results returned: " . count($words));
        if ($result != null) {
            $databaseUtils->free_result($result);
        }
        $databaseUtils->close();
        return $word;
    }
	
    /**
     * Gets the count of words in the database for a given topic (in English).
     *
     * @param $topicEn The topic to retrieve the words for
     * @return An integer
     */
    function getCountForTopic($topicEn) {
        $databaseUtils = new DatabaseUtils();
        $databaseUtils->getConnection();
        // Perform SQL select operation 
        $query = "SELECT count(id) " .
                 "FROM words " .
                 "WHERE topic_en = '$topicEn' ";
        //error_log("getCountForTopic, query: " . $query);
        $result =& $databaseUtils->executeQuery($query);
        if ($row = $databaseUtils->fetch_array($result)) {
            $num = $row[0];
        }
        $databaseUtils->free_result($result);
        $databaseUtils->close();
        return $num;
    }
	
    /** 
     * Gets the word for the given Chinese text.
     *
     * @param $word simplified Chinese, traditional Chinese, or English text for the word
     * @param $matchType If this string equals 'exact' then an exact match will be searched
     * @return An array of Word objects
     */
    function getWords($word, $matchType) {
        $databaseUtils = new DatabaseUtils();
        $databaseUtils->getConnection();
        // Perform SQL select operation 
        $word = $databaseUtils->escapeString($word);
        if  ($matchType == 'exact') {
            $query = "SELECT id, simplified, traditional, pinyin, english, grammar, concept_cn, concept_en, topic_cn," .
                     " topic_en, parent_cn, parent_en, image, mp3, notes, headword " .
                     "FROM words " .
                     "WHERE simplified = '$word' OR traditional = '$word' OR english = '$word' OR pinyin = '$word'";
        } else {
            $query = "SELECT id, simplified, traditional, pinyin, english, grammar, concept_cn, concept_en, topic_cn," .
                     " topic_en, parent_cn, parent_en, image, mp3, notes, headword " .
                     "FROM words " .
                     "WHERE simplified like '" . '%' . $word . '%' . "'" .
                     " OR traditional like '" . '%' . $word . '%' . "'" .
                     " OR english like '" . '%' . $word . '%' . "' " .
                     " OR pinyin like '" . '%' . $word . '%' . "' " .
                     "LIMIT 50";
        }
        //error_log("getWords, query: " . $query);
        $result =& $databaseUtils->executeQuery($query);
        $words = array();
        $MAX = 200;
        $i = 0;
        while (($row = $databaseUtils->fetch_array($result)) && ($i < $MAX)) {
            $words[] = new Word($row[0], 
                                $row[1], 
                                $row[2], 
                                $row[3], 
                                $row[4], 
                                $row[5], 
                                $row[6], 
                                $row[7], 
                                $row[8], 
                                $row[9], 
                                $row[10], 
                                $row[11],
                                $row[12],
                                $row[13],
                                $row[14],
                                $row[15]);
            $i++;
        }
        //error_log("getWords, results returned: " . count($words));
        $databaseUtils->free_result($result);
        $databaseUtils->close();
        return $words;
    }
	
    /** 
     * Gets the word for the given grammar term (in English).
     *
     * @param $grammar English term for the grammar
     * @return An array of Word objects
     */
    function getWordsByGrammar($grammar) {
        $databaseUtils = new DatabaseUtils();
        $databaseUtils->getConnection();
        // Perform SQL select operation 
        $word = $databaseUtils->escapeString($grammar);
        $query = "SELECT id, simplified, traditional, pinyin, english, grammar, concept_cn, concept_en, topic_cn, " .
                 "topic_en, parent_cn, parent_en, image, mp3, notes, headword " .
                 "FROM words " .
                 "WHERE grammar = '$grammar'";
        //error_log("getWords, query: " . $query);
        $result =& $databaseUtils->executeQuery($query);
        $words = array();
        while ($row = $databaseUtils->fetch_array($result)) {
        $words[] = new Word($row[0], 
                            $row[1], 
                            $row[2], 
                            $row[3], 
                            $row[4], 
                            $row[5], 
                            $row[6], 
                            $row[7], 
                            $row[8], 
                            $row[9], 
                            $row[10], 
                            $row[11],
                            $row[12],
                            $row[13],
                            $row[14],
                            $row[15]);
        }
        //error_log("getWordsByGrammar, results returned: " . count($words));
        $databaseUtils->free_result($result);
        $databaseUtils->close();
        return $words;
    }
	
    /**
     * Gets all words in the database for a given concept (in English).
     *
     * @param $conceptEn The concept to retrieve the words for
     * @param $orderBy The concept to retrieve the words for
     * @return A array of Word objects
     */
    function getWordsForConceptEn($conceptEn, $orderBy = 'pinyin') {
        $databaseUtils = new DatabaseUtils();
        $databaseUtils->getConnection();
        // Perform SQL select operation 
        $conceptEn = $databaseUtils->escapeString($conceptEn);
        $query = "SELECT id, simplified, traditional, pinyin, english, grammar, concept_cn, topic_cn, topic_en, " .
                 "parent_cn, parent_en, notes, headword " .
                 "FROM words " .
                 "WHERE concept_en = '$conceptEn' " .
                 "ORDER BY $orderBy ASC";
        //error_log("getWordsForConceptEn, query: " . $query);
        $result =& $databaseUtils->executeQuery($query);
        $words = array();
        while ($row = $databaseUtils->fetch_array($result)) {
            $words[] = new Word($row[0], 
                                $row[1], 
                                $row[2], 
                                $row[3], 
                                $row[4], 
                                $row[5], 
                                $row[6], 
                                $conceptEn, 
                                $row[7], 
                                $row[8], 
                                $row[9], 
                                $row[10], 
                                null,
                                null,
                                $row[11],
                                $row[12]);
        }
        //error_log("getWordsForConceptEn, results returned: " . count($words));
        $databaseUtils->free_result($result);
        $databaseUtils->close();
        return $words;
    }
	
    /**
     * Gets all words in the database for a given topic (in English).
     *
     * @param $topicEn The topic to retrieve the words for
     * @param $orderBy The field to order the results by (default is pinyin)
     * @return A array of Word objects
     */
    function getWordsForTopicEn($topicEn, $orderBy = 'pinyin') {
        $databaseUtils = new DatabaseUtils();
        $databaseUtils->getConnection();
        // Perform SQL select operation 
        $query = "SELECT id, simplified, traditional, pinyin, english, grammar, concept_cn, concept_en, topic_cn, topic_en, " .
                 "parent_cn, parent_en, notes, headword " .
                 "FROM words " .
                 "WHERE topic_en = '$topicEn' " .
                 "ORDER BY $orderBy ASC";
        //error_log("getWordsForTopicEn, query: " . $query);
        $result =& $databaseUtils->executeQuery($query);
        $words = array();
        while ($row = $databaseUtils->fetch_array($result)) {
        $words[] = new Word($row[0], 
                            $row[1], 
                            $row[2], 
                            $row[3], 
                            $row[4], 
                            $row[5], 
                            $row[6], 
                            $row[7], 
                            $row[8], 
                            $row[9], 
                            $row[10], 
                            $row[11], 
                            null,
                            null,
                            $row[12],
                            $row[13]);
        }
        //error_log("getWordsForTopicEn, results returned: " . count($words));
        $databaseUtils->free_result($result);
        $databaseUtils->close();
        return $words;
    }
	
    /**
     * Gets the word for the given id.
     *
     * @param $id Unique identifier for the word
     * @return An array of Word objects
     */
    function getWordForId($id) {
        $databaseUtils = new DatabaseUtils();
        $databaseUtils->getConnection();
        // Perform SQL select operation 
        $id = $databaseUtils->escapeString($id);
        $query = "SELECT simplified, traditional, pinyin, english, grammar, concept_cn, concept_en, topic_cn, " .
                 "topic_en, parent_cn, parent_en, image, mp3, notes, headword " .
                 "FROM words " .
                 "WHERE id = '$id'";
        //error_log("getWordForId, query: " . $query);
        $result =& $databaseUtils->executeQuery($query);
        $words = array();
        if ($row = $databaseUtils->fetch_array($result)) {
        $words[] = new Word($id,
                            $row[0], 
                            $row[1], 
                            $row[2], 
                            $row[3], 
                            $row[4], 
                            $row[5], 
                            $row[6], 
                            $row[7], 
                            $row[8], 
                            $row[9], 
                            $row[10], 
                            $row[11],
                            $row[12],
                            $row[13],
                            $row[14]);
        } else {
            error_log("getWordForId, no results found for id: $id");
        }
        //error_log("getWordForId, results returned: " . count($words));
        $databaseUtils->free_result($result);
        $databaseUtils->close();
        return $words;
    }
	
    /** 
     * Gets the word for the given english text.
     *
     * @param $english english text for the word
     * @return An array of Word objects
     */
    function getWordForEnglish($english) {
        $databaseUtils = new DatabaseUtils();
        $databaseUtils->getConnection();
        // Perform SQL select operation 
        $english = $databaseUtils->escapeString($english);
        $query = "SELECT id, simplified, traditional, pinyin, grammar, concept_cn, concept_en, topic_cn, " .
                 "topic_en, parent_cn, parent_en, image, mp3, notes, headword " .
                 "FROM words " .
                 "WHERE english = '$english'";
        //error_log("getWordForId, query: " . $query);
        $result =& $databaseUtils->executeQuery($query);
        $words = array();
        if ($row = $databaseUtils->fetch_array($result)) {
            $words[] = new Word($row[0], 
                                $row[1], 
                                $row[2], 
                                $row[3], 
                                $english,
                                $row[4], 
                                $row[5], 
                                $row[6], 
                                $row[7], 
                                $row[8], 
                                $row[9], 
                                $row[10], 
                                $row[11],
                                $row[12],
                                $row[13],
                                $row[14]);
        }
        //error_log("getWordForEnglish, results returned: " . count($words));
        $databaseUtils->free_result($result);
        $databaseUtils->close();
        return $words;
    }
}
?>

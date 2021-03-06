/*
 * RELATIONAL DATABASE DEFINITIONS FOR DICTIONARY TABLES
 * ============================================================================
 */

/*
 * There are three dictionaries and related tables:
 *
 * words: A Chinese to English word dictionary. Tables include grammar,
 *     topics, and words.
 *
 * characters: A character dictionary. Includes tables characters. Includes
 *     tables characters, character_rend, character_types, 
 *     font_names, radicals, and variants. This table only
 *     has a small number of characters (3425) at the moment. It is not 
 *     large enough for a dictionary of Chinese characters. However, t is used by
 *     some web pages explaining Chinese radicals, fonts, Sanskrit, and IPA.
 *
 * The text files in the same directory map one-to-one to the table names.
 * 
 * Also see files drop.sql and load_data.sql. Create the database 'cse_dict'
 * before executing this file. Use this file by logging into the mysql client
 * and executing the command
 *
 * > source dictionary.ddl
 */

use cse_dict;

/*
 * Table for phonetics
 * id       A unique id for the entry
 * pinyin     Hanyu Pinyin with diacritics for tones
 * tonenumbers    Hanyu Pinyin with numbers for tones
 * notones      Hanyu Pinyin with no tones
 * ipa        International Phonetic Alphabet symbols
 * pronunciation  Type of pronunciation.  Standard Chinese assumed if null.
 * initial      Initial part of the syllable (only if a single syllable)
 * final      Final part of the syllable (only if a single syllable)
 * nosyllables    Number of syllables (integer number)
 * mp3        An mp3 recording of the sound
 * notes      Commentary on the entry
 */
CREATE TABLE phonetics (
  id INT UNSIGNED NOT NULL,
  pinyin VARCHAR(125) NOT NULL,
  tonenumbers VARCHAR(125) NOT NULL,
  notones VARCHAR(125) NOT NULL,
  ipa VARCHAR(125) NOT NULL,
  pronunciation VARCHAR(125),
  initial VARCHAR(125),
  final VARCHAR(125),
  nosyllables INT UNSIGNED NOT NULL,
  mp3 VARCHAR(125),
  notes TEXT,
  PRIMARY KEY (id)
  )
  CHARACTER SET UTF8
  COLLATE utf8_general_ci
;

/*
 * Table for grammar for Chinese words.
 *
 * Each word in the words table should have an entry that matches a record
 * in the grammar table.
 */
CREATE TABLE grammar (english VARCHAR(125) NOT NULL,
                      PRIMARY KEY (english)
                     )
    CHARACTER SET UTF8
    COLLATE utf8_general_ci
;

/*
 * Table for topics
 *
 * Words can be classified into topics. This helps the user know the context within
 * which a word is used.
 *
 * simplified:  Simplified Chinese text describing the topic
 * english:     English text describing the topic
 * url:	        The URL of a page to display information about the topic
 * title:       The title of the page to display information about the topic
 */
CREATE TABLE topics (simplified VARCHAR(125) NOT NULL,
                     english VARCHAR(125) NOT NULL,
                     url VARCHAR(125),
                     title TEXT,
                     PRIMARY KEY (simplified, english)
                    )
    CHARACTER SET UTF8
    COLLATE utf8_general_ci
;

/*
 * Table for Chinese to English mapping of words
 *
 * Each entry in the table represents one sense of a Chinese word.
 *
 * id           A unique identifier for the word
 * simplified:	Simplified Chinese text for the word
 * traditional:	Traditional Chinese text for the word (if different)
 * pinyin:      Hanyu pinyin
 * english:     English text for the word 
 * function:	Grammatical function 
 * concept_cn:	The general concept for the word in Chinese (country, chemical, etc)
 * concept_en:	The general concept for the word in English (country, chemical, etc)
 * topic_cn:	The general topic for the word in Chinese (geography, technology, etc)
 * topic_en:	The general topic for the word in English (geography, technology, etc)
 * parent_cn:	The parent for the concept (Chinese)
 * parent_en:	The parent for the concept (English)
 * mp3:         Name of an audio file for the word
 * image:       The name of a file for an image illustrating the concept
 * notes:       Notes about the word
 */
CREATE TABLE words (id INT UNSIGNED NOT NULL,
                    simplified VARCHAR(255) NOT NULL,
                    traditional VARCHAR(255),
                    pinyin VARCHAR(255) NOT NULL,
                    english VARCHAR(255) NOT NULL,
                    grammar VARCHAR(255),
                    concept_cn VARCHAR(255),
                    concept_en VARCHAR(255),
                    topic_cn VARCHAR(125),
                    topic_en VARCHAR(125),
                    parent_cn VARCHAR(255),
                    parent_en VARCHAR(255),
                    image VARCHAR(255),
                    mp3 VARCHAR(255),
                    notes TEXT,
                    headword INT UNSIGNED NOT NULL,
                    PRIMARY KEY (id),
                    FOREIGN KEY (topic_cn, topic_en) REFERENCES topics(simplified, english),
                    FOREIGN KEY (grammar) REFERENCES grammar(english),
                    INDEX (simplified),
                    INDEX (traditional),
                    INDEX (english)
    )
    CHARACTER SET UTF8
    COLLATE utf8_general_ci
    ;

/*
 * Table for illustration licenses
 *
 * name:              The type of license
 * license_full_name: The unabbreviated name of the license
 * license_url:	      The URL of the license
 */
CREATE TABLE licenses (name VARCHAR(255) NOT NULL,
                       license_full_name VARCHAR(255) NOT NULL,
                       license_url VARCHAR(255),
                       PRIMARY KEY (name)
                      )
    CHARACTER SET UTF8
    COLLATE utf8_general_ci
;

/*
 * Table for illustration authors
 *
 * name:        The name of the creator of the image
 * author_url:	The URL of the home page of the creator of the image
 */
CREATE TABLE authors (name VARCHAR(255),
                      author_url VARCHAR(255),
                      PRIMARY KEY (name)
                     )
    CHARACTER SET UTF8
    COLLATE utf8_general_ci
;

/*
 * Table for illustrations
 *
 * medium_resolution: The file name of a medium resolution image
 * title_zh_cn:       A title in simplified Chinese
 * title_en:          A title in English
 * author:            The creator of the illustration
 * license:           The type of license
 * high_resolution:   The file name of a high resolution image
 */
CREATE TABLE illustrations (medium_resolution VARCHAR(255),
                            title_zh_cn VARCHAR(255) NOT NULL,
                            title_en VARCHAR(255) NOT NULL,
                            author VARCHAR(255),
                            license VARCHAR(255) NOT NULL,
                            high_resolution VARCHAR(255),
                            PRIMARY KEY (medium_resolution),
                            FOREIGN KEY (author) REFERENCES authors(name),
                            FOREIGN KEY (license) REFERENCES licenses(name)
                           )
    CHARACTER SET UTF8
    COLLATE utf8_general_ci
;

/*
 * Table for mapping nominal measure words to matching nouns
 *
 * measure_word:	Simplified Chinese text for the measure word
 * noun:			Simplified Chinese text for the matching noun
 */
CREATE TABLE measure_words (
	measure_word VARCHAR(80),
	noun VARCHAR(80),
	PRIMARY KEY (measure_word, noun),
	FOREIGN KEY (measure_word) REFERENCES words(simplified),
	FOREIGN KEY (noun) REFERENCES words(simplified)
	)
	CHARACTER SET UTF8
	COLLATE utf8_general_ci
;

/*
 * Table for synonyms.
 *
 * simplified1: the first word in the synonym pair
 * simplified2: the second word in the synonym pair
 */
CREATE TABLE synonyms (
	simplified1 VARCHAR(125) NOT NULL,
	simplified2 VARCHAR(125) NOT NULL,
	PRIMARY KEY (simplified1, simplified2),
	FOREIGN KEY (simplified1) REFERENCES words(simplified),
	FOREIGN KEY (simplified2) REFERENCES words(simplified)
	)
	CHARACTER SET UTF8
	COLLATE utf8_general_ci
;

/*
 * Table for related words.
 *
 * Related words are not synonyms but connected because one is an 
 * abbreviation or different way of writing the other
 *
 * simplified1: the first term in the related terms pair, must be a word in the words table
 * simplified2: the second term in the synonym pair, not necessarily a word in the words table
 * note:        describes the type of relationship (abbreviation, etc)
 * link:        a URL describing more about the word relationship
 */
CREATE TABLE related (
	simplified1 VARCHAR(125) NOT NULL,
	simplified2 VARCHAR(125) NOT NULL,
	note VARCHAR(125),
	link VARCHAR(125),
	PRIMARY KEY (simplified1, simplified2),
	FOREIGN KEY (simplified1) REFERENCES words(simplified)
	)
	CHARACTER SET UTF8
	COLLATE utf8_general_ci
;

/*
 * Table for phrase entries.
 *
 * Entries in the phrase table may be identified when parsing new text data.
 * Phrases are tagged using Penn Chinese part-of-speech tag definitions.
 *
 * id:             An id for the phrase entry
 * chinese_phrase: Plain text Chinese
 * pos_tagged:     The phrase tagged with PoS tags, including word and phrase gloss
 * sanskrit:       The Sanskrit equivalent, if known
 * source_no:      The id of the corpus source document
 * source_name:    The name of the source document
 */
CREATE TABLE phrases (
	id INT UNSIGNED NOT NULL,
	chinese_phrase VARCHAR(125) NOT NULL,
	pos_tagged TEXT NOT NULL,
	sanskrit TEXT,
	source_no INT UNSIGNED,
	source_name TEXT NOT NULL,
	PRIMARY KEY (id)
	)
	CHARACTER SET UTF8
	COLLATE utf8_general_ci
;

/*
 * Table for unigram frequency.
 *
 * This table records the frequency for the word sense of single words in the 
 * tagged corpus. The Penn Treebank syntax is used for part-of-speech tags.
 *
 * pos_tagged_text: The element text with POS tag and gloss in pinyin and English
 * element_text:    The element text in traditional Chinese
 * word_id:         Matching id in the word table (positive integer)
 * frequency:       The frequency of occurence of the word sense (positive integer)
 */
CREATE TABLE unigram (
	pos_tagged_text VARCHAR(125) NOT NULL,
	element_text VARCHAR(125) NOT NULL,
	word_id INT UNSIGNED NOT NULL,
	frequency INT UNSIGNED NOT NULL,
	PRIMARY KEY (pos_tagged_text),
	FOREIGN KEY (word_id) REFERENCES words(id)
	)
	CHARACTER SET UTF8
	COLLATE utf8_general_ci
;

/*
 * Table for bigram frequency.
 *
 * This table records the frequency for the word sense of sequences of two words 
 * in the tagged corpus.
 *
 * pos_tagged_text: The element text with POS tag and gloss in pinyin and English
 * previous_text:    The element text in traditional Chinese
 * element_text:    The element text in traditional Chinese
 * word_id:         Matching id in the word table (positive integer)
 * frequency:       The frequency of occurence of the word sense (positive integer)
 */
CREATE TABLE bigram (
        id MEDIUMINT NOT NULL AUTO_INCREMENT,
	pos_tagged_text TEXT,
	previous_text VARCHAR(125),
	element_text VARCHAR(125),
	word_id INT UNSIGNED,
	frequency INT UNSIGNED,
	PRIMARY KEY (id),
	FOREIGN KEY (word_id) REFERENCES words(id)
	)
	CHARACTER SET UTF8
	COLLATE utf8_general_ci
;

/*
 * Table for Kangxi radicals
 *
 * id           A unique identifier for the radical
 * traditional  Traditional Chinese text for the radical
 * simplified   Simplified Chinese text for the radical (if different)
 * pinyin       Hanyu pinyin
 * strokes      The number of strokes
 * other_forms  Other forms of the radical
 * english      English text for the radical 
 */
CREATE TABLE radicals (id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                       traditional VARCHAR(10) NOT NULL,
                       simplified VARCHAR(10),
                       pinyin VARCHAR(30),
                       strokes INT UNSIGNED NOT NULL,
                       simplified_strokes INT UNSIGNED,
                       other_forms VARCHAR(255),
                       english VARCHAR(255) NOT NULL,
                       PRIMARY KEY (id),
                       INDEX (traditional)
                      )
    CHARACTER SET UTF8
    COLLATE utf8_general_ci
;

/*
 * Types of characters. For example, Sanskrit, traditional Chinese, IPA.
 * 
 * type   A key for the type name
 * name   An English name for the type
 */
CREATE TABLE character_types (
	type VARCHAR(125) NOT NULL,
	name VARCHAR(125) NOT NULL,
	PRIMARY KEY (type)
	)
	CHARACTER SET UTF8
	COLLATE utf8_general_ci
;

/*
 * Table for character dictionary.
 * 
 * Table maps characters to English meaning with Unicode codes as primary key. 
 * Most entries are Chinese characters. There are fields for number of strokes and pronuncia
 *
 * unicode        The Unicode unique identifier for the character (decimal)
 * c              Chinese text for the character (simplified, traditional, or other symbol)
 * pinyin         Hanyu pinyin
 * radical        Main radical
 * strokes        The number of strokes
 * other_strokes  The number of strokes other than the main radical
 * english        English text for the radical 
 * notes:         Miscellaneous notes about the character, if any
 */
CREATE TABLE characters (unicode INT UNSIGNED NOT NULL,
                         c VARCHAR(10) NOT NULL,
                         pinyin VARCHAR(80),
                         radical VARCHAR(10),
                         strokes INT UNSIGNED,
                         other_strokes INT UNSIGNED,
                         english VARCHAR(255) NOT NULL,
                         notes TEXT,
                         type VARCHAR(125) NOT NULL,
                         PRIMARY KEY (unicode),
                         FOREIGN KEY (type) REFERENCES character_types(type),
                         UNIQUE (c),
                         INDEX (c)
                        )
    CHARACTER SET UTF8
    COLLATE utf8_general_ci
;

/*
 * Table for relationship between character variants, traditional / simplified and other variant
 * c1             The UTF-8 text for the subject character
 * c2:            The UTF-8 text for the variant character
 * relation_type: Traditional / simplified or other variant
 */
CREATE TABLE variants (
	c1 VARCHAR(10) NOT NULL,
	c2 VARCHAR(10) NOT NULL,
	relation_type VARCHAR(255) NOT NULL,
	PRIMARY KEY (c1,c2),
	FOREIGN KEY (c1) REFERENCES characters(c),
	FOREIGN KEY (c2) REFERENCES characters(c),
	INDEX (c1),
	INDEX (c2)
	)
	CHARACTER SET UTF8
	COLLATE utf8_general_ci
;

/*
 * Table for font names
 * font_name_en	The name of the font that the character is rendered in (English)
 * font_name_zh	The name of the font that the character is rendered in (Chinese)
 */
CREATE TABLE font_names (
	font_name_en VARCHAR(80) NOT NULL,
	font_name_zh VARCHAR(80) NOT NULL,
	PRIMARY KEY (font_name_en)
	)
	CHARACTER SET UTF8
	COLLATE utf8_general_ci
;

/*
 * Table for character renderings in different fonts
 * unicode		The Unicode unique identifier for the character (decimal)
 * font_name_en	The name of the font that the character is rendered in (English)
 * image		The name of the image file
 * svg			The name of the svg file
 */
CREATE TABLE character_rend (
	unicode INT UNSIGNED NOT NULL,
	font_name_en VARCHAR(80) NOT NULL,
	image VARCHAR(80) NOT NULL,
	svg VARCHAR(80) NOT NULL,
	PRIMARY KEY (unicode, font_name_en),
	FOREIGN KEY (unicode) REFERENCES characters(unicode),
	FOREIGN KEY (font_name_en) REFERENCES font_names(font_name_en)
	)
	CHARACTER SET UTF8
	COLLATE utf8_general_ci
;

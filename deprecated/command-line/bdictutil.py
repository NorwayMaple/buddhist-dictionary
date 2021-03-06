"""Command line to compile a vocabulary from a text file. =====================

The words in the text file will be compared against the dictionary and word
frequencies will be generated. See PrintUsage() for details.
"""
import locale
import sys

from bdict import app_exceptions
from bdict import chinesevocab
from bdict import cjktextreader
from bdict import configmanager
from bdict import corpusmanager
from bdict import corpusstats
from bdict import glossgenerator
from bdict import postaggeraccuracy
from bdict import sanskritglossgenerator
from bdict import sanskritvocab
from bdict import taggeddoc


def PrintUsage():
    print(
"""Usage: python bdictutil.py <command> [arguments].
Supported commands: buildvocab, generategloss, help, listcorpus, tag wordsensefreq.

The buildvocab command builds a vocabulary with word frequency analysis from a
corpus document. Usage:

    python bdictutil.py buildvocab <doc_num>  [collection_name]

Example:

    python bdictutil.py buildvocab 1

Get the corpus document number from the listcorpus command. If a 
collection_name is supplied then the document should be in file
collection_name.txt rather than corpus.txt.

The generatejson command generates a JSON file with corpus metadata. Usage:

    python bdictutil.py generatejson [collection_name]

If no collection name is supplied then the data will be written to a file
called corpus.json. If a collection name is supplied, for example,
'gaosengzhuan', then the file will be written to collection_name.json.

The generategloss command generates a file with gloss in HTML format. Usage:

    python bdictutil.py generategloss <doc_num> [--wholetext] [collection_name]

If the --wholetext option is included then the whole text will be output,
ignoring start and end markers.

The listcorpus command lists all the documents in the corpus (aka library).
Usage:

    python bdictutil.py listcorpus

The tag command generates part-of-speech tags for a document in the corpus.
Usage:

    python bdictutil.py tag <doc_num> [collection_name]

The transcode command transcodes a file from simplified to traditional Chinese:
Usage:

    python bdictutil.py transcode infile

The wordsensefreq command generates word sense frequency from part-of-speech
tagged documents in the corpus. Usage:

    python bdictutil.py wordsensefreq
""")

def main():
    locale.setlocale(locale.LC_ALL, '')
    if len(sys.argv) < 2:
        print('You must have at one argument following bdictutil.py')
        PrintUsage()
        sys.exit(2)
    command = sys.argv[1]
    if command == 'buildvocab':
        corpus_entries = []
        cmanager = corpusmanager.CorpusManager()
        collection_file = GetCollectionFile(sys.argv)
        if len(sys.argv) < 3:
            print('Analyzing the whole corpus')
            corpus_entries = cmanager.LoadCorpusFlattened()
            print('Scanning %d corpus files' % len(corpus_entries))
        else:
            corpus = cmanager.LoadCorpus(collection_file)
            doc_num = int(sys.argv[2])
            corpus_entry = corpus[doc_num-1]
            corpus_entries.append(corpus_entry)
        vocabstats = corpusstats.CorpusStats()
        for corpus_entry in corpus_entries:
            try:
                language = corpus_entry['language']
                languages = ['Sanskrit', 'Chinese']
                if language not in languages:
                    print('Language found %s is not supported' % language) 
                    print('Please use one of these languages: %s' % languages) 
                    pass
                if language == 'Sanskrit':
                    vocab = sanskritvocab.SanskritVocabulary()
                    vocab.BuildVocabulary(corpus_entry)
                elif language == 'Chinese':
                    vocab = chinesevocab.ChineseVocabulary()
                    result = vocab.BuildVocabulary(corpus_entry)
                vocabstats.Add(result)
            except app_exceptions.BDictException:
                print('Exception analyzing vocabulary for a corpus entry. '
                      'Skipping this entry and continuing')
        vocabstats.WriteStats()
    elif command == 'generategloss':
        if len(sys.argv) < 3:
            print('A document number is required for the generategloss command')
            PrintUsage()
            sys.exit(2)
        doc_num = int(sys.argv[2])
        cmanager = corpusmanager.CorpusManager()
        wholetext = False
        popover = False
        if len(sys.argv) > 3:
            wholetext = sys.argv[3] == '--wholetext'
        if len(sys.argv) > 3:
            popover = sys.argv[3] == '--popover'
        print('Reading doc %d, whole text: %s' % (doc_num, wholetext))
        collection_file = GetCollectionFile(sys.argv)
        corpus = cmanager.LoadCorpus(collection_file)
        corpus_entry = corpus[doc_num-1]
        corpusmanager.PrintCorpusEntry(corpus_entry)
        language = corpus_entry['language']
        languages = ['Sanskrit', 'Chinese']
        if language not in languages:
            print('Language found %s is not supported' % language) 
            print('Please use one of these languages: %s' % languages) 
            sys.exit(2)
        if language == 'Sanskrit':
            generator = sanskritglossgenerator.GlossGenerator()
        elif language == 'Chinese':
            generator = glossgenerator.GlossGenerator(wholetext=wholetext, 
                                                      charset=corpus_entry['charset'],
                                                      popover=popover)
        filename = generator.WriteDoc(corpus_entry)
        print('Wrote output to file %s' % filename)
    elif command == 'generatejson':
        cmanager = corpusmanager.CorpusManager()
        if len(sys.argv) == 2:
            filename = cmanager.GenCorpusJSON()
            print('Generated corpus JSON as file: %s' % filename)
        else:
            corpus_file = sys.argv[2]
            filename = cmanager.GenCorpusJSON(corpus_file)
            print('Generated corpus JSON as file: %s' % filename)
    elif command == 'help':
        PrintUsage()
    elif command == 'listcorpus':
        cmanager = corpusmanager.CorpusManager()
        cmanager.PrintCorpus()
    elif command == 'tag':
        if len(sys.argv) < 3:
            print('A document number is required for the tag command')
            PrintUsage()
            sys.exit(2)
        doc_num = int(sys.argv[2])
        cmanager = corpusmanager.CorpusManager()
        collection_file = GetCollectionFile(sys.argv)
        corpus = cmanager.LoadCorpus(collection_file)
        corpus_entry = corpus[doc_num-1]
        generator = glossgenerator.GlossGenerator(output_type=glossgenerator.POS_TAGGED_TYPE)
        filename = generator.WriteDoc(corpus_entry)
        print('Wrote output to file %s' % filename)
        accuracy = postaggeraccuracy.TaggerAccuracy(corpus_entry, filename)
    elif command == 'transcode':
        if len(sys.argv) < 3:
            print('A filename is required for the transcode command')
            PrintUsage()
            sys.exit(2)
        infile = sys.argv[2]
        reader = cjktextreader.CJKTextReader()
        outfile = reader.TranscodeTrad(infile)
    elif command == 'wordsensefreq':
        if len(sys.argv) < 3:
            doc_analyzer = taggeddoc.TaggedDocumentAnalyzer()
            results = doc_analyzer.WordSenseForCorpus()
            print('Word count for corpus: %d' % results[0])
            print('Unigram word sense frequency compiled to %s.' % results[1])
            print('Bigram word sense frequency compiled to %s.' % results[2])
        else:
            print('A document number is required for the wordsensefreq command')
            PrintUsage()
            sys.exit(2)
    else:
        print('Did not understand command.')
        PrintUsage()


def GetCollectionFile(argv):
    collection_name = 'corpus'
    if len(argv) > 3 and argv[-1] != '--wholetext':
        collection_name = argv[-1]
    collection_file = '%s.txt' % collection_name
    # print('Collection %s' % collection_name)
    return collection_file


if __name__ == "__main__":
    main()


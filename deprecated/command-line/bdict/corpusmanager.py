"""Module to read the corpus (aka library) data. ==============================

The corpus documents are listed in the file $PROJECT_HOME/data/corpos/corpus.txt
"""
import codecs

from bdict import configmanager

CORPUS_FILE = 'corpus.txt'
WHOLE_COLLECTION = 'corpus'
JSON_FILE = 'corpus.json'
JSON_DIR = '../web/script/'

class CorpusManager:
    """Reads and prints the corpus data.

    Loads the corpus based on entries in corpus file.
    """

    def __init__(self):
        """Constructor for CorpusManager class.
        """
        manager = configmanager.ConfigurationManager()
        self.config = manager.LoadConfig()

    def GetAllTagged(self):
        """Returns a list of corpus entries for all POS tagged documents in the corpus.

        Returns:
            A list of corpus entries.
        """
        tagged_entries = []
        corpus = self.LoadCorpusFlattened()
        for entry in corpus:
            if 'pos_tagged' in entry:
                tagged_entries.append(entry)
            if 'type' in entry and entry['type'] == 'collection':
                collection_file = '%s.txt' % entry['uri']
                collection = self.LoadCorpus(collection_file)
                for col_entry in collection:
                    if 'pos_tagged' in col_entry:
                        tagged_entries.append(col_entry)
        return tagged_entries

    def LoadCorpus(self, corpus_file=CORPUS_FILE):
        """Loads the corpus text file into memory.

        Returns:
            A list of corpus entries.

        Args:
            corpus_file: name of a file listing corpus entries
        """
        corpus_data_dir = self.config['corpus_data_dir']
        fullpath = '%s/%s' % (corpus_data_dir, corpus_file)
        # print('Loading corpus from %s' % fullpath)
        corpus = []
        with codecs.open(fullpath, 'r', "utf-8") as f:
            for line in f:
                if not line.strip():
                    continue
                tokens = line.split('\t')
                if tokens:
                    entry = {}
                    entry['id'] = tokens[0]
                    if len(tokens) > 1:
                        entry['source_name'] = tokens[1]
                    # print('%s : %s' % (entry['id'], entry['source_name']))
                    if len(tokens) > 2:
                        entry['type'] = tokens[2]
                    if len(tokens) > 3:
                        entry['language'] = tokens[3]
                    if len(tokens) > 4:
                        entry['charset'] = tokens[4]
                    if len(tokens) > 5:
                        entry['doc_type'] = tokens[5]
                    if len(tokens) > 6:
                        entry['uri'] = tokens[6]
                    # print('URI: %s' % entry['uri'])
                    if len(tokens) > 7:
                        entry['source'] = tokens[7]
                    if len(tokens) > 8:
                        entry['start'] = tokens[8]
                    if len(tokens) > 9:
                        if tokens[9].strip() != u'\\N':
                        	entry['end'] = tokens[9]
                    if len(tokens) > 10:
                        if tokens[10].strip() != u'\\N':
                        	entry['plain_text'] = tokens[10].strip()
                    if len(tokens) > 11:
                        if tokens[11].strip() != u'\\N':
                        	entry['translator'] = tokens[11].strip()
                    if len(tokens) > 12:
                        if tokens[12].strip() != u'\\N':
                        	entry['reference'] = tokens[12].strip()
                    if len(tokens) > 13:
                        if tokens[13].strip() != u'\\N':
                        	entry['genre'] = tokens[13].strip()
                    if len(tokens) > 14:
                        if tokens[14].strip() != u'\\N':
                        	entry['period'] = tokens[14].strip()
                    if len(tokens) > 15:
                        if tokens[15].strip() != u'\\N':
                        	entry['pos_tagged'] = tokens[15].strip()
                    if len(tokens) > 16:
                        if tokens[16].strip() != u'\\N':
                        	entry['analysis_file'] = tokens[16].strip()
                    if len(tokens) > 17:
                        if tokens[17].strip() != u'\\N':
                        	entry['gloss_file'] = tokens[17].strip()
                    if len(tokens) > 18:
                        if tokens[18].strip() != u'\\N':
                        	entry['short_name'] = tokens[18].strip()
                    if len(tokens) > 19:
                        if tokens[19].strip() != u'\\N':
                        	entry['description'] = tokens[19].strip()
                    corpus.append(entry)
        return corpus

    def LoadCorpusFlattened(self):
        """Loads the flattened corpus, including collections, for analysis.

        Used in performing a vocabulary analysis for the whole corpus.
        This excludes entries that do not have a value for analysis_file
        and those that are not in Chinese.

        Returns:
            A list of corpus entries with analysis files.
        """
        corpus_entries = []
        corpus = self.LoadCorpus()
        for corp_entry in corpus:
            entry_type = corp_entry['type']
            if entry_type == 'file' and 'analysis_file' in corp_entry:
                source_name = corp_entry['source_name']
                #print('Adding corpus entry %s.' % source_name)
                corpus_entries.append(corp_entry)
            elif entry_type == 'collection':
                collection_name = corp_entry['uri']
                collection_file = '%s.txt' % collection_name
                collection = self.LoadCorpus(collection_file)
                for collection_entry in collection:
                    source_name = collection_entry['source_name']
                    col_entry_type = collection_entry['type']
                    lang = collection_entry['language']
                    if (col_entry_type == 'file' 
                        and 
                        'analysis_file' in collection_entry
                        and
                        lang == 'Chinese'):
                        #print('Adding collection entry %s.' % source_name)
                        corpus_entries.append(collection_entry)
                    #else:
                        #print('Will not add collection entry %s with type %s.' % 
                               #(source_name, col_entry_type))
            else:
                print('Cannot add entry %s with type %s.' % 
                      (source_name, entry_type))
        return corpus_entries

    def LoadCorpusAll(self):
        """Loads the whole corpus, flattened, including members of collections.

        Used in generate JSON for the whole corpus.

        Returns:
            A list of all corpus entries.
        """
        corpus_entries = []
        corpus = self.LoadCorpus()
        for corp_entry in corpus:
            entry_type = corp_entry['type']
            if entry_type != 'collection':
                corpus_entries.append(corp_entry)
            else:
                collection_name = corp_entry['uri']
                collection_file = '%s.txt' % collection_name
                collection = self.LoadCorpus(collection_file)
                for collection_entry in collection:
                    corpus_entries.append(collection_entry)
        return corpus_entries

    def PrintCorpus(self):
        """Prints the corpus data to standard output.

        Only the entries with plain text files are printed because those are
        only files that the command line tool can operate on.
        """
        corpus = self.LoadCorpus()
        print('Corpus entries with plain text files')
        for entry in corpus:
            if 'plain_text' in entry:
                print('%s\t%s\t%s' % (entry['id'], entry['source_name'],
                      entry['plain_text']))

    def GenCorpusJSON(self, collection=WHOLE_COLLECTION):
        """Prints the corpus data out in json format.
 
        Loads the corpus from the corpus.txt file and translates into JSON.

        Returns:
          The name of the file written to.
        """
        if collection == 'all':
            corpus = self.LoadCorpusAll()
            output_file = 'corpus_all.json'
        else:
            input_file = '%s.txt' % collection
            corpus = self.LoadCorpus(input_file)
            output_file = '%s.json' % collection
        output_file = '%s%s' % (JSON_DIR, output_file)
        with codecs.open(output_file, 'w', "utf-8") as f:
            f.write('[')
            for i in range(len(corpus)):
                doc = corpus[i]
                f.write('{')
                j = 0
                for key in doc.keys():
                    if doc[key] == '\\N':
                        f.write('"%s": ""' % (key))
                    else:
                        f.write('"%s": "%s"' % (key, doc[key]))
                    if j != len(doc.keys()) - 1:
                        f.write(',')
                    j += 1
                f.write('}\n')
                if i != len(corpus) - 1:
                    f.write(',\n')
            f.write(']')
            f.close()
        return output_file

def PrintCorpusEntry(entry):
    """Prints the corpus entry to standard output.

    Args:
        corpus_entry: the corpus entry to print
    """
    print("CorpusManager.PrintCorpusEntry")
    print("id:\t%s\nsource_name:\t%s\nplain_text:\t%s\ntype:\t%s\n"
          "language:\t%s\ncharset:\t%s\ndoc_type:\t%s\nuri:\t%s\nsource:\t%s\n"
          "start:\t%s\nend:\t%s\nplain_text:\t%s\n" %
          (entry['id'], entry['source_name'], entry['plain_text'],
           entry['type'], entry['language'], entry['charset'],
           entry['doc_type'], entry['uri'], entry['source'], entry['start'],
           entry['end'], entry['plain_text']))

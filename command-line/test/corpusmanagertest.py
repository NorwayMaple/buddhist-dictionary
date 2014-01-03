"""Unit tests for the bdict.corpusmanager module.

Tests the methods for loading the corpus file.
"""

import unittest

from bdict import corpusmanager


class CorpusManagerTest(unittest.TestCase):

    def testLoadCorpus(self):
        manager = corpusmanager.CorpusManager()
        corpus = manager.LoadCorpus()
        self.assertTrue(corpus)
        self.assertTrue(len(corpus) > 0)
        entry = corpus[0]
        self.assertEquals(entry['plain_text'], 'diamond-sutra-sanskrit.txt')


    def testGetAllTagged(self):
        manager = corpusmanager.CorpusManager()
        entries = manager.GetAllTagged()
        self.assertTrue(entries)


if __name__ == '__main__':
    unittest.main()

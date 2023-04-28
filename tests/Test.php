<?php

use PHPUnit\Framework\TestCase;
use LOTR\LOTR;

final class Test extends TestCase
{
    private $apiKey = '<YOUR_API_KEY>';
    private $LOTR;

    protected function setUp(): void
    {
        $this->LOTR = new LOTR($this->apiKey);
    }

    /**
     * Test the getMovies function.
     * It should return a JSON response with a list of all movies
     */
    public function testGetMovies()
    {
        $expectedNames = [
            "The Lord of the Rings Series",
            "The Hobbit Series",
            "The Unexpected Journey",
            "The Desolation of Smaug",
            "The Battle of the Five Armies",
            "The Two Towers",
            "The Fellowship of the Ring",
            "The Return of the King"
        ];
        $movies = $this->LOTR->getMovies();

        // The respose should be an array
        $this->assertIsArray($movies);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $movies);

        $docs = $movies['docs'];

        // check that there are 8 movies in the response
        $this->assertEquals(8, $movies['total']);
        $this->assertCount(8, $docs);

        foreach ($docs as $doc) {
            $this->assertContains($doc['name'], $expectedNames);
        }
    }

    /**
     * Test the getMovie function.
     * It should return a JSON response with the movie information of "The Lord of the Rings Series"
     */
    public function testGetMovieById()
    {
        // "The Lord of the Rings Series"
        $movieId = '5cd95395de30eff6ebccde56';
        $movie = $this->LOTR->getMovie($movieId);

        // The respose should be an array
        $this->assertIsArray($movie);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $movie);

        $docs = $movie['docs'];

        // check that there's only one response
        $this->assertEquals(1, $movie['total']);
        $this->assertCount(1, $docs);

        // check that the specified movie id matches the one returned
        $this->assertArrayHasKey('_id', $docs[0]);
        $this->assertEquals($docs[0]['_id'], $movieId);

        // check that some other expected values for the requested movie match
        $this->assertEquals($docs[0]['name'], "The Lord of the Rings Series");
        $this->assertEquals($docs[0]['boxOfficeRevenueInMillions'], 2917);
    }

    /**
     * Test the getMovieQuotes function.
     * It should return a JSON response with all movie quotes for "The Two Towers"
     */
    public function testGetMovieQuotesById()
    {
        // "The Two Towers"
        $movieId = '5cd95395de30eff6ebccde5b';
        $quotes = $this->LOTR->getMovieQuotes($movieId);

        // The response should be an array
        $this->assertIsArray($quotes);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $quotes);

        $docs = $quotes['docs'];

        // check that the returned quote and page counts are correct
        $this->assertEquals(1009, $quotes['total']);
        $this->assertCount(1000, $docs);
        $this->assertEquals(2, $quotes['pages']);

        // check that the movie node exists and that the id matches the one of the first returned quote
        $this->assertArrayHasKey('movie', $docs[0]);
        $this->assertEquals($docs[0]['movie'], $movieId);

        // check that some other expected values for the requested movie quotes match
        $this->assertEquals($docs[0]['dialog'], "Sauron's wrath will be terrible, his retribution swift.");
        $this->assertEquals($docs[0]['character'], "5cd99d4bde30eff6ebccfea0");
    }

    /**
     * Test the getQuotes function.
     * It should return a JSON response with all movie quotes
     */
    public function testGetQuotes()
    {
        $quotes = $this->LOTR->getQuotes();

        // The response should be an array
        $this->assertIsArray($quotes);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $quotes);

        $docs = $quotes['docs'];

        // check that the returned quote and page counts are correct
        $this->assertEquals(2384, $quotes['total']);
        $this->assertCount(1000, $docs);
        $this->assertEquals(3, $quotes['pages']);

        // check that the movie node exists and that the id of the first returned quote is the expected one
        $this->assertArrayHasKey('movie', $docs[0]);
        $this->assertEquals($docs[0]['movie'], "5cd95395de30eff6ebccde5d");

        // check that some other expected values for the requested quotes match
        $this->assertEquals($docs[0]['dialog'], "Deagol!");
        $this->assertEquals($docs[1]['character'], "5cd99d4bde30eff6ebccfe9e");
        $this->assertEquals($docs[2]['id'], "5cd96e05de30eff6ebcce7eb");
    }

    /**
     * Test the getQuote function.
     * It should return a JSON response with a specific movie quote
     */
    public function testGetQuoteById()
    {
        $quoteId = '5cd96e05de30eff6ebcce7e9';
        $quote = $this->LOTR->getQuote($quoteId);

        // The response should be an array
        $this->assertIsArray($quote);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $quote);

        $docs = $quote['docs'];

        // check that there's only one response
        $this->assertEquals(1, $quote['total']);
        $this->assertCount(1, $docs);

        // check that the specified movie id matches the one returned
        $this->assertArrayHasKey('_id', $docs[0]);
        $this->assertEquals($docs[0]['_id'], $quoteId);

        // check that some other expected values for the requested quote match
        $this->assertEquals($docs[0]['dialog'], "Deagol!");
        $this->assertEquals($docs[0]['character'], "5cd99d4bde30eff6ebccfe9e");
        $this->assertEquals($docs[0]['id'], "5cd96e05de30eff6ebcce7e9");
    }

    /**
     * Test the getMovies function with a limit of 2.
     */
    public function testGetLimitedMovies()
    {
        $expectedNames = [
            "The Lord of the Rings Series",
            "The Hobbit Series"
        ];
        $this->LOTR->setLimit(2);
        $movies = $this->LOTR->getMovies();

        // The response should be an array
        $this->assertIsArray($movies);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $movies);

        $docs = $movies['docs'];

        // check that there are 2 movies in the response
        $this->assertEquals(2, $movies['limit']);
        $this->assertCount(2, $docs);

        foreach ($docs as $doc) {
            $this->assertContains($doc['name'], $expectedNames);
        }
    }

    /**
     * Test the getMovies function with a limit of 2 and the second page.
     */
    public function testGetSecondPageOfLimitedMovies()
    {
        $expectedNames = [
            "The Unexpected Journey",
            "The Desolation of Smaug"
        ];
        $this->LOTR->setLimit(2);
        $this->LOTR->setPage(2);
        $movies = $this->LOTR->getMovies();

        // The response should be an array
        $this->assertIsArray($movies);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $movies);

        $docs = $movies['docs'];

        // check that there are 2 movies in the response
        $this->assertEquals(2, $movies['limit']);
        $this->assertCount(2, $docs);

        foreach ($docs as $doc) {
            $this->assertContains($doc['name'], $expectedNames);
        }
    }

    /**
     * Test the getMovies function with an offset of 1 and a limit of 2.
     * NOTE | When the `offset` is set, the `page` does nothing!
     */
    public function testGetLimitedMoviesWithOffset()
    {
        $expectedNames = [
            "The Hobbit Series",
            "The Unexpected Journey"
        ];
        $this->LOTR->setOffset(1);
        $this->LOTR->setLimit(2);
        $movies = $this->LOTR->getMovies();

        // The response should be an array
        $this->assertIsArray($movies);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $movies);

        $docs = $movies['docs'];

        // check that there are 2 movies in the response
        $this->assertEquals(2, $movies['limit']);
        $this->assertCount(2, $docs);

        foreach ($docs as $doc) {
            $this->assertContains($doc['name'], $expectedNames);
        }
    }

    /**
     * Test the getMovies function sorted by descending order of award wins and a limited to 2 per page.
     */
    public function testGetLimitedMoviesSortByDescendingAwardWins()
    {
        $expectedNames = [
            "The Lord of the Rings Series",
            "The Return of the King"
        ];
        $this->LOTR->setLimit(2);
        $this->LOTR->setSort('academyAwardWins', 'desc');
        $movies = $this->LOTR->getMovies();

        // The response should be an array
        $this->assertIsArray($movies);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $movies);

        $docs = $movies['docs'];

        // check that there are 2 movies in the response
        $this->assertEquals(2, $movies['limit']);
        $this->assertCount(2, $docs);

        foreach ($docs as $doc) {
            $this->assertContains($doc['name'], $expectedNames);
        }
    }

    /**
     * Test the getCharacters function filtered by those who are of `Hobbit` or `Human` race, do not have `Blonde`
     * hair and whose name contains `king`. Limited to 3 results per page.
     * ?limit=3&race=Hobbit,Human&hair!=Blonde&name=/king/i
     */
    public function testGetFilteredAndLimitedCharacters()
    {
        $filters = [
            [
                'key' => 'race',
                'filter_type' => 'include',
                'value' => 'Hobbit,Human'
            ],
            [
                'key' => 'hair',
                'filter_type' => 'not_match',
                'value' => 'Blonde'
            ],
            [
                'key' => 'name',
                'filter_type' => 'regex_match',
                'value' => 'king'
            ]
        ];
        $expectedNames = [
            "Eldacar (King of Arnor)",
            "Eldacar (King of Gondor)",
            "The King of the Dead"
        ];
        $this->LOTR->setLimit(3);
        $this->LOTR->setFilters($filters);
        $movies = $this->LOTR->getCharacters();

        // The response should be an array
        $this->assertIsArray($movies);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $movies);

        $docs = $movies['docs'];

        // check that there are 3 charcters in the response
        $this->assertEquals(3, $movies['limit']);
        $this->assertEquals(4, $movies['total']);
        $this->assertEquals(2, $movies['pages']);
        $this->assertCount(3, $docs);

        foreach ($docs as $doc) {
            $this->assertContains($doc['name'], $expectedNames);
        }
    }

    /**
     * Test the getMovies function filtered by those whose revenue was more than 1 billion and which won at least 1
     * academy award. Limited to 1 results per page.
     * ?limit=1&boxOfficeRevenueInMillions<1000&academyAwardWins>=1&sort=academyAwardNominations:desc
     */
    public function testGetFilteredMoviesByRevenue()
    {
        $filters = [
            [
                'key' => 'boxOfficeRevenueInMillions',
                'filter_type' => '<',
                'value' => 1000
            ],
            [
                'key' => 'academyAwardWins',
                'filter_type' => '>=',
                'value' => 1
            ]
        ];
        $expectedNames = [
            "The Fellowship of the Ring"
        ];
        $this->LOTR->setLimit(1);
        $this->LOTR->setFilters($filters);
        $this->LOTR->setSort('academyAwardNominations', 'desc');
        $movies = $this->LOTR->getMovies();

        // The response should be an array
        $this->assertIsArray($movies);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $movies);

        $docs = $movies['docs'];

        // check that there is 1 movie in the response
        $this->assertEquals(1, $movies['limit']);
        $this->assertEquals(2, $movies['total']);
        $this->assertEquals(2, $movies['pages']);
        $this->assertCount(1, $docs);

        foreach ($docs as $doc) {
            $this->assertContains($doc['name'], $expectedNames);
        }
    }

    /**
     * Test the getMovies function with an invalid ID
     */
    public function testGetMovieByIdInvalidIdError()
    {
        $invalidMovieId = 'bad id';
        $this->expectException(Exception::class);
        $this->LOTR->getMovie($invalidMovieId);
    }

    /**
     * Test the getMovies function with a comparison operator filter that should fail
     * The filter will be omitted and all the results will be returned
     */
    public function testGetFilteredMoviesComparisonOperatorsError()
    {
        $filters = [
            [
                'key' => 'boxOfficeRevenueInMillions',
                'filter_type' => '<',
                'value' => 'a'
            ],
        ];
        $this->LOTR->setFilters($filters);
        $movies = $this->LOTR->getMovies();

        // The response should be an array
        $this->assertIsArray($movies);

        // The response should contain a 'docs' node
        $this->assertArrayHasKey('docs', $movies);

        $docs = $movies['docs'];

        // check that there are 8 movies in the response. i.e. the filter was not applied
        $this->assertEquals(8, $movies['total']);
        $this->assertEquals(1, $movies['pages']);
        $this->assertCount(8, $docs);
    }
}

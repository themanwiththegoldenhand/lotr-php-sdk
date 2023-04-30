# lotr-php-sdk
## A Lord of the Rings (LOTR) PHP SDK

This is the PHP SDK for the Lord of the Rings API available at https://the-one-api.dev/v2.

Documentation for the API can be found at https://the-one-api.dev/documentation.

You will need to generate your own unique Access token, which you can obtain from https://the-one-api.dev/sign-up

## Installation
You can install the LOTR SDK using Composer. 

First, make sure you have Composer installed on your system. 

Then, run `composer init` and `composer require themanwiththegoldenhand/lotr-php-sdk`.

Alternatively, you can download the `LOTR.php` file and include it in your project manually.

## Usage
To use the LOTR SDK in your project, you first need to create a new instance of the LOTR\LOTR class:

```php
use LOTR\LOTR;
require_once '/path/to/vendor/autoload.php';
$lotr = new LOTR(YOUR_APIKEY);
```

###### The usage examples in this README assume you have installed the package via Composer.
###### Make sure to replace `YOUR_APIKEY` with the Access token, which you can obtain from https://the-one-api.dev/sign-up.

You can then use the client to make requests to the LOTR API. For example, to get a list of all movies:

```php
$response = $lotr->getMovies();
```

The `$response` variable will contain the response from the API, which you can then manipulate as needed.

## Further Usage Examples

##### Get a list of all movies
```php
$response = $lotr->getMovies();
```

##### Request one specific movie
```php
$response = $lotr->getMovie('5cd95395de30eff6ebccde56');
```

##### Request all movie quotes for one specific movie 
###### (only working for the LotR trilogy)
```php
$response = $lotr->getMovieQuotes('5cd95395de30eff6ebccde5b');
```

##### Get a list of all movie quotes
```php
$response = $lotr->getQuotes();
```

##### Request one specific movie quote
```php
$response = $lotr->getQuote('5cd96e05de30eff6ebcce7e9');
```

##### Limiting results
###### The movies service paginated with up to 2 results per page
```php
$lotr->setLimit(2);
$response = $lotr->getMovies();
```

##### Paginating results
###### The second page of the movies service paginated with up to 2 results per page
```php
$lotr->setLimit(2);
$lotr->setPage(2);
$response = $lotr->getMovies();
```

##### Offsetting results
###### The movies service paginated with up to 2 results per page and offset by 1 result
```php
$lotr->setOffset(1);
$lotr->setLimit(2);
$response = $lotr->getMovies();
```
###### Note: If the offset is set, setting the page will not be applied

##### Sorting results
###### The movies results sorted by descending order of `academyAwardWins`
```php
$lotr->setSort('academyAwardWins', 'desc');
$response = $lotr->getMovies();
```
###### Note: Sorting can be applied against any valid key with either an `asc` or `desc` direction,

##### Filtering results (Example 1)
###### The character results filtered by those who are of `Hobbit` or `Human` race, do not have `Blonde` hair and whose name contains `king`.
```php
$lotr->setFilters([
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
        ]);
$response = $lotr->getCharacters();
```

##### Filtering results (Example 2)
###### The movies results filtered by those whose revenue was more than 1 billion and which won at least 1 academy award.
```php
$lotr->setFilters([
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
        ]);
$response = $lotr->getMovies();
```

### Building the filtering argument
The `setFilters` function accepts a two-dimensional array. Each one of which must contain a value for `key` and `filter_type`. 

All filter types other than `exists` and `not_exist`, must also have a value for `value`.

The only accepted values for `filter_type` are `match`, `not_match`, `include`, `exclude`, `exists`, `not_exist`, `regex_match`, `regex_not_match`, `>`, `<`, `>=` and `<=`. 

If the `filter_type` is one of `>`, `<`, `>=` or `<=`, then the value of `value` must be numeric. For all other `filter_type` values, the value for `value` must be a non-empty string.

## Disclaimers
This SDK is NOT a complete implementation. A greater emphasis has being put into the `/movie` and `/quote` services. 

## Testing
To run the unit tests for the LOTR SDK, first make sure you have PHPUnit installed and have added your Access token in the test file (`tests/Test.php:8`). Then, run the following command from the root directory of the project:

```bash
vendor/bin/phpunit
```
This will run all the tests in the `tests` directory.
###### Running all the tests in the project assumes you have cloned the repository.

## Contributing
If you find any bugs or have any feature requests, please open an issue on the GitHub repository. Pull requests are also welcome!

## License
This SDK is licensed under the Apache-2.0 license. See the LICENSE file for more information.
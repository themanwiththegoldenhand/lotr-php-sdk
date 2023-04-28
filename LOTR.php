<?php

namespace LOTR;

class LOTR
{
    private $apiKey;
    private $apiBaseUrl = 'https://the-one-api.dev/v2';
    private $headers = [];

    // 429 Too many request retry variables
    private $tooManyRequestsMaxRetry = 10;
    private $tooManyRequestsRetryDelay = 10;

    // Non-429 failure retry variables
    private $maxRetries = 3;
    private $retryDelay = 5;

    // Pagination variables
    private $limit = null;
    private $page = null;
    private $offset = null;

    // Sorting variables
    private $sort = null;
    private $sortDirection = null;

    // Filtering variables
    private $filters = [];
    private $filter_types = ['match', 'not_match', 'include', 'exclude', 'exists', 'not_exist', 'regex_match', 'regex_not_match', '>', '<', '>=', '<='];

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
    }

    /**
     * Makes a request to the One API.
     *
     * @param string $endpoint The API endpoint to request.
     * @param int $retry The number of times the function has been retried after encountering a non-429 error.
     *
     * @return array The API response as an array.
     *
     * @throws Exception When the API returns a 4xx or 5xx HTTP status code.
     */
    private function makeRequest($endpoint, $retry = 0)
    {
        $path = $this->apiBaseUrl . $endpoint;
        $params = [];

        // Handle the pagination parameters
        if (!empty($this->limit)) {
            $params['limit'] = $this->limit;
        }
        if (!empty($this->page)) {
            $params['page'] = $this->page;
        }
        if (!empty($this->offset)) {
            $params['offset'] = $this->offset;
        }

        // Handle the sorting parameters
        if (!empty($this->sort) && !empty($this->sortDirection)) {
            $params['sort'] = $this->sort . ':' . $this->sortDirection;
        }

        // Handle the filtering parameters
        $filter_parameters = $this->buildFilterParameters();

        if (!empty($params)) {
            $path .= '?' . http_build_query($params) . (empty($filter_parameters) ? '' : '&' . $filter_parameters);
        } elseif (!empty($filter_parameters)) {
            $path .= '?' . $filter_parameters;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode >= 500) {
            throw new \Exception('API error: ' . $httpCode . ' ' . $response);
        }

        if ($httpCode >= 400) {
            if ($httpCode === 429 && $retry < $this->tooManyRequestsMaxRetry) {
                sleep($this->tooManyRequestsRetryDelay);
                return $this->makeRequest($endpoint, $retry + 1);
            } else if ($retry < $this->maxRetries) {
                sleep($this->retryDelay);
                return $this->makeRequest($endpoint, $retry + 1);
            }
            throw new \Exception('API error: ' . $httpCode . ' ' . $response);
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * @param string $str
     * @return false|int
     */
    private function isHexadecimal($str)
    {
        if (empty($str)) {
            return false;
        }
        return preg_match('/^[0-9a-fA-F]+$/i', $str);
    }

    /**
     * @return string
     */
    private function buildFilterParameters()
    {
        if (empty($this->filters)) {
            return '';
        }

        $filter_parameters = [];

        foreach ($this->filters as $filter) {
            if (
                isset($filter['key']) &&
                !empty($filter['key']) &&
                isset($filter['filter_type']) &&
                !empty($filter['filter_type']) &&
                in_array($filter['filter_type'], $this->filter_types)
            ) {
                if (in_array($filter['filter_type'], ['exists', 'not_exists'])) {
                    switch ($filter['filter_type']) {
                        case 'not_exists':
                            array_push($filter_parameters, '!' . $filter['key']);
                            break;
                        default:
                            array_push($filter_parameters, $filter['key']);
                            break;
                    }

                } elseif (isset($filter['value']) && (!empty($filter['value']) || $filter['value'] == 0)) {
                    if (in_array($filter['filter_type'], ['>', '<', '>=', '<=']) && is_numeric($filter['value'])) {
                        array_push($filter_parameters, $filter['key'] . $filter['filter_type'] . $filter['value']);
                    } else {
                        switch ($filter['filter_type']) {
                            case 'match':
                            case 'include':
                                array_push($filter_parameters, $filter['key'] . '=' . $filter['value']);
                                break;
                            case 'not_match':
                            case 'exclude':
                                array_push($filter_parameters, $filter['key'] . '!=' . $filter['value']);
                                break;
                            case 'regex_match':
                                array_push($filter_parameters, $filter['key'] . '=/' . $filter['value'] . '/i');
                                break;
                            case 'regex_not_match':
                                array_push($filter_parameters, $filter['key'] . '!=/' . $filter['value'] . '/i');
                                break;
                        }
                    }
                }
            }
        }

        if (!empty($filter_parameters)) {
            return implode('&', $filter_parameters);
        }
        return '';
    }

    /**
     * @param integer $limit
     * @return void
     */
    public function setLimit($limit)
    {
        if (!isset($limit)) {
            return;
        }

        // must be a positive integer
        if (is_int($limit) && $limit >= 1) {
            $this->limit = $limit;
        }
    }

    /**
     * @param integer $page
     * @return void
     */
    public function setPage($page)
    {
        if (!isset($page)) {
            return;
        }

        // must be a positive integer
        if (is_int($page) && $page >= 1) {
            $this->page = $page;
        }
    }

    /**
     * @param integer $offset
     * @return void
     */
    public function setOffset($offset)
    {
        if (!isset($offset)) {
            return;
        }

        // must be a non-negative integer
        if (is_int($offset) && $offset >= 0) {
            $this->offset = $offset;
        }
    }

    /**
     * @param string $key
     * @param string $direction
     * The sort direction. Accepts 'asc' or 'desc'.
     *
     * @return void
     */
    public function setSort($key, $direction)
    {
        if (!isset($key) || !isset($direction)) {
            return;
        }

        // The optional sort parameter must be a string and sortDirection must be `asc` or `desc`
        // NOTE | Setting a minimum string length of 1, whereas ideally we would be checking that the key is in the model
        if (is_string($key) && strlen($key) >= 1 && in_array($direction, ['asc', 'desc'])) {
            $this->sort = $key;
            $this->sortDirection = $direction;
        }
    }

    /**
     * @param array $filters
     * Should be a two-dimensional array containing one or more filters
     *
     * @return void
     */
    public function setFilters($filters)
    {
        if (!isset($filters)) {
            return;
        }

        $this->filters = $filters;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getBooks()
    {
        return $this->makeRequest('/book');
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function getBook($id)
    {
        if (!$this->isHexadecimal($id)) {
            throw new \Exception('Invalid id');
        }
        return $this->makeRequest('/book/' . $id);
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function getBookChapters($id)
    {
        if (!$this->isHexadecimal($id)) {
            throw new \Exception('Invalid id');
        }
        return $this->makeRequest('/book/' . $id . 'chapter');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getMovies()
    {
        return $this->makeRequest('/movie');
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function getMovie($id)
    {
        if (!$this->isHexadecimal($id)) {
            throw new \Exception('Invalid id');
        }
        return $this->makeRequest('/movie/' . $id);
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function getMovieQuotes($id)
    {
        if (!$this->isHexadecimal($id)) {
            throw new \Exception('Invalid id');
        }
        return $this->makeRequest('/movie/' . $id . '/quote');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCharacters()
    {
        return $this->makeRequest('/character');
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function getCharacter($id)
    {
        if (!$this->isHexadecimal($id)) {
            throw new \Exception('Invalid id');
        }
        return $this->makeRequest('/character/' . $id);
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function getCharacterQuotes($id)
    {
        if (!$this->isHexadecimal($id)) {
            throw new \Exception('Invalid id');
        }
        return $this->makeRequest('/character/' . $id . '/quote');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getQuotes()
    {
        return $this->makeRequest('/quote');
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function getQuote($id)
    {
        if (!$this->isHexadecimal($id)) {
            throw new \Exception('Invalid id');
        }
        return $this->makeRequest('/quote/' . $id);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getChapters()
    {
        return $this->makeRequest('/chapter');
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function getChapter($id)
    {
        if (!$this->isHexadecimal($id)) {
            throw new \Exception('Invalid id');
        }
        return $this->makeRequest('/chapter/' . $id);
    }
}

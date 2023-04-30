# LOTR SDK Design
## Overview

The LOTR SDK provides a simple interface to access the Rings API available at https://the-one-api.dev/v2. The SDK provides methods to retrieve various types of data, including books, characters, movies, quotes and chapters.

## API Authentication
Authentication is required to use the LOTR API. Users need to provide their API key to authenticate their requests. The API key is provided when you sign up at https://the-one-api.dev/sign-up.

## SDK Installation and Usage
Please view the instructions in the README file 

## Error Handling
The SDK throws an exception if the API returns a 5xx HTTP status code.

Requests resulting in a [429 HTTP status code](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429) responses, will be reattempted up to 10 times, with a 10 seconds delay between requests. All other 4xx HTTP status code responses will be reattempted up to 3 times with a 5 seconds delay between requests. Once the retries have been unsuccessfully exhausted the SDK throws an exception.

In a more sophisticated implementation of the SDK, we could track the `X-RateLimit-Limit`, `X-RateLimit-Remaining` and `Retry-After` headers in the response (when available) to tracks our usage and time our delays better.
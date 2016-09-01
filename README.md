# MarketScan mScan API client in PHP

This is a PHP client for the [MarketScan mScan API](http://www.marketscan.com/mScanAPIDocumentation/html/Welcome.htm).

This library is written, maintained, and used by MarketScan customers. If you have problems, suggestions, or (best!) improvements to this client library, please open an issue or pull request.  If you're having credential or other MarketScan problems, please contact their excellent API support team at `mscanapi@marketscan.com`

The original implementation is designed to take inventory and prices from an existing smart system, and quickly get payment information from the `RunScan` API call. As such, the entire MarketScan API has not yet been ported.

## Getting Started

You can install this library through Composer:

```bash
composer require marketscan/mscan
```

In your code, instantiate the API client by passing your Partner ID and Account number to the constructor.  Optionally you can pass in the root URL as the third argument.

```php
$mscan = new \MarketScan\MScan(
  'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
  '000000',
  'http://integration.marketscan.com/mscanservice/rest/mscanservice.rst/?'
);
```

Wherever possible, I'd recommend you use the methods that take direct arguments:

```php
$mscan->GetVehiclesByVIN('1G1YB2D73G5121725');
```

The entire API has not yet been mapped in this way (pull requests are welcome). If you need an API method that is not yet implemented directly, the general form for API calls is:

```php
$mscan->api_request(
  'api method name',
  'GET or POST',
  'string to append to the URL',
  'data to JSON encode and POST in the request body'
);
```

## Demo Pages

The library ships with a demo page that takes a VIN and uses mPencil to get a matrix of Lease and Retail Loan payments.

  1. Clone this repository
  1. In the repo folder, run `composer install` to load dependencies and set up the autoloader.
  1. `cp demo/credentials-sample.php demo/credentials.php` then update credentials.php with actual MarketScan credentials from your account team.  (This new file will be ignored by git if you commit changes for a pull request.)
  1. Start the PHP local server: `php -S localhost:4242 -t demo`
  1. Open your browser to [http://localhost:4242](http://localhost:4242)

# MarketScan mScan API client in PHP

This is a PHP client for the [MarketScan mScan API](http://www.marketscan.com/mScanAPIDocumentation/html/Welcome.htm).

This library is written, maintained, and used by MarketScan customers. For help with this client library, please open an issue or pull request.  If you're having credential or other MarketScan problems, please contact their excellent API support team at `mscanapi@marketscan.com`

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
$mscan->GetMarketByZIP(68123);
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

The library includes demo code to take a VIN and return a matrix of Lease and Retail Loan payments. To run the demo:

  1. Clone this repository
  1. In the repo folder, run `composer install` to load dependencies and set up the autoloader.
  1. `cp demo/credentials-sample.php demo/credentials.php` then update credentials.php with actual MarketScan credentials from your account team.  (This new file will be ignored by git if you commit changes for a pull request.)
  1. Start the PHP local server: `php -S localhost:4242 -t demo`
  1. Open your browser to [http://localhost:4242](http://localhost:4242)

## Getting to RunScan

There is a lot of good stuff in the MarketScan API, but here's how to get to `RunScan` results (the very best stuff) ASAP.

To set up your dealership:

  1. Run `GetMarketByZIP` for your dealership's ZIP and cache the Market ID
  1. Download and cache `GetManufacturers`

To `RunScan` for a vehicle (assuming you're starting with its VIN):

  1. Get the MarketScan vehicle ID by calling `GetVehiclesByVINParams` with the VIN as the only string argument: `$mscan->GetVehiclesByVINParams($vin)`
  1. In those results, you'll get a Make ID (e.g., Jaguar = 13415). Look up that Make ID in your cached results of `GetManufacturers` to figure out whose ZIP is used to localize manufacturer rebates. (e.g., Jaguar uses customer's ZIP, Lexus uses dealer's)
  1. Build your [request object to pass to RunScan](http://www.marketscan.com/mScanAPIDocumentation/html/ed481d63-01f7-38fc-e444-c14233114f11.htm).
    1. Set `Vehicle.ID` to the value you received from `GetVehiclesByVINParams`
    1. If the vehicle manufacturer's `RebateZIPPreference` is 0, set `AutoRebateParams.ZIP` to the customer's ZIP
    1. If the vehicle manufacturer's `RebateZIPPreference` is 1, set `AutoRebateParams.ZIP` to the dealer's ZIP
    1. Set `Market` to the dealership's market ID you received from `GetMarketByZIP`

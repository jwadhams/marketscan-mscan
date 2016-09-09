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

## Commands

### Looking up vehicles

The most powerful way to look up vehicles is using `GetVehiclesByVINParams`.

You can look up a specific vehicle by VIN by passing that as the only parameter:

```php
$mscan->GetVehiclesByVINParams('1G1YB2D73G5121725');
```

You can also pass a more detailed query object that uses the MarketScan VIN exploder to search your inventory. [See documentation for details.](http://www.marketscan.com/mScanAPIDocumentation/html/15d771de-6906-4951-9ccf-9d9a97c48269.htm)

```php
$mscan->GetVehiclesByVINParams([
  'Vin' => '1G1YB2D73G5',
  'IsNew' => true
]);
```

You can get a complete list of Makes and Models. Each call takes one parameter, if true it returns Makes or Models that are currently New, if false it returns Makes and Models that may be available used (e.g., DMC, Plymouth).

```php
$mscan->GetMakes(true);
$mscan->GetModels(true);
```


### Get Manufacturer Information

You can get a complete list of supported manufacturers, and their rebate ZIP policy. This information rarely changes and can be cached.

```php
$mscan->GetManufacturers();
```

This result is useful in building the `AutoRebateParams.ZIP` in a `RunScan` request.

You can also get the manufacturer of a specific vehicle (by MarketScan Vehicle ID):

```php
$mscan->GetManufacturer($vehicle_id);
```

### Information About Your Dealership

You can look up the code MarketScan uses to describe your region by looking up the ZIP code of your dealership:

```php
$mscan->GetMarketByZIP(68123);
```

### Etc.

The entire API has not yet been mapped to PHP methods yet. (Pull requests are welcome!) If you need an API method that is not yet implemented directly, the general form for API calls is:

```php
$mscan->api_request(
  'api method name',
  'GET or POST',
  'string to append to the URL',
  'data to JSON encode and POST in the request body'
);
```

Some of these API actions, especially `RunScan`, offer incredibly fine-grained control. If you need help figuring out what options can be passed, check out [MarketScan's API documentation](http://www.marketscan.com/mScanAPIDocumentation/html/Welcome.htm).

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

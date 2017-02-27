<?php

namespace MarketScan;


/*

http://www.marketscan.com/mScanAPIDocumentation/html/b40172da-018e-4a80-a14f-37918e8748e3.htm
*/

class MScan{

  const SCANMODE_LEASE = 0;
  const SCANMODE_RETAIL = 1;
  const SCANMODE_LOAN = 1; //Synonym
  const SCANMODE_MPENCIL = 2;

  const SCANTYPE_PROFIT = 0;
  const SCANTYPE_SELLINGPRICE = 1;
  const SCANTYPE_PAYMENT = 2;
  const SCANTYPE_MAXI = 3; //Used to calculate the scan by the maximum profit allowed. The scan will collect maximum upfront profit that can be made - difference between Selling Price and Dealer Cost. The scan will collect the maximum paid reserve that is allowed by the lender and deal structure. This scan will maximize all other values that can be marked up for profit.


  //Filter RunScan results to, for example, only include specials
  const SPECIALSTANDARD_BOTH = 0;
  const SPECIALSTANDARD_SPECIAL_ONLY = 1;
  const SPECIALSTANDARD_STANDARD_ONLY = 2;

  // Returned as part of GetManufacturer dictates whether RunScan should pass the Customer's ZIP or the Dealer's ZIP in AutoRebateParams.ZIP
  const MANUFACTURER_REBATE_ZIP_DEALER = 1;
  const MANUFACTURER_REBATE_ZIP_CUSTOMER = 0;

  private $partner_id;
  private $account;
  private $base_url = 'http://integration.marketscan.com/mscanservice/rest/mscanservice.rst/?';

  public function __construct($partner_id, $account, $base_url = null){
    $this->partner_id = $partner_id;
    $this->account = $account;
    if($base_url){
      $this->base_url = $base_url;
    }
  }

  public function api_request($command, $method = "GET", $append_to_url = "", $data = null){
    $url = $this->base_url . $command . '/' . $this->partner_id . '/' . $this->account;

    if($append_to_url){
      if($append_to_url{0} != '/'){ $url .= '/'; } //Insert a / between account and this, but don't double up
      $url .= $append_to_url;
    }

    if($data !== null){
      $data = json_encode($data);
    }

    return new \GuzzleHttp\Psr7\Request($method, $url, [], $data);
  }

  public function call_api($command, $method = "GET", $append_to_url = "", $data = null){
    $request = $this->api_request($command, $method, $append_to_url, $data);

    $client = new \GuzzleHttp\Client();
    $response = $client->send($request);
    return json_decode( $response->getBody(), true );
  }

  /*
    Note, when looking up a specific VIN from inventory, you're better off using GetVehiclesByVINParams and passing the VIN string as the only arg
  */
  public function GetVehiclesByVIN($vin, $is_new = true){
    return $this->call_api(
      'GetVehiclesByVIN',
      'GET',
      $vin . '/' . $this->bool_to_url_component($is_new)
    );
  }


  /*
    Can be called either with just a VIN string:
    $mscan->GetVehiclesByVINParams('1G1YA2D73H5104346');

    Or with any of the supported parameters in an associative array:
    $mscan->GetVehiclesByVINParams['VIN'=>'1G1YA2D73H5', 'Model'=>"Corvette", 'Year'=>2016]);
  */
  public function GetVehiclesByVINParams($arg){
    if(is_array($arg)){
      $params = $arg;
    }else{
      $params = ['VIN' => $arg];
    }
    return $this->call_api(
      'GetVehiclesByVINParams',
      'POST',
      '',
      $params
    );
  }

  public function GetLenders(){
    return $this->call_api(
      'GetLenders',
      'GET'
    );
  }

  /*
    Given a vehicle ID, get the Manufacturer name and rebates ZIP policy
  */
  public function GetManufacturer($vehicle_id){
    return $this->call_api(
      'GetManufacturer',
      'GET',
      $vehicle_id
    );
  }

  /*
    Get a complete list of manufacturers, what make IDs belong to each manufacturer, and what that manufacturer's rebate ZIP policy is.

    It might be more useful to transform this result into a simpler map like:
    [
      'make ID' => 'ZIP policy',
      'make ID' => 'ZIP policy',
    ]
  */
  public function GetManufacturers(){
    return $this->call_api(
      'GetManufacturers',
      'GET'
    );
  }


  public function GetMarketByZIP($zip){
    return $this->call_api(
      'GetMarketByZIP',
      'GET',
      $zip
    );
  }

  public function GetMakes($is_new = true){
    return $this->call_api(
      'GetMakes',
      'GET',
      $this->bool_to_url_component($is_new)
    );
  }

  //Not clear why this doesn't take a filter by make, I guess you can get the global list (maybe rarely, once a day?) and filter it yourself?
  public function GetModels($is_new = true){
    return $this->call_api(
      'GetModels',
      'GET',
      $this->bool_to_url_component($is_new)
    );
  }

  public function RunScan($scan_request){
    return $this->call_api(
      'RunScan',
      'POST',
      '',
      $scan_request
    );

//In the response, AmountFinanced = Price + AcquisitionFee + InceptionFeesTaxes - TotalRebate - (customer cash which isn't part of the response?)

  }

  //So you want to run a few at once
  public function RunScan_request($scan_data){
    return $this->api_request("RunScan", "POST", '', $scan_data);
  }


  /*
    Parameter-based search of rebates for a vehicle in a ZIP code
    Region ID is optional. Always? Just if NULL from GetVehicleRebateRegions ?
  */
  public function GetRebatesParams($vehicle_id, $zip, $region_id = null, $expired = false){
    $body = [
      'DateTimeStamp' => date(\DateTime::ISO8601), //'2016-09-13T03:07:46.069Z'
      'VehicleID' => $vehicle_id,
      'ZIP' => $zip,
      'IncludeExpired' => $expired,
    ];
    if($region_id){
      $body['RegionID'] = $region_id;
    }


    return $this->call_api(
      'GetRebatesParams',
      'POST',
      '',
      $body
    );
  }

  //Can return region numbers based on vehicle and ZIP. If a ZIP straddles two regions, you can disambiguate by city in Name in the response
  //Can also return null -- possibly Manufacturer-based?
  public function GetVehicleRebateRegions($vehicle_id, $zip){
    return $this->call_api(
      'GetVehicleRebateRegions',
      'GET',
      $vehicle_id . '/' . $zip
    );
  }


  public static function bool_to_url_component($bool){
    return ($bool) ? "true" : "false";
  }

  public static function url_component_to_bool($string){
    return (strtolower(trim($string)) === "true");
  }


}

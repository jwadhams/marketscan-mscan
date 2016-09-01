<?php

namespace MarketScan;


/*

http://www.marketscan.com/mScanAPIDocumentation/html/b40172da-018e-4a80-a14f-37918e8748e3.htm
*/

class MScan{

  const SCANMODE_LEASE = 0;
  const SCANMODE_LOAN = 1;
  const SCANMODE_MPENCIL = 2;

  const SCANTYPE_PROFIT = 0;
  const SCANTYPE_SELLINGPRICE = 1;
  const SCANTYPE_PAYMENT = 2;
  const SCANTYPE_MAXI = 3; //Used to calculate the scan by the maximum profit allowed. The scan will collect maximum upfront profit that can be made - difference between Selling Price and Dealer Cost. The scan will collect the maximum paid reserve that is allowed by the lender and deal structure. This scan will maximize all other values that can be marked up for profit.

  private $partner_id;
  private $account;
  private $base_url = 'http://integration.marketscan.com/mscanservice/rest/mscanservice.rst/?';

  public function __construct($partner_id, $account, $base_url = null){
    $this->partner_id = $partner_id;
    $this->account = $account;
    if($base_url){
      $this->$base_url = $base_url;
    }
  }

  public static function hello(){
    return "Hello World";
  }

  public function api_request($command, $method = "GET", $append_to_url = "", $data = null){
    $client = new \GuzzleHttp\Client();
    $url = $this->base_url . $command . '/' . $this->partner_id . '/' . $this->account;

    if($append_to_url){
      if($append_to_url{0} != '/'){ $url .= '/'; } //Insert a / between account and this, but don't double up
      $url .= $append_to_url;
    }

    $options = [];
    if($data !== null){
      $options['json'] = $data;
    }


    $res = $client->request($method, $url, $options);
    return json_decode( $res->getBody(), true );
  }

  /*
    Testing with a 2011 Elantra, results are the same whether is_new is true or false

  */
  public function GetVehiclesByVIN($vin, $is_new = true){
    return $this->api_request(
      'GetVehiclesByVIN',
      'GET',
      $vin . '/' . $this->bool_to_url_component($is_new)
    );
  }

  public function GetMarketByZIP($zip){
    return $this->api_request(
      'GetMarketByZIP',
      'GET',
      $zip
    );
  }

  public function GetMakes($is_new = true){
    return $this->api_request(
      'GetMakes',
      'GET',
      $this->bool_to_url_component($is_new)
    );
  }

  //Not clear why this doesn't take a filter by make, I guess you can get the global list (maybe rarely, once a day?) and filter it yourself?
  public function GetModels($is_new = true){
    return $this->api_request(
      'GetModels',
      'GET',
      $this->bool_to_url_component($is_new)
    );
  }

  public function RunScan($scan_request){
    return $this->api_request(
      'RunScan',
      'POST',
      '',
      $scan_request
    );

//In the response, AmountFinanced = Price + AcquisitionFee + InceptionFeesTaxes - TotalRebate - (customer cash which isn't part of the response?)

  }




  public static function bool_to_url_component($bool){
    return ($bool) ? "true" : "false";
  }

  public static function url_component_to_bool($string){
    return (strtolower(trim($string)) === "true");
  }


}

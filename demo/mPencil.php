<?php

header('Content-type:application/json;charset=utf-8');

require '../vendor/autoload.php';

use MarketScan\MScan;

//Load credentials, then intialize an MScan API instance
require 'credentials.php';
$mscan = new MScan($marketscan_account, $marketscan_partner_id);

$scan_request = [
  "AutoRebateParams" => [
    "ZIP" => 93012
  ],
  "CreditScore" => 890,
  //DesiredValue is desired profit for SCANTYPE_PROFIT
  //DesiredValue is selling price for SCANTYPE_SELLINGPRICE
  "DesiredValue" => 23070,

  //http://www.marketscan.com/mScanAPIDocumentation/html/01462dcd-cdf5-afeb-1b31-cfb57b3514b1.htm
  "mPencil" => [

    "LeasePart" => [
      "Cash" => [0, 1000, 2000, 3000], //not more than four
      "Term" => [24, 36, 48, 60],
    ],
    "RetailPart" => [
      "Cash" => [0, 1000, 2000, 3000],
      "Term" => [24, 36, 48, 60, 72],

    ],

  ],
  "LeasePart" => [
    //In an mPencil call CustomerCash is ignored but AnnualMileage is honored
    "AnnualMileage" => 15000,
  ],

  "RetailPart" => [],

  "Market" => 51,
  "ScanMode" => MScan::SCANMODE_MPENCIL,
  "ScanType" => MScan::SCANTYPE_SELLINGPRICE,
  "StateFeeTax" => [
    "SalesUseTaxPct" => 0,
  ],
] ;


$request_data = json_decode(file_get_contents('php://input'), true);

if(isset($request_data['Vehicle'])){
  $scan_request['Vehicle'] = $request_data['Vehicle'];

}else{
  http_response_code(400);
  exit( json_encode(['message' => 'Vehicle is required']) );
}

if(isset($request_data['Price'])){
  $scan_request['DesiredValue'] = $request_data['Price'];
}else{
  http_response_code(400);
  exit( json_encode(['message' => 'Price is required']) );
}



if(isset($request_data['Lease']['Cash'])){
  $scan_request['mPencil']['LeasePart']['Cash'] = $request_data['Lease']['Cash'];
}
if(isset($request_data['Lease']['Term'])){
  $scan_request['mPencil']['LeasePart']['Term'] = $request_data['Lease']['Term'];
}

if(isset($request_data['Retail']['Cash'])){
  $scan_request['mPencil']['RetailPart']['Cash'] = $request_data['Retail']['Cash'];
}
if(isset($request_data['Retail']['Term'])){
  $scan_request['mPencil']['RetailPart']['Term'] = $request_data['Retail']['Term'];
}


echo json_encode($mscan->RunScan($scan_request), JSON_PRETTY_PRINT);

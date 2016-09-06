<?php

header('Content-type:application/json;charset=utf-8');

require '../vendor/autoload.php';

use MarketScan\MScan;

//Load credentials, then intialize an MScan API instance
require 'credentials.php';
$mscan = new MScan($marketscan_partner_id, $marketscan_account );

if(!isset($_REQUEST['vin'])){
  http_response_code(400);
  exit( json_encode(['message' => 'vin is required']) );
}else{
  $vin = $_REQUEST['vin'];
}

echo json_encode($mscan->GetVehiclesByVINParams($vin), JSON_PRETTY_PRINT);

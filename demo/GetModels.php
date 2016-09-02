<?php

header('Content-type:application/json;charset=utf-8');

require '../vendor/autoload.php';

use MarketScan\MScan;

//Load credentials, then intialize an MScan API instance
require 'credentials.php';
$mscan = new MScan($marketscan_partner_id, $marketscan_account );

if(isset($_REQUEST['new'])){
  $new = $mscan->url_component_to_bool($_REQUEST['new']);
}else{
  $new = true;
}

//Changes slowly, could cache
echo json_encode($mscan->GetModels($new), JSON_PRETTY_PRINT);

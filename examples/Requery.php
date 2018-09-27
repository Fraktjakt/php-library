<?php

  require_once '../src/Client.php';

  try {

    $fraktjakt = new Fraktjakt\Client();

    $fraktjakt->setConsignorId(123456)
              ->setConsignorKey('0123456789abcdef0123456789abcdef01234567')
              ->setTestMode(true);

    $request = array(
      'consignor' => array(
        'currency' => 'SEK',
        'language' => 'sv',
        'encoding' => 'UTF-8',
      ),
      'value' => 199.50,
      'shipper_info' => 1,
      'shipment_id' => 1494539,
    );

    $result = $fraktjakt->Requery($request);

    var_dump($result);

  } catch(Exception $e) {
    die('An error occured: '. $e->getMessage() . PHP_EOL . PHP_EOL
      . $fraktjakt->getLastLog());
  }

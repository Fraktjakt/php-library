<?php

  require_once __DIR__.'/../src/Client.php';

  try {

    $fraktjakt = new \Fraktjakt\Client();

    $fraktjakt->setConsignorId(123456)
              ->setConsignorKey('0123456789abcdef0123456789abcdef01234567')
              ->setTestMode(true);

    $request = [
      'consignor' => [
        'currency' => 'SEK',
        'language' => 'sv',
        'encoding' => 'UTF-8',
      ],
      'value' => 199.50,
      'shipper_info' => 1,
      'shipment_id' => 1496973,
    ];

    $result = $fraktjakt->Requery($request);

    echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

  } catch(Exception $e) {
    die('An error occured: '. $e->getMessage() . PHP_EOL . PHP_EOL
      . $fraktjakt->getLastLog());
  }

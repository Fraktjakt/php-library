<?php

  require_once __DIR__.'/../src/Client.php';

  try {

    $fraktjakt = new \Fraktjakt\Client();

    $fraktjakt->setConsignorId(123456)
              ->setConsignorKey('0123456789abcdef0123456789abcdef01234567')
              ->setTestMode(true);

    $request = [
      'shipment_id' => 1497951,
    ];

    $result = $fraktjakt->Trace($request);

    echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

  } catch(Exception $e) {
    die('An error occured: '. $e->getMessage() . PHP_EOL . PHP_EOL
      . $fraktjakt->getLastLog());
  }

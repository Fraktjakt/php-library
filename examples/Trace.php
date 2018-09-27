<?php

  require_once '../src/Client.php';

  try {

    $fraktjakt = new Fraktjakt\Client();

    $fraktjakt->setConsignorId(123456)
              ->setConsignorKey('0123456789abcdef0123456789abcdef01234567')
              ->setTestMode(true);

    $request = array(
      'shipment_id' => 1492159,
    );

    $result = $fraktjakt->Trace($request);

    var_dump($result);

  } catch(Exception $e) {
    die('An error occured: '. $e->getMessage() . PHP_EOL . PHP_EOL
      . $fraktjakt->getLastLog());
  }

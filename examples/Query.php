<?php

  require_once __DIR__.'/../src/Client.php';

  try {

    $fraktjakt = new \Fraktjakt\Client();

    $fraktjakt->setConsignorId(123456)
              ->setConsignorKey('0123456789abcdef0123456789abcdef01234567')
              ->setTestMode(true);

    $request = [
      'value' => 199.50,
      'shipper_info' => 1,
      'consignor' => [
        'currency' => 'SEK',
        'language' => 'sv',
        'encoding' => 'utf-8',
      ],
      'reference' => uniqid(),
      'address_to' => [
        'street_address_1' => 'Longway Street 1',
        'street_address_2' => '',
        'postal_code' => '12345',
        'city_name' => 'Noplace',
        'residential' => false,
        'country_code' => 'SE',
      ],
      'recipient' => [
        'company_to' => 'ACME Corp.',
        'name_to' => 'John Doe',
        'telephone_to' => '+46123456789',
        'email_to' => 'test@tim-international.net',
      ],
      'parcels' => [
        [
          'weight' => 1,
          'length' => 30,
          'width' => 20,
          'height' => 10,
        ],
      ],
      'shipper_info' => 1,
    ];

    $result = $fraktjakt->Query($request);

    echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

  } catch(Exception $e) {
    die('An error occured: '. $e->getMessage() . PHP_EOL . PHP_EOL
      . $fraktjakt->getLastLog());
  }

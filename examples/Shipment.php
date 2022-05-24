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
      'commodities' => [
        [
          'name' => 'Test Item',
          'quantity' => 1,
          'taric' => '',
          'quantity_units' => 'EA',
          'description' => 'This is an item description.',
          'country_of_manufacture' => '',
          'weight' => 1,
          'unit_price' => 199.50,
        ],
      ],
    ];

    $result = $fraktjakt->Shipment($request);

    var_dump($result);

  } catch(Exception $e) {
    die('An error occured: '. $e->getMessage() . PHP_EOL . PHP_EOL
      . $fraktjakt->getLastLog());
  }

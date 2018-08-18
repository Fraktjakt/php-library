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
        'encoding' => 'utf-8',
      ),
      'reference' => uniqid(),
      'address_to' => array(
        'street_address_1' => 'Longway Street 1',
        'street_address_2' => '',
        'postal_code' => '12345',
        'city_name' => 'Noplace',
        'residential' => false,
        'country_code' => 'SE',
        'country_subdivision_code' => '',
      ),
      'recipient' => array(
        'company_to' => 'ACME Corp.',
        'name_to' => 'John Doe',
        'telephone_to' => '+46123456789',
        'email_to' => 'test@tim-international.net',
      ),
      'commodities' => array(
        array(
          'name' => 'Test Item',
          'quantity' => 1,
          'taric' => '',
          'quantity_units' => 'EA',
          'description' => 'This is an item description.',
          'country_of_manufacture' => '',
          'weight' => 1,
          'unit_price' => 199.50,
        ),
      ),
    );

    $result = $fraktjakt->Shipment($request);

  } catch(Exception $e) {
    die('An error occured: '. $e->getMessage() . PHP_EOL . PHP_EOL
      . $fraktjakt->getLastLog());
  }

  var_dump($result);

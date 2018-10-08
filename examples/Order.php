<?php

  require_once '../src/Client.php';

  try {

    $fraktjakt = new \Fraktjakt\Client();

    $fraktjakt->setConsignorId(123456)
              ->setConsignorKey('0123456789abcdef0123456789abcdef01234567')
              ->setTestMode(true);

    $request = array(
      'consignor' => array(
        'currency' => 'SEK',
        'language' => 'sv',
        'encoding' => 'utf-8',
      ),
      'shipping_product_id' => 99,
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
        'email_to' => 'user@email.com',
      ),
      //'booking' => array(
      //  'pickup_date' => '',
      //  'driving_instruction' => '',
      //  'user_notes' => '',
      //),
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
      'parcels' => array(
        array(
          'weight' => 2.8,
          'length' => 25,
          'width' => 20,
          'height' => 15,
        ),
      ),
    );

    $result = $fraktjakt->Order($request);

    var_dump($result);

  } catch(Exception $e) {
    die('An error occured: '. $e->getMessage() . PHP_EOL . PHP_EOL
      . $fraktjakt->getLastLog());
  }

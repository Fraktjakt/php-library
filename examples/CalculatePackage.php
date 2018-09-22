<?php

  require_once '../src/Client.php';

  try {

    $fraktjakt = new Fraktjakt\Client();

    $items = array(
      array(
        'name' => 'Item 1',
        'quantity' => 2,
        'length' => 20,
        'width' => 15,
        'height' => 10,
        'weight' => 1,
        //'weight_unit' => 'kg', // If omitted we assume kg
        //'length_unit' => 'cm', // If omitted we assume cm
      ),
      array(
        'name' => 'Item 2',
        'quantity' => 2,
        'length' => 200,
        'width' => 150,
        'height' => 100,
        'weight' => 1000,
        'weight_unit' => 'g',
        'length_unit' => 'mm',
      ),
    );

    $package = $fraktjakt->CalculatePackage($items);

    var_dump($package);

  } catch(Exception $e) {
    die('An error occured: '. $e->getMessage());
  }

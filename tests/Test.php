<?php

class Test extends PHPUnit\Framework\TestCase {

  /**
  * Check if the class exists and has valid syntax
  */
  public function testIsThereAnySyntaxError():void {
    $this->assertTrue(class_exists('\\Fraktjakt\\Client'));
  }

  /**
  * Test CreateShipment API
  */
  public function testCreateShipment():void {

    chdir(__DIR__);
    include '../examples/Shipment.php';

    $this->assertTrue((in_array($result['status'], ['ok', 'warning'])));
    $this->assertTrue(!empty($result['access_code']));
  }

  /**
  * Test CreateShipment API
  */
  public function testOrder():void {

    echo 'Testing Order...' . PHP_EOL;

    chdir(__DIR__);
    include '../examples/Order.php';

    $this->assertTrue((in_array($result['status'], ['ok', 'warning'])));
  }

  /**
  * Test Query API
  */
  public function testQuery():void {

    echo 'Testing Query...' . PHP_EOL;

    chdir(__DIR__);
    include '../examples/Query.php';

    $this->assertTrue(($result['status'] == 'ok'));
  }

  /**
  * Test Requery API
  */
  public function testRequery():void {

    echo 'Testing ReQuery...' . PHP_EOL;

    chdir(__DIR__);
    include '../examples/Requery.php';

    $this->assertTrue(($result['status'] == 'ok'));
  }

  /**
  * Test Trace API
  */
  public function testTrace():void {

    echo 'Testing Trace...' . PHP_EOL;

    chdir(__DIR__);
    include '../examples/Trace.php';

    $this->assertTrue(($result['status'] == 'ok'));
  }
}

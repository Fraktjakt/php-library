# Fraktjakt PHP Library

This is a PHP library for machine-to-machine communication with Fraktjakt.


## Install

Manually include Client.php in your script before initiating the client.

    require_once 'path/to/Client.php';

    $fraktjakt = new Fraktjakt\Client();


## Example Code (More examples in the examples/ folder)

    require_once 'path/to/Client.php';

    try {

      $fraktjakt = new Fraktjakt\Client();

      $fraktjakt->setConsignorId(12345)
                ->setConsignorKey('0123456789abcdef0123456789abcdef')
                ->setTestMode(true);

      $request = array(
        // ...
      );

      $result = $fraktjakt->Query($request);

    } catch(Exception $e) {
      die('An error occured: '. $e->getMessage() . PHP_EOL . PHP_EOL
        . $fraktjakt->getLastLog());
    }

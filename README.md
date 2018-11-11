# Fraktjakt PHP Library

This is a PHP library for machine-to-machine communication with Fraktjakt.


## Use With Composer

(We will just assume you have composer installed for your project)

1. Open a command-line interface and navigate to your project folder.

2. Run the following command in your command-line interface:

    composer require fraktjakt/library:dev-master

Composer will now autoload Client.php when you create the class object:

```
    $fraktjakt = new \Fraktjakt\Client();
```

## Use Without Composer

Manually include Client.php in your script before initiating the client.

```
    require_once 'path/to/Client.php';

    $fraktjakt = new \Fraktjakt\Client();
```


## Example Code (More examples in the examples/ folder)

```
  require_once 'path/to/Client.php';

  try {

    $fraktjakt = new \Fraktjakt\Client();

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
```


## Testing

1. Make sure you have PHP installed on your machine.

2. Open a command-line interface and navigate to the examples/ folder.

3. Run the following command in your command-line interface:

    php ExampleFile.php

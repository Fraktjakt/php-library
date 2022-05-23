# Install Composer for Windows

1. Download and run Composer-Setup.exe from https://getcomposer.org/download/.

2. In the project folder (next to composer.json) create the file "Run Composer.cmd" with the following content:

    @C:\Windows\System32\cmd.exe /k composer --version

3. Execute "Run Composer.cmd" and in the command prompt type:

    composer install

Done! Composer should now have installed all necessary libraries.


# Test PHP Code

1. Make sure PHP is installed on your computer.

2. Open a command line interface.

3. Navigate to the examples/ folder, setting it to currrent working directory.

4. Type any of the commands below to execute a script and see the result:


```
    php CalculatePackage.php
    php Order.php
    php Query.php
    php Requery.php
    php Shipment.php
    php Trace.php
```

# Test PHP Code Using PHP Unit

1. Make sure Composer is installed and operating properly

2. In the project folder (next to composer.json) create the file "Run PHPUnit Tests.cmd" with the following content:

    @C:\Windows\System32\cmd.exe /k vendor\bin\phpunit.bat

3. Execute the file to perform PHP Unit tests.


# Social Coding

  [Github Repository](https://www.github.com/Fraktjakt/php-library)
  Branch: master


## Changelog / Commit Messages

    ! means critical
    + means added
    - means removed
    * means changed

  Examples:

    ! Fix critical issue where drinks was not coming out of the tap
    + Added lettuce to the sallad
    - Removed tomatoes as some guests are allergic
    * Replaced the smaller plate with a larger one

  Issue Tracker Fix Example:

    * Fix #1234 - Car engine doesn't start

  The commit message must always reveal what's inside the commit, no surprises or unreferenced work.

  DO NOT COMMIT test data or debug code. All commits should be ready for production.

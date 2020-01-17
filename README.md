Algolia Search Indexer for TYPO3 9LTS
============================================
This Extension depends on [Codappix/search_core](https://github.com/Codappix/search_core)

Testing the extension
============================================
Test Setup
----------
The first thing you need to do is to go to your extension directory and *"tell"* composer to set up
the testing environment for a TYPO3 version you want to test against. For that, type `composer install`

Environment Variables
---------- 
Algolia credentials are taken from environment variables which are located at:
`/Tests/Functional/.env.example` 
Update this file with your credentials and rename it to `/Tests/Functional/.env`


Unit Tests
----------
You can now execute the unit tests of your extension simply by calling phpunit with the following command::

    TYPO3_PATH_WEB=$PWD/.Build/Web \
    \
    .Build/bin/phpunit --colors -c \
      .Build/vendor/typo3/cms/typo3/sysext/core/Build/UnitTests.xml \
      Tests/Unit


Functional Tests
----------------
You can execute the functional tests of your extension by calling phpunit with the following command::

    typo3DatabaseName="yourDatabaseName" \
    typo3DatabaseUsername="yourUsername" \
    typo3DatabasePassword="yourPassword" \
    typo3DatabaseHost="localhost" \
    TYPO3_PATH_WEB=.Build/web \
    .Build/bin/phpunit -v -c Tests/Functional/FunctionalTests.xml


Please note, that you need to specify a database user, that is allowed to create and delete databases for that to work.
You can specify an arbitrary database name, it is only used to derive the test database names from.



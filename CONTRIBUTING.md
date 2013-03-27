Contributing to Tox
===================

Commits Security
----------------

Tox requests all later commits MUST have signed with the contributor's [**GPG**](http://gnupg.org) key since the version [0.1.0-beta1](../../tree/0.1.0-beta1) released.

    git commit -veS

Coding Standard
---------------

Tox has got started to use the [**PSR-2 Coding Standard**][PSR-2 Coding Standard] as defined by [PHP Framework Interoperability Group (PHP-FIG)](http://www.php-fig.org/).

RECOMMENDED to check your codes using [**PHP_CodeSniffer**][PHP_CodeSniffer]. The [PSR-2 Coding Standard][] has already been included as [`PSR2`][PSR-2 Coding Standard] by default in the most recent versions.

*For files exclusion, A modified [`PSR2`][PSR-2 Coding Standard] standard as [`etc/phpcs/ruleset.xml`](etc/phpcs/ruleset.xml) is used.*

    phpcs --standard=etc/phpcs/ruleset.xml src

Unit Testing
------------

Tox aims to be have at least **80%** Code Coverage using unit-tests under [**PHPUnit**][PHPUnit].

In this case, unit-tests are *REQUIRED* in your Pull Requests to confirm whether the source codes are stable.

[PSR-2 Coding Standard]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

    phpunit

The printable log in Testdox format and the Code Coverage report in HTML format can be found in the directory `share/doc/report`.

*For more information, see the default [PHPUnit]() configuration [`phpunit.xml.dist`](phpunit.xml.dist).*

Documentation
-------------

Tox uses [**phpDocumentor2**][phpDocumentor2] to generate the SDK documentation from PHPDocs.

    phpdoc

The generated documentation can be found in the directory `share/doc/sdk`.

*For more information, see the default [phpDocumentor2][] configuration [`phpdoc.dist.xml`](phpdoc.dist.xml).*

[PHP_CodeSniffer]: https://github.com/squizlabs/PHP_CodeSniffer
[PHPUnit]: https://github.com/sebastianbergmann/phpunit
[phpDocumentor2]: https://github.com/phpDocumentor/phpDocumentor2
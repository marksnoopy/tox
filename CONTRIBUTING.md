CONTRIBUTING TO TOX
===================

It is very expected and appriciated to contribute your intelligence on [**ISSUING**](#issuing) and [**IMPLEMENTING**](#implementing).

These valuable works make our lives easier by reporting bugs through [issues](../../issues) and sending your contributions through [pull requests](../../pulls).

<a name="issuing"></a>ISSUING
-----------------------------

Bugs reports and features requests are both welcomed. One make our work more trusty. And the other make us grown up faster and stronger.

### For Bugs Reports ###

It is advised to give us a briefing on the title, and the bug context in the main content. *See [this example](../../issues/39) for more visualized details (Thanks to [**@redrum0003**](/redrum0003) for his pretty bug report).*

### For Features Requests ###

For our semantization purpose, the imagined sample codes of the request feature are highly expected.

<a name="implementing"></a>IMPLEMENTING
---------------------------------------

### 0 Commits Security ###

For more reliability, Tox requests all later commits *MUST* have signed with the contributor's [**GPG**][GnuPG] key since the version [0.1.0-beta1](../../tree/0.1.0-beta1) released.

    git commit -veS

Your [GPG][GnuPG] key *WOULD* be signed by the official [GPG][GnuPG] key `ED417AB8` after an approved source contribution.

### I Semi-Auto Building ###

For focusing on developing, a semi-auto building toolkit was made with [**Ant**][Ant].

We hope that would make your work simpler on syntax checking, [coding standard validating](#implementing-coding-standard), [mess detecting](#implementing-messes-detection), [unit tesing](#implementing-unit-testing) and a full combined progress of each task before.

For more details, try the following command in your root working directory.

    ant -p

To customize the executable commands of utilities, you *SHOULD* create a Java properties file named `build.properties` as preferences besides the `build.xml`, and set the following properties on your need:

* `bin.composer` - `composer` defaults
* `bin.git` - `git` defaults
* `bin.php` - `php` defaults
* `bin.phpcs` - `bin/phpcs` defaults
* `bin.phpdoc` - `phpdoc` defaults
* `bin.phpmd` - `bin/phpmd` defaults
* `bin.phpunit` - `bin/phpunit` defaults

### II Dependences ###

[**Composer**][Composer] is chosen to manage above developing utilities. The installation and upgrading progresses have already integrated into the semi-auto building toolkit.

However, we *HAVE* to use `composer` as the executable command for we do not know that where it is in your own environment.

To fit that, set the `bin.composer` property in your preferences.

### <a name="implementing-coding-standard"></a>III Coding Standard ###

Tox has got started to use the [**PSR-2 Coding Standard**][PSR-2 Coding Standard] as defined by [PHP Framework Interoperability Group (PHP-FIG)](http://www.php-fig.org/).

RECOMMENDED to check your codes using [**PHP_CodeSniffer**][PHP_CodeSniffer]. The [PSR-2 Coding Standard][] has already been included as [`PSR2`][PSR-2 Coding Standard] by default in the most recent versions.

As default, [Composer][] *WOULD* install [PHP_CodeSniffer][] automatically. So you can simply use the following command to go every lines of codes over:

    ant cs

*This command WOULD checks unit-tests in the `share/test` directory with a modified standard [`etc/phpcs/share-test.xml`](etc/phpcs/share-test.xml) and source in the `src` directory with a modified standard [`etc/phpcs/src.xml`](etc/phpcs/src.xml).*

### <a name="implementing-messes-detection"></a>IV Messes Detection ###

We are about to use [**PHPMD**][PHPMD] to detect messes in codes with the ruleset [`etc/phpmd.xml`](etc/phpmd.xml). However, the ruleset is still a draft, so the following command *WOULD* do nothing:

    ant md

### <a name="implementing-unit-testing"></a>V Unit Testing ###

Tox aims to be have **100%** methods and at least **80%** lines Code Coverage using unit-tests under [**PHPUnit**][PHPUnit].

In this case, unit-tests are *REQUIRED* in your Pull Requests to confirm whether the source codes are stable.

    ant unit

The printable log in Testdox format and the Code Coverage report in HTML format can be found in the directory `build`.

The default configuration [`etc/phpunit.xml`](etc/phpunit.xml) *WOULD* make [PHPUnit][] to run in `processIsolation` mode. That makes less coupling but more elapse.

*You can copy it to the root working directory and disable the `processIsolation` mode to run tests quite faster. However, DO NOT forget to modify the paths of source codes and outputting reports.*

### VI Documentation ###

Tox uses [**phpDocumentor2**][phpDocumentor2] to generate the SDK documentation from PHPDocs.

    ant doc

The generated documentation can be found in the directory `build/sdk`.

*For more information, see the default [phpDocumentor2][] configuration [`etc/phpdoc.xml`](etc/phpdoc.xml).*

[GnuPG]: http://gnupg.org
[Ant]: http://ant.apache.org
[Composer]: http://getcomposer.org
[PSR-2 Coding Standard]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PHP_CodeSniffer]: https://github.com/squizlabs/PHP_CodeSniffer
[PHPMD]: http://phpmd.org
[PHPUnit]: https://github.com/sebastianbergmann/phpunit
[phpDocumentor2]: https://github.com/phpDocumentor/phpDocumentor2
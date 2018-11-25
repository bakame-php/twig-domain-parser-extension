Twig Domain parser extension
===========================

This package provides Twig extensions for PHP Domain parser v5.4+


System Requirements
-------

You need:

- **PHP >= 7.1.0** but the latest stable version of PHP is recommended

Installation
--------

```bash
$ composer require bakame/twig-domain-parser-extension
```

Setup
--------

~~~php

use Pdp\Cache;
use Pdp\CurlHttpClient;
use Pdp\Manager;
use Bakame\Twig\Pdp\Extension;


$manager = new Manager(new Cache(), new CurlHttpClient(), '1 DAY');
$PdpExtension = new Extension($manager->getRules(), $manager->getTLDs());

$twig->addExtension($PdpExtension);
~~~

Because the `Pdp\Cache` class implements PSR-16, you can use any PSR-16 compatible cache driver. For instance you can use the Symfony cache component instead:

```bash
$ composer require symfony/cache
```

~~~php

use Pdp\CurlHttpClient;
use Pdp\Manager;
use Bakame\Twig\Pdp\Extension;
use Symfony\Component\Cache\Simple\PDOCache;

$dbh = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'dbuser', 'dbpass');
$cache = new PDOCache($dbh, 'psl', 86400);

$manager = new Manager($cache, new CurlHttpClient(), 86400);
$PdpExtension = Extension::createFromManager($manager);

$twig->addExtension($PdpExtension);
~~~

*You can directly get an `Extension` instance from a `Pdp\Manager` object using the `createFromManager` named constructor.*

Usage
--------

### Manipulating a Domain object in Twig template

~~~twig
{% set host = 'www.食狮.公司.cn' %}
hostname: {{ resolve_domain(host) }} {#  www.食狮.公司.cn #}
subDomain : {{ resolve_domain(host).subDomain }} {#  www #}
registrableDomain : {{ resolve_domain(host).registrableDomain }} {#  食狮.公司.cn #}
publicSuffix : {{ resolve_domain(host).publicSuffix }} {#  公司.cn #}
isICANN : {{ resolve_domain(host).ICANN ? 'ok' : 'ko' }} {#  ok #}
isPrivate : {{ resolve_domain(host).private ? 'ok' : 'ko' }} {# ko #}
isKnown : {{ resolve_domain(host).known ? 'ok' : 'ko' }} {#  ok #}
ascii : {{ resolve_domain(host).toAscii }} {#  www.xn--85x722f.xn--55qx5d.cn #}
unicode : {{ resolve_domain(host).toUnicode }} {#  www.食狮.公司.cn #}
label : {{ resolve_domain(host).label(0) }} {# cn #}
publicSuffix : {{ resolve_domain('foo.github.io', constant('Pdp\\Rules::PRIVATE_DOMAINS')).publicSuffix }} {# io #}
~~~

The `resolve_domain` function returns a `Pdp\Domain` object you can use to manipulate to returns various informations about your hostname. The returned object is resolved againts the PSL resources using `Pdp\Rules::resolve` method. This means that you can optionnally decide which section the domain should be resolve too.

The `resolve_domain` parameters are:

- `$host` a scalar or a stringable object
- `$section` : a string representing one of the PSL section
	- `Rules::ICANN_DOMAINS` : to resolve the domain against the PSL ICANN section
	- `Rules::PRIVATE_DOMAINS` : to resolve the domain against the PSL private section

By default the resolution is made against the section with the longest public suffix.

### Detecting if the host contains a IANA top level domain

~~~twig
hostname: {{ host is topLevelDomain ? 'ok' : 'ko' }} {# ok #}
~~~

The `topLevelDomain` tests tells whether the submitted domain contains a known IANA top level domain

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

Testing
-------

The library has a has a :

- a [PHPUnit](https://phpunit.de) test suite
- a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/).
- a code analysis compliance test suite using [PHPStan](https://github.com/phpstan/phpstan).

To run the tests, run the following command from the project folder.

``` bash
$ composer test
```

Security
-------

If you discover any security related issues, please email nyamsprod@gmail.com instead of using the issue tracker.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [All Contributors](https://github.com/thephpleague/uri-query-parser/contributors)

License
-------

The MIT License (MIT). Please see [License File](LICENSE) for more information.
Twig Domain parser extension
===========================

This package provides Twig extensions for PHP Domain parser v5.4+


System Requirements
-------

You need:

- **PHP >= 7.0.10** but the latest stable version of PHP is recommended

Installation
--------

```bash
$ composer require bakame/twig-domain-parser-extension
```

Documentation
--------

### Functions

#### domain

The `domain` function returns a `Pdp\Domain` object you can use to manipulate and returns various informations about your hostname. The returned object is resolved againts the PSL resources using `Pdp\Rules::resolve` method.

~~~twig
    {% set host = 'www.食狮.公司.cn' %}
    hostname: {{ domain(host).content }} {#  www.食狮.公司.cn #}
    subDomain : {{ domain(host).subDomain }} {#  www #}
    registrableDomain : {{ domain(host).registrableDomain }} {#  食狮.公司.cn #}
    publicSuffix : {{ domain(host).publicSuffix }} {#  公司.cn #}
    isICANN : {{ domain(host).ICANN ? 'ok' : 'ko' }} {#  ok #}
    isPrivate : {{ domain(host).private ? 'ok' : 'ko' }} {# ko #}
    isKnown : {{ domain(host).known ? 'ok' : 'ko' }} {#  ok #}
    ascii : {{ domain(host).toAscii.content }} {#  www.xn--85x722f.xn--55qx5d.cn #}
    unicode : {{ domain(host).toUnicode.content }} {#  www.食狮.公司.cn #}
    label : {{ domain(host).label(0) }} {# cn #}
~~~

### Tests

#### icannSuffix

Tells whether the submitted domain contains a valid ICANN public suffix

~~~twig
    {% set host = 'www.食狮.公司.cn' %}
    hostname: {{ host is icannSuffix ? 'ok' : 'ko' }} {# ok #}
~~~

#### privateSuffix

Tells whether the submitted domain contains a valid Private public suffix

~~~twig
    {% set host = 'www.食狮.公司.cn' %}
    hostname: {{ host is privateSuffix ? 'ok' : 'ko' }} {# ko #}
~~~

#### knownSuffix

Tells whether the submitted domain contains a known public suffix

~~~twig
    {% set host = 'www.食狮.公司.cn' %}
    hostname: {{ host is knownSuffix ? 'ok' : 'ko' }} {# ok #}
~~~

#### topLevelDomain

Tells whether the submitted domain contains a known IANA top level domain

~~~twig
    {% set host = 'www.食狮.公司.cn' %}
    hostname: {{ host is topLevelDomain ? 'ok' : 'ko' }} {# ok #}
~~~

### Filters

If you only need to get access to domain properties use the following filters 

#### subDomain

~~~twig
    {% set host = 'www.食狮.公司.cn' %}
    publicSuffix: {{ host | subDomain }}
~~~

#### registrableDomain

~~~twig
    {% set host = 'www.食狮.公司.cn' %}
    subDomain : {{ host | registrableDomain }}
~~~

#### publicSuffix

~~~twig
    {% set host = 'www.食狮.公司.cn' %}
    publicSuffix : {{ host | publicSuffix }}
~~~

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
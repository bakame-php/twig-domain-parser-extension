<?php

/**
 * Twig PHP Domain Parser Extension.
 *
 * @author     Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @license    https://github.com/bakame-php/twig-domain-parser-extension/blob/master/LICENSE (MIT License)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace BakameTest\Pdp\Twig;

use Bakame\Pdp\Twig\DomainParserExtension;
use Pdp\Cache;
use Pdp\CurlHttpClient;
use Pdp\Manager;
use Pdp\Rules;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Bakame\Pdp\Twig\DomainParserExtension
 */
final class DomainParserExtensionTest extends TestCase
{
    private $extension;

    public function setUp()
    {
        $manager = new Manager(new Cache(), new CurlHttpClient());
        $this->extension = new DomainParserExtension($manager->getRules(), $manager->getTLDs());
    }

    /**
     * @covers ::getDomain
     *
     * @dataProvider getDomainProvider
     */
    public function testGetDomain(string $url, string $type, string $expected)
    {
        self::assertSame($expected, $this->extension->getDomain($url, $type));
    }

    public function getDomainProvider(): array
    {
        return [
            'ascii domain' => [
                'url' => 'https://www.bbc.co.uk:80',
                'type' => 'ascii',
                'expected' => 'www.bbc.co.uk',
            ],
            'unicode domain' => [
                'url' => 'https://www.食狮.公司.cn:443',
                'type' => 'unicode',
                'expected' => 'www.食狮.公司.cn',
            ],
            'unicode to ascii domain' => [
                'url' => 'https://www.食狮.公司.cn:443',
                'type' => 'ascii',
                'expected' => 'www.xn--85x722f.xn--55qx5d.cn',
            ],
            'ascii to unicode domain' => [
                'url' => 'https://www.xn--85x722f.xn--55qx5d.cn:443',
                'type' => 'unicode',
                'expected' => 'www.食狮.公司.cn',
            ],
            'invalid domain' => [
                'url' => '::',
                'type' => 'unicode',
                'expected' => '',
            ],
            'url without host' => [
                'url' => 'scheme:path',
                'type' => 'unicode',
                'expected' => '',
            ],
            'url with IP host' => [
                'url' => 'https://127.0.0.1',
                'type' => 'unicode',
                'expected' => '127.0.0.1',
            ],
            'invalid type used' => [
                'url' => 'https://www.食狮.公司.cn:443',
                'type' => 'foo',
                'expected' => 'www.食狮.公司.cn',
            ],
        ];
    }

    /**
     * @covers ::getSubDomain
     * @covers ::getRegistrableDomain
     * @covers ::getPublicSuffix
     *
     * @dataProvider getSubDomainProvider
     */
    public function testDomainInfo(
        string $host,
        string $section,
        string $subDomain,
        string $registrableDomain,
        string $publicSuffix
    ) {
        self::assertSame($subDomain, $this->extension->getSubDomain($host, $section));
        self::assertSame($registrableDomain, $this->extension->getRegistrableDomain($host, $section));
        self::assertSame($publicSuffix, $this->extension->getPublicSuffix($host, $section));
    }

    public function getSubDomainProvider(): array
    {
        return [
            'icann domain' => [
                'host' => 'www.bbc.co.uk',
                'section' => Rules::ICANN_DOMAINS,
                'subDomain' => 'www',
                'registrableDomain' => 'bbc.co.uk',
                'publicSuffix' => 'co.uk',
            ],
            'private domain' => [
                'host' => 'foo.domain-parser.github.io',
                'section' => Rules::PRIVATE_DOMAINS,
                'subDomain' => 'foo',
                'registrableDomain' => 'domain-parser.github.io',
                'publicSuffix' => 'github.io',
            ],
            'icann domain for a private domain' => [
                'host' => 'foo.domain-parser.github.io',
                'section' => Rules::ICANN_DOMAINS,
                'subDomain' => 'foo.domain-parser',
                'registrableDomain' => 'github.io',
                'publicSuffix' => 'io',
            ],
            'invalid section given (1)' => [
                'host' => 'www.bbc.co.uk',
                'section' => 'foo',
                'subDomain' => 'www',
                'registrableDomain' => 'bbc.co.uk',
                'publicSuffix' => 'co.uk',
            ],
            'invalid section given (2)' => [
                'host' => 'foo.domain-parser.github.io',
                'section' => 'foo',
                'subDomain' => 'foo',
                'registrableDomain' => 'domain-parser.github.io',
                'publicSuffix' => 'github.io',
            ],
        ];
    }

    /**
     * @covers ::isValidTld
     * @covers ::isValidICANNSuffix
     * @covers ::isValidPrivateSuffix
     *
     * @dataProvider isValidDataProvider
     */
    public function testIsValidMethods(string $host, bool $isValidTld, bool $isValidICANN, bool $isValidPrivate)
    {
        self::assertSame($isValidTld, $this->extension->isValidTld($host));
        self::assertSame($isValidICANN, $this->extension->isValidICANNSuffix($host));
        self::assertSame($isValidPrivate, $this->extension->isValidPrivateSuffix($host));
    }

    public function isValidDataProvider()
    {
        return [
            'basic icann website' => [
                'host' => 'bbc.co.uk',
                'isValidTld' => true,
                'isValidICANN' => true,
                'isValidPrivate' => false,
            ],
            'basic private website' => [
                'host' => 'foo.github.io',
                'isValidTld' => true,
                'isValidICANN' => true,
                'isValidPrivate' => true,
            ],
            'host is too short' => [
                'host' => 'github.io',
                'isValidTld' => true,
                'isValidICANN' => true,
                'isValidPrivate' => false,
            ],
            'invalid host' => [
                'host' => '::',
                'isValidTld' => false,
                'isValidICANN' => false,
                'isValidPrivate' => false,
            ],
            'non registred host' => [
                'host' => 'localhost',
                'isValidTld' => false,
                'isValidICANN' => false,
                'isValidPrivate' => false,
            ],
        ];
    }

    /**
     * @covers ::hostToAscii
     * @covers ::hostToUnicode
     *
     * @dataProvider urlConverterDataProvider
     */
    public function testURLConverter(string $url, string $ascii_url, string $unicode_url)
    {
        self::assertSame($ascii_url, $this->extension->hostToAscii($url));
        self::assertSame($unicode_url, $this->extension->hostToUnicode($url));
    }

    public function urlConverterDataProvider()
    {
        return [
            'basic ascii URL' => [
                'url' => 'https://user:pass@bbc.co.uk/path?query#fragment',
                'ascii_url' => 'https://user@bbc.co.uk/path?query#fragment',
                'unicode_url' => 'https://user@bbc.co.uk/path?query#fragment',
            ],
            'basic unicode URL' => [
                'url' => 'https://user:pass@www.食狮.公司.cn/path?query#fragment',
                'ascii_url' => 'https://user@www.xn--85x722f.xn--55qx5d.cn/path?query#fragment',
                'unicode_url' => 'https://user@www.食狮.公司.cn/path?query#fragment',
            ],
            'invalid url' => [
                'url' => '::',
                'ascii_url' => '::',
                'unicode_url' => '::',
            ],
            'url without host' => [
                'url' => '/path?query#fragment',
                'ascii_url' => '/path?query#fragment',
                'unicode_url' => '/path?query#fragment',
            ],
            'url without invalid host' => [
                'url' => 'https://user@127.0.0.1/path?query#fragment',
                'ascii_url' => 'https://user@127.0.0.1/path?query#fragment',
                'unicode_url' => 'https://user@127.0.0.1/path?query#fragment',
            ],
        ];
    }
}

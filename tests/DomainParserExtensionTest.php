<?php

/**
 * Twig PHP Domain Parser Extension.
 *
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @license https://github.com/bakame-php/twig-domain-parser-extension/blob/master/LICENSE (MIT License)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace BakameTest\Pdp\Twig;

use Bakame\Pdp\Twig\DomainParserExtension;
use Pdp\Cache;
use Pdp\CurlHttpClient;
use Pdp\Domain;
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
     */
    public function testGetDomainTest()
    {
        self::assertInstanceOf(Domain::class, $this->extension->getDomain('foo.example.com'));
    }


    /**
     * @covers ::getSubDomain
     * @covers ::getRegistrableDomain
     * @covers ::getPublicSuffix
     * @covers ::filterSection
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
     * @covers ::isTopLevelDomain
     * @covers ::isICANN
     * @covers ::isPrivate
     * @covers ::isKnown
     *
     * @dataProvider isValidDataProvider
     */
    public function testIsValidMethods(
        string $host,
        bool $isTopLevelDomain,
        bool $isICANN,
        bool $isPrivate,
        bool $isKnown
    ) {
        self::assertSame($isTopLevelDomain, $this->extension->isTopLevelDomain($host));
        self::assertSame($isICANN, $this->extension->isICANN($host));
        self::assertSame($isPrivate, $this->extension->isPrivate($host));
        self::assertSame($isKnown, $this->extension->isKnown($host));
    }

    public function isValidDataProvider()
    {
        return [
            'basic icann website' => [
                'host' => 'bbc.co.uk',
                'isTopLevelDomain' => true,
                'isICANN' => true,
                'isPrivate' => false,
                'isKnown' => true,
            ],
            'basic private website' => [
                'host' => 'foo.github.io',
                'isTopLevelDomain' => true,
                'isICANN' => false,
                'isPrivate' => true,
                'isKnown' => true,
            ],
            'result depend on the domaine label length' => [
                'host' => 'github.io',
                'isTopLevelDomain' => true,
                'isICANN' => false,
                'isPrivate' => false,
                'isKnown' => false,
            ],
            'invalid host' => [
                'host' => '::',
                'isTopLevelDomain' => false,
                'isICANN' => false,
                'isPrivate' => false,
                'isKnown' => false,
            ],
            'non registred host' => [
                'host' => 'example.localhost',
                'isTopLevelDomain' => false,
                'isICANN' => false,
                'isPrivate' => false,
                'isKnown' => false,
            ],
        ];
    }
}

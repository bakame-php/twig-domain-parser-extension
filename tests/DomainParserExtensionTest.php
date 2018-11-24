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
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Bakame\Pdp\Twig\DomainParserExtension
 */
final class DomainParserExtensionTest extends TestCase
{
    private $extension;

    public function setUp()
    {
        $this->extension = DomainParserExtension::createFromManager(
            new Manager(new Cache(), new CurlHttpClient())
        );
    }

    /**
     * @covers ::resolve
     */
    public function testGetDomainTest()
    {
        self::assertInstanceOf(Domain::class, $this->extension->resolve('foo.example.com'));
    }

    /**
     * @covers ::isTopLevelDomain
     *
     * @dataProvider isValidDataProvider
     */
    public function testIsValidMethods(string $host, bool $isTopLevelDomain)
    {
        self::assertSame($isTopLevelDomain, $this->extension->isTopLevelDomain($host));
    }

    public function isValidDataProvider()
    {
        return [
            'basic icann website' => [
                'host' => 'bbc.co.uk',
                'isTopLevelDomain' => true,
            ],
            'basic private website' => [
                'host' => 'foo.github.io',
                'isTopLevelDomain' => true,
            ],
            'result depend on the domaine label length' => [
                'host' => 'github.io',
                'isTopLevelDomain' => true,
            ],
            'invalid host' => [
                'host' => '::',
                'isTopLevelDomain' => false,
            ],
            'non registred host' => [
                'host' => 'example.localhost',
                'isTopLevelDomain' => false,
            ],
        ];
    }
}

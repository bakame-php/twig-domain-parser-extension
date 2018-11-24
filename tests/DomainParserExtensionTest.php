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
use Pdp\Manager;
use Twig_Test_IntegrationTestCase;

final class DomainParserExtensionTest extends Twig_Test_IntegrationTestCase
{
    public function getExtensions(): array
    {
        return [
            DomainParserExtension::createFromManager(
                new Manager(new Cache(), new CurlHttpClient())
            ),
        ];
    }

    public function getFixturesDir(): string
    {
        return __DIR__.'/Fixtures/';
    }
}

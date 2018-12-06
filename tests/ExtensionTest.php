<?php

/**
 * Twig PHP Domain Parser Extension (https://github.com/bakame-php/twig-domain-parser-extension).
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace BakameTest\Twig\Pdp;

use Bakame\Twig\Pdp\Extension;
use Pdp\Cache;
use Pdp\CurlHttpClient;
use Pdp\Manager;
use Twig_Test_IntegrationTestCase;

final class ExtensionTest extends Twig_Test_IntegrationTestCase
{
    public function getExtensions(): array
    {
        return [
            Extension::createFromManager(
                new Manager(new Cache(), new CurlHttpClient())
            ),
        ];
    }

    public function getFixturesDir(): string
    {
        return __DIR__.'/Fixtures/';
    }
}

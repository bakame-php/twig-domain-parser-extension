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

namespace Bakame\Pdp\Twig;

use Pdp\Domain;
use Pdp\Manager;
use Pdp\Rules;
use Pdp\TopLevelDomains;
use Throwable;
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleTest;

final class DomainParserExtension extends Twig_Extension
{
    /**
     * @var Rules
     */
    private $rules;

    /**
     * @var TopLevelDomains
     */
    private $topLevelDomains;

    /**
     * Create a new instance from a Pdp\Manager instance.
     */
    public static function createFromManager(Manager $manager): self
    {
        return new self($manager->getRules(), $manager->getTLDs());
    }

    /**
     * New instance.
     */
    public function __construct(Rules $rules, TopLevelDomains $topLevelDomains)
    {
        $this->rules = $rules;
        $this->topLevelDomains = $topLevelDomains;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction('resolve_domain', [$this, 'resolve']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(): array
    {
        return [
            new Twig_SimpleTest('topLevelDomain', [$this, 'isTopLevelDomain']),
        ];
    }

    /**
     * Tells whether the host contains a valid Top Level Domains according to the IANA records.
     *
     * @param mixed $host a string or a stringable object
     */
    public function isTopLevelDomain($host): bool
    {
        try {
            return $this->topLevelDomains->contains((new Domain($host))->getLabel(0));
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Returns the domain object.
     */
    public function resolve($host, string $section = ''): Domain
    {
        static $sectionList = [
            Rules::ICANN_DOMAINS => Rules::ICANN_DOMAINS,
            Rules::PRIVATE_DOMAINS => Rules::PRIVATE_DOMAINS,
        ];

        return $this->rules->resolve($host, $sectionList[$section] ?? '');
    }
}

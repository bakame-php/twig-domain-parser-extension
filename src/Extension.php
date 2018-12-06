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

namespace Bakame\Twig\Pdp;

use Closure;
use Pdp\Domain;
use Pdp\Manager;
use Pdp\Rules;
use Pdp\TopLevelDomains;
use Throwable;
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleTest;

final class Extension extends Twig_Extension
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
    public static function createFromManager(
        Manager $manager,
        string $psl_url = Manager::PSL_URL,
        string $rzd_url = Manager::RZD_URL
    ): self {
        return new self($manager->getRules($psl_url), $manager->getTLDs($rzd_url));
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
            new Twig_SimpleFunction('resolve_domain', Closure::fromCallable([$this, 'resolve'])),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(): array
    {
        return [
            new Twig_SimpleTest('topLevelDomain', Closure::fromCallable([$this, 'isTopLevelDomain'])),
        ];
    }

    /**
     * Tells whether the host contains a valid Top Level Domains according to the IANA records.
     *
     * @param mixed $host a string or a stringable object
     */
    private function isTopLevelDomain($host): bool
    {
        try {
            return $this->topLevelDomains->contains((new Domain($host))->getLabel(0));
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Returns the domain object.
     *
     * @param mixed $host a string or a stringable object
     */
    private function resolve($host, string $section = ''): Domain
    {
        static $sectionList = [
            Rules::ICANN_DOMAINS => Rules::ICANN_DOMAINS,
            Rules::PRIVATE_DOMAINS => Rules::PRIVATE_DOMAINS,
        ];

        return $this->rules->resolve($host, $sectionList[$section] ?? '');
    }
}

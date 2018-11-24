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
use Pdp\Rules;
use Pdp\TopLevelDomains;
use Throwable;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Twig_SimpleTest;
use function in_array;

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
    public function getFilters(): array
    {
        return [
            new Twig_SimpleFilter('subDomain', [$this, 'getSubDomain']),
            new Twig_SimpleFilter('registrableDomain', [$this, 'getRegistrableDomain']),
            new Twig_SimpleFilter('publicSuffix', [$this, 'getPublicSuffix']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction('domain', [$this, 'getDomain']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(): array
    {
        return [
            new Twig_SimpleTest('topLevelDomain', [$this, 'isTopLevelDomain']),
            new Twig_SimpleTest('icannSuffix', [$this, 'isICANN']),
            new Twig_SimpleTest('privateSuffix', [$this, 'isPrivate']),
            new Twig_SimpleTest('knownSuffix', [$this, 'isKnown']),
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
    public function getDomain($host, string $section = ''): Domain
    {
        return $this->rules->resolve($host, $this->filterSection($section));
    }

    /**
     * Returns the supported section.
     */
    private function filterSection(string $section): string
    {
        if (in_array($section, ['', Rules::ICANN_DOMAINS, Rules::PRIVATE_DOMAINS], true)) {
            return $section;
        }

        return '';
    }

    /**
     * Returns the host subdomain part.
     *
     * If the host can not be resolved an empty string is returned
     *
     * @param mixed $host a string or a stringable object
     */
    public function getSubDomain($host, string $section = ''): string
    {
        return (string) $this->getDomain($host, $section)->getSubDomain();
    }

    /**
     * Returns the host registrable domain part.
     *
     * If the host can not be resolved an empty string is returned
     *
     * @param mixed $host a string or a stringable object
     */
    public function getRegistrableDomain($host, string $section = ''): string
    {
        return (string) $this->getDomain($host, $section)->getRegistrableDomain();
    }

    /**
     * Returns the host public suffix part.
     *
     * If the host can not be resolved an empty string is returned
     *
     * @param mixed $host a string or a stringable object
     */
    public function getPublicSuffix($host, string $section = ''): string
    {
        return (string) $this->getDomain($host, $section)->getPublicSuffix();
    }

    /**
     * Tells whether the host contains a known public suffix.
     *
     * @param mixed $host a string or a stringable object
     */
    public function isKnown($host): bool
    {
        return $this->getDomain($host)->isKnown();
    }

    /**
     * Tells whether the host contains a valid ICANN public suffix.
     *
     * @param mixed $host a string or a stringable object
     */
    public function isICANN($host): bool
    {
        return $this->getDomain($host)->isICANN();
    }

    /**
     * Tells whether the host contains a valid Private public suffix.
     *
     * @param mixed $host a string or a stringable object
     */
    public function isPrivate($host): bool
    {
        return $this->getDomain($host)->isPrivate();
    }
}

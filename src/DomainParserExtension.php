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

namespace Bakame\Pdp\Twig;

use Pdp\Domain;
use Pdp\Rules;
use Pdp\TopLevelDomains;
use Throwable;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleTest;
use function in_array;
use function League\Uri\build;
use function League\Uri\parse;

final class DomainParserExtension extends Twig_Extension
{
    /**
     * @var Rules
     */
    private $rules;

    /**
     * @var TopLevelDomains
     */
    private $tldCollection;

    public function __construct(Rules $rules, TopLevelDomains $tldCollection)
    {
        $this->rules = $rules;
        $this->tldCollection = $tldCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new Twig_SimpleFilter('domain', [$this, 'getDomain']),
            new Twig_SimpleFilter('subDomain', [$this, 'getSubDomain']),
            new Twig_SimpleFilter('registrableDomain', [$this, 'getRegistrableDomain']),
            new Twig_SimpleFilter('publicSuffix', [$this, 'getPublicSuffix']),
            new Twig_SimpleFilter('host_to_unicode', [$this, 'hostToUnicode']),
            new Twig_SimpleFilter('host_to_ascii', [$this, 'hostToAscii']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(): array
    {
        return [
            new Twig_SimpleTest('tld', [$this, 'isValidTLD']),
            new Twig_SimpleTest('icannSuffix', [$this, 'isValidICANNSuffix']),
            new Twig_SimpleTest('privateSuffix', [$this, 'isValidPrivateSuffix']),
        ];
    }

    /**
     * Converts the host part of an URI into its ascii representation.
     *
     * If the url can not be parsed the input is returned as is.
     *
     * @param mixed $url a string or a stringable object
     *
     * @return string|mixed
     */
    public function hostToAscii($url)
    {
        try {
            $components = parse($url);
        } catch (Throwable $e) {
            return $url;
        }

        if (null === $components['host']) {
            return build($components);
        }

        $domain = $this->rules->resolve($components['host']);
        if (null === $domain->getContent()) {
            return build($components);
        }

        $components['host'] = $domain->toAscii()->getContent();

        return build($components);
    }

    /**
     * Converts the host part of an URI into its unicode representation.
     *
     * If the url can not be parsed the input is returned as is.
     *
     * @param mixed $url a string or a stringable object
     *
     * @return string|mixed
     */
    public function hostToUnicode($url)
    {
        try {
            $components = parse($url);
        } catch (Throwable $e) {
            return $url;
        }

        if (null === $components['host']) {
            return build($components);
        }

        $domain = $this->rules->resolve($components['host']);
        if (null === $domain->getContent()) {
            return build($components);
        }

        $components['host'] = $domain->toUnicode()->getContent();

        return build($components);
    }

    /**
     * Returns the domain name of the submitted URL.
     *
     * If the url can not be parsed an empty string is returned
     *
     * @param mixed $url a string or a stringable object
     */
    public function getDomain($url, string $type = 'ascii'): string
    {
        try {
            $components = parse($url);
        } catch (Throwable $e) {
            return '';
        }

        $domain = $this->rules->resolve($components['host']);
        if (null === $domain->getContent()) {
            return (string) $components['host'];
        }

        if ('ascii' === $type) {
            return (string) $domain->toAscii()->getContent();
        }

        if ('unicode' === $type) {
            return (string) $domain->toUnicode()->getContent();
        }

        return $domain->getContent();
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
        if (!in_array($section, ['', Rules::ICANN_DOMAINS, Rules::PRIVATE_DOMAINS], true)) {
            $section = '';
        }

        return (string) $this->rules->resolve($host, $section)->getSubDomain();
    }

    /**
     * Returns the host registrable domain part.
     *
     * If the host can not be resolved an empty string is returned
     *
     * @param mixed $host a string or a stringable object
     */
    public function getRegistrableDomain($host, $section = ''): string
    {
        if (!in_array($section, ['', Rules::ICANN_DOMAINS, Rules::PRIVATE_DOMAINS], true)) {
            $section = '';
        }

        return (string) $this->rules->resolve($host, $section)->getRegistrableDomain();
    }

    /**
     * Returns the host public suffix part.
     *
     * If the host can not be resolved an empty string is returned
     *
     * @param mixed $host a string or a stringable object
     */
    public function getPublicSuffix($host, $section = ''): string
    {
        if (!in_array($section, ['', Rules::ICANN_DOMAINS, Rules::PRIVATE_DOMAINS], true)) {
            $section = '';
        }

        return (string) $this->rules->resolve($host, $section)->getPublicSuffix();
    }

    /**
     * Tells whether the host contains a valid Top Level Domains according to the IANA records.
     *
     * @param mixed $host a string or a stringable object
     */
    public function isValidTLD($host): bool
    {
        try {
            return $this->tldCollection->contains((new Domain($host))->getLabel(0));
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Tells whether the host contains a valid ICANN public suffix.
     *
     * @param mixed $host a string or a stringable object
     */
    public function isValidICANNSuffix($host): bool
    {
        return $this->rules->resolve($host, Rules::ICANN_DOMAINS)->isICANN();
    }

    /**
     * Tells whether the host contains a valid Private public suffix.
     *
     * @param mixed $host a string or a stringable object
     */
    public function isValidPrivateSuffix($host): bool
    {
        return $this->rules->resolve($host, Rules::PRIVATE_DOMAINS)->isPrivate();
    }
}

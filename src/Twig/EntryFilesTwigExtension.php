<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Twig;

use Psr\Container\ContainerInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class EntryFilesTwigExtension extends AbstractExtension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('encore_entry_js_files', [$this, 'getWebpackJsFiles']),
            new TwigFunction('encore_entry_css_files', [$this, 'getWebpackCssFiles']),
            new TwigFunction('encore_entry_script_tags', [$this, 'renderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags', [$this, 'renderWebpackLinkTags'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $entryName
     * @param string $entrypointName
     *
     * @return array
     */
    public function getWebpackJsFiles($entryName, $entrypointName = '_default')
    {
        return $this->getEntrypointLookup($entrypointName)
            ->getJavaScriptFiles($entryName);
    }

    /**
     * @param string $entryName
     * @param string $entrypointName
     *
     * @return array
     */
    public function getWebpackCssFiles($entryName, $entrypointName = '_default')
    {
        return $this->getEntrypointLookup($entrypointName)
            ->getCssFiles($entryName);
    }

    /**
     * @param string      $entryName
     * @param string|null $packageName
     * @param string      $entrypointName
     *
     * @throws \Exception
     *
     * @return string
     */
    public function renderWebpackScriptTags($entryName, $packageName = null, $entrypointName = '_default')
    {
        return $this->getTagRenderer()
            ->renderWebpackScriptTags($entryName, $packageName, $entrypointName);
    }

    /**
     * @param string      $entryName
     * @param string|null $packageName
     * @param string      $entrypointName
     *
     * @throws \Exception
     *
     * @return string
     */
    public function renderWebpackLinkTags($entryName, $packageName = null, $entrypointName = '_default')
    {
        return $this->getTagRenderer()
            ->renderWebpackLinkTags($entryName, $packageName, $entrypointName);
    }

    /**
     * @param string $entrypointName
     *
     * @return EntrypointLookupInterface
     */
    private function getEntrypointLookup($entrypointName)
    {
        return $this->container->get('webpack_encore.entrypoint_lookup_collection')
            ->getEntrypointLookup($entrypointName);
    }

    /** @return TagRenderer */
    private function getTagRenderer()
    {
        return $this->container->get('webpack_encore.tag_renderer');
    }
}

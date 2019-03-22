<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Asset;

use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class TagRenderer
{
    private $entrypointLookupCollection;

    private $packages;

    public function __construct($entrypointLookupCollection, Packages $packages)
    {
        if ($entrypointLookupCollection instanceof EntrypointLookupInterface) {
            @trigger_error(
                sprintf(
                    'The "$entrypointLookupCollection" argument in method "%s()" must be an instance of EntrypointLookupCollection.',
                    __METHOD__
                ),
                E_USER_DEPRECATED
            );

            $this->entrypointLookupCollection = new EntrypointLookupCollection(
                new ServiceLocator(['_default' => function () use ($entrypointLookupCollection) {
                    return $entrypointLookupCollection;
                }])
            );
        } elseif ($entrypointLookupCollection instanceof EntrypointLookupCollection) {
            $this->entrypointLookupCollection = $entrypointLookupCollection;
        } else {
            throw new \TypeError(
                'The "$entrypointLookupCollection" argument must be an instance of EntrypointLookupCollection.'
            );
        }

        $this->packages = $packages;
    }

    /**
     * @param string      $entryName
     * @param string|null $packageName
     * @param string      $entrypointName
     *
     * @return string
     */
    public function renderWebpackScriptTags($entryName, $packageName = null, $entrypointName = '_default')
    {
        $scriptTags = [];
        foreach ($this->getEntrypointLookup($entrypointName)->getJavaScriptFiles($entryName) as $filename) {
            $scriptTags[] = sprintf(
                '<script src="%s"></script>',
                htmlentities($this->getAssetPath($filename, $packageName))
            );
        }

        return implode('', $scriptTags);
    }

    /**
     * @param string      $entryName
     * @param string|null $packageName
     * @param string      $entrypointName
     *
     * @return string
     */
    public function renderWebpackLinkTags($entryName, $packageName = null, $entrypointName = '_default')
    {
        $scriptTags = [];
        foreach ($this->getEntrypointLookup($entrypointName)->getCssFiles($entryName) as $filename) {
            $scriptTags[] = sprintf(
                '<link rel="stylesheet" href="%s">',
                htmlentities($this->getAssetPath($filename, $packageName))
            );
        }

        return implode('', $scriptTags);
    }

    /**
     * @param string      $assetPath
     * @param string|null $packageName
     *
     * @return string
     */
    private function getAssetPath($assetPath, $packageName = null)
    {
        if (null === $this->packages) {
            throw new \Exception('To render the script or link tags, run "composer require symfony/asset".');
        }

        return $this->packages->getUrl(
            $assetPath,
            $packageName
        );
    }

    /**
     * @param string $buildName
     *
     * @return EntrypointLookupInterface
     */
    private function getEntrypointLookup($buildName)
    {
        return $this->entrypointLookupCollection->getEntrypointLookup($buildName);
    }
}

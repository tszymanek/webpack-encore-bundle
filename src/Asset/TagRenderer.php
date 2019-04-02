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
        $entryPointLookup = $this->getEntrypointLookup($entrypointName);
        $integrityHashes = ($entryPointLookup instanceof IntegrityDataProviderInterface) ? $entryPointLookup->getIntegrityData() : [];

        foreach ($entryPointLookup->getJavaScriptFiles($entryName) as $filename) {
            $attributes = [
                'src' => $this->getAssetPath($filename, $packageName),
            ];

            if (isset($integrityHashes[$filename])) {
                $attributes['integrity'] = $integrityHashes[$filename];
            }

            $scriptTags[] = sprintf(
                '<script %s></script>',
                $this->convertArrayToAttributes($attributes)
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
        $entryPointLookup = $this->getEntrypointLookup($entrypointName);
        $integrityHashes = ($entryPointLookup instanceof IntegrityDataProviderInterface) ? $entryPointLookup->getIntegrityData() : [];

        foreach ($entryPointLookup->getCssFiles($entryName) as $filename) {
            $attributes = [
                'rel' => 'stylesheet',
                'href' => $this->getAssetPath($filename, $packageName),
            ];

            if (isset($integrityHashes[$filename])) {
                $attributes['integrity'] = $integrityHashes[$filename];
            }

            $scriptTags[] = sprintf(
                '<link %s>',
                $this->convertArrayToAttributes($attributes)
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

    /**
     * @param array $attributesMap
     *
     * @return string
     */
    private function convertArrayToAttributes(array $attributesMap)
    {
        return implode(' ', array_map(
            function ($key, $value) {
                return sprintf('%s="%s"', $key, htmlentities($value));
            },
            array_keys($attributesMap),
            $attributesMap
        ));
    }
}

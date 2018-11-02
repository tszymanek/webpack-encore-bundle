<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Asset;

use Symfony\Component\Asset\Packages;

final class TagRenderer
{
    private $entrypointLookup;

    private $manifestLookup;

    private $packages;

    public function __construct(EntrypointLookup $entrypointLookup, ManifestLookup $manifestLookup, Packages $packages)
    {
        $this->entrypointLookup = $entrypointLookup;
        $this->manifestLookup = $manifestLookup;
        $this->packages = $packages;
    }

    public function renderWebpackScriptTags(string $entryName, string $packageName = null): string
    {
        $scriptTags = [];
        foreach ($this->entrypointLookup->getJavaScriptFiles($entryName) as $filename) {
            $scriptTags[] = sprintf(
                '<script src="%s"></script>',
                htmlentities($this->getAssetPath($filename, $packageName))
            );
        }

        return implode('', $scriptTags);
    }

    public function renderWebpackLinkTags(string $entryName, string $packageName = null): string
    {
        $scriptTags = [];
        foreach ($this->entrypointLookup->getCssFiles($entryName) as $filename) {
            $scriptTags[] = sprintf(
                '<link rel="stylesheet" href="%s" />',
                htmlentities($this->getAssetPath($filename, $packageName))
            );
        }

        return implode('', $scriptTags);
    }

    private function getAssetPath(string $assetPath, string $packageName = null): string
    {
        // to help avoid issues, use the manifest.json path always
        $newAssetPath = $this->manifestLookup->getManifestPath($assetPath);

        // could not find the path in manifest.json?
        if (null === $newAssetPath) {
            throw new \InvalidArgumentException(sprintf('The path "%s" could not be found in the Encore "manifest.json" file. This could be a problem with the dumped entrypoints.json file.', $assetPath));
        }

        return $this->packages->getUrl(
            $newAssetPath,
            $packageName
        );
    }
}
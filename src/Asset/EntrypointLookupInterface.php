<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Asset;

use Symfony\WebpackEncoreBundle\Exception\EntrypointNotFoundException;
use Symfony\WebpackEncoreBundle\Service\ResetInterface;

interface EntrypointLookupInterface extends ResetInterface
{
    /**
     * @param string $entryName
     *
     * @throws EntrypointNotFoundException if an entry name is passed that does not exist in entrypoints.json
     *
     * @return array
     */
    public function getJavaScriptFiles($entryName);

    /**
     * @param string $entryName
     *
     * @throws EntrypointNotFoundException if an entry name is passed that does not exist in entrypoints.json
     *
     * @return array
     */
    public function getCssFiles($entryName);

    /**
     * Resets the state of this service.
     */
    public function reset();
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Client;

interface ScopedClientInterface
{
    /**
     * Returns a string representation of the API currently in use.
     *
     * @return string
     */
    public function getApi(): string;

    /**
     * Returns the ID of the space currently in use.
     *
     * @return string
     */
    public function getSpaceId(): string;

    /**
     * Returns the ID of the environment currently in use.
     *
     * @return string
     */
    public function getEnvironmentId(): string;
}

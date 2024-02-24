<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Synchronization;

use Contentful\Core\Resource\ResourceInterface;

/**
 * The Result of synchronization.
 */
class Result
{
    /**
     * @var ResourceInterface[]
     */
    private $items;

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    private $done;

    /**
     * Result constructor.
     *
     * @param ResourceInterface[] $items
     */
    public function __construct(array $items, string $token, bool $done)
    {
        $this->items = $items;
        $this->token = $token;
        $this->done = $done;
    }

    /**
     * Returns the items retrieved by this synchronization operation.
     *
     * @return ResourceInterface[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Returns the token needed to continue the synchronization.
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Returns true if there are currently no more results in the synchronization.
     */
    public function isDone(): bool
    {
        return $this->done;
    }
}

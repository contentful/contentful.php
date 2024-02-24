<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Core\Api\Link;
use Contentful\Core\Resource\ResourceInterface;

class MockClientEntryHas extends MockClient
{
    /**
     * @var string[]
     */
    private $availableLinks;

    /**
     * MockClient constructor.
     *
     * @param string[] $availableLinks
     */
    public function __construct(array $availableLinks, string $spaceId = 'spaceId', string $environmentId = 'environmentId')
    {
        $this->availableLinks = $availableLinks;
        parent::__construct($spaceId, $environmentId);
    }

    public function resolveLink(Link $link, ?string $locale = null): ResourceInterface
    {
        $id = $link->getId();
        if (\in_array($id, $this->availableLinks, true)) {
            return 'Entry' === $link->getLinkType()
                ? MockEntry::withSys($id)
                : MockAsset::withSys($id);
        }

        throw new \Exception();
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Core\Api\Link;
use Contentful\Core\Api\LinkResolverInterface;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Delivery\Client\ClientInterface;

class LinkResolver implements LinkResolverInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * LinkResolver constructor.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveLink(Link $link, array $parameters = []): ResourceInterface
    {
        $locale = $parameters['locale'] ?? \null;

        switch ($link->getLinkType()) {
            case 'Asset':
                return $this->client->getAsset($link->getId(), $locale);
            case 'ContentType':
                return $this->client->getContentType($link->getId());
            case 'Entry':
                return $this->client->getEntry($link->getId(), $locale);
            case 'Environment':
                return $this->client->getEnvironment();
            case 'Space':
                return $this->client->getSpace();
            default:
                throw new \InvalidArgumentException(\sprintf(
                    'Trying to resolve link for unknown type "%s".',
                    $link->getLinkType()
                ));
        }
    }
}

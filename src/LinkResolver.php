<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Core\Api\Link;
use Contentful\Core\Api\LinkResolverInterface;

class LinkResolver implements LinkResolverInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * LinkResolver constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveLink(Link $link, array $parameters = [])
    {
        $locale = isset($parameters['locale']) ? $parameters['locale'] : \null;

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

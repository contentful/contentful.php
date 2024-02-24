<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Mapper;

use Contentful\Core\Api\Link;

/**
 * Class BaseTaggedMapper.
 *
 * Extension of the BaseMapper for tagged types.
 */
abstract class BaseTaggedMapper extends BaseMapper
{
    protected function createTags(array $data): array
    {
        $tags = [];

        if (isset($data['metadata'])) {
            $metadata = $data['metadata'];
            if (isset($metadata['tags'])) {
                $tagLinks = $metadata['tags'];
                foreach ($tagLinks as $tagLink) {
                    if ('Link' !== $tagLink['sys']['type']) {
                        // currently not supported
                        continue;
                    }

                    $link = new Link($tagLink['sys']['id'], $tagLink['sys']['linkType']);
                    $tag = $this->client->resolveLink($link);
                    $tags[] = $tag;
                }
            }
        }

        return $tags;
    }
}

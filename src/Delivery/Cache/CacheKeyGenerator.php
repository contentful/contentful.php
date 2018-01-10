<?php

namespace Contentful\Delivery\Cache;

final class CacheKeyGenerator
{
    public static function getSpaceKey()
    {
        return 'space';
    }

    /**
     * @param $id
     */
    public static function getContentTypeKey($id)
    {
        return 'ct-'.$id;
    }
}

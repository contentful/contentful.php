<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);
$classes = [
    'Contentful\\Exception\\AccessTokenInvalidException' => 'Contentful\\Core\\Exception\\AccessTokenInvalidException',
    'Contentful\\Exception\\ApiException' => 'Contentful\\Core\\Api\\Exception',
    'Contentful\\Exception\\BadRequestException' => 'Contentful\\Core\\Exception\\BadRequestException',
    'Contentful\\Exception\\InvalidQueryException' => 'Contentful\\Core\\Exception\\InvalidQueryException',
    'Contentful\\Exception\\NotFoundException' => 'Contentful\\Core\\Exception\\NotFoundException',
    'Contentful\\Exception\\RateLimitExceededException' => 'Contentful\\Core\\Exception\\RateLimitExceededException',
    'Contentful\\File\\File' => 'Contentful\\Core\\File\\File',
    'Contentful\\File\\FileInterface' => 'Contentful\\Core\\File\\FileInterface',
    'Contentful\\File\\ImageFile' => 'Contentful\\Core\\File\\ImageFile',
    'Contentful\\File\\ImageOptions' => 'Contentful\\Core\\File\\ImageOptions',
    'Contentful\\File\\LocalUploadFile' => 'Contentful\\Core\\File\\LocalUploadFile',
    'Contentful\\File\\RemoteUploadFile' => 'Contentful\\Core\\File\\RemoteUploadFile',
    'Contentful\\File\\UnprocessedFileInterface' => 'Contentful\\Core\\File\\UnprocessedFileInterface',
    'Contentful\\Link' => 'Contentful\\Core\\Api\\Link',
    'Contentful\\Location' => 'Contentful\\Core\\Api\\Location',
    'Contentful\\ResourceArray' => 'Contentful\\Core\\Resource\\ResourceArray',
    'Contentful\\Delivery\\Asset' => 'Contentful\\Delivery\\Resource\\Asset',
    'Contentful\\Delivery\\ContentType' => 'Contentful\\Delivery\\Resource\\ContentType',
    'Contentful\\Delivery\\ContentTypeField' => 'Contentful\\Delivery\\Resource\\ContentType\\Field',
    'Contentful\\Delivery\\DynamicEntry' => 'Contentful\\Delivery\\Resource\\Entry',
    'Contentful\\Delivery\\Space' => 'Contentful\\Delivery\\Resource\\Space',
    'Contentful\\Delivery\\Locale' => 'Contentful\\Delivery\\Resource\\Locale',
    'Contentful\\Delivery\\Synchronization\\DeletedResource' => 'Contentful\\Delivery\\Resource\\DeletedResource',
    'Contentful\\Delivery\\Synchronization\\DeletedAsset' => 'Contentful\\Delivery\\Resource\\DeletedAsset',
    'Contentful\\Delivery\\Synchronization\\DeletedContentType' => 'Contentful\\Delivery\\Resource\\DeletedContentType',
    'Contentful\\Delivery\\Synchronization\\DeletedEntry' => 'Contentful\\Delivery\\Resource\\DeletedEntry',
];

foreach ($classes as $previous => $new) {
    class_alias($new, $previous, true);
}

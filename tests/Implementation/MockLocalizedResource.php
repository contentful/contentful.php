<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\LocalizedResource;
use Contentful\Delivery\SystemProperties\Entry as SystemProperties;

class MockLocalizedResource extends LocalizedResource
{
    protected $sys;

    public function __construct(array $locales)
    {
        $this->sys = new SystemProperties([
            'id' => 'resourceId',
            'type' => 'Entry',
            'space' => MockSpace::withSys('spaceId'),
            'environment' => MockEnvironment::withSys('environmentId'),
            'contentType' => MockContentType::withSys('contentTypeId'),
            'revision' => 1,
            'createdAt' => '2010-01-01T12:00:00.123Z',
            'updatedAt' => '2010-01-01T12:00:00.123Z',
        ]);

        $this->initLocales($locales);
    }

    public function getSystemProperties()
    {
        return $this->sys;
    }

    public function getLocaleFromInput($locale = \null)
    {
        return parent::getLocaleFromInput($locale);
    }

    public function walkFallbackChain(array $valueMap, $localeCode, Environment $environment)
    {
        return parent::walkFallbackChain($valueMap, $localeCode, $environment);
    }

    public function jsonSerialize()
    {
        return [];
    }
}

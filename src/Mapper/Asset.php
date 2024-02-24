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
use Contentful\Core\File\File;
use Contentful\Core\File\FileInterface;
use Contentful\Core\File\ImageFile;
use Contentful\Core\File\LocalUploadFile;
use Contentful\Core\File\RemoteUploadFile;
use Contentful\Delivery\Resource\Asset as ResourceClass;
use Contentful\Delivery\SystemProperties\Asset as SystemProperties;

/**
 * Asset class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\Asset.
 */
class Asset extends BaseTaggedMapper
{
    public function map($resource, array $data): ResourceClass
    {
        /** @var SystemProperties $sys */
        $sys = $this->createSystemProperties(SystemProperties::class, $data);
        $locale = $sys->getLocale();

        /** @var ResourceClass $asset */
        $asset = $this->hydrator->hydrate($resource ?: ResourceClass::class, [
            'sys' => $sys,
            'title' => isset($data['fields']['title'])
                ? $this->normalizeFieldData($data['fields']['title'], $locale)
                : null,
            'description' => isset($data['fields']['description'])
                ? $this->normalizeFieldData($data['fields']['description'], $locale)
                : null,
            'file' => isset($data['fields']['file'])
                ? array_map([$this, 'buildFile'], $this->normalizeFieldData($data['fields']['file'], $locale))
                : null,
        ]);

        $asset->initLocales($asset->getSystemProperties()->getEnvironment()->getLocales());

        $tags = $this->createTags($data);
        $asset->initTags($tags);

        return $asset;
    }

    /**
     * Creates a File or a subclass thereof.
     */
    protected function buildFile(array $data): FileInterface
    {
        if (isset($data['uploadFrom'])) {
            return new LocalUploadFile(
                $data['fileName'],
                $data['contentType'],
                new Link(
                    $data['uploadFrom']['sys']['id'],
                    $data['uploadFrom']['sys']['linkType']
                )
            );
        }

        if (isset($data['upload'])) {
            return new RemoteUploadFile(
                $data['fileName'],
                $data['contentType'],
                $data['upload']
            );
        }

        if (isset($data['details']['image'])) {
            return new ImageFile(
                $data['fileName'],
                $data['contentType'],
                $data['url'],
                $data['details']['size'],
                $data['details']['image']['width'],
                $data['details']['image']['height']
            );
        }

        return new File(
            $data['fileName'],
            $data['contentType'],
            $data['url'],
            $data['details']['size']
        );
    }
}

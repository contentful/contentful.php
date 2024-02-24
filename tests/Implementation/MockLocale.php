<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Resource\Locale;
use Contentful\Delivery\SystemProperties\Locale as SystemProperties;

class MockLocale extends Locale
{
    /**
     * MockLocale constructor.
     */
    public function __construct(array $data)
    {
        $data['sys'] ??= new SystemProperties(['id' => $data['code'], 'type' => 'Locale']);

        parent::__construct($data);
    }

    public static function withSys(string $id = 'localeId', array $data = []): self
    {
        return new static(array_merge($data, [
            'sys' => new SystemProperties([
                'id' => $id,
                'type' => 'Locale',
                'version' => 1,
            ]),
        ]));
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Resource\Locale;
use Contentful\Delivery\SystemProperties;

class MockLocale extends Locale
{
    public function __construct(array $data)
    {
        $data['sys'] = $data['sys'] ?? new SystemProperties(['id' => $data['code'], 'type' => 'Locale']);

        parent::__construct($data);
    }

    public static function withSys(string $id, $data = []): self
    {
        return new static(\array_merge($data, [
            'sys' => new SystemProperties(['id' => $id, 'type' => 'Locale']),
        ]));
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties\Component;

trait LocaleTrait
{
    /**
     * @var string|null
     */
    protected $locale;

    protected function initLocale(array $data)
    {
        $this->locale = $data['locale'] ?? null;
    }

    protected function jsonSerializeLocale(): array
    {
        return [
            'locale' => $this->locale,
        ];
    }

    /**
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }
}

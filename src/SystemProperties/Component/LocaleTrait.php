<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
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

    /**
     * @param array $data
     */
    protected function initLocale(array $data)
    {
        $this->locale = $data['locale'] ?? \null;
    }

    /**
     * @return array
     */
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

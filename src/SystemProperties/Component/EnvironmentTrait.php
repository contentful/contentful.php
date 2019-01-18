<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties\Component;

use Contentful\Delivery\Resource\Environment;

trait EnvironmentTrait
{
    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @param array $data
     */
    protected function initEnvironment(array $data)
    {
        $this->environment = $data['environment'];
    }

    /**
     * @return array
     */
    protected function jsonSerializeEnvironment(): array
    {
        return [
            'environment' => $this->environment->asLink(),
        ];
    }

    /**
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }
}

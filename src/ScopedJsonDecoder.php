<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use function GuzzleHttp\json_decode as guzzle_json_decode;

class ScopedJsonDecoder
{
    /**
     * @var string
     */
    private $spaceId;

    /**
     * @var string
     */
    private $environmentId;

    /**
     * ScopedJsonDecoder constructor.
     */
    public function __construct(string $spaceId, string $environmentId)
    {
        $this->spaceId = $spaceId;
        $this->environmentId = $environmentId;
    }

    public function decode(string $json)
    {
        $data = guzzle_json_decode($json, true);

        $spaceId = '';
        $environmentId = '';
        if (\is_array($data)) {
            $spaceId = $this->extractSpaceId($data);
            $environmentId = $this->extractEnvironmentId($data);
        }

        if ($spaceId !== $this->spaceId || $environmentId !== $this->environmentId) {
            throw new \InvalidArgumentException(sprintf('Trying to parse and build a JSON structure with a client configured for handling space "%s" and environment "%s", but space "%s" and environment "%s" were detected.', $this->spaceId, $this->environmentId, $spaceId, $environmentId));
        }

        return $data;
    }

    /**
     * Checks a data structure and extracts the space ID, if present.
     */
    private function extractSpaceId(array $data): string
    {
        // Space resource
        if (isset($data['sys']['type']) && 'Space' === $data['sys']['type']) {
            return $data['sys']['id'];
        }

        // Environment resource
        if (isset($data['sys']['type']) && 'Environment' === $data['sys']['type']) {
            return $this->spaceId;
        }

        // Resource linked to a space
        if (isset($data['sys']['space'])) {
            return $data['sys']['space']['sys']['id'];
        }

        // Array resource with at least an element
        if (isset($data['items'][0]['sys']['space'])) {
            return $data['items'][0]['sys']['space']['sys']['id'];
        }

        // Empty array resource
        if (isset($data['items']) && !$data['items']) {
            return $this->spaceId;
        }

        return '[blank]';
    }

    /**
     * Checks a data structure and extracts the environment ID, if present.
     */
    public function extractEnvironmentId(array $data): string
    {
        // Space resource
        if (isset($data['sys']['type']) && 'Space' === $data['sys']['type']) {
            return $this->environmentId;
        }

        // Environment resource
        if (isset($data['sys']['type']) && 'Environment' === $data['sys']['type']) {
            return $data['sys']['id'];
        }

        // Resource linked to a environment
        if (isset($data['sys']['environment'])) {
            return $data['sys']['environment']['sys']['id'];
        }

        // Array resource with at least an element
        if (isset($data['items'][0]['sys']['environment'])) {
            return $data['items'][0]['sys']['environment']['sys']['id'];
        }

        // Empty array resource
        if (isset($data['items']) && !$data['items']) {
            return $this->environmentId;
        }

        return 'master';
    }
}

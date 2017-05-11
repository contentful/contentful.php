<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Synchronization;

use Contentful\Delivery\HasSystemProperties;
use Contentful\Delivery\SystemProperties;

/**
 * A DeletedResource encodes metadata about a deleted resource.
 */
abstract class DeletedResource implements \JsonSerializable
{
    use HasSystemProperties;

    /**
     * DeletedResource constructor.
     *
     * @param SystemProperties $sys
     */
    public function __construct(SystemProperties $sys)
    {
        $this->sys = $sys;
    }

    /**
     * Returns an object to be used by `json_encode` to serialize objects of this class.
     *
     * @return object
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php JsonSerializable::jsonSerialize
     * @api
     */
    public function jsonSerialize()
    {
        return (object) [
            'sys' => $this->sys
        ];
    }
}

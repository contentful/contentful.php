<?php

namespace Contentful\Delivery;

use Contentful\Exception\InvalidArgumentException;
use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Traversable;

class InstanceCache implements CacheInterface
{
    /**
     * @var array cached instances
     */
    private $instances = array();

    /**
     * @var string control characters for keys, reserved by PSR-16
     */
    const PSR16_RESERVED = '/\{|\}|\(|\)|\/|\\\\|\@|\:/u';

    /**
     * Fetches a value from the cache.
     *
     * @param string $key The unique key of this item in the cache.
     * @param mixed $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function get($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->instances[$key]['value'];
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key The key of the item to store.
     * @param mixed $value The value of the item to store, must be serializable.
     * @param null|int|DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                     the driver supports TTL then the library may set a default value
     *                                     for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null)
    {
        $this->validateKey($key);

        if (is_int($ttl)) {
            $expiresAt = $this->getTime() + $ttl;
        } elseif ($ttl instanceof DateInterval) {
            $expiresAt = date_create_from_format("U", $this->getTime())->add($ttl)->getTimestamp();
        } elseif ($ttl === null) {
            $expiresAt = $ttl;
        } else {
            throw new InvalidArgumentException("Invalid TTL: " . print_r($ttl, true));
        }

        $this->instances[$key] = array(
            'value' => $value,
            'expires_at' => $expiresAt
        );

        return true;
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key)
    {
        $this->validateKey($key);

        if (!array_key_exists($key, $this->instances)) {
            return false;
        }

        unset($this->instances[$key]);
        return true;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        $this->instances = array();
        return true;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys A list of keys that can obtained in a single operation.
     * @param mixed $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        if (! is_array($keys) && ! $keys instanceof Traversable) {
            throw new InvalidArgumentException("Keys must be either of type array or Traversable!");
        }

        $list = array();

        foreach ($keys as $key) {
            $list[$key] = $this->get($key, $default);
        }

        return $list;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        if (! is_array($values) && ! $values instanceof Traversable) {
            throw new InvalidArgumentException("Keys must be either of type array or Traversable!");
        }

        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        if (! is_array($keys) && ! $keys instanceof Traversable) {
            throw new InvalidArgumentException("Keys must be either of type array or Traversable!");
        }

        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        $this->validateKey($key);

        if (!array_key_exists($key, $this->instances)) {
            return false;
        }

        $instance = $this->instances[$key];

        if ($instance['expires_at'] === null || $instance['expires_at'] > $this->getTime()) {
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    protected function validateKey($key)
    {
        if (preg_match(self::PSR16_RESERVED, $key, $match) === 1) {
            throw new InvalidArgumentException("Invalid character in key: {$match[0]}");
        }
    }

    /**
     * Retrieve time
     *
     * @return int current timestamp
     */
    protected function getTime()
    {
        return time();
    }
}

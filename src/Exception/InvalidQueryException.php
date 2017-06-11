<?php
/**
 * @copyright 2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Exception;

/**
 * An InvalidQueryException is thrown when the query could not be executed. The most common case is setting a non-existing
 * content type or field name.
 *
 * @api
 */
class InvalidQueryException extends ApiException
{
}

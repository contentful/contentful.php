<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

use Contentful\Delivery\ContentType;

/**
 * A Query is used to filter and order collections when making API requests.
 *
 * @api
 */
class Query
{
    /**
     * ISO8601 but with the seconds forced to 0.
     */
    const DATE_FORMAT = 'Y-m-d\TH:i:00P';

    /**
     * Maximum number of results to retrieve
     *
     * @var int|null
     */
    private $limit;

    /**
     * The first result to retrieve
     *
     * @var int|null
     */
    private $skip;

    /**
     * The field to order the retrieved results by
     *
     * @var string|null
     */
    private $order;

    /**
     * For entries, limit results to this content type
     *
     * @var string|null
     */
    private $contentType;

    /**
     * Assets only. Limit to a group of MIME-types.
     *
     * @var string|null
     */
    private $mimeTypeGroup;

    /**
     * Whether the order of the retrieved results should be reversed.
     *
     * @var bool
     */
    private $orderReversed = false;

    /**
     * List of fields for filters
     *
     * @var  array
     */
    private $whereConditions = [];

    /**
     * Filter entity result
     *
     * @var array
     */
    private $select = [];    
    
    /**
     * Query constructor.
     *
     * Empty for now, included for forward compatibility.
     *
     * @api
     */
    public function __construct()
    {
    }

    /**
     * Returns the parameters to execute this query.
     *
     * @return array
     *
     * @api
     */
    public function getQueryData()
    {
        $data = [
            'limit' => $this->limit,
            'skip' => $this->skip,
            'content_type' => $this->contentType,
            'mimetype_group' => $this->mimeTypeGroup
        ];

        if ($this->order !== null) {
            $dir = '';
            if ($this->orderReversed) {
                $dir .= '-';
            }
            $data['order'] = $dir . $this->order;
        }
        foreach ($this->whereConditions as $whereCondition) {
            $key = $whereCondition['field'];
            if ($whereCondition['operator'] !== null) {
                $key .= '[' . $whereCondition['operator'] . ']';
            }
            $data[$key] = $whereCondition['value'];
        }
        
        if (count($this->select) > 0) {
            $data['select'] = implode(',', $this->select);
        }
        
        return $data;
    }

    /**
     * The urlencoded query string for this query.
     *
     * @return string
     *
     * @internal
     */
    public function getQueryString()
    {
        return http_build_query($this->getQueryData(), '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Sets the index of the first result to retrieve. To reset set to NULL.
     *
     * @param  int|null $skip The index of the first result that will be retrieved. Must be >= 0.
     *
     * @return $this
     *
     * @throws \RangeException If $skip is not within the specified range
     *
     * @api
     */
    public function setSkip($skip)
    {
        if ($skip !== null && $skip < 0) {
            throw new \RangeException('$skip must be 0 or larger, ' . $skip . ' given.');
        }

        $this->skip = $skip;

        return $this;
    }

    /**
     * Returns the index of the first result to retrieve. Can return NULL if no index is specified.
     *
     * @return int|null
     *
     * @api
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * Set the maximum number of results to retrieve. To reset set to NULL;
     *
     * @param  int|null $limit The maximum number of results to retrieve, must be between 1 and 1000 or null
     *
     * @return $this
     *
     * @throws \RangeException If $maxArguments is not withing the specified range
     *
     * @api
     */
    public function setLimit($limit)
    {
        if ($limit !== null && ($limit < 1 || $limit > 1000)) {
            throw new \RangeException('$maxResults must be between 0 and 1000, ' . $limit . ' given.');
        }

        $this->limit = $limit;

        return $this;
    }

    /**
     * Returns the maximum number of results to retrieve. Can return NULL if no maximum is defined.
     *
     * @return int|null
     *
     * @api
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the order of the items retrieved by this query.
     *
     * Note that when ordering Entries by fields you must set the content_type URI query parameter to the ID of
     * the Content Type you want to filter by.
     *
     * @param  string|null $field
     * @param  bool        $reverse
     *
     * @return $this
     *
     * @api
     */
    public function orderBy($field, $reverse = false)
    {
        $this->order = $field;
        $this->orderReversed = $reverse;

        return $this;
    }

    /**
     * Set the content type to which results should be limited. Set to NULL to not filter for a content type.
     *
     * Only works when querying entries.
     *
     * @param  ContentType|string|null $contentType
     *
     * @return $this
     *
     * @api
     */
    public function setContentType($contentType)
    {
        if ($contentType instanceof ContentType) {
            $contentType = $contentType->getId();
        }

        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Returns the name of the content type for which results will be retrieved. Can be NULL if no content type is set
     *
     * @return string|null The name of the content type results will be limited to
     *
     * @api
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param  string|null $group
     *
     * @return $this
     *
     * @api
     */
    public function setMimeTypeGroup($group)
    {
        $validGroups = [
            'attachment',
            'plaintext',
            'image',
            'audio',
            'video',
            'richtext',
            'presentation',
            'spreadsheet',
            'pdfdocument',
            'archive',
            'code',
            'markup'
        ];
        if ($group !== null && !in_array($group, $validGroups, true)) {
            throw new \InvalidArgumentException('Unknown MIMI-type group \'' . $group . '\'');
        }

        $this->mimeTypeGroup = $group;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMimeTypeGroup()
    {
        return $this->mimeTypeGroup;
    }

    /**
     * Add a filter condition to this query.
     *
     * Valid operators are
     * - ne
     * - in
     * - nin
     * - exists
     * - lt
     * - lte
     * - gt
     * - gte
     * - match
     * - near
     * - within
     *
     * @param  string                                         $field
     * @param  string|\DateTimeInterface|\Contentful\Location $value
     * @param  string|null                                    $operator The operator to use for this condition.
     *                                                                  Default is strict equality.
     * @return $this
     *
     * @throws \InvalidArgumentException If $operator is not one of the valid values
     *
     * @api
     */
    public function where($field, $value, $operator = null)
    {
        $validOperators = [
            'ne', // No equal
            'in', // Includes
            'nin', // Excludes
            'exists', // Exists
            'lt', // Less than
            'lte', // Less than or equal to
            'gt', // Greater than
            'gte', // Greater than or equal to,
            'match', // Full text search
            'near', // Nearby (for locations)
            'within', // Within an rectangle (for locations)
        ];
        if ($operator !== null && !in_array($operator, $validOperators, true)) {
            throw new \InvalidArgumentException('Unknown operator \'' . $operator . '\'');
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(self::DATE_FORMAT);
        }
        if ($value instanceof Location) {
            $value = $value->queryStringFormatted();
        }

        $this->whereConditions[] = [
            'field' => $field,
            'value' => $value,
            'operator' => $operator
        ];

        return $this;
    }

    /**
     * The select operator allows you to choose what to return from an entity.
     * You provide a json path and the API will return the property at that path
     *
     * @param array $select
     * @return $this
     *
     * @api
     */
    public function select(array $select)
    {
        $this->select = $select;

        return $this;
    }    
}

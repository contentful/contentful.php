# UPGRADE FROM 4.x to 5.0

## Removal of general ResourcePool class

The class `Contentful\Delivery\ResourcePool` was deprecated in version 4.1, and was removed in 5.0; if you were type hinting against this implementation, you can change it to use `Contentful\Delivery\ResourcePool\Extended` instead, or better yet, use the interface `Contentful\Core\Resource\ResourcePoolInterface`.

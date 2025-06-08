<?php

namespace Library\Router;

/**
 * Represents a match in the router list
 *
 * @package Library\Router
 */
interface RouterMatchInterface
{
    /**
     * Create a match from the incoming request and the attributes that are associated with it
     *
     * @param string $value The value that is associated with a pattern.
     *      Pattern is usually an regex pattern or similar. The value is what the pattern is pointing at.
     *      This is usually a string that represents a Controller, but can an an instance of a class or
     *      a `Callable` object or other.
     * @param array $attributes Attributes are the values that are dynamic in the URI path.
     *      If the pattern is /artist/:artistID/album/:albumID, and the URI is
     *      /artist/1/album/2, then the attributes would be `['artistID' => '1', 'albumID' => '2]`
     * @param string $pattern The pattern that matched.
     * @return mixed
     */
    public function __construct(mixed $value, array $attributes, string $pattern);

    /**
     * Get the value that a pattern is associated with.
     *
     * This is often time a Controller, the canonical name of the controller (`Controller::class`) or
     * anything that matching pattern is connected to.
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Dynamic values embedded in the URI.
     *
     * If the pattern is `/artist/:artistID/album/:albumID`, and the URI is
     * `/artist/1/album/2`, then the attributes would be `['artistID' => '1', 'albumID' => '2]`
     * @return array
     */
    public function getAttributes(): array;

    /**
     * The pattern that was matched.
     *
     * @return string
     */
    public function getPattern(): string;
}

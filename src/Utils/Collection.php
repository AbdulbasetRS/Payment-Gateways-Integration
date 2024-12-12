<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Utils;

/**
 * Collection Class
 *
 * A utility class to manage and manipulate arrays with common collection operations.
 * Provides methods for creating a collection, finding items by specific conditions, and converting the collection back to an array.
 *
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Utils
 */
class Collection
{
    /**
     * The items stored in the collection.
     *
     * @var array
     */
    private array $items;

    /**
     * Constructor to initialize the collection with an array of items.
     *
     * @param array $items The initial items to store in the collection.
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Creates a new instance of the Collection class with the provided items.
     *
     * @param array $items The items to include in the collection.
     * @return self A new Collection instance.
     */
    public static function make(array $items = []): self
    {
        return new static($items);
    }

    /**
     * Finds the first item in the collection that matches the given key and value.
     *
     * @param string $key The key to look for in the items.
     * @param mixed $value The value that the key should match.
     * @return array|null The first matching item as an associative array, or null if no match is found.
     */
    public function firstWhere(string $key, $value): ?array
    {
        foreach ($this->items as $item) {
            if (isset($item[$key]) && $item[$key] === $value) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Converts the collection to an array.
     *
     * @return array The items in the collection as an array.
     */
    public function toArray(): array
    {
        return $this->items;
    }
}

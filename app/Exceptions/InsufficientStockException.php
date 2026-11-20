<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a transaction would make a product's quantity negative.
 * Caught in the controller to show the user a friendly error instead
 * of a generic 500 page.
 */
class InsufficientStockException extends Exception
{
    public function __construct(string $productName, int $available, int $requested)
    {
        $message = "Insufficient stock for \"{$productName}\": only {$available} available, but {$requested} were requested.";
        parent::__construct($message);
    }
}

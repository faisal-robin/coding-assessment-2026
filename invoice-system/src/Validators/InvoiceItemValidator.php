<?php

namespace Validators;

class InvoiceItemValidator
{
    /**
     * Validate invoice item data.
     *
     * @param string $name
     * @param float $price
     * @param int $quantity
     * @throws \InvalidArgumentException
     */
    public static function validate(string $name, float $price, int $quantity): void
    {
        if (trim($name) === '') {
            throw new \InvalidArgumentException("Item name is required");
        }

        if ($price <= 0) {
            throw new \InvalidArgumentException("Price must be greater than zero");
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException("Quantity must be greater than zero");
        }
    }
}

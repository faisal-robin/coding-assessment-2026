<?php

class InvoiceCalculator {

    private static $taxRates = null;

    /**
     * Load tax rates from JSON file (only once)
     */
    private static function loadTaxRates($filename = __DIR__ . '/../data/tax_rates.json') {
        if (self::$taxRates === null) {
            if (!file_exists($filename)) {
                throw new Exception("Tax rates file not found: " . $filename);
            }

            $json = file_get_contents($filename);
            $rates = json_decode($json, true);

            if ($rates === null) {
                throw new Exception("Failed to decode tax rates JSON");
            }

            self::$taxRates = $rates;
        }
    }

    /**
     * Calculate tax for a subtotal
     *
     * @param float $subtotal
     * @param string $region Format: "Country-State" e.g., "US-CA"
     * @return float Tax amount
     */
    public static function calculateTax(float $subtotal, string $region = 'US-CA'): float {
        self::loadTaxRates();

        [$country, $state] = explode('-', $region);

        $rate = 0.0;

        if (isset(self::$taxRates[$country])) {
            if (isset(self::$taxRates[$country][$state])) {
                $rate = self::$taxRates[$country][$state];
            } elseif (isset(self::$taxRates[$country]['default'])) {
                $rate = self::$taxRates[$country]['default'];
            }
        }

        return round($subtotal * $rate, 2);
    }

    /**
     * Apply business rules to an invoice
     *
     * @param Invoice $invoice
     * @return Invoice Modified invoice
     */
    public static function applyBusinessRules($invoice) {
        // Placeholder for future business rules
        return $invoice;
    }

    /**
     * Calculate line item total
     *
     * @param array $item Item with price and quantity/qty
     * @return float Line item total
     */
    public static function calculateLineItem(array $item): float {
        $price = $item['price'];
        $quantity = $item['quantity'];

        if ($quantity < 0 || $price < 0) {
            throw new InvalidArgumentException("Negative price or quantity not allowed");
        }

        return $price * $quantity;
    }

    /**
     * Format currency for display
     *
     * @param float $amount
     * @return string
     */
    public static function formatCurrency(float $amount): string {
        return '$' . number_format($amount, 2);
    }

    /**
     * Validate invoice
     *
     * @param Invoice $invoice
     * @return array List of validation errors
     */
    public static function validateInvoice($invoice): array {
        $errors = [];

        if (empty($invoice->getCustomer())) {
            $errors[] = "Customer name cannot be empty";
        }

        $items = $invoice->getItems();
        if (empty($items)) {
            $errors[] = "Invoice must have at least one item";
        }

        foreach ($items as $i => $item) {
            if (empty($item['name'])) {
                $errors[] = "Item #$i name cannot be empty";
            }
            if (($item['price'] ?? 0) <= 0) {
                $errors[] = "Item #$i price must be greater than 0";
            }
            $qty = $item['quantity'];
            if ($qty <= 0) {
                $errors[] = "Item #$i quantity must be greater than 0";
            }
        }

        return $errors;
    }
}

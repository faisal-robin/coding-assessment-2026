<?php
require_once __DIR__ . '/Validators/InvoiceItemValidator.php';
use Validators\InvoiceItemValidator;

/**
 * Invoice Class
 *
 * Handles invoice creation and management
 * Started: 2 weeks ago
 * Last modified: Friday (was in a hurry)
 */
class Invoice {

    private $customer;
    private $items = [];
    private $discount = 0;
    private $id;
    private $createdAt;

    public function __construct($customerName) {
        $this->customer = $customerName;
        $this->id = time(); // Not sure if this is the best approach...
        $this->createdAt = date('Y-m-d H:i:s');
    }

    /**
     * Add an item to the invoice
     * Note: Make sure to use consistent naming!
     */
    public function addItem($name, $price, $quantity) {

        // Validate first
        InvoiceItemValidator::validate($name, $price, $quantity);

        $this->items[] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity  // Using 'qty' here
        ];
    }

    /**
     * Calculate total
     * BUG: This doesn't match up with addItem() - need to fix
     */
    public function getTotal() {
        $total = 0;
        foreach ($this->items as $item) {
            // Accessing 'quantity' but we stored it as 'qty'!
            $total += $item['price'] * $item['quantity'];
        }
        return max(0, $total - $this->discount);
    }

    /**
     * Apply discount to invoice
     * TODO: Should discounts apply before or after tax?
     * TODO: Client hasn't decided on the business rules yet
     */
    public function applyDiscount($percent) {
        // Started implementing but not sure about requirements
        // throw new Exception("Not implemented - waiting on client clarification");

        // Trying basic implementation but commented out until we get clarity
        // $subtotal = $this->getTotal();
        // $this->discount = $subtotal * ($percent / 100);

        // For now just throw exception
        throw new Exception("Discount feature incomplete - need business rules from client");
    }

    /**
     * Get invoice ID
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get customer name
     */
    public function getCustomer() {
        return $this->customer;
    }

    /**
     * Get items array
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * Convert invoice to array for JSON serialization
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'customer' => $this->customer,
            'items' => $this->items,
            'discount' => $this->discount,
            'total' => $this->getTotal(),
            'created_at' => $this->createdAt
        ];
    }

    /**
     * Save invoice to file
     * FIXME: This overwrites everything! Need to fix but running out of time
     * Should APPEND to the file, not replace it
     */
    public function saveToFile($filename = 'data/invoices.json') {
        $data = $this->toArray();
        $invoices = [];

        if (file_exists($filename)) {
            $contents = file_get_contents($filename);
            $decoded = json_decode($contents, true);

            if (is_array($decoded)) {
                // Handle single invoice case
                $invoices = isset($decoded['id']) ? [$decoded] : $decoded;
            }
        }

        $invoices[] = $data;

        file_put_contents(
            $filename,
            json_encode($invoices, JSON_PRETTY_PRINT)
        );

        // TODO: Fix this before client demo!
        return true;
    }

    /**
     * Load invoice from file by ID
     * Started this but didn't finish testing it
     */
    public static function loadFromFile($id, $filename = 'data/invoices.json') {
        if (!file_exists($filename)) {
            throw new Exception("Invoice file not found");
        }

        $contents = file_get_contents($filename);
        $invoices = json_decode($contents, true);

        // Handle both single invoice and array of invoices
        // (since saveToFile is broken and only saves one)
        if (isset($invoices['id'])) {
            $invoices = [$invoices];
        }

        foreach ($invoices as $invoiceData) {
            if ($invoiceData['id'] == $id) {
                $invoice = new Invoice($invoiceData['customer']);
                $invoice->id = $invoiceData['id'];
                $invoice->discount = $invoiceData['discount'];

                foreach ($invoiceData['items'] as $item) {
                    // This might break because of the qty/quantity issue
                    $qty = isset($item['quantity']) ? $item['quantity'] : $item['qty'];
                    $invoice->addItem($item['name'], $item['price'], $qty);
                }

                return $invoice;
            }
        }

        throw new Exception("Invoice not found: " . $id);
    }
}

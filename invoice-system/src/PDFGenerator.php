<?php
namespace PDF;
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use Invoice;

class PDFGenerator {

    private Dompdf $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true); // allow images if needed
        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generate PDF from Invoice
     *
     * @param Invoice $invoice
     * @param string|null $filename optional: save PDF to file
     * @return string PDF content
     */
    public function generatePDF(Invoice $invoice, ?string $filename = null): string
    {
        $html = $this->generateHTML($invoice);

        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        $pdfContent = $this->dompdf->output();

        if ($filename) {
            file_put_contents($filename, $pdfContent);
        }

        return $pdfContent;
    }

    /**
     * Generate HTML for PDF
     */
    private function generateHTML(Invoice $invoice): string
    {
        $itemsHtml = '';
        foreach ($invoice->getItems() as $item) {
            $qty = $item['quantity'] ?? $item['qty'];
            $itemsHtml .= '<tr>
                <td>' . htmlspecialchars($item['name']) . '</td>
                <td>$' . number_format($item['price'], 2) . '</td>
                <td>' . intval($qty) . '</td>
                <td>$' . number_format($item['price'] * $qty, 2) . '</td>
            </tr>';
        }

        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #000; padding: 5px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { font-weight: bold; }
            </style>
        </head>
        <body>
            <h2>Invoice #' . $invoice->getId() . '</h2>
            <p>Customer: ' . htmlspecialchars($invoice->getCustomer()) . '</p>
            <p>Date: ' . $invoice->toArray()['created_at'] . '</p>

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $itemsHtml . '
                </tbody>
            </table>

            <p class="total">Discount: $' . number_format($invoice->toArray()['discount'], 2) . '</p>
            <p class="total">Total: $' . number_format($invoice->getTotal(), 2) . '</p>
        </body>
        </html>';

        return $html;
    }
}

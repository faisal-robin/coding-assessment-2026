<?php
// Autoload Composer packages (for Dompdf)
require_once __DIR__ . '/vendor/autoload.php';

// Include your classes
require_once __DIR__ . '/src/Invoice.php';
require_once __DIR__ . '/src/PDFGenerator.php';

use PDF\PDFGenerator;

// 1. Create a new invoice
$invoice = new Invoice("John Doe");
$invoice->addItem("Laptop", 1200, 1);
$invoice->addItem("Mouse", 25, 2);

// 2. Generate PDF and output directly in browser
$pdfContent = $invoice->generatePDF();
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="invoice.pdf"');
echo $pdfContent;
exit;

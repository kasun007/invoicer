<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGeneratorService
{
    private string $pdfFolder;
    private string $projectRoot;

    public function __construct(string $pdfFolder)
    {
        $this->pdfFolder = $pdfFolder;
        $this->projectRoot = dirname(__DIR__, 2); // Go up two levels from src/Service to project root
    }


    public function generateInvoicePdf(array $invoiceData, string $filename): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);

        $html = $this->renderInvoiceHtml($invoiceData);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        if (!is_dir($this->pdfFolder)) {
            mkdir($this->pdfFolder, 0775, true);
        }

        $pdfPath = rtrim($this->pdfFolder, '/') . '/' . $filename;
        file_put_contents($pdfPath, $dompdf->output());

        return $pdfPath;
    }

    private function h(?string $v): string
    {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function money(float $v): string
    {
        return number_format($v, 2, '.', ',');
    }

    private function renderInvoiceHtml(array $data): string
    {
        $itemsHtml = '';
        $subtotal = 0.0;

        foreach ($data['items'] as $item) {
            $qty   = (float)($item['quantity'] ?? 0);
            $price = (float)($item['unitPrice'] ?? 0);
            $amt   = $qty * $price;
            $subtotal += $amt;

            $itemsHtml .= sprintf(
                '<tr>
                    <td class="qty">%s</td>
                    <td class="desc">%s</td>
                    <td class="unit">%s</td>
                    <td class="amt">%s</td>
                 </tr>',
                $this->money($qty),
                $this->h($item['description'] ?? ''),
                $this->money($price),
                $this->money($amt)
            );
        }

        $taxRate = (float)($data['tax']['rate'] ?? 0.0);
        $taxAmt  = $subtotal * $taxRate;
        $total   = $subtotal + $taxAmt;

        $currency = $this->h($data['invoice']['currency'] ?? 'USD');

        // Format totals for display
        $subtotalDisplay = $this->money($subtotal);
        $taxAmtDisplay = $this->money($taxAmt);
        $totalDisplay = $this->money($total);
        $taxRateDisplay = $this->h($data['tax']['rate'] ?? 0);

        $logo = '';
        if (!empty($data['company']['logoUrl'])) {
            $logoUrl = $data['company']['logoUrl'];

            // Convert relative paths to absolute paths for Dompdf
            if (strpos($logoUrl, '/') === 0) {
                // Path starts with /, make it relative to project root
                $logoUrl = $this->projectRoot . $logoUrl;
            } elseif (strpos($logoUrl, 'assets/') === 0) {
                // Relative path starting with assets/, prepend project root
                $logoUrl = $this->projectRoot . '/' . $logoUrl;
            } elseif (!file_exists($logoUrl)) {
                // If it's not an absolute path and doesn't exist, try prepending project root
                $logoUrl = $this->projectRoot . '/' . $logoUrl;
            }

            // Check if file exists
            if (file_exists($logoUrl)) {
                // For SVG files, use data URI
                if (pathinfo($logoUrl, PATHINFO_EXTENSION) === 'svg') {
                    $svgContent = file_get_contents($logoUrl);
                    $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svgContent);
                    $logo = sprintf(
                        '<img class="logo" src="%s" alt="logo" />',
                        $this->h($dataUri)
                    );
                } else {
                    // For other image formats (PNG, JPG, etc.), convert to data URI
                    $imageData = file_get_contents($logoUrl);
                    $mimeType = mime_content_type($logoUrl);
                    $dataUri = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                    $logo = sprintf(
                        '<img class="logo" src="%s" alt="logo" />',
                        $this->h($dataUri)
                    );
                }
            } else {
                // File doesn't exist, show placeholder
                $logo = '<div class="logo uploader">Logo</div>';
            }
        } else {
            // empty uploader-style box to match the template
            $logo = '<div class="logo uploader">Upload Logo</div>';
        }

        $totalsHtml = '<table class="totals">
  <tr>
    <td class="label">Subtotal</td>
    <td class="value">$' . htmlspecialchars($subtotalDisplay) . '</td>
  </tr>
  <tr>
    <td class="label">Sales Tax (' . htmlspecialchars($taxRateDisplay) . '%)</td>
    <td class="value">$' . htmlspecialchars($taxAmtDisplay) . '</td>
  </tr>
  <tr>
    <td class="label grand">Total (' . htmlspecialchars($currency) . ')</td>
    <td class="value grand">$' . htmlspecialchars($totalDisplay) . '</td>
  </tr>
</table>';

        // Build the HTML with variables directly, avoiding sprintf format errors
        $html = <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { margin: 40px 40px; }

  :root {
    --accent: #3b82f6;       /* light blue */
    --accent-ink: #1d4ed8;
    --ink: #0f172a;
    --muted: #6b7280;
    --border: #e5e7eb;
  }

  * { box-sizing: border-box; }

  body {
    font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
    color: var(--ink);
  }

  .header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 50px;
  }

  .header-table td {
    padding: 0;
    vertical-align: middle;
  }

  .logo-cell {
    width: 50%;
    text-align: left;
  }

  .invoice-cell {
    width: 50%;
    text-align: right;
    vertical-align: top;
  }

  .company {
    margin: 0;
  }

  .company h2 {
    margin: 0 0 6px 0;
    font-size: 18px;
  }

  .company .addr {
    font-size: 12px;
    color: var(--muted);
    line-height: 1.3;
  }

  .logo {
    max-width: 120px;
    height: auto;
  }

  .logo.uploader {
    border: 1px solid var(--border);
    border-radius: 8px;
    text-align: center;
    line-height: 48px;
    font-size: 12px;
    color: var(--muted);
  }

  .title {
    text-align: right;
    letter-spacing: 6px;
    color: var(--accent);
    font-weight: 700;
    font-size: 28px;
    margin: 18px 0 0 0;
  }

  .top-section {
    margin-top: 16px;
  }

  .combined-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  .combined-table td {
    padding: 0;
    vertical-align: top;
  }

  .billto-cell {
    width: 60%;
    padding-right: 20px;
  }

  .invoice-cell {
    width: 40%;
    text-align: right;
  }

  .billto-content {
    margin-top: 10px;
  }

  .billto-name {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 4px;
  }

  .billto-email {
    color: var(--accent-ink);
    font-weight: 500;
    margin-bottom: 2px;
  }

  .invoice-meta {
    width: 100%;
    font-size: 12px;
    color: #111827;
    border-collapse: collapse;
    margin-left: auto;
  }

  .invoice-meta td {
    padding: 2px 0;
    border: none;
  }

  .invoice-meta .label {
    font-weight: 500;
    text-align: left;
    padding-right: 12px;
    width: 90px;
  }

  .invoice-meta .value {
    text-align: right;
    font-weight: 600;
    width: 100px;
  }

  h3 {
    font-size: 12px;
    text-transform: uppercase;
    color: var(--accent-ink);
    margin: 0 0 8px 0;
    letter-spacing: 0.6px;
  }

  h1 {
    font-size: 36px;
    font-weight: bold;
    color: var(--accent);
    margin: 0;
  }

  .box {
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 10px;
  }

  .billto .name {
    font-weight: 600;
  }

  .billto-name {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 4px;
  }

  .billto-email {
    color: var(--accent-ink);
    font-weight: 500;
    margin-bottom: 2px;
  }

  .table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
    font-size: 12px;
  }

  .table th {
    background: #dbeafe;
    color: #1e40af;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: .4px;
    padding: 10px;
    border-bottom: 1px solid var(--border);
  }

  .table td {
    padding: 10px;
    border-bottom: 1px solid var(--border);
  }

  .table .qty {
    width: 48px;
  }

  .table .unit,
  .table .amt {
    width: 90px;
    text-align: right;
  }

  .totals {
    width: 45%;
    margin-left: auto;
    font-size: 12px;
    margin-top: 8px;
  }

  .totals td {
    padding: 6px 8px;
  }

  .totals .label {
    text-align: right;
    color: #374151;
  }

  .totals .value {
    text-align: right;
  }

  .totals .grand {
    border-top: 2px solid var(--accent);
    font-weight: 700;
    color: #000;
  }

  .footer {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 30px;
    font-size: 11px;
    color: var(--muted);
    text-align: center;
    border-top: 1px solid var(--border);
    padding-top: 15px;
    margin-top: 20px;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    box-shadow: 0 -2px 8px rgba(59, 130, 246, 0.1);
  }

  .terms {
    margin-top: 18px;
    font-size: 11px;
    color: #374151;
  }
</style>
</head>
<body>

  <table class="header-table">
    <tr>
      <td class="logo-cell">
        {$logo}
      </td>
      <td class="invoice-cell">
        <h1>INVOICE</h1>
      </td>
    </tr>
  </table>



  <div class="top-section">
    <table class="combined-table">
      <tr>
        <td class="billto-cell">
          <h3>Bill To :</h3>
          <div class="billto-content">
            <div class="billto-name">{$this->h($data['billTo']['name'] ?? '')}</div>
            <div class="billto-email">{$this->h($data['billTo']['email'] ?? '')}</div>

          </div>
        </td>
        <td class="invoice-cell">
          <table class="invoice-meta">
            <tr>
              <td class="label">Invoice No :</td>
              <td class="value">{$this->h($data['invoice']['number'] ?? '')}</td>
            </tr>
            <tr>
              <td class="label">Invoice Date :</td>
              <td class="value">{$this->h($data['invoice']['date'] ?? '')}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>QTY</th>
        <th>Description</th>
        <th>Unit Price</th>
        <th>Amount</th>
      </tr>
    </thead>
    <tbody>
      {$itemsHtml}
    </tbody>
  </table>

{$totalsHtml}

  <div class="footer">Lunar Tech™ 2026</div>

</body>
</html>
HTML;

        return $html;
    }
}

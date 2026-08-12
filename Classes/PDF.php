<?php
/**
  * This file is part of consoletvs/invoices.
  *
  * (c) Erik Campobadal <soc@erik.cat>
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace ConsoleTVs\Invoices\Classes;

use Dompdf\Canvas;
use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

/**
 * This is the PDF class.
 *
 * @author Erik Campobadal <soc@erik.cat>
 */
class PDF
{
    /**
     * Generate the PDF.
     *
     * @method generate
     *
     * @param ConsoleTVs\Invoices\Classes\Invoice $invoice
     * @param string                              $template
     *
     * @return Dompdf\Dompdf
     */
    public static function generate(Invoice $invoice, $template = 'default')
    {
        $template = strtolower($template);

        $options = new Options();
        $logoHost = parse_url($invoice->logo, PHP_URL_HOST);

        if (is_string($logoHost) && $logoHost !== '') {
            $options->set('isRemoteEnabled', true);
            $options->set('allowedRemoteHosts', [$logoHost]);
        }

        $pdf = new Dompdf($options);

        $pdf->loadHtml(View::make('invoices::'.$template, ['invoice' => $invoice]));
        $pdf->render();

        if ($invoice->with_pagination) {
            $pdf->getCanvas()->page_script(function (
                int $pageNumber,
                int $pageCount,
                Canvas $canvas,
                FontMetrics $fontMetrics
            ): void {
                if ($pageCount <= 1) {
                    return;
                }

                $pageText = sprintf('%d of %d', $pageNumber, $pageCount);
                $font = $fontMetrics->getFont('DejaVu Sans, Arial, Helvetica, sans-serif', 'normal');
                $x = ($canvas->get_width() - $fontMetrics->getTextWidth($pageText, $font, 7)) / 2;

                $canvas->text($x, $canvas->get_height() - 20, $pageText, $font, 7);
            });
        }

        return $pdf;
    }
}

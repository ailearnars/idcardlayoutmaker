<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Autoload composer dependencies if available
if ( file_exists( SCHOOL_ID_CARD_MAKER_DIR . 'vendor/autoload.php' ) ) {
    require_once SCHOOL_ID_CARD_MAKER_DIR . 'vendor/autoload.php';
}

use Dompdf\Dompdf;
use Dompdf\Options;

function school_id_card_maker_generate_pdf($html, $orientation = 'portrait', $filename = 'id-card.pdf') {
    if ( ! class_exists( 'Dompdf\\Dompdf' ) ) {
        wp_die('DOMPDF library not found. Please run composer install.');
    }

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // Allow loading remote images (e.g. WP uploads)

    $dompdf = new Dompdf($options);

    // Read the CSS file
    $css_path = SCHOOL_ID_CARD_MAKER_DIR . 'assets/css/id-card.css';
    $css_content = file_exists($css_path) ? file_get_contents($css_path) : '';

    // Add CSS wrapper
    $full_html = '
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body { font-family: sans-serif; margin: 0; padding: 0; }
            .page-break { page-break-after: always; }
            ' . $css_content . '
        </style>
    </head>
    <body>
        ' . $html . '
    </body>
    </html>';

    $dompdf->loadHtml($full_html);

    // Size based on orientation
    if ($orientation === 'vertical' || $orientation === 'portrait') {
        // Vertical: 54mm x 86mm ~ 153.07pt x 243.78pt
        $customPaper = array(0,0,153.07,243.78);
        $dompdf->setPaper($customPaper);
    } else {
        // Horizontal: 86mm x 54mm ~ 243.78pt x 153.07pt
        $customPaper = array(0,0,243.78,153.07);
        $dompdf->setPaper($customPaper);
    }

    $dompdf->render();

    $dompdf->stream($filename, array("Attachment" => false)); // Attachment false opens in browser
    exit;
}

<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @var int $order_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$pdf_font = apply_filters('pdf_font_family', '"dejavu sans"');
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <style type="text/css">
        body {
            color: #000;
            font-family: <?php echo $pdf_font ?>;
        }
        h1, h2, h3, p {
            margin: 0;
        }
        .logo {
            width: 50%;
            float: left;
            max-width: 350px;
        }
        .order_info {
            width: 50%;
            float: right;
            font-size: 8pt;
            text-align: right;
        }
        .order_info h1 {
            font-size: 11pt;
        }
        .order_info h2 {
            font-size: 10pt;
        }
        .clear {
            clear: both;
        }
        .info {
            width: 100%;
            height: auto;
            font-size: 8pt;
            margin: 20px 0;
            clear: both;
        }
        .info .label {
            font-size: 8pt;
        }
        .info .info_emetteur {
            width: 50%;
            height: auto;
            float: left;
        }
        .info .info_emetteur .content {
            width: 90%;
            height: 120px;
            padding: 10px;
            background-color:#ddd;
        }
        .info .info_client {
            width: 50%;
            height: auto;
            float: left;
        }
        .info .info_client .content {
            width: 100%;
            height: 119px;
            padding: 10px;
            background-color: white;
            border: 1px solid #bbb;
        }
        .info .info_livraison, .info .note_livraison {
            width: 100%;
            height: auto;
            margin-top: 20px;
            clear: both;
        }
        .info .info_livraison .content, .info .note_livraison .content {
            width: 100%;
            padding: 10px;
            background-color: white;
            border: 1px solid #bbb;
        }
        .info .info_livraison .content small{
            font-size: 0.7em;
        }
        .after-list {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            background-color: white;
            border: 1px solid #bbb;
        }
        .after-list p {
            font-size: 8pt;
        }
        table {
            border: 0;
        }
        table.quote-table, table.quote-total, table.delivery-table {
            border: 0;
            font-size: 8pt;
        }
        .quote-total {
            width: 100%;
			display: table-footer-group;			   
            page-break-inside: avoid;
        }
        .quote-table small {
            font-size: 7pt;
        }
        .quote-table th, .quote-total th {
            text-align: center;
        }
        .quote-table th:first-child, .quote-total th:first-child {
            text-align: left;
        }
        .quote-total tr {
            border: 0;
            background-color: #f5f5f5;
        }
        .spaced-row td {
            padding-top: 12px;
        }
        .quote-table td, .quote-total td {
            border: 0;
        }
        .quote-total td {
            font-size: 7pt;
            height: 20px;
        }
        .quote-total td .wc-item-sku,
        .quote-total td .wc-item-meta-label {
            font-size: 1.1em;
        }
        .quote-table .last-col.tot {
            font-weight: 600;
        }
        .delivery-table {
            width: 100%;
            page-break-inside: auto;
        }
        .ywraq-buttons td {
            font-size: 0.8em;
        }
        .pdf-button {
            display: inline-block;
            padding: 7px 14px;
            text-decoration: none;
            text-transform: uppercase;
        }
        .pdf-button.accept {
            color: #0a5699;
			padding:20px;
			width:100%;
			text-align:center;
            border: 1px solid #0a5699;
			position: relative;
			text-decoration: none;
        }
		.pdf-button.accept p{
			padding:20px;
			width:100%;
			text-align:center;
			text-transform:none;
        }

        .pdf-button.reject {
            color: #8b050d;
            border: 1px solid #8b050d;
        }
        .footer {
            width: 86%;
            position: absolute;
            text-align: center;
            bottom: 10px;
        }
        .footer-content hr {
            display: block;
            height: 0;
            border: 0;
            padding: 0;
            border-top: 1pt solid #868686;
        }
        .footer-content p {
            font-size: 7pt;
        }
        .pagenum:before {
            content: counter(page);
        }
    </style>
	<?php do_action( 'yith_ywraq_quote_template_head' ); ?>
</head>

<body>
<?php do_action( 'yith_ywraq_quote_template_header', $order_id ); ?>
<div class="content">
	<?php do_action( 'yith_ywraq_quote_template_content', $order_id ); ?>
</div>
<?php do_action( 'yith_ywraq_quote_template_after_content', $order_id ); ?>
<?php do_action( 'yith_ywraq_quote_template_footer', $order_id ); ?>
</body>
</html>
<?php

// Optionally define the filesystem path to your system fonts
// otherwise tFPDF will use [path to tFPDF]/font/unifont/ directory
// define("_SYSTEM_TTFONTS", "C:/Windows/Fonts/");
ob_clean();
ob_start();
require 'tfpdf.php';

// $pdf = new tFPDF();
class wf_dhl_commercial_invoice extends tFPDF {

    public $xfactor = 0;
    public $yfactor = 0;
    public $new_page = 0;
    public $font_for_document  = 'DejaVu';
    public $ttf_for_document   = 'DejaVuSansCondensed.ttf';

    function get_package_total($data) {
        $this->total = $data;
    }

    function Header($data = '') {
        $total = $this->total;
        $total = 0; //($total - 5) *4 ;
        if ($total <= 0) {
            $total = 0;
        }
        
        $chartYPos = 35;
        $chartXPos = 70;
        if (!empty($data)) {
            $this->new_page = '2';
            $this->get_table_params(32);
            $start = $chartYPos;
            $this->Line(15, 45, 195, 45);
            $this->Line(15, 35, 195, 35);
            $this->Line(37, $start, 37, $start + 200);
            $this->Line(51, $start, 51, $start + 200);
            $this->Line(62, $start, 62, $start + 200);

            $this->Line(27, $start, 27, $start + 200);
            $this->Line(37, $start, 37, $start + 200);
            $this->Line(110, $start, 110, $start + 200);
            $this->Line(125, $start, 125, $start + 200);
            $this->Line(154, $start, 154, $start + 200);
            $this->Line(176, $start + 200, 176, $start + 200);
            $this->Line(15, $start + 200 + 13, 195, $start + 200 + 13);
            $this->Line(15, $start + 200 + 25, 195, $start + 200 + 25);
            $this->Line(15, $start + 200 + 31, 195, $start + 200 + 31);
            $this->Line(15, $start + 200 + 37, 195, $start + 200 + 37);
            $this->Line(15, $start + 200, 195, $start + 200);
            $total = 30;
            $this->Line(91, $start + 200 + 13, 91, $start + 200);
            $this->Line(37, $start + 200 + 13, 37, $start + 200);
            $this->Line(51, $start, 51, $start + 200);
            $this->Line(62, $start + 200 + 13, 62, $start + 200);
        } else {
            $this->new_page = '1';
            //$this->get_table_params(132);
            //Line(abscissa ,ending point ,abscissa ,starting point)
            $this->Line($chartXPos + 30, $chartYPos, $chartXPos + 30, 120);

            $start = 195;
            if ($this->total <= 8) {
                //Horizontal lines
                $this->Line(15, $start + 13, 195, $start + 13);
                $this->Line(15, $start + 25, 195, $start + 25);
                $this->Line(15, $start + 31, 195, $start + 31);
                $this->Line(15, $start + 39, 195, $start + 39);
                $this->Line(15, $start + 57, 195, $start + 57);
                $this->Line(15, $start, 195, $start);
                $this->Line(91, $start + 13, 91, 195 + $total);
                $this->Line(118, $start + 13, 118, 195 + $total);
                $this->Line(15, 202 + $total + 1, 118, 202 + $total + 1);
                $this->Line(154, 201 + $total + 1, 195, 201 + $total + 1);
                $this->Line(154, 213 + $total + 1, 195, 213 + $total + 1);

                //Vertical lines
                //Line(ending point, ordinate ,starting point, ordinate)
                $this->Line(37, $start + 13, 37, 135);
                $this->Line(51, $start, 51, 135);
                $this->Line(62, $start + 13, 62, 135);
                $this->Line(27, 208 + $total, 27, 135);
                $this->Line(15, 252 + $total, 15, 238);
                $this->Line(195, 252 + $total, 195, 238);
                $this->Line(110, $start, 110, 135);

                $this->Line(125, $start, 125, 135);
                $this->Line(154, $start + 39, 154, 195);
                $this->Line(142, 195, 142, 135);
                $this->Line(160, 195, 160, 135);
                $this->Line(176, $start + 39, 176, 135);

            } else {
                $this->Line(37, 48 + $start, 37, 135);
                $this->Line(51, 48 + $start, 51, 135);
                $this->Line(62, 48 + $start, 62, 135);
                $this->Line(27, 48 + $start, 27, 135);

                $this->Line(111, 48 + $start, 111, 135);
                $this->Line(124, 48 + $start, 124, 135);
                $this->Line(154, 49 + $start, 154, 135);
                $this->Line(176, 49 + $start, 176, 135);

            }
            $this->Line(15, 80, 195, 80);
            $this->Line(15, 120, 195, 120);
            $this->Line(15, 145, 195, 145);
            $this->Line(15, 135, 195, 135);

        }
        $this->Rect(15, 35, 180, 208 + $total, 'D');
        //horizontal lines
        //Line(starting point,abscissa ,ending point ,abscissa)

        //vertical lines

    }

    function init($par, $document_title = 'Commercial Invoice') {
        //function to add page
        $this->AddPage();
        $this->AddFont($this->font_for_document, '', $this->ttf_for_document, true);
        $this->SetFont($this->font_for_document, '', 8 * $this->xfactor);
        $this->xfactor = $par + 0.18;

        $this->fontfactor = 2;

        $this->addTitle($document_title);
    }

    function addTitle($document_title) {

        $this->SetXY(83, 26);
        $this->SetFont($this->font_for_document, '', 5.6 * $this->fontfactor);
        $title = apply_filters('elex_dhl_express_alter_commercial_invoice_title', __($document_title, 'wf-shipping-dhl'));
        $this->Cell(20, 10, __($title, 'wf-shipping-dhl'), 0, 0, 'L');
    }

    function designated_broker($designated_details) {

        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(15, 117);
        $this->Cell(60, 10, __('If there is a designated broker for this shipment, please provide contact information.', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 122);
        $this->Cell(40, 10, __('Name of Broker', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 128);
        $this->Cell(40, 10, __('Duties and Taxes Payable by', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(83, 122);
        $this->Cell(10, 10, __('Tel.No.', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(130, 122);
        $this->Cell(30, 10, __('Contact Name', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(58, 128);
        $this->Cell(30, 10, __('Exporter', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(76, 128);
        $this->Cell(20, 10, __('Consignee', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(96, 128);
        $this->Cell(20, 10, __('Other', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);

        if (($designated_details['dutyaccount_number'] != "") && ($designated_details['dutypayment_type'] == 'T')) {
            $this->SetXY(109, 128);
            $this->Cell(45, 10, __('Duty Account Number: ' . $designated_details['dutyaccount_number'], 'wf-shipping-dhl'), 0, 0, 'L');
            $this->Ln(4);
        } else {
            $this->SetXY(109, 128);
            $this->Cell(45, 10, __('If Other, please specify', 'wf-shipping-dhl'), 0, 0, 'L');
            $this->Ln(4);
        }

        $this->Rect(54, 131, 3.8, 3.4, 'D');
        $this->Rect(72, 131, 3.8, 3.4, 'D');
        $this->Rect(92, 131, 3.8, 3.4, 'D');
        $this->SetFont($this->font_for_document, '', 4.8 * $this->fontfactor);

        $dutypayment_type_horizontal_position = 54;
        if ($designated_details['dutypayment_type'] == 'S') {
            $dutypayment_type_horizontal_position = 54;
        } else if ($designated_details['dutypayment_type'] == 'R') {
            $dutypayment_type_horizontal_position = 72;
        } else if ($designated_details['dutypayment_type'] == 'T') {
            $dutypayment_type_horizontal_position = 92;
        }

        if ($designated_details['dutypayment_type'] != "") {
            $this->SetXY($dutypayment_type_horizontal_position, 128);
            $this->Cell(5, 10, __('X', 'wf-shipping-dhl'), 0, 0, 'L');
            $this->Ln(4);
        }
    }

    function addShippingFromAddress($faddress, $from_address_extra_data) {
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(15, 32);
        $this->Cell(10, 10, __('EXPORTER:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 36);
        $this->Cell(20, 10, __('Contact Name: ', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetXY(34, 36);
        $this->Cell(60, 10, __($faddress['sender_name'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 40);
        $this->Cell(20, 10, __('Telephone No.: ', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetXY(34, 40);
        $this->Cell(20, 10, __($faddress['phone_number'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 44);
        $this->Cell(60, 10, __('Email:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetXY(23, 44);
        $this->Cell(30, 10, $faddress['sender_email'], 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 48);
        $this->Cell(40, 10, __('Company Name/Address:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 52);
        $this->Cell(40, 10, __(html_entity_decode($faddress['sender_company']), 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 56);
        $this->Cell(60, 10, __($faddress['sender_address_line1'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 60);
        $this->Cell(60, 10, __($faddress['sender_address_line2'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 69);
        $this->Cell(60, 10, __($faddress['sender_city'] . '  ' . $faddress['sender_state_code'] . '  ' . $faddress['sender_postalcode'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 73);
        $this->Cell(10, 10, __('Country: ', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetXY(26, 73);
        $this->Cell(60, 10, __(strtoupper($faddress['sender_country']), 'wf-shipping-dhl'), 0, 0, 'L');
        if(isset($from_address_extra_data['vat_number'])){
            $this->SetXY(65, 73);
            $this->Cell(10, 10, __('VAT No: ', 'wf-shipping-dhl'), 0, 0, 'L');
            $this->SetXY(75, 73);
            $this->Cell(60, 10, $from_address_extra_data['vat_number'], 0, 0, 'L');
        }

        $this->SetXY(100, 32);
        $this->Cell(10, 10, __('Invoice Date:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(100, 36);
        $this->Cell(20, 10, __(current_time("Y/m/d"), 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(100, 40);
        $this->Cell(50, 10, __('AirWay bill No./Tracking No.:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(135, 40);
        if (isset($from_address_extra_data['archive_ref']) && !empty($from_address_extra_data['archive_ref'])) {
            $this->Cell(20, 10, $from_address_extra_data['archive_ref']['airway bill number'], 0, 0, 'L');
        } else {
            $this->Cell(20, 10, '', 0, 0, 'L');
        }
        $this->Ln(4);
        $this->SetXY(100, 44);
        $this->Cell(20, 10, __('Invoice No.:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(115, 44);
        $this->Cell(20, 10, $from_address_extra_data['order_number'], 0, 0, 'L');
        $this->Ln(4);
        if(isset($from_address_extra_data['pickup_booking_no'])){
            $this->SetXY(100, 47);
            $this->Cell(20, 10, __('Pickup Booking Number:', 'wf-shipping-dhl'), 0, 0, 'L');
            $this->Ln(4);
            $this->SetXY(130, 47);
            $this->Cell(20, 10, $from_address_extra_data['pickup_booking_no'], 0, 0, 'L');
            $this->Ln(4);
        }
        $this->SetXY(146, 44);
        $this->Cell(20, 10, __('Purchase Order No.:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(170, 44);
        $this->Cell(20, 10, $from_address_extra_data['order_number'], 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(146, 47);
        $this->Cell(20, 10, __('Bill of Lading:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
    }

    function addShippingToAddress($addr) {

        $dhl_tax_id = isset($addr['dhl_tax_id']) ? $addr['dhl_tax_id'] : '';
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(15, 77);
        $this->Cell(20, 10, __('CONSIGNEE:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 81);
        $this->Cell(20, 10, __('Contact Name:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(34, 81);
        $this->Cell(80, 10, __($addr['first_name'] . ' ' . $addr['last_name'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(15, 85);
        $this->Cell(20, 10, __('Telephone No.:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(34, 85);
        $this->Cell(20, 10, __($addr['phone'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(15, 89);
        $this->Cell(60, 10, __('E-Mail:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(25, 89);
        $this->Cell(20, 10, __($addr['email'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 93);
        $this->Cell(20, 10, __('Company Name/Address:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(15, 97);
        $this->Cell(60, 10, __($addr['company'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 101);
        $this->Cell(60, 10, __($addr['address_1'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 104);
        $this->Cell(60, 10, __($addr['address_2'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 109);
        $this->Cell(60, 10, __($addr['city'] . ' ' . $addr['postcode'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(15, 113);
        $this->Cell(60, 10, __('Country: ', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(26, 113);
        $this->Cell(10, 10, __(strtoupper($addr['country']), 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(102, 82);
        $this->Cell(5, 10, __('X', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);

        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(100, 77);
        $this->Cell(60, 10, __('SOLD TO / IMPORTER (if different from Consignee):', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->Rect(102, 85, 3.8, 3.4, 'D');
        $this->SetXY(106, 82);
        $this->Cell(20, 10, __('Same as CONSIGNEE:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(100, 87);
        $this->Cell(10, 10, __('Tax ID#: ' . $dhl_tax_id, 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(100, 93);
        $this->Cell(20, 10, __('Company Name/Address:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(100, 97);
        $this->Cell(60, 10, __($addr['company'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(100, 101);
        $this->Cell(60, 10, __($addr['address_1'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(100, 105);
        $this->Cell(60, 10, __($addr['address_2'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(100, 110);
        $this->Cell(60, 10, __($addr['city'] . ' ' . $addr['postcode'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(100, 113);
        $this->Cell(20, 10, __('Country:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(111, 113);
        $this->Cell(60, 10, __(strtoupper($addr['country']), 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
    }

    function get_table_params($line) {
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(16, $line);
        $this->Cell(10, 10, __('Sl. No.', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->Ln(4);
        $this->SetXY(27, $line);
        $this->Cell(10, 10, __('Product', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->Ln(4);
        $this->SetXY(27, $line + 4);
        $this->Cell(10, 10, __('Quantity', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->SetXY(39, $line);
        $this->Cell(10, 10, __('Unit', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->SetXY(39, $line + 3);
        $this->Cell(10, 10, __('Net Weight', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3 * $this->fontfactor);
        $this->SetXY(39, $line + 6);
        $this->Cell(10, 10, __('(LB/KG)', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(51, $line);
        $this->Cell(10, 10, __('Unit of', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.3 * $this->fontfactor);
        $this->SetXY(51, $line + 4);
        $this->Cell(10, 10, __('Measure', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(82, $line + 2);
        $this->Cell(10, 10, __('Description of Goods', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->SetXY(112, $line + 2);
        $this->Cell(10, 10, __('HS Tariff', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->SetXY(129, $line);
        $this->Cell(10, 10, __('Country of', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.3 * $this->fontfactor);
        $this->SetXY(129, $line + 4);
        $this->Cell(10, 10, __('Manufacture', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(145, $line);
        $this->Cell(10, 10, __('Unit', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->Ln(4);
        $this->SetXY(145, $line + 4);
        $this->Cell(10, 10, __('Value', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->SetXY(162, $line + 2);
        $this->Cell(10, 10, __('Tax', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->Ln(4);
        $this->SetXY(181, $line);
        $this->Cell(10, 10, __('Total', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->Ln(4);
        $this->SetXY(181, $line + 4);
        $this->Cell(10, 10, __('Value', 'wf-shipping-dhl'), 0, 0, 'C');
        $this->Ln(4);
    }

    function addProductDetails($product_details) {
        $this->get_table_params(132);
        $vertical_position = 143;
        $line_horizontal_position = 151;
        $this->package_information = $product_details;
        $i = 0;
        $next_page = true;
        $second_page = true;
        $product_serial_number = 0;
        foreach ($product_details as $key => $product) {
            if(!isset($product['composite_product_component'])){
                $product_serial_number++;
            }
            if ($key > 9 && $next_page) {
                $this->AddPage('', '', 0, 'add');
                $next_page = false;
                $i = 0;
            }

            if ($next_page) {
                $this->table_values($product, 143, 151, $i, $product_serial_number);
                $next_page = false;
            } else {
                $this->table_values($product, 143, 151, $i, $product_serial_number);
            }

            $i = $i + 6;
        }

    }

    function table_values($product, $vertical_position, $line_horizontal_position, $i, $product_serial_number) {
        if(!is_array($product)) return;

        $this->SetXY(16, $vertical_position + $i);
        if(!isset($product['price'])) $product_serial_number = '';
        if(!isset($product['composite_product_component'])){
            $this->Cell(10, 10, __($product_serial_number, 'wf-shipping-dhl'), 0, 0, 'C');
        }

        $this->SetXY(27, $vertical_position + $i);
        if(!isset($product['quantity'])) $product['quantity'] = '';
        if(!isset($product['composite_product_component'])){
            $this->Cell(10, 10, __($product['quantity'], 'wf-shipping-dhl'), 0, 0, 'C');
        }

        $this->SetXY(41, $vertical_position + $i);
        if(!isset($product['weight'])) $product['weight'] = '';
        if(!isset($product['composite_product_component'])){
            $this->Cell(10, 10, __($product['weight'], 'wf-shipping-dhl'), 0, 0, 'R');
        }

        $this->SetXY(52, $vertical_position + $i);
        if(!isset($product['composite_product_component'])){
            $this->Cell(10, 10, __($product['weight_unit'], 'wf-shipping-dhl'), 0, 0, 'C');
        }

        $this->SetXY(62, $vertical_position + $i);
        $this->Cell(10, 10, __(substr($product['description'], 0, 35), 'wf-shipping-dhl'), 0, 0, 'L');
        if (strlen($product['description']) > 35) {
            $i = $i + 4;
            $this->SetXY(62, $vertical_position + $i);
            $this->Cell(10, 10, __(substr($product['description'], 36, 35), 'wf-shipping-dhl'), 0, 0, 'L');
        }
        $this->SetXY(112, $vertical_position + $i);
        $this->Cell(10, 10, __($product['hs'], 'wf-shipping-dhl'), 0, 0, 'C');

        $this->SetXY(129, $vertical_position + $i);
        $this->Cell(10, 10, $product['manufacture'], 0, 0, 'C');

        $this->SetXY(145, $vertical_position + $i);
        if(!isset($product['price'])) $product['price'] = '';
        if(!isset($product['composite_product_component'])){
            $this->Cell(10, 10, round($product['price'], 2), 0, 0, 'R');
        }

        $this->SetXY(162, $vertical_position + $i);
        if(!isset($product['price'])) $product['tax'] = '';
        if(!isset($product['composite_product_component'])){
            $this->Cell(10, 10, round($product['tax'], 2), 0, 0, 'R');
        }

        $this->SetXY(185, $vertical_position + $i);
        if(!isset($product['total'])) $product['total'] = '';
        if(!isset($product['composite_product_component'])){
            $this->Cell(10, 10, round($product['total'], 2), 0, 0, 'R');
        }
    }

    function addPackageDetails($package_details) {
        if ($this->total <= 8) {
            $height = 0;
        } else {
            $height = 41;
        }

        $show_shipping_cost = false;
        if(isset($package_details['shipping_cost']) && !empty($package_details['shipping_cost'])){
            $show_shipping_cost = true;
        }

        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(17, 192 + $height);
        $this->Cell(10, 10, __('Total', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(17, 195 + $height);
        $this->Cell(10, 10, __('Pkgs', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetXY(28, 192 + $height);
        $this->Cell(10, 10, __('Total', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(28, 195 + $height);
        $this->Cell(10, 10, __('Units', 'wf-shipping-dhl'), 0, 0, 'L');

        $this->SetXY(37, 192 + $height);
        $this->Cell(20, 10, __('Total Net', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(38, 195 + $height);
        $this->Cell(10, 10, __('Weight', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetXY(49, 192 + $height);
        $this->Cell(20, 10, __('(Indicate', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(48, 195 + $height);
        $this->Cell(10, 10, __('LB/KG)', 'wf-shipping-dhl'), 0, 0, 'L');

        $this->SetXY(62, 192 + $height);
        $this->Cell(20, 10, __('Total Gross', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(64, 195 + $height);
        $this->Cell(10, 10, __('Weight', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->SetXY(78, 192 + $height);
        $this->Cell(10, 10, __('(Indicate', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(77, 195 + $height);
        $this->Cell(20, 10, __('LB/KG)', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);

        $this->SetXY(92, 192 + $height);
        $this->Cell(20, 10, __('Shipment Insurance', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(100, 195 + $height);
        $this->Cell(10, 10, __('('.$package_details['currency'].')', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);

        $this->SetXY(16, 200 + $height);
        $this->Cell(10, 10, $package_details['total_package'], 0, 0, 'C');
        $this->SetXY(27, 200 + $height);
        $this->Cell(10, 10, $package_details['total_unit'], 0, 0, 'C');
        $this->SetXY(41, 200 + $height);
        $this->Cell(10, 10, $package_details['net_weight'], 0, 0, 'R');
        $this->SetXY(49, 200 + $height);
        $this->Cell(10, 10, $package_details['weight_unit'], 0, 0, 'C');
        $this->SetXY(69, 200 + $height);
        $this->Cell(10, 10, $package_details['gross_weight'], 0, 0, 'C');
        $this->SetXY(75, 200 + $height);
        $this->Cell(10, 10, $package_details['weight_unit'], 0, 0, 'C');
        $this->SetXY(100, 200 + $height);
        $this->Cell(10, 10, $package_details['insurance'], 0, 0, 'C');

        $this->SetFont($this->font_for_document, '', 3.3 * $this->fontfactor);
        $this->SetXY(15, 205 + $height);
        $this->Cell(10, 10, __('Declaration Statement(s):', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 217 + $height);
        $this->Cell(10, 1, __('I declare that all the information contained in this invoice to be true and correct.', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 222 + $height);
        $this->Cell(10, 1, __("Shipment Contents: ".$package_details['shipment_content'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(15, 223 + $height);
        $this->Cell(10, 10, __('Originator or Name of Company Representative if the invoice is being completed on behalf of a company or individual:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
        $this->SetXY(15, 227 + 25 + 7);
        $this->Cell(10, 10, __($package_details['originator'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        if ($height <= 0) {
            $this->SetXY(15, 232);
        } else {
            $this->SetXY(15, 232 + 15);
        }
        $this->Cell(10, 10, __('Comments: '.$package_details['shipment_comments'], 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);

        if ($height <= 0) {
            $this->SetXY(15, 244);
        } else {
            $this->SetXY(15, 244 + 15);
        }
        $this->Cell(10, 10, __('Signature / Title / Date:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);

        $this->SetXY(154, 194 + $height);
        $this->Cell(10, 10, __('Subtotal:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        if($show_shipping_cost){
            $this->SetXY(154, 200 + $height);
            $this->Cell(10, 10, __('Shipping Cost:', 'wf-shipping-dhl'), 0, 0, 'L');
            $this->Ln(4);
        }
        $this->SetXY(154, 206 + $height);
        $this->Cell(10, 10, __('Shipping Tax:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(154, 212 + $height);
        $this->Cell(10, 10, __('Discount:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(154, 218 + $height);
        $this->Cell(10, 10, __('Invoice Total:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);
        $this->SetXY(154, 224 + $height);
        $this->Cell(15, 15, __('Currency Code:', 'wf-shipping-dhl'), 0, 0, 'L');
        $this->Ln(4);

        $this->SetXY(185, 194 + $height);
        $this->Cell(10, 10, $package_details['value'], 0, 0, 'R');
        $this->Ln(4);
        if($show_shipping_cost){
            $this->SetXY(185, 200 + $height);
            $this->Cell(10, 10, $package_details['shipping_cost'], 0, 0, 'R');
            $this->Ln(4);
        }
        $this->SetXY(185, 206 + $height);
        $this->Cell(10, 10, $package_details['tax'], 0, 0, 'R');
        $this->Ln(4);
        $this->SetXY(185, 212 + $height);
        $this->Cell(10, 10, $package_details['discount'], 0, 0, 'R');
        $this->Ln(4);
        $this->SetXY(185, 218 + $height);
        $this->Cell(10, 10, $package_details['total'], 0, 0, 'R');
        $this->Ln(4);
        $this->SetXY(178, 224 + $height);
        $this->Cell(15, 15, $package_details['currency'], 0, 0, 'C');
        $this->Ln(4);
    }

    function addExtraDetails($extras) {
        $trade_vertical_position = 51;
        foreach ($extras as $key => $value) {
            $this->SetFont($this->font_for_document, '', 3.5 * $this->fontfactor);
            $this->SetXY(100, $trade_vertical_position);

            $this->Cell(10, 10, __($key . ':', 'wf-shipping-dhl'), 0, 0, 'L');
            
            if($key == 'Terms Of Trade'){
                $this->SetXY(120, $trade_vertical_position);    
            }else if($key == 'Shipment Estimated Delivery Date'){
                $this->SetXY(140, $trade_vertical_position);    
            }else{
                $this->SetXY(127, $trade_vertical_position);
            }
            $this->Cell(10, 10, __($value, 'wf-shipping-dhl'), 0, 0, 'L');
            $this->Ln(4);
            $trade_vertical_position = $trade_vertical_position + 4;
        }
    }
}
?>
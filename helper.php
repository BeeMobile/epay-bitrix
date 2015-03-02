<?php

$approve_method = CSalePaySystemAction::GetParamValue("APPROVE_METHOD");


function getKkbHelper()
{
    require_once( 'KkbHelper.php');

    $testmode = CSalePaySystemAction::GetParamValue("TESTMODE");
    $merchant_id = CSalePaySystemAction::GetParamValue("MERCHANT_ID");
    $merchant_name = CSalePaySystemAction::GetParamValue("MERCHANT_NAME");
    $merchant_certificate_id = CSalePaySystemAction::GetParamValue("MERCHANT_CERTIFICATE_ID");
    $private_key_path = CSalePaySystemAction::GetParamValue("PRIVATE_KEY_PATH");
    $private_key_pass = CSalePaySystemAction::GetParamValue("PRIVATE_KEY_PASS");
    $public_key_path = 'kkbca.pem' ;
    $url = 'https://epay.kkb.kz/jsp/process/logon.jsp';

    $response_url = '/personal/order/payment/result_kkb.php';

    // Setup the test data, if in test mode.
    if ( $testmode == 'Y' ) {
        $url = 'http://3dsecure.kkb.kz/jsp/process/logon.jsp';

        $merchant_id = '92061101';
        $merchant_name = 'Test shop';
        $merchant_certificate_id = '00C182B189';
        $private_key_path = 'test_prv.pem';
        $private_key_pass = 'nissan';

    }

    $dataDirectory = getDataDirectory();

    $helper = new KkbHelper($merchant_certificate_id, $merchant_name, $merchant_id, $dataDirectory . $private_key_path, $private_key_pass, $dataDirectory . $public_key_path, $url);
    return $helper;
}

function getDataDirectory(){
    return $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_payment/kkb/data/";
}

function approvePayment($result, $order)
{
    $helper = getKkbHelper();
    $xml = $helper->process_complete($result['PAYMENT_REFERENCE'], $result['PAYMENT_APPROVAL_CODE'], (int)$order["ID"], 398, $result['PAYMENT_AMOUNT']);

    $url = 'http://3dsecure.kkb.kz/jsp/remote/control.jsp';
    $message = "_sendRequest: \n url: $url \n xml: $xml \n";
    kkb_log($message);

    $urlFull = $url . '?'. urlencode($xml);

    $response = $helper->request($urlFull);

    kkb_log(trim($response) . "\n");

    if (strpos(strtolower($response), 'error'))
    {
        $success =  0;
    } else {
        $success = 1;
    }


    return $success;
}

function refundPayment($result, $order)
{
    $helper = getKkbHelper();
    $xml = $helper->process_refund($result['PAYMENT_REFERENCE'], $result['PAYMENT_APPROVAL_CODE'], (int)$order["ID"], 398, $result['PAYMENT_AMOUNT'], '');

    $url = 'http://3dsecure.kkb.kz/jsp/remote/control.jsp';

    $message = "_sendRequest: \n url: $url \n xml: $xml \n";
    kkb_log($message);

    $urlFull = $url . '?'. urlencode($xml);

    $response = $helper->request($urlFull);

    kkb_log(trim($response) . "\n");

    if (strpos(strtolower($response), 'error'))
    {
        $success =  0;
    } else {
        $success = 1;
    }


    return $success;
}

function kkb_log( $message, $close = false ) {
    $logging = CSalePaySystemAction::GetParamValue("LOG");

    if ( $logging != 'Y' ) { return; }

    static $fh = 0;

    if( $close ) {
        @fclose( $fh );
    } else {
        // If file doesn't exist, create it
        if( !$fh ) {
            $pathinfo = pathinfo( __FILE__ );
            $dir = $pathinfo['dirname'];
            $fh = @fopen( __DIR__ .'/kkb.log', 'a' );
        }

        // If file was successfully created
        if( $fh ) {
            $line = date('d.m.Y H:i:s') . "\n";
            $line .= $message ."\n";

            fwrite( $fh, $line );
        }
    }
}
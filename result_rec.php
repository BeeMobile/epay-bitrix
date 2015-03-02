<?//if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
//if ($_SERVER["REQUEST_METHOD"] !== "POST") die();
ini_set( "display_errors", true );
error_reporting( E_ALL );

if(!require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php")) die('prolog_before.php not found!');
if (CModule::IncludeModule('sale')) {

	$orderId = (int)$_GET['order_id'];
	if (!($order = CSaleOrder::GetByID($orderId)))
		die();
	if ($order["PAYED"] == "Y")
		die();
	CSalePaySystemAction::InitParamArrays($order, $order["ID"]);
	require_once('helper.php');

	$result = 0;
	if (isset($_POST["response"])) {
		$response = $_POST["response"];
	} else {
		echo 'error';
		exit();
	}

	$helper = getKkbHelper();
	$result = $helper->process_response(stripslashes($response));
	$message = '_receivedNotification: response:' . $_POST["response"] . "\n";
	kkb_log($message);


	$err = false;
	$err_message = date('d.m.Y H:i:s') . ' ';
	if (is_array($result)) {
		if (in_array("ERROR", $result)) {
			$err = true;
			if ($result["ERROR_TYPE"] == "ERROR") {
				$err_message .= "System error:" . $result["ERROR"] . "\n";
			} elseif ($result["ERROR_TYPE"] == "system") {
				$err_message .= "Bank system error > Code: '" . $result["ERROR_CODE"] . "' Text: '" . $result["ERROR_CHARDATA"] . "' Time: '" . $result["ERROR_TIME"] . "' Order_ID: '" . $result["RESPONSE_ORDER_ID"] . "'";
			} elseif ($result["ERROR_TYPE"] == "auth") {
				$err_message .= "Bank system user autentication error > Code: '" . $result["ERROR_CODE"] . "' Text: '" . $result["ERROR_CHARDATA"] . "' Time: '" . $result["ERROR_TIME"] . "' Order_ID: '" . $result["RESPONSE_ORDER_ID"] . "'";
			};
		};
		if (in_array("DOCUMENT", $result)) {
			$order_id = ltrim($result['ORDER_ORDER_ID'], '0');
		};
	} else {
		$err = true;
		$err_message .= "System error: " . $result;
	};

	if (!isset($order_id)) {
		echo 'error';
		exit();
	}

	if ($order_id != $orderId)
		die();

	//CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);
	//$merchant_id = CSalePaySystemAction::GetParamValue("MERCHANT_ID");
	//$signature = CSalePaySystemAction::GetParamValue("SIGN");


	if ($result) {
		//update_post_meta( $order->id, 'kkb_fullresponse', wp_slash(json_encode($result)) );
	}

	if ($err) {
		$status = 'cancel';
	} else {

		if ($approve_method == 'automatic') {

			$success = approvePayment($result, $order);

			if ($success) {
				$status = 'success';
			} else {
				$status = 'pending';
			}


		} else {
			$status = 'pending';
		}
	}

	$allowed = array('PAYMENT_REFERENCE', 'PAYMENT_APPROVAL_CODE', 'PAYMENT_AMOUNT');
	$psMessage = array_intersect_key($result, array_flip($allowed));

	$arFields = array(
		"PS_STATUS_MESSAGE" => json_encode($psMessage),
		"PS_SUM" => $result['PAYMENT_AMOUNT'],
		"PS_CURRENCY" => 'KZT',
		"PS_RESPONSE_DATE" => date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
	);

	include(GetLangFileName(dirname(__FILE__)."/", "/kkb.php"));

	switch (strtolower($status)) {
		case 'success' :

			$arFields["PS_STATUS"] = "Y";
			$arFields["STATUS_ID"] = "P";
			$arFields["PS_STATUS_CODE"] = '2';
			$arFields["PS_STATUS_DESCRIPTION"] = GetMessage("KKB_PAYMENT_APPROVED_MESSAGE");


			CSaleOrder::PayOrder($order["ID"], "Y", false);
			CSaleOrder::Update($order["ID"], $arFields);
			//CSaleOrder::PayOrder($order["ID"], "Y");
			break;
		case 'pending' :

			$arFields["PS_STATUS"] = "Y";
			$arFields["STATUS_ID"] = "P";
			$arFields["PS_STATUS_CODE"] = '1';
			$arFields["PS_STATUS_DESCRIPTION"] = GetMessage("KKB_PAYMENT_HOLDED_MESSAGE");


			CSaleOrder::PayOrder($order["ID"], "Y", false);
			CSaleOrder::Update($order["ID"], $arFields);
			break;
		default:
			$arFields["PS_STATUS_CODE"] = '0';
			$arFields["PS_STATUS_DESCRIPTION"] = 'Error';


			CSaleOrder::Update($order["ID"], $arFields);//$order->update_status( 'failed', __('Payment error via kkb.', 'woocommerce-gateway-kkb' ) );
			break;
	}

	exit;
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

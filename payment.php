<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
include(GetLangFileName(dirname(__FILE__)."/", "/kkb.php"));


require_once( 'helper.php');

$currency_id = '398';
$order_id = CSalePaySystemAction::GetParamValue("ORDER_ID");
$shouldPay = (strlen(CSalePaySystemAction::GetParamValue("SHOULD_PAY")) > 0) ? CSalePaySystemAction::GetParamValue("SHOULD_PAY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
$currency = (strlen(CSalePaySystemAction::GetParamValue("CURRENCY")) > 0) ? CSalePaySystemAction::GetParamValue("CURRENCY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"];
if ($currency != "KZT")
{
	$shouldPay = CCurrencyRates::ConvertCurrency($shouldPay, $currency, "KZT");
}

$helper = getKkbHelper();
$content = $helper->process_request($order_id, $currency_id, $shouldPay, false);
// Construct variables for post
$data_to_send = array(
	'Signed_Order_B64' => base64_encode($content),
	'Language' => 'rus',

	'BackLink' =>  "http://" . $_SERVER["HTTP_HOST"] . "/personal/order/",
	'FailureBackLink' => "http://" . $_SERVER["HTTP_HOST"] . "/personal/order/",
	'PostLink' => "http://" . $_SERVER["HTTP_HOST"] . "/bitrix/tools/result_kkb.php?order_id=" . $order_id,
	//'PostLink' => "http://7243f9e1.ngrok.com/bitrix/tools/result_kkb.php?order_id=" . $order_id,

	//'email' => $order->billing_email,

);


$kkb_args_array = array();
$message = "Kkb request: \n";
foreach ($data_to_send as $key => $value) {
	$message .=  sprintf( "%s: %s\n" , $key , $value );
}
kkb_log($message);
?>
<?=GetMessage("PAYMENT_DESCRIPTION_PS")?> <b>epay.kkb.kz</b>.<br /><br />
<?=GetMessage("PAYMENT_DESCRIPTION_SUM")?>: <b><?=CurrencyFormat($shouldPay, 'KZT')?></b><br /><br />

<form action="<?= $helper->getActionUrl()?>" method="post">
	<?foreach ($data_to_send as $key => $value):?>
		<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
	<?endforeach;?>
	<input type="submit" value="<?= GetMessage("PAYMENT_PAY")?>" />
</form>

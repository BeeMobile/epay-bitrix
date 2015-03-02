<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/kkb.php"));

require_once( 'helper.php');

$psTitle = "Epay.kkb.kz";
$psDescription = "";

$data_dir = getDataDirectory();

//var_dump($data_dir);
$files = glob($data_dir . "*.*");//var_dump($files);exit();
$options = array();
foreach($files as $file)
{
	$fileName = str_replace($data_dir, '', $file);
	$options[$fileName] = array("NAME" => $fileName);
}

$arPSCorrespondence = array(
	"TESTMODE" => array(
		"NAME" => GetMessage("KKB_TESTMODE"),
		"DESCR" => GetMessage("KKB_TESTMODE_DESC"),
		"VALUE" => array('Y' => array('NAME' => GetMessage("KKB_YES")), 'N' => array('NAME' => GetMessage("KKB_NO"))),
		"TYPE" => "SELECT"
	),
	"MERCHANT_ID" => array(
		"NAME" => GetMessage("KKB_MERCHANT_ID"),
		"DESCR" => GetMessage("KKB_MERCHANT_ID_DESC"),
		"VALUE" => '',
		"TYPE" => ""
	),
	"MERCHANT_NAME" => array(
		"NAME" => GetMessage("KKB_MERCHANT_NAME"),
		"DESCR" => GetMessage("KKB_MERCHANT_NAME_DESC"),
		"VALUE" => '',
		"TYPE" => ""
	),
	"MERCHANT_CERTIFICATE_ID" => array(
		"NAME" => GetMessage("KKB_MERCHANT_CERTIFICATE_ID"),
		"DESCR" => GetMessage("KKB_MERCHANT_CERTIFICATE_ID_DESC"),
		"VALUE" => '',
		"TYPE" => ""
	),
	"PRIVATE_KEY_PATH" => array(
		"NAME" => GetMessage("KKB_PRIVATE_KEY_PATH"),
		"DESCR" => GetMessage("KKB_PRIVATE_KEY_PATH_DESC"),
		"VALUE" => $options,
		"TYPE" => "SELECT"
	),
	"PRIVATE_KEY_PASS" => array(
		"NAME" => GetMessage("KKB_PRIVATE_KEY_PASS"),
		"DESCR" => GetMessage("KKB_PRIVATE_KEY_PASS_DESC"),
		"VALUE" => '',
		"TYPE" => ""
	),
	"APPROVE_METHOD" => array(
		"NAME" => GetMessage("KKB_APPROVE_METHOD"),
		"DESCR" => GetMessage("KKB_APPROVE_METHOD_DESC"),
		"VALUE" => array('automatic' => array('NAME' => GetMessage("KKB_APPROVE_METHOD_AUTOMATIC")), 'manual' => array('NAME' => GetMessage("KKB_APPROVE_METHOD_MANUAL"))),
		"TYPE" => "SELECT"
	),

	"LOG" => array(
		"NAME" => GetMessage("KKB_LOG"),
		"DESCR" => GetMessage("KKB_LOG_DESC"),
		"VALUE" => array('Y' => array('NAME' => GetMessage("KKB_YES")), 'N' => array('NAME' => GetMessage("KKB_NO"))),
		"TYPE" => "SELECT"
	),

	"ORDER_ID" => array(
			"NAME" => GetMessage("KKB_ORDER_ID"),
			"DESCR" => "",
			"VALUE" => "ID",
			"TYPE" => "ORDER"
		),

	"BUYER_EMAIL" => array(
		"NAME" => GetMessage("KKB_EMAIL"),
		"DESCR" => GetMessage("KKB_EMAIL_DESC"),
		"VALUE" => "EMAIL",
		"TYPE" => "PROPERTY"
	)


	);
?>

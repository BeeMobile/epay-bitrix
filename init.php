<?php
AddEventHandler('sale', "OnSaleStatusOrder", "KkbOnSaleStatusOrder");
AddEventHandler("sale", "OnSalePayOrder", "KkbOnSalePayOrder");

function KkbOnSaleStatusOrder($order_id, $status)
{

    if (!($order = CSaleOrder::GetByID($order_id)))
        return false;
    if ($order["PAYED"] == "N")
        return false;

    if ($status != "F")
        return false;


    CSalePaySystemAction::InitParamArrays($order, $order["ID"]);

    if (!empty($order["PS_STATUS_MESSAGE"]))
    {

        $result = (array)json_decode($order["PS_STATUS_MESSAGE"]);
        if (isset($result['PAYMENT_REFERENCE']) && $result['PAYMENT_APPROVAL_CODE'])
        {
            if ($order["PS_STATUS_CODE"] == '1')
            {
                require_once('helper.php');

                $success = approvePayment($result, $order);
                if ($success) {
                    include(GetLangFileName(dirname(__FILE__)."/", "/kkb.php"));

                    $arFields = array();
                    $arFields["PS_STATUS_CODE"] = '2';
                    $arFields["PS_STATUS_DESCRIPTION"] = GetMessage("KKB_PAYMENT_APPROVED_MESSAGE");


                    CSaleOrder::Update($order["ID"], $arFields);
                }
            }

        }

    }
}

function KkbOnSalePayOrder($order_id, $status)
{
    if ($status == 'N')
    {
        if (!($order = CSaleOrder::GetByID($order_id)))
            return false;

        CSalePaySystemAction::InitParamArrays($order, $order["ID"]);

        if (!empty($order["PS_STATUS_MESSAGE"]))
        {
            $result = (array)json_decode($order["PS_STATUS_MESSAGE"]);
            if (isset($result['PAYMENT_REFERENCE']) && $result['PAYMENT_APPROVAL_CODE'])
            {
                if ($order["PS_STATUS_CODE"] == '1')
                {
                    require_once('helper.php');
                    $success = refundPayment($result, $order);
                    if ($success)
                    {
                        include(GetLangFileName(dirname(__FILE__)."/", "/kkb.php"));
                        $arFields = array();
                        $arFields["PS_STATUS_CODE"] = '3';
                        $arFields["PS_STATUS_DESCRIPTION"] = GetMessage("KKB_PAYMENT_REFUNDED_MESSAGE");


                        CSaleOrder::Update($order["ID"], $arFields);
                    }
                }

            }

        }

    }

}


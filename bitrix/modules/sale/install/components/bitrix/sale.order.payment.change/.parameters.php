<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"PATH_TO_PAYMENT" => array(
			"NAME" => GetMessage("SOPC_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/order/payment",
			"COLS" => 25,
		),
		"SET_TITLE" => array()
	)
);

$paySystemList = array(GetMessage("SAPP_SHOW_ALL"));

$paySystemManagerResult = Bitrix\Sale\PaySystem\Manager::getList(array('select' => array('ID','NAME')));

while ($paySystem = $paySystemManagerResult->fetch())
{
	if (!empty($paySystem['NAME']))
	{
		$paySystemList[$paySystem['ID']] = $paySystem['NAME'].' ['.$paySystem['ID'].']';
	}
}

if (isset($paySystemList))
{
	$arComponentParameters['PARAMETERS']['ELIMINATED_PAY_SYSTEMS'] = array(
		"NAME"=>GetMessage("SOPC_ELIMINATED_PAY_SYSTEMS"),
		"TYPE"=>"LIST",
		"MULTIPLE"=>"Y",
		"DEFAULT" => "0",
		"VALUES"=>$paySystemList,
		"SIZE" => 6,
		"COLS"=>25,
		"ADDITIONAL_VALUES"=>"N",
	);
}



<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFramemode(false);

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

global $APPLICATION;

$APPLICATION->RestartBuffer();

$bUseAccountNumber = (COption::GetOptionString("sale", "account_number_template", "") !== "") ? true : false;

$ORDER_ID = urldecode(urldecode($_REQUEST["ORDER_ID"]));
$paymentId = isset($_REQUEST["PAYMENT_ID"]) ? $_REQUEST["PAYMENT_ID"] : '';

$arOrder = false;
if ($bUseAccountNumber)
{
	$dbOrder = CSaleOrder::GetList(
		array("DATE_UPDATE" => "DESC"),
		array(
			"LID" => SITE_ID,
			"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
			"ACCOUNT_NUMBER" => $ORDER_ID
		)
	);

	$arOrder = $dbOrder->GetNext();
}

if (!$arOrder)
{
	$dbOrder = CSaleOrder::GetList(
		array("DATE_UPDATE" => "DESC"),
		array(
			"LID" => SITE_ID,
			"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
			"ID" => $ORDER_ID
		)
	);

	$arOrder = $dbOrder->GetNext();
}

if ($arOrder)
{
	$paymentItem = null;

	/** @var \Bitrix\Sale\Order $order */
	$order = \Bitrix\Sale\Order::load($arOrder['ID']);

	if ($order)
	{
		/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $order->getPaymentCollection();

		if ($paymentCollection)
		{
			if ($paymentId)
			{
				$params = array(
					'select' => array('ID'),
					'filter' => array(
						'LOGIC' => 'OR',
						'ID' => $paymentId,
						'ACCOUNT_NUMBER' => $paymentId,
					)
				);

				$data = \Bitrix\Sale\Internals\PaymentTable::getRow($params);

				if ($data !== null && $data['ID'] > 0)
				{
					/** @var \Bitrix\Sale\Payment $paymentItem */
					$paymentItem = $paymentCollection->getItemById($data['ID']);
				}
			}

			if ($paymentItem === null)
			{
				/** @var \Bitrix\Sale\Payment $item */
				foreach ($paymentCollection as $item)
				{
					if (!$item->isInner() && !$item->isPaid())
					{
						$paymentItem = $item;
						break;
					}
				}
			}

			if ($paymentItem !== null)
			{
				$service = \Bitrix\Sale\PaySystem\Manager::getObjectById($paymentItem->getPaymentSystemId());
				if ($service)
				{
					$context = \Bitrix\Main\Application::getInstance()->getContext();

					$service->initiatePay($paymentItem, $context->getRequest());

					if($service->getField('ENCODING') != '')
					{
						define("BX_SALE_ENCODING", $service->getField('ENCODING'));

						AddEventHandler("main", "OnEndBufferContent", "ChangeEncoding");
						function ChangeEncoding($content)
						{
							global $APPLICATION;
							header("Content-Type: text/html; charset=".BX_SALE_ENCODING);
							$content = $APPLICATION->ConvertCharset($content, SITE_CHARSET, BX_SALE_ENCODING);
							$content = str_replace("charset=".SITE_CHARSET, "charset=".BX_SALE_ENCODING, $content);
						}
					}
				}
			}
		}
	}
}
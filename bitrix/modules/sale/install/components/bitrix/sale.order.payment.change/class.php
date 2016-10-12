<?php

use Bitrix\Main,
	Bitrix\Sale,
	Bitrix\Currency,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Sale\PaySystem,
	Bitrix\Main\Application;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);


/**
 * Class SaleAccountPay
 */
class SaleOrderPaymentChange extends \CBitrixComponent
{
	/** @var  Main\ErrorCollection $errorCollection*/
	protected $errorCollection;

	/** @var \Bitrix\Sale\Order $order */
	protected $order = null;
	
	/**
	 * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
	 * @param mixed[] $params List of unchecked parameters
	 * @return mixed[] Checked and valid parameters
	 */
	public function onPrepareComponentParams($params)
	{
		$this->errorCollection = new Main\ErrorCollection();

		if (!isset($params["ELIMINATED_PAY_SYSTEMS"]) && !is_array($params["ELIMINATED_PAY_SYSTEMS"]))
		{
			$params["ELIMINATED_PAY_SYSTEMS"] = array();
		}

		$params['NAME_CONFIRM_TEMPLATE'] = 'confirm_template';

		$params["TEMPLATE_PATH"] = $this->getTemplateName();

		if (empty($params['NAME_CONFIRM_TEMPLATE']))
		{
			$params['NAME_CONFIRM_TEMPLATE'] = "confirm_template";
		}

		if (empty($params['ACCOUNT_NUMBER']))
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage("SOPC_ERROR_ORDER_NOT_EXISTS")));
		}

		if (empty($params['PAYMENT_NUMBER']))
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage("SOPC_ERROR_PAYMENT_NOT_EXISTS")));
		}

		if (strlen($params["PATH_TO_PAYMENT"]) <= 0)
		{
			$params["PATH_TO_PAYMENT"] = "/personal/order/payment";
		}
		else
		{
			$params["PATH_TO_PAYMENT"] = trim($params["PATH_TO_PAYMENT"]);
		}

		return $params;
	}

	/**
	 * Check Required Modules
	 * @throws Main\SystemException
	 * @return bool
	 */
	protected function checkModules()
	{
		if (!Loader::includeModule('sale'))
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage('SOPC_MODULE_NOT_INSTALL')));
			return false;
		}
		return true;
	}

	/**
	 * Prepare data to render in new version of component.
	 * @return void
	 */
	protected function buildPaySystemsList()
	{
		global $USER;

		if (!$USER->IsAuthorized())
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage('SALE_ACCESS_DENIED')));
			return;
		}

		$paymentCollection = $this->order->getPaymentCollection();
		/** @var \Bitrix\Sale\Payment $payment */
		$payment = $paymentCollection->createItem();
		
		$paySystemList = PaySystem\Manager::getListWithRestrictions($payment);

		foreach ($paySystemList as $paySystemElement)
		{
			if (!empty($paySystemElement['PAY_SYSTEM_ID']) && !in_array($paySystemElement['ID'], $this->arParams['ELIMINATED_PAY_SYSTEMS']))
			{
				if (!empty($paySystemElement["LOGOTIP"]))
				{
					$paySystemElement["LOGOTIP"] = CFile::GetFileArray($paySystemElement['LOGOTIP']);
					$fileTemp = CFile::ResizeImageGet(
						$paySystemElement["LOGOTIP"]["ID"],
						array("width" => "95", "height" =>"55"),
						BX_RESIZE_IMAGE_PROPORTIONAL,
						true
					);
					$paySystemElement["LOGOTIP"] = $fileTemp["src"];
				}

				$this->arResult['PAYSYSTEMS_LIST'][] = $paySystemElement;
			}
		}

		if (empty($this->arResult['PAYSYSTEMS_LIST']))
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage("SOPC_EMPTY_PAY_SYSTEM_LIST")));
		}
	}

	/**
	 * Function implements all the life cycle of our component
	 * @return void
	 */
	public function executeComponent()
	{
		global $APPLICATION;
		$templateName = null;

		if ($this->checkModules() && $this->errorCollection->isEmpty())
		{
			if ($this->arParams["SET_TITLE"] !== "N")
			{
				$APPLICATION->SetTitle(Loc::getMessage('SOPC_TITLE'));
			}

			if (strlen($this->arParams['ACCOUNT_NUMBER']))
			{
				$this->order = Sale\Order::loadByAccountNumber($this->arParams['ACCOUNT_NUMBER']);

				$paymentList = Sale\Payment::getList(
					array(
						"filter" => array("ACCOUNT_NUMBER" => $this->arParams['PAYMENT_NUMBER']),
						"select" => array('*')
					)
				);

				$this->arResult['PAYMENT'] = $paymentList->fetch();

			}

			if ($this->order)
			{
				if ($this->arParams['AJAX_DISPLAY'] === 'Y')
				{
					$this->orderPayment();
					$templateName = $this->arParams['NAME_CONFIRM_TEMPLATE'];
				}
				else
				{
					$this->buildPaySystemsList();
					if ($this->errorCollection->isEmpty())
					{
						$signer = new Main\Security\Sign\Signer;
						$this->arResult['$signedParams'] = $signer->sign(base64_encode(serialize($this->arParams)), 'sale.order.payment.change');
					}
				}
			}
			else
			{
				$this->errorCollection->setError(new Main\Error(Loc::getMessage('SOPC_ERROR_ORDER_NOT_EXISTS')));
			}
		}

		$this->formatResultErrors();
		$this->includeComponentTemplate($templateName);
	}

	/**
	 * Move all errors to $this->arResult, if there were any
	 * @return void
	 */
	protected function formatResultErrors()
	{
		if (!$this->errorCollection->isEmpty())
		{
			/** @var Main\Error $error */
			foreach ($this->errorCollection->toArray() as $error)
			{
				$this->arResult['errorMessage'][] = $error->getMessage();
			}
		}
	}

	/**
	 * Ordering payment for calling in ajax callback
	 * @return void
	 */
	protected function orderPayment()
	{
		global $USER;

		if (!$USER->IsAuthorized())
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage('SALE_ACCESS_DENIED')));
			return;
		}

		$paySystemObject  = PaySystem\Manager::getObjectById((int)$this->arParams['NEW_PAY_SYSTEM_ID']);
		if (empty($paySystemObject))
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage('SOPC_ERROR_ORDER_PAYMENT_SYSTEM')));
			return;
		}

		/** @var \Bitrix\Sale\Payment $payment */
		$paymentCollection = $this->order->getPaymentCollection();
		$payment = $paymentCollection->getItemById($this->arResult['PAYMENT']['ID']);
		$paymentResult = $payment->setFields(array(
				'PAY_SYSTEM_ID' => $paySystemObject->getField('ID'),
				'PAY_SYSTEM_NAME' => $paySystemObject->getField('NAME')
			)
		);

		if (!$paymentResult->isSuccess())
		{
			$this->errorCollection->add($paymentResult->getErrors());
			return;
		}

		$resultSaving = $this->order->save();

		if ($resultSaving->isSuccess())
		{

			$paySystemBufferedOutput = $paySystemObject->initiatePay($payment, null, PaySystem\BaseServiceHandler::STRING);

			if ($paySystemBufferedOutput->isSuccess())
			{
				$values = $paySystemObject->getFieldsValues();
				$this->arResult = array(
					"ORDER_ID"=>$this->order->getField("ACCOUNT_NUMBER"),
					"ORDER_DATE"=>$this->order->getDateInsert()->toString(),
					"PAYMENT_ID"=>$payment->getField("ACCOUNT_NUMBER"),
					"PAY_SYSTEM_NAME"=>$payment->getField("PAY_SYSTEM_NAME"),
					"TEMPLATE"=>$paySystemBufferedOutput->getTemplate(),
					"IS_CASH" => $paySystemObject->isCash(),
					"NAME_CONFIRM_TEMPLATE"=>$this->arParams['NAME_CONFIRM_TEMPLATE']
				);

				if ($values['NEW_WINDOW'] === 'Y')
				{
					$this->arResult["PAYMENT_LINK"] = $this->arParams['PATH_TO_PAYMENT']."/?ORDER_ID=".$this->order->getField("ACCOUNT_NUMBER")."&PAYMENT_ID=".$payment->getId();
				}
			}
			else
			{
				$this->errorCollection->add($paySystemBufferedOutput->getErrors());
			}
		}
		else
		{
			$this->errorCollection->add($resultSaving->getErrors());
		}
	}
}
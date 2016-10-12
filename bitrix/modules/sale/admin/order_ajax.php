<?
namespace Bitrix\Sale\AdminPage;

/**
 * Bitrix Framework
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web;
use Bitrix\Sale;
use Bitrix\Sale\Result;
use Bitrix\Sale\Provider;
use Bitrix\Sale\Helpers\Admin;
use Bitrix\Main\SystemException;
use Bitrix\Main\Entity\EntityError;
use Bitrix\Sale\UserMessageException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\Services\Company;

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);

global $USER;
$arResult = array();
$result = new \Bitrix\Main\Entity\Result();
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if(!isset($_REQUEST["action"]))
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED")));
	$result->setData(array("SYSTEM_ERROR" => "REQUEST[action] not defined!"));
}
elseif($saleModulePermissions == "D" || !check_bitrix_sessid())
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED2")));
	$result->setData(array("SYSTEM_ERROR" => "Access denied!"));
}
elseif(!\Bitrix\Main\Loader::includeModule('sale'))
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED")));
	$result->setData(array("SYSTEM_ERROR" => "Error! Can't include module \"Sale\"!"));
}
else
{
	$processor = new AjaxProcessor($USER->GetID(), $_REQUEST);
	$result = $processor->processRequest();
}

if($result->isSuccess())
{
	$arResult["RESULT"] = "OK";
}
else
{
	$arResult["RESULT"] = "ERROR";
	$arResult["ERROR"] = implode("\n", $result->getErrorMessages());
	$arResult["ERRORS"] = array();

	foreach($result->getErrorMessages() as $error)
		$arResult["ERRORS"][] = $error;
}

$data = $result->getData();

if(is_array($data))
	$arResult = array_merge($arResult, $result->getData());

$arResult = AjaxProcessor::convertEncodingArray($arResult, SITE_CHARSET, 'UTF-8');

Header('Content-Type: application/json');

echo json_encode($arResult);
\CMain::FinalActions();
die();

/**
 * Class AjaxProcessor
 * @package Bitrix\Sale\AdminPage
 * Class helper for processing ajax requests
 */
class AjaxProcessor
{
	protected $userId;
	/** @var \Bitrix\Sale\Result $result*/
	protected $result;
	protected $request;
	/** @var \Bitrix\Sale\Order $order  */
	protected $order = null;
	protected $formDataChanged = false;

	public function __construct($userId, array $request)
	{
		$this->userId = $userId;
		$this->result = new Result();
		$this->request = $request;
	}

	/**
	 * @return Result
	 * @throws SystemException
	 */
	public function processRequest()
	{
		if(!isset($this->request['action']))
			throw new SystemException("Undefined \"action\"");

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");

		global $APPLICATION;

		if(strtolower(SITE_CHARSET) != 'utf-8')
			$this->request = $APPLICATION->ConvertCharsetArray($this->request, 'utf-8', SITE_CHARSET);

		try
		{
			call_user_func(
				array($this, $this->request['action']."Action")
			);

			/* Caution!
			 * You must update $this->request by fresh data,
			 * or you will refresh and receive old data!
			 */
			if(
				isset($this->request["refreshOrderData"])
				&& $this->request["refreshOrderData"] == "Y"
				&& $this->request['action'] != "refreshOrderData"
			)
				$this->refreshOrderDataAction();
		}
		catch(UserMessageException $e)
		{
			$this->addResultError($e->getMessage());
		}

		return $this->result;
	}

	/**
	 * @param $message
	 */
	public function addResultError($message)
	{
		$this->result->addError(new EntityError($message));
	}

	protected function addResultData($dataKey, $data)
	{
		if(strlen($dataKey) <= 0)
			$this->result->addData($data);
		else
			$this->result->addData(array($dataKey => $data));
	}

	/* * * * * * requests actions handlers * * * * * * * * */

	protected function addProductToBasketAction()
	{
		if(!$this->request["formData"]) throw new ArgumentNullException("formatData");
		if(!$this->request["quantity"]) throw new ArgumentNullException("quantity");
		if(!$this->request["productId"]) throw new ArgumentNullException("productId");

		$productId = isset($this->request['productId']) ? intval($this->request['productId']) : 0;
		$quantity = isset($this->request['quantity']) ? floatval($this->request['quantity']) : 1;
		$columns = isset($this->request['columns']) ? $this->request['columns'] : array();
		$customPrice = isset($this->request['customPrice']) ? $this->request['customPrice'] : false;

		$alreadyInBasketCode = "";
		$productParams = array();

		if(isset($this->request["formData"]["PRODUCT"]) && is_array($this->request["formData"]["PRODUCT"]))
		{
			foreach($this->request["formData"]["PRODUCT"] as $basketCode => &$params)
			{
				if(!isset($params["MODULE"]) || $params["MODULE"] != "catalog")
					continue;

				if(!isset($params["OFFER_ID"]) || $params["OFFER_ID"] != $productId)
					continue;

				$params["QUANTITY"] += $quantity;
				$this->request["ADD_QUANTITY_INSTEAD_ONLY"] = "Y";
				$alreadyInBasketCode = $basketCode;
				$productParams = $params;
				break;
			}
		}

		if(empty($productParams))
		{
			$productParams = Admin\Blocks\OrderBasket::getProductDetails(
				$productId,
				$quantity,
				!empty($this->request["formData"]["USER_ID"]) ? $this->request["formData"]["USER_ID"] : 0,
				isset($this->request["formData"]["SITE_ID"]) ? $this->request["formData"]["SITE_ID"] : SITE_ID,
				$columns
			);

			if($customPrice !== false)
			{
				$productParams["CUSTOM_PRICE"] = "Y";
				$productParams["PRICE"] = $customPrice;
			}
		}

		if(
			isset($this->request["replaceBasketCode"])
			&& strlen($this->request["replaceBasketCode"]) > 0
			&& isset($this->request["formData"]["PRODUCT"][$this->request["replaceBasketCode"]])
		)
		{
			$this->request["formData"]["PRODUCT"][$this->request["replaceBasketCode"]] = $productParams;

			if(strlen($alreadyInBasketCode) > 0)
				unset($this->request["formData"]["PRODUCT"][$alreadyInBasketCode]);
		}
		elseif(strlen($alreadyInBasketCode) <= 0)
		{
			$this->request["formData"]["PRODUCT"]["new"] = $productParams;
		}

		$this->formDataChanged = true;
	}

	protected function cancelOrderAction()
	{
		global $USER;
		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;
		$canceled = isset($this->request['canceled']) ? $this->request['canceled'] : "N";
		$comment = isset($this->request['comment']) ? trim($this->request['comment']) : "";
		$errors = array();

		if(!\CSaleOrder::CanUserCancelOrder($orderId, $USER->GetUserGroupArray(), $this->userId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_CANCEL_ORDER'));

		/** @var  \Bitrix\Sale\Order $saleOrder*/
		if(!$saleOrder = \Bitrix\Sale\Order::load($orderId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$state = $saleOrder->getField("CANCELED");

		if($state != $canceled)
			throw new UserMessageException(
				$state == "Y" ? Loc::getMessage('SALE_OA_ERROR_CANCEL_ORDER_ALREADY') : Loc::getMessage('SALE_OA_ERROR_CANCEL_ORDER_NOT_YET')
			);

		/** @var \Bitrix\Sale\Result $res */
		$res = $saleOrder->setField("CANCELED", $canceled == "Y" ? "N" : "Y");

		if(!$res->isSuccess())
			$errors = array_merge($errors, $res->getErrorMessages());

		$saleOrder->setField("REASON_CANCELED", $canceled == "N" ? $comment : "");

		if(!$res = $saleOrder->save())
			$errors = array_merge($errors, $res->getErrorMessages());

		$canceled = $saleOrder->getField("CANCELED");
		$this->addResultData("CANCELED", $canceled);

		if($canceled == "Y")
		{
			$userInfo = Admin\Blocks\OrderStatus::getUserInfo($saleOrder->getField("EMP_CANCELED_ID"));
			$this->addResultData("DATE_CANCELED", $saleOrder->getField("DATE_CANCELED")->toString());
			$this->addResultData("EMP_CANCELED_ID", $saleOrder->getField("EMP_CANCELED_ID"));
			$this->addResultData("EMP_CANCELED_NAME", $userInfo["NAME"]." (".$userInfo["LOGIN"].")");
		}

		if (!empty($errors))
		{
			throw new UserMessageException(implode("<br>\n", $errors));
		}
	}

	protected function saveCommentsAction()
	{
		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new SystemException("Wrong order id!");

		if(!isset($this->request['comments']))
			throw new SystemException("Can't find the comments content!");

		$res = Sale\Internals\OrderTable::update(
			$this->request['orderId'],
			array(
				"COMMENTS" => $this->request['comments'],
				"DATE_UPDATE" => new DateTime()
		));

		if(!$res->isSuccess())
			$this->addResultError(join("\n", $res->getErrorMessages()));

		$CBXSanitizer = new \CBXSanitizer;
		$CBXSanitizer->SetLevel(\CBXSanitizer::SECURE_LEVEL_MIDDLE);
		$this->addResultData("COMMENTS", $CBXSanitizer->SanitizeHtml($this->request['comments']));
	}

	protected function saveStatusAction()
	{
		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new SystemException("Wrong order id!");

		if(!isset($this->request['statusId']) || strlen($this->request['statusId']) <= 0)
			throw new SystemException("Wrong status id!");


		/** @var \Bitrix\Sale\Order $order */
		$order = \Bitrix\Sale\Order::load($this->request['orderId']);

		if (!$order)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": \"".$this->request['orderId']."\"");

		$statusesList = \Bitrix\Sale\OrderStatus::getAllowedUserStatuses(
			$this->userId,
			$order->getField('STATUS_ID')
		);

		if(array_key_exists($this->request['statusId'], $statusesList))
		{
			$res = $order->setField("STATUS_ID", $this->request['statusId']);

			if(!$res->isSuccess())
				throw new UserMessageException(implode("<br>\n", $res->getErrorMessages()));

			$res = $order->save();

			if(!$res->isSuccess())
				throw new UserMessageException(implode("<br>\n", $res->getErrorMessages()));
		}
	}

	protected function getOrderFieldsAction()
	{
		if(!isset($this->request['demandFields']) || !array($this->request['demandFields']) || empty($this->request['demandFields']))
			throw new SystemException("Demand fields is empty!");

		$this->addResultData(
			"RESULT_FIELDS",
			$this->getDemandedFields(
				$this->request['demandFields'],
				$this->request['givenFields']
			)
		);
	}

	protected function refreshOrderDataAction()
	{
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$additional = isset($this->request["additional"]) ? $this->request["additional"] : array();

		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = null;

		/** @var \Bitrix\Sale\Payment $payment */
		$payment = null;
		$opResults = new Result();

		//Use or not data from form and don't refresh data from provider
		Admin\OrderEdit::$isTrustProductFormData = (!empty($additional["operation"]) && $additional["operation"] == "DATA_ACTUALIZE") ? false : true;
		$order = $this->getOrder($formData, $opResults);
		$isStartField = $order->isStartField();

		if($order->getId() > 0)
		{
			$order = Admin\OrderEdit::editOrderByFormData($formData, $order, $this->userId, false, array(), $opResults);

			if(!$order)
			{
				if(!$opResults->isSuccess())
				{
					$this->addFilteredErrors($opResults);
					return;
				}
			}
		}

		if($order->getId() <= 0)
		{
			if(isset($formData['SHIPMENT']) && is_array($formData['SHIPMENT']))
			{
				$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
				$res->getErrorMessages();
				$data = $res->getData();
				$shipment = array_shift($data['SHIPMENT']);
				if (!$shipment->isCustomPrice())
					$shipment->setField('BASE_PRICE_DELIVERY', Admin\Blocks\OrderShipment::getDeliveryPrice($shipment));
			}

			if(isset($formData['PAYMENT']) && is_array($formData['PAYMENT']))
			{
				$res = Admin\Blocks\OrderPayment::updateData($order, $formData['PAYMENT'], true);
				$res->getErrorMessages();
				$data = $res->getData();
				$payment = array_shift($data['PAYMENT']);
			}
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $order->hasMeaningfulField();

			/** @var Result $r */
			$r = $order->doFinalAction($hasMeaningfulFields);
		}

		$result['PAYABLE'] = $order->getPrice() - $order->getSumPaid();
		$result["BASE_PRICE"] = Admin\Blocks\OrderBasket::getBasePrice($order);

		$data = $this->result->getData();
		if ($shipment)
		{
			if ($shipment->getField('CUSTOM_PRICE_DELIVERY') == 'Y')
				$result["CALCULATED_PRICE"] = Admin\Blocks\OrderShipment::getDeliveryPrice($shipment);

			if (!isset($data['SHIPMENT_DATA']['DELIVERY_SERVICE_LIST']))
			{
				$deliveryService = Admin\Blocks\OrderShipment::getDeliveryServiceList();
				$deliveryServiceTree = Admin\Blocks\OrderShipment::makeDeliveryServiceTree($deliveryService);
				$result['DELIVERY_SERVICE_LIST'] = Admin\Blocks\OrderShipment::getTemplate($deliveryServiceTree);
				if (!isset($data['SHIPMENT_DATA']['DELIVERY_ERROR']))
				{
					foreach ($deliveryService as $delivery)
					{
						if ($shipment->getDeliveryId() == $delivery['ID'] && $delivery['RESTRICTED'] != Sale\Services\PaySystem\Restrictions\Manager::SEVERITY_NONE)
							$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
					}
				}
			}
			if (!isset($data['SHIPMENT_DATA']['PROFILES']))
			{
				if ($shipment->getDeliveryId())
				{
					$service = Sale\Delivery\Services\Manager::getObjectById($shipment->getDeliveryId());

					if($service)
					{
						$parentService = $service->getParentService();
						if ($parentService && $parentService->canHasProfiles())
						{
							$profiles = Admin\Blocks\OrderShipment::getDeliveryServiceProfiles($parentService->getId());
							$profiles = Admin\Blocks\OrderShipment::checkProfilesRestriction($profiles, $shipment);
							$result["PROFILES"] = Admin\Blocks\OrderShipment::getProfileEditControl($profiles);
							if (!isset($data['SHIPMENT_DATA']['DELIVERY_ERROR']))
							{
								foreach ($profiles as $profile)
								{
									if ($shipment->getDeliveryId() == $profile['ID'] && $profile['RESTRICTED'] == Sale\Delivery\Restrictions\Manager::SEVERITY_SOFT)
										$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
								}
							}
						}
					}
				}
			}

			$companies = Company\Manager::getListWithRestrictions($shipment, Company\Restrictions\Manager::MODE_MANAGER);
			$result['SHIPMENT_COMPANY_ID'] = Admin\OrderEdit::makeSelectHtmlBodyWithRestricted($companies, $shipment->getField('COMPANY_ID'));
		}

		if ($payment)
		{
			$paySystemList = Admin\Blocks\OrderPayment::getPaySystemList($payment);

			if (isset($paySystemList[$payment->getPaymentSystemId()]['RESTRICTED']))
				$result['PAYSYSTEM_ERROR'] = Loc::getMessage('SALE_OA_ERROR_PAYSYSTEM_SERVICE');

			$result['PAY_SYSTEM_LIST'] = Admin\OrderEdit::makeSelectHtmlBodyWithRestricted($paySystemList, '', false);
			$result['PRICE_COD'] = $this->updatePriceCodAction($payment);

			$companies = Company\Manager::getListWithRestrictions($payment, Company\Restrictions\Manager::MODE_MANAGER);
			$result['PAYMENT_COMPANY_ID'] = Admin\OrderEdit::makeSelectHtmlBodyWithRestricted($companies, $payment->getField('COMPANY_ID'));
		}
		$orderBasket = new Admin\Blocks\OrderBasket($order,"", $this->request["formData"]["BASKET_PREFIX"]);
		$basketPrepareParams = array();

		if((
			!empty($additional["operation"]) && $additional["operation"] == "PRODUCT_ADD")
			|| ($this->request["action"] == "addProductToBasket"
				&& (!isset($this->request["ADD_QUANTITY_INSTEAD_ONLY"])
					|| $this->request["ADD_QUANTITY_INSTEAD_ONLY"] != "Y"
				)
			)
		)
		{
			$basketPrepareParams["SKIP_SKU_INFO"] = false;
			$basketPrepareParams["ADDED_PRODUCTS"] = array($this->request["productId"]);
		}
		else
		{
			$basketPrepareParams["SKIP_SKU_INFO"] = true;
			$basketPrepareParams["ADDED_PRODUCTS"] = array();
		}

		$result["BASKET"] = $orderBasket->prepareData($basketPrepareParams);
		$result["BASKET"]["LIGHT"] = "Y";
		// collect info about changed fields
		if($basketPrepareParams["SKIP_SKU_INFO"] && !empty($formData["PRODUCT"]) && is_array($formData["PRODUCT"]))
		{
			//prices
			$result["BASKET"]["PRICES_UPDATED"] = array();
			$errors = array();
			$PRECISE = 0.005;

			foreach($formData["PRODUCT"] as $basketCode => $itemParams)
			{
				if($basketCode == "new")
					continue;

				if(!isset($result["BASKET"]["ITEMS"][$basketCode]["PRICE"]) || !isset($itemParams["PRICE"]))
				{
					$errors[] = "Product price with basket code \"".$basketCode."\" not found.";
					continue;
				}

				if(abs(floatval($result["BASKET"]["ITEMS"][$basketCode]["PRICE"]) - floatval($itemParams["PRICE"])) >= $PRECISE)
					$result["BASKET"]["PRICES_UPDATED"][$basketCode] = $result["BASKET"]["ITEMS"][$basketCode]["PRICE"];
			}

			if(!empty($errors))
				$this->addResultData("ERROR_PRICE_COMPARING", $errors);

		}

		$resData = $opResults->getData();

		if(!empty($resData["NEW_ITEM_BASKET_CODE"]))
			$result["BASKET"]["NEW_ITEM_BASKET_CODE"] = $resData["NEW_ITEM_BASKET_CODE"];

		$result['RELATED_PROPS'] = Admin\Blocks\OrderBuyer::getRelPropData($order);
		$result["DISCOUNTS_LIST"] = Admin\OrderEdit::getOrderedDiscounts($order, false);

		if ($order->getBasket())
			$result['BASE_PRICE_DELIVERY'] = $result["DISCOUNTS_LIST"]['PRICES']['DELIVERY']['BASE_PRICE'];
		else
			$result['BASE_PRICE_DELIVERY'] = $order->getDeliveryPrice();

		$result['BASE_PRICE_DELIVERY'] = Sale\PriceMaths::roundByFormatCurrency($result['BASE_PRICE_DELIVERY'], $order->getCurrency());
		$result['DELIVERY_PRICE_DISCOUNT'] = Sale\PriceMaths::roundByFormatCurrency($result["DISCOUNTS_LIST"]['PRICES']['DELIVERY']['PRICE'], $order->getCurrency());
		$result["COUPONS_LIST"] = Admin\OrderEdit::getCouponList($order, false);
		$result["TOTAL_PRICES"] = Admin\OrderEdit::getTotalPrices($order, $orderBasket, false);
		$result["DELIVERY_DISCOUNT"] = $result["TOTAL_PRICES"]["DELIVERY_DISCOUNT"];

		$result = array_merge($result, $order->getFieldValues());

		if(!isset($result["PRICE"]))
			$result["PRICE"] = 0;

		/* DEMANDED */
		if(isset($additional["demanded"]) && is_array($additional["demanded"]))
		{
			if(isset($additional["given"]) && is_array($additional["given"]))
				$result=array_merge($result, $additional["given"]);

			$demanded = $this->getDemandedFields($additional["demanded"], $result, $order);
			$result = array_merge($result, $demanded);
		}

		$this->addFilteredErrors($opResults);
		$this->addResultData("ORDER_DATA", $result);
	}

	/* We don't show all errors during forming order via ajax requests */
	protected function addFilteredErrors(Result $opResults)
	{
		if(!$opResults->isSuccess())
		{
			foreach($opResults->getErrors() as $error)
			{
				if($error->getCode() == "CATALOG_QUANTITY_NOT_ENOGH"
						|| $error->getCode() == "SALE_ORDER_SYSTEM_SHIPMENT_LESS_QUANTITY"
						|| $error->getCode() == "CATALOG_NO_QUANTITY_PRODUCT"
						|| $error->getCode() == "SALE_SHIPMENT_SYSTEM_QUANTITY_ERROR"
						|| $error->getCode() == "SALE_BASKET_AVAILABLE_QUANTITY"
						|| $error->getCode() == "SALE_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY"

				)
					$this->addResultError($error->getMessage());
			}
		}
	}

	protected function changeResponsibleUserAction()
	{
		if(!isset($this->request['userId']) || intval($this->request['userId']) <= 0)
			throw new ArgumentNullException("userId");

		$siteId = strlen($this->request['siteId']) > 0 ? $this->request['siteId'] : "";
		global $USER;

		$dateResponsible = new \Bitrix\Main\Type\DateTime();
		$this->addResultData("RESPONSIBLE", Admin\OrderEdit::getUserName($this->request['userId'], $siteId));
		$this->addResultData("EMP_RESPONSIBLE", Admin\OrderEdit::getUserName($USER->GetID(), $siteId));
		$this->addResultData("DATE_RESPONSIBLE", $dateResponsible->toString());
	}

	protected function updatePaymentStatusAction()
	{
		global $USER;

		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new ArgumentNullException("orderId");

		if(!isset($this->request['paymentId']) || intval($this->request['paymentId']) <= 0)
			throw new ArgumentNullException("paymentId");

		$fields = array();
		$orderStatusId = '';
		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($this->request['orderId']);

		/** @var \Bitrix\Sale\Payment $payment */
		$payment = $order->getPaymentCollection()->getItemById($this->request['paymentId']);
		$hasErrors = false;

		if ($this->request['method'] == 'save')
		{
			if ($payment->getField('IS_RETURN') == 'Y')
			{
				$res = $payment->setReturn('N');
				if (!$res->isSuccess())
				{
					$this->addResultError(join("\n", $res->getErrorMessages()));
					$hasErrors = true;
				}
			}
			else
			{
				$res = $payment->setPaid('Y');
				if (!$res->isSuccess())
				{
					$this->addResultError(join("\n", $res->getErrorMessages()));
					$hasErrors = true;
				}
			}

			if (!$hasErrors)
			{
				foreach ($this->request['data'] as $key => $value)
				{
					$newKey = substr($key, 0, strripos($key, '_'));
					if (strpos($newKey, 'PAY_VOUCHER') !== false)
						$fields[$newKey] = $value;
					if ($newKey == 'ORDER_STATUS_ID')
						$orderStatusId = $value;
				}
				try
				{
					$fields['PAY_VOUCHER_DATE'] = new \Bitrix\Main\Type\Date($fields['PAY_VOUCHER_DATE']);
				}
				catch (ObjectException $exception)
				{
					$this->addResultError(Loc::getMessage('SALE_OA_ERROR_INCORRECT_DATE'));
					return;
				}

			}
		}
		else
		{
			foreach ($this->request['data'] as $key => $value)
			{
				$newKey = substr($key, 0, strripos($key, '_'));
				if (strpos($newKey, 'PAY_RETURN') !== false)
					$fields[$newKey] = $value;
			}

			if (isset($fields['PAY_RETURN_OPERATION_ID']))
			{
				/** @var Result $refResult */
				$refResult = $payment->setReturn($fields['PAY_RETURN_OPERATION_ID']);
				if (!$refResult->isSuccess())
				{
					$this->addResultError(join("\n", $refResult->getErrorMessages()));
					return;
				}

				unset($fields['PAY_RETURN_OPERATION_ID']);
			}
			else
			{
				$res = $payment->setPaid('N');
				if (!$res->isSuccess())
					$this->addResultError(join("\n", $res->getErrorMessages()));
			}
			try
			{
				$fields['PAY_RETURN_DATE'] = new Date($fields['PAY_RETURN_DATE']);
			}
			catch (ObjectException $exception)
			{
				$this->addResultError(Loc::getMessage('SALE_OA_ERROR_INCORRECT_DATE'));
				return;
			}
		}

		if (!$hasErrors)
		{
			$saveResult = $payment->setFields($fields);
			if ($saveResult->isSuccess())
			{
				if (!empty($orderStatusId))
				{
					if ($USER && $USER->isAuthorized())
						$statusesList = Sale\OrderStatus::getAllowedUserStatuses($USER->getID(), $order->getField('STATUS_ID'));
					else
						$statusesList = Sale\OrderStatus::getAllStatuses();

					if ($order->getField('STATUS_ID') != $orderStatusId && array_key_exists($orderStatusId, $statusesList))
					{
						/** @var Result $res */
						$res = $order->setField('STATUS_ID', $orderStatusId);
						if (!$res->isSuccess())
						{
							$this->addResultError(join("\n", $res->getErrorMessages()));
							return;
						}
					}
				}

				$result = $order->save();
				if ($result->isSuccess())
				{
					$preparedData = Admin\Blocks\OrderFinanceInfo::prepareData($order);
					$preparedData["PAYMENT_PAID_".$payment->getId()] = $payment->isPaid() ? "Y" : "N";

					$shipmentCollection = $order->getShipmentCollection();
					if ($shipmentCollection)
					{
						/** @var \Bitrix\Sale\Shipment $shipment */
						foreach ($shipmentCollection as $shipment)
						{
							if (!$shipment->isSystem())
							{
								$preparedData['DEDUCTED_'.$shipment->getId()] = $shipment->getField('DEDUCTED');
								$preparedData['ALLOW_DELIVERY_'.$shipment->getId()] = $shipment->getField('ALLOW_DELIVERY');

								$preparedStatusList = array();
								$statusList = Admin\Blocks\OrderShipmentStatus::getShipmentStatusList($shipment->getField('STATUS_ID'));
								foreach ($statusList as $id => $name)
								{
									if ($shipment->getField('STATUS_ID') === $id)
										continue;

									$preparedStatusList[] = array(
										'ID' => $id,
										'NAME' => htmlspecialcharsbx($name)
									);
								}

								$preparedData['SHIPMENT_STATUS_LIST_'.$shipment->getId()] = $preparedStatusList;
								$preparedData['SHIPMENT_STATUS_'.$shipment->getId()] = array('id' => $shipment->getField('STATUS_ID'), 'name' => htmlspecialcharsbx($statusList[$shipment->getField('STATUS_ID')]));
							}
						}
					}

					$this->addResultData(
						"RESULT",
						$preparedData
					);
				}
				else
				{
					$this->addResultError(join("\n", $result->getErrorMessages()));
					return;
				}
			}
			else
			{
				$this->addResultError(join("\n", $saveResult->getErrorMessages()));
			}
		}
	}

	protected function deletePaymentAction()
	{
		global $USER;
		$orderId = $this->request['orderId'];
		$paymentId = $this->request['paymentId'];

		if ($orderId <= 0 || $paymentId <=0)
			throw new ArgumentNullException("paymentId or orderId");

		/** @var \Bitrix\Sale\Order $order */
		$order = \Bitrix\Sale\Order::load($orderId);

		if (!$order)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->getItemById($paymentId);

		if (!$payment)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_PAYMENT').": ".$paymentId);

		$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
		if(!in_array($order->getField("STATUS_ID"), $allowedStatusesUpdate))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_DELETE_PAYMENT_PERMISSION').': '.$paymentId);
		}

		$delResult = $payment->delete();

		if ($delResult->isSuccess())
		{
			$result = $order->save();
			if ($result->isSuccess())
				$this->addResultData("RESULT", "OK");
			else
				throw new UserMessageException(join("\n", $result->getErrorMessages()));
		}
		else
		{
			throw new UserMessageException(join("\n", $delResult->getErrorMessages()));
		}
	}

	protected function deleteShipmentAction()
	{
		global $USER;
		$orderId = $this->request['order_id'];
		$shipmentId = $this->request['shipment_id'];

		if ($orderId <= 0 || $shipmentId <= 0)
			throw new UserMessageException('Error');

		/** @var \Bitrix\Sale\Order $order */
		$order = \Bitrix\Sale\Order::load($orderId);

		if (!$order)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$shipmentCollection = $order->getShipmentCollection();
		$shipmentItem = $shipmentCollection->getItemById($shipmentId);

		if (!$shipmentItem)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_SHIPMENT').': '.$shipmentId);
		
		$allowedStatusesDelete = Sale\DeliveryStatus::getStatusesUserCanDoOperations($USER->GetID(), array('delete'));
		if(!in_array($shipmentItem->getField("STATUS_ID"), $allowedStatusesDelete))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_DELETE_SHIPMENT_PERMISSION').': '.$shipmentId);
		}

		$delResult = $shipmentItem->delete();

		if ($delResult->isSuccess())
		{
			$saveResult = $order->save();
			if ($saveResult->isSuccess())
			{
				$result["DELIVERY_PRICE"] = $shipmentCollection->getBasePriceDelivery();
				$result["DELIVERY_PRICE_DISCOUNT"] = $shipmentCollection->getPriceDelivery();
				$result['PRICE'] = $order->getPrice();
				$result['PAYABLE'] = $result['PRICE'] - $order->getSumPaid();

				$orderBasket = new Admin\Blocks\OrderBasket($order);
				$result["TOTAL_PRICES"] = Admin\OrderEdit::getTotalPrices($order, $orderBasket, false);

				$this->addResultData("RESULT", $result);
			}
			else
			{
				$this->addResultError(join("\n", $saveResult->getErrorMessages()));
			}
		}
		else
		{
			$this->addResultError(join("\n", $delResult->getErrorMessages()));
		}
	}

	protected function saveBasketVisibleColumnsAction()
	{
		$columns = isset($this->request['columns']) ? $this->request['columns'] : array();
		$idPrefix = isset($this->request['idPrefix']) ? $this->request['idPrefix'] : "";

		if(\CUserOptions::SetOption($idPrefix."order_basket_table", "table_columns", array("columns" => implode(",", $columns))))
			$this->addResultData("RESULT", "OK");
		else
			$this->addResultError("Can't save columns!");
	}

	protected function updateShipmentStatusAction()
	{
		$shipmentId = $this->request['shipmentId'];
		$orderId = $this->request['orderId'];
		$field = $this->request['field'];
		$index = $this->request['index'];
		$newStatus = $this->request['status'];

		/** @var \Bitrix\Sale\Order $order */
		$order = \Bitrix\Sale\Order::load($orderId);

		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $order->getShipmentCollection()->getItemById($shipmentId);

		$setResult = $shipment->setField($field, $newStatus);

		if ($setResult->isSuccess())
		{
			$saveResult = $order->save();
			if (!$saveResult->isSuccess())
				$this->addResultError(join("\n", $saveResult->getErrorMessages()));
		}
		else
		{
			$serResultMessage = $setResult->getErrorMessages();
			if (!empty($serResultMessage))
				$this->addResultError(join("\n", $serResultMessage));
			else
				$this->addResultError(Loc::getMessage('SALE_OA_SHIPMENT_STATUS_ERROR'));
		}

		if($shipment)
		{
			$preparedStatusList = array();
			$statusList = Admin\Blocks\OrderShipmentStatus::getShipmentStatusList($shipment->getField('STATUS_ID'));
			foreach ($statusList as $id => $name)
			{
				if ($shipment->getField('STATUS_ID') === $id)
					continue;

				$preparedStatusList[] = array(
					'ID' => $id,
					'NAME' => htmlspecialcharsbx($name)
				);
			}

			$result = array(
				'DEDUCTED_'.$shipment->getId() => $shipment->getField('DEDUCTED'),
				'ALLOW_DELIVERY_'.$shipment->getId() => $shipment->getField('ALLOW_DELIVERY'),
				'SHIPMENT_STATUS_LIST_'.$shipment->getId() => $preparedStatusList,
				'SHIPMENT_STATUS_'.$shipment->getId() => array('id' => $shipment->getField('STATUS_ID'), 'name' => htmlspecialcharsbx($statusList[$shipment->getField('STATUS_ID')]))
			);

			$this->addResultData("RESULT", $result);
		}
	}

	protected function createNewPaymentAction()
	{
		$formData = $this->request['formData'];
		$index = $this->request['index'];

		$order = $this->getOrder($formData);

		if(isset($formData['SHIPMENT']) && is_array($formData['SHIPMENT']))
		{
			$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
			$res->getErrorMessages();
		}

		if(isset($formData['PAYMENT']) && is_array($formData['PAYMENT']))
		{
			$res = Admin\Blocks\OrderPayment::updateData($order, $formData['PAYMENT']);
			$res->getErrorMessages();
		}

		$payment = $order->getPaymentCollection()->createItem();
		$this->addResultData("PAYMENT", \Bitrix\Sale\Helpers\Admin\Blocks\OrderPayment::getEdit($payment, $index));
	}

	protected function getProductEditDialogHtmlAction()
	{
		$currency = isset($this->request['currency']) ? $this->request['currency'] : array();
		$objName = isset($this->request['objName']) ? $this->request['objName'] : "";
		$this->addResultData(
			'DIALOG_CONTENT',
			Admin\Blocks\OrderBasket::getProductEditDialogHtml(
				$currency,
				$objName
			)
		);
	}

	protected function changeDeliveryServiceAction()
	{
		$result = array();
		$profiles = array();
		$index = $this->request['index'];
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$formData['ID'] = $formData['order_id'];
		$deliveryId = intval($formData['SHIPMENT'][$index]['DELIVERY_ID']);

		if ($deliveryId <= 0)
			return;

		Admin\OrderEdit::$isTrustProductFormData = true;
		$order = $this->getOrder($formData);

		/** @var  \Bitrix\Sale\Delivery\Services\Base $service */
		$service = Sale\Delivery\Services\Manager::getObjectById($deliveryId);
		if ($service && $service->canHasProfiles())
		{
			$profiles = Admin\Blocks\OrderShipment::getDeliveryServiceProfiles($deliveryId);
			if (!isset($formData['SHIPMENT'][$index]['PROFILE']))
			{
				reset($profiles);
				$initProfile = current($profiles);
				$formData['SHIPMENT'][$index]['PROFILE'] = $initProfile['ID'];
				$this->request["formData"]['SHIPMENT'][$index]['PROFILE'] = $initProfile['ID'];
			}
		}

		$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
		$data = $res->getData();
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = array_shift($data['SHIPMENT']);

		if ($service->canHasProfiles())
		{
			$profiles = Admin\Blocks\OrderShipment::checkProfilesRestriction($profiles, $shipment);
			$result["PROFILES"] = Admin\Blocks\OrderShipment::getProfileEditControl($profiles, $index, $shipment->getDeliveryId());

			foreach ($profiles as $profile)
			{
				if ($formData['SHIPMENT'][$index]['PROFILE'] == $profile['ID'] && $profile['RESTRICTED'] == Sale\Delivery\Restrictions\Manager::SEVERITY_SOFT)
				{
					$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
					break;
				}
			}
		}

		$deliveryService = Admin\Blocks\OrderShipment::getDeliveryServiceList($shipment);
		$deliveryServiceTree = Admin\Blocks\OrderShipment::makeDeliveryServiceTree($deliveryService);
		$result['DELIVERY_SERVICE_LIST'] = Admin\Blocks\OrderShipment::getTemplate($deliveryServiceTree);

		foreach ($deliveryService as $delivery)
		{
			if ($deliveryId == $delivery['ID'] && $delivery['RESTRICTED'] != Sale\Delivery\Restrictions\Manager::SEVERITY_NONE)
			{
				$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
				break;
			}
		}

		$storeMap = Admin\Blocks\OrderShipment::getMap($shipment->getDeliveryId(), $index);
		if ($storeMap)
			$result['MAP'] = $storeMap;

		$extraServiceManager = new \Bitrix\Sale\Delivery\ExtraServices\Manager($deliveryId);
		$extraServiceManager->setOperationCurrency($order->getCurrency());
		$deliveryExtraService = $shipment->getExtraServices();

		if ($deliveryExtraService)
			$extraServiceManager->setValues($deliveryExtraService);

		$extraService = $extraServiceManager->getItems();

		if ($extraService)
			$result["EXTRA_SERVICES"] = Admin\Blocks\OrderShipment::getExtraServiceEditControl($extraService, $index, false, $shipment);

		$deliveryPrice = Admin\Blocks\OrderShipment::getDeliveryPrice($shipment);

		if ($shipment->getField('CUSTOM_PRICE_DELIVERY') == 'Y')
		{
			$result["CALCULATED_PRICE"] = $deliveryPrice;
		}
		else
		{
			$result["PRICE_DELIVERY"] = $deliveryPrice;
			$shipment->setField('PRICE_DELIVERY', $deliveryPrice);
			$this->request['formData']['SHIPMENT'][$index]['PRICE_DELIVERY'] = $deliveryPrice;
		}

		$this->addResultData("SHIPMENT_DATA", $result);

		$this->formDataChanged = true;
	}

	protected function getDefaultDeliveryPriceAction()
	{
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$formData['ID'] = $formData['order_id'];

		$order = $this->getOrder($formData);

		$result = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);

		$data = $result->getData();
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = array_shift($data['SHIPMENT']);
		$deliveryPrice = Admin\Blocks\OrderShipment::getDeliveryPrice($shipment);

		$this->addResultData(
			"RESULT",
			array(
				"CALCULATED_PRICE" => $deliveryPrice
			)
		);
	}

	protected function checkProductBarcodeAction()
	{
		if(!\Bitrix\Main\Loader::includeModule("catalog"))
			throw new UserMessageException("ERROR");
		$basketItem = null;
		$result = false;

		$barcode = $this->request['barcode'];
		$basketId = $this->request['basketId'];
		$orderId = $this->request['orderId'];
		$storeId = $this->request['storeId'];

		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($orderId);
		if ($order)
		{
			$basket = $order->getBasket();
			if ($basket)
				$basketItem = $basket->getItemById($basketId);
		}

		if ($basketItem)
		{
			$params = array(
				'BARCODE' => $barcode,
				'STORE_ID' => $storeId
			);
			$result = Provider::checkProductBarcode($basketItem, $params);
		}

		if ($result)
			$this->addResultData('RESULT', 'OK');
		else
			$this->addResultError('ERROR');
	}

	protected function deleteCouponAction()
	{
		if(!isset($this->request["userId"])) throw new ArgumentNullException("userId");
		if(!isset($this->request["coupon"])) throw new ArgumentNullException("coupon");
		if(!isset($this->request["orderId"])) throw new ArgumentNullException("orderId");

		Admin\OrderEdit::initCouponsData($this->request["userId"], $this->request["orderId"]);

		if(Sale\DiscountCouponsManager::delete($this->request["coupon"]))
			$this->addResultData('RESULT', 'OK');
		else
			$this->addResultError('ERROR');
	}

	protected function addCouponsAction()
	{
		if(!isset($this->request["userId"])) throw new ArgumentNullException("userId");
		if(!isset($this->request["coupon"])) throw new ArgumentNullException("coupon");
		if(!isset($this->request["orderId"])) throw new ArgumentNullException("orderId");

		Admin\OrderEdit::initCouponsData($this->request["userId"], $this->request["orderId"]);

		if(strlen($this->request["coupon"]) > 0)
		{
			$coupons = explode(",", $this->request["coupon"]);

			if(is_array($coupons) && count($coupons) > 0)
				foreach($coupons as $coupon)
					if(strlen($coupon) > 0)
						Sale\DiscountCouponsManager::add($coupon);
		}

		$this->addResultData('RESULT', 'OK');
	}

	protected function getProductIdByBarcodeAction()
	{
		\Bitrix\Main\Loader::includeModule('catalog');

		$barcode = $this->request['barcode'];

		if(strlen($barcode) > 0)
		{
			$rsBarCode = \CCatalogStoreBarCode::getList(array(), array("BARCODE" => $barcode), false, false, array('PRODUCT_ID'));
			$arBarCode = $rsBarCode->Fetch();
		}

		$this->addResultData(
			'RESULT',
			array(
				"PRODUCT_ID" => isset($arBarCode["PRODUCT_ID"]) ? intval($arBarCode["PRODUCT_ID"]) : 0
			)
		);
	}

	/* * * * * * * accessory methods * * * * * * * */

	protected function getDemandedFields(array $demandedFields, array $incomingFields, \Bitrix\Sale\Order $order = null)
	{
		$result = array();
		$userId = isset($incomingFields["USER_ID"]) && intval($incomingFields["USER_ID"]) > 0 ? intval($incomingFields["USER_ID"])  : 0;
		$currency = isset($incomingFields["CURRENCY"]) ? trim($incomingFields["CURRENCY"]) : "";
		$personTypeId = isset($incomingFields['PERSON_TYPE_ID']) ? intval($incomingFields['PERSON_TYPE_ID']) : 0;
		$siteId = !empty($incomingFields["SITE_ID"]) ? trim($incomingFields["SITE_ID"])  : SITE_ID;
		$orderId = isset($incomingFields["ID"]) ? intval($incomingFields["ID"]) : 0;

		if($order === null && intval($orderId) > 0)
			$order = \Bitrix\Sale\Order::load($orderId);

		foreach($demandedFields as $demandedField)
		{
			switch($demandedField)
			{
				case "BUYER_USER_NAME":

					$siteId = (bool)$order ? $order->getSiteId() : "";
					$result["BUYER_USER_NAME"] = intval($userId) > 0 ? \Bitrix\Sale\Helpers\Admin\OrderEdit::getUserName(intval($userId), $siteId) : "";
					break;

				case "PROPERTIES":

					if($userId > 0)
					{
						$profileId = isset($incomingFields["BUYER_PROFILE_ID"]) ? intval($incomingFields["BUYER_PROFILE_ID"]) : 0;
						$result["PROPERTIES"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getProfileParams($userId, $profileId);
					}
					else
					{
						$porder = Sale\Order::create($siteId);
						$porder->setPersonTypeId($personTypeId);
						$result["PROPERTIES"] = array();

						/** @val \Bitrix\Sale\PropertyValue $prop */
						foreach(\Bitrix\Sale\PropertyValue::loadForOrder($porder) as $prop)
						{
							$p = $prop->getProperty();
							$result["PROPERTIES"][$prop->getPropertyId()] = !empty($p["DEFAULT_VALUE"]) ? $p["DEFAULT_VALUE"] : "";
						}
					}
					break;

				case "BUYER_PROFILES_LIST":

					if(intval($personTypeId)<=0)
						throw new \Bitrix\Main\ArgumentNullException("personTypeId");

					$result["BUYER_PROFILES_LIST"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getBuyerProfilesList($userId, $personTypeId);
					break;

				case "BUYER_PROFILES_DATA":

					$result["BUYER_PROFILES_DATA"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getUserProfiles($userId, $personTypeId);
					break;

				case "BUYER_BUDGET":
					$res = \CSaleUserAccount::getList(
						array(),
						array(
							'USER_ID' => $userId,
							'CURRENCY' => $currency,
							'LOCKED' => 'N'
						),
						false,
						false,
						array(
							'CURRENT_BUDGET'
						)
					);

					if($userAccount = $res->Fetch())
						$result["BUYER_BUDGET"] = $userAccount['CURRENT_BUDGET'];
					else
						$result["BUYER_BUDGET"] = 0;

					break;
				case "PROPERTIES_ARRAY":

					if(!$order)
						throw new \Bitrix\Main\SystemException("Can't init order");

					if(intval($personTypeId)<=0)
						throw new \Bitrix\Main\ArgumentNullException("personTypeId");

					$order->setPersonTypeId($personTypeId);

					$result["PROPERTIES_ARRAY"] = $order->loadPropertyCollection()->getArray();
					break;

				case "PRODUCT":
					$result["PRODUCT"] = array();
					break;

				case "COUPONS":
					if(!$userId)
						throw new \Bitrix\Main\ArgumentNullException("userId");

					$result["COUPONS"] = Admin\OrderEdit::getCouponsData();

					break;

				case "COUPONS_LIST":

					$result["COUPONS_LIST"] = Admin\OrderEdit::getCouponList($order);

					break;

				default:
					throw new \Bitrix\Main\SystemException("Field: \"".$demandedField."\" is unknown!");
			}
		}

		return $result;
	}

	/**
	 * @param $formData
	 * @return Sale\Order
	 * @throws ArgumentNullException
	 * @throws UserMessageException
	 */
	protected function getOrder(array $formData, Result &$result = null)
	{
		$formData["ID"] = (!isset($formData["ID"]) ? 0 : (int)$formData["ID"]);

		if($this->order !== null  && !$this->formDataChanged && $this->order->getId() == $formData["ID"])
			return $this->order;

		if(!$result)
			$result = new Result();

		$currentUserId = 0;
		$oldUserId = null;

		if ($formData["ID"] > 0)
		{
			if ((int)$formData["USER_ID"] > 0)
				$currentUserId = (int)$formData["USER_ID"];
			if ((int)$formData["OLD_USER_ID"] > 0)
				$oldUserId = (int)$formData["OLD_USER_ID"];
		}
		else
		{
			if (isset($formData["USER_ID"]))
				$currentUserId = (int)$formData["USER_ID"];
			if (isset($formData["OLD_USER_ID"]))
				$oldUserId = (int)$formData["OLD_USER_ID"];
		}

		Admin\OrderEdit::initCouponsData($currentUserId, $formData["ID"], $oldUserId);
		unset($oldUserId, $currentUserId);

		if($formData["ID"] > 0)
		{
			$this->order = Sale\Order::load($formData["ID"]);

			if(!$this->order)
				throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$formData["ID"]);
		}
		else
		{
			$this->order = Admin\OrderEdit::createOrderFromForm($formData, $this->userId, false, array(), $result);

			if(!$this->order)
			{
				$this->addFilteredErrors($result);
				throw new UserMessageException;
			}
		}

		$this->formDataChanged = false;
		return $this->order;
	}

	public static function convertEncodingArray($arData, $charsetFrom, $charsetTo, &$errorMessage = "")
	{
		if (!is_array($arData))
		{
			if (is_string($arData))
			{
				$arData = Encoding::convertEncoding($arData, $charsetFrom, $charsetTo, $errorMessage);
			}
		}
		else
		{
			foreach ($arData as $key => $value)
			{
				$s = '';

				$newKey = Encoding::convertEncoding($key, $charsetFrom, $charsetTo, $s);
				$arData[$newKey] = Encoding::convertEncodingArray($value, $charsetFrom, $charsetTo, $s);

				if($newKey != $key)
					unset($arData[$key]);

				if($s!=='')
				{
					$errorMessage .= ($errorMessage == "" ? "" : "\n").$s;
				}
			}
		}

		return $arData;
	}

	protected function updatePaySystemInfoAction()
	{
		if ($this->request["orderId"])
			$orderId = $this->request["orderId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_ORDER_ID_WRONG'));

		if ($this->request["paymentId"])
			$paymentId = $this->request["paymentId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_PAYMENT_ID_WRONG'));

		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($orderId);
		if ($order)
		{
			/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
			$paymentCollection = $order->getPaymentCollection();

			/** @var \Bitrix\Sale\Payment $payment */
			$payment = $paymentCollection->getItemById($paymentId);

			if ($payment)
			{
				/** @var Sale\PaySystem\Service $service */
				$service = Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
				if ($service->isCheckable())
				{
					try
					{
						$res = $service->check($payment);
						if ($res instanceof Sale\PaySystem\ServiceResult)
						{
							if (!$res->isSuccess())
								$this->addResultError(join('\n', $res->getErrorMessages()));
						}
						else
						{
							if (!$res)
								$this->addResultError(Loc::getMessage('SALE_OA_ERROR_CONNECT_PAY_SYS'));
						}
					}
					catch(SystemException $e)
					{
						$this->addResultError($e->getMessage());
					}
				}
			}
		}
	}

	protected function saveTrackingNumberAction()
	{
		$trackingNumber = '';

		if ($this->request["orderId"])
			$orderId = $this->request["orderId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_ORDER_ID_WRONG'));

		if ($this->request["shipmentId"])
			$shipmentId = $this->request["shipmentId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_SHIPMENT_ID_WRONG'));

		if ($this->request['trackingNumber'])
			$trackingNumber = $this->request['trackingNumber'];

		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($orderId);
		if ($order)
		{
			/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
			$shipmentCollection = $order->getShipmentCollection();

			/** @var \Bitrix\Sale\Payment $payment */
			$shipment = $shipmentCollection->getItemById($shipmentId);

			if ($shipment)
			{
				$result = $shipment->setField('TRACKING_NUMBER', $trackingNumber);
				if ($result->isSuccess())
				{
					$result = $order->save();
					if (!$result->isSuccess())
					{
						$messages = join(', ', $result->getErrorMessages());
						$this->addResultError($messages);
					}
				}
			}
		}
	}

	protected function refreshTrackingStatusAction()
	{
		$shipmentId = !empty($this->request["shipmentId"]) && intval($this->request["shipmentId"]) > 0 ? intval($this->request["shipmentId"]) : 0;
		$trackingNumber = !empty($this->request["trackingNumber"]) && strlen($this->request["trackingNumber"]) > 0 ? $this->request["trackingNumber"] : '';

		if($shipmentId <= 0)
			throw new ArgumentNullException('shipmentId');

		if(strlen($trackingNumber) <= 0)
			return;

		$manager = Sale\Delivery\Tracking\Manager::getInstance();
		$result = $manager->getStatusByShipmentId($shipmentId, $trackingNumber);

		if($result->isSuccess())
		{
			$this->addResultData(
				'TRACKING_STATUS',
				Sale\Delivery\Tracking\Manager::getStatusName($result->status)
			);
			$this->addResultData('TRACKING_DESCRIPTION', $result->description);

			$this->addResultData(
				'TRACKING_LAST_CHANGE',
				\Bitrix\Main\Type\DateTime::createFromTimestamp(
					$result->lastChangeTimestamp
				)->toString()
			);

			$res = $manager->updateShipment($shipmentId, $result);

			if(!$res->isSuccess())
				$this->addResultError(implode(", ", $res->getErrorMessages()));
		}
		else
		{
			$this->addResultError(implode("\n", $result->getErrorMessages()));
		}
	}

	protected function unmarkOrderAction()
	{
		global $USER;
		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;

		if(!\CSaleOrder::CanUserMarkOrder($orderId, $USER->GetUserGroupArray(), $this->userId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_UNMARK_RIGHTS'));

		/** @var  \Bitrix\Sale\Order $saleOrder*/
		if(!$saleOrder = \Bitrix\Sale\Order::load($orderId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		/** @var \Bitrix\Sale\Result $res */
		$res = $saleOrder->setField("MARKED", "N");

		$errors = array();

		if(!$res->isSuccess())
			$errors = $res->getErrorMessages();

		if(!$res = $saleOrder->save())
			$errors = array_merge($errors, $res->getErrorMessages());

		if (!empty($errors))
			$this->addResultError($errors);
	}

	protected function updatePriceCodAction($payment = null)
	{
		if ($payment === null)
		{
			if ($this->request["paySystemId"] !== null)
				$paySystemId = $this->request["paySystemId"];
			else
				throw new ArgumentNullException('paymentId');

			if ($this->request["orderId"] !== null)
				$orderId = $this->request["orderId"];
			else
				throw new ArgumentNullException('orderId');

			if ($this->request["paymentId"] !== null)
				$paymentId = $this->request["paymentId"];
			else
				throw new ArgumentNullException('paymentId');

			if ($orderId > 0)
			{
				/** @var \Bitrix\Sale\Order $order */
				$order = Sale\Order::load($orderId);
				if ($order)
				{
					/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
					$paymentCollection = $order->getPaymentCollection();
					if ($paymentCollection)
					{
						/** @var \Bitrix\Sale\Payment $payment */
						if ($paymentId > 0)
						{
							$payment = $paymentCollection->getItemById($paymentId);
							if ($payment)
								$payment->setField('PAY_SYSTEM_ID', $paySystemId);
						}
						else
						{
							$payment = $paymentCollection->createItem(Sale\PaySystem\Manager::getObjectById($paySystemId));
							$price = floatval($this->request["price"]);
							$payment->setField('SUM', $price);
						}
					}
				}
			}
		}
		$priceCod = 0;

		if ($payment && $payment->getPaymentSystemId() > 0)
		{
			$service = Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
			if ($service !== null)
			{
				$priceCod = $service->getPaymentPrice($payment);
				$this->addResultData('PRICE_COD', $priceCod);
			}
		}
		return $priceCod;
	}

	protected function getOrderTailsAction()
	{
		$orderId = isset($this->request["orderId"]) ? $this->request["orderId"] : array();
		$formType = isset($this->request["formType"]) && $this->request["formType"] == "edit" ? "edit" : "view";
		$idPrefix = isset($this->request["idPrefix"]) ? trim($this->request["idPrefix"]) : "";

		$result = array();
		/** @var \Bitrix\Sale\Order $order */
		$order = \Bitrix\Sale\Order::load($orderId);
		$orderBasket = new Admin\Blocks\OrderBasket($order, "", $idPrefix);
		$result["DISCOUNTS_LIST"] = Admin\OrderEdit::getOrderedDiscounts($order, false);
		$result["BASKET"] = $orderBasket->prepareData(
			array("DISCOUNTS" => $result["DISCOUNTS_LIST"])
		);
		$result["ANALYSIS"] = Admin\Blocks\OrderAnalysis::getView($order, $orderBasket);
		$result["SHIPMENTS"] = "";
		Admin\Blocks\OrderShipment::setBackUrl($_SERVER['HTTP_REFERER']);

		$httpReferrer = new Web\Uri($_SERVER['HTTP_REFERER']);
		Admin\Blocks\OrderShipment::setBackUrl($httpReferrer->getPathQuery());

		$shipments = $order->getShipmentCollection();
		$index = 0;

		/** @var \Bitrix\Sale\Shipment  $shipment*/
		foreach ($shipments as $shipment)
		{
			if(!$shipment->isSystem())
			{
				$result["SHIPMENTS"] .= Admin\Blocks\OrderShipment::getView(
					$shipment,
					$index++,
					$formType == 'edit' ? 'edit' : ''
				);
			}
		}

		$this->addResultData("", $result);
	}
}
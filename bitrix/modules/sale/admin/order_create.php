<?

/**
 * @var  CUser $USER
 * @var  CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Sale\Helpers\Admin\Blocks;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

Loc::loadMessages(__FILE__);
$ID = isset($_REQUEST["ID"]) ? intval($_REQUEST["ID"]) : 0;
$isSavingOperation = (
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& (
		isset($_POST["apply"])
		|| isset($_POST["save"])
	)
	&& check_bitrix_sessid()
);
$needFieldsRestore = $_SERVER["REQUEST_METHOD"] == "POST" && !$isSavingOperation;
$isCopyingOrderOperation = $ID > 0;
$createWithProducts = (isset($_GET["USER_ID"]) && isset($_GET["SITE_ID"]) || isset($_GET["product"]));
$showProfiles = false;
$profileId = 0;

$arUserGroups = $USER->GetUserGroupArray();
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if (
	$saleModulePermissions == "D"
	|| ($isSavingOperation && $saleModulePermissions < "U")
)
{
	$APPLICATION->AuthForm(Loc::getMessage("SALE_OK_ACCESS_DENIED"));
}

$moduleId = "sale";
Bitrix\Main\Loader::includeModule('sale');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");

$siteId = isset($_REQUEST["SITE_ID"]) ? htmlspecialcharsbx($_REQUEST["SITE_ID"]) : "";
$siteName = OrderEdit::getSiteName($siteId);
$order = null;
$result = new \Bitrix\Sale\Result();

$customTabber = new CAdminTabEngine("OnAdminSaleOrderCreate");
$customDraggableBlocks = new CAdminDraggableBlockEngine('OnAdminSaleOrderCreateDraggable');

DiscountCouponsManager::init(
	DiscountCouponsManager::MODE_MANAGER,
	array(
		'userId' => isset($_POST["USER_ID"]) ? $_POST["USER_ID"] : 0
	)
);
// try to create order from form data & save it
if($isSavingOperation || $needFieldsRestore)
{
	$order = OrderEdit::createOrderFromForm($_POST, $USER->GetID(), true, $_FILES, $result);

	if($order)
	{
		$errorMessage = '';

		if (!$customTabber->Check())
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString();
			else
				$errorMessage .= "Custom tabber check unknown error!";

			$result->addError(new \Bitrix\Main\Entity\EntityError($errorMessage));
		}

		if (!$customDraggableBlocks->check())
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString();
			else
				$errorMessage .= "Custom draggable block check unknown error!";

			$result->addError(new \Bitrix\Main\Entity\EntityError($errorMessage));
		}

		if(isset($_POST["SHIPMENT"]) && $_POST["SHIPMENT"])
		{
			$dlvRes = Blocks\OrderShipment::updateData($order, $_POST['SHIPMENT']);

			if(!$dlvRes->isSuccess())
				$result->addErrors($dlvRes->getErrors());
		}

		if(isset($_POST["PAYMENT"]) && $_POST["PAYMENT"])
		{
			$payRes = Blocks\OrderPayment::updateData($order, $_POST['PAYMENT'], !$result->isSuccess());

			if(!$payRes->isSuccess())
				$result->addErrors($payRes->getErrors());
		}

		if($isSavingOperation && $result->isSuccess())
		{
			$res = OrderEdit::saveCoupons($order->getUserId(), $_POST);

			if(!$res)
				$result->addError(new \Bitrix\Main\Entity\EntityError("Can't save coupons!"));

			/* To apply discounts depended on paysystems, or delivery services */
			if (!($basket = $order->getBasket()))
				throw new \Bitrix\Main\ObjectNotFoundException('Entity "Basket" not found');

			$res = $basket->refreshData(array('PRICE', 'QUANTITY', 'COUPONS'));

			if(!$res->isSuccess())
				$result->addErrors($res->getErrors());
			/* * */

			$res = $order->save();

			if($res->isSuccess())
			{
				if(isset($_POST["BUYER_PROFILE_ID"]))
					$profileId = intval($_POST["BUYER_PROFILE_ID"]);
				else
					$profileId = 0;

				$profResult = OrderEdit::saveProfileData($profileId, $order, $_POST);
				\CSaleMobileOrderPush::send("ORDER_CREATED", array("ORDER_ID" => $order->getId()));

				$customTabber->SetArgs(array("ID" => $order->getId()));
				if (!$customTabber->Action())
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= "Custom tabber action unknown error!";
				}

				if (!$customDraggableBlocks->action())
				{
					if ($ex = $APPLICATION->GetException())
						$errorMessage .= $ex->GetString();
					else
						$errorMessage .= "Custom draggable block action unknown error!";
				}

				if(!empty($errorMessage))
					$_SESSION['SALE_ORDER_EDIT_ERROR'] = $errorMessage;

				if(isset($_POST["save"]))
					LocalRedirect("/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
				else
					LocalRedirect("/bitrix/admin/sale_order_edit.php?lang=".LANGUAGE_ID."&ID=".$order->getId().GetFilterParams("filter_", false));
			}
			else
			{
				$result->addErrors($res->getErrors());
			}
		}
	}
	else
	{
		$result->addError(new \Bitrix\Main\Entity\EntityError("Can't create order!"));
	}
}
elseif($createWithProducts)
{
	$showProfiles = true;
	$formData = array(
		"USER_ID" => $_GET["USER_ID"],
		"SITE_ID" => $_GET["SITE_ID"]
	);


	$formData["PRODUCT"] = array();
	$basketCode = 1;
	$userProfiles = array();

	if(isset($_GET["product"]) && is_array($_GET["product"]))
	{
		foreach($_GET["product"] as $productId => $quantity)
		{
			$productParams = Blocks\OrderBasket::getProductDetails(
				$productId, $quantity, $formData["USER_ID"], $formData["SITE_ID"]
			);

			if(
				!is_array($productParams)
				|| empty($productParams)
				|| intval($productParams["PRODUCT_ID"]) <= 0
				|| strlen($productParams["MODULE"]) <= 0
			)
			{
				continue;
			}

			$formData["PRODUCT"][$basketCode] = $productParams;
			$formData["PRODUCT"][$basketCode]["BASKET_CODE"] = $basketCode;
			$basketCode++;
		}
	}
	else
	{
		if(isset($_GET['FUSER_ID']) && intval($_GET['FUSER_ID']) > 0)
			$fuserId = $_GET['FUSER_ID'];
		else
			$fuserId = \Bitrix\Sale\Fuser::getIdByUserId($_GET["USER_ID"]);

		if(intval($fuserId) > 0)
		{
			$basketFilter = array(
				'filter' => array(
					'LID' => $_GET['SITE_ID'],
					'FUSER_ID' => intval($fuserId),
					'CAN_BUY' => "Y",
					'DELAY' => "N",
					'ORDER_ID' => null,
					'!MODULE' => false,
					'SET_PARENT_ID' => false,
				),
				'select' => array('PRODUCT_ID', 'QUANTITY', 'NAME'),
				'order' => array('ID' => 'ASC'),
			);

			$res = \Bitrix\Sale\Basket::getList($basketFilter);

			while($basketItem = $res->fetch())
			{
				$productParams = Blocks\OrderBasket::getProductDetails(
					$basketItem['PRODUCT_ID'], $basketItem['QUANTITY'], $formData["USER_ID"], $formData["SITE_ID"]
				);

				if(!is_array($productParams) || empty($productParams))
					continue;

				if(strlen($productParams['PRODUCT_ID']) <= 0)
				{
					$result->addError(
						new \Bitrix\Main\Error(
							Loc::getMessage(
								'SALE_OK_ORDER_CREATE_ERROR_NO_PRODUCT',
								array('##NAME##' => $basketItem['NAME'])
							)
						)
					);
					continue;
				}

				$formData["PRODUCT"][$basketCode] = $productParams;
				$formData["PRODUCT"][$basketCode]["BASKET_CODE"] = $basketCode;
				$basketCode++;
			}
		}
	}

	if(empty($formData["PRODUCT"]))
		unset($formData["PRODUCT"]);

	$res = new \Bitrix\Sale\Result();
	$order = OrderEdit::createOrderFromForm($formData, $USER->GetID(), false, array(), $res);
	$userProfiles = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getUserProfiles($_GET['USER_ID']);

	//Just get first available profile
	if($order && !empty($userProfiles))
	{
		$propCollection = $order->getPropertyCollection();
		$ptList = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getBuyerTypesList($order->getSiteId());
		$ptIndex = 0;

		foreach($userProfiles as $userPersonTypeId => $profiles)
		{
			if(!empty($ptList[$userPersonTypeId]))
			{
				reset($userProfiles[$userPersonTypeId]);
				$userProfile = current($userProfiles[$userPersonTypeId]);
				$profileId = key($userProfiles[$userPersonTypeId]);
				$order->setPersonTypeId($userPersonTypeId);

				foreach($userProfile as $propId => $propValue)
				{
					$property = $propCollection->getItemByOrderPropertyId($propId);

					if($property)
					{
						try
						{
							$property->setValue($propValue);
						}
						catch(\Exception $e)
						{}
					}
				}

				break;
			}
		}
	}

	if(!$order)
	{
		if(!$res->isSuccess())
			$result->addErrors($res->getErrors());
		else
			$result->addError(
				new \Bitrix\Main\Entity\EntityError(
					Loc::getMessage('SALE_OK_ORDER_CREATE_ERROR')
				)
			);
	}
}
elseif($isCopyingOrderOperation) // copy order
{
	/** @var \Bitrix\Sale\Order $originalOrder */
	$originalOrder = Bitrix\Sale\Order::load($ID);
	if ($originalOrder)
	{
		$order = \Bitrix\Sale\Order::create($originalOrder->getSiteId(), $originalOrder->getUserId(), $originalOrder->getCurrency());
		$order->setPersonTypeId($originalOrder->getPersonTypeId());

		$originalPropCollection = $originalOrder->getPropertyCollection();

		$properties['PROPERTIES'] = array();
		$files = array();

		/** @var \Bitrix\Sale\PropertyValue $prop */
		foreach ($originalPropCollection as $prop)
		{
			if ($prop->getField('TYPE') == 'FILE')
			{
				$propValue = $prop->getValue();
				if ($propValue)
				{
					$files[] = CAllFile::MakeFileArray($propValue['ID']);
					$properties['PROPERTIES'][$prop->getPropertyId()] = $propValue['ID'];
				}
			}
			else
			{
				$properties['PROPERTIES'][$prop->getPropertyId()] = $prop->getValue();
			}
		}

		$propCollection = $order->getPropertyCollection();
		$propCollection->setValuesFromPost($properties, $files);
		$originalBasket = $originalOrder->getBasket();
		$originalBasketItems = $originalBasket->getBasketItems();
		$basket = \Bitrix\Sale\Basket::create($originalOrder->getSiteId());
		$basket->setFUserId($originalBasket->getFUserId());

		/** @var \Bitrix\Sale\BasketItem $originalBasketItem */
		foreach($originalBasketItems as $originalBasketItem)
		{
			$item = $basket->createItem($originalBasketItem->getField("MODULE"), $originalBasketItem->getProductId());
			$item->setField('NAME', $originalBasketItem->getField('NAME'));

			$item->setFields(
				array_intersect_key(
					$originalBasketItem->getFields()->getValues(),
					array_flip(
						$originalBasketItem->getAvailableFields()
					)
				)
			);
		}

		$res = $order->setBasket($basket);

		if(!$res->isSuccess())
			$result->addErrors($res->getErrors());

		$paymentCollection = $originalOrder->getPaymentCollection();
		$originalPayment = $paymentCollection->current();

		if ($originalPayment)
		{
			$payment = $order->getPaymentCollection()->createItem();
			/** @var \Bitrix\Sale\Payment $payment */
			$payment->setField('PAY_SYSTEM_ID', $originalPayment->getPaymentSystemId());
		}

		$originalDeliveryId = 0;
		$shipmentCollection = $originalOrder->getShipmentCollection();
		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$originalDeliveryId = $shipment->getDeliveryId();
				$customPriceDelivery = $shipment->getField('CUSTOM_PRICE_DELIVERY');
				$basePrice = $shipment->getField('BASE_PRICE_DELIVERY');
				break;
			}
		}
		if ($originalDeliveryId > 0)
		{
			$shipment = $order->getShipmentCollection()->createItem();
			$shipment->setField('DELIVERY_ID', $originalDeliveryId);
			$shipment->setField('CUSTOM_PRICE_DELIVERY', $customPriceDelivery);
			$shipment->setField('BASE_PRICE_DELIVERY', $basePrice);
		}

		$order->getDiscount()->calculate();
	}
}

if(!$order)
{
	$order = \Bitrix\Sale\Order::create($siteId);
	$order->setPersonTypeId(
		Blocks\OrderBuyer::getDefaultPersonType(
			$siteId
		)
	);
}

if(strlen($siteName) > 0)
	$APPLICATION->SetTitle(str_replace("##SITE##", $siteName, Loc::getMessage("SALE_OK_TITLE_SITE")));
else
	$APPLICATION->SetTitle(Loc::getMessage("SALE_OK_TITLE_NO_SITE"));

CUtil::InitJSCore();
\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_edit.js");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

Blocks\OrderBasket::getCatalogMeasures();
// context menu
$aMenu = array();

$aMenu[] = array(
	"ICON" => "btn_list",
	"TEXT" => Loc::getMessage("SALE_OK_LIST"),
	"TITLE"=> Loc::getMessage("SALE_OK_LIST_TITLE"),
	"LINK" => "/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

//errors
$errorMessage = "";

if(!empty($_SESSION['SALE_ORDER_EDIT_ERROR']))
{
	$errorMessage = $_SESSION['SALE_ORDER_EDIT_ERROR']."<br>\n";
	unset($_SESSION['SALE_ORDER_EDIT_ERROR']);
}

if(!$result->isSuccess() && !$needFieldsRestore)
	foreach($result->getErrors() as $error)
		$errorMessage .= $error->getMessage()."<br>\n";

if(!empty($errorMessage))
{
	$admMessage = new CAdminMessage($errorMessage);
	echo $admMessage->Show();
}

//prepare blocks order
$defaultBlocksOrder = array(
	"basket",
	"buyer",
	"financeinfo",
	"delivery",
	"payment",
	"relprops",
	"additional",
	"statusorder",
);

$fastNavItems = array();

foreach($defaultBlocksOrder as $item)
	$fastNavItems[$item] = Loc::getMessage("SALE_OK_BLOCK_TITLE_".toUpper($item));

foreach($customDraggableBlocks->getBlocksBrief() as $blockId => $blockParams)
{
	$defaultBlocksOrder[] = $blockId;
	$fastNavItems[$blockId] = $blockParams['TITLE'];
}

$formId = "sale_order_create";
$basketPrefix = "sale_order_basket";

$orderBasket = new Blocks\OrderBasket($order,"BX.Sale.Admin.OrderBasketObj", $basketPrefix);

echo OrderEdit::getScripts($order, $formId);
echo Blocks\OrderBuyer::getScripts();
echo Blocks\OrderAdditional::getScripts();
echo Blocks\OrderPayment::getScripts();
echo Blocks\OrderShipment::getScripts();
echo Blocks\OrderFinanceInfo::getScripts();
echo $orderBasket->getScripts(false);
echo $customDraggableBlocks->getScripts();

echo OrderEdit::getFastNavigationHtml($fastNavItems);

$aTabs = array(
	array("DIV" => "tab_order", "TAB" => Loc::getMessage("SALE_OK_TAB_ORDER"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y"),
);

?><form method="POST" action="<?=$APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&SITE_ID=".$siteId.GetFilterParams("filter_", false)?>" name="<?=$formId?>_form" id="<?=$formId?>_form" enctype="multipart/form-data"><?
$tabControl = new CAdminTabControlDrag($formId, $aTabs, $moduleId, false, true);
$tabControl->AddTabs($customTabber);
$tabControl->Begin();
//TAB order --
$tabControl->BeginNextTab();
$blocksOrder = $tabControl->getCurrentTabBlocksOrder($defaultBlocksOrder);

?>
<tr><td>
	<input type="hidden" id="SITE_ID" name="SITE_ID" value="<?=htmlspecialcharsbx($siteId)?>">
	<input type="hidden" id="OLD_USER_ID" name="OLD_USER_ID" value="0">
	<input type="hidden" name="BASKET_PREFIX" value="<?=$basketPrefix?>">
	<?=bitrix_sessid_post()?>
	<div style="position: relative; vertical-align: top">
		<?$tabControl->DraggableBlocksStart();?>
		<?
		foreach ($blocksOrder as $blockCode)
		{
			echo '<a id="'.$blockCode.'"></a>';
			$tabControl->DraggableBlockBegin($fastNavItems[$blockCode], $blockCode);
			switch ($blockCode)
			{
				case "basket":
					echo $orderBasket->getEdit(false);
					break;
				case "buyer":
					echo Blocks\OrderBuyer::getEdit($order, $showProfiles, $profileId);
					break;
				case "delivery":

					$shipments = $order->getShipmentCollection();

					if(count($shipments) < 2)
						$order->getShipmentCollection()->createItem();

					/** @var \Bitrix\Sale\Shipment  $shipment*/
					foreach ($shipments as $shipment)
						if (!$shipment->isSystem())
							echo Blocks\OrderShipment::getEdit($shipment, 0, '', $_POST['SHIPMENT'][1]);

					break;
				case "payment":
					$payments = $order->getPaymentCollection();

					if(count($payments) == 0)
						$order->getPaymentCollection()->createItem();

					$index = 0;
					foreach ($payments as $payment)
						echo Blocks\OrderPayment::getEdit($payment, ++$index, $_POST['PAYMENT'][$index]);

					echo Blocks\OrderPayment::createButtonAddPayment('edit');
					break;
				case 'relprops' :
					echo Blocks\OrderBuyer::getPropsEdit($order);
					break;
				case "financeinfo":
					echo Blocks\OrderFinanceInfo::getView($order);
					break;
				case "additional":
					echo Blocks\OrderAdditional::getEdit($order, $formId."_form", 'ORDER');
					break;
				case "statusorder":
					echo Blocks\OrderStatus::getEditSimple($USER->GetID(), 'STATUS_ID', \Bitrix\Sale\OrderStatus::getInitialStatus());
					break;
				default:
					echo $customDraggableBlocks->getBlockContent($blockCode, $tabControl->selectedTab);
					break;
			}
			$tabControl->DraggableBlockEnd();
		}
		?>
	</div>
</td></tr>
<?

$tabControl->EndTab();

$tabControl->Buttons(
	array(
		"back_url" => "/bitrix/admin/sale_order_create.php?lang=".LANGUAGE_ID."&SITE_ID=".$siteId.GetFilterParams("filter_"))
);

$tabControl->End();
?>
<div style="display: none;">
	<?=$orderBasket->getSettingsDialogContent();?>
</div>

<?if(!$result->isSuccess() || $needFieldsRestore):?>
	<script type="text/javascript">
		BX.ready( function(){
			BX.Sale.Admin.OrderEditPage.restoreFormData(
				<?=CUtil::PhpToJSObject(OrderEdit::restoreFieldsNames(
						array_diff_key($_POST, array("USER_ID" => true))
					));
				?>
			);
		});
	</script>
<?endif;?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
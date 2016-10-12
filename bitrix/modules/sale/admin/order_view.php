<?
/**
 * @var  CUser $USER
 * @var  CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Helpers\Admin;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$saleOrder = null;
$moduleId = "sale";
$errorMsgs = array();

Loc::loadMessages(__FILE__);
Bitrix\Main\Loader::includeModule('sale');
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
$arUserGroups = $USER->GetUserGroupArray();

if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");

$allowedStatusesView = array();

//load order
if(!empty($_REQUEST["ID"]) && intval($_REQUEST["ID"]) > 0)
	$saleOrder = Bitrix\Sale\Order::load($_REQUEST["ID"]);

if($saleOrder)
	$allowedStatusesView = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));

if(!$saleOrder || !in_array($saleOrder->getField("STATUS_ID"), $allowedStatusesView))
	LocalRedirect("/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));

$ID = intval($_REQUEST["ID"]);
$boolLocked = \Bitrix\Sale\Order::isLocked($ID);

//Unlocking if we leave this page
if(isset($_REQUEST['unlock']) && 'Y' == $_REQUEST['unlock'])
{
	$lockStatusRes = \Bitrix\Sale\Order::getLockedStatus($ID);

	if($lockStatusRes->isSuccess())
		$lockStatusData = $lockStatusRes->getData();

	if(isset($lockStatusData['LOCK_STATUS'])
		&&
		(	$lockStatusData['LOCK_STATUS'] != \Bitrix\Sale\Order::SALE_ORDER_LOCK_STATUS_RED
			|| !isset($_REQUEST['target'])
		)
	)
	{
		$res = \Bitrix\Sale\Order::unlock($ID);

		if($res->isSuccess())
			\Bitrix\Sale\DiscountCouponsManager::clearByOrder($ID);
	}

	if(isset($_REQUEST['target']) && 'list' == $_REQUEST['target'])
		LocalRedirect("sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
	else
		LocalRedirect("sale_order_view.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
}

if ($boolLocked)
	$errorMsgs[] = Admin\OrderEdit::getLockingMessage($ID);
else
	\Bitrix\Sale\Order::lock($ID);

$customTabber = new CAdminTabEngine("OnAdminSaleOrderView", array("ID" => $ID));
$customDraggableBlocks = new CAdminDraggableBlockEngine('OnAdminSaleOrderViewDraggable', array('ORDER' => $saleOrder));

/** @var Bitrix\Sale\Order $saleOrder */
Admin\OrderEdit::initCouponsData(
	$saleOrder->getUserId(),
	$ID,
	null
);

CUtil::InitJSCore();
$APPLICATION->SetTitle(
	Loc::getMessage(
		"SALE_OVIEW_TITLE",
		array(
			"#ID#" => $saleOrder->getId(),
			"#NUM#" => strlen($saleOrder->getField('ACCOUNT_NUMBER')) > 0 ? $saleOrder->getField('ACCOUNT_NUMBER') : $saleOrder->getId(),
			"#DATE#" => $saleOrder->getDateInsert()->toString()
		)
	)
);

\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_edit.js");

ob_start();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/order_history.php");
$historyContent = ob_get_contents();
ob_end_clean();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/* context menu */
$aMenu = array();

$aMenu[] = array(
	"ICON" => "btn_list",
	"TEXT" => Loc::getMessage("SALE_OVIEW_TO_LIST"),
	"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_LIST_TITLE"),
	"LINK" => "/bitrix/admin/sale_order_view.php?unlock=Y&target=list&ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
);

if ($boolLocked && $saleModulePermissions >= 'W')
{
	$aMenu[] = array(
			"TEXT" => GetMessage("SALE_OVIEW_UNLOCK"),
			"LINK" => "/bitrix/admin/sale_order_view.php?ID=".$ID."&unlock=Y&lang=".LANGUAGE_ID.GetFilterParams("filter_"),
	);
}

$allowedStatusesUpdate = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

if(!$boolLocked && in_array($saleOrder->getField("STATUS_ID"), $allowedStatusesUpdate))
{
	$aMenu[] = array(
		"TEXT" => Loc::getMessage("SALE_OVIEW_TO_EDIT"),
		"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_EDIT_TITLE"),
		"LINK" => "/bitrix/admin/sale_order_edit.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
	);
}

$arSysLangs = array();
$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
while ($arLang = $db_lang->Fetch())
	$arSysLangs[] = $arLang["LID"];

$arReports = array();
$dirs = array(
	$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/",
	$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/"

);
foreach ($dirs as $dir)
{
	if (file_exists($dir))
	{
		if ($handle = opendir($dir))
		{
			while (($file = readdir($handle)) !== false)
			{
				$file_contents = '';
				if ($file == "." || $file == ".." || $file == ".access.php")
					continue;
				if (is_file($dir.$file) && ToUpper(substr($file, -4)) == ".PHP")
				{
					$rep_title = $file;
					if ($dir == $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/")
					{
						if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/reports/".$file))
							$file_contents = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/reports/".$file);
					}

					if (empty($file_contents))
						$file_contents = file_get_contents($dir.$file);

					$rep_langs = "";
					$arMatches = array();
					if (preg_match("#<title([\s]+langs[\s]*=[\s]*\"([^\"]*)\"|)[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches))
					{
						$arMatches[3] = Trim($arMatches[3]);
						if (strlen($arMatches[3]) > 0) $rep_title = $arMatches[3];
						$arMatches[2] = Trim($arMatches[2]);
						if (strlen($arMatches[2]) > 0) $rep_langs = $arMatches[2];
					}
					if (strlen($rep_langs) > 0)
					{
						$bContinue = true;
						foreach ($arSysLangs as $sysLang)
						{
							if (strpos($rep_langs, $sysLang) !== false)
							{
								$bContinue = false;
								break;
							}
						}

						if ($bContinue)
							continue;
					}

					$arReports[] = array(
						"TEXT" => $rep_title,
						"ONCLICK" => "window.open('/bitrix/admin/sale_order_print_new.php?&ORDER_ID=".$ID."&doc=".substr($file, 0, strlen($file) - 4)."&".bitrix_sessid_get()."', '_blank');"
					);
				}
			}
		}
		closedir($handle);
	}
}

$aMenu[] = array(
	"TEXT" => Loc::getMessage("SALE_OVIEW_TO_PRINT"),
	"TITLE"=> Loc::getMessage("SALE_OVIEW_TO_PRINT_TITLE"),
	"LINK" => "/bitrix/admin/sale_order_print.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_"),
	"MENU" => $arReports
);

$aMenu[] = array(
	"TEXT" => Loc::getMessage("SALE_OVIEW_ORDER_COPY"),
	"TITLE"=> Loc::getMessage("SALE_OVIEW_ORDER_COPY_TITLE"),
	"LINK" => '/bitrix/admin/sale_order_create.php?lang='.LANGUAGE_ID."&SITE_ID=".$saleOrder->getSiteId()."&ID=".$ID."&".bitrix_sessid_get().GetFilterParams("filter_")
);

if(CSaleOrder::CanUserDeleteOrder($ID, $arUserGroups, $USER->GetID()))
{
	if(!$boolLocked)
	{
		$aMenu[] = array(
			"TEXT" => Loc::getMessage("SALE_OVIEW_DELETE"),
			"TITLE"=> Loc::getMessage("SALE_OVIEW_DELETE_TITLE"),
			"LINK" => "javascript:if(confirm('".GetMessageJS("SALE_OVIEW_DEL_MESSAGE")."')) window.location='sale_order.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get().urlencode(GetFilterParams("filter_"))."'",
			"WARNING" => "Y"
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if(!empty($errorMsgs))
{
	$m = new CAdminMessage(array(
			"TYPE" => "ERROR",
			"MESSAGE" => implode("<br>\n", $errorMsgs),
			"HTML" => true
	));

	echo $m->Show();
}

//prepare blocks order
$defaultBlocksOrder = array(
	"statusorder",
	"buyer",
	"delivery",
	"financeinfo",
	"payment",
	"additional",
	"basket"
);

$fastNavItems = array();

foreach($defaultBlocksOrder as $item)
	$fastNavItems[$item] = Loc::getMessage("SALE_OVIEW_BLOCK_TITLE_".toUpper($item));

foreach($customDraggableBlocks->getBlocksBrief() as $blockId => $blockParams)
{
	$defaultBlocksOrder[] = $blockId;
	$fastNavItems[$blockId] = $blockParams['TITLE'];
}

$basketPrefix = "sale_order_basket";
$formId = "sale_order_view";

$orderBasket = new Admin\Blocks\OrderBasket(
	$saleOrder,
	"BX.Sale.Admin.OrderBasketObj",
	$basketPrefix,
	true,
	Admin\Blocks\OrderBasket::VIEW_MODE
);

echo Admin\OrderEdit::getScripts($saleOrder, $formId);
echo Admin\Blocks\OrderInfo::getScripts();
echo Admin\Blocks\OrderBuyer::getScripts();
echo Admin\Blocks\OrderPayment::getScripts();
echo Admin\Blocks\OrderStatus::getScripts($saleOrder, $USER->GetID());
echo Admin\Blocks\OrderAdditional::getScripts();
echo Admin\Blocks\OrderFinanceInfo::getScripts();
echo Admin\Blocks\OrderShipment::getScripts();
echo Admin\Blocks\OrderAnalysis::getScripts();
echo $orderBasket->getScripts(true);
echo $customDraggableBlocks->getScripts();

// navigation
echo Admin\OrderEdit::getFastNavigationHtml($fastNavItems);

// yellow block with brief
echo Admin\Blocks\OrderInfo::getView($saleOrder, $orderBasket);

// Problem block
if($saleOrder->getField("MARKED") == "Y" )
	echo Admin\OrderEdit::getProblemBlockHtml($saleOrder->getField("REASON_MARKED"), $saleOrder->getId());

$aTabs = array(
	array("DIV" => "tab_order", "TAB" => Loc::getMessage("SALE_OVIEW_TAB_ORDER"), "TITLE" => Loc::getMessage("SALE_OVIEW_TAB_ORDER"), "SHOW_WRAP" => "N", "IS_DRAGGABLE" => "Y"),
	array("DIV" => "tab_history", "TAB" => Loc::getMessage("SALE_OVIEW_TAB_HISTORY"), "TITLE" => Loc::getMessage("SALE_OVIEW_TAB_HISTORY")),
	array("DIV" => "tab_analysis", "TAB" => Loc::getMessage("SALE_OVIEW_TAB_ANALYSIS"), "TITLE" => Loc::getMessage("SALE_OVIEW_TAB_ANALYSIS"))
);

$tabControl = new CAdminTabControlDrag($formId, $aTabs, $moduleId, false, true);
$tabControl->AddTabs($customTabber);

$tabControl->Begin();

//TAB order --
$tabControl->BeginNextTab();
$blocksOrder = $tabControl->getCurrentTabBlocksOrder($defaultBlocksOrder);

$statusOnPaid = Bitrix\Main\Config\Option::get('sale', 'status_on_paid');
$statusOnAllowDelivery = Bitrix\Main\Config\Option::get('sale', 'status_on_allow_delivery');
$statusOnPaid2AllowDelivery = Bitrix\Main\Config\Option::get('sale', 'status_on_payed_2_allow_delivery');

$autoChangeStatus = 'Y';
if (empty($statusOnPaid) && (empty($statusOnAllowDelivery) || empty($statusOnPaid2AllowDelivery)))
	$autoChangeStatus = 'N';

?>
<tr><td>
	<input type="hidden" id="ID" name="ID" value="<?=$ID?>">
	<input type="hidden" id="SITE_ID" name="SITE_ID" value="<?=htmlspecialcharsbx($saleOrder->getSiteId())?>">
	<input type="hidden" id="AUTO_CHANGE_STATUS_ON_PAID" name="AUTO_CHANGE_STATUS_ON_PAID" value="<?=$autoChangeStatus;?>">
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
				case "statusorder":
					echo Admin\Blocks\OrderStatus::getEdit($saleOrder, $USER, true, true);
					break;
				case "buyer":
					echo Admin\Blocks\OrderBuyer::getView($saleOrder);
					break;
				case "delivery":
					\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_shipment_basket.js");
					echo '<div id="sale-adm-order-shipments-content"><img src="/bitrix/images/sale/admin-loader.gif"/></div>';
					echo Admin\Blocks\OrderShipment::createNewShipmentButton();

					break;
				case "financeinfo":
					echo Admin\Blocks\OrderFinanceInfo::getView($saleOrder, false);
					break;
				case "payment":
					$payments = $saleOrder->getPaymentCollection();
					$index = 0;

					foreach ($payments as $payment)
						echo Admin\Blocks\OrderPayment::getView($payment, $index++);

					echo Admin\Blocks\OrderPayment::createButtonAddPayment('view');
					break;
				case "additional":
					echo Admin\Blocks\OrderAdditional::getView($saleOrder, $formId."_form");
					break;
				case "basket":
					echo $orderBasket->getView();
					echo '<div style="display: none;">'.$orderBasket->settingsDialog->getHtml().'</div>';
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
//--TAB order
$tabControl->EndTab();

//TAB history --
$tabControl->BeginNextTab();
?><tr><td id="order-history">
		<?=$historyContent?>
</td></tr><?
//-- TAB history
$tabControl->EndTab();
//TAB analysis --
$tabControl->BeginNextTab();
?>
<tr>
	<td>
		<div style="position:relative; vertical-align:top" id="sale-adm-order-analysis-content">
			<img src="/bitrix/images/sale/admin-loader.gif"/>
		</div>
	</td>
</tr>
<?
//-- TAB analysis
$tabControl->EndTab();
$tabControl->End();

?>

<div style="display: none;">
	<?=$orderBasket->getSettingsDialogContent();?>
</div>

<script type="text/javascript">
	BX.ready( function(){
		BX.Sale.Admin.OrderAjaxer.sendRequest(
			BX.Sale.Admin.OrderEditPage.ajaxRequests.getOrderTails("<?=$saleOrder->getId()?>", "view", "<?=$basketPrefix?>"),
			true
		);
	});
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
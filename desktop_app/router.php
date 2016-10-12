<?
define("BX_IM_FULLSCREEN", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/public/desktop_app/router.php");

$APPLICATION->SetTitle(GetMessage("IM_ROUTER_PAGE_TITLE"));
$APPLICATION->IncludeComponent("bitrix:im.router", "", Array(), false, Array("HIDE_ICONS" => "Y"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>

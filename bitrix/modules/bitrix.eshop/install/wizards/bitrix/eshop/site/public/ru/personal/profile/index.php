<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("��������� ������������");
?><?$APPLICATION->IncludeComponent("bitrix:main.profile", "eshop", Array(
	"SET_TITLE" => "Y",	// ������������� ��������� ��������
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
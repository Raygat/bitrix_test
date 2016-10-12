<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<?php
	/** @var CMain $APPLICATION */
	use Bitrix\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);
	CJSCore::Init(array('ajax', 'viewer'));
	$APPLICATION->ShowHead();
	?>
</head>
<body style="height: 100%;margin: 0;padding: 0; background: #f5f5f5">
	<?=$arResult['REQUEST'];?>
</body>
</html>
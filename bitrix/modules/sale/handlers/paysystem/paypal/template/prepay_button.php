<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) 
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

?>
<input style='padding-top:7px;' type='image' src='<?=Loc::getMessage("SALE_HPS_PAYPAL_BUTTON_SRC")?> ' name='paypalbutton' value='<?=Loc::getMessage("SALE_HPS_PAYPAL_BUTTON");?>'>
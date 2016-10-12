<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent("bitrix:im.messenger", "content", Array(
	"DESIGN" => 'POPUP',
	"RECENT" => "Y"
), false, Array("HIDE_ICONS" => "Y"));

<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPSNMA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPSNMA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "SocNetMessageActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
);
?>

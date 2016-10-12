<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @global CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

$linked = array();
$listLinkedId = array();

$currentUserId = $GLOBALS["USER"]->getID();
$currentUserGroups = $GLOBALS["USER"]->getUserGroupArray();
$backUrl = $APPLICATION->getCurPageParam();
foreach($arResult["ELEMENTS_ROWS"] as $rowId => $rowData)
{
	if($arResult["BIZPROC"] == "Y")
	{
		$arDocumentStates = CBPDocument::getDocumentStates(
			BizProcDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"], $arResult["IBLOCK_ID"]),
			BizProcDocument::getDocumentComplexId($arParams["IBLOCK_TYPE_ID"], $rowData["data"]["ID"])
		);

		$USER_GROUPS = $currentUserGroups;
		if($rowData["data"]["~CREATED_BY"] == $currentUserId)
			$USER_GROUPS[] = "Author";

		$ii = 0;
		$html = "";
		$proccesses = false;
		if($arResult["PROCESSES"] && $arResult["USE_COMMENTS"])
		{
			$proccesses = true;
			$workflows = array();
		}
		foreach ($arDocumentStates as $kk => $vv)
		{
			if(!$vv["ID"])
				continue;

			if($proccesses && !empty($rowData["data"]["WORKFLOW_ID"]))
				$workflows[] = 'WF_'.$vv["ID"];

			$canViewWorkflow = CIBlockDocument::canUserOperateDocument(
				CBPCanUserOperateOperation::ViewWorkflow,
				$currentUserId,
				$rowData["data"]["ID"],
				array(
					"IBlockPermission" => $arResult["IBLOCK_PERM"],
					"AllUserGroups" => $USER_GROUPS,
					"DocumentStates" => $arDocumentStates,
					"WorkflowId" => $kk,
				)
			);
			if (!$canViewWorkflow)
				continue;

			if(strlen($vv["TEMPLATE_NAME"]) > 0)
				$html .= "<b>".$vv["TEMPLATE_NAME"]."</b>:<br />";
			else
				$html .= "<b>".(++$ii)."</b>:<br />";

			$url = CHTTP::urlAddParams(str_replace(
				array("#list_id#", "#document_state_id#", "#group_id#"),
				array($arResult["IBLOCK_ID"], $vv["ID"], $arParams["SOCNET_GROUP_ID"]),
				$arParams["~BIZPROC_LOG_URL"]
			),
				array("back_url" => $backUrl),
				array("skip_empty" => true, "encode" => true)
			);

			$html .= "<a href=\"".htmlspecialcharsbx($url)."\">".(strlen($vv["STATE_TITLE"]) > 0 ?
					$vv["STATE_TITLE"] : $vv["STATE_NAME"])."</a><br />";
		}

		if ($proccesses)
		{
			$workflows = array_unique($workflows);
			if ($workflows)
			{
				$iterator = CForumTopic::getList(array(), array("@XML_ID" => $workflows));
				while ($row = $iterator->fetch())
					$arResult["COMMENTS_COUNT"][$row["XML_ID"]] = $row["POSTS"];
				$rowData["data"]["COMMENTS"] = '<div class="bp-comments"><a href="#" onclick="if (BX.Bizproc.showWorkflowInfoPopup) return BX.Bizproc.showWorkflowInfoPopup(\''.$rowData["data"]["WORKFLOW_ID"].'\')"><span class="bp-comments-icon"></span>'
					.(!empty($arResult["COMMENTS_COUNT"]['WF_'.$rowData["data"]["WORKFLOW_ID"]]) ? (int) $arResult["COMMENTS_COUNT"]['WF_'.$rowData["data"]["WORKFLOW_ID"]] : '0')
					.'</a></div>';
			}
		}

		$rowData["data"]["BIZPROC"] = $html;
	}

	foreach($rowData["data"] as $fieldId => $fieldValue)
	{
		$arField = $arResult["FIELDS"][$fieldId];

		if($fieldId == "PREVIEW_PICTURE" || $fieldId == "DETAIL_PICTURE")
		{
			$obFile = new CListFile(
				$arResult["IBLOCK_ID"],
				0, //section_id
				$rowData["data"]["ID"],
				$fieldId,
				$fieldValue
			);
			$obFile->setSocnetGroup($arParams["SOCNET_GROUP_ID"]);

			$obFileControl = new CListFileControl($obFile, $fieldId);

			$fieldValue = '<nobr>'.$obFileControl->getHTML(array(
					'show_input' => false,
					'max_size' => 1024000,
					'max_width' => 50,
					'max_height' => 50,
					'url_template' => $arParams["~LIST_FILE_URL"],
					'a_title' => GetMessage("CT_BLL_ENLARGE"),
					'download_text' => GetMessage("CT_BLL_DOWNLOAD"),
				)).'</nobr>';
		}
		elseif($fieldId == "IBLOCK_SECTION_ID")
		{
			if(array_key_exists($fieldValue, $arResult["SECTIONS"]))
			{
				$fieldValue = '<a href="'.str_replace(
						array("#list_id#", "#section_id#", "#group_id#"),
						array($arResult["IBLOCK_ID"], $fieldValue, $arParams["SOCNET_GROUP_ID"]),
						$arParams['LIST_URL']
					).'">'.$arResult["SECTIONS"][$fieldValue]["NAME"].'</a>';
			}
			else
			{
				$fieldValue = "";
			}
		}
		elseif($arField["TYPE"] == "F")
		{
			if(is_array($fieldValue))
			{
				foreach($fieldValue as $ii => $file)
				{
					$obFile = new CListFile(
						$arResult["IBLOCK_ID"],
						0, //section_id
						$rowData["data"]["ID"],
						$fieldId,
						$file
					);
					$obFile->setSocnetGroup($arParams["SOCNET_GROUP_ID"]);

					$obFileControl = new CListFileControl($obFile, $fieldId);

					$fieldValue[$ii] = '<nobr>'.$obFileControl->getHTML(array(
							'show_input' => false,
							'max_size' => 1024000,
							'max_width' => 50,
							'max_height' => 50,
							'url_template' => $arParams["~LIST_FILE_URL"],
							'a_title' => GetMessage("CT_BLL_ENLARGE"),
							'download_text' => GetMessage("CT_BLL_DOWNLOAD"),
						)).'</nobr>';
				}
			}
			else
			{
				$obFile = new CListFile(
					$arResult["IBLOCK_ID"],
					0, //section_id
					$rowData["data"]["ID"],
					$fieldId,
					$fieldValue
				);
				$obFile->setSocnetGroup($arParams["SOCNET_GROUP_ID"]);

				$obFileControl = new CListFileControl($obFile, $fieldId);

				$fieldValue = '<nobr>'.$obFileControl->getHTML(array(
						'show_input' => false,
						'max_size' => 1024000,
						'max_width' => 50,
						'max_height' => 50,
						'url_template' => $arParams["~LIST_FILE_URL"],
						'a_title' => GetMessage("CT_BLL_ENLARGE"),
						'download_text' => GetMessage("CT_BLL_DOWNLOAD"),
					)).'</nobr>';
			}
		}
		elseif(preg_match("/^(G|E|E:)/", $arField["TYPE"]))
		{
			$linked[$rowId][$fieldId]["TYPE"] = $arField["TYPE"];
			$linked[$rowId][$fieldId]["FIELD_VALUE"] = array();
			if(!is_array($listLinkedId[$arField["TYPE"]]))
				$listLinkedId[$arField["TYPE"]] = array();
			if(is_array($fieldValue))
			{
				foreach($fieldValue as $valueId)
				{
					$linked[$rowId][$fieldId]["FIELD_VALUE"][] = $valueId;
					if(!in_array($valueId, $listLinkedId[$arField["TYPE"]]))
						$listLinkedId[$arField["TYPE"]][] = $valueId;
				}
			}
			elseif($fieldValue > 0)
			{
				$linked[$rowId][$fieldId]["FIELD_VALUE"][] = $fieldValue;
				if(!in_array($fieldValue, $listLinkedId[$arField["TYPE"]]))
					$listLinkedId[$arField["TYPE"]][] = $fieldValue;
			}
			continue;
		}

		$arResult["ELEMENTS_ROWS"][$rowId]["data"][$fieldId] = $fieldValue;

		if(is_array($fieldValue))
		{
			if(count($fieldValue) > 1)
				$arResult["ELEMENTS_ROWS"][$rowId]["data"][$fieldId] = implode("&nbsp;/<br>", $fieldValue);
			else
				$arResult["ELEMENTS_ROWS"][$rowId]["data"][$fieldId] = $fieldValue[0];
		}
	}
}

/* Get name by id for fields */
if($listLinkedId)
{
	foreach($listLinkedId as $fieldType => $listId)
	{
		$listName = array();
		if(preg_match("/^(E|E:)/", $fieldType))
		{
			$object = CIBlockElement::getList(
				array(),
				array("=ID" => $listId),
				false,
				false,
				array("ID", "NAME", "IBLOCK_ID")
			);
			while($result = $object->getNext())
			{
				$urlElement = str_replace(
					array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
					array($result["IBLOCK_ID"], 0, $result["ID"], $arParams["SOCNET_GROUP_ID"]),
					$arParams["LIST_ELEMENT_URL"]
				);
				$listName[$result["ID"]] = '<a href="'.$urlElement.'">'.$result["NAME"].'</a>';
			}
		}
		elseif($fieldType == "G")
		{
			$object = CIBlockSection::getList(array(), array("=ID" => $listId));
			while($result = $object->getNext())
				$listName[$result["ID"]] = $result["NAME"];
		}
		if($listName)
		{
			foreach($linked as $rowId => $rowData)
			{
				foreach($rowData as $fieldId => $field)
				{
					if($field["TYPE"] != $fieldType)
						continue;
					$preview = array();
					foreach($field['FIELD_VALUE'] as $valueId)
						$preview[] = $listName[$valueId];
					$arResult["ELEMENTS_ROWS"][$rowId]["data"][$fieldId] = implode("&nbsp;/<br>", $preview);
				}
			}
		}
	}
}
?>
<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2016 Bitrix
 */

use Bitrix\Main,
	Bitrix\Main\Config,
	Bitrix\Main\Localization,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Sale,
	Bitrix\Iblock,
	Bitrix\Main\Data,
	Bitrix\Sale\Location;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class PersonalProfileDetail extends CBitrixComponent
{
	const E_SALE_MODULE_NOT_INSTALLED = 10000;

	/** @var  Main\ErrorCollection $errorCollection*/
	protected $errorCollection;

	protected $idProfile;

	/**
	 * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
	 * @param $params		Parameters of component.
	 * @return array		Checked and valid parameters.
	 */
	public function onPrepareComponentParams($params)
	{
		global $APPLICATION;

		$this->errorCollection = new Main\ErrorCollection();

		$this->idProfile = 0;

		if (isset($params['ID']) && $params['ID'] > 0)
		{
			$this->idProfile = (int)$params['ID'];
		}

		if (isset($params['PATH_TO_LIST']))
		{
			$params['PATH_TO_LIST'] = trim($params['PATH_TO_LIST']);
		}
		elseif ($this->idProfile)
		{
			$params['PATH_TO_LIST'] = htmlspecialcharsbx($APPLICATION->GetCurPage());
		}
		else
		{
			return false;
		}

		if ($params["PATH_TO_DETAIL"] !== '')
		{
			$params["PATH_TO_DETAIL"] = trim($params["PATH_TO_DETAIL"]);
		}
		else
		{
			$params["PATH_TO_DETAIL"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?ID=#ID#");
		}

		return $params;
	}

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		$this->setFrameMode(false);

		$this->checkRequiredModules();

		if (!$USER->IsAuthorized())
		{
			$APPLICATION->AuthForm(Loc::getMessage("SALE_ACCESS_DENIED"));
		}

		$request = Main\Application::getInstance()->getContext()->getRequest();
		$request->addFilter(new Main\Web\PostDecodeFilter);

		if ($this->arParams["SET_TITLE"] === 'Y')
		{
			$APPLICATION->SetTitle(Loc::getMessage("SPPD_TITLE").$this->idProfile);
		}

		if ($this->idProfile <= 0 || $request->get('reset'))
		{
			LocalRedirect($this->arParams["PATH_TO_LIST"]);
		}

		$userProperties = Sale\OrderUserProperties::getList(
			array(
				'order' => array("DATE_UPDATE" => "DESC"),
				'filter' => array(
					"ID" => $this->idProfile,
					"USER_ID" => (int)($USER->GetID())
				),
				"select" => array("*")
			)
		);

		if ($userOrderProperties = $userProperties->fetch())
		{
			if ($request->isPost() && ($request->get("save") || $request->get("apply"))	&& check_bitrix_sessid())
			{
				$this->updateProfileProperties($request, $userOrderProperties);
			}

			$this->fillResultArray($userOrderProperties);
		}
		else
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage("SALE_NO_PROFILE")));
		}

		$this->formatResultErrors();

		$this->includeComponentTemplate();
	}

	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function checkRequiredModules()
	{
		if (!Loader::includeModule('sale'))
		{
			throw new Main\SystemException(Loc::getMessage("SALE_MODULE_NOT_INSTALL"), self::E_SALE_MODULE_NOT_INSTALLED);
		}
	}

	/**
	 * @param Main\HttpRequest $request
	 * @param array $userOrderProperties
	 * @return void
	 */
	protected function updateProfileProperties($request, $userOrderProperties)
	{
		$fieldValues = $this->prepareUpdatingProperties($request, $userOrderProperties);

		if ($this->errorCollection->isEmpty())
		{
			$this->executeUpdatingProperties($request, $fieldValues);
		}

		if ($this->errorCollection->isEmpty())
		{
			if (strlen($request->get("save")) > 0)
			{
				LocalRedirect($this->arParams["PATH_TO_LIST"]);
			}
			elseif (strlen($request->get("apply")) > 0)
			{
				LocalRedirect(CComponentEngine::makePathFromTemplate($this->arParams["PATH_TO_DETAIL"], Array("ID" => $this->idProfile)));
			}
		}
	}

	/**
	 * Fill $arResult array for output in template 
	 * @param $property
	 * @throws Main\ArgumentException
	 * @return void
	 */
	protected function fillResultArray($property)
	{
		$this->arResult["ORDER_PROPS"] = array();

		$this->arResult = $property;

		$this->arResult["TITLE"] = Loc::getMessage("SPPD_PROFILE_NO", array("#ID#" => $property["ID"]));

		$personType = Sale\PersonType::load(SITE_ID, $property["PERSON_TYPE_ID"]);
		$this->arResult["PERSON_TYPE"] = $personType[$property["PERSON_TYPE_ID"]];
		$this->arResult["PERSON_TYPE"]["NAME"] = htmlspecialcharsbx($this->arResult["PERSON_TYPE"]["NAME"]);

		$locationValue = array();

		$locationDb = CSaleLocation::GetList(
			array("SORT" => "ASC", "COUNTRY_NAME_LANG" => "ASC", "CITY_NAME_LANG" => "ASC"),
			array(),
			LANGUAGE_ID
		);
		while ($location = $locationDb->Fetch())
		{
			$locationValue[] = $location;
		}

		$arrayTmp = array();

		$orderPropertiesListGroup = CSaleOrderPropsGroup::GetList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			array("PERSON_TYPE_ID" => $property["PERSON_TYPE_ID"]),
			false,
			false,
			array("ID", "PERSON_TYPE_ID", "NAME", "SORT")
		);
		while ($orderPropertyGroup = $orderPropertiesListGroup->GetNext())
		{
			$arrayTmp[$orderPropertyGroup["ID"]] = $orderPropertyGroup;
			$orderPropertiesList = CSaleOrderProps::GetList(
				array("SORT" => "ASC", "NAME" => "ASC"),
				array(
					"PERSON_TYPE_ID" => $property["PERSON_TYPE_ID"],
					"PROPS_GROUP_ID" => $orderPropertyGroup["ID"],
					"USER_PROPS" => "Y", "ACTIVE" => "Y", "UTIL" => "N"
				),
				false,
				false,
				array("ID", "PERSON_TYPE_ID", "NAME", "TYPE", "REQUIED", "DEFAULT_VALUE", "SORT", "USER_PROPS",
					"IS_LOCATION", "PROPS_GROUP_ID", "SIZE1", "SIZE2", "DESCRIPTION", "IS_EMAIL", "IS_PROFILE_NAME",
					"IS_PAYER", "IS_LOCATION4TAX", "CODE", "SORT")
			);
			while ($orderProperty = $orderPropertiesList->GetNext())
			{
				if ($orderProperty["REQUIED"] == "Y" || $orderProperty["IS_EMAIL"] == "Y" || $orderProperty["IS_PROFILE_NAME"] == "Y" || $orderProperty["IS_LOCATION"] == "Y" || $orderProperty["IS_PAYER"] == "Y")
					$orderProperty["REQUIED"] = "Y";
				if (in_array($orderProperty["TYPE"], Array("SELECT", "MULTISELECT", "RADIO")))
				{
					$dbVars = CSaleOrderPropsVariant::GetList(($by = "SORT"), ($order = "ASC"), Array("ORDER_PROPS_ID" => $orderProperty["ID"]));
					while ($vars = $dbVars->GetNext())
						$orderProperty["VALUES"][] = $vars;
				}
				elseif ($orderProperty["TYPE"] == "LOCATION")
				{
					$orderProperty["VALUES"] = $locationValue;
				}
				$arrayTmp[$orderPropertyGroup["ID"]]["PROPS"][] = $orderProperty;
			}

			$this->arResult["ORDER_PROPS"] = $arrayTmp;

			// get prop values
			$propertiesValueList = Array();

			$resultUserProperties = CSaleOrderUserPropsValue::GetList(
				array("SORT" => "ASC"),
				array("USER_PROPS_ID" => $property["ID"]),
				false,
				false,
				array("ID", "ORDER_PROPS_ID", "VALUE", "SORT", "USER_PROPS_ID")
			);
			while ($userProperty = $resultUserProperties->GetNext())
			{
				$propertiesValueList["ORDER_PROP_" . $userProperty["ORDER_PROPS_ID"]] = $userProperty["VALUE"];
			}

			$this->arResult["ORDER_PROPS_VALUES"] = $propertiesValueList;
		}
	}

	/**
	 * Move all errors to $this->arResult, if there were any
	 * @return void
	 */
	protected function formatResultErrors()
	{
		if (!$this->errorCollection->isEmpty())
		{
			/** @var Main\Error $error */
			foreach ($this->errorCollection->toArray() as $error)
			{
				$this->arResult['ERROR_MESSAGE'] .= $error->getMessage();
			}
		}
	}

	/**
	 * Check value required params of property
	 * @param $property
	 * @param $currentValue
	 * @return bool
	 */
	protected function checkProperty($property, $currentValue)
	{
		if ($property["TYPE"] == "LOCATION" && $property["IS_LOCATION"] == "Y")
		{
			if ((int)($currentValue) <= 0)
				return false;
		}
		elseif ($property["IS_PROFILE_NAME"] == "Y")
		{
			if (strlen(trim($currentValue)) <= 0)
				return false;
		}
		elseif ($property["IS_PAYER"] == "Y")
		{
			if (strlen(trim($currentValue)) <= 0)
				return false;
		}
		elseif ($property["IS_EMAIL"] == "Y")
		{
			if (strlen(trim($currentValue)) <= 0 || !check_email(trim($currentValue)))
				return false;
		}
		elseif ($property["REQUIED"] == "Y")
		{
			if ($property["TYPE"] == "LOCATION")
			{
				if ((int)($currentValue) <= 0)
					return false;
			}
			elseif ($property["TYPE"] == "MULTISELECT")
			{
				if (!is_array($currentValue) || count($currentValue) <= 0)
					return false;
			}
			else
			{
				if (strlen($currentValue) <= 0)
					return false;
			}
		}

		return true;
	}

	/**
	 * Create array properties for updating and check required properties
	 * @param Main\HttpRequest $request
	 * @param array $userOrderProperties
	 * @return array $fieldValues
	 */
	protected function prepareUpdatingProperties($request, $userOrderProperties)
	{
		if (strlen($request->get("NAME")) <= 0)
		{
			$this->errorCollection->setError(new Main\Error(Loc::getMessage("SALE_NO_NAME")."<br>"));
		}

		$fieldValues = array();
		$orderPropertiesList = CSaleOrderProps::GetList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			array(
				"PERSON_TYPE_ID" => $userOrderProperties["PERSON_TYPE_ID"],
				"USER_PROPS" => "Y", "ACTIVE" => "Y", "UTIL" => "N"
			),
			false,
			false,
			array("ID", "NAME", "TYPE", "REQUIED", "MULTIPLE", "IS_LOCATION", "PROPS_GROUP_ID", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "CODE", "SORT")
		);

		while ($orderProperty = $orderPropertiesList->GetNext())
		{
			$currentValue = $request->get("ORDER_PROP_" . $orderProperty["ID"]);

			if ($this->checkProperty($orderProperty, $currentValue))
			{
				$fieldValues[$orderProperty["ID"]] = array(
					"USER_PROPS_ID" => $this->idProfile,
					"ORDER_PROPS_ID" => $orderProperty["ID"],
					"NAME" => $orderProperty["NAME"],
					'MULTIPLE' => $orderProperty["MULTIPLE"]
				);

				if ($orderProperty["TYPE"] === 'FILE')
				{
					$fileIdList = array();

					$currentValue = $request->getFile("ORDER_PROP_" . $orderProperty["ID"]);

					foreach ($currentValue['name'] as $key => $fileName)
					{
						if (strlen($fileName) > 0)
						{
							$fileArray = array(
								'name' => $fileName,
								'type' => $currentValue['type'][$key],
								'tmp_name' => $currentValue['tmp_name'][$key],
								'error' => $currentValue['error'][$key],
								'size' => $currentValue['size'][$key],
							);

							$fileIdList[] = CFile::SaveFile($fileArray, "/sale/profile/");
						}
					}

					$fieldValues[$orderProperty["ID"]]['VALUE'] = $fileIdList;
				}
				elseif ($orderProperty['TYPE'] == "MULTISELECT")
				{
					$fieldValues[$orderProperty["ID"]]['VALUE'] = implode(',',$currentValue);
				}
				else
				{
					$fieldValues[$orderProperty["ID"]]['VALUE'] = $currentValue;
				}
			}
			else
			{
				$this->errorCollection->setError(new Main\Error(Loc::getMessage("SALE_NO_FIELD") . " \"" . $orderProperty["NAME"] . "\".<br />"));
			}
		}

		return $fieldValues;
	}

	/**
	 * Update and add profile properties
	 * @param Main\HttpRequest $request
	 * @param $fieldValues
	 * @return void
	 */
	protected function executeUpdatingProperties($request, $fieldValues)
	{
		if ($this->errorCollection->isEmpty())
		{
			$saleProps = new \CSaleOrderUserProps;

			if (!$saleProps->Update($this->idProfile, array("NAME" => trim($request->get("NAME")))))
			{
				$this->errorCollection->setError(new Main\Error(Loc::getMessage("SALE_ERROR_EDIT_PROF") . "<br />"));
				return;
			}
		}

		$updatedValues = array();
		$saleOrderUserPropertiesValue = new CSaleOrderUserPropsValue;
		$userPropertiesList = $saleOrderUserPropertiesValue::GetList(
			array("SORT" => "ASC"),
			array("USER_PROPS_ID" => $this->idProfile),
			false,
			false,
			array("ID", "ORDER_PROPS_ID", "VALUE", "SORT", "PROP_TYPE")
		);

		while ($propertyValues = $userPropertiesList->Fetch())
		{
			if ($propertyValues['PROP_TYPE'] === 'FILE')
			{
				$baseArray = unserialize(htmlspecialchars_decode($propertyValues['VALUE']));
				if ($idFileDeletingList = $request->get("ORDER_PROP_" . $propertyValues["ORDER_PROPS_ID"] . "_del"))
				{
					$propertyValues['VALUE'] = $this->deleteFromPropertyTypeFile($idFileDeletingList, $baseArray);
				}

				$inputArray = $fieldValues[$propertyValues["ORDER_PROPS_ID"]];

				if (count($inputArray['VALUE']) > 0)
				{
					if ($inputArray['MULTIPLE'] === 'Y')
					{
						foreach ($inputArray['VALUE'] as $value)
						{
							if (!in_array($value, $baseArray))
							{
								$baseArray[] = $value;
							}
						}

						$fieldValues[$propertyValues["ORDER_PROPS_ID"]]['VALUE'] = serialize($baseArray);
					}
					else
					{
						CFile::Delete($propertyValues['VALUE']);
						$fieldValues[$propertyValues["ORDER_PROPS_ID"]]['VALUE'] = serialize($inputArray['VALUE']);
					}
				}
				else
				{
					$fieldValues[$propertyValues["ORDER_PROPS_ID"]]['VALUE'] = $propertyValues['VALUE'];
				}
			}

			$saleOrderUserPropertiesValue->Update(
				$propertyValues["ID"],
				array("VALUE" => $fieldValues[$propertyValues["ORDER_PROPS_ID"]]['VALUE'])
			);

			$updatedValues[$propertyValues["ORDER_PROPS_ID"]] = $fieldValues[$propertyValues["ORDER_PROPS_ID"]];
		}

		if ($newValues = array_diff_key($fieldValues, $updatedValues))
		{
			foreach ($newValues as $value)
			{
				unset($value['MULTIPLE']);
				$saleOrderUserPropertiesValue->Add($value);
			}
		}
	}

	/**
	 * Delete id's files from property with type "File"
	 * @param string $idFileDeletingList
	 * @param array $baseArray
	 * @return string $newValue
	 */
	protected function deleteFromPropertyTypeFile($idFileDeletingList, $baseArray)
	{
		$idFileDeletingList = explode(';', $idFileDeletingList);
		foreach ($idFileDeletingList as $idDelete)
		{
			$key = array_search($idDelete, $baseArray);
			if ($key !== false)
			{
				CFile::Delete($baseArray[$key]);
				unset($baseArray[$key]);
			}
		}
		$newValue = serialize($baseArray);
		return $newValue;
	}
}
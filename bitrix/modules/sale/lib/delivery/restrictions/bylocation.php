<?php
namespace Bitrix\Sale\Delivery\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Internals\CollectableEntity;

Loc::loadMessages(__FILE__);

/**
 * Class ByLocation
 * Restricts delivery by location(s)
 * @package Bitrix\Sale\Delivery\Restrictions
 */
class ByLocation extends Base
{
	public static $easeSort = 200;

	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_LOCATION_NAME");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_LOCATION_DESCRIPT");
	}

	/**
	 * This function should accept only location CODE, not ID, being a part of modern API
	 */
	public static function check($locationCode, array $restrictionParams, $deliveryId = 0)
	{
		if(intval($deliveryId) <= 0)
			return true;

		if(strlen($locationCode) <= 0)
			return false;

		try
		{
			return DeliveryLocationTable::checkConnectionExists(
				intval($deliveryId),
				$locationCode,
				array(
					'LOCATION_LINK_TYPE' => 'AUTO'
				)
			);
		}
		catch(\Bitrix\Sale\Location\Tree\NodeNotFoundException $e)
		{
			return false;
		}
	}

	protected static function extractParams(CollectableEntity $shipment)
	{
		/** @var \Bitrix\Sale\Order $order */
		$order = $shipment->getCollection()->getOrder();

		if(!$props = $order->getPropertyCollection())
			return '';

		if(!$locationProp = $props->getDeliveryLocation())
			return '';

		if(!$locationCode = $locationProp->getValue())
			return '';

		return $locationCode;
	}

	protected static function prepareParamsForSaving(array $params = array(), $deliveryId = 0)
	{
		if($deliveryId > 0)
		{
			$arLocation = array();

			if(!!\CSaleLocation::isLocationProEnabled())
			{
				if(strlen($params["LOCATION"]['L']))
					$LOCATION1 = explode(':', $params["LOCATION"]['L']);

				if(strlen($params["LOCATION"]['G']))
					$LOCATION2 = explode(':', $params["LOCATION"]['G']);
			}

			if (isset($LOCATION1) && is_array($LOCATION1) && count($LOCATION1) > 0)
			{
				$arLocation["L"] = array();
				$locationCount = count($LOCATION1);

				for ($i = 0; $i<$locationCount; $i++)
					if (strlen($LOCATION1[$i]))
						$arLocation["L"][] = $LOCATION1[$i];
			}

			if (isset($LOCATION2) && is_array($LOCATION2) && count($LOCATION2) > 0)
			{
				$arLocation["G"] = array();
				$locationCount = count($LOCATION2);

				for ($i = 0; $i<$locationCount; $i++)
					if (strlen($LOCATION2[$i]))
						$arLocation["G"][] = $LOCATION2[$i];

			}

			DeliveryLocationTable::resetMultipleForOwner($deliveryId, $arLocation);
		}

		return array();
	}

	public static function getParamsStructure($deliveryId = 0)
	{

		$result =  array(
			"LOCATION" => array(
				"TYPE" => "LOCATION_MULTI"
				//'LABEL' => Loc::getMessage("SALE_DLVR_RSTR_BY_LOCATION_LOC"),
			)
		);

		if($deliveryId > 0 )
			$result["LOCATION"]["DELIVERY_ID"] = $deliveryId;

		return $result;
	}

	public static function save(array $fields, $restrictionId = 0)
	{
		$fields["PARAMS"] = self::prepareParamsForSaving($fields["PARAMS"], $fields["SERVICE_ID"]);
		return parent::save($fields, $restrictionId);
	}

	public static function delete($restrictionId, $deliveryId = 0)
	{
		DeliveryLocationTable::resetMultipleForOwner($deliveryId);
		return parent::delete($restrictionId);
	}
}
<?php
namespace Bitrix\Sale\Services\Base;

use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\Delivery\Restrictions\Manager;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Internals\ServiceRestrictionTable;

/**
 * Class RestrictionBase.
 * Base class for payment and delivery services restrictions.
 * @package Bitrix\Sale\Services
 */
abstract class Restriction {

	/** @var int
	 * 100 - lightweight - just compare with params
	 * 200 - middleweight - may be use base queries
	 * 300 - hardweight - use base, and/or hard calculations
	 * */
	public static $easeSort = 100;

	/**
	 * @return string
	 * @throws NotImplementedException
	 */
	public static function getClassTitle()
	{
		throw new NotImplementedException;
	}

	/**
	 * @return string
	 * @throws NotImplementedException
	 */
	public static function getClassDescription()
	{
		throw new NotImplementedException;
	}

	/**
	 * @param $params
	 * @param array $restrictionParams
	 * @param int $serviceId
	 * @return bool
	 * @throws NotImplementedException
	 */
	protected static function check($params, array $restrictionParams, $serviceId = 0)
	{
		throw new NotImplementedException;
	}

	public static function checkByEntity(CollectableEntity $entity, array $restrictionParams, $mode, $serviceId = 0)
	{
		$severity = static::getSeverity($mode);

		if($severity == RestrictionManager::SEVERITY_NONE)
			return RestrictionManager::SEVERITY_NONE;

		$entityRestrictionParams = static::extractParams($entity);
		$res = static::check($entityRestrictionParams, $restrictionParams, $serviceId);
		return $res ? RestrictionManager::SEVERITY_NONE : $severity;
	}

	/**
	 * @param CollectableEntity $entity
	 * @return mixed
	 * @throws NotImplementedException
	 */
	protected static function extractParams(CollectableEntity $entity)
	{
		throw new NotImplementedException;
	}

	/**
	 * Returns params structure to show it to user
	 * @return array
	 */
	public static function getParamsStructure($entityId = 0)
	{
		return array();
	}

	/** ? */
	public static function prepareParamsValues(array $paramsValues, $entityId = 0)
	{
		return $paramsValues;
	}

	public static function save(array $fields, $restrictionId = 0)
	{
		$fields["CLASS_NAME"] = '\\'.get_called_class();

		if($restrictionId > 0)
			$res = \Bitrix\Sale\Internals\ServiceRestrictionTable::update($restrictionId, $fields);
		else
			$res = \Bitrix\Sale\Internals\ServiceRestrictionTable::add($fields);

		return $res;
	}

	public static function delete($restrictionId, $entityId = 0)
	{
		return \Bitrix\Sale\Internals\ServiceRestrictionTable::delete($restrictionId);
	}

	public static function getSeverity($mode)
	{
		$result = RestrictionManager::SEVERITY_STRICT;

		if($mode == RestrictionManager::MODE_MANAGER)
			return RestrictionManager::SEVERITY_SOFT;

		return $result;
	}

	public static function prepareData(array $servicesIds)
	{
		return true;
	}
}
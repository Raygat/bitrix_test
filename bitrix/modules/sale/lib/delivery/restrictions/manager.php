<?
namespace Bitrix\Sale\Delivery\Restrictions;

use Bitrix\Sale\Internals\ServiceRestrictionTable;

class Manager extends \Bitrix\Sale\Services\Base\RestrictionManager
{
	protected static $classNames = null;

	protected static function getServiceType()
	{
		return self::SERVICE_TYPE_SHIPMENT;
	}

	public static function getBuildInRestrictions()
	{
		return  array(
			'\Bitrix\Sale\Delivery\Restrictions\BySite' => 'lib/delivery/restrictions/bysite.php',
			'\Bitrix\Sale\Delivery\Restrictions\ByPrice' => 'lib/delivery/restrictions/byprice.php',
			'\Bitrix\Sale\Delivery\Restrictions\ByWeight' => 'lib/delivery/restrictions/byweight.php',
			'\Bitrix\Sale\Delivery\Restrictions\ByMaxSize' => 'lib/delivery/restrictions/bymaxsize.php',
			'\Bitrix\Sale\Delivery\Restrictions\ByLocation' => 'lib/delivery/restrictions/bylocation.php',
			'\Bitrix\Sale\Delivery\Restrictions\PersonType' => 'lib/delivery/restrictions/bypersontype.php',
			'\Bitrix\Sale\Delivery\Restrictions\ByPaySystem' => 'lib/delivery/restrictions/bypaysystem.php',
			'\Bitrix\Sale\Delivery\Restrictions\ByDimensions' => 'lib/delivery/restrictions/bydimensions.php',
			'\Bitrix\Sale\Delivery\Restrictions\ByPublicMode' => 'lib/delivery/restrictions/bypublicmode.php',
			'\Bitrix\Sale\Delivery\Restrictions\ByProductCategory' => 'lib/delivery/restrictions/byproductcategory.php'
		);
	}

	public static function getEventName()
	{
		return 'onSaleDeliveryRestrictionsClassNamesBuildList';
	}

	public static function deleteByDeliveryIdClassName($deliveryId, $className)
	{
		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();
		$strSql = "DELETE FROM ".ServiceRestrictionTable::getTableName().
			" WHERE SERVICE_ID=".$sqlHelper->forSql($deliveryId).
			" AND SERVICE_TYPE=".$sqlHelper->forSql(Manager::SERVICE_TYPE_SHIPMENT).
			" AND CLASS_NAME='".$sqlHelper->forSql($className)."'";

		$con->queryExecute($strSql);
	}

	public static function getRestrictedIds(\Bitrix\Sale\Shipment $shipment = null, $restrictionMode)
	{
		$result = array();

		static $dataPrepared = false;

		if($dataPrepared === false)
		{
			self::init();

			$dbRes = ServiceRestrictionTable::getList(array(
				'runtime' => array(
					new \Bitrix\Main\Entity\ReferenceField(
						'DELIVERY_SERVICE',
						'\Bitrix\Sale\Delivery\Services\Table',
						array(
							'=this.SERVICE_ID' => 'ref.ID',
							'=this.SERVICE_TYPE' => array('?', self::SERVICE_TYPE_SHIPMENT)
						),
						array('join_type' => 'inner')
					)
				),
				'filter' => array(
					'DELIVERY_SERVICE.ACTIVE' => 'Y'
				),
				'order' => array('SORT' =>'ASC')
			));

			$data = array();

			while($rstr = $dbRes->fetch())
			{
				if(!isset($data[$rstr["SERVICE_ID"]]))
					$data[$rstr["SERVICE_ID"]] = array();

				$data[$rstr["SERVICE_ID"]][$rstr['ID']] = $rstr;
			}

			self::prepareData(array_keys($data), $data);
			$dataPrepared = true;
		}
		else
		{
			$data = self::$cachedFields[self::getServiceType()];
		}

		foreach($data as $serviceId => $serviceRestrictions)
		{
			$srvRes = self::SEVERITY_NONE;

			foreach($serviceRestrictions as $restrictionId => $rstr)
			{
				if($shipment == null)
				{
					$result[] = $serviceId;
					continue 2;
				}

				if(!$rstr['PARAMS'])
					$rstr['PARAMS'] = array();

				$res = $rstr['CLASS_NAME']::checkByEntity(
					$shipment,
					$rstr['PARAMS'],
					$restrictionMode,
					$serviceId
				);

				if($res == self::SEVERITY_STRICT)
					continue 2;

				if($res == self::SEVERITY_SOFT && $restrictionMode == self::MODE_CLIENT)
					continue 2;

				if($res == self::SEVERITY_SOFT && $srvRes == self::SEVERITY_NONE)
					$srvRes = self::SEVERITY_SOFT;
			}

			$result[$serviceId] = $srvRes;
		}

		return $result;
	}
}
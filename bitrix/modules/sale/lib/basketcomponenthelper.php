<?php


namespace Bitrix\Sale;


use Bitrix\Main;

class BasketComponentHelper
{

	/**
	 * @param $fuserId
	 * @param $siteId
	 *
	 * @return int
	 */
	public static function getFUserBasketQuantity($fuserId, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$quantity = null;
		if (!empty($_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId]) && is_array($_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId])
			&& array_key_exists($fuserId, $_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId]))
		{
			$quantity = $_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId][$fuserId];
		}

		if ($quantity === null)
		{
			static::updateFUserBasketQuantity($fuserId, $siteId);
			$quantity = $_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId][$fuserId];
		}

		return $quantity;
	}

	/**
	 * @param int $fuserId
	 * @param string $siteId
	 *
	 * @return int
	 */
	public static function getFUserBasketPrice($fuserId, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$price = null;
		if (!empty($_SESSION['SALE_USER_BASKET_PRICE'][$siteId]) && is_array($_SESSION['SALE_USER_BASKET_PRICE'][$siteId])
			&& array_key_exists($fuserId, $_SESSION['SALE_USER_BASKET_PRICE'][$siteId]))
		{
			$price = $_SESSION['SALE_USER_BASKET_PRICE'][$siteId][$fuserId];
		}

		if ($price === null)
		{
			static::updateFUserBasketPrice($fuserId, $siteId);
			$price = $_SESSION['SALE_USER_BASKET_PRICE'][$siteId][$fuserId];
		}

		return $price;
	}

	/**
	 * @param int $fuserId
	 * @param int $quantity
	 * @param string $siteId
	 */
	protected static function setFUserBasketQuantity($fuserId, $quantity, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId][$fuserId] = $quantity;
	}

	/**
	 * @param int $fuserId
	 * @param float $price
	 * @param string $siteId
	 */
	protected static function setFUserBasketPrice($fuserId, $price, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}
		$_SESSION['SALE_USER_BASKET_PRICE'][$siteId][$fuserId] = $price;
	}

	/**
	 * @param int $fuserId
	 * @param string $siteId
	 * @param array|null $basketList
	 */
	public static function updateFUserBasketPrice($fuserId, $siteId = null, $basketList = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$price = 0;

		if ($basketList === null)
		{
			$basketList = static::getFUserBasketList($fuserId, $siteId);
		}

		if (!empty($basketList) && is_array($basketList))
		{
			$orderData = static::calculatePrice($fuserId, $basketList);
			$price = $orderData['ORDER_PRICE'];
		}

		static::setFUserBasketPrice($fuserId, $price, $siteId);
	}

	/**
	 * @param int $fuserId
	 * @param string $siteId
	 * @param array|null $basketList
	 *
	 * @return float
	 */
	public static function updateFUserBasketQuantity($fuserId, $siteId = null, $basketList = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$quantity = 0;

		if ($basketList === null)
		{
			$basketList = static::getFUserBasketList($fuserId, $siteId);
		}

		if (!empty($basketList) && is_array($basketList))
		{
			$quantity = count($basketList);
		}

		static::setFUserBasketQuantity($fuserId, $quantity, $siteId);
	}

	/**
	 * @param $fuserId
	 * @param null $siteId
	 */
	public static function updateFUserBasket($fuserId, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$basketList = static::getFUserBasketList($fuserId, $siteId);

		static::updateFUserBasketPrice($fuserId, $siteId, $basketList);
		static::updateFUserBasketQuantity($fuserId, $siteId, $basketList);
	}

	/**
	 * @param int $fuserId
	 * @param string $siteId
	 *
	 * @return array
	 */
	protected static function getFUserBasketList($fuserId, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$basketList = array();
		$res = Basket::getList(array(
								   'filter' => array(
									   'FUSER_ID' => $fuserId,
									   'ORDER_ID' => null,
									   'LID' => $siteId,
									   'CAN_BUY' => 'Y',
									   'DELAY' => 'N'
								   ),
							   ));
		while($data = $res->fetch())
		{
			if (\CSaleBasketHelper::isSetItem($data))
				continue;

			if (!isset($basketData['BASE_PRICE']) || (float)$basketData['BASE_PRICE'] <= 0)
			{
				$basketData['BASE_PRICE'] = $basketData['PRICE'] + $basketData['DISCOUNT_PRICE'];
			}

			$basketList[] = $data;
		}

		return $basketList;
	}

	/**
	 * @param int $fuserId
	 * @param array $basketList
	 *
	 * @return array
	 */
	protected static function calculatePrice($fuserId, array $basketList)
	{
		$totalPrice = 0;
		$totalWeight = 0;

		foreach ($basketList as $basketData)
		{
			$totalPrice += $basketData["PRICE"] * $basketData["QUANTITY"];
			$totalWeight += $basketData["WEIGHT"] * $basketData["QUANTITY"];
		}

		$orderData = array(
			'SITE_ID' => SITE_ID,
			'ORDER_PRICE' => $totalPrice,
			'ORDER_WEIGHT' => $totalWeight,
			'BASKET_ITEMS' => $basketList
		);

		$userId = Fuser::getUserIdById($fuserId);
		if (intval($userId) > 0)
		{
			$orderData['USER_ID'] = $userId;
			$errors = array();
			\CSaleDiscount::DoProcessOrder($orderData, array(), $errors);
			Compatible\DiscountCompatibility::stopUsageCompatible();
		}

		return $orderData;
	}


	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleBasketItemEntitySaved(Main\Event $event)
	{
		$fuserId = Fuser::getId(true);
		$basketItem = $event->getParameter('ENTITY');

		$originalValues = $event->getParameter('VALUES');
		if ((!$basketItem instanceof BasketItem))
		{
			return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
		}

		/** @var \Bitrix\Sale\Basket $basket */
		if ((!$basket = $basketItem->getCollection())
			|| ($basketItem->getFUserId() != $fuserId)
		)
		{
			return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
		}

		if ($basketItem->isChanged())
		{
			$updateSessionData = false;

			if (!$basket->getOrder())
			{
				if (!$updateSessionData && array_key_exists('QUANTITY', $originalValues))
				{
					$updateSessionData = true;
				}

				if (!$updateSessionData && (array_key_exists('PRICE', $originalValues) && PriceMaths::roundPrecision($originalValues['PRICE']) !== PriceMaths::roundPrecision($basketItem->getPrice())))
				{
					$updateSessionData = true;
				}

				if (!$updateSessionData && (array_key_exists('DISCOUNT_PRICE', $originalValues) && PriceMaths::roundPrecision($originalValues['DISCOUNT_PRICE']) !== PriceMaths::roundPrecision($basketItem->getDiscountPrice())))
				{
					$updateSessionData = true;
				}
			}

			if (!$updateSessionData && (array_key_exists('ORDER_ID', $originalValues) && (intval($originalValues['ORDER_ID'])) == 0 && intval($basketItem->getField('ORDER_ID') > 0)))
			{
				$updateSessionData = true;
			}

			if (!$updateSessionData
				&& (array_key_exists('CAN_BUY', $originalValues) && ($originalValues['CAN_BUY'] !== $basketItem->getField('CAN_BUY'))))
			{
				$updateSessionData = true;
			}

			if (!$updateSessionData
				&& (array_key_exists('DELAY', $originalValues) && ($originalValues['DELAY'] !== $basketItem->getField('DELAY'))))
			{
				$updateSessionData = true;
			}

			if ($updateSessionData)
			{
				/** @var \Bitrix\Sale\Basket $allBasket */
				if ($allBasket = $basketItem->getCollection())
				{

					static::updateFUserBasketPrice($fuserId, SITE_ID);
					static::updateFUserBasketQuantity($fuserId, SITE_ID);
				}
			}
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}


	/**
	 * @param \Bitrix\Main\Event $event
	 *
	 * @return \Bitrix\Main\EventResult
	 */
	public static function onSaleBasketItemDeleted(Main\Event $event)
	{
		$fuserId = Fuser::getId(true);
		$originalValues = $event->getParameter('VALUES');
		if ($originalValues['FUSER_ID'] != $fuserId)
		{
			return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
		}

		static::updateFUserBasketPrice($fuserId, SITE_ID);
		static::updateFUserBasketQuantity($fuserId, SITE_ID);

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}
}
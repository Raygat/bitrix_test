<?php

namespace Bitrix\Sale\PaySystem;

interface IRequested
{
	/**
	 * @return bool
	 */
	public function createMovementListRequest();

	/**
	 * @param $requestId
	 * @return array
	 */
	public function getMovementListStatus($requestId = null);

	/**
	 * @param $requestId
	 * @return mixed
	 */
	public function getMovementList($requestId = null);
}
<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) 
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_BILL_TITLE'),
	'SORT' => 100,
	'CODES' => array(
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DATE"),
			"SORT" => 100,
			'GROUP' => 'PAYMENT',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DATE_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "DATE_BILL_DATE",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"BILL_ORDER_SUBJECT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_ORDER_SUBJECT"),
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			"SORT" => 200
		),
		"PAYMENT_DATE_PAY_BEFORE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_PAY_BEFORE"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_PAY_BEFORE_DESC"),
			"SORT" => 300,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "DATE_PAY_BEFORE",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"SELLER_COMPANY_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_SUPPLI_DESC"),
			"SORT" => 400
		),
		"SELLER_COMPANY_ADDRESS" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_ADRESS_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_ADRESS_SUPPLI_DESC"),
			"SORT" => 500
		),
		"SELLER_COMPANY_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_PHONE_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_PHONE_SUPPLI_DESC"),
			"SORT" => 600
		),
		"SELLER_COMPANY_INN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_INN_SUPPLI"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_INN_SUPPLI_SHORT"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_INN_SUPPLI_DESC"),
			"SORT" => 700
		),
		"SELLER_COMPANY_KPP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_KPP_SUPPLI"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_KPP_SUPPLI_SHORT"),
			'GROUP' => 'SELLER_COMPANY',
			"SORT" => 800,
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_KPP_SUPPLI_DESC")
		),
		"SELLER_COMPANY_BANK_ACCOUNT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_ORDER_SUPPLI"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_ORDER_SUPPLI_SHORT"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_ORDER_SUPPLI_DESC"),
			"SORT" => 900,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_ORDER_SUPPLI_VAL"),
				"PROVIDER_KEY" => ""
			)
		),
		"SELLER_COMPANY_BANK_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BANK_SUPPLI"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_BANK_SUPPLI_SHORT"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BANK_SUPPLI_DESC"),
			"SORT" => 1000,
		),
		"SELLER_COMPANY_BANK_CITY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BCITY_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BCITY_SUPPLI_DESC"),
			"SORT" => 1100
		),
		"SELLER_COMPANY_BANK_ACCOUNT_CORR" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_KORORDER_SUPPLI"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_KORORDER_SUPPLI_SHORT"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_KORORDER_SUPPLI_DESC"),
			"SORT" => 1200
		),
		"SELLER_COMPANY_BANK_BIC" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BIK_SUPPLI"),
			"SHORT_NAME" => Loc::getMessage("SALE_HPS_BILL_BIK_SUPPLI_SHORT"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BIK_SUPPLI_DESC"),
			"SORT" => 1300
		),
		"SELLER_COMPANY_DIRECTOR_POSITION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DIR_POS_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DIR_POS_SUPPLI_DESC"),
			"SORT" => 1400,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DIR_POS_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_ACCOUNTANT_POSITION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_ACC_POS_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_ACC_POS_SUPPLI_DESC"),
			'GROUP' => 'SELLER_COMPANY',
			"SORT" => 1500,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_ACC_POS_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_DIRECTOR_NAME" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DIR_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DIR_SUPPLI_DESC"),
			"SORT" => 1600
		),
		"SELLER_COMPANY_ACCOUNTANT_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_ACC_SUPPLI"),
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_ACC_SUPPLI_DESC"),
			"SORT" => 1700
		),
		"BUYER_PERSON_COMPANY_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_DESC"),
			"SORT" => 1800,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "COMPANY_NAME",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_INN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_INN"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_INN_DESC"),
			"SORT" => 1900,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "INN",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_ADDRESS" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_ADRES"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_ADRES_DESC"),
			"SORT" => 200,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "ADDRESS",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_PHONE"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_PHONE_DESC"),
			"SORT" => 2100,
			"PROVIDER_VALUE" => "PHONE",
			"PROVIDER_KEY" => "PROPERTY"
		),
		"BUYER_PERSON_COMPANY_FAX" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_FAX"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_FAX_DESC"),
			"SORT" => 2200,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "FAX",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_NAME_CONTACT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_PERSON"),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_CUSTOMER_PERSON_DESC"),
			"SORT" => 2300,
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "PAYER_NAME",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BILL_COMMENT1" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_COMMENT1"),
			"SORT" => 2400,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_COMMENT1_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILL_COMMENT2" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_COMMENT2"),
			"SORT" => 2500,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
		),
		"BILL_PATH_TO_LOGO" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_LOGO"),
			"SORT" => 2600,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_LOGO_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILL_LOGO_DPI" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_LOGO_DPI"),
			"SORT" => 2700,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'96' => Loc::getMessage("SALE_HPS_BILL_LOGO_DPI_96"),
					'600' => Loc::getMessage("SALE_HPS_BILL_LOGO_DPI_600"),
					'300' => Loc::getMessage("SALE_HPS_BILL_LOGO_DPI_300"),
					'150' => Loc::getMessage("SALE_HPS_BILL_LOGO_DPI_150"),
					'72' => Loc::getMessage("SALE_HPS_BILL_LOGO_DPI_72")
				)
			),
		),
		"BILL_PATH_TO_STAMP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_PRINT"),
			"SORT" => 2800,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_PRINT_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"SELLER_COMPANY_DIR_SIGN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DIR_SIGN_SUPPLI"),
			"SORT" => 2900,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DIR_SIGN_SUPPLI_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"SELLER_COMPANY_ACC_SIGN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_ACC_SIGN_SUPPLI"),
			"SORT" => 3000,
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_ACC_SIGN_SUPPLI_DESC"),
			'GROUP' => 'SELLER_COMPANY',
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILL_BACKGROUND" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BACKGROUND"),
			"SORT" => 3100,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_BACKGROUND_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILL_BACKGROUND_STYLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_BACKGROUND_STYLE"),
			"SORT" => 3200,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			'TYPE' => 'SELECT',
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'none' => Loc::getMessage("SALE_HPS_BILL_BACKGROUND_STYLE_NONE"),
					'tile' => Loc::getMessage("SALE_HPS_BILL_BACKGROUND_STYLE_TILE"),
					'stretch' => Loc::getMessage("SALE_HPS_BILL_BACKGROUND_STYLE_STRETCH")
				)
			),
		),
		"BILL_MARGIN_TOP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_MARGIN_TOP"),
			"SORT" => 3300,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILL_MARGIN_RIGHT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_MARGIN_RIGHT"),
			"SORT" => 3400,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILL_MARGIN_BOTTOM" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_MARGIN_BOTTOM"),
			"SORT" => 3500,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILL_MARGIN_LEFT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_MARGIN_LEFT"),
			"SORT" => 3600,
			'GROUP' => 'CONNECT_SETTINGS_BILL',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "20",
				"PROVIDER_KEY" => "VALUE"
			)
		)
	)
);
?>
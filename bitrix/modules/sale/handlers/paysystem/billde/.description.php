<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) 
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage("SALE_HPS_BILL_DE_TITLE"),
	'SORT' => 1800,
	'CODES' => array(
		"DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_DATE"),
			'SORT' => 100,
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_DATE_DESC"),
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "DATE_BILL_DATE",
				"PROVIDER_KEY" => "PAYMENT"
			)
		),
		"DATE_PAY_BEFORE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_PAY_BEFORE"),
			'SORT' => 200,
			'GROUP' => 'PAYMENT',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_PAY_BEFORE_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "DATE_PAY_BEFORE",
				"PROVIDER_KEY" => "ORDER"
			)
		),
		"SELLER_COMPANY_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_SUPPLI"),
			'SORT' => 300,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_SUPPLI_DESC")
		),
		"SELLER_COMPANY_ADDRESS" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_ADRESS_SUPPLI"),
			'SORT' => 400,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_ADRESS_SUPPLI_DESC")
		),
		"SELLER_COMPANY_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_PHONE_SUPPLI"),
			'SORT' => 500,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_PHONE_SUPPLI_DESC")
		),
		"SELLER_COMPANY_EMAIL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_EMAIL_SUPPLI"),
			'SORT' => 600,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_EMAIL_SUPPLI_DESC")
		),
		"SELLER_COMPANY_BANK_ACCOUNT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_ACCNO_SUPPLI"),
			'SORT' => 700,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_ACCNO_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_ACCNO_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_BANK_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SUPPLI"),
			'SORT' => 800,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_BANK_BIC" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_BLZ_SUPPLI"),
			'SORT' => 900,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_BLZ_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_BLZ_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_BANK_IBAN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_IBAN_SUPPLI"),
			'SORT' => 1000,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_IBAN_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_IBAN_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_BANK_SWIFT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SWIFT_SUPPLI"),
			'SORT' => 1100,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SWIFT_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_BANK_SWIFT_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_EU_INN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_EU_INN_SUPPLI"),
			'SORT' => 1200,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_EU_INN_SUPPLI_DESC")
		),
		"SELLER_COMPANY_INN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_INN_SUPPLI"),
			'SORT' => 1300,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_INN_SUPPLI_DESC")
		),
		"SELLER_COMPANY_REG" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_REG_SUPPLI"),
			'SORT' => 1400,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_REG_SUPPLI_DESC")
		),
		"SELLER_COMPANY_DIRECTOR_POSITION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_POS_SUPPLI"),
			'SORT' => 1500,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_POS_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_POS_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_ACCOUNTANT_POSITION" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_POS_SUPPLI"),
			'SORT' => 1600,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_POS_SUPPLI_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_POS_SUPPLI_VAL"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"SELLER_COMPANY_DIRECTOR_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_SUPPLI"),
			'SORT' => 1700,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_SUPPLI_DESC")
		),
		"SELLER_COMPANY_ACCOUNTANT_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_SUPPLI"),
			'SORT' => 1800,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_SUPPLI_DESC")
		),
		"BUYER_PERSON_COMPANY_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_ID"),
			'SORT' => 1900,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "USER_ID",
				"PROVIDER_KEY" => "ORDER"
			)
		),
		"BUYER_PERSON_COMPANY_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER"),
			'SORT' => 2000,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "COMPANY_NAME",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_ADDRESS" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_ADRES"),
			'SORT' => 2100,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_ADRES_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "ADDRESS",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_PHONE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_PHONE"),
			'SORT' => 2200,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_PHONE_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "PHONE",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_FAX" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_FAX"),
			'SORT' => 2300,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_FAX_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "FAX",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_PAYER_NAME" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_PERSON"),
			'SORT' => 2400,
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_CUSTOMER_PERSON_DESC"),
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "PAYER_NAME",
				"PROVIDER_KEY" => "PROPERTY"
			)
		),
		"BILLDE_COMMENT1" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COMMENT1"),
			'SORT' => 2500,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => Loc::getMessage("SALE_HPS_BILL_DE_COMMENT1_VALUE"),
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_COMMENT2" => array(
			'SORT' => 2600,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_COMMENT2")
		),
		"BILLDE_PATH_TO_LOGO" => array(
			'SORT' => 2700,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_LOGO"),
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_LOGO_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLDE_LOGO_DPI" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_LOGO_DPI"),
			'SORT' => 2800,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'96' => Loc::getMessage('SALE_HPS_BILL_EN_LOGO_DPI_96'),
					'600' => Loc::getMessage('SALE_HPS_BILL_EN_LOGO_DPI_600'),
					'300' => Loc::getMessage('SALE_HPS_BILL_EN_LOGO_DPI_300'),
					'150' => Loc::getMessage('SALE_HPS_BILL_EN_LOGO_DPI_150'),
					'72' => Loc::getMessage('SALE_HPS_BILL_EN_LOGO_DPI_72')
				)
			)
		),
		"BILLDE_PATH_TO_STAMP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_PRINT"),
			'SORT' => 2900,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_PRINT_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"SELLER_COMPANY_DIR_SIGN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_SIGN_SUPPLI"),
			'SORT' => 3000,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_DIR_SIGN_SUPPLI_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"SELLER_COMPANY_ACC_SIGN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_SIGN_SUPPLI"),
			'SORT' => 3100,
			'GROUP' => 'SELLER_COMPANY',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_ACC_SIGN_SUPPLI_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLDE_BACKGROUND" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BACKGROUND"),
			'SORT' => 3200,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			"DESCRIPTION" => Loc::getMessage("SALE_HPS_BILL_DE_BACKGROUND_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLDE_BACKGROUND_STYLE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_BACKGROUND_STYLE"),
			'SORT' => 3300,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'tile' => Loc::getMessage('SALE_HPS_BILL_EN_BACKGROUND_STYLE_TILE'),
					'stretch' => Loc::getMessage('SALE_HPS_BILL_EN_BACKGROUND_STYLE_STRETCH')
				)
			)
		),
		"BILLDE_MARGIN_TOP" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_MARGIN_TOP"),
			'SORT' => 3400,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_MARGIN_RIGHT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_MARGIN_RIGHT"),
			'SORT' => 3500,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_MARGIN_BOTTOM" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_MARGIN_BOTTOM"),
			'SORT' => 3600,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "15",
				"PROVIDER_KEY" => "VALUE"
			)
		),
		"BILLDE_MARGIN_LEFT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_BILL_DE_MARGIN_LEFT"),
			'SORT' => 3700,
			'GROUP' => 'CONNECT_SETTINGS_BILLDE',
			'DEFAULT' => array(
				"PROVIDER_VALUE" => "20",
				"PROVIDER_KEY" => "VALUE"
			)
		)
	)
);
<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage("SBLP_DTITLE"),
	'SORT' => 100,
	'CODES' => array(
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SBLP_ORDER_ID"),
			'GROUP' => 'PAYMENT',
			"DEFAULT" => array(
				"VALUE" => "ID",
				"TYPE" => "PAYMENT"
			)
		),
		"DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SBLP_DATE"),
			"DESCRIPTION" => Loc::getMessage("SBLP_DATE_DESC"),
			'DEFAULT' => array(
				"VALUE" => "DATE_BILL_DATE",
				"TYPE" => "PAYMENT"
			)
		),
		"DATE_PAY_BEFORE" => array(
			"NAME" => Loc::getMessage("SBLP_PAY_BEFORE"),
			'DEFAULT' => array(
				"VALUE" => "DATE_PAY_BEFORE",
				"TYPE" => "PAYMENT"
			)
		),
		"SELLER_COMPANY_NAME" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SBLP_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_BANK_ACCOUNT" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_ORDER_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SBLP_ORDER_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_BANK_NAME" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_ORDER_BANK"),
		),
		"SELLER_COMPANY_MFO" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_ORDER_MFO"),
		),
		"SELLER_COMPANY_ADDRESS" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_ADRESS_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SBLP_ADRESS_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_PHONE" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_PHONE_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SBLP_PHONE_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_EDRPOY" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_EDRPOY_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SBLP_EDRPOY_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_IPN" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_IPN_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SBLP_IPN_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_PDV" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_PDV_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SBLP_PDV_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_SYS" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_SYS_SUPPLI"),
			"DESCRIPTION" => Loc::getMessage("SBLP_SYS_SUPPLI_DESC"),
		),
		"SELLER_COMPANY_ACCOUNTANT_NAME" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_ACC_SUPPLI"),
		),
		"SELLER_COMPANY_ACCOUNTANT_POSITION" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_ACC_POS_SUPPLI"),
		),
		"BUYER_PERSON_COMPANY_NAME" => array(
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"NAME" => Loc::getMessage("SBLP_CUSTOMER"),
			"DESCRIPTION" => Loc::getMessage("SBLP_CUSTOMER_DESC"),
			"VALUE" => "COMPANY_NAME",
			"TYPE" => "PROPERTY"
		),
		"BUYER_PERSON_COMPANY_ADDRESS" => array(
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"NAME" => Loc::getMessage("SBLP_CUSTOMER_ADRES"),
			"DESCRIPTION" => Loc::getMessage("SBLP_CUSTOMER_ADRES_DESC"),
			'DEFAULT' => array(
				"VALUE" => "ADDRESS",
				"TYPE" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_FAX" => array(
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"NAME" => Loc::getMessage("SBLP_CUSTOMER_FAX"),
			"DESCRIPTION" => Loc::getMessage("SBLP_CUSTOMER_FAX_DESC"),
		),
		"BUYER_PERSON_COMPANY_PHONE" => array(
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"NAME" => Loc::getMessage("SBLP_CUSTOMER_PHONE"),
			"DESCRIPTION" => Loc::getMessage("SBLP_CUSTOMER_PHONE_DESC"),
			'DEFAULT' => array(
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			)
		),
		"BUYER_PERSON_COMPANY_DOGOVOR" => array(
			'GROUP' => 'BUYER_PERSON_COMPANY',
			"NAME" => Loc::getMessage("SBLP_CUSTOMER_DOGOVOR"),
			"DESCRIPTION" => Loc::getMessage("SBLP_CUSTOMER_DOGOVOR"),
			),
		"BILLUA_COMMENT1" => array(
			'GROUP' => 'CONNECT_SETTINGS_BILLUA',
			"NAME" => Loc::getMessage("SBLP_COMMENT1"),
			'DEFAULT' => array(
				"VALUE" => Loc::getMessage("SBLP_COMMENT1_VALUE"),
				"TYPE" => 'VALUE'
			)
		),
		"BILLUA_COMMENT2" => array(
			'GROUP' => 'CONNECT_SETTINGS_BILLUA',
			"NAME" => Loc::getMessage("SBLP_COMMENT2")
		),
		"BILLUA_PATH_TO_STAMP" => array(
			'GROUP' => 'CONNECT_SETTINGS_BILLUA',
			"NAME" => Loc::getMessage("SBLP_PRINT"),
			"DESCRIPTION" => Loc::getMessage("SBLP_PRINT_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"SELLER_COMPANY_ACC_SIGN" => array(
			'GROUP' => 'SELLER_COMPANY',
			"NAME" => Loc::getMessage("SBLP_ACC_SIGN_SUPPLI"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLUA_BACKGROUND" => array(
			"NAME" => Loc::getMessage("SBLP_BACKGROUND"),
			'GROUP' => 'CONNECT_SETTINGS_BILLUA',
			"DESCRIPTION" => Loc::getMessage("SBLP_BACKGROUND_DESC"),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		"BILLUA_BACKGROUND_STYLE" => array(
			"NAME" => Loc::getMessage("SBLP_BACKGROUND_STYLE"),
			'GROUP' => 'CONNECT_SETTINGS_BILLUA',
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'none' => Loc::getMessage("SBLP_BACKGROUND_STYLE_NONE"),
					'tile' => Loc::getMessage("SBLP_BACKGROUND_STYLE_TILE"),
					'stretch' => Loc::getMessage("SBLP_BACKGROUND_STYLE_STRETCH")
				)
			),
			"TYPE" => "SELECT"
		),
		"BILLUA_MARGIN_TOP" => array(
			"NAME" => Loc::getMessage("SBLP_MARGIN_TOP"),
			'GROUP' => 'CONNECT_SETTINGS_BILLUA',
			'DEFAULT' => array(
				"VALUE" => "15",
				"TYPE" => 'VALUE'
			)
		),
		"BILLUA_MARGIN_RIGHT" => array(
			"NAME" => Loc::getMessage("SBLP_MARGIN_RIGHT"),
			'GROUP' => 'CONNECT_SETTINGS_BILLUA',
			'DEFAULT' => array(
				"VALUE" => "15",
				"TYPE" => 'VALUE'
			)
		),
		"BILLUA_MARGIN_BOTTOM" => array(
			"NAME" => Loc::getMessage("SBLP_MARGIN_BOTTOM"),
			'GROUP' => 'CONNECT_SETTINGS_BILLUA',
			'DEFAULT' => array(
				"VALUE" => "15",
				"TYPE" => 'VALUE'
			)
		),
		"BILLUA_MARGIN_LEFT" => array(
			"NAME" => Loc::getMessage("SBLP_MARGIN_LEFT"),
			'GROUP' => 'CONNECT_SETTINGS_BILLUA',
			'DEFAULT' => array(
				"VALUE" => "20",
				"TYPE" => 'VALUE'
			)
		)
	)
);
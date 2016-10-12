CREATE TABLE b_im_chat(
	ID int(18) not null auto_increment,
	TITLE varchar(255) null,
	DESCRIPTION text null,
	COLOR varchar(255) null,
	TYPE char(1) null,
	EXTRANET char(1) null,
	AUTHOR_ID int(18) not null,
	AVATAR int(18) null,
	CALL_TYPE smallint(1) DEFAULT 0,
	CALL_NUMBER varchar(20) NULL,
	ENTITY_TYPE varchar(50) NULL,
  ENTITY_ID varchar(50) NULL,
	ENTITY_DATA_1 varchar(255) null,
	ENTITY_DATA_2 varchar(255) null,
	ENTITY_DATA_3 varchar(255) null,
	DISK_FOLDER_ID int(18) null,
	LAST_MESSAGE_ID int(18) null,
	PRIMARY KEY (ID),
	KEY IX_IM_CHAT_1 (AUTHOR_ID),
	KEY IX_IM_CHAT_2 (ENTITY_TYPE, ENTITY_ID, AUTHOR_ID),
	KEY IX_IM_CHAT_3 (CALL_NUMBER, AUTHOR_ID),
	KEY IX_IM_CHAT_4 (TYPE)
);

CREATE TABLE b_im_message(
	ID int(18) not null auto_increment,
	CHAT_ID int(18) not null,
	AUTHOR_ID int(18) not null,
	MESSAGE text null,
	MESSAGE_OUT text null,
	DATE_CREATE datetime not null,
	EMAIL_TEMPLATE varchar(255) null,
	NOTIFY_TYPE smallint(2) DEFAULT 0,
	NOTIFY_MODULE varchar(255) null,
	NOTIFY_EVENT varchar(255) null,
	NOTIFY_TAG varchar(255) null,
	NOTIFY_SUB_TAG varchar(255) null,
	NOTIFY_TITLE varchar(255) null,
	NOTIFY_BUTTONS text null,
	NOTIFY_READ char(1) DEFAULT 'N',
	IMPORT_ID int(18) null,
	PRIMARY KEY (ID),
	KEY IX_IM_MESS_2 (NOTIFY_TAG, AUTHOR_ID),
	KEY IX_IM_MESS_3 (NOTIFY_SUB_TAG, AUTHOR_ID),
	KEY IX_IM_MESS_4 (CHAT_ID, NOTIFY_READ),
	KEY IX_IM_MESS_5 (CHAT_ID, DATE_CREATE),
	KEY IX_IM_MESS_6 (AUTHOR_ID),
	KEY IX_IM_MESS_7 (CHAT_ID, ID)
);

CREATE TABLE b_im_message_param
(
	ID int(18) not null auto_increment,
	MESSAGE_ID INT(11) NOT NULL,
	PARAM_NAME VARCHAR(100) NOT NULL,
	PARAM_VALUE VARCHAR(100) NULL,
	PARAM_JSON text null,
	UNIQUE KEY pk_b_im_message_param (ID),
	KEY IX_B_IM_MESSAGE_PARAM_1 (MESSAGE_ID, PARAM_NAME),
	KEY IX_B_IM_MESSAGE_PARAM_2 (PARAM_NAME, PARAM_VALUE(50), MESSAGE_ID)
);

CREATE TABLE b_im_status
(
	USER_ID int(18) not null,
	COLOR varchar(255) null,
	STATUS varchar(50) default 'online',
	STATUS_TEXT varchar(255) null,
	IDLE datetime null,
	DESKTOP_LAST_DATE datetime null,
	MOBILE_LAST_DATE datetime null,
	EVENT_ID int(18) null,
	EVENT_UNTIL_DATE datetime null,
	PRIMARY KEY (USER_ID),
	INDEX IX_IM_STATUS_EUD (EVENT_UNTIL_DATE)
);

CREATE TABLE b_im_relation (
	ID int(18) not null auto_increment,
	CHAT_ID int(18) not null,
	MESSAGE_TYPE char(1) default 'P',
	USER_ID int(18) not null,
	START_ID int(18) DEFAULT 0,
	LAST_ID int(18) DEFAULT 0,
	LAST_SEND_ID int(18) DEFAULT 0,
	LAST_FILE_ID int(18) DEFAULT 0,
	LAST_READ datetime null,
	STATUS smallint(1) DEFAULT 0,
	CALL_STATUS smallint(1) DEFAULT 0,
	NOTIFY_BLOCK char(1) DEFAULT 'N',
	PRIMARY KEY (ID),
	KEY IX_IM_REL_1 (CHAT_ID),
	KEY IX_IM_REL_2 (USER_ID, MESSAGE_TYPE, STATUS),
	KEY IX_IM_REL_3 (USER_ID, MESSAGE_TYPE, CHAT_ID),
	KEY IX_IM_REL_4 (USER_ID, STATUS),
	KEY IX_IM_REL_5 (MESSAGE_TYPE, STATUS),
	KEY IX_IM_REL_6 (CHAT_ID, USER_ID)
);

CREATE TABLE b_im_recent(
	USER_ID int(18) not null,
	ITEM_TYPE char(1) default 'P' not null,
	ITEM_ID int(18) not null,
	ITEM_MID int(18) not null,
	PRIMARY KEY (USER_ID, ITEM_TYPE, ITEM_ID),
	KEY IX_IM_REC_1 (ITEM_TYPE, ITEM_ID)
);

CREATE TABLE b_im_bot(
	BOT_ID int(18) not null,
	MODULE_ID VARCHAR(50) not null,
	CODE VARCHAR(50) not null,
	TYPE char(1) default 'B' not null,
	CLASS VARCHAR(255),
	METHOD_BOT_DELETE VARCHAR(255),
	METHOD_MESSAGE_ADD VARCHAR(255),
	METHOD_WELCOME_MESSAGE VARCHAR(255),
	COUNT_COMMAND int(18) DEFAULT 0,
	COUNT_MESSAGE int(18) DEFAULT 0,
	COUNT_CHAT int(18) DEFAULT 0,
	COUNT_USER int(18)  DEFAULT 0,
	APP_ID varchar(128) NULL,
	PRIMARY KEY PK_B_IM_BOT (BOT_ID)
);

CREATE TABLE b_im_bot_chat(
	ID int(18) not null auto_increment,
	BOT_ID int(18) not null,
	CHAT_ID int(18) not null,
	PRIMARY KEY PK_B_IM_BOT_CHAT (ID),
	KEY IX_IM_BC_1 (BOT_ID, CHAT_ID)
);

CREATE TABLE b_im_bot_token(
	ID int(18) not null auto_increment,
	TOKEN varchar(32) null,
	DATE_CREATE datetime not null,
	DATE_EXPIRE datetime null,
	BOT_ID int(18) default 0,
	DIALOG_ID varchar(255) not null,
	PRIMARY KEY PK_B_IM_BOT_TOKEN (ID),
	KEY IX_IM_BOT_TOKEN_1 (DATE_EXPIRE, BOT_ID),
	KEY IX_IM_BOT_TOKEN_2 (TOKEN)
);

CREATE TABLE b_im_command(
	ID int(18) not null auto_increment,
	MODULE_ID VARCHAR(50) not null,
	BOT_ID int(18) default 0,
	APP_ID varchar(128) NULL,
	COMMAND varchar(255) not null,
	COMMON char(1) default 'N',
	HIDDEN char(1) default 'N',
	EXTRANET_SUPPORT char(1) default 'N',
	SONET_SUPPORT char(1) default 'N',
	CLASS VARCHAR(255) null,
	METHOD_COMMAND_ADD VARCHAR(255) null,
	METHOD_LANG_GET VARCHAR(255) null,
	PRIMARY KEY PK_B_IM_COMMAND (ID),
	KEY IX_IM_COMMAND_1 (BOT_ID)
);

CREATE TABLE b_im_command_lang
(
	ID int(18) not null auto_increment,
	COMMAND_ID int(18) not null,
	LANGUAGE_ID char(2) not null,
	TITLE varchar(255) null,
	PARAMS varchar(255) null,
	PRIMARY KEY PK_B_IM_COMMAND_LANG (ID),
	UNIQUE UX_B_IM_COMMAND_LANG (COMMAND_ID, LANGUAGE_ID)
);

CREATE TABLE b_im_alias
(
	ID int(18) not null auto_increment,
	ALIAS varchar(255) not null,
	ENTITY_TYPE varchar(255) not null,
	ENTITY_ID varchar(255) not null,
	PRIMARY KEY PK_B_IM_ALIAS (ID),
	UNIQUE UX_B_IM_ALIAS (ALIAS)
);
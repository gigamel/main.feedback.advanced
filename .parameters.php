<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$site = ($_REQUEST["site"] <> ''? $_REQUEST["site"] : ($_REQUEST["src_site"] <> ''? $_REQUEST["src_site"] : false));
$arFilter = Array("TYPE_ID" => "FEEDBACK_FORM", "ACTIVE" => "Y");
if($site !== false)
	$arFilter["LID"] = $site;

$arEvent = Array();
$dbType = CEventMessage::GetList($by="ID", $order="DESC", $arFilter);
while($arType = $dbType->GetNext())
	$arEvent[$arType["ID"]] = "[".$arType["ID"]."] ".$arType["SUBJECT"];

$arComponentParameters = array(
    "GROUPS" => array(
	    "GOOGLE_SETTINGS" => Array(
		    "NAME" => GetMessage("MFP_GROUP_GOOGLE")
		),
	    "FEEDBACK_SETTINGS" => Array(
		    "NAME" => GetMessage("MFP_GROUP_FEEDBACK")
		),
	    "FEEDBACK_ADMIN_SETTINGS" => Array(
		    "NAME" => GetMessage("MFP_GROUP_ADMIN_FEEDBACK")
		),
	    "ADDITIONALLY_SETTINGS" => Array(
		    "NAME" => GetMessage("MFP_GROUP_ADDITIONALLY")
		),
	),
	"PARAMETERS" => array(
		"TITLE" => Array(
			"NAME" => GetMessage("MFP_TITLE"), 
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("MFP_TITLE_DEFAULT"), 
			"PARENT" => "BASE",
		),
		"OK_TEXT" => Array(
			"NAME" => GetMessage("MFP_OK_MESSAGE"), 
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("MFP_OK_TEXT"), 
			"PARENT" => "BASE",
		),
		"EMAIL_TO" => Array(
			"NAME" => GetMessage("MFP_EMAIL_TO"), 
			"TYPE" => "STRING",
			"DEFAULT" => htmlspecialcharsbx(COption::GetOptionString("main", "email_from")), 
			"PARENT" => "BASE",
		),
		"DISPLAY_FIELDS" => Array(
			"NAME" => GetMessage("MFP_DISPLAY_FIELDS"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"Y", 
			"VALUES" => Array("NAME" => GetMessage("MFP_NAME"), "PHONE" => GetMessage("MFP_PHONE"), "EMAIL" => "E-mail", "MESSAGE" => GetMessage("MFP_MESSAGE")),
			"DEFAULT"=>"", 
			"COLS"=>25, 
			"PARENT" => "BASE",
		),
		"REQUIRED_FIELDS" => Array(
			"NAME" => GetMessage("MFP_REQUIRED_FIELDS"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"Y", 
			"VALUES" => Array("NONE" => GetMessage("MFP_ALL_REQ"), "NAME" => GetMessage("MFP_NAME"), "PHONE" => GetMessage("MFP_PHONE"), "EMAIL" => "E-mail", "MESSAGE" => GetMessage("MFP_MESSAGE")),
			"DEFAULT"=>"", 
			"COLS"=>25, 
			"PARENT" => "BASE",
		),
		"USE_CAPTCHA" => Array(
			"NAME" => GetMessage("MFP_CAPTCHA"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"N", 
			"VALUES" => Array("N" => GetMessage("MFP_CAPTCHA_NONE"), "BX" => GetMessage("MFP_CAPTCHA_BX"), "RECAPTCHA" => GetMessage("MFP_CAPTCHA_GOOGLE")),
			"DEFAULT"=>"N", 
			"COLS"=>25, 
			"PARENT" => "BASE",
		),
		"EVENT_MESSAGE_ID" => Array(
			"NAME" => GetMessage("MFP_EMAIL_TEMPLATES"), 
			"TYPE"=>"LIST", 
			"VALUES" => $arEvent,
			"DEFAULT"=>"", 
			"MULTIPLE"=>"Y", 
			"COLS"=>25, 
			"PARENT" => "BASE",
		),
		"SUBMIT_TEXT" => Array(
			"NAME" => GetMessage("MFP_SUBMIT"), 
			"TYPE" => "STRING",
			"DEFAULT" => "", 
			"PARENT" => "BASE",
		),
		"GOOGLE_KEY" => Array(
			"NAME" => GetMessage("MFP_GOOGLE_PUBLIC_KEY"), 
			"TYPE" => "STRING",
			"DEFAULT" => "", 
			"PARENT" => "GOOGLE_SETTINGS",
		),
		"SECRET_KEY" => Array(
			"NAME" => GetMessage("MFP_GOOGLE_SECRET_KEY"), 
			"TYPE" => "STRING",
			"DEFAULT" => "", 
			"PARENT" => "GOOGLE_SETTINGS",
		),
		"SEND_USER_MESSAGE" => Array(
			"NAME" => GetMessage("MFP_SEND_MESSAGE"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N", 
			"PARENT" => "FEEDBACK_SETTINGS",
		),
		"SEND_MESSAGE_TITLE" => Array(
			"NAME" => GetMessage("MFP_MESSAGE_TITLE"), 
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("MFP_MESSAGE_TITLE_DEFAULT"), 
			"PARENT" => "FEEDBACK_SETTINGS",
		),
		"SEND_MESSAGE_TEXT" => Array(
			"NAME" => GetMessage("MFP_MESSAGE_TEXT"), 
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("MFP_MESSAGE_TEXT_DEFAULT"), 
			"PARENT" => "FEEDBACK_SETTINGS",
		),
		"SEND_ADMIN_MESSAGE" => Array(
			"NAME" => GetMessage("MFP_SEND_ADMIN_MESSAGE"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N", 
			"PARENT" => "FEEDBACK_ADMIN_SETTINGS",
		),
		"SEND_ADMIN_TITLE" => Array(
			"NAME" => GetMessage("MFP_MESSAGE_TITLE"), 
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("MFP_ADMIN_TITLE_DEFAULT"), 
			"PARENT" => "FEEDBACK_ADMIN_SETTINGS",
		),
		"SEND_ADMIN_TEXT" => Array(
			"NAME" => GetMessage("MFP_MESSAGE_TEXT"), 
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("MFP_ADMIN_TEXT_DEFAULT"), 
			"PARENT" => "FEEDBACK_ADMIN_SETTINGS",
		),
		"ATTR_FORM_1" => Array(
			"NAME" => GetMessage("MFP_ATTR_FORM_1"),
			"TYPE" => "STRING",
			"DEFAULT" => "", 
			"PARENT" => "ADDITIONALLY_SETTINGS",
		),
		"ATTR_FORM_2" => Array(
			"NAME" => GetMessage("MFP_ATTR_FORM_2"),
			"TYPE" => "STRING",
			"DEFAULT" => "", 
			"PARENT" => "ADDITIONALLY_SETTINGS",
		),
		"ATTR_FORM_3" => Array(
			"NAME" => GetMessage("MFP_ATTR_FORM_3"),
			"TYPE" => "STRING",
			"DEFAULT" => "", 
			"PARENT" => "ADDITIONALLY_SETTINGS",
		),
	)
);


?>
<?php
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
$arParams["DISPLAY_FIELDS"] = isset($arParams["DISPLAY_FIELDS"]) ? $arParams["DISPLAY_FIELDS"] : array();
if (empty($arParams["DISPLAY_FIELDS"])) {
	return;
}

$arParams["REQUIRED_FIELDS"] = isset($arParams["REQUIRED_FIELDS"]) ? $arParams["REQUIRED_FIELDS"] : array();
$arParams["REQUIRED_FIELDS"] = array_intersect($arParams["DISPLAY_FIELDS"], $arParams["REQUIRED_FIELDS"]);

$arResult["TITLE"] = is_string($arParams["TITLE"]) ? $arParams["TITLE"] : "";
$arResult["SUBMIT_TEXT"] = (is_string($arParams["SUBMIT_TEXT"]) && !empty(trim($arParams["SUBMIT_TEXT"]))) ? $arParams["SUBMIT_TEXT"] : GetMessage("MF_SUBMIT_DEFAULT");

$arResult["ATTR_FORM_1"] = is_string($arParams["ATTR_FORM_1"]) ? $arParams["ATTR_FORM_1"] : "";
$arResult["ATTR_FORM_2"] = is_string($arParams["ATTR_FORM_2"]) ? $arParams["ATTR_FORM_2"] : "";
$arResult["ATTR_FORM_3"] = is_string($arParams["ATTR_FORM_3"]) ? $arParams["ATTR_FORM_3"] : "";

$arResult["PARAMS_HASH"] = md5(serialize($arParams).$this->GetTemplateName());

$arParams["USE_CAPTCHA"] = (($arParams["USE_CAPTCHA"] != "N" && !$USER->IsAuthorized()) ? $arParams["USE_CAPTCHA"] : "N");
switch ($arParams["USE_CAPTCHA"]) {
	case 'BX':
	case 'RECAPTCHA':
	    break;

    default:
	    $arParams["USE_CAPTCHA"] = "N";
		break;
}
if($arParams["USE_CAPTCHA"] == "RECAPTCHA") {
	$arResult["capCode"] = is_string($arParams["GOOGLE_KEY"]) ? $arParams["GOOGLE_KEY"] : "";
	$arResult["SECRET_KEY"] = is_string($arParams["SECRET_KEY"]) ? $arParams["SECRET_KEY"] : "";
}

$arParams["EVENT_NAME"] = trim($arParams["EVENT_NAME"]);
if($arParams["EVENT_NAME"] == '')
	$arParams["EVENT_NAME"] = "FEEDBACK_FORM";
$arParams["EMAIL_TO"] = trim($arParams["EMAIL_TO"]);
if($arParams["EMAIL_TO"] == '')
	$arParams["EMAIL_TO"] = COption::GetOptionString("main", "email_from");
$arParams["OK_TEXT"] = trim($arParams["OK_TEXT"]);
if($arParams["OK_TEXT"] == '')
	$arParams["OK_TEXT"] = GetMessage("MF_OK_MESSAGE");

$arParams["SEND_USER_MESSAGE"] = isset($arParams["SEND_USER_MESSAGE"]) ? $arParams["SEND_USER_MESSAGE"] : "N";
$arParams["SEND_USER_MESSAGE"] = ($arParams["SEND_USER_MESSAGE"] == "Y") ? $arParams["SEND_USER_MESSAGE"] : "N";

$arParams["SEND_MESSAGE_TITLE"] = is_string($arParams["SEND_MESSAGE_TITLE"]) ? $arParams["SEND_MESSAGE_TITLE"] : "";
$arParams["SEND_MESSAGE_TEXT"] = is_string($arParams["SEND_MESSAGE_TEXT"]) ? $arParams["SEND_MESSAGE_TEXT"] : "";

if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["submit"] <> '' && (!isset($_POST["PARAMS_HASH"]) || $arResult["PARAMS_HASH"] === $_POST["PARAMS_HASH"]))
{
	$arResult["ERROR_MESSAGE"] = array();
	if(check_bitrix_sessid())
	{
		if(!empty($arParams["REQUIRED_FIELDS"]))
		{
			if((empty($arParams["REQUIRED_FIELDS"]) || in_array("NAME", $arParams["REQUIRED_FIELDS"])) && strlen($_POST["user_name"]) <= 1)
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_REQ_NAME");		
			if((empty($arParams["REQUIRED_FIELDS"]) || in_array("EMAIL", $arParams["REQUIRED_FIELDS"])) && !filter_var($_POST["user_email"], FILTER_VALIDATE_EMAIL))
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_REQ_EMAIL");
			if((empty($arParams["REQUIRED_FIELDS"]) || in_array("MESSAGE", $arParams["REQUIRED_FIELDS"])) && strlen($_POST["MESSAGE"]) <= 3)
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_REQ_MESSAGE");
			if((empty($arParams["REQUIRED_FIELDS"]) || in_array("PHONE", $arParams["REQUIRED_FIELDS"])) && !preg_match("/^((\+?[0-9]{1} ?\(?[0-9]{3}\)? ?)?[0-9]{3}([- ]+)?[0-9]{2}([- ]+)?[0-9]{2})$/", $_POST["user_phone"]))
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_REQ_PHONE");
		}
		if(strlen($_POST["user_email"]) > 1 && !check_email($_POST["user_email"]))
			$arResult["ERROR_MESSAGE"][] = GetMessage("MF_EMAIL_NOT_VALID");
		if($arParams["USE_CAPTCHA"] == "BX")
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
			$captcha_code = $_POST["captcha_sid"];
			$captcha_word = $_POST["captcha_word"];
			$cpt = new CCaptcha();
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if (strlen($captcha_word) > 0 && strlen($captcha_code) > 0)
			{
				if (!$cpt->CheckCodeCrypt($captcha_word, $captcha_code, $captchaPass))
					$arResult["ERROR_MESSAGE"][] = GetMessage("MF_CAPTCHA_WRONG");
			}
			else
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_CAPTHCA_EMPTY");

		}
		if($arParams["USE_CAPTCHA"] == "RECAPTCHA")
		{
			$APPLICATION->AddHeadScript("https://www.google.com/recaptcha/api.js");

            if (isset($_POST["g-recaptcha-response"])) {
				$gUrl = "https://www.google.com/recaptcha/api/siteverify";
				
				$gPostdata = http_build_query(array(
				    "secret" => $arResult["SECRET_KEY"],
					"response" => $_POST["g-recaptcha-response"],
					"remoteip" => $_SERVER["REMOTE_ADDR"],
				));
				
				$gOptions = array(
				    "http" => array(
					    "method" => "POST",
						"content" => $gPostdata
					)
				);
				
				$gContext = stream_context_create($gOptions);

				$googleRecaptchaResult = json_decode(file_get_contents($gUrl, false, $gContext));
				if (!$googleRecaptchaResult->success) {
                    $arResult["ERROR_MESSAGE"][] = GetMessage("MF_RECAPTHCA_EMPTY");
				}
			}
		}			
		if(empty($arResult["ERROR_MESSAGE"]))
		{
			$arFields = Array(
				"AUTHOR" => $_POST["user_name"],
				"AUTHOR_EMAIL" => $_POST["user_email"],
				"EMAIL_TO" => $arParams["EMAIL_TO"],
				"TEXT" => $_POST["MESSAGE"],
				"PHONE" => $_POST["user_phone"],
			);

			if(!empty($arParams["EVENT_MESSAGE_ID"]))
			{
				foreach($arParams["EVENT_MESSAGE_ID"] as $v)
					if(IntVal($v) > 0)
						CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields, "N", IntVal($v));
			}
			else
				CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields);
			$_SESSION["MF_NAME"] = htmlspecialcharsbx($_POST["user_name"]);
			$_SESSION["MF_EMAIL"] = htmlspecialcharsbx($_POST["user_email"]);
			$_SESSION["MF_PHONE"] = htmlspecialcharsbx($_POST["user_phone"]);
			
			if ($arParams["SEND_USER_MESSAGE"] == "Y" && !empty(trim($arParams["SEND_MESSAGE_TITLE"]))
                && !empty(trim($arParams["SEND_MESSAGE_TEXT"])) && filter_var($_POST["user_email"], FILTER_VALIDATE_EMAIL)) {
				mail($_POST["user_email"], $arParams["SEND_MESSAGE_TITLE"], $arParams["SEND_MESSAGE_TEXT"]);
			}
			
			LocalRedirect($APPLICATION->GetCurPageParam("success=".$arResult["PARAMS_HASH"], Array("success")));
		}
		
		$arResult["MESSAGE"] = htmlspecialcharsbx($_POST["MESSAGE"]);
		$arResult["AUTHOR_NAME"] = htmlspecialcharsbx($_POST["user_name"]);
		$arResult["AUTHOR_EMAIL"] = htmlspecialcharsbx($_POST["user_email"]);
		$arResult["AUTHOR_PHONE"] = htmlspecialcharsbx($_POST["user_phone"]);
	}
	else
		$arResult["ERROR_MESSAGE"][] = GetMessage("MF_SESS_EXP");
}
elseif($_REQUEST["success"] == $arResult["PARAMS_HASH"])
{
	$arResult["OK_MESSAGE"] = $arParams["OK_TEXT"];
}

if(empty($arResult["ERROR_MESSAGE"]))
{
	if($USER->IsAuthorized())
	{
		$arResult["AUTHOR_NAME"] = $USER->GetFormattedName(false);
		$arResult["AUTHOR_EMAIL"] = htmlspecialcharsbx($USER->GetEmail());
	}
	else
	{
		if(strlen($_SESSION["MF_NAME"]) > 0)
			$arResult["AUTHOR_NAME"] = htmlspecialcharsbx($_SESSION["MF_NAME"]);
		if(strlen($_SESSION["MF_EMAIL"]) > 0)
			$arResult["AUTHOR_EMAIL"] = htmlspecialcharsbx($_SESSION["MF_EMAIL"]);
		if(strlen($_SESSION["MF_PHONE"]) > 0)
			$arResult["AUTHOR_PHONE"] = htmlspecialcharsbx($_SESSION["MF_PHONE"]);
	}
}

if($arParams["USE_CAPTCHA"] == "BX")
	$arResult["capCode"] =  htmlspecialcharsbx($APPLICATION->CaptchaGetCode());

$this->IncludeComponentTemplate();

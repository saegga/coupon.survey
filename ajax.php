<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->IncludeComponent(
	'sp-artgroup:coupon.survey',
	'',
	[
		'IBLOCK_ID' => "73",
		'flagAjax'  => true,
	],
	false,
	array('HIDE_ICONS'=>'Y')
);
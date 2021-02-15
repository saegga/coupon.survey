<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Diag;

class CouponSurvey extends CBitrixComponent{

	const CACHE_DIR = "coupon.survey";

	public function executeComponent()
	{
		global $USER;
		\Bitrix\Main\Loader::includeModule("iblock");
		\Bitrix\Main\Loader::includeModule("sale");

		$userId = $USER->GetID();

		$arParams = &$this->arParams;
		$arResult = &$this->arResult;

		$arResult['FLAG_MOBILE'] = (empty($arParams['flagAjax'])) ? CSite::InDir('/mobile_app/') : (boolean) \SP\Helper::getFromRequest('flagMobile');

		if($this->alreadySurvey($userId)){
			$arResult['ALREADY_FINISHED'] = \Bitrix\Main\Localization\Loc::getMessage("ALREADY_FINISHED");
		}
		if($arParams['flagAjax']){
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$request = $context->getRequest();

			if($this->alreadySurvey($userId)){
				global $APPLICATION;
				$arResult["SUCCESS"] = false;
				$arResult["ERROR_MSG"] = \Bitrix\Main\Localization\Loc::getMessage("ALREADY_FINISHED");
				echo \Bitrix\Main\Web\Json::encode($arResult);
				return;
			}

			$arrRequest = $request->toArray();
			$props = [];

			foreach ($arrRequest as $nameProp => $val){
				if(is_array($val)){
					foreach ($val as $idVal){
						$props[$nameProp][] = $idVal; // MULTIPLE
					}
				}
				$props[$nameProp] = $val; // OP_WHAT_APTEKA => 66 ...
			}

			$idCity = $this->getUserCity($userId, $request);
			$props["OP_USER_CITY"] = $idCity;
			$props["OP_USER_ID"] = $userId;

			$elem = new CIBlockElement();
			$result = $elem->Add([
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"NAME" => $USER->GetLogin() . " - " . date_format(new DateTime(), "Y-m-d H:i:s"),
				"PROPERTY_VALUES" => $props,
			], false, false, true);

			if($result){
				if(strlen($result) > 4){
					$coupon = "J" . substr($result, -4);
				}else{
					$coupon = "J";
					for ($i = 0; $i < 4; $i++){
						$coupon .= random_int(0, 9);
					}
				}
				try {
					$rsCoupon = \Bitrix\Sale\Internals\DiscountCouponTable::getList([
						'select' => ['*'],
						'filter' => ['COUPON' => $coupon],
					]);
					if($rsCoupon->getSelectedRowsCount() > 0){
						$coupon = \Bitrix\Sale\Internals\DiscountCouponTable::generateCoupon(true);
					}
					$resAddCoupon = \Bitrix\Sale\Internals\DiscountCouponTable::add([
						"DISCOUNT_ID" => "376", // правило корзины
						"COUPON" => $coupon,
						"ACTIVE" => "Y",
						"TYPE" => "2",
						"MAX_USER" => "1",
						"USER_ID" => $userId,
					]);
				} catch (Exception $e) {
					Diag\Debug::dumpToFile($e->getMessage(), "add coupon", "debug.txt");
				}
				if($resAddCoupon->isSuccess()){
					$arResult["SUCCESS"] = true;
					$arResult["COUPON"] = $resAddCoupon->getData()["COUPON"];
				}else{
					$arResult["SUCCESS"] = false;
				}
				echo \Bitrix\Main\Web\Json::encode($arResult);
				return;
			}else{
				Diag\Debug::dumpToFile($elem->LAST_ERROR, "add elem", "debug.txt");
				$arResult["SUCCESS"] = false;
				$arResult["ERROR_MSG"] = $elem->LAST_ERROR;
				echo \Bitrix\Main\Web\Json::encode($arResult);
				return;
			}
		}else{
			$rsProps = CIBlockProperty::GetList(["SORT" => "ASC"], ["ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"]]);
			while ($arProp = $rsProps->GetNext()) {

				if($arProp['CODE'] == 'OP_USER_ID'){
					continue;
				}

				// показ в строку
				if(preg_match('/___ROW/', $arProp['CODE'])){
					$arResult['ITEMS'][$arProp['CODE']]['IS_ROW'] = true;
				}else{
					$arResult['ITEMS'][$arProp['CODE']]['IS_ROW'] = false;
				}


				$arResult['ITEMS'][$arProp['CODE']]['NAME_QUEST'] = $arProp['NAME'];
				$arResult['ITEMS'][$arProp['CODE']]['MULTIPLE'] = $arProp['MULTIPLE'];
				$arResult['ITEMS'][$arProp['CODE']]['IS_REQUIRED'] = $arProp['IS_REQUIRED'];
				$arResult['ITEMS'][$arProp['CODE']]['TYPE_ANSWER'] = $arProp['PROPERTY_TYPE'];
				$arResult['ITEMS'][$arProp['CODE']]['HINT'] = $arProp['HINT'];

				if($arProp['USER_TYPE'] != null){
					$arResult['ITEMS'][$arProp['CODE']]['TYPE_ANSWER'] = $arProp['USER_TYPE'];
				}
			}

			$rsAnswer = CIBlockPropertyEnum::GetList(["SORT" => "ASC"], ["IBLOCK_ID" => $arParams["IBLOCK_ID"]]);
			while ($arAnswer = $rsAnswer->GetNext()){
				$arResult['ITEMS'][$arAnswer['PROPERTY_CODE']]['ANSWER'][$arAnswer['ID']] = $arAnswer['VALUE'];
			}
			$this->includeComponentTemplate();
		}
	}

	// прошел ли пользователь опрос
	public function alreadySurvey($userId){
		$rsUsers = CIBlockElement::GetList(["SORT" => "ASC"], ["ACTIVE" => "Y", "=PROPERTY_OP_USER_ID" => $userId, "IBLOCK_ID" => $this->arParams['IBLOCK_ID']], false, false, ["ID"]);
		return $rsUsers->SelectedRowsCount() > 0 ? true : false;
	}
	public function  getUserCity($userId, $request){

		if($cityId = $this->getUserCityProfile($userId)){
			return $cityId;
		}
		elseif(!empty($request->getCookie("S_CITY_ID"))){
			return $request->getCookie("S_CITY_ID");
		}
		elseif(!empty($_SESSION['GEOIP']['D_CITY_ID'])){
			return $_SESSION['GEOIP']['D_CITY_ID'];
		}
		else{
			\Bitrix\Main\Loader::includeModule('altasib.geoip');
			$resGeoIP = ALX_GeoIP::GetAddr();
		}

		return $resGeoIP['D_CITY_ID'];
	}
	public function getUserCityProfile($userId){
		$result = \Bitrix\Main\UserTable::getList(array(
			'filter' => array('ID'=>$userId,'ACTIVE'=>'Y'),
			'select' => array('ID','UF_CITY_ID'),
			'limit' => 1
		));
		while ($arUser = $result->fetch()) {
			$idCity = $arUser['UF_CITY_ID'];
		}
		return $idCity;
	}
}
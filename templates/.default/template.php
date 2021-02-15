<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="survey">
    <div class="survey_list">
        <?if(isset($arResult["ALREADY_FINISHED"])):?>
            <p class="survey_finish"><?=$arResult["ALREADY_FINISHED"];?></p>
        <?else:?>
        <form action="<?=$componentPath . "/ajax.php"?>" method="post" class="survey_form">
			<?foreach ($arResult["ITEMS"] as $code => $question):?>
				<?if($question["TYPE_ANSWER"] == "L"):?>
                <?$typeInput = $question["MULTIPLE"] == "Y" ? "checkbox" : "radio";?>
                    <div class="survey_item">
                        <p class="survey_title">
                            <?=$question["NAME_QUEST"];?>
							<?if($question['HINT'] != ""):?>
                                <span class="survey_hint"><?=$question['HINT']?></span>
						    <?endif;?>
                        </p>
                        <ul class="survey_questions <?if($question['IS_ROW']):?>quest_row<?endif;?>">
                            <?foreach ($question["ANSWER"] as $id => $answer):?>
                                <li>
                                    <label class="survey_checkbox">
                                        <input type="<?=$typeInput?>"
                                               value="<?=$id?>"
                                               name="<?=$code?><?if($question["MULTIPLE"] == "Y"):?>[]<?endif;?>"
                                               class="<?if($question['IS_REQUIRED'] == "Y"):?>validate[required]<?endif;?>">
                                        <span><?=$answer;?></span>
                                    </label>
                                </li>
                            <?endforeach;?>
                        </ul>
                    </div>
                <?endif;?>
                <?if($question["TYPE_ANSWER"] == "HTML" || $question["TYPE_ANSWER"] == "SP_IBlockPropertyLongtext"):?>
                    <div class="survey_item-textarea">
                        <textarea name="<?=$code;?>" id="" placeholder="<?=$question["NAME_QUEST"]?>" class="<?if($question['IS_REQUIRED'] == "Y"):?>validate[required]<?endif;?>"></textarea>
                    </div>
                <?endif;?>
			<?endforeach;?>
            <div class="survey_btns">
                <input type="submit" value="<?=GetMessage("SURVEY_SUBMIT");?>" class="survey_btn-green">
                <?if($arResult['FLAG_MOBILE'] == ''):?>
                    <a href="/personal/order/" class="survey_btn-gray"><?=GetMessage("SURVEY_CLOSE");?></a>
                <?endif;?>
            </div>
        </form>
        <?endif;?>
    </div>
</div>

<div class="survey_coupon-modal" style="display: none">
    <a href="<?=$arResult['FLAG_MOBILE'];?>/personal/order/" class="survey_close"></a>
    <div class="survey_coupon-container">
        <div class="survey_coupon-title">
			<?=GetMessage("SURVEY_THANKS_MODAL");?>
        </div>
        <div class="copied" style="display: none;"><?=GetMessage("SURVEY_COUPON_COPY");?></div>
        <div class="survey_coupon-yourpromo">
			<?=GetMessage("SURVEY_YOUR_COUPON");?>
        </div>
        <div class="survey_coupon-number">

        </div>
    </div>
</div>
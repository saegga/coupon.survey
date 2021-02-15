$(document).ready(function () {

    var bool = false;

    $(".survey_form").on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var data = form.serialize();
        if (form.validationEngine('validate') && !bool){
            bool = true;
            var url = form.attr("action");

            $.fancybox.showLoading();

            $.ajax({
                type: "POST",
                url: url,
                data: data,
                success: function (data) {
                    var response = JSON.parse(data);
                    if(response.SUCCESS){
                        showCoupon(response.COUPON);
                    }else{
                        $.fancybox.hideLoading();
                        showError(response.ERROR_MSG);
                        console.log(response);
                    }
                    bool = false;

                },
                error: function (response) {
                    $.fancybox.hideLoading();
                    console.log(response);
                    bool = false;
                }
            })
        }
    });
    function showCoupon(coupon) {
        var modal = $('.survey_coupon-modal');
        var divCoupon =  modal.find($('.survey_coupon-number'));

        divCoupon.text(coupon);

        $(divCoupon).on('click', copyCoupon);

        $.fancybox.open(modal, {
            autoSize:    true,
            padding:     0,
            fitToView:   false,
            openEffect:  'none',
            closeEffect: 'none',
            closeBtn:    false,
            scrolling:   'no',
            arrows: false,
            helpers: {
                title:   null,
                overlay: {
                    closeClick: false,
                    css:{
                        'background': 'transparent'
                    }
                }
            },
            afterShow: function () {
                $.fancybox.hideLoading();
            }
        });
    }
    function showError(error) {
        var modal = $('.survey_coupon-modal');

        $('.survey_coupon-modal .survey_coupon-number').remove();
        $('.survey_coupon-modal .survey_coupon-yourpromo').remove();
        $('.survey_coupon-modal .survey_close').attr("href", "");
        $('.survey_coupon-modal .copied').remove();
        $('.survey_coupon-modal .survey_coupon-container').append("<p class='err_msg'>" + error + "</p>");

        $.fancybox.open(modal, {
            autoSize:    true,
            padding:     0,
            fitToView:   false,
            openEffect:  'none',
            closeEffect: 'none',
            closeBtn:    false,
            scrolling:   'no',
            arrows: false,
            helpers: {
                title:   null,
                overlay: {
                    closeClick: false,
                    css:{
                        'background': 'transparent'
                    }
                }
            },
            afterShow: function () {
                $.fancybox.hideLoading();
            }
        });
    }

    function copyCoupon() {

        var temp = $("<input>");
        $("body").append(temp);

        temp.val($(this).text()).select();
        document.execCommand("copy");
        temp.remove();

        $('.survey_coupon-modal').find(".copied").show();
        setTimeout(function(){
            $('.survey_coupon-modal').find(".copied").hide();
        }, 1000);
    }
});
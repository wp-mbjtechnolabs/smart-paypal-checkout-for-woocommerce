jQuery(function ($) {
    if (typeof psb_pay_later_messaging === 'undefined') {
        return false;
    }
    var front_end_home_page_pay_later_messaging_preview = function () {
        var home_style_object = {};
        home_style_object['layout'] = psb_pay_later_messaging.pay_later_messaging_home_layout_type;
        if (home_style_object['layout'] === 'text') {
            home_style_object['logo'] = {};
            home_style_object['logo']['type'] = psb_pay_later_messaging.pay_later_messaging_home_text_layout_logo_type;
            if (home_style_object['logo']['type'] === 'primary' || home_style_object['logo']['type'] === 'alternative') {
                home_style_object['logo']['position'] = psb_pay_later_messaging.pay_later_messaging_home_text_layout_logo_position;
            }
            home_style_object['text'] = {};
            home_style_object['text']['size'] = parseInt(psb_pay_later_messaging.pay_later_messaging_home_text_layout_text_size);
            home_style_object['text']['color'] = psb_pay_later_messaging.pay_later_messaging_home_text_layout_text_color;
        } else {
            home_style_object['color'] = psb_pay_later_messaging.pay_later_messaging_home_flex_layout_color;
            home_style_object['ratio'] = psb_pay_later_messaging.pay_later_messaging_home_flex_layout_ratio;
        }
        if (typeof paypal !== 'undefined') {
            paypal.Messages({
                placement: 'home',
                style: home_style_object
            }).render('.psb_message_home');
        }
    };
    front_end_home_page_pay_later_messaging_preview();
});
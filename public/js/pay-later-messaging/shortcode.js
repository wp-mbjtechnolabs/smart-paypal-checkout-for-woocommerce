jQuery(function ($) {
    if (typeof psb_pay_later_messaging === 'undefined') {
        return false;
    }
    var front_end_shortcode_page_pay_later_messaging_preview = function () {
        var shortcode_style_object = {};
        shortcode_style_object['layout'] = psb_pay_later_messaging.style;
        if (shortcode_style_object['layout'] === 'text') {
            shortcode_style_object['logo'] = {};
            shortcode_style_object['logo']['type'] = psb_pay_later_messaging.logotype;
            if (shortcode_style_object['logo']['type'] === 'primary' || psb_pay_later_messaging.logotype === 'alternative') {
                shortcode_style_object['logo']['position'] = psb_pay_later_messaging.logoposition;
            }
            shortcode_style_object['text'] = {};
            shortcode_style_object['text']['size'] = parseInt(psb_pay_later_messaging.textsize);
            shortcode_style_object['text']['color'] = psb_pay_later_messaging.textcolor;
        } else {
            shortcode_style_object['color'] = psb_pay_later_messaging.color;
            shortcode_style_object['ratio'] = psb_pay_later_messaging.ratio;
        }
        if (typeof paypal !== 'undefined') {
            paypal.Messages({
                amount: psb_pay_later_messaging.amount,
                placement: psb_pay_later_messaging.placement,
                style: shortcode_style_object
            }).render('.psb_message_shortcode');
        }
    };
    front_end_shortcode_page_pay_later_messaging_preview();
});
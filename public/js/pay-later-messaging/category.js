jQuery(function ($) {
    if (typeof psb_pay_later_messaging === 'undefined') {
        return false;
    }
    var front_end_category_page_pay_later_messaging_preview = function () {
        var category_style_object = {};
        category_style_object['layout'] = psb_pay_later_messaging.pay_later_messaging_category_layout_type;
        if (category_style_object['layout'] === 'text') {
            category_style_object['logo'] = {};
            category_style_object['logo']['type'] = psb_pay_later_messaging.pay_later_messaging_category_text_layout_logo_type;
            if (category_style_object['logo']['type'] === 'primary' || category_style_object['logo']['type'] === 'alternative') {
                category_style_object['logo']['position'] = psb_pay_later_messaging.pay_later_messaging_category_text_layout_logo_position;
            }
            category_style_object['text'] = {};
            category_style_object['text']['size'] = parseInt(psb_pay_later_messaging.pay_later_messaging_category_text_layout_text_size);
            category_style_object['text']['color'] = psb_pay_later_messaging.pay_later_messaging_category_text_layout_text_color;
        } else {
            category_style_object['color'] = psb_pay_later_messaging.pay_later_messaging_category_flex_layout_color;
            category_style_object['ratio'] = psb_pay_later_messaging.pay_later_messaging_category_flex_layout_ratio;
        }
        if (typeof paypal !== 'undefined') {
            paypal.Messages({
                placement: 'category',
                style: category_style_object
            }).render('.psb_message_category');
        }
    };
    front_end_category_page_pay_later_messaging_preview();
});
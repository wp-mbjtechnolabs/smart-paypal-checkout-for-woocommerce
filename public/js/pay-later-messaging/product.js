jQuery(function ($) {
    if (typeof psb_pay_later_messaging === 'undefined') {
        return false;
    }
    if ($('.variations_form').length) {
        $('.variations_form').on('show_variation', function () {
            $('.psb_message_product').show();
        }).on('hide_variation', function () {
            $('.psb_message_product').hide();
        });
    }
    var front_end_product_page_pay_later_messaging_preview = function () {
        var product_style_object = {};
        product_style_object['layout'] = psb_pay_later_messaging.pay_later_messaging_product_layout_type;
        if (product_style_object['layout'] === 'text') {
            product_style_object['logo'] = {};
            product_style_object['logo']['type'] = psb_pay_later_messaging.pay_later_messaging_product_text_layout_logo_type;
            if (product_style_object['logo']['type'] === 'primary' || product_style_object['logo']['type'] === 'alternative') {
                product_style_object['logo']['position'] = psb_pay_later_messaging.pay_later_messaging_product_text_layout_logo_position;
            }
            product_style_object['text'] = {};
            product_style_object['text']['size'] = parseInt(psb_pay_later_messaging.pay_later_messaging_product_text_layout_text_size);
            product_style_object['text']['color'] = psb_pay_later_messaging.pay_later_messaging_product_text_layout_text_color;
        } else {
            product_style_object['color'] = psb_pay_later_messaging.pay_later_messaging_product_flex_layout_color;
            product_style_object['ratio'] = psb_pay_later_messaging.pay_later_messaging_product_flex_layout_ratio;
        }
        if (typeof paypal !== 'undefined') {
            paypal.Messages({
                amount: psb_pay_later_messaging.amount,
                placement: 'product',
                style: product_style_object
            }).render('.psb_message_product');
        }
    };
    front_end_product_page_pay_later_messaging_preview();
});
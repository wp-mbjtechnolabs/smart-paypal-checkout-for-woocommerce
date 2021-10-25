jQuery(function ($) {
    if (typeof psb_pay_later_messaging === 'undefined') {
        return false;
    }
    var front_end_cart_page_pay_later_messaging_preview = function () {
        var cart_style_object = {};
        cart_style_object['layout'] = psb_pay_later_messaging.pay_later_messaging_cart_layout_type;
        if (cart_style_object['layout'] === 'text') {
            cart_style_object['logo'] = {};
            cart_style_object['logo']['type'] = psb_pay_later_messaging.pay_later_messaging_cart_text_layout_logo_type;
            if (cart_style_object['logo']['type'] === 'primary' || cart_style_object['logo']['type'] === 'alternative') {
                cart_style_object['logo']['position'] = psb_pay_later_messaging.pay_later_messaging_cart_text_layout_logo_position;
            }
            cart_style_object['text'] = {};
            cart_style_object['text']['size'] = parseInt(psb_pay_later_messaging.pay_later_messaging_cart_text_layout_text_size);
            cart_style_object['text']['color'] = psb_pay_later_messaging.pay_later_messaging_cart_text_layout_text_color;
        } else {
            cart_style_object['color'] = psb_pay_later_messaging.pay_later_messaging_cart_flex_layout_color;
            cart_style_object['ratio'] = psb_pay_later_messaging.pay_later_messaging_cart_flex_layout_ratio;
        }
        $('.psb_message_cart').addClass('psb_' + psb_pay_later_messaging.pay_later_messaging_cart_layout_type);
        if (typeof paypal !== 'undefined') {
            paypal.Messages({
                amount: psb_pay_later_messaging.amount,
                placement: 'cart',
                style: cart_style_object
            }).render('.psb_message_cart');
        }
    };
    front_end_cart_page_pay_later_messaging_preview();
    $(document.body).on('updated_cart_totals updated_checkout', function () {
        front_end_cart_page_pay_later_messaging_preview();
    });

});
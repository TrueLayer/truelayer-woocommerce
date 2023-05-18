import { Payment } from 'truelayer-embedded-payment-page'

/* global truelayerParams */
jQuery(function ($) {
    var truelayerForWooCommerce = {
        init: function () {
            window.addEventListener("hashchange", truelayerForWooCommerce.handleHashChange);
        },

        handleHashChange: function() {
            const currentHash = location.hash;

            // If the hash doesn't start with #truelayer, we don't care about it, so bail.
            if (currentHash.indexOf('#truelayer=') !== 0) {
                return;
            }

            // Remove the #truelayer prefix from the hash.
            const valueWithoutPrefix = decodeURIComponent(currentHash.replace('#truelayer=', ''));

            // Parse the value into a URL object.
            const url = new URL(valueWithoutPrefix);

            // Get the payment ID and token from the URL.
            const args = new URLSearchParams(url.search);
            const paymentId = args.get('payment_id');
            const paymentToken = args.get('token');

            truelayerForWooCommerce.initializeEpp(url.href, paymentId, paymentToken);
        },

        initializeEpp: function (url, paymentId, paymentToken) {
            const payment = Payment({
                payment_id: paymentId,
                resource_token: paymentToken,
                return_uri: url,
                production: truelayerParams.testmode === 'no',
                onAbort: () => {
                    let redirect = url + '&error=tl_hpp_cancel';
                    window.location.href = redirect;
                },
                onError: error => {
                    let redirect = url + '&error=tl_hpp_error';
                    window.location.href = redirect;
                },
            });

            payment.start();
        },
    };

    truelayerForWooCommerce.init();
});

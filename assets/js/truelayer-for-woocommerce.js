/* global truelayerParams */
jQuery(function ($) {
    var truelayerForWooCommerce = {
        Truelayer: null,

        Payment: null,

        init: function () {
            window.addEventListener("hashchange", truelayerForWooCommerce.handleHashChange);
            this.Truelayer = window.Truelayer;
            this.Payment = this.Truelayer?.Payment;
        },

        handleHashChange: function() {
            var currentHash = location.hash;
            var splittedHash = currentHash.split(":");
            if( splittedHash[0] === "#truelayer" ){
                var url = atob( splittedHash[1] );
                var payment_id = atob( splittedHash[2] );
                var payment_token = atob( splittedHash[3] );
                truelayerForWooCommerce.initializeEpp(url, payment_id, payment_token);
            }
        },

        initializeEpp: function (url, payment_id, payment_token) {
            if (!this.Payment) {
                console.log("Truelayer Payment is not initialized");
                return;
            }

            const payment = this.Payment({
                payment_id: payment_id,
                resource_token: payment_token,
                return_uri: url,
                production: truelayerParams.production,
                onHandoffStart: () => {
                    let redirect = decodeURIComponent(url) + '?payment_id=' + payment_id;
                    window.location.href = redirect;
                },
                onAbort: () => {
                    let redirect = decodeURIComponent(url) + '?payment_id=' + payment_id + '&error=tl_hpp_cancel';
                    window.location.href = redirect;
                },
                onError: error => {
                    let redirect = decodeURIComponent(url) + '?payment_id=' + payment_id + '&error=tl_hpp_error';
                    window.location.href = redirect;
                },
            });

            payment.start();
        },
    };

    truelayerForWooCommerce.init();
});

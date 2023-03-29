
import { Payment } from 'truelayer-embedded-payment-page';
// jQuery(function ($) {
//     var truelayerForWooCommerce = {
//         init: function () {
//             window.addEventListener("hashchange", truelayerForWooCommerce.handleHashChange);
//         },
//
//         handleHashChange: function() {
//             var currentHash = location.hash;
//             var splittedHash = currentHash.split(":");
//             console.log(12345);
//             if( splittedHash[0] === "#truelayer" ){
//                 var url = atob( splittedHash[1] );
//                 var payment_id = atob( splittedHash[2] );
//                 var payment_token = atob( splittedHash[3] );
//                 truelayerForWooCommerce.addDomNode();
//                 truelayerForWooCommerce.initializeEpp(url, payment_id, payment_token);
//             }
//         },
//
//         addDomNode: function() {
//             console.log(1111);
//             $('body').append( `<div class="truelayer-wrapper" id="truelayer-wrapper"></div>` )
//         },
//
//         initializeEpp: function( url, payment_id, payment_token ) {
//             console.log(456);
//
//
//             const payment = Payment({
//                 payment_id: payment_id,
//                 resource_token: payment_token,
//                 return_uri: url,
//                 target: document.getElementById('truelayer-wrapper')
//             });
//             console.log(payment);
//
//             payment.start();
//         },
//     };
//
//     truelayerForWooCommerce.init();
// });



document.addEventListener('DOMContentLoaded', () => {

    saveButton = document.getElementsByName('save');

	const pluginEnabledCheckbox = document.getElementById('woocommerce_truelayer_enabled');
	const testmodeCheckbox = document.getElementById('woocommerce_truelayer_testmode');

    let beneficiaryMerchantAccount = document.getElementById('woocommerce_truelayer_truelayer_beneficiary_merchant_account_id');
    let beneficiaryAccountHolderName = document.getElementById('woocommerce_truelayer_truelayer_beneficiary_account_holder_name');

	let clientId = document.getElementById('woocommerce_truelayer_truelayer_client_id');
	let sharedSecret = document.getElementById('woocommerce_truelayer_truelayer_client_secret');
    let certificate = document.getElementById('woocommerce_truelayer_truelayer_client_certificate');
	let privateKey = document.getElementById('woocommerce_truelayer_truelayer_client_private_key');

	let testClientId = document.getElementById('woocommerce_truelayer_truelayer_sandbox_client_id');
	let testSharedSecret = document.getElementById('woocommerce_truelayer_truelayer_sandbox_client_secret');
	let testCertificate = document.getElementById('woocommerce_truelayer_truelayer_sandbox_client_certificate');
	let testPrivateKey = document.getElementById('woocommerce_truelayer_truelayer_sandbox_client_private_key');

    let beneficiarySet = [ beneficiaryMerchantAccount, beneficiaryAccountHolderName ];
    let liveEnvironmentSet = [ clientId, sharedSecret, certificate, privateKey ];
    let testEnvironmentSet = [ testClientId, testSharedSecret, testCertificate, testPrivateKey ];

    // Check for empty fields in the Test Environment.
    const checkBeneficiaryFields = () => {

        if(pluginEnabledCheckbox.checked){
            if((beneficiaryAccountHolderName.value !== '')) {
                saveButton[0].removeAttribute('disabled');
            } else {
                saveButton[0].setAttribute('disabled', true);
            }

        } else {
            saveButton[0].removeAttribute('disabled');
        }
    }

    // Toggle Live/Sandbox display.
    const fieldsDisplayAvailability = (shifter) => {

        // Production field group
        let productionField = document.getElementById('woocommerce_truelayer_truelayer_credentials')
        let productionFieldLabels = [...productionField.nextSibling.nextSibling.querySelectorAll('tbody')];

        // Sandbox field group
        let sandboxField = document.getElementById('woocommerce_truelayer_truelayer_sandbox_credentials')
        let sandboxFieldLabels = [...sandboxField.nextSibling.nextSibling.querySelectorAll('tbody')];

        if (shifter == 'testEnvironment') {
            // Production Fields Disabled
            productionField.style.display = "none";
            productionFieldLabels[0].style.display = "none";

            // Sandbox Fields Disabled
            sandboxField.style.display = "block";
            sandboxFieldLabels[0].style.display = "block";

        } else if (shifter === 'liveEnvironment') {
            // Production Fields Enabled
            productionField.style.display = "block";
            productionFieldLabels[0].style.display = "block";

            // Sandbox Fields Disabled
            sandboxField.style.display = "none";
            sandboxFieldLabels[0].style.display = "none";
        }
    }

    // Check for empty fields in the Test Environment.
    const checkTestButtonValues = () => {

        if(testmodeCheckbox.checked){
            if((testClientId.value !== '') && (testSharedSecret.value !== '') && (testCertificate.value !== '') && (testPrivateKey.value !== '')) {
                saveButton[0].removeAttribute('disabled');
            } else {
                saveButton[0].setAttribute('disabled', true);
            }

        } else {
            saveButton[0].removeAttribute('disabled');
        }
    }

    // Check for empty fields in the Live Environment.
    const checkButtonValues = () => {

        if(!testmodeCheckbox.checked){
            if((clientId.value !== '') && (sharedSecret.value !== '') && (certificate.value !== '') && (privateKey.value !== '')) {
                saveButton[0].removeAttribute('disabled');
            } else {
                saveButton[0].setAttribute('disabled', true);
            }

        }  else {
            saveButton[0].removeAttribute('disabled');
        }
    }

	// On page start, check if Test mode is enabled
    if (testmodeCheckbox.checked) {
        fieldsDisplayAvailability('testEnvironment');
        checkTestButtonValues();

    } else {
        fieldsDisplayAvailability('liveEnvironment');
        checkButtonValues()
    }

    // Check for field values upon enabling/disabling Test mode
	testmodeCheckbox.addEventListener('change', () => {
        if(testmodeCheckbox.checked){
            fieldsDisplayAvailability('testEnvironment');
		    checkTestButtonValues();

        } else {
            fieldsDisplayAvailability('liveEnvironment');
            checkButtonValues();
        }
	})

    // Disable/enable button upon all Sandbox input fields empty/non-empty.
    testEnvironmentSet.forEach(element => {
        element.addEventListener('input', () => {
            checkTestButtonValues();
        })
    });


    // Disable/enable button upon all Live input fields empty/non-empty.
    liveEnvironmentSet.forEach(element => {
        element.addEventListener('input', () => {
            checkButtonValues();
        })
    });

    // On plugin start, check if the beneficiary fields have values.
    checkBeneficiaryFields();

    // Disable/enable button upon all Beneficiary input fields empty/non-empty.
    beneficiarySet.forEach(element => {
        element.addEventListener('input', () => {
            checkBeneficiaryFields();
        })
    });

    // Check for field values upon enabling/disabling Test mode
	pluginEnabledCheckbox.addEventListener('change', () => {
        checkBeneficiaryFields();
	})
})

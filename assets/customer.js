jQuery(document).ready(function () {

    window.onload = function() {

        params = bonusPlusWp.get_params(); 
        
        isElemetsExist = bonusPlusWp.is_elements_exist();
        isFormExist = bonusPlusWp.is_form_exist();

        if (typeof document.getElementById("qrcode") !== 'undefined' && document.getElementById("qrcode") != null){
            if (params['card_number']){ 
                const cardNumber = params['card_number']; 
                bonusPlusWp.qrcode_render(cardNumber);
                hide(document.getElementById("loader"));
            }
        }

        // Регистрация
        if (isElemetsExist){
            show(document.getElementById('bpwp-verify-start'));
            // Запрос СМС
            document.getElementById("bpwpSendSms").addEventListener("click", function() {
                if (params['phone']){
                    const phoneCustomer = params['phone']; 
                    bonusPlusWp.bp_send_sms(phoneCustomer);
                }
            });

            hide(document.getElementById("loader"));
        }

    }
});

    function hide(elements) {
        elements = elements.length ? elements : [elements];
        for (var index = 0; index < elements.length; index++) {
            elements[index].style.display = 'none';
        }
    }

    function show(elements) {
        elements = elements.length ? elements : [elements];
        for (var index = 0; index < elements.length; index++) {
            elements[index].style.display = 'block';
        }
    }

    var bonusPlusWp = {

        /**
         *  prepare localization user data 
         */
        get_params: function () {
            var arrayResult = {};
            accountBonusPlusData = window['accountBonusPlusData'];
            arrayResult['phone'] = accountBonusPlusData['phone'];
            arrayResult['card_number'] = accountBonusPlusData['cardNumber'];
            arrayResult['redirect'] = accountBonusPlusData['redirect'];
            arrayResult['is_debug'] = accountBonusPlusData['debug'];
            return arrayResult;
        },

        /**
         * Проверяет наличие элементов
         * 
         * @returns boolean
         */
        is_elements_exist: function(){
            var verifyContainerStart = document.getElementById("bpwp-verify-start");
                verifyContainerEnd = document.getElementById("bpwp-verify-end");
                sendSmsButton = document.getElementById("bpwpSendSms");
                sendOtpButton = document.getElementById("bpwpSendOtp");
                bonusesInput = document.getElementById("bpwpBonusesInput");
                otpInput = document.getElementById("bpwpOtpInput");
            if (typeof verifyContainerStart !== 'undefined' && verifyContainerStart != null && typeof verifyContainerEnd !== 'undefined' &&
                verifyContainerEnd != null && typeof sendSmsButton !== 'undefined' && sendSmsButton != null && typeof sendOtpButton !== 'undefined' &&
                sendOtpButton != null) {
                    return 1;
            } else {
                    return 0;
            }
        },

        /**
         * Проверяет наличие элементов
         * 
         * @returns boolean
         */
        is_form_exist: function(){
            var verifyContainerStart = document.getElementById("bpwp-verify-start");
                verifyContainerEnd = document.getElementById("bpwp-verify-end");
                sendSmsButton = document.getElementById("bpwpSendSms");
                sendOtpButton = document.getElementById("bpwpSendOtp");
                otpInput = document.getElementById("bpwpOtpInput");
            if (typeof verifyContainerStart !== 'undefined' && verifyContainerStart != null && typeof verifyContainerEnd !== 'undefined' &&
                verifyContainerEnd != null && typeof sendSmsButton !== 'undefined' && sendSmsButton != null && typeof sendOtpButton !== 'undefined' &&
                sendOtpButton != null) {
                    return 1;
            } else {
                    return 0;
            }
        },

        // Отправка запроса SMS на телефон 
        bp_send_sms: async function(phoneCustomer){
            const data = {
                phone: phoneCustomer,
            };

            jQuery.ajax( {
                url: wpApiSettings.root + 'wp/v1/sendcode',
                method: 'POST',
                beforeSend: function ( xhr ) {
                    show(document.getElementById("loader"));            
                    hide(document.getElementById("bpwp-verify-start"));
                    //hide(document.getElementById("bpwpOtpInput"));
                    hide(document.getElementById("bpmsg"));
                    document.getElementById("bpwpSendSms").disabled = true;
                    
                    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                },
                complete: function () {
                    hide(document.getElementById("loader"));
                }
            } )
            .done( function( response ) {
                if (response.success) {
                    document.getElementById('bpmsg').innerHTML = response.message;
                    show(document.getElementById('bpmsg'));
                    show(document.getElementById("bpwp-verify-end"));
                
                document.getElementById("bpwpSendOtp").addEventListener("click", function() {
                    hide(document.getElementById("bpwp-verify-end"));
                    otpInput = document.getElementById('bpwpOtpInput');
                    bonusesInput = document.getElementById('bpwpBonusesInput');
                    if (typeof otpInput !== 'undefined' && otpInput != null){
                        code = otpInput.value;
                        let debit = bonusesInput ? bonusesInput.value : 0;
                        if (code != null){
                            bonusPlusWp.bp_check_code(phoneCustomer, code, params['redirect'], debit);
                        }
                    }                    
                });
                hide(document.getElementById("loader"));
                show(document.getElementById("bpwp-verify-end"));

                } else {
                    document.getElementById('bpmsg').innerHTML = response.message;
                    show(document.getElementById('bpmsg'));
                    show(document.getElementById("bpwp-verify-start"));
                    document.getElementById("bpwpSendSms").disabled = false;
                    
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                console.log(textStatus);
            })
        },

        // Отправка запроса - проверка кода 
        bp_check_code: async function(phoneCustomer, code, redirect, debit = null){
            
            jQuery.ajax( {
                url: wpApiSettings.root + 'wp/v1/checkcode',
                method: 'POST',
                data: {                         
                    phone : phoneCustomer,
                    code : code,
                    debit: debit,
                },
                beforeSend: function ( xhr ) {
                    // Показываем лоадер
                    show(document.getElementById("loader"));            
                    hide(document.getElementById("bpmsg"));
                    document.getElementById("bpwpSendOtp").disabled = true;
                    
                    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                },
                complete: function () {
                    hide(document.getElementById("loader"));
                }
            } )
            .done( function( response ) {
            if (response.success && response.customer_created) {
                document.getElementById('bpmsg').innerHTML = 'Подтверждено, сейчас вы будете перенаправлены!';
                show(document.getElementById('bpmsg'));
                window.location.href = redirect;
            } else if (response.success && response.debit_bonuses) {
                document.getElementById('bpmsg').innerHTML = 'Списание бонусов';
                show(document.getElementById('bpmsg'));
                
                jQuery.ajax({
                    type: "post",
                    url:  wc_checkout_params.ajax_url,
                    data: {
                        'action' : 'set_bpwp_debit_bonuses',
                        'bonuses' : response.debit_bonuses
                    },
                    success: function(response) {
                        jQuery('body').trigger('update_checkout');
                    },
                    error: function(error){
                        console.log('error: '+error);
                    }
                });

            } else {
                document.getElementById('bpmsg').innerHTML = 'Код не верный, попробуйте еще раз';
                show(document.getElementById('bpmsg'));
                show(document.getElementById('bpwp-verify-start'));
                document.getElementById("bpwpSendSms").disabled = false;
                document.getElementById("bpwpSendOtp").disabled = false;
                }
                
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                console.log(textStatus);
            })
        },

        /**
         *  Render QR code
         */
        qrcode_render: function (cardNumber){
            if (cardNumber) { 
                var elem = document.getElementById("qrcode");
                qrcode = new QRCode(elem, {
                    text: cardNumber,
                    width: 147,
                    height: 147,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        },
    }
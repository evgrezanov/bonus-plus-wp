jQuery( 'document' ).ready( function( $ ) {
    window.onload = function() {
        params = bonusPlusWp.get_params();  
        console.log(params);
        isElemetsExist = bonusPlusWp.is_elements_exist();
        if (typeof params['card_number'] != 'undefined' && params['card_number'] != null /*&& isElemetsExist > 0*/){
            if (params['card_number']){ 
                const cardNumber = params['card_number']; 
                console.log(cardNumber);
                //console.log('have card number, not need add listener - render QRcode');
                bonusPlusWp.qrcode_render(cardNumber);
            } else {
                // console.log('add event listener click send SmS');
                document.getElementById("bpwpSendSms").addEventListener("click", function() {
                    hide(document.getElementById("bpwp-verify-start"));
                    document.getElementById("bpwpSendSms").disabled = true;
                    //document.querySelector(".preload").style.display = "block";
                    bonusPlusWp.bp_send_sms(params['send_sms_uri'], params['authKey']);
                    show(document.getElementById("bpwp-verify-end"));
                });
            }
        }
    }

    function hide(elements){
        elements = elements.length ? elements : [elements];
        for (var index = 0; index < elements.length; index++) {
            elements[index].style.display = 'none';
        }
    }

    /**
    *  Show dom element
    * 
    * @param {*} elements 
    */
    function show(elements){
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
            arrayResult['authKey'] = accountBonusPlusData['auth'];
            arrayResult['send_sms_uri'] = accountBonusPlusData['sendSmsUri'];
            arrayResult['send_otp_uri'] = accountBonusPlusData['sendOtpUri'];
            arrayResult['redirect'] = accountBonusPlusData['redirect'];
            arrayResult['ajax_url'] = accountBonusPlusData['ajax_url'];
            arrayResult['card_number'] = accountBonusPlusData['cardNumber'];
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
         * Запрос SMS  c кодом
         * 
         * @param {string} uri 
         * @param {string} authKey 
         */
        bp_send_sms: async function(uri, authKey){
            let myHeaders = new Headers();
            myHeaders.append('Authorization', 'ApiKey '+authKey);

            const myInit = {
                method: 'PUT',
                headers: myHeaders
            };

            let smsRequest = new Request(uri);
            let response = await 
                fetch(smsRequest, myInit)
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                            console.log(`HTTP error! status: ${response.status}`);
                        }
                        if (response.status == 204){
                            // все ок
                            return response.blob();
                        }
                    })
                    .then(function() {
                        console.log('SMS sended...');
                        document.getElementById("bpwpSendOtp").addEventListener("click", function() {
                            hide(document.getElementById("bpwp-verify-end"));
                            otpInput = document.getElementById('bpwpOtpInput');
                            if (typeof otpInput !== 'undefined' && otpInput != null){
                                code = otpInput.value;
                                if (code != null){
                                    sendOtpUri = params['send_otp_uri'] + code + '/';
                                    bonusPlusWp.bp_send_otp(sendOtpUri, params['authKey'], params['redirect'], params['ajax_url']);
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.log('Error:');
                        console.log(error);
                    });
        },

        /**
         *  Отправка OTP в Б+
         */
        bp_send_otp: async function(sendOtpUri, authKey, redirect, ajax_url){
            hide(document.getElementById("bpwp-verify-end"));
            const spinner = document.getElementById("spinner");
            spinner.removeAttribute('hidden');

            let myHeaders = new Headers();
            myHeaders.append('Authorization', 'ApiKey '+authKey);

            const myOTPInit = {
                method: 'PUT',
                headers: myHeaders
            };

            let otpRequest = new Request(sendOtpUri);
            let response = await 
                fetch(otpRequest, myOTPInit)
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                            console.log(`HTTP error! status: ${response.status}`);
                        }
                        if (response.status == 204){
                            console.log('Phone OTP compleate, get customer data from Bonus plus...');
                            return response.blob();
                        }
                    })
                    .then(function() {
                        // Update user meta
                        $.ajax({
                            url : ajax_url,                 
                            type: 'POST',                   
                            data: {                         
                                action  : 'bpwp_cv',
                            }
                        })
                        .success( function( response ) {
                            userData = response.request;
                            spinner.setAttribute('hidden', '');
                            document.getElementById('bpmsg').innerHTML = 'Подтверждено, сейчас вы будете перенаправлены!';
                            show(document.getElementById('bpmsg'));
                            window.location.href = redirect;
                        })
                        .fail( function( data ) {
                            console.log( 'Customer data updated request FAILED: ' + data.statusText + responseText );
                        })
                        .error( function(error){ 
                            console.log( 'Request error!');
                            console.log(error) 
                        });
                    })
                    .catch(error => {
                        console.log('Error:');
                        console.log(error);
                    });
        },

        /**
         *  Render QR code
         */
        qrcode_render: function (cardNumber){
        if (cardNumber) { 
            console.log('generate qr code');
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

    };
});
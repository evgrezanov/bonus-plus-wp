jQuery( 'document' ).ready( function( $ ) {
    window.onload = function() {
        //show(document.getElementById("loader"));

        params = bonusPlusWp.get_params();  
        
        isElemetsExist = bonusPlusWp.is_elements_exist();

        bonusPlusWp.bp_get_client(params['registration_uri'], params['authKey'], params['client_info'], params['ajax_url']);

        if (typeof params['card_number'] != 'undefined' && params['card_number'] != null /*&& isElemetsExist > 0*/){
            if (params['card_number']){ 
                const cardNumber = params['card_number']; 
                console.log(cardNumber);
                bonusPlusWp.qrcode_render(cardNumber);
                
            } else {
                // no have cardnumber, so need register
                if (isElemetsExist){
                    // Регистрация
                    document.getElementById("bpwpRegistration").addEventListener("click", function() {
                        bonusPlusWp.bp_registration(params['registration_uri'], params['authKey'], params['client_info'], params['ajax_url']);
                    });
                    // Запрос СМС
                    document.getElementById("bpwpSendSms").addEventListener("click", function() {
                        bonusPlusWp.bp_send_sms(params['send_sms_uri'], params['authKey']);
                    });
                }
            }
            
        }
        hide(document.getElementById("loader"));
        
        show(document.getElementById('bpwp-registration'));
    }

    function checkOtpSentStatus() {
        return sessionStorage.getItem('otpSent');
    }
    
    function setOtpSentStatus(status) {
        sessionStorage.setItem('otpSent', status);
    }
    
    /**
    *  hide dom element
    * 
    * @param {*} elements 
    */
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
            arrayResult['registration_uri'] = accountBonusPlusData['registrationUri'];
            arrayResult['redirect'] = accountBonusPlusData['redirect'];
            arrayResult['ajax_url'] = accountBonusPlusData['ajax_url'];
            arrayResult['card_number'] = accountBonusPlusData['cardNumber'];
            arrayResult['is_debug'] = accountBonusPlusData['debug'];
            arrayResult['client_info'] = accountBonusPlusData['clientInfo'];
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
            hide(document.getElementById("bpwp-verify-start"));
            show(document.getElementById("loader"));            
            document.getElementById("bpwpSendSms").disabled = true;

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
                        setOtpSentStatus(true);
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
                        hide(document.getElementById("loader"));
                        show(document.getElementById("bpwp-verify-end"));
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
            if (checkOtpSentStatus()) {
                hide(document.getElementById("bpwp-verify-end"));
                //const spinner = document.getElementById("loader");
                //spinner.removeAttribute('hidden');

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
                                setOtpSentStatus(false); // Сброс статуса отправки OTP после успешного подтверждения
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
                                document.getElementById('bpmsg').innerHTML = 'Подтверждено, сейчас вы будете перенаправлены!';
                                show(document.getElementById('bpmsg'));
                                window.location.href = redirect;
                            })
                            .fail( function( data ) {
                                console.log(data);
                                console.log('Customer data updated request FAILED: ' + data.statusText);
                            })
                            .error( function(error){ 
                                console.log( 'Request error!');
                                console.log(error) 
                            });
                        });
                        /*.catch(error => {
                        console.log('Error:');
                        console.log(error);
                    });*/
            } else {
                // Показать сообщение пользователю, что OTP не был отправлен или запросить отправку OTP снова
                error: function(xhr, status, error) {
                    console.error('Ошибка при отправке SMS: ', error);
                    // Показать сообщение пользователю, что OTP не был отправлен
                    const errorMsg = document.getElementById('bpwpErrorMsg');
                    errorMsg.innerHTML = 'Ошибка при отправке SMS с OTP-кодом. Пожалуйста, попробуйте еще раз.';
                    show(errorMsg);
                
                    // Добавляем обработчик событий для кнопки повторного запроса OTP
                    const retryBtn = document.getElementById('bpwpRetrySendSms');
                    show(retryBtn);
                    retryBtn.addEventListener('click', function() {
                        hide(errorMsg);
                        hide(retryBtn);
                        bonusPlusWp.bp_send_sms(params['send_sms_uri'], params['authKey']);
                    }, false);
                }
            }
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

        /**
         *  Registration at BonusPlus
         */
        bp_registration: function (reg_url, authKey, clientInfo, ajax_url){
            hide(document.getElementById("bpwp-registration"));
            show(document.getElementById("loader"));            
            document.getElementById("bpwpRegistration").disabled = true;

            // Register user
            var settings = {
                "url": reg_url,
                "method": "POST",
                "timeout": 0,
                "headers": {
                    "Authorization": 'ApiKey '+authKey,
                    "Content-Type": "application/json"
                },
                "data": clientInfo,
            };

            $.ajax(settings)
                .done(function (response) {
                    console.log(response);
                    document.getElementById('bpmsg').innerHTML = 'Регистрация завершена!';
                    // регистрация прошла успешно, запишем мету
                    $.ajax({
                        url : ajax_url,                 
                        type: 'POST',                   
                        data: {                         
                            action  : 'bpwp_cv',
                        }
                    })
                    .done( function( response ) {
                        userData = response.request;
                        console.log(userData);
                            
                    });
                    hide(document.getElementById('loader'));
                    show(document.getElementById('bpmsg'));
                    show(document.getElementById("bpwp-verify-start"));
                    
                })
                .fail(function(jqXHR, textStatus, errorThrown){
                    console.log(jqXHR.responseText);
                    console.log(textStatus);
                    document.getElementById('bpmsg').innerHTML = 'При регистрации произошла ошибка <pre>' + jqXHR.responseText + '</pre>';
                    hide(document.getElementById('loader'));
                    show(document.getElementById('bpmsg'));
                })
        },
        
        /**
         * Return client data from API
         * 
         * @param {*} url for request
         * @param {*} authKey 
         * @param {*} clientInfo 
         * @param {*} ajax_url 
         */
        bp_get_client: function (url, authKey, clientInfo, ajax_url){
            url = "https://bonusplus.pro/api/customer?phone=79119387283";
            var csettings = {
                "url": "https://bonusplus.pro/api/customer?phone=79119387283",
                "method": "GET",
                "timeout": 0,
                "headers": {
                    "Authorization": 'ApiKey '+authKey,
                    "Content-Type": "application/json"                
                },
                "statusCode":   {
                    412: function (response) { // CUSTOMER_NOT_FOUND || CUSTOMER_ALREADY_EXIST
                        console.log('pizdec');
                        console.log(response);
                        show(document.getElementById('bpwp-registration'));
                    },
                },
            };

            $.ajax(csettings)
            .done(function (response) {
                console.log(response);
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                console.log(textStatus);
            })
        },

        bp_show_client_data: function(){

        },

        bp_show_registration_form: function(){

        },

        bp_show_send_sms_form: function(){

        },

        bp_show_send_otp_form: function(){

        },

        bp_show_myaccount_page: function(){

        },
    }
});
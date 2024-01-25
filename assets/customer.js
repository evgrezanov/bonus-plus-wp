jQuery( 'document' ).ready( function( $ ) {

    window.onload = function() {
        
        hide(document.getElementById("loader"));
        
        document.getElementById("bpwpSendSms").addEventListener("click", function() {
            // Получаем телефон и отправлем запрос - на этот номер код в SMS
            const phoneCustomer = this.getAttribute('data-phone');
            bonusPlusWp.bp_send_sms(phoneCustomer);
        });

        const cardNumber = document.getElementById("qrcode").getAttribute('data-cardnumber');

        console.log(cardNumber);
        if (cardNumber) {
            bonusPlusWp.qrcode_render;
        }

    }


});

    /**
    *  hide dom element
    * 
    * @param {*} elements 
    */
    function hide(elements) {
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
    function show(elements) {
        elements = elements.length ? elements : [elements];
        for (var index = 0; index < elements.length; index++) {
            elements[index].style.display = 'block';
        }
    }

    var bonusPlusWp = {

        // Отправка запроса SMS на телефон 
        bp_send_sms: async function(phoneCustomer){
            const data = {
                // Здесь добавьте данные, которые хотите передать
                phone: phoneCustomer,
            };

            jQuery.ajax( {
                url: wpApiSettings.root + 'wp/v1/sendcode',
                method: 'POST',
                beforeSend: function ( xhr ) {
                    // Показываем лоадер
                    hide(document.getElementById("bpwp-verify-start"));
                    show(document.getElementById("loader"));            
                    document.getElementById("bpwpSendSms").disabled = true;
                    
                    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                },
                complete: function () {
                    // Прячем лоадер или удаляем его из DOM
                    hide(document.getElementById("loader"));
                }
            } )
            .success( function( response ) {
                console.log( response );
                if (response.success) {
                show(document.getElementById("bpwp-verify-end"));
                
                document.getElementById("bpwpSendOtp").addEventListener("click", function() {
                    hide(document.getElementById("bpwp-verify-end"));
                    otpInput = document.getElementById('bpwpOtpInput');
                    if (typeof otpInput !== 'undefined' && otpInput != null){
                        code = otpInput.value;
                        if (code != null){
                            bonusPlusWp.bp_check_code(phoneCustomer, code);
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
            .error( function(error){ // ??
                console.log( 'Request error!');
                console.log(error) 
            });
        },

        // Отправка запроса - проверка кода 
        bp_check_code: async function(phoneCustomer, code){
            const data = {
                // Здесь добавьте данные, которые хотите передать
                phone: phoneCustomer,
                code: code,
            };

            console.log(data);

            jQuery.ajax( {
                url: wpApiSettings.root + 'wp/v1/checkcode',
                method: 'POST',
                data: {                         
                    phone : phoneCustomer,
                    code : code,
                },
                beforeSend: function ( xhr ) {
                    // Показываем лоадер
                    show(document.getElementById("loader"));            
                    //document.getElementById("bpwpSendOtp").disabled = true;
                    
                    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                },
                complete: function () {
                    // Прячем лоадер или удаляем его из DOM
                    hide(document.getElementById("loader"));
                }
            } )
            .success( function( response ) {
                console.log( response );
                document.getElementById('bpmsg').innerHTML = 'Подтверждено!';
                show(document.getElementById('bpmsg'));
                hide(document.getElementById("loader"));
                
                // Вывести данные карты response.userdata

                // Показать QR код
                //bonusPlusWp.qrcode_render(response.cardnumber);

                const qrcodeElement = document.getElementById('qrcode');
                let dataCardValue = qrcodeElement.dataset.card;
                if (dataCardValue != '') {
                    let qrcode = new QRCode(qrcodeElement, {
                        text: dataCardValue,
                        width: 147,
                        height: 147,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
                
            })
            .fail( function( data ) {
                console.log(data);
                console.log('Customer data updated request FAILED: ' + data.statusText);
            })
            .error( function(error){ 
                console.log( 'Request error!');
                console.log(error) 
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
    }
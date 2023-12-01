jQuery( 'document' ).ready( function( $ ) {
        // Регистрация
        document.getElementById("bpwpRegistration").addEventListener("click", function() {
            
            const accountContent = $('.woocommerce-MyAccount-content');
            const bpwpRegistration = $('#bpwp-registration');

            $.ajax( {
                url: wpApiSettings.root + 'wp/v1/getcustomer',
                method: 'GET',
                beforeSend: function ( xhr ) {
                    // Показываем лоадер или добавляем его в DOM
                    // const loader = '<div class="loader" style="display:none">Загружаем данные...</div>';
                    $('#loader').show();
                    bpwpRegistration.empty();
                    // accountContent.append(loader);
                    xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
                },
                complete: function () {
                    // Прячем лоадер или удаляем его из DOM
                    $('#loader').hide();
                }
            } ).done( function ( response ) {
                console.log( response );
                
                const bonusPlusInfo = $(response); // Если получаем HTML
                accountContent.append(bonusPlusInfo);

                // Показать QR код
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
            } );
            

            /*
            fetch(wpApiSettings.root + 'wp/v1/getcustomer', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': wpApiSettings.nonce
                }
            })
            //.then(response => response.json())
            .then(data => {
                console.log(data);
                // Дальнейшая обработка результата
            })
            .catch(error => {
                console.log(error);
                // Обработка ошибки
            });
            */

        });
        // Запрос СМС
        // document.getElementById("bpwpSendSms").addEventListener("click", function() {
        //     bonusPlusWp.bp_send_sms(params['send_sms_uri'], params['authKey']);
        // });
});
jQuery( 'document' ).ready( function( $ ) {
        // Регистрация
        document.getElementById("bpwpRegistration").addEventListener("click", function() {

            $.ajax( {
                url: wpApiSettings.root + 'wp/v1/getcustomer',
                method: 'GET',
                beforeSend: function ( xhr ) {
                    xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
                },
                data:{
                    'title' : 'Hello Moon'
                }
            } ).done( function ( response ) {
                console.log( response );
            } );


        });
        // Запрос СМС
        // document.getElementById("bpwpSendSms").addEventListener("click", function() {
        //     bonusPlusWp.bp_send_sms(params['send_sms_uri'], params['authKey']);
        // });
});
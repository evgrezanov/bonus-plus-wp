jQuery( 'document' ).ready( function( $ ) {
  window.onload = function() {
    var requestBonusPlusData = window['requestBonusPlusData'];
      authKey = requestBonusPlusData['auth'];
      uri = requestBonusPlusData['sendSmsUri'];
      redirect = requestBonusPlusData['redirect'];
      ajax_url = requestBonusPlusData['ajax_url'];
    // customer card number
    var discountCardNumber = window['discountCardNumber'];
      cardNumber = discountCardNumber['cardNumber'];
    var verifyContainerStart = document.getElementById("bpwp-verify-start");
      verifyContainerEnd = document.getElementById("bpwp-verify-end");
      sendSmsButton = document.getElementById("bpwpSendSms");
      sendOtpButton = document.getElementById("bpwpSendOtp");
      otpInput = document.getElementById("bpwpOtpInput");

    if (typeof verifyContainerStart !== 'undefined' && verifyContainerStart != null && typeof verifyContainerEnd !== 'undefined' &&
      verifyContainerEnd != null && typeof sendSmsButton !== 'undefined' && sendSmsButton != null && typeof sendOtpButton !== 'undefined' &&
      sendOtpButton != null) {
        
            hide(verifyContainerEnd);

            // add event click send SMS
            document.getElementById("bpwpSendSms").addEventListener("click", function() {
              hide(verifyContainerStart);
              sendSms();
              show(verifyContainerEnd);
            });

            // add event click send OTP
            document.getElementById("bpwpSendOtp").addEventListener("click", function() {
              hide(verifyContainerEnd);
              //show(loader);
              otpInput = document.getElementById('bpwpOtpInput');
              if (typeof otpInput !== 'undefined' && otpInput != null){
                code = otpInput.value;
                if (code != null){
                  sendOtpUri = requestBonusPlusData['sendOtpUri'] + code + '/';
                  sendOtp(sendOtpUri);
                }
              }
            });
    }

    /* 
      Usage:
        hide(document.querySelectorAll('.target'));
        hide(document.querySelector('.target'));
        hide(document.getElementById('target')); 
    */
    function hide (elements) {
        elements = elements.length ? elements : [elements];
        for (var index = 0; index < elements.length; index++) {
          elements[index].style.display = 'none';
        }
    }

    function show (elements) {
        elements = elements.length ? elements : [elements];
        for (var index = 0; index < elements.length; index++) {
          elements[index].style.display = 'block';
        }
    }

    /**
     *  Отправка SMS для подтверждения телефона
     */
    function sendSms(){
        var xhr = new XMLHttpRequest();

        xhr.withCredentials = false;

        xhr.addEventListener("readystatechange", function() {
            if(this.readyState === 4 && this.responseText != null && this.responseText != '') {
              console.log(this.responseText);
            }
        });

        xhr.open("PUT", uri);
        xhr.setRequestHeader("Authorization", "ApiKey "+authKey);

        xhr.send();
    }

    /**
       *  Отправка OTP в Б+
       */
    function sendOtp(sendOtpUri){
        var xhr = new XMLHttpRequest();
        xhr.withCredentials = false;
        xhr.addEventListener("readystatechange", function() {
          //show(loader);
          if(this.readyState === 4 && this.responseText != '') {
            document.querySelector('.msg').innerHTML = 'Подтверждено, сейчас вы будете перенаправлены!';
            updateClientMeta();
          } else {
            response = xhr.response;
            if (typeof response.msg !== 'undefined' && response.msg != null){
              document.querySelector('.msg').innerHTML = response.msg;
            }
          }
          //hide(loader);
          show(document.querySelector('.msg'));
        });

        xhr.open("PUT", sendOtpUri);
        xhr.setRequestHeader("Authorization", "ApiKey "+authKey);

        xhr.send();
    }

    /**
       *  Вызов ajax для обновления данных о верификации
       */
    function updateClientMeta(){
        $.ajax({
          url : ajax_url,                 
          type: 'POST',                   
            data: {                         
              action  : 'bpwp_cv',
            }
        })
        .success( function( response ) {
            console.log( 'User Meta Updated!' );
            console.log( response );
            window.location.href = redirect;
        })
        .fail( function( data ) {
            console.log( data.responseText );
            console.log( 'Request failed: ' + data.statusText );
        })
        .error( function(error){ 
          console.log(error) 
        });
    }

    /**
     *  Rende4 QR code
     */
    function qrcodeRender(cardNumber){
      if (cardNumber != '') {
          var elem = document.getElementById("qrcode");
          var qrcode = new QRCode(elem, {
              text: cardNumber,
              width: 147,
              height: 147,
              colorDark: "#000000",
              colorLight: "#ffffff",
              correctLevel: QRCode.CorrectLevel.H
          });
      }
    }
  }
});

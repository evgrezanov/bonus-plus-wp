window.onload = function() {
  var verifyContainerStart = document.getElementById("bpwp-verify-start");
      verifyContainerEnd = document.getElementById("bpwp-verify-end");
      sendSmsButton = document.getElementById("bpwpSendSms");
      sendOtpButton = document.getElementById("bpwpSendOtp");
      otpInput = document.getElementById("bpwpOtpInput");
      
      if (
        typeof verifyContainerStart !== 'undefined' && 
        verifyContainerStart != null &&
        typeof verifyContainerEnd !== 'undefined' &&
        verifyContainerEnd != null &&
        typeof sendSmsButton !== 'undefined' &&
        sendSmsButton != null &&
        typeof sendOtpButton !== 'undefined' &&
        sendOtpButton != null
      ){
        
        hide(verifyContainerEnd);

        // add event click send SMS
        document.getElementById("bpwpSendSms").addEventListener("click", function() {
          hide(verifyContainerStart);
          sendSms();
          show(verifyContainerEnd);
        });

        // add event click send OTP
        document.getElementById("bpwpSendOtp").addEventListener("click", function() {
          otpInput = document.getElementById('bpwpOtpInput');
          if (typeof otpInput !== 'undefined' && otpInput != null){
            code = otpInput.value;
            if (code != null){
              sendOtpUri = requestBonusPlusData['sendOtpUri'] + '/' + code + '/';
              sendOtp(sendOtpUri);
            }
          }
        });

      }

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
    var requestBonusPlusData = window['requestBonusPlusData'];
    authKey = requestBonusPlusData['auth'];
    uri = requestBonusPlusData['sendSmsUri'];

    var xhr = new XMLHttpRequest();

    xhr.withCredentials = false;

    xhr.addEventListener("readystatechange", function() {
      if(this.readyState === 4) {
        console.log(this.responseText);
      } else {
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
    var requestBonusPlusData = window['requestBonusPlusData'];
    authKey = requestBonusPlusData['auth'];

    var xhr = new XMLHttpRequest();
    xhr.withCredentials = false;

    xhr.addEventListener("readystatechange", function() {
      if(this.readyState === 4) {
        console.log(this.responseText);
      }
    });

    xhr.open("PUT", sendOtpUri);
    xhr.setRequestHeader("Authorization", "ApiKey "+authKey);

    xhr.send();
  }
}

window.onload = function() {
    // customer card number
    var discountCardNumber = window['discountCardNumber'];
    cardNumber = discountCardNumber['cardNumber'];
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
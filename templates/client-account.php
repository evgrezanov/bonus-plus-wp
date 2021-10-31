<div id="verify-phone-dialog">

    <div id="loader" class="center-body">
        <div class="loader-ball-8"></div>
    </div>

    <div hidden id="bpmsg" class="msg"></div>

    <div id="qrcode"></div>

    <div id='bpwp-registration' style="display:none;">
        <p><?= __('Вы еще не зарегистрированы в программе лояльности', 'bonus-plus-wp') ?>
        </p>
        <button id="bpwpRegistration"><?= __('Продолжить регистрацию', 'bonus-plus-wp') ?></button>
    </div>

    <div id='bpwp-verify-start' style="display:none;">
        <p><?= __('Для завершения регистрации в Бонус+, подтвердите номер телефона, после отправки СМС', 'bonus-plus-wp') ?>
            <strong><?= $phone ?></strong>
        </p>
        <button id="bpwpSendSms"><?= __('Отправить SMS c кодом подтверждения', 'bonus-plus-wp') ?></button>
    </div>

    <div id='bpwp-verify-end' style="display:none;">
        <p><?= __('Введите код высланый в SMS, на номер телефона:', 'bonus-plus-wp') ?>
            <strong><?= $phone ?></strong>
        </p>
        <input id="bpwpOtpInput" type="number" maxLength="1" size="6" min="0" max="999999" pattern="[0-9]{6}" />
        <button id="bpwpSendOtp"><?= __('Подтвердить', 'bonus-plus-wp') ?></button>
    </div>
</div>
<div id="verify-phone-dialog">

    <div id="loader" class="center-body">
        <div class="loader-ball-8"></div>
    </div>

    <div hidden id="bpmsg" class="msg"></div>

    <div id="qrcode"></div>

    <div id='bpwp-registration' style="display:none;">
        <p><?php echo __('Вы еще не зарегистрированы в программе лояльности', 'bonus-plus-wp') ?>
        </p>
        <button id="bpwpRegistration"><?php echo __('Продолжить регистрацию', 'bonus-plus-wp') ?></button>
    </div>

    <div id='bpwp-verify-start' style="display:none;">
        <p><?php echo __('Для завершения регистрации в Бонус+, подтвердите номер телефона, после отправки СМС', 'bonus-plus-wp') ?>
        <strong><?php echo $phone; ?></strong>
        </p>
        <button id="bpwpSendSms"><?php echo __('Отправить SMS c кодом подтверждения', 'bonus-plus-wp'); ?></button>
    </div>

    <div id='bpwp-verify-end' style="display:none;">
        <p><?php echo __('Введите код высланый в SMS, на номер телефона:', 'bonus-plus-wp'); ?></p>
        <strong><?php echo $phone; ?></strong>
        </p>
        <input id="bpwpOtpInput" type="number" maxLength="1" size="6" min="0" max="999999" pattern="[0-9]{6}" />
        <button id="bpwpSendOtp"><?php echo __('Подтвердить', 'bonus-plus-wp'); ?></button>
    </div>
</div>
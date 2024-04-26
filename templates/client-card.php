<h2><?php esc_html_e('Информация по карте лояльности', 'bonus-plus-wp'); ?></h2>
<div class="bp-info-card">
    <div class="bp-card-type"><?php echo esc_html($discountCardName); ?></div>
    <br>
    <div id="qrcode"></div>
    <br>
    <div class="bp-card-number" alt="<?php esc_attr_e('Номер карты', 'bonus-plus-wp'); ?>"><?php echo esc_html($discountCardNumber); ?></div>
    <span class="bp-availeble-bonuses"><?php esc_html_e('Доступно бонусов: ', 'bonus-plus-wp'); ?><span class="bp-bonuses"><?php echo esc_html($availableBonuses); ?></span></span>
    <span class="bp-total-spend"><?php printf(esc_html__('Сумма покупок: %s', 'bonus-plus-wp'), esc_html($purchasesTotalSum)); ?></span>
    <span class="bp-next-card-type"><?php printf(esc_html__('Сумма покупок до следующего статуса: %s', 'bonus-plus-wp'), esc_html($purchasesSumToNextCard)); ?></span>
</div>

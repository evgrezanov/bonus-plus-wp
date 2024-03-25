<h2>Информация по карте лояльности</h2>
<div class=bp-info-card>
  <div class="bp-card-type"><?php echo $discountCardName; ?></div>
  <br>
  <div id="qrcode"></div>  
  <br>
  <div class="bp-card-number" alt="Номер карты"><?php echo $discountCardNumber; ?></div>
    <span class="bp-availeble-bonuses">Доступных бонусов: <span class="bp-bonuses"><?php echo $availableBonuses; ?></span></span>
    <span class="bp-total-spend">Сумма покупок: <?php echo $purchasesTotalSum ?></span>
    <span class="bp-next-card-type">Покупок для смены карты: <?php echo $purchasesSumToNextCard ?></span>
</div>
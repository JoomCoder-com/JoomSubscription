<button class="btn btn-large btn-warning" type="button">
	<select name="ya_type" id="ya_type">
		<option value="PC">Яндекс.Деньги</option>
		<option value="AC">Банковская карта</option>
		<option value="MC">Мобильный телефон</option>
		<option value="GP">Касса или терминал</option>
		<option value="WM">WebMoney</option>
		<option value="SB">SMS или Сбербанк Онлайн</option>
		<option value="MP">Мобильный терминал (mPOS)</option>
		<option value="AB">Альфа-Клик</option>
		<option value="MA">MasterPass</option>
		<option value="PB">Промсвязьбанк</option>
		<option value="QW">QIWI Wallet</option>
	</select>
	<br>
	<p id="yaButton">
		Оплатить через Яндекс.Деьги
	</p>
</button>
<style>
	.chzn-results li {
		color: black;
	}
</style>
<script>
	(function($){
		$('#yaButton').click(function(){
			Joomsubscription.checkout('yandex');
		});
	}(jQuery))
</script>
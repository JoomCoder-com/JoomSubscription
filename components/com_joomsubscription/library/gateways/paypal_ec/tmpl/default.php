<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 3/1/17
 * Time: 18:26
 */
?>

<div id="ppec_btn">
	<img src="https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypal-60px.png" alt="PayPal">
</div>
<script>
	(function($){
		$('#ppec_btn').click(function(){
			Joomsubscription.checkout('paypal_ec');
		});
	}(jQuery))
</script>


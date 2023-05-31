<?php
JFactory::getDocument()->addScript('https://checkout.stripe.com/checkout.js');
?>

<button id="customButton" class="btn btn-large btn-warning" type="button">Purchase</button>

<script>
	(function($) {
		var token = function(token) {
			$('input[name="task"]').val('empayment.send');
			$('input[name="processor"]').val('stripe');
			$('#formsubscr')
				.append($(document.createElement('input'))
					.attr({
						name: 'stripe_token',
						type: 'hidden'
					})
					.val(token.id))
				.submit();
		};


		$(window).on('popstate', function() {
			handler.close();
		});

		$('#customButton').on('click', function(e) {
			$(this).prop('disabled', true);

			if(!Joomsubscription.validate_form()) {
				$(this).prop('disabled', false);
				return;
			}

			var handler = StripeCheckout.configure({
				key: '<?php echo JText::_($this->params->get('publish_key', 'pk_test_6pRNASCoBOKtIshFeQd4XMUh')) ?>',
				<?php if(JFile::exists(JPATH_ROOT.$this->params->get('logo'))): ?>
				image: '<?php echo $this->params->get('logo') ?>',
				<?php endif; ?>
				locale: 'auto',
				name: '<?php echo JText::_($this->params->get('name', 'Site name')) ?>',
				email: <?php echo JFactory::getUser()->get('email') ?  "'".JFactory::getUser()->get('email')."'" : "$('#em_email').val()" ?>,
				billingAddress: <?php echo $this->params->get('billaddr') ? 'true' : 'false' ?>,
				description: '<?php echo $plan->name ?>',
				alipay: 'auto',
				bitcoin: <?php echo $this->params->get('bitcoin') ? 'true' : 'false' ?>,
				currency: '<?php echo $this->params->get('currency', 'USD') ?>',
				amount: <?php echo str_replace('.', '', number_format($total, 2, '.', '')) ?>,
				token: token
			});

			handler.open();
		});

		<?php if(JFactory::getApplication()->input->get('validation') == 1): ?>
		<?php endif; ?>

	}(jQuery));
</script>

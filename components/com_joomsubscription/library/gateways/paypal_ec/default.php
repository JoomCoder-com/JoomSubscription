<div class="page-header">
    <h1>PayPal Payment Confirmation</h1>
</div>
<p>
    Your order is ready to pe purchased with PayPal
</p>
<table class="table table-stripped table-bordered">
<tr>
    <td><?php echo JText::_('EMR_PLANNAME'); ?></td>
    <td><?php echo $plan->name; ?> [<?php echo $plan->cname ?>]</td>
</tr>
<tr>
    <td><?php echo JText::_('EMR_INVOICETOTAL'); ?></td>
    <td><?php echo JoomsubscriptionApi::getPrice($amount, $plan->params); ?></td>
</tr>
</table>
<script src="https://www.paypalobjects.com/api/checkout.js" data-version-4></script>
<div id="paypal-button"></div>

<script>
    paypal.Button.render({
        env: '<?php echo $this->params->get('sandbox', 'production') ?>',
        locale: '<?php echo $this->params->get('btn_lang', 'en_US') ?>',
        style: {
            size: '<?php echo $this->params->get('btn_size', 'small'); ?>',
            color: '<?php echo $this->params->get('btn_color', 'orange'); ?>',
            shape: '<?php echo $this->params->get('btn_shape', 'bill'); ?>'
        },
        client: {
            sandbox:    "<?php echo $this->params->get('client_id_sandbox', '') ?>",
            production: "<?php echo $this->params->get('client_id_production', '') ?>"
        },
        commit: true,
        payment: function() {
            var c = this.props.client;
            var e = this.props.env;
            return paypal.rest.payment.create(e, c, {
                //intent: "sale",
                transactions:[
                    {
                        amount:{
                            total: "<?php echo $amount ?>",
                            currency: "<?php echo $this->params->get('currency', 'USD'); ?>"
                        },
                        item_list: {
                            items: [
                                {
                                sku: "<?php echo $subscription->id ?>",
                                name: "<?php echo $name ?>",
                                description: "<?php echo $name ?>",
                                quantity: "1",
                                price: "<?php echo $amount ?>",
                                currency: "<?php echo $this->params->get('currency', 'USD'); ?>"
                            }
                            ]
                        },
                        description: "<?php echo $name ?>",
                        custom: "TEST",
                        invoice_number: "<?php echo $subscription->id ?>",
                        soft_descriptor: "MINT<?php echo $subscription->id ?>",
                        notify_url: "<?php echo $this->_get_notify_url($subscription->id); ?>"
                    }
                ],
                redirect_urls: {
                    return_url: "<?php echo $this->_get_return_url($subscription->id); ?>",
                    cancel_url: "<?php echo $this->_get_return_url($subscription->id); ?>"
                }
            });
        },
        onAuthorize: function(data, actions) {
            return actions.payment.execute().then(function() {
                return actions.redirect();
            });
        },
        onCancel: function(data, actions) {
            return actions.redirect();
        }
    }, '#paypal-button');
</script>
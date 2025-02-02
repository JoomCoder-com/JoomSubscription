<?php
/**
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');
JHtml::_('formbehavior.chosen', 'select');
?>
<br/>
<div class="container-fluid">
	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post">
		<div class="row">
			<?php echo JHtml::_('select.genericlist', $this->inv_list, 'invoice', 'required class="col-12"', 'value', 'text', JFactory::getApplication()->input->get('invoice')); ?>
		</div>
		<div id="invoice_data" class="hide"></div>
		<button type="submit" class="btn btn-primary btn-large" id="apply-btn"><?php echo JText::_('EAPPLY'); ?></button>
		<input type="hidden" name="add_address" value="1">
	</form>
</div>

<script type="text/javascript">
	(function($) {

		var inv_dat = $('#invoice_data');
		var btn = $('#apply-btn');

		$('#invoice').bind('change keyup', function() {
			load($(this).val());
		});
		load($('#invoice').val());


		function load(value)
		{
			inv_dat.hide();
			btn.hide();
			value = parseInt(value);
			if(value > 0) {
				loadText(value);
			} else if(value == -1) {
				loadForm();
			}
		}

		function loadText(value) {
			$.ajax({
				url: '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=empayment.getinvoicetext', FALSE); ?>',
				type: 'GET',
				dataType: 'html',
				data: {id: value}
			}).done(function(html){
					inv_dat.html(html).slideDown('fast');
					btn.show();
				});
		}

		function loadForm() {
			$.ajax({
				url: '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=empayment.getinvoiceform', FALSE); ?>',
				dataType: 'html'
			}).done(function(html){
					inv_dat.html(html).slideDown('fast');
					btn.show();
					$('#invoiceto_fields_country')
						.chosen({
							disable_search_threshold: 10,
							allow_single_deselect:    true
						});
				});
		}
	}(jQuery))
</script>
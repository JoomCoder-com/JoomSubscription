<?php
/**
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');
$params = JComponentHelper::getParams('com_joomsubscription');
if($params->get('country'))
{
	$this->form->setValue('country', 'fields', $params->get('country'));
}
?>
<br/>
<?php echo $this->form->getControlGroups('fields'); ?>
<script type="text/javascript">
	(function($) {
		var state = $(document.createElement('div'))
			.css({
				'display':     'inline',
				"margin-left": '10px'
			});

		var vies = $('#invoiceto_fields_vies-lbl').closest('.control-group');

		$('#invoiceto_fields_country')
			.after(state)
			.bind('change keyup', function() {
				changeCountry($(this));
			});

		changeCountry($('#invoiceto_fields_country'));

		function changeCountry(el) {
			if(!el.val()) {
				return;
			}

			if($.inArray(el.val(), ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL', 'ES', 'FI', 'FR', 'GB', 'UK', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK']) == -1) {
				vies.addClass('hide');
			} else {
				vies.removeClass('hide');
			}

			state.html('<img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/load.gif" >');

			$.ajax({
				url:      '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emajax.getstates', FALSE); ?>',
				type:     'GET',
				dataType: 'html',
				data:     {id: el.val(), 'default': '<?php echo @$this->defaults['fields']['states']; ?>'}
			})
				.done(function(html) {
					state.html(html);
					$('#invoicetofieldsstate').chosen({
						disable_search_threshold: 10,
						allow_single_deselect:    true
					});
					Joomsubscription.redrawBS();
				})
		}

	}(jQuery));
</script>
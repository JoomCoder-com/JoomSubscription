<?php
/**
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<br/>
<address>
	<strong><?php echo $this->data->fields->get('billto'); ?></strong><br>
	<?php echo $this->data->fields->get('line1'); ?>
	<br/>
	<?php echo nl2br($this->data->fields->get('address')); ?>, <?php echo nl2br($this->data->fields->get('city')); ?>
	<br/>
	<br/>

	<?php if($this->data->fields->get('tax_id')): ?>
	<abbr title="<?php echo JText::_('E_INVOICE_TAX_ID'); ?>"><?php echo JText::_('E_INVOICE_TAX_ID'); ?>:</abbr>
	<?php if($this->data->fields->get('vies')): ?>
		<?php echo $this->data->fields->get('country_id'); ?>
	<?php endif; ?>
	<?php echo $this->data->fields->get('tax_id'); ?>
	<br/>
	<?php endif; ?>

	<?php if($this->data->fields->get('phone')): ?>
		<abbr title="<?php echo JText::_('E_INVOICE_PHONE'); ?>"><?php echo JString::substr(JText::_('E_INVOICE_PHONE'), 0, 1); ?>:</abbr> <?php echo $this->data->fields->get('phone'); ?>
	<?php endif; ?>
</address>
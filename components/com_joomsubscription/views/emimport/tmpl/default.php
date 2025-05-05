<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

/** @var \Joomla\Component\Content\Site\View\Form\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = \Joomla\CMS\Factory::getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$fieldset = $this->import->form->getFieldset();
?>
<script type="text/javascript">
	Joomsubscription.submitbutton = function(task)
	{
		Joomsubscription.submitform(task, document.getElementById('item-form'));
	}
</script>
<div class="page-header">
	<h1>
		<?php echo JText::sprintf('EIMPORT_S', JText::_($this->import->title));?>
	</h1>
</div>
<form method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
	<div class="form-actions">
	<div class="float-end btn-toolbar">
		<div>
			<button type="button" class="btn btn-primary <?php if(!$this->canImport) echo 'disabled'; ?>" onclick="<?php echo $this->canImport ? "Joomsubscription.submitbutton('emimport.run');" : 'javascript:void(0);'?>">
				<?php echo JText::_('EIMPORT'); ?>
			</button>
			<a type="button" class="btn" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emimports');?>">
				<?php echo JText::_('ECANCEL'); ?>
			</a>
		</div>
	</div>
	</div>
	<div class="row">
		<div class="col-6">
			<p>
				<?php echo $this->import->description; ?>
			</p>
			<fieldset class="adminform">
				<?php foreach ($fieldset as $field):?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
				<?php endforeach;?>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="type" value="<?php echo $this->import->type;?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
	Joomsubscription.redrawBS();
</script>
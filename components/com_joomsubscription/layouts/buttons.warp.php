<?php
/**
 * Cobalt by JoomCoder
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();
$app = JFactory::getApplication();
$controller = $app->input->getCmd('view', 'emplan');
?>
<div class="form-actions">
<div class="uk-float-right uk-button-toolbar">
	<?php //if(!$this->isCheckedOut()):?>
		<?php //if(in_array($this->tmpl_params->get('tmpl_core.form_show_apply_button'), $this->user->getAuthorisedViewLevels())):?>
			<button type="button" class="uk-button-submit uk-button uk-button-primary" onclick="Joomsubscription.submitbutton('<?php echo $controller;?>.apply');">
			<i class="uk-icon-check"></i>&nbsp;&nbsp;<?php echo JText::_('EAPPLY'); ?>
			</button>
		<?php //endif; ?>

		<div class="uk-button-group">
			<button type="button" class="uk-button uk-button-success" onclick="Joomsubscription.submitbutton('<?php echo $controller;?>.save');">
				<i class="uk-icon-save"></i>&nbsp;&nbsp;<?php echo JText::_('ESAVE'); ?>
			</button>
            <div class="uk-button-dropdown" data-uk-dropdown="{mode:'hover'}">
			<button class="uk-button uk-button-success" type="button">
				<i class="uk-icon-caret-down"></i>
			</button>
            <div class="uk-dropdown uk-dropdown-small">
			<ul class="uk-nav uk-nav-dropdown">
				<li>
					<a href="javascript:void(0);" onclick="Joomsubscription.submitbutton('<?php echo $controller;?>.save2new');">
						<?php echo JText::_('ESAVE2NEW'); ?>
					</a>
				</li>
				<?php if(JFactory::getApplication()->input->getInt('id')): ?>
					<li>
						<a href="javascript:void(0);" onclick="Joomsubscription.submitbutton('<?php echo $controller;?>.save2copy');">
							<?php echo JText::_('ESAVE2COPY'); ?>
						</a>
					</li>
				<?php endif; ?>
			</ul>
            </div>
            </div>
		</div>

	<?php //if(in_array($this->tmpl_params->get('tmpl_core.form_show_save_button'), $this->user->getAuthorisedViewLevels())):?>
	<?php //endif; ?>

		<?php //if(in_array($this->tmpl_params->get('tmpl_core.form_show_savenew_button'), $this->user->getAuthorisedViewLevels())):?>
		<?php //endif; ?>

		<?php //if(in_array($this->tmpl_params->get('tmpl_core.form_show_savecopy_button'), $this->user->getAuthorisedViewLevels()) && $this->item->id):?>
		<?php //endif; ?>
	<?php //endif; ?>

	<?php //if(in_array($this->tmpl_params->get('tmpl_core.form_show_close_button'), $this->user->getAuthorisedViewLevels())):?>
		<button type="button" class="uk-button-submit uk-button uk-button-danger" onclick="Joomsubscription.submitbutton('<?php echo $controller;?>.cancel');">
		<i class="uk-icon-remove"></i>&nbsp;&nbsp;<?php echo JText::_('ECANCEL'); ?>
		</button>
	<?php //endif; ?>
</div>
<div class="uk-clearfix"></div>
</div>
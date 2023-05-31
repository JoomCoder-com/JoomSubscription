<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('dropdown.init');
//JHtml::_('formbehavior.chosen', 'select');
?>

<script type="text/javascript">
	Joomsubscription.submitbutton = function(task) {
		if(task == 'emplan.cancel' || document.formvalidator.isValid('#item-form')) {
			Joomsubscription.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}

	jQuery(document).ready(function() {
		Joomsubscription.redrawBS();
	});
</script>
<style type="text/css">
	ul.unstyled input[type="text"] {
		width: 98%;
	}
	.alert-tiny {
		margin-bottom: 0;
	}
</style>
<form method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
<div class="page-header">
    <div class="pull-right">
        <?php
        $layout = Mint::loadLayout('buttons', $basePath = JPATH_COMPONENT . '/layouts');
        echo $layout->render(NULL);
        ?>    
    </div>
	<h1>
		<?php if($this->item->id): ?>
            <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/plans.png" />
			<?php echo JText::sprintf('EEDITPLAN', $this->item->name); ?>
		<?php else: ?>
            <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/plans.png" />
			<?php echo JText::_('ENEWPLAN'); ?>
		<?php endif; ?>
	</h1>
</div>

	<ul class="nav nav-tabs">
		<li class="active"><a href="#page-general" data-toggle="tab"><?php echo JText::_('FS_GENERAL') ?></a></li>
		<li><a href="#page-prop" data-toggle="tab"><?php echo JText::_('FS_PROPERTIES') ?></a></li>
		<li><a href="#page-restrict" data-toggle="tab"><?php echo JText::_('FS_RESTRICTIONS') ?></a></li>
		<li><a href="#page-actions" data-toggle="tab"><?php echo JText::_('FS_ACTIONS') ?></a></li>
		<li><a href="#page-gateway" data-toggle="tab"><?php echo JText::_('FS_GATEWAYS') ?></a></li>
		<li><a href="#page-cross-plans" data-toggle="tab"><?php echo JText::_('FS_CROSSPLANS') ?></a></li>
		<li><a href="#page-alerts" data-toggle="tab"><?php echo JText::_('FS_ALERTS') ?></a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="page-general">
			<div class="row-fluid">
				<div class="span7">
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('group_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('group_id'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('access'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('access'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('access_pay'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('access_pay'); ?></div>
					</div>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'main', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
				</div>
				<div class="span5">
					<?php echo MFormHelper::renderFieldset($this->params_form, 'period', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'period2', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'period3', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
				</div>
			</div>
			<div class="control-group row-fluid">
					<?php echo MFormHelper::renderFieldset($this->params_form, 'descriptions', $this->item->params, 'descriptions',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			</div>
		</div>

		<div class="tab-pane" id="page-prop">
			<div class="row-fluid">
				<div class="span7">
					<?php echo MFormHelper::renderFieldset($this->params_form, 'limits', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'properties', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'rds', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
				</div>
				<div class="span5">
					<?php echo MFormHelper::renderFieldset($this->params_form, 'grant', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'donation', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
				</div>
			</div>
		</div>

		<div class="tab-pane" id="page-restrict">
			<?php echo JoomsubscriptionRulesHelper::rules_form($this->item); ?>
		</div>

		<div class="tab-pane" id="page-actions">
			<?php echo JoomsubscriptionActionsHelper::actions_form($this->item); ?>
		</div>

		<div class="tab-pane" id="page-gateway">

			<?php echo MFormHelper::renderFieldset($this->params_form, 'maingate', $this->item->params, 'gateway',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>

			<?php $gateways = MFormHelper::getGateways($this->params_form, $this->item->params['gateways']); ?>
			<?php if(!empty($gateways)): ?>
				<div class="accordion" id="gateways">
					<?php foreach($gateways as $name => $gateway): ?>
						<div class="accordion-group">
							<div class="accordion-heading">
								<a class="accordion-toggle" data-toggle="collapse"
								   data-parent="#gateways" href="#<?php echo $name ?>">
									<?php echo $gateway['title']; ?>
								</a>
							</div>
							<div id="<?php echo $name ?>" class="accordion-body collapse">
								<div class="accordion-inner">
									<?php echo $gateway['html']; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<p></p>
			<small><?php echo JText::_('EMR_NEW_GATEWAY'); ?></small>
			</p>
		</div>

		<div class="tab-pane" id="page-cross-plans">
			<div class="row-fluid">
				<div class="span6">
					<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_period', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_deactivated', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_hide', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
				</div>
				<div class="span6">
					<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_require', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_grant', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
					<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_upgrade', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
				</div>
			</div>
		</div>


		<div class="tab-pane" id="page-alerts">
			<div class="row-fluid">
				<?php echo MFormHelper::renderFieldset($this->params_form, 'alerts', $this->item->params, 'alerts',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
				<?php echo MFormHelper::renderFieldset($this->params_form, 'messages', $this->item->params, 'alerts', MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_CLASSIC); ?>
			</div>
		</div>

	</div>
	<div class="clearfix"></div>

	<input type="hidden" name="task" value=""/> <input type="hidden" name="return" value="<?php echo $this->state->get('plan.return'); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
<script type="text/javascript">
	(function($) {
		$.each($('input[name$="\\[enable\\]"]'), function(k, v) {
			if(v.value == 0) {
				return true;
			}
			var el = $(v);
			var parent = el.closest('.accordion-group');

			if(el.is(':checked')) {
				set_bg(parent);
			}

			el.change(function() {
				if($(this).is(':checked')) {
					set_bg(parent);
				} else {
					unset_bg(parent);
				}
			});
		});

		function set_bg(parent) {
			$('.accordion-heading', parent).css('background-color', '#f0f0f0');
		}

		function unset_bg(parent) {
			$('.accordion-heading', parent).css('background-color', 'transparent');
		}

		$('#params_properties_price').keyup(function() {
			Joomsubscription.formatFloat(this, 2, 10);
		});
		$('#params_properties_discount').keyup(function() {
			Joomsubscription.formatFloat(this, 2, 10);
		});
		$('#params_properties_days').keyup(function() {
			Joomsubscription.formatInt(this);
		});
		$('#params_properties_purchase_limit').keyup(function() {
			Joomsubscription.formatInt(this);
		});
		$('#params_properties_purchase_limit_user').keyup(function() {
			Joomsubscription.formatInt(this);
		});
		$('#params_properties_purchase_limit_user_period').keyup(function() {
			Joomsubscription.formatInt(this);
		});
		$('#params_properties_count_limit').keyup(function() {
			Joomsubscription.formatInt(this);
		});

	}(jQuery))
</script>

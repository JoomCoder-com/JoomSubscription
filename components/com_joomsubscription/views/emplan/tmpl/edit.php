<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

/** @var \Joomla\Component\Content\Site\View\Form\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = \Joomla\CMS\Factory::getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

JHtml::_('dropdown.init');

// temporary will be removed on future versions
\Joomla\CMS\Factory::getDocument()->addScript(JURI::root(true) . '/components/com_joomsubscription/library/js/main.js');

?>

<style type="text/css">
    ul.unstyled input[type="text"] {
        width: 98%;
    }
    .alert-tiny {
        margin-bottom: 0;
    }
    /* BS5 accordion custom styles */
    .accordion-button {
        font-weight: 500;
    }
    .accordion-button.gateway-enabled {
        background-color: #f0f0f0;
    }
    .accordion-button:not(.collapsed).gateway-enabled {
        background-color: #e0e0e0;
    }
</style>
<form method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
    <div class="page-header d-flex justify-content-between">
        <h1>
			<?php if($this->item->id): ?>
                <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/plans.png" />
				<?php echo JText::sprintf('EEDITPLAN', $this->item->name); ?>
			<?php else: ?>
                <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/plans.png" />
				<?php echo JText::_('ENEWPLAN'); ?>
			<?php endif; ?>
        </h1>
		<?php echo \Joomla\CMS\Layout\LayoutHelper::render('core.edit.actionBar',[]) ?>
    </div>



	<?php echo HTMLHelper::_('uitab.startTabSet', 'plan', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'plan', 'general', Text::_('FS_GENERAL')); ?>

    <div class="row">
        <div class="col-7">
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
        <div class="col-5">
			<?php echo MFormHelper::renderFieldset($this->params_form, 'period', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			<?php echo MFormHelper::renderFieldset($this->params_form, 'period2', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			<?php echo MFormHelper::renderFieldset($this->params_form, 'period3', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
        </div>
    </div>
    <div class="control-group row">
		<?php echo MFormHelper::renderFieldset($this->params_form, 'descriptions', $this->item->params, 'descriptions',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
    </div>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'plan', 'prop', Text::_('FS_PROPERTIES')); ?>

    <div class="row">
        <div class="col-7">
			<?php echo MFormHelper::renderFieldset($this->params_form, 'limits', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			<?php echo MFormHelper::renderFieldset($this->params_form, 'properties', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			<?php echo MFormHelper::renderFieldset($this->params_form, 'rds', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
        </div>
        <div class="col-5">
			<?php echo MFormHelper::renderFieldset($this->params_form, 'grant', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			<?php echo MFormHelper::renderFieldset($this->params_form, 'donation', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
        </div>
    </div>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'plan', 'restrict', Text::_('FS_RESTRICTIONS')); ?>

	<?php echo JoomsubscriptionRulesHelper::rules_form($this->item); ?>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>


	<?php echo HTMLHelper::_('uitab.addTab', 'plan', 'actions', Text::_('FS_ACTIONS')); ?>

	<?php echo JoomsubscriptionActionsHelper::actions_form($this->item); ?>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'plan', 'gateway', Text::_('FS_GATEWAYS')); ?>

	<?php echo MFormHelper::renderFieldset($this->params_form, 'maingate', $this->item->params, 'gateway',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>

	<?php $gateways = MFormHelper::getGateways($this->params_form, $this->item->params['gateways']); ?>
	<?php if(!empty($gateways)): ?>
        <div class="accordion" id="gateways">
			<?php foreach($gateways as $name => $gateway): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-<?php echo $name ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse-<?php echo $name ?>" aria-expanded="false"
                                aria-controls="collapse-<?php echo $name ?>">
							<?php echo $gateway['title']; ?>
                        </button>
                    </h2>
                    <div id="collapse-<?php echo $name ?>" class="accordion-collapse collapse"
                         aria-labelledby="heading-<?php echo $name ?>" data-bs-parent="#gateways">
                        <div class="accordion-body">
							<?php echo $gateway['html']; ?>
                        </div>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
	<?php endif; ?>
    <p>
        <small><?php echo JText::_('EMR_NEW_GATEWAY'); ?></small>
    </p>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'plan', 'cross-plans', Text::_('FS_CROSSPLANS')); ?>

    <div class="row">
        <div class="col-6">
			<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_period', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_deactivated', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_hide', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
        </div>
        <div class="col-6">
			<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_require', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_grant', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
			<?php echo MFormHelper::renderFieldset($this->params_form, 'crossplans_upgrade', $this->item->params, 'crossplans',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
        </div>
    </div>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'plan', 'alerts', Text::_('FS_ALERTS')); ?>

    <div class="row">
		<?php echo MFormHelper::renderFieldset($this->params_form, 'alerts', $this->item->params, 'alerts',  MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE); ?>
		<?php echo MFormHelper::renderFieldset($this->params_form, 'messages', $this->item->params, 'alerts', MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_CLASSIC); ?>
    </div>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>



	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

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
            var parent = el.closest('.accordion-item');

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
            $('.accordion-button', parent).addClass('gateway-enabled');
        }

        function unset_bg(parent) {
            $('.accordion-button', parent).removeClass('gateway-enabled');
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
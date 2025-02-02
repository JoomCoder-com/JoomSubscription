<?php
/**
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');
?>

<?php if(empty($this->plan->id)): ?>
	<div class="alert"><?php echo JText::_('ERULESNO_PLANS'); ?></div>
	<?php return; ?>
<?php endif; ?>

<style type="text/css">
	#rules-list div.alert {
		margin-bottom: 5px;
	}

	#rules-list div.alert div small {
		margin-top: 10px;
		display: block;
	}

	#rules-list div.alert h4 {
		cursor: pointer;
	}

	#rules-list div.alert h4 small {
		font-size: 12px;
	}
</style>
<div class="alert alert-success">
	<?php echo JText::_('EM_NOSAVERULE'); ?>
</div>
<div class="row">
	<div class="col-5">
		<div id="rules-list">
			<?php foreach($this->rules AS $rule): ?>
				<div class="alert alert-info" data-rule-id="<?php echo $rule->id; ?>">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<?php echo JoomsubscriptionRulesHelper::description($rule); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="col-7">
		<legend><?php echo JText::_('E_ADD_RULE') ?></legend>
		<p>
			<small><?php echo JText::_('EMR_NEW_RULE'); ?></small>
		</p>
		<p><?php echo JHtml::_('select.genericlist', $this->model->getAdapters(), 'rule_components'); ?></p>

		<div id="rule-form" class="in">
		</div>

		<div class="form-actions" id="form-actions" style="display: none;">
			<button class="btn btn-primary" type="button" id="btn-add" data-dismiss="modal"><?php echo JText::_('E_SAVE_RULE') ?></button>
			<button class="btn btn-link" type="button" id="btn-close" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('E_CLOSE') ?></button>
		</div>

	</div>
</div>

<script type="text/javascript">
	(function($) {
		var form = $('#rule-form');
		var actions = $('#form-actions');
		var select = $('#rule_components');
		var list = $('#rules-list');

		updateEdit();

		$('#btn-close').click(function() {
			actions.hide();
			form.slideUp('fast', function() {
				$(this).html('');
			});
		});

		$(".alert").on('close', function() {
			delete_rule($(this).data('rule-id'));
		});

		$('#btn-add').click(function() {
			var formdata = jQuery('*[name^="rules\\[rule\\]"]').filter(':input');
			var data = {};
			$.each(formdata, function(k, v) {
				var el = $(v);
				if((v.type == 'radio' || v.type == 'checkbox') && v.checked == false) {
					return true;
				}
				data[el.attr('name').replace('rules[rule][', 'rules[')] = el.val();
			});

			data.component = select.val();
			data.plan_id = '<?php echo $this->plan->id; ?>';

			$.ajax({
				url:      '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emajax.setRuleForm&tmpl=component', FALSE); ?>',
				dataType: 'json',
				type:     'POST',
				data:     data
			})
				.done(function(json) {
					if(json.error) {
						alert(json.error);
						return;
					}

					actions.hide();
					select.val('').trigger("liszt:updated");
					form.slideUp('fast', function() {
						$(this).html('');
					});

					$('*[data-rule-id="' + json.id + '"]').remove();

					list.append($(document.createElement('div'))
						.addClass('alert alert-info').attr('data-rule-id', json.id)
						.html('<button type="button" class="close" data-dismiss="alert">&times;</button>' + json.html)
						.on('close', function() {
							delete_rule(json.id);
						}));
					updateEdit();
				});

		});

		select.change(function() {
			showForm($(this).val());
		});

		function showForm(id) {
			form.html('<div class="progress progress-striped active"><div class="bar" style="width: 100%;"><?php echo JText::_('EMLOAD'); ?></div></div>')
			$.ajax({
				url:      '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emajax.getRuleForm&tmpl=component', FALSE); ?>',
				dataType: 'html',
				type:     'POST',
				data:     {
					component: id
				}
			}).done(function(html) {
					actions.show();
					form.html(html).hide().slideDown('fast', function() {
						Joomsubscription.redrawBS();
					});
				});
		}

		function delete_rule(id) {
			$.ajax({
				url:      '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emajax.deleteRule&tmpl=component', FALSE); ?>',
				dataType: 'json',
				type:     'POST',
				data:     {id: id}
			});
		}

		function updateEdit() {
			$('small[data-rule-edit]').unbind('click').click(function() {
				select.val($(this).data('controller'));
				showForm($(this).data('rule-edit'));
			});
		}

	}(jQuery))
</script>
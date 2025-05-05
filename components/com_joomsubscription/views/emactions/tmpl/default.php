<?php
/**
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php if(empty($this->plan->id)): ?>
	<div class="alert" xmlns="http://www.w3.org/1999/html"><?php echo JText::_('EACTIONSNO_PLANS'); ?></div>
	<?php return; ?>
<?php endif; ?>

<style type="text/css">
	#actions-list div.alert {
		margin-bottom: 5px;
	}

	#actions-list div.alert div small {
		margin-top: 10px;
		display: block;
	}

	#actions-list div.alert h4 {
		cursor: pointer;
	}

	#actions-list div.alert h4 small {
		font-size: 12px;
	}
</style>
<div class="alert alert-success">
	<?php echo JText::_('EM_NOSAVEACT'); ?>
</div>
<div class="row">
    <div class="col-7">
        <legend><?php echo JText::_('E_ADD_ACTION') ?></legend>
        <p>
            <small><?php echo JText::_('EMR_NEW_ACTION'); ?></small>
        </p>

        <p><?php echo JHtml::_('select.genericlist', $this->model->getActionList(), 'action_type','class="form-select"'); ?></p>

        <div id="action-form">
        </div>

        <div class="form-actions" style="display: none;" id="form-actions-2">
            <button class="btn btn-primary" type="button" id="btn-add-action" data-dismiss="modal"><?php echo JText::_('E_SAVE_RULE') ?></button>
            <button class="btn btn-link" type="button" id="btn-close-action" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('E_CLOSE') ?></button>
        </div>

    </div>
	<div class="col-5">
		<div id="actions-list">
			<?php foreach($this->actions AS $action): ?>
				<div class="alert alert-light" data-action-id="<?php echo $action->id; ?>">
					<button  type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					<?php echo JoomsubscriptionActionsHelper::description($action); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	(function($) {
		var form = $('#action-form');
		var actions = $('#form-actions-2');
		var select = $('#action_type');
		var list = $('#actions-list');

		updateEdit();

		$('#btn-close-action').click(function() {
			actions.hide();
			form.slideUp('fast', function() {
				$(this).html('');
			});
		});

		$(".alert").on('close, closed.bs.alert', function() {
			delete_rule($(this).data('action-id'));
		});

		$('#btn-add-action').click(function() {
			var formdata = jQuery('*[name^="actions\\[action\\]"]').filter(':input');
			var data = {};
			$.each(formdata, function(k, v) {
				var el = $(v);
				if((v.type == 'radio' || v.type == 'checkbox') && v.checked == false) {
					return true;
				}
				data[el.attr('name').replace('actions[action][', 'actions[')] = el.val();
			});

			data.type = select.val();
			data.plan_id = '<?php echo $this->plan->id; ?>';

			$.ajax({
				url:      '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emajax.sendActionForm&tmpl=component', FALSE); ?>',
				dataType: 'json',
				type:     'POST',
				data:     data
			}).done(function(json) {
					if(json.error) {
						alert(json.error);
						return;
					}

					actions.hide();
					select.val('');
					form.slideUp('fast', function() {
						$(this).html('');
					});

					$('*[data-action-id="' + json.id + '"]').remove();

					list.append($(document.createElement('div'))
						.addClass('alert alert-info').attr('data-action-id', json.id)
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
			if(!id) {
				return;
			}

			form.html('<div class="progress progress-striped active"><div class="bar" style="width: 100%;"><?php echo JText::_('EMLOAD'); ?></div></div>')
			$.ajax({
				url:      '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emajax.getActionForm&tmpl=component', FALSE); ?>',
				dataType: 'html',
				type:     'POST',
				data:     {
					type: id
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
				url:      '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emajax.deleteAction&tmpl=component', FALSE); ?>',
				dataType: 'json',
				type:     'POST',
				data:     {id: id}
			});
		}

		function updateEdit() {
			$('small[data-action-edit]').unbind('click').click(function() {
				select.val('');
				showForm($(this).data('action-edit'));
			});
		}

	}(jQuery))
</script>
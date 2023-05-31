<?php
include_once JPATH_ROOT . '/libraries/joomla/form/fields/calendar.php';
$form = JForm::getInstance('myform', '<form><field id="cal" name="cal" type="calendar" label="EDISCOUNT" required="true" /></form>', array('control' => 'fields[' . $this->id . ']', 'load_data' => TRUE), FALSE, FALSE);

$date = $this->default['cal'] ? $this->default['cal'] : date('Y-m-d');
$days = range($this->params->get('params.min_days'), $this->params->get('params.max_days'));
sort($days);
?>
<style>
	.form-inline #field1_chzn {
		width: 60px !important;
	}
</style>
<div class="alert alert-warning">
	<?php echo $this->params->get('params.note'); ?>
</div>
<table>
	<tr>
		<td>
			<?php echo $form->getInput('cal', NULL, $date); ?>
		</td>
		<td>
			<div class="form-inline">

				<select name="fields[<?php echo $this->id ?>][day]" id="field<?php echo $this->id ?>">
					<?php foreach($days as $day): ?>
						<option
							value="<?php echo $day ?>" <?php echo $this->default['day'] == $day ? 'selected' : NULL; ?> ><?php echo $day ?></option>
					<?php endforeach; ?>
				</select>
				<button class="btn btn-primary" type="button"
						id="dateapplybutton"><?php echo JText::_('EMR_APPLY'); ?></button>
			</div>
		</td>
	</tr>
</table>

<script>
	(function ($) {

		$('#field<?php echo $this->id ?>').change(function () {
			$('#formsubscr').submit();
		});
		$('#dateapplybutton').click(function () {
			$('#formsubscr').submit();
		});
	}(jQuery));
</script>

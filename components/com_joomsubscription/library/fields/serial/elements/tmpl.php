<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 11/24/16
 * Time: 20:18
 */

?>

<?php if(!$list): ?>
	<p class="alert alert-info">
		<?php echo JText::_('EMR_NOUNUSEDSERIALS') ?>
	</p>
<?php else: ?>
	<div id="alercntr">
		<p class="alert alert-warning">
			<?php echo JText::_('EMR_UNUSEDSERIALS') ?>
		</p>

		<div class="well well-small">
			<ul class="unstyled">
				<?php foreach($list as $l): ?>
					<li><?php echo $l->serial; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<br>
		<button type="button" id="clnbtnem" class="btn btn-primary"><?php echo JText::_('EMR_CLEAN') ?></button>
	</div>

	<script>
		(function ($) {
			$('#clnbtnem').click(function () {
				if (confirm('<?php echo JText::_('EMR_CLEAN_ALERT') ?>')) {
					$.ajax({
						url: '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emajax.cleanSerials&tmpl=component', FALSE); ?>',
						dataType: 'json',
						type: 'POST',
						data: {
							field_id: <?php echo JFactory::getApplication()->input->get('field_id'); ?>
						}
					}).done(function (json) {
						$('#alercntr').html('<p class="alert alert-info"><?php echo JText::_('EMR_NOUNUSEDSERIALS') ?></p>');
					});
				}
			});
		}(jQuery))
	</script>
<?php endif; ?>

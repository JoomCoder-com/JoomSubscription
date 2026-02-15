<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();
?>
<div class="page-header">
	<h1><?php echo \Joomla\CMS\Language\Text::_('EMR_MUA_TITLE'); ?></h1>
</div>

<p>
	<a class="btn btn-link" href="<?php echo JoomsubscriptionApi::getLink('emhistory'); ?>">
		<img src="<?php echo \Joomla\CMS\Uri\Uri::root(true); ?>/components/com_joomsubscription/images/back.png">
		<?php echo \Joomla\CMS\Language\Text::_('EMR_MUA_BAKTOHISTORY'); ?>
	</a>
</p>

<?php if($this->instruction): ?>
	<?php echo $this->instruction; ?>
<?php endif; ?>

<table class="table table-striped">
	<thead>
		<tr>
			<th width="1%"><?php echo \Joomla\CMS\Language\Text::_('ID')?></th>
			<th><?php echo \Joomla\CMS\Language\Text::_('EUSER')?></th>
			<th width="1%"><?php echo \Joomla\CMS\Language\Text::_('EACTIVE')?></th>
			<th nowrap width="1%"><?php echo \Joomla\CMS\Language\Text::_('ESTARTON')?></th>
			<th nowrap width="1%"><?php echo \Joomla\CMS\Language\Text::_('EENDON')?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->mua as $row): ?>
			<?php
			$img = 'active.png';
			if($row->published == 0) $img = 'block.png';
			if($row->expired)
			{
				$class = 'red';
				$img = 'block.png';
			}
			else
			{
				$class = 'green';
			}
			?>
			<tr>
				<td><?php echo $row->id; ?></td>
				<td><?php echo $row->username; ?></td>
				<td align="center">
					<img align="absmiddle" border="0"
						src="<?php echo \Joomla\CMS\Uri\Uri::root(true)?>/components/com_joomsubscription/images/<?php echo $img;?>"></td>
				<td nowrap><?php echo Joomla\CMS\HTML\HTMLHelper::_('date', $row->ctime, $this->params->get('date_format')); ?></td>
				<td nowrap><span class="<?php echo $class; ?>"><?php echo Joomla\CMS\HTML\HTMLHelper::_('date', $row->extime, \Joomla\CMS\Language\Text::_('DATE_FORMAT_LC3'));?></span></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
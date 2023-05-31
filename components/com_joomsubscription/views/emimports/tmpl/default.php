<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

?>
<style type="text/css">
	.cpanel-list a:hover {
		background-color: #f5f5f5;
		text-decoration: none;
	}
	.cpanel-list a{
        display: block;
        float: left;
        width: 100px;
        height: 40px;
        border: 1px solid #e9e9e9;
        margin-right: 10px;
        background-repeat: no-repeat;
        background-position: center 15px;
        text-align: center;
        padding-top: 75px;
        border-radius: 5px;
	}
</style>
<?php echo $this->menu->render(null); ?>
<div class="page-header">
	<h1>
		<img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/import.png">
		<?php echo JText::_('COM_JOOMSUBSCRIPTION_IMPORTS'); ?>
	</h1>
</div>

<?php if (!$this->canImport):?>
	<div class="alert alert-error">
		<?php echo JText::_('EMR_IMPORTNOTALLOW'); ?>
	</div>
<?php endif; ?>

<ul class="unstyled cpanel-list">
<?php foreach ($this->items as $item): ?>
	<li>
		<a href="<?php echo $this->canImport ? JRoute::_('index.php?option=com_joomsubscription&view=emimport&name='.$item->name) : 'javascript:void(0);';?>"
		<?php if($item->icon):?> style="background-image: url('<?php echo $item->icon;?>');"<?php endif;?>>
			<?php echo $item->title;?>
		</a>
	</li>
<?php endforeach;?>
</ul>
<div class="clearfix"></div>
<br />
<div class="alert alert-success"><?php echo JText::_('EMR_NEW_IMPORT'); ?></div>

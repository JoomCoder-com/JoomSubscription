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
.cpbut a:hover {
    background-color:#f5f5f5;
    text-decoration:none;
}
.cpbut a {
    display:block;
    border:1px solid #e9e9e9;
    background-color:#ffffff;
    margin-bottom:10px!important;
    background-repeat:no-repeat;
    background-position:center 15px;
    text-align:center;
    padding-top:80px;
    height:45px;
    font-weight:bold;
}
ul.unstyled {
    list-style:none;
}    
</style>
<?php echo $this->menu->render(null); ?>
<div class="page-header">
<div class="uk-clearfix"></div>
	<h1>
		<img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/import.png" />
		<?php echo JText::_('COM_JOOMSUBSCRIPTION_IMPORTS'); ?>
	</h1>
</div>
<hr />
<?php if (!$this->canImport):?>
	<div class="uk-alert uk-alert-danger">
		<?php echo JText::_('EMR_IMPORTNOTALLOW'); ?>
	</div>
<?php endif; ?>

<div class="uk-grid uk-grid-small">
<?php foreach ($this->items as $item): ?>
	<div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
		<a href="<?php echo $this->canImport ? JRoute::_('index.php?option=com_joomsubscription&view=emimport&name='.$item->name) : 'javascript:void(0);';?>"
		<?php if($item->icon):?> style="background-image: url('<?php echo $item->icon;?>');"<?php endif;?>>
			<?php echo $item->title;?>
		</a>
	</div>
<?php endforeach;?>
</div>
<div class="uk-clearfix"></div>
<div class="uk-alert uk-alert-success"><?php echo JText::_('EMR_NEW_IMPORT'); ?></div>

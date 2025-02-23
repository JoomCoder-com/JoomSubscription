<?php
/**
 * Cobalt by JoomCoder
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

if (!JoomsubscriptionHelper::isModer()) return;
$view = JFactory::getApplication()->input->getCmd('view');

$navMenu = [
	['title' => 'ESUBSCRIPTIONS', 'url' => 'index.php?option=com_joomsubscription&view=emsales'],
	['title' => 'EPLANS', 'url' => 'index.php?option=com_joomsubscription&view=emplans'],
	['title' => 'EGROUPS', 'url' => 'index.php?option=com_joomsubscription&view=emgroups'],
	['title' => 'EFIELDS', 'url' => 'index.php?option=com_joomsubscription&view=emfields'],
	['title' => 'ECOUPONS', 'url' => 'index.php?option=com_joomsubscription&view=emcoupons'],
	['title' => 'EANALYTICS', 'url' => 'index.php?option=com_joomsubscription&view=emanalytics'],
	['title' => 'ESTATES', 'url' => 'index.php?option=com_joomsubscription&view=emstates'],
	['title' => 'ETAXES', 'url' => 'index.php?option=com_joomsubscription&view=emtaxes'],
	['title' => 'EPLUNLIST', 'url' => 'index.php?option=com_joomsubscription&view=emlist'],
	['title' => 'EPLUNHISTORY', 'url' => 'index.php?option=com_joomsubscription&view=emhistory'],
];

HTMLHelper::_('bootstrap.collapse', '.navbar-toggler');

?>
<nav class="navbar navbar-expand-lg bg-body-tertiary mb-3 rounded shadow-sm border">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($navMenu as $menuItem): ?>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="<?php echo Route::_($menuItem['url'])  ?>">
                            <?php echo \Joomla\CMS\Language\Text::_($menuItem['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
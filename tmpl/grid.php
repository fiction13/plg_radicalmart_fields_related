<?php
/*
 * @package   RadicalMart Fields - Related
 * @version   1.1.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

?>

<?php if (!empty($items)): ?>
    <div class="radicalmart-related">
        <?php if ($params->get('show_group_title', 0)) : ?>
            <?php echo Text::_($field->title); ?>
        <?php endif; ?>

        <div class="uk-grid-divider uk-grid-medium radicalmart-related__list" uk-grid
             uk-height-match="target: > div > .uk-card >.uk-card-body,> div > .uk-card >.uk-card-footer > .uk-grid; row:false">
            <?php foreach ($items as $i => $item): ?>
                <div class="uk-width-1-<?php echo $params->get('cols'); ?>@s">
                    <?php echo LayoutHelper::render('plugins.radicalmart_fields.related.display.grid',
                        array('product' => $item, 'mode' => $mode, 'params' => $params)); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

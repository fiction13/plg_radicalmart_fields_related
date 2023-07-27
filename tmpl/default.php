<?php
/*
 * @package   RadicalMart Fields - Related
 * @version   __DEPLOY_VERSION__
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

        <div class="radicalmart-related__list">
            <?php foreach ($items as $i => $item): ?>
                <?php if ($i > 0) echo '<hr class="uk-margin-remove">'; ?>
                <div class="item-<?php echo $item->id; ?>">
                    <?php echo LayoutHelper::render('plugins.radicalmart_fields.related.display.list',
                        array('product' => $item, 'mode' => $mode, 'params' => $params)); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

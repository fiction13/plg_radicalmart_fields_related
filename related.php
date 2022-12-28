<?php
/*
 * @package   plg_radicalmart_fields_related
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class plgRadicalMart_FieldsRelated extends CMSPlugin
{
	/**
	 * Loads the application object.
	 *
	 * @var  CMSApplication
	 *
	 * @since  1.0.0
	 */
	protected $app = null;

	/**
	 * Loads the database object.
	 *
	 * @var  JDatabaseDriver
	 *
	 * @since  1.0.0
	 */
	protected $db = null;

	/**
	 * Affects constructor behavior.
	 *
	 * @var  boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Method to add field type to admin list.
	 *
	 * @param   string  $context  Context selector string.
	 * @param   object  $item     List item object.
	 *
	 * @return string|false Field type constant on success, False on failure.
	 *
	 * @since  1.1.0
	 */
	public function onRadicalMartGetFieldType($context = null, $item = null)
	{
		return 'PLG_RADICALMART_FIELDS_RELATED_FIELD_TYPE';
	}

	/**
	 * Method to add field config.
	 *
	 * @param   string    $context  Context selector string.
	 * @param   Form      $form     Form object.
	 * @param   Registry  $tmpData  Temporary form data.
	 *
	 * @since  1.0.0
	 */
	public function onRadicalMartGetFieldForm($context = null, $form = null, $tmpData = null)
	{
		if ($context !== 'com_radicalmart.field') return;
		if ($tmpData->get('plugin') !== 'related') return;

		Form::addFormPath(__DIR__ . '/config');
		$form->loadFile('admin');

		$form->setFieldAttribute('display_products', 'readonly', 'true', 'params');
		$form->setFieldAttribute('display_filter', 'readonly', 'true', 'params');
		$form->setFieldAttribute('display_variability', 'readonly', 'true', 'params');
	}

	/**
	 * Method to set field values.
	 *
	 * @param   string    $context  Context selector string.
	 * @param   Form      $form     Form object.
	 * @param   Registry  $tmpData  Temporary form data.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onRadicalMartAfterGetFieldForm($context = null, $form = null, $tmpData = null)
	{
		$form->setValue('display_products', 'params', '0');
		$form->setValue('display_filter', 'params', '0');
		$form->setValue('display_variability', 'params', '0');
	}

	/**
	 * Method to add field to product form.
	 *
	 * @param   string    $context  Context selector string.
	 * @param   object    $field    Field data object.
	 * @param   Registry  $tmpData  Temporary form data.
	 *
	 * @return false|SimpleXMLElement SimpleXMLElement on success, False on failure.
	 *
	 * @since  1.0.0
	 */
	public function onRadicalMartGetProductFieldXml($context = null, $field = null, $tmpData = null)
	{
		if ($context !== 'com_radicalmart.product') return false;
		if ($field->plugin !== 'related') return false;

		$wa = $this->app->getDocument()->getWebAssetManager();
		$wa->addInlineScript('
            document.addEventListener("DOMContentLoaded", function(event) {
                let relatedContainer = document.querySelector(\'input[name="jform[fields][' . $field->alias . ']"]\').parentElement.parentElement;
                let relatedLabel = relatedContainer.querySelector(\'.control-label\');
                
                relatedLabel.classList.add(\'fw-bold\', \'mb-2\', \'d-block\', \'w-100\');
                relatedLabel.classList.remove(\'control-label\');
                relatedLabel.querySelector(\'.controls\').classList.remove(\'controls\');
            });
        ');

		// Add Stylesheet
		Factory::getDocument()->addStyleDeclaration('
            input[id*="' . str_replace('-', '_', $field->alias) . '"] {
                width: inherit;
            }
        ');

		// Add fields
		$file = Path::find(__DIR__ . '/config', 'product.xml');

		if (!$file)
		{
			return false;
		}

		$xmlField = simplexml_load_file($file);

		// This is important for display field!
		$xmlField->attributes()->name = $field->alias;
		$xmlField->addAttribute('label', $field->title);

		return $xmlField;
	}

	/**
	 * Method to add field value to products list.
	 *
	 * @param   string        $context  Context selector string.
	 * @param   object        $field    Field data object.
	 * @param   array|string  $value    Field value.
	 *
	 * @return  string  Field html value.
	 *
	 * @since  1.0.0
	 */
	public function onRadicalMartGetProductFieldValue($context = null, $field = null, $value = null)
	{
		if ($context !== 'com_radicalmart.product') return false;
		if ($field->plugin !== 'related') return false;
		if (!(int) $field->params->get('display_product', 1)) return false;

		return $this->getFieldValue($field, $value);
	}

	/**
	 * Method to add field value to products list.
	 *
	 * @param   object        $field  Field data object.
	 * @param   string|array  $value  Field value.
	 *
	 * @return  string|false  Field string values on success, False on failure.
	 *
	 * @since  1.0.0
	 */
	protected function getFieldValue($field = null, $value = null)
	{
		if (empty($field)) return false;
		if (empty($value)) return false;

		if (!is_array($value)) $value = array($value);

		// Model
		if (!$model = Factory::getApplication()->bootComponent('com_radicalmart')->getMVCFactory()->createModel('Products', 'Site', ['ignore_request' => true]))
		{
			throw new \Exception(Text::_('PLG_RADICALMART_FIELDS_RELATED_ERROR_MODEL_NOT_FOUND'), 500);
		}

		// Get values
		$value = ArrayHelper::getColumn($value, 'id');
		$value = array_values(array_unique($value));

		$model->setState('filter.item_id', $value);
		$model->setState('filter.published', 1);

		// Set language filter state
		$model->setState('filter.language', Multilanguage::isEnabled());

		// Get items
		$items  = $model->getItems();
		$params = $field->params;

		// Get mode
		$mode = ComponentHelper::getParams('com_radicalmart')->get('mode', 'shop');

		// Get html
		$layout = $params->get('layout', 'default');
		$path   = PluginHelper::getLayoutPath('radicalmart_fields', 'related', $layout);

		// Render the layout
		ob_start();
		include $path;
		$html = ob_get_clean();

		return $html;
	}
}

<?php
/*
 * @package   plg_radicalmart_fields_related
 * @version   1.0.0
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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
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

		$form->setFieldAttribute('display_filter', 'type', 'hidden', 'params');
		$form->setValue('display_filter', 'params', 0);

		$form->setFieldAttribute('display_products', 'type', 'hidden', 'params');
		$form->setValue('display_filter', 'params', 0);
	}

	/**
	 * Prepare options data.
	 *
	 * @param   string  $context  Context selector string.
	 * @param   object  $objData  Input data.
	 * @param   Form    $form     Joomla Form object.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public function onContentNormaliseRequestData($context, $objData, $form)
	{
		if ($context === 'com_radicalmart.field')
		{
			// noop
		}
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

		// Add Javascript
		Factory::getDocument()->addScriptDeclaration('
            document.addEventListener("DOMContentLoaded", function(event) {
                let relatedContainer = document.querySelector(\'input[name="jform[fields][' . $field->alias . ']"]\').parentElement.parentElement;
                let relatedLabel = relatedContainer.querySelector(\'.control-label\');
                
                relatedLabel.style.marginLeft = "28px";
                relatedLabel.classList.add(\'lead\');
                relatedLabel.classList.remove(\'control-label\');
                relatedContainer.querySelector(\'.controls\').classList.remove(\'controls\');
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
	 * Method to add field to filter form.
	 *
	 * @param   string  $context  Context selector string.
	 * @param   object  $field    Field data object.
	 * @param   array   $data     Data.
	 *
	 * @return false|SimpleXMLElement SimpleXMLElement on success, False on failure.
	 *
	 * @since  1.0.0
	 */
	public function onRadicalMartGetFilterFieldXml($context = null, $field = null, $data = null)
	{
		if ($field->plugin === 'related') return false;
	}

	/**
	 * Method to modify query.
	 *
	 * @param   string          $context  Context selector string.
	 * @param   JDatabaseQuery  $query    JDatabaseQuery  A JDatabaseQuery object to retrieve the data set
	 * @param   object          $field    Field data object.
	 * @param   array|string    $value    Value.
	 *
	 * @since  1.0.0
	 */
	public function onRadicalMartGetProductsListQuery($context = null, $query = null, $field = null, $value = null)
	{
		if ($field->plugin === 'related') return;
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
	public function onRadicalMartGetProductsFieldValue($context = null, $field = null, $value = null)
	{
		if ($context !== 'com_radicalmart.category' && $context !== 'com_radicalmart.products') return false;
		if ($field->plugin !== 'related') return false;

		if (!(int) $field->params->get('display_products', 1)) return false;

		return $this->getFieldValue($field, $value);
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
	 * @param   object        $field   Field data object.
	 * @param   string|array  $value   Field value.
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

		\JLoader::register('RadicalMartHelperIntegration', JPATH_ADMINISTRATOR . '/components/com_radicalmart/helpers/integration.php');
		RadicalMartHelperIntegration::initializeSite();

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_radicalmart/models');

		// Get values
		$value = ArrayHelper::getColumn($value,'id');
		$value = array_values(array_unique($value));

		// Model
		$model = BaseDatabaseModel::getInstance('Products', 'RadicalMartModel', array('ignore_request' => true));

		$model->setState('params', Factory::getApplication()->getParams());
		$model->setState('filter.item_id', $value);
		$model->setState('filter.published', 1);

		// Get items
		$items  = $model->getItems();
		$params = $field->params;

		// Get mode
		$mode   = ComponentHelper::getParams('com_radicalmart')->get('mode', 'shop');

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

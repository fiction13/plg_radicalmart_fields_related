<?php
/*
 * @package   RadicalMart Fields - Related
 * @version   1.1.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2023 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalMartFields\Related\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Language;
use Joomla\Component\RadicalMart\Administrator\Helper\PluginsHelper;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use SimpleXMLElement;

class Related extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    bool
	 *
	 * @since  1.0.3
	 */
	protected $autoloadLanguage = true;

	/**
	 * Loads the application object.
	 *
	 * @var  \Joomla\CMS\Application\CMSApplication
	 *
	 * @since  1.0.3
	 */
	protected $app = null;

	/**
	 * Loads the database object.
	 *
	 * @var  \Joomla\Database\DatabaseDriver
	 *
	 * @since  1.0.3
	 */
	protected $db = null;

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  &$subject  The object to observe
	 * @param   array                 $config   An optional associative array of configuration settings.
	 *                                          Recognized key values include 'name', 'group', 'params', 'language'
	 *                                          (this list is not meant to be comprehensive).
	 *
	 * @since   1.0.3
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);


	}

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   1.0.3
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onRadicalMartGetFieldType'          => 'onRadicalMartGetFieldType',
			'onRadicalMartGetFieldForm'          => 'onRadicalMartGetFieldForm',
			'onRadicalMartGetProductFieldXml'    => 'onRadicalMartGetProductFieldXml',
			'onRadicalMartGetProductFieldValue'  => 'onRadicalMartGetProductFieldValue',
			'onRadicalMartAfterGetFieldForm'     => 'onRadicalMartAfterGetFieldForm'
		];
	}

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

		Form::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');
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
	 * @since  1.0.2
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

		// Add fields
		$file = Path::find(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms', 'product.xml');

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
		$model->setState('list.limit', count($value));

		// Set language filter state
		$model->setState('filter.language', Multilanguage::isEnabled());

		// Get items
		$items  = $model->getItems();
		$params = $field->params;

		if (empty($items))
		{
			return false;
		}

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
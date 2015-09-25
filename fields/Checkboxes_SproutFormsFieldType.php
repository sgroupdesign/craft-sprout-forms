<?php
namespace Craft;

/**
 *
 */
class Checkboxes_SproutFormsFieldType extends BaseSproutFormsFieldType
{
	/**
	 * Returns the field's input HTML.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @return string
	 */
	public function getInputHtml($field, $value, $settings, $renderingOptions = null)
	{
		return craft()->templates->render('fields/checkboxes/input', array(
			'name'    => $field->handle,
			'options' => $settings->options,
			'field'    => $field,
			'values'  => $value,
			'renderingOptions' => $renderingOptions,
		));
	}
}
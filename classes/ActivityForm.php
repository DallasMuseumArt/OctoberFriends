<?php namespace DMA\Friends\Classes;

use Backend\Classes\FormField;
use Backend\Classes\WidgetBase;
use System\Classes\ApplicationException;
use Str;
use File;
use DMA\Friends\Classes\ActivityManager;

/**
 * Class to handle form configuration for activity types
 *
 * @package DMA\Friends\Classes
 * @author Kristen Arnold, Carlos Arroyo
 */
class ActivityForm extends WidgetBase
{

    /**
     * @var ActivityManager an instance of the ActivityManager class
     */
    protected $manager;

    /**
     * @var An elequant model
     */
    protected $model;

    /**
     * @var an array of fields to be rendered
     */
    public $fields = [];

    /**
     * @var string Path to backend widgets
     */
    protected $partialsDir = '@/modules/backend/widgets/form/partials/';

    /**
     * @var an array of data values for a form
     */
    protected $data = [];

    /**
     * @var Field name
     */
    protected $fieldName;

    /**
     * @var override default functionality provided in WidgetBase
     */
    public $previewMode = false;

    public function __construct(ActivityManager $manager, $model, $fieldName)
    {
        $this->model        = $model;
        $this->manager      = $manager;
        $config             = $this->manager->getConfig($model->activity_type);
        
        if ($config) {
            $this->fields       = $config->fields;
            $this->data         = $this->manager->getFormDefaultValues($model);
            $this->fieldName    = $fieldName;
        }
    }

    /**
     * Render a group of form elements
     */
    public function render()
    {
        $fields = [];

        foreach($this->fields as $name => $field) {
            $field = $this->makeFormField($name, $field);

            $fields[] = $this->renderField($field);
        }

        return implode("<br/>", $fields);
    }

    /**
     * Locates a file based on it's definition. If the file starts with
     * an "at symbol", it will be returned in context of the application base path,
     * otherwise it will be returned in context of the view path.
     * @param string $fileName File to load.
     * @param mixed $viewPath Explicitly define a view path.
     * @return string Full path to the view file.
     */
    public function getViewPath($fileName, $viewPath = null)
    {

        $fileName = File::symbolizePath($fileName, $fileName);

        if (File::isLocalPath($fileName) || realpath($fileName) !== false) {
            return $fileName;
        } else {
            $path = $this->partialsDir . $fileName;
            return File::symbolizePath($path, $path);
        }

    }

    /**
     * Renders a single form field
     * @param A field name or field object
     * @param array An array of options
     */
    public function renderField($field, $options = [])
    {
        if (is_string($field)) {
            if (!isset($this->fields[$field])) {
                throw new ApplicationException(Lang::get(
                    'backend::lang.form.missing_definition',
                    compact('field')
                ));
            }

            $field = $this->fields[$field];
        }

        if (!isset($options['useContainer'])) {
            $options['useContainer'] = true;
        }
        $targetPartial = $options['useContainer'] ? '_field-container.htm' : '_field.htm';
        $targetPartial = $this->partialsDir . $targetPartial;

        return $this->makePartial($targetPartial, ['field' => $field]);
    }

    /**
     * Creates a form field object from name and configuration.
     */
    protected function makeFormField($name, $config)
    {
        $label = (isset($config['label'])) ? $config['label'] : null;
        list($fieldName, $fieldContext) = $this->getFieldName($name);

        $field = new FormField($fieldName, $label);
        if ($fieldContext) {
            $field->context = $fieldContext;
        }
        $field->idPrefix = $this->getId();

        /*
         * Simple field type
         */
        if (is_string($config)) {
            $field->displayAs($config);
        /*
         * Defined field type
         */
        } else {

            $fieldType = isset($config['type']) ? $config['type'] : null;
            if (!is_string($fieldType) && !is_null($fieldType)) {
                throw new ApplicationException(Lang::get(
                    'backend::lang.field.invalid_type',
                    ['type'=>gettype($fieldType)]
                ));
            }

            $field->displayAs($fieldType, $config);

        }

        /*
         * Set field value
         */
        $field->value = $this->getFieldValue($field);

        /*
         * Get field options from model
         */
        $optionModelTypes = ['dropdown', 'radio', 'checkboxlist', 'balloon-selector'];
        if (in_array($field->type, $optionModelTypes)) {

            /*
             * Defer the execution of option data collection
             */
            $field->options(function () use ($field, $config) {
                $fieldOptions = (isset($config['options'])) ? $config['options'] : null;
                $fieldOptions = $this->getOptionsFromModel($field, $fieldOptions);
                return $fieldOptions;
            });
        }

        return $field;
    }

    /**
     * Looks up the column
     */
    public function getFieldValue($field)
    {
        if (is_string($field)) {
            if (!isset($this->fields[$field])) {
                throw new ApplicationException(Lang::get(
                    'backend::lang.form.missing_definition',
                    compact('field')
                ));
            }

            $field = $this->fields[$field];
        }

        $fieldName = $field->fieldName;

        $defaultValue = strlen($field->defaults) ? $field->defaults : null;

        /*
         * Array field name, eg: field[key][key2][key3]
         */
        $keyParts   = Str::evalHtmlArray($fieldName);
        $lastField  = end($keyParts);
        $result     = $this->data;

        // Sub fields are always the last field in the array
        return $result[$lastField];
    }

    /**
     * Parses a field's name
     * @param string $field Field name
     * @return array [columnName, context]
     */
    public function getFieldName($field)
    {
        $field = $this->fieldName . '[' . $field . ']';

        if (strpos($field, '@') === false) {
            return [$field, null];
        }

        return explode('@', $field);
    }

    /**
     * Returns a HTML encoded value containing the other fields this
     * field depends on
     * @param  use Backend\Classes\FormField $field
     * @return string
     */
    public function getFieldDepends($field)
    {
        if (!$field->dependsOn) {
            return;
        }

        $dependsOn = is_array($field->dependsOn) ? $field->dependsOn : [$field->dependsOn];
        $dependsOn = htmlspecialchars(json_encode($dependsOn), ENT_QUOTES, 'UTF-8');
        return $dependsOn;
    }

        /**
     * Renders the HTML element for a field
     */
    public function renderFieldElement($field)
    {
        return $this->makePartial('field_'.$field->type, ['field' => $field]);
    }
}
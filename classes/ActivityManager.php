<?php namespace DMA\Friends\Classes;

use Str;
use Lang;
use Config;
use File;
use Illuminate\Container\Container;
use System\Classes\PluginManager;
use SystemException;

/**
 * Manage activity behavior
 *
 * @package DMA\Friends\Classes
 * @author Kristen Arnold, Carlos Arroyo
 */
class ActivityManager
{
    use \October\Rain\Support\Traits\Singleton;
    use \System\Traits\ConfigMaker;

    /** 
     * @var array Cache of registration callbacks.
     */
    protected $callbacks = []; 

    /** 
     * @var array An array where keys are codes and values are class names.
     */
    protected $codeMap;

    /** 
     * @var array An array where keys are class names and values are codes.
     */
    protected $classMap;

    /** 
     * @var array An array containing references to a corresponding plugin for each activity class.
     */
    protected $pluginMap;

    /** 
     * @var array A cached array of activity details.
     */
    protected $detailsCache;

    /**
     * @var a specific activity type to load
     */
    protected $activityType;

    public function __construct()
    {
        $this->configPath = base_path() . Config::get('cms.pluginsPath');
    }

    /**
     * Load the available activities that are defined by plugins
     */
    protected function loadActivities()
    {   
        /*  
         * Load module activities
         */
        foreach ($this->callbacks as $callback) {
            $callback($this);
        }   

        /*  
         * Load plugin activities
         */
        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();

        foreach ($plugins as $plugin) {
            if (method_exists($plugin, 'registerFriendsActivities')) {
                $activities = $plugin->registerFriendsActivities();
                if (!is_array($activities)) {
                    continue;
                }   

                foreach ($activities as $className => $code) {
                    $this->registerActivity($className, $code, $plugin);
                }   
            } 
        }   
    }  

    /** 
     * Registers a single activity.
     *
     * @param string $className
     * the name of the class to register
     * 
     * @param string $code
     * An id for the Activity to register
     *
     * @param string $plugin
     * the name of the plugin
     */
    public function registerActivity($className, $code = null, $plugin = null)
    {   
        if (!$this->classMap) {
            $this->classMap = []; 
        }   

        if (!$this->codeMap) {
            $this->codeMap = []; 
        }   

        if (!$code) {
            $code = Str::getClassId($className);
        }   

        $className = Str::normalizeClassName($className);
        $this->codeMap[$code] = $className;
        $this->classMap[$className] = $code;
        if ($plugin !== null) {
            $this->pluginMap[$className] = $plugin;
        }   
    } 

    /** 
     * Returns a list of registered activities.
     * @return array Array keys are codes, values are class names.
     */
    public function listActivities()
    {   
        if ($this->codeMap === null) {
            $this->loadActivities();
        }   

        return $this->codeMap;
    } 

    /** 
     * Returns a class name from a component code
     * Normalizes a class name or converts an code to it's class name.
     * 
     * @param string $name
     * The alias of the class being resolved
     *
     * @return string The class name resolved, or null.
     */
    public function resolve($name)
    {   
        $codes = $this->listActivities();

        if (isset($codes[$name])) {
            return $codes[$name];
        }   

        $name = Str::normalizeClassName($name);
        if (isset($this->classMap[$name])) {
            return $name;
        }   

        return null;
    } 

    /**
     * Return field configuration for an Activity Type
     *
     * @param Activity Alias
     *
     * @return an array representing the field configuration
     */
    public function getConfig($alias)
    {
        $this->loadActivity($alias);

        if (!$this->activityType) return false;

        $formConfig = $this->activityType->getConfig();
        $formConfig = $this->getConfigPath($formConfig);
        if (File::isFile($formConfig)) {
            return $this->makeConfig($formConfig);
        } else {
            return false;
        }
    }

    /**
     * An alias to access the getFormDefaultValues method of the called activity type
     *
     * @param object $model
     * an activity model
     *
     * @return array
     * An array of default form values
     */
    public function getFormDefaultValues($model)
    {
        if (!$this->activityType)
                throw new SystemException(Lang::get('dma.friends::lang.exceptions.activityTypeNotInitiated'));

        return $this->activityType->getFormDefaultValues($model);
    }

    /**
     * An alias to access the saveData() method of the called activity type
     *
     * @param object $model
     * An activity model
     *
     * @param array $values
     * An array of values to be saved
     */
    public function saveData($model, $values)
    {
        if (!$this->activityType)
            $this->loadActivity($values['activity_type']);

        $this->activityType->saveData($model, $values);
    }

    /**
     * Instantiate an instance of the choosen activity type by class alias
     *
     * @param string $alias
     * An activity type class alias
     */
    protected function loadActivity($alias)
    {
        if (!$alias) return;

        if (!$this->activityType) {
            $className = $this->resolve($alias);

            if (!$className)
                throw new SystemException(Lang::get('dma.friends::lang.exceptions.missingActivityClass', ['class' => $alias]));

            $this->activityType = new $className;
        }
    }
}

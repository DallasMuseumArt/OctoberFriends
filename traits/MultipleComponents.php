<?php namespace DMA\Friends\Traits;

/**
 * Trait to allow have multiple components in a single page without 
 * sharing variables.
 * 
 * @author Carlos Arroyo, Kristen Arnold
 */
trait MultipleComponents
{
    /**
     * Flag to control if properties alias has been added
     * @var boolean
     */
    private $aliasProperties = false;
    
  
    /**
     * Sets multiple properties.
     * @see \System\Traits\PropertyContainer
     */
    public function setProperties($properties)
    {
        return parent::setProperties($properties);
    }
    
    /**
     * Sets a property value
     * @see \System\Traits\PropertyContainer
     */
    public function setProperty($name, $value)
    {
        $name = $this->alias . '_' . $name;
        return parent::setProperty($name, $value);
    }
    
    /**
     * Returns all properties.
     * @see \System\Traits\PropertyContainer
     */
    public function getProperties()
    {
        $prop = parent::getProperties();
        return $prop;
    }
    
    /**
     * Returns a defined property value or default if one is not set.
     * @param string $name The property name to look for.
     * @param string $default A default value to return if no name is found.
     * @return string The property value or the default specified.
     * @see \System\Traits\PropertyContainer
     */
    public function property($name, $default = null)
    {
        $name = $this->alias . '_' . $name;
        // Call add alias to properties just in case the alias has not been added.
        // This situation happens when adding components with CMS interface.
        $this->properties = $this->addAliasProperties($this->properties);
        return parent::property($name, $default);
    }
    
    /**
     * Executed when this component is first initialized, before AJAX requests.
     * @see \Cms\Classes\ComponentBase
     */
    public function init()
    {
        // Apply alias to each property
        $this->properties = $this->addAliasProperties($this->properties);
        parent::init();
    }
    
    protected function addAliasProperties($properties){
        if (!$this->aliasProperties){
            $newProperties = [];
            foreach($properties as $key => $value){
                $key = $this->alias . '_' . $key;
                $newProperties[$key] = $value;
            }
            // Stop adding multiple times alias to each property
            $this->aliasProperties = true;
            return $newProperties;
        }else{
            return $properties;
        }
    }
    
    

} 
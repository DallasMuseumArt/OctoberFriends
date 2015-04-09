<?php namespace DMA\Friends\Classes\API;

use Illuminate\Database\Eloquent\Builder;

class ModelRepository {

    protected $modelClassName;

    public function __construct($modelClassName)
    {
        $this->modelClassName = $modelClassName;
    }

    private function call($method, $parameters=array())
    {
        return call_user_func_array("{$this->modelClassName}::{$method}", $parameters);
    }

    public function create(array $attributes)
    {
        return $this->call("create", array($attributes));
    }

    public function all($columns = array('*'))
    {
        return  $this->call("all", array($columns));
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return  $this->call("where", array($column, $operator, $value, $boolean));
    }
    
    public function find($id, $columns = array('*'))
    {
        return  $this->call("find", array($id, $columns));
    }

    public function findOrFail($id, $columns = array('*'))
    {
        return  $this->call("findOrFail", array($id, $columns));
    }

    public function destroy($ids)
    {
        return  $this->call("destroy", array($ids));
    }

    public function paginate($perPage = null, $columns = array('*'))
    {
         return $this->call("paginate", array($perPage, $columns));
    }
    
    public function query()
    {
        return $this->call("query");
    }
    
    
    public function applyFiltersToQuery($filters, Builder $query=null)
    {
        $query = (is_null($query)) ? $this->query() : $query;
        foreach($filters as $filterSpec) {
            $field      = $filterSpec->getField();
            $value      = $filterSpec->getValue();
            $operator   = $filterSpec->getOperator();
            
            // TODO : Review this logic I think there 
            // is a better way of doing this
            // Test if field is a scope
            $fieldCamelCase = ucwords($this->underscoreToCamelCase($field));
            $scopeName = 'scope'. $fieldCamelCase;
            if (method_exists($this->modelClassName, $scopeName)) {
                $query = $query->{$fieldCamelCase}($value);
            } else {
                // Apply filters and operators 
                switch($filterSpec->getOperatorAlias()) {
                    case 'is_null':
                        if ($value) {
                            $query = $query->whereNull($field);
                        } else {
                            $query = $query->whereNotNull($field);
                        }
                        break;
                    case 'in':
                        $value = is_string($value) ? explode(',', $value) : $value;
                        $value = is_array($value) ? $value : [];
                        $value = array_map('trim', $value);
                        $query = $query->whereIn($field, $value);
                        break;
                        
                    default:
                        $query = $query->where($field, $operator, $value);
                        break;
                }
                
            }
            
        }
        return $query;
    }
    
    public function applySortByToQuery(array $sortBy, Builder $query=null)
    {

        $query = (is_null($query)) ? $this->query() : $query;
        
        // Regex operation to detect what operation use to sort
        $re = "/^(-|\\+).*/";
        
        foreach($sortBy as $field) {
            if ($field) {
                $dir = '+';
                if(preg_match($re, $field, $matches)){
                    $dir = $matches[1];
                    $field = trim(str_replace($dir, '', $field));
                }
                $oper = [
                   '+' => 'ASC',
                   '-' => 'DESC'          
                ][$dir];
                
                $query = $query->orderBy($field, $oper);
            }
        }
        return $query;
    }
    
    
    
    protected function underscoreToCamelCase($string, $capitalizeFirstCharacter = false)
    {
    
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
    
        return $str;
    }
    

}

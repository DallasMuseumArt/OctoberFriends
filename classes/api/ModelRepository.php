<?php namespace DMA\Friends\Classes\API;

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

}

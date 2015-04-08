<?php namespace DMA\Friends\Classes\API;


class FilterSpec
{

    private $field;

    private $value;

    private $operatorAlias;
    
    private $operator;
    
    protected $operators = ['exact' => '=',  'iexact' => 'like', 'ne' => '!=',
                             'gt' => '>', 'gte' => '>=', 'lt' => '<', 'lte' => '<=',
                             'is_null' => 'IS NOT NULL', 'in' => 'IN'
                            ];

    public function __construct($field, $value, $operatorAlias = 'exact')
    {
        $field = trim($field);
        $value = is_string($value) ? trim($value) : $value;
        $operatorAlias = strtolower( trim($operatorAlias) );

        $this->validate($field, $value, $operatorAlias);

        $this->field = $field;
        $this->value = $value;
        $this->operatorAlias = $operatorAlias;
        $this->operator = array_get($this->operators, $operatorAlias);
    }

    protected function validate($field, $value, $operator)
    {
        $validOperators = array_keys($this->operators);
        
        if( ( empty( $field)  || empty( $value )  && $value != 0) ) {
            throw new \Exception('Filters must have a non-empty field and value');
        }
        if( ! in_array( $operator,  $validOperators ) ) {
            throw new \Exception('Operator must be among the following: [ ' . implode(', ', $validOperators ). ' ]');
        }
    }
    
    public function getField()
    {
        return $this->field;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function getOperatorAlias()
    {
        return $this->operatorAlias;
    }
    
    public function getOperator()
    {
        return $this->operator;
    }
}
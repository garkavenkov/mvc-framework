<?php

namespace MVC\Framework\Base;

use Registry\Registry;

class Model
{
    private static  $model;
    private static  $table;   
    
    private static  $inject_stack = [];
    private static  $result_stack = [];
    private static  $relation_stack = [];
    private static  $relation_alias = [];

    private static $_plural = array(
        "/^(ox)$/" => "\\1\\2en",
        "/([m|l])ouse$/" => "\\1ice",
        "/(matr|vert|ind)ix|ex$/" => "\\1ices",
        "/(x|ch|ss|sh)$/" => "\\1es",
        "/([^aeiouy]|qu)y$/" => "\\1ies",
        "/(hive)$/" => "\\1s",
        "/(?:([^f])fe|([lr])f)$/" => "\\1\\2ves",
        "/sis$/" => "ses",
        "/([ti])um$/" => "\\1a",
        "/(p)erson$/" => "\\1eople",
        "/(m)an$/" => "\\1en",
        "/(c)hild$/" => "\\1hildren",
        "/(buffal|tomat)o$/" => "\\1\\2oes",
        "/(bu|campu)s$/" => "\\1\\2ses",
        "/(alias|status|virus)/" => "\\1es",
        "/(octop)us$/" => "\\1i",
        "/(ax|cris|test)is$/" => "\\1es",
        "/s$/" => "s",
        "/$/" => "s"
    );


    static function plural($string)
    {
        $result = $string;
        foreach (self::$_plural as $rule => $replacement) {            
            if (preg_match($rule, $string)) {
                $result = preg_replace($rule, $replacement, $string);
                break;
            }
        }
        return $result;
    }

    /**
     * Define name of the table associated with Model
     * Table's name obtain from Model's proterty 'table', or (if that property does not set) 
     * use Model's plural name
     *
     * @param string $model     Model name
     * @return string           Table name
     */
    private static function getTableName(string $model): string
    {        
        try {
            $reflector = new \ReflectionClass($model);

            if ($reflector->hasProperty('table')) {     
                $property = $reflector->getProperty('table');

                if ($property->hasDefaultValue()) {
                    return $property->getDefaultValue();
                }
                throw new \Exception("Table name does not set", 500);

            } else {                
                return static::plural(strtolower($reflector->getShortName()));                 
            }
        } catch(\Exception $ex) {
            throw new \Exception($ex);
        }
    }
    
    /**
     * Define Model and associated table
     *
     * @return void
     */
    private static function resolveModelInfo()
    {        
        self::$model = get_called_class();     
        self::$table = self::getTableName(self::$model);
    }

    /**
     * Get records from table associated with Model
     *
     * @param integer|null $limit   Limit return records
     * @return void
     */
    public static function get(int $limit = null)
    {
        self::resolveModelInfo();
        
        // Make SQL statement.. Consider creating method for generating SQL
        $sql = "SELECT * FROM `" .self::$table . "`";

        // Return particular ($limit) record. 
        if ($limit !== NULL && is_int($limit)) {
            $sql .= " LIMIT $limit";
        }
        
        // Make method for quering DB... Consider creating Registry class for getting DB instance
        // global $dbh;
        $db = Registry::get('db');
        self::$result_stack[self::$model] = $db->query($sql)->getRows(\PDO::FETCH_OBJ);

               
        if (self::$relation_stack) {
            self::resolveRelations();           
        }        
        return self::$result_stack[self::$model];
    }

    /**
     * Find Model's record by Id
     *
     * @param mixed $id     Record ID 
     * @return void
     */
    public static function findById(mixed $id)
    {
        self::resolveModelInfo();        
        
        $sql  = "SELECT * FROM `" .self::$table ."` WHERE `" . self::$table ."`.`id` = $id" ;
        
        $db = Registry::get('db');
        $result = $db->query($sql)->getRow(\PDO::FETCH_OBJ);
        self::$result_stack[self::$model] = $result;
        
        if (self::$relation_stack) {
            self::resolveRelations();            
        } 
        
        return self::$result_stack[self::$model];        
    }

    /**
     * Define model's relationships.
     * For using deep relationships use ".". For using several relationships for model, separete them by ",".     
     *
     * @param mixed $relations      Model's relationships
     * @return static
     */
    public static function with(mixed $relations)
    {           
        $relations = explode(',', preg_replace('/[ ]*/', '', $relations));                
        foreach($relations as $relation) {
            $deeper_relations = explode('.', $relation);            
            if (count($deeper_relations) > 1) {                
                $parent = get_called_class();
                foreach($deeper_relations as $deep_relation) {           
                    self::$relation_stack[$parent] = array('relation' => $deep_relation);
                    $parent = $deep_relation;
                }
            } else {
                self::$relation_stack[get_called_class()] = array('relation' => $deeper_relations[0]);
            }
        }        
        return new static();
    }   

    private static function queryCondition($model, $key): string
    {
        if ( is_array(self::$result_stack[$model]) && (count(self::$result_stack[$model]) > 1) ) {
            return  "IN (" . 
                    implode(',', 
                        array_map(
                            function($r) use($key) {
                                return $r->$key; 
                            }, 
                            self::$result_stack[$model]
                        )
                    ) . 
                    ")";
        } else {
            return " = " . self::$result_stack[$model]->$key;
        }
    }

    /**
     * Return records from child table (relationship type: one-to-many)
     *
     * @param string $related_model     Model (Child model class)
     * @param string $foreign_key       Foreing key in child table
     * @param string $reference_key     Reference key in parent table
     * @return boolean
     */
    public static function hasMany(string $related_model, $foreign_key = '', $reference_key = 'id')
    {
        $parent_class = get_called_class();
        $relation_name = debug_backtrace()[1]['function'];
        
        self::$inject_stack[$parent_class][$relation_name] = "$related_model:$foreign_key:$reference_key";        
        self::$relation_alias[$relation_name] = $related_model;        
        
        $table_name = static::getTableName($related_model);
            
        $sql = "SELECT * FROM `$table_name` WHERE `$table_name`.`$foreign_key` " . 
                self::queryCondition($parent_class, $reference_key);
        
        $db = Registry::get('db');
        $result = $db->query($sql)->getRows(\PDO::FETCH_OBJ);        
        self::$result_stack[$related_model] = $result;        
    }

    /**
     * Return records from parent table (relationship type: one-to-one / many-to-one)
     *
     * @param string $related_model     Model (Parent model class)
     * @param string $reference_key     Reference key in parent table
     * @param string $foreign_key       Foreing key in child table
     * @return void
     */
    public function belongsTo(string $related_model, string $reference_key = 'id', string $foreign_key = '')
    {
        $parent_class = get_called_class();
        $relation_name = debug_backtrace()[1]['function'];        
        
        self::$inject_stack[$parent_class][$relation_name] = "$related_model:$reference_key:$foreign_key";        
        self::$relation_alias[$relation_name] = $related_model;
             
        $table_name = static::getTableName($related_model);
                
        $sql = "SELECT * FROM `$table_name` WHERE `$table_name`.`$reference_key` " .
                self::queryCondition($parent_class, $foreign_key);;       
        
        $db = Registry::get('db');
        $result = $db->query($sql)->getRows(\PDO::FETCH_OBJ);
        self::$result_stack[$related_model] = $result;        
    }  

    /**
     * Resolve relationships.
     * Call Model's relationship method. Compose relationships' results into single 
     *
     * @return void
     */
    protected static function resolveRelations()
    { 
        foreach(self::$relation_stack as $model => $info) {                        
            if (isset(self::$relation_alias[$model])) {
                $model = self::$relation_alias[$model];
            }
            $reflector = new \ReflectionClass($model);
                
            if (!$reflector->hasMethod($info['relation'])) {
                echo "Relation '{$info['relation']}' does not exist in model " . $model . "<br>";
                die();
            }
            call_user_func_array(array(new $model, $info['relation']), []);            
        }
        
        if(self::$inject_stack){ 
            $models = array_reverse(array_keys(self::$inject_stack));            
            foreach($models as $model) {                
                foreach(self::$inject_stack[$model] as $relation => $injection) {
                    [$related_model, $related_model_key, $model_key] = explode(':', $injection);                    
                    $model_result_set = self::$result_stack[$model];
                    
                    if (is_array($model_result_set)) {                        
                        foreach($model_result_set as $model_result) {                            
                            $related_model_result_set = array_merge(array_filter(
                                self::$result_stack[$related_model], 
                                function($r) use($related_model_key, $model_result, $model_key) {
                                    return $r->$related_model_key == $model_result->$model_key;
                                }
                            ));
                            $model_result->$relation = count($related_model_result_set) == 1 ? $related_model_result_set[0] : $related_model_result_set ;                            
                        }
                    } else { 
                        $related_model_result_set = array_filter(
                            self::$result_stack[$related_model], 
                            function($r) use($related_model_key, $model_result_set, $model_key) {
                                return $r->$related_model_key == $model_result_set->$model_key;
                            }
                        );                            
                        $model_result_set->$relation = count($related_model_result_set) == 1 ? $related_model_result_set[0] : $related_model_result_set ;
                    }

                }
            }
        }        
    }
}
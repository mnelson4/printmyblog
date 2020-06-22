<?php

namespace PrintMyBlog\system;

use ReflectionClass;

/**
 * Class Context
 *
 * Stores instances of the classes used by Print My Blog for dependency injection.
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class Context
{

    /**
     * @var Context
     */
    static $instance;

    const USE_NEW = 'use_new';
    const REUSE = 'reuse';

    /**
     * @var object[]
     */
    protected $classes = [];

    /**
     * Keys are classnames, values are an array of dependencies to be injected via setter injection.
     * @var array
     */
    protected $deps;


    /**
     *
     * @param string      $classname
     * @param array $args
     * @return object
     */
    public function reuse($classname, $args = []){
        $classname = $this->normalizeClassname($classname);
        if(! isset($this->classes[$classname])){
            $this->classes[$classname] = $this->instantiate($classname,$args);
        }
        return $this->classes[$classname];
    }


    /**
     * @param string $classname
     * @param array $args
     * @return object
     */
    public function use_new($classname, $args = []){
        return $this->instantiate($classname, $args);
    }

    protected function instantiate($classname, $args){
        $classname = $this->normalizeClassname($classname);
        $reflection = new ReflectionClass($classname);
        $obj = $reflection->newInstanceArgs($args);

        if(isset($this->deps[$classname]) && method_exists($obj, 'inject')){
            $classes_depended_on = $this->deps[$classname];
            $dependency_instances = [];
            foreach($classes_depended_on as $dependency_classname => $policy){
                $dependency_classname = $this->normalizeClassname($dependency_classname);
                if($policy === self::USE_NEW){
                    $dependency_instance = $this->instantiate($dependency_classname);
                } else {
                    $dependency_instance = $this->reuse($dependency_classname);
                }
                $dependency_instances[] = $dependency_instance;
            }
            call_user_func_array([$obj,'inject'],$dependency_instances);
        }
        return $obj;
    }


    /**
     * Sets the dependencies in the context. Keys are classnames, values are an array
     * whose keys are classnames dependend on, and values are either self::USE_NEW or self::REUSE.
     * Classes
     */
    protected function setDependencies(){
        $this->deps = [
            'PrintMyBlog\system\Init' => [
                'PrintMyBlog\system\Activation' => self::REUSE,
                'PrintMyBlog\system\VersionHistory' => self::REUSE,
            ],
            'PrintMyBlog\system\Activation' => [
                'PrintMyBlog\system\RequestType' => self::REUSE,
            ],
            'PrintMyBlog\system\RequestType' => [
                'PrintMyBlog\system\VersionHistory' => self::REUSE
            ],
        ];
    }


    /**
     * Makes sure there is no slash at the start of the classname.
     * @param $classname
     * @return string
     */
    protected function normalizeClassname($classname){
        if($classname[0] === '/'){
            $classname = substr(1);
        }
        return $classname;
    }


    /**
     * Wrapper for the global.
     * @return Context
     */
    public static function instance(){
        if(! self::$instance instanceof Context){
            self::$instance = new Context();
            self::$instance->setDependencies();
        }
        return self::$instance;
    }
}
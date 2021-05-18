<?php

namespace Twine\system;

use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Class Context
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
abstract class Context
{
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
     * Context constructor.
     */
    final public function __construct()
    {
    }


    /**
     *
     * @param string      $classname
     * @param array $args
     * @return object
     */
    public function reuse($classname, $args = [])
    {
        $classname = $this->normalizeClassname($classname);
        if (! isset($this->classes[$classname])) {
            $this->classes[$classname] = $this->instantiate($classname, $args);
        }
        return $this->classes[$classname];
    }


    /**
     * @param string $classname
     * @param array $args
     * @return object
     */
    public function useNew($classname, $args = [])
    {
        return $this->instantiate($classname, $args);
    }

    protected function instantiate($classname, $args = [])
    {
        $classname = $this->normalizeClassname($classname);
        $reflection = new ReflectionClass($classname);
        // use the "inject" method if it exists, otherwise fallback to using the constructor
        try {
            // this throws a ReflectionException if the method doesn't exist eh
            $reflection->getMethod('inject');
            $obj = $reflection->newInstanceArgs($args);
            call_user_func_array([$obj,'inject'], $this->getDependencies($classname));
        } catch (ReflectionException $e) {
            $combined_constructor_args = array_merge($args, $this->getDependencies($classname));
            $obj = $reflection->newInstanceArgs($combined_constructor_args);
        }
        return $obj;
    }

    /**
     * @param $classname
     *
     * @return array of whatever dependencies were declared for this classname in the setDependencies method
     */
    protected function getDependencies($classname)
    {
        $dependency_instances = [];
        if (isset($this->deps[$classname])) {
            $classes_depended_on = $this->deps[$classname];

            foreach ($classes_depended_on as $dependency_classname => $policy) {
                // Account for when the dependency isn't a class at all.
                if (is_int($dependency_classname) && ! is_object($policy)) {
                    $dependency_instance = $policy;
                } else {
                    $dependency_classname = $this->normalizeClassname($dependency_classname);
                    if ($policy === self::USE_NEW) {
                        $dependency_instance = $this->instantiate($dependency_classname);
                    } else {
                        $dependency_instance = $this->reuse($dependency_classname);
                    }
                }

                $dependency_instances[] = $dependency_instance;
            }
        }
        return $dependency_instances;
    }

    /**
     * Makes sure there is no slash at the start of the classname.
     * @param $classname
     * @return string
     */
    protected function normalizeClassname($classname)
    {
        if ($classname[0] === '/') {
            $classname = substr($classname, 1);
        }
        return $classname;
    }


    /**
     * Wrapper for the global.
     * @return Context
     */
    public static function instance()
    {
        /** @phpstan-ignore-next-line */
        if (! static::$instance instanceof Context) {
            static::$instance = new static();
            static::$instance->setDependencies();
        }
        return static::$instance;
    }

    /**
     * Sets the dependencies in the context. Keys are classnames, values are an array
     * whose keys are classnames dependend on, and values are either self::USE_NEW or self::REUSE.
     * Classes
     */
    abstract protected function setDependencies();
}

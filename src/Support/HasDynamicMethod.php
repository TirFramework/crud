<?php

namespace Tir\Crud\Support;

trait HasDynamicMethod
{
    /**
     * Store the relations
     *
     * @var array
     */
    private static $dynamic_methods = [];

    /**
     * Add a new relation
     *
     * @param $name
     * @param $closure
     */
    public static function addDynamicRelation($name, $closure)
    {
        static::$dynamic_methods[$name] = $closure;
    }

    /**
     * If the key exists in relations then
     * return call to relation or else
     * return the call to the parent
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (static::hasDynamicMethod($name)) {
            // check the cache first
            if ($this->relationLoaded($name)) {
                return $this->relations[$name];
            }

            // load the relationship
            return $this->getRelationshipFromMethod($name);
        }

        return parent::__get($name);
    }

    /**
     * Determine if a relation exists in dynamic relationships list
     *
     * @param $name
     *
     * @return bool
     */
    public static function hasDynamicMethod($name)
    {
        return array_key_exists($name, static::$dynamic_methods);
    }

    /**
     * If the method exists in relations then
     * return the relation or else
     * return the call to the parent
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (static::hasDynamicMethod($name)) {
            return call_user_func(static::$dynamic_methods[$name], $this);
        }

        return parent::__call($name, $arguments);
    }
}
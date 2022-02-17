<?php

namespace Sitepilot\WpFramework\Traits;

trait HasAttributes
{
    /**
     * The available attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed    
     */
    public function get_attribute($key)
    {
        if (!$key) {
            return;
        }

        // If the attribute exists in the attribute array or has a "get" mutator we will
        // get the attribute's value. Otherwise, we will proceed as if the developers
        // are asking for a relationship's value. This covers both types of values.
        if (
            array_key_exists($key, $this->attributes) ||
            $this->has_get_mutator($key)
        ) {
            if (method_exists($this, 'get_namespace')) {
                $filter = $this->get_namespace($key);
            } else {
                $filter = $this->app->get_namespace($key);
            }

            return apply_filters($filter, $this->get_attribute_value($key));
        }

        return;
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function get_attributes()
    {
        return $this->attributes;
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string  $key
     * @return mixed
     */
    public function get_attribute_value($key)
    {
        return $this->transform_model_value($key, $this->get_attribute_from_array($key));
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function get_attribute_from_array($key)
    {
        return $this->get_attributes()[$key] ?? null;
    }

    /**
     * Transform a raw model value using mutators, casts, etc.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform_model_value($key, $value)
    {
        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->has_get_mutator($key)) {
            return $this->mutate_attribute($key, $value);
        }

        return $value;
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutate_attribute($key, $value)
    {
        return $this->{'get_' . strtolower($key) . '_attribute'}($value);
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function has_get_mutator($key)
    {
        return method_exists($this, 'get_' . strtolower($key) . '_attribute');
    }

    /**
     * Dynamically retrieve attributes.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get_attribute($key);
    }
}

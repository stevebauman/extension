<?php namespace Orchestra\Extension\Traits;

use Illuminate\Support\Arr;

trait OperationTrait
{
    /**
     * Activate an extension.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function activate($name)
    {
        return $this->activating($name);
    }

    /**
     * Activating an extension.
     *
     * @param  string  $name
     *
     * @return bool
     */
    protected function activating($name)
    {
        if (is_null($active = $this->refresh($name))) {
            return false;
        }

        $this->extensions[$name] = $active[$name];
        $this->publish($name);

        $this->dispatcher->activating($name, $active[$name]);

        return true;
    }

    /**
     * Check whether an extension is active.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function activated($name)
    {
        return is_array($this->memory->get("extensions.active.{$name}"));
    }

    /**
     * Check whether an extension is available.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function available($name)
    {
        return is_array($this->memory->get("extensions.available.{$name}"));
    }

    /**
     * Deactivate an extension.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function deactivate($name)
    {
        $memory = $this->memory;
        $active = $memory->get('extensions.active', []);

        if (! isset($active[$name])) {
            return false;
        }

        $memory->put('extensions.active', Arr::except($active, $name));
        $this->dispatcher->deactivating($name, $active[$name]);

        return true;
    }

    /**
     * Refresh extension configuration.
     *
     * @param  string  $name
     *
     * @return array|null
     */
    public function refresh($name)
    {
        $memory    = $this->memory;
        $available = $memory->get('extensions.available', []);
        $active    = $memory->get('extensions.active', []);

        if (! isset($available[$name])) {
            return;
        }

        // Append the activated extension to active extensions, and also
        // publish the extension (migrate the database and publish the
        // asset).
        if (! is_null($handles = Arr::get($active, "{$name}.config.handles"))) {
            Arr::set($available, "{$name}.config.handles", $handles);
        }

        $active[$name] = $available[$name];

        $memory->put('extensions.active', $active);

        return $active;
    }

    /**
     * Reset extension.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function reset($name)
    {
        $memory  = $this->memory;
        $default = $memory->get("extensions.available.{$name}", []);

        $memory->put("extensions.active.{$name}", $default);

        if ($memory->has("extension_{$name}")) {
            $memory->put("extension_{$name}", []);
        }

        return true;
    }

    /**
     * Check if extension is started.
     *
     * @param  string  $name
     *
     * @return bool
     */
    public function started($name)
    {
        return $this->extensions->has($name);
    }

    /**
     * Publish an extension.
     *
     * @param  string
     *
     * @return void
     */
    abstract public function publish($name);
}

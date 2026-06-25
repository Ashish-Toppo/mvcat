<?php

namespace Core\Classes;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionException;
use Core\Classes\Exceptions\ContainerException;
use Core\Classes\Exceptions\CircularDependencyException;

class Container
{
    /**
     * The container's bindings.
     */
    protected array $bindings = [];

    /**
     * The container's shared instances.
     */
    protected array $instances = [];

    /**
     * The stack of concretions currently being built (for circular dependency detection).
     */
    protected array $buildStack = [];

    /**
     * Register a binding with the container.
     */
    public function bind(string $abstract, Closure|string|null $concrete = null, bool $shared = false): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Register a shared binding in the container.
     */
    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register an existing instance as shared in the container.
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Resolve the given type from the container.
     */
    public function get(string $abstract)
    {
        // If we have an instance already, return it
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Get the concrete type or closure
        $concrete = $this->getConcrete($abstract);

        // We're ready to instantiate an instance of the concrete type
        if ($concrete === $abstract || $concrete instanceof Closure) {
            $object = $this->build($concrete);
        } else {
            $object = $this->get($concrete);
        }

        // If it's shared, store it
        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Get the concrete type for a given abstract.
     */
    protected function getConcrete(string $abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }
        return $abstract;
    }

    /**
     * Determine if a given type is shared.
     */
    protected function isShared(string $abstract): bool
    {
        return isset($this->bindings[$abstract]['shared']) && $this->bindings[$abstract]['shared'] === true;
    }

    /**
     * Instantiate a concrete instance of the given type.
     */
    public function build(Closure|string $concrete)
    {
        // If it's a Closure, just execute it
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        // Check for circular dependency
        if (isset($this->buildStack[$concrete])) {
            throw new CircularDependencyException("Circular dependency detected while resolving [$concrete].");
        }

        $this->buildStack[$concrete] = true;

        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            unset($this->buildStack[$concrete]);
            throw new ContainerException("Target class [$concrete] does not exist.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            unset($this->buildStack[$concrete]);
            throw new ContainerException("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // No constructor, just instantiate
        if (is_null($constructor)) {
            unset($this->buildStack[$concrete]);
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters, $concrete);

        unset($this->buildStack[$concrete]);
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     */
    protected function resolveDependencies(array $parameters, string $class): array
    {
        $results = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $results[] = $parameter->getDefaultValue();
                } else {
                    throw new ContainerException("Unresolvable dependency resolving [\${$parameter->getName()}] in class {$class}");
                }
            } else {
                $results[] = $this->get($type->getName());
            }
        }

        return $results;
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     */
    public function call(callable|array|string $callback, array $parameters = [])
    {
        if (is_string($callback) && str_contains($callback, '@')) {
            $callback = explode('@', $callback);
        }

        if (is_array($callback)) {
            // It's [$class, $method]
            $class = is_string($callback[0]) ? $this->get($callback[0]) : $callback[0];
            $method = $callback[1];
            $reflector = new ReflectionMethod($class, $method);
        } else {
            // It's a closure or standalone function
            $reflector = new \ReflectionFunction($callback);
        }

        $dependencies = $this->getMethodDependencies($reflector->getParameters(), $parameters);

        if (is_array($callback)) {
            return $reflector->invokeArgs($class, $dependencies);
        }
        
        return $reflector->invokeArgs($dependencies);
    }

    /**
     * Get all dependencies for a given method.
     */
    protected function getMethodDependencies(array $reflectionParameters, array $providedParameters): array
    {
        $dependencies = [];

        foreach ($reflectionParameters as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            // First check if a parameter with this name was explicitly provided (e.g. route parameters)
            if (array_key_exists($name, $providedParameters)) {
                $dependencies[] = $providedParameters[$name];
                continue;
            }

            // If it's a class type, auto-inject it from the container
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
                continue;
            }

            // Use default value if available
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new ContainerException("Unable to resolve parameter [\${$name}] in method call.");
        }

        return $dependencies;
    }
}

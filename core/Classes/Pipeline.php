<?php

namespace Core\Classes;

use Closure;

class Pipeline
{
    protected Container $container;
    protected mixed $passable;
    protected array $pipes = [];

    public function __construct(Container $container = null)
    {
        $this->container = $container ?? new Container();
    }

    /**
     * Set the object being sent through the pipeline.
     */
    public function send($passable): self
    {
        $this->passable = $passable;
        return $this;
    }

    /**
     * Set the array of pipes.
     */
    public function through(array $pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     */
    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            $destination
        );

        return $pipeline($this->passable);
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     */
    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if (is_string($pipe)) {
                    // Resolve the middleware from the container
                    $pipeInstance = $this->container->get($pipe);
                    return $pipeInstance->handle($passable, $stack);
                } elseif (is_object($pipe)) {
                    // If it's already instantiated
                    return $pipe->handle($passable, $stack);
                } elseif ($pipe instanceof Closure) {
                    // If it's a closure
                    return $pipe($passable, $stack);
                }
            };
        };
    }
}

<?php

namespace AppBundle\Entity;

/**
 * This trait gives the ability to an entity to become CRUDable. It means it
 * virtually exposes its private state as if it were public. The magic getters
 * and setters methods will be invoked when trying to access a non exposed
 * attribute. If the entity class has an explicit getter and/or setter methods
 * for an attribute, it'll automatically be invoked.
 *
 * This trait should only be used for entity objects for which guaranteeing the
 * consistency of its internal state is not really relevant. It helps build
 * complex PHP applications with lots of specific domain logic by mixing both
 * a clean business API (relevant domain methods, atomic operations, etc.) and a
 * CRUD interface (getters, setters, public attributes) to speed up development
 * (RAD, agile, prototyping philosophies).
 */
trait EntityCrudTrait
{
    /**
     * Returns the number of required attributes for a method of a class.
     *
     * @param string $class  The fully qualified class name
     * @param string $method The method name to introspect in this class
     *
     * @return int
     */
    private static function countNumberOfRequiredArguments(string $class, string $method): int
    {
        $rm = new \ReflectionMethod($class, $method);

        return $rm->getNumberOfRequiredParameters();
    }

    /**
     * Magically mutates the value of a non accessible object's property when
     * it's accessed like if it was public.
     *
     * The method first tries to check if the property is available through one
     * of the following mutator methods:
     *
     *   * updateThePropertyName()
     *   * changeThePropertyName()
     *   * setThePropertyName()
     *
     * These mutator methods must have one and only one required argument. If
     * they have zero or at least two required arguments, then they're ignored.
     *
     * If none of these methods combinations and requirements match, then the
     * property is magically mutated as if it was defined with a public scope.
     *
     * @param string $property The name of the virtual property to change
     * @param mixed  $value    The value to give to that virtual property
     *
     * @throws \InvalidArgumentException
     */
    public function __set($property, $value)
    {
        $class = get_class($this);
        $ucProperty = ucfirst($property);
        foreach (['update', 'change', 'set'] as $mutatorPrefix) {
            $method = sprintf('%s%s', $mutatorPrefix, $ucProperty);
            if (method_exists($this, $method) && 1 === self::countNumberOfRequiredArguments($class, $method)) {
                call_user_func_array([$this, $method], [$value]);

                return;
            }
        }

        if (!property_exists($this, $property)) {
            throw new \InvalidArgumentException(sprintf('Unable to magically write the value of the $%s property of the object of type %s.', $property, $class));
        }

        $this->{$property} = $value;
    }

    /**
     * Magically reads the value of a non accessible object's property when it
     * is accessed like if it was public.
     *
     * The method first tries to check if the property is available through one
     * of the following accessor methods:
     *
     *   * getThePropertyName()
     *   * thePropertyName()
     *   * isThePropertyName()
     *   * hasThePropertyName()
     *
     * These accessor methods must not have any required arguments. If they
     * have at least one required argument, then they will be ignored.
     *
     * If none of these methods combinations and requirements match, then the
     * property is magically read as if it was defined with a public scope.
     *
     * @param string $property The name of the virtual property to read
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function __get($property)
    {
        $class = get_class($this);
        $ucProperty = ucfirst($property);
        foreach (['get', '', 'is', 'has'] as $accessorPrefix) {
            $method = lcfirst(sprintf('%s%s', $accessorPrefix, $ucProperty));
            if (method_exists($this, $method) && !self::countNumberOfRequiredArguments($class, $method)) {
                return call_user_func([$this, $method]);
            }
        }

        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        throw new \InvalidArgumentException(sprintf('Unable to magically read the value of the $%s property of the object of type %s.', $property, $class));
    }

    /**
     * Temporary workaround to be able to call the same method multiple times
     * with Alice. Current Alice version doesn't support calling the same method
     * multiple times and will be implemented in the next upcoming major release.
     *
     * @todo remove this method when Alice library will be >= 3.0
     *
     * @param string $method    The magic invoked method name
     * @param array  $arguments The arguments to pass to the magic invoked method
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $method, array $arguments)
    {
        if (!preg_match('/^(?:set)?\_(?:\d+)?\_(?P<method>[a-z][a-zA-Z0-9]+)$/', $method, $matches)) {
            throw new \BadMethodCallException(sprintf('Trying to call an unsupported method %s() on an object of type %s.', $method, get_class($this)));
        }

        $invokable = $matches['method'];
        $callable = [$this, $invokable];
        if (!method_exists($this, $invokable) || !is_callable($callable)) {
            throw new \BadMethodCallException(sprintf('Method %s() does not exist or is not invokable on an object of type %s.', $invokable, get_class($this)));
        }

        @trigger_error(sprintf('Invoking the real %s() method through its virtual alias method name %s() is deprecated and will be removed as soon as Alice fixtures library reaches version 3.0.0.', $invokable, $method), E_USER_DEPRECATED);

        return call_user_func_array($callable, (array) $arguments[0]);
    }
}

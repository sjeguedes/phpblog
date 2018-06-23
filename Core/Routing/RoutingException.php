<?php
namespace Core\Routing;

/**
 * Create a custom class to manage routing exceptions
 */
class RoutingException extends \Exception
{
    /**
     * Override Constructor: should use message
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     *
     * @return void
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        // Make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    /**
      * Override __toString()
      * Example
      *
      * @return string
      */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    // Declare custom methods: do stuff here!
}

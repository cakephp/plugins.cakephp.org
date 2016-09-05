<?php
namespace App\Exception;

use Cake\Core\Exception\Exception;

class RedirectException extends Exception
{
    /**
     * A route array
     *
     * @var array
     */
    protected $_route = [];

    /**
     * Set the route
     *
     * @param array $route a route array
     * @return void
     */
    public function setRoute(array $route)
    {
        $this->_route = $route;
    }

    /**
     * Get the route
     *
     * @return array
     */
    public function getRoute()
    {
        return $this->_route;
    }
}

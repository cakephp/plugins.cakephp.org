<?php
namespace Cake\Controller\Component;

use Cake\Controller\Component;
use Exception;
use InvalidArgumentException;
use ReflectionMethod;

class PersistErrorsComponent extends Component
{
    /**
     * Default config for the Prg Component.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'sessionPrefix' => 'PersistErrors',
    ];

    /**
     * Initialize properties.
     *
     * @param array $config The config data.
     * @return void
     */
    public function initialize(array $config)
    {
        $controller = $this->_registry->getController();
        if ($controller === null) {
            $this->request = Request::createFromGlobals();
        }
    }

    /**
     * Applies errors stored in the session to an object
     *
     * @param object $object An object that has an errors/setErrors method
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function apply($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('Argument 1 passed to PersistErrorsComponent::apply() must be an object');
        }

        $sessionPrefix = $this->getConfig('sessionPrefix');
        $className = get_class($object);
        $sessionKey = sprintf('%s.%s', $sessionPrefix, $className);
        if (!$this->request->getSession()->check($sessionKey)) {
            return;
        }

        $errors = $this->request->getSession()->read($sessionKey);
        $this->request->getSession()->delete($sessionKey);
        if (method_exists($object, 'setErrors')) {
            $object->setErrors($errors);
        } elseif (method_exists($object, 'errors')) {
            $ref = new ReflectionMethod($className, 'errors');
            if (count($ref->getParameters()) === 0) {
                throw new Exception('Object passed to PersistErrorsComponent::apply() must have an errors() or setErrors() method');
            }
            $object->errors($errors);
        }
    }

    /**
     * Persists an object's errors to the session
     *
     * @param object $object An object that has an errors/getErrors method
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function persist($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('Argument 1 passed to PersistErrorsComponent::persist() must be an object');
        }

        $sessionPrefix = $this->getConfig('sessionPrefix');
        $className = get_class($object);
        $sessionKey = sprintf('%s.%s', $sessionPrefix, $className);

        $errors = [];
        if (method_exists($object, 'getErrors')) {
            $errors = $object->getErrors();
        } elseif (method_exists($object, 'errors')) {
            $errors = $object->errors();
        }

        $this->request->getSession()->write([
            $sessionKey => $errors,
        ]);
    }
}

<?php
namespace App\Error;

use App\Exception\RedirectException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Error\ExceptionRenderer as CoreExceptionRenderer;
use Cake\Routing\Router;

class ExceptionRenderer extends CoreExceptionRenderer
{
    /**
     * Renders the response for the exception.
     *
     * @return \Cake\Network\Response The response to be sent.
     */
    public function render()
    {
        $renderMethod = sprintf(
            'render%s%s',
            $this->controller->request->getParam('controller'),
            ucfirst($this->controller->request->getParam('action'))
        );

        if (method_exists($this, $renderMethod) && $renderMethod !== 'render') {
            return $this->$renderMethod();
        }

        return parent::render();
    }

    /**
     * Renders the /packages/view response for the exception.
     *
     * @return \Cake\Network\Response The response to be sent.
     */
    public function renderPackagesView()
    {
        if ($this->error instanceof RecordNotFoundException) {
            $this->controller->response = $this->controller->response->withStatus(302);
            $this->controller->response = $this->controller->response->withLocation(Router::url([
                'controller' => 'Packages',
                'action' => 'index'
            ], true));
            return $this->controller->response;
        }

        if ($this->error instanceof RedirectException) {
            $route = $this->error->getRoute();
            $this->controller->response = $this->controller->response->withStatus(302);
            $this->controller->response = $this->controller->response->location(Router::url($route, true));
            return $this->controller->response;
        }

        return parent::render();
    }
}

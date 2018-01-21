<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View;

use BootstrapUI\View\UIViewTrait;
use Cake\Event\EventListenerInterface;
use Cake\View\View;

/**
 * Application View
 *
 * Your application’s default view class
 *
 * @link http://book.cakephp.org/3.0/en/views.html#the-app-view
 */
class AppView extends View implements EventListenerInterface
{

    use UIViewTrait;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading helpers.
     *
     * e.g. `$this->loadHelper('Html');`
     *
     * @return void
     */
    public function initialize()
    {
        \Cake\Log\Log::info(json_encode($this->request->getAttributes() + ['referer' => $this->request->referer()]));
        $this->initializeUI(['layout' => $this->layout]);
        $this->loadHelper('AssetCompress.AssetCompress');
        $this->loadHelper('Menu');
        $this->loadHelper('Form');
        $this->getEventManager()->on($this);
    }

    /**
     * Returns a list of all events that this View class will listen to.
     *
     * @return array List of events this class listens to. Defaults to `[]`.
     */
    public function implementedEvents()
    {
        return [
            'View.beforeLayout' => 'beforeLayout',
        ];
    }

    /**
     * View.beforeLayout event
     *
     * @param string $layoutFileName Name of layout being rendered
     * @return void
     * @throws \Cake\Core\Exception\Exception if there is an error in the view.
     */
    public function beforeLayout($layoutFileName)
    {
        $controllerName = lcfirst($this->request->params['controller']);
        $actionName = $this->request->params['action'];

        $this->set([
            '_bodyId' => $controllerName,
            '_bodyClass' => "{$controllerName}-{$actionName}",
        ]);
    }
}

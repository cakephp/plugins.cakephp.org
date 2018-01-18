<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{

    public function debug()
    {
        $request = $this->request;
        $ip = $this->getRequestIpAddress();

        if (!in_array($ip, explode(',', env('WHITELISTED_IPS', 'Example')))) {
            $this->set('_serialize', ['data']);
            $this->set('data', []);
            return;
        }

        $data = [
            'env' => ['PHP_VERSION' => phpversion()] + $this->getEnvByPrefix('DOKKU_') + $this->getEnvByPrefix('DOCKER_')
            'request' => [
                'attributes' => $request->getAttributes(),
                'get' => $request->getQueryParams(),
                'headers' => $request->getHeaders(),
                'isMobile' => $this->RequestHandler->isMobile(),
                'isXhr' => $request->is('ajax'),
                'method' => $request->getMethod(),
                'parameters' => $request->getData(),
                'uri' => $request->getUri(),
            ],
            'user' => [
                'ip' => $ip,
            ],
        ];

        $this->set('_serialize', ['data']);
        $this->set('data', $data);
    }

    /**
     * getEnvByPrefix
     *
     * @param string $prefix
     * @param bool $stripPrefix
     * @return array
     */
    public function getEnvByPrefix($prefix = '', $stripPrefix = false)
    {
        if (!$prefix) {
            return [];
        }

        $raw = $_SERVER + $_ENV;
        $len = strlen($prefix);

        $return = [];
        foreach ($raw as $key => $val) {
            if (substr($key, 0, $len) !== $prefix) {
                continue;
            }

            if ($stripPrefix) {
                $key = substr($key, $len);
            }
            $return[$key] = $val;
        }
        ksort($return);

        return $return;
    }

    protected function getRequestIpAddress()
    {
        $ordered_choices = array(
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        );

        // check each server var in order
        // accepted ip must be non null and not private or reserved
        foreach ($ordered_choices as $var) {
            if (isset($_SERVER[$var])) {
                $ip = $_SERVER[$var];
                if ($ip && $this->isValidIp($ip)) {
                    return $ip;
                }
            }
        }

        return null;
    }

    protected function isValidIp($ip)
    {
        $options = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        return filter_var($ip, FILTER_VALIDATE_IP, $options) !== false;
    }

    /**
     * Displays a view
     *
     * @param string ...$path Path segments.
     * @return void|\Cake\Network\Response
     * @throws \Cake\Network\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Network\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function display(...$path)
    {
        $count = count($path);
        if (!$count) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        try {
            $this->render(implode('/', $path));
        } catch (MissingTemplateException $e) {
            if (Configure::read('debug')) {
                throw $e;
            }
            throw new NotFoundException();
        }
    }
}

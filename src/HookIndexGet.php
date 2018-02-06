<?php
/**
 * This file is part of the Hooks
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/hooks
 * @version 1.0.1
 * @package pllano.hooks
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Pllano\Hooks;
 
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
class HookIndexGet {
 
    private $args;
    private $request;
    private $response;
    private $query = null;
    private $app = null;
    private $routers = null;
    private $view;
    private $render;
 
    public function http(Request $request, Response $response, array $args, $query = null, $app = null, $routers = null)
    {
        $this->args = $args;
        $this->request = $request;
        $this->response = $response;
        $this->query = $query;
        $this->app = $app;
        $this->routers = $routers;
        $this->set();
    }
 
    public function set()
    {
        // Обрабатываем данные
        // Получаем GET параметры
        $getParams = $this->request->getQueryParams();
        // print_r($getParams);
        // Получаем данные отправленные нам через POST
        $postParams = $this->request->getParsedBody();
        // print_r($postParams);
    }
 
    public function get($view = null, $render = null)
    {
        $this->view = $view;
        $this->render = $render;
        $this->run();
    }
 
    public function run()
    {
        // Обрабатываем данные
        // Меняем файл рендера
        $this->render = 'hook.html';
    }
 
    public function request()
    {
        return $this->request;
    }
 
    public function response()
    {
        return $this->response;
    }
 
    public function args()
    {
        return $this->args;
    }
 
    public function view()
    {
        return $this->view;
    }
 
    public function render()
    {
        return $this->render;
    }
 
    public function query()
    {
        return $this->query;
    }
 
    public function app()
    {
        return $this->app;
    }
 
    public function routers()
    {
        return $this->routers;
    }
 
}
 
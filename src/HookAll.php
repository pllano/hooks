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
 
class HookAll {
 
    private $request;
    private $view = [];
    private $render = null;
    private $resource = null;
    private $name_db = null;
    private $query = null;
    private $app = null;
    private $postArr = [];
    private $postQuery = null;
    private $id = null;
    private $callback = null;
 
    public function http(Request $request, $query = null, $app = null)
    {
        $this->request = $request;
        $this->query = $query;
        $this->app = $app;
        $this->set();
    }
 
    public function state()
    {
        return true;
    }
 
    public function set()
    {
        // Обрабатываем данные
        // Получаем GET параметры
        $getParams = $this->request->getQueryParams();
        // print_r($getParams);
        // Получаем данные отправленные нам через POST
        $postParams = $this->request->getParsedBody();
    }
 
    public function get($view = null, $render = null)
    {
        $this->view = $view;
        $this->render = $render;
        $this->run();
    }
 
    public function post($resource = null, $name_db = null, $postQuery = null, array $postArr = [], $id = null)
    {
        $this->resource = $resource;
        $this->name_db = $name_db;
        $this->postQuery = $postQuery;
        $this->postArr = $postArr;
        $this->id = $id;
        $this->run();
    }
 
    public function run()
    {
        // Обрабатываем данные
        $this->render = '404.html';
    }
 
    public function request()
    {
        return $this->request;
    }
 
    public function query()
    {
        return $this->query;
    }
 
    public function app()
    {
        return $this->app;
    }
 
    public function view()
    {
        return $this->view;
    }
 
    public function render()
    {
        return $this->render;
    }
 
    public function resource()
    {
        return $this->resource;
    }
 
    public function name_db()
    {
        return $this->name_db;
    }
 
    public function postArr()
    {
        return $this->postArr;
    }
 
    public function postQuery()
    {
        return $this->postQuery;
    }
 
    public function id()
    {
        return $this->id;
    }
 
    public function callback($callback = null)
    {
        if(isset($this->callback)) {
            return $this->callback;
        } else {
            return $callback;
        }
    }
 
}
 
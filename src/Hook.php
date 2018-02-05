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
 
class Hook
{
    private $config;
    protected $request;
    protected $response;
    protected $args;
    protected $view = [];
    protected $render = null;
    protected $name_db = null;
    protected $query = null;
    protected $app = null;
    protected $routers = null;
    protected $resource = null;
    protected $postArr = [];
    protected $postQuery = null;
    protected $id = null;
    protected $callback = null;
    protected $hooks = null;
    protected $logger = null;
    protected $print = null;
    private $path = __DIR__ . '/';
 
    function __construct($config = [])
    {
        if(isset($config)) {
            $this->config = $config;
            if(isset($this->config['hooks']['print'])) {
                $this->print = $this->config['hooks']['print'];
            }
            if((int)$this->print == 1) {
                 print("Config из конструктора<br>");
            }
        } else {
            
            $this->config = $this->get_config();
            $this->print = $this->config['hooks']['print'];
            if((int)$this->print == 1) {
                print("Config из файла hooks.json<br>");
            }
        }
    }
 
    public function set_config($path = null)
    {
        if(isset($path)) {
            $this->path = $path;
        }
    }
 
    public function get_config()
    {
        return json_decode($this->path.'/hooks.json', true);
    }
 
    public function http(Request $request, Response $response, array $args, $query = null, $app = null, $routers = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        if(isset($query)) {
            $this->query = $query;
        }
        if(isset($app)) {
            $this->app = $app;
        }
        if(isset($routers)) {
            $this->routers = $routers;
        }
        $this->set();
    }
 
    public function set()
    {
        $hooks = $this->hooks($this->query);
        if(isset($hooks[0])) {
            foreach($hooks as $value)
            {
                if(isset($value['vendor'])) {
                    $vendor = $value['vendor'];
                    if (class_exists($vendor)) {
                        $hook = new $vendor();
                    } else {
                        $this->logger = "Vendor {$vendor} недоступен";
                        //print_r($this->logger);
                        return false;
                    }
                    if(method_exists($vendor,'http')) {
                        $hook->http($this->request, $this->response, $this->args, $this->query);
                    }
                    if(method_exists($vendor,'request')) {
                        $this->request = $hook->request();
                    }
                    if(method_exists($vendor,'response')) {
                        $this->response = $hook->response();
                    }
                    if(method_exists($vendor,'args')) {
                        $this->args = $hook->args();
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }
 
    public function get($view = [], $render = null)
    {
        $this->view = $view;
        $this->render = $render;
        $this->logger = $this->render;
        $this->run();
    }
 
    public function post($resource = null, $name_db = null, $postQuery = null, array $postArr = [], $id = null)
    {
        if(isset($resource)) {
            $this->id = $resource;
        }
        if(isset($name_db)) {
            $this->name_db = $name_db;
        }
        if(isset($postQuery)) {
            $this->postQuery = $postQuery;
        }
        if(isset($postArr)) {
            $this->postArr = $postArr;
        }
        if(isset($id)) {
            $this->id = $id;
        }
        $this->run();
    }
 
    public function run()
    {
        $hooks = $this->hooks($this->query);
        if((int)$this->print == 1) {
            print_r($hooks);
        }
        if(isset($hooks[0])) {
            foreach($hooks as $value)
            {
                if(isset($value['vendor'])) {
                    $vendor = $value['vendor'];
                    if (class_exists($vendor)) {
                        $hook = new $vendor();
                        if((int)$this->print == 1) {
                            print("vendor = {$vendor}<br>");
                        }
                    } else {
                        $this->logger = "Vendor {$vendor} недоступен";
                        if((int)$this->print == 1) {
                            print("logger - Vendor {$vendor} недоступен<br>");
                        }
                        return false;
                    }
                    if ($this->query == 'GET') {
                        if(method_exists($vendor,'get')) {
                            $hook->get($this->view, $this->render);
                        }
                        if(method_exists($vendor,'view')) {
                            $this->view = $hook->view();
                        }
                        if(method_exists($vendor,'render')) {
                            $this->render = $hook->render();
                        }
                    } elseif ($this->query == 'POST') {
                        if(method_exists($vendor,'post')) {
                            $hook->post($this->resource, $this->name_db, $this->postQuery, $this->postArr, $this->id);
                        }
                        if(method_exists($vendor,'callback')) {
                            $this->callback = $hook->callback($this->callback);
                        } 
                    }
                }
            }
            return true;
        } else {
            $this->logger = $this->render;
            return false;
        }
    }
 
    public function hooks($query = null)
    {
        $hooks = [];
        $hook = null;
        foreach($this->config['hooks']['vendor'] as $key => $value)
        {
            $run = false;
            if (isset($value['state']) && (int)$value['state'] == 1) {
                if ($value['app'] == $this->app || $value['app'] == 'all') {
                    if (isset($value['render']) && $value['render'] != '' && $value['render'] != ' ' && (int)$value['render'] != 0) {
                        if($value['query'] == $query && $value['render'] == $this->render) {
                            $run = true;
                            if((int)$this->print == 1) {
                                print("run = {$run} - true - 5<br>");
                            }
                        } elseif ($value['query'] == $query && $value['render'] == 'all') {
                            $run = true;
                            if((int)$this->print == 1) {
                                print("run = {$run} - true - 4<br>");
                            }
                        } elseif ($value['query'] == 'all' && $value['render'] == 'all') {
                            $run = true;
                            if((int)$this->print == 1) {
                                print("run = {$run} - true - 3<br>");
                            }
                        }
                    } else {
                        if($value['query'] == $query) {
                            $run = true;
                            if((int)$this->print == 1) {
                                print("run = {$run} - true - 2<br>");
                            }
                        } elseif ($value['query'] == 'all') {
                            $run = true;
                            if((int)$this->print == 1) {
                                print("run = {$run} - true - 1<br>");
                            }
                        }
                    }
                }
            }
 
            if($run === true) {
                $hook['vendor'] = $value['vendor'];
                $hooks[] = $hook;
            }
        }
        
        if($run === true) {
            if((int)$this->print == 1) {
                print("run = {$run} - true - 7<br>");
            }
        } else {
            if((int)$this->print == 1) {
                print("run = {$run} - false - 8<br>");
            }
        }
 
        return $hooks;
 
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
 
    public function setResource($resource = null)
    {
        if(isset($resource)) {
            $this->resource = $resource;
        }
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
 
    public function logger()
    {
        return $this->logger;
    }
 
}
 
<?php /**
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

class Hook
{
    private $param;
    protected $request;
    private $view = [];
    private $render = null;
    private $name_db = null;
    private $query = null;
    private $app = null;
    private $routers = null;
    private $resource = null;
    private $url;
    private $postArr = [];
    private $postQuery = null;
    private $id = null;
    private $callback = null;
    private $hooks = null;
    private $logger = null;
    private $print = null;
    private $path = __DIR__ . '/';
    private $state = true;
    
    function __construct($param = [])
    {
        if(isset($param)) {
            $this->param = $param;
            if(isset($this->param['hooks']['print'])) {
                $this->print = $this->param['hooks']['print'];
            }
            if((int)$this->print == 1) {
                print("param из конструктора<br>");
            }
        } else {
            
            $this->param = $this->get_param();
            $this->print = $this->param['hooks']['print'];
            if((int)$this->print == 1) {
                print("param из файла hooks.json<br>");
            }
        }
    }
    
    public function set_param($path = null)
    {
        if(isset($path)) {
            $this->path = $path;
        }
    }
    
    public function get_param()
    {
        return json_decode($this->path.'/hooks.json', true);
    }
    
    public function http(Request $request, $query = null, $app = null, $routers = null)
    {
        $this->request = $request;
        if(isset($query) && !empty($query)) {
            $this->query = $query;
        }
        if(isset($app) && !empty($app)) {
            $this->app = $app;
        }
        $this->url = $request->getUri()->getPath();
        if((int)$this->print == 1) {
            print("getUri = {$this->url}<br>");
        }
        if(isset($routers) && !empty($routers)) {
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
                        return false;
                    }
                    if(method_exists($vendor,'http')) {
                        $hook->http($this->request, $this->query, $this->app, $this->routers);
                        $this->state = $hook->state();
                    }
                    if(method_exists($vendor,'request')) {
                        $this->request = $hook->request();
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function get($render = null, $view = [])
    {
        $this->view = $view;
        
        if(isset($render) && !empty($render)) {
            $this->render = $render;
        }
        $this->run();
    }
    
    public function post($resource = null, $name_db = null, $postQuery = null, array $postArr = [], $id = null)
    {
        if(isset($resource)) {
            $this->resource = $resource;
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
    
    public function state()
    {
        return $this->state;
    }
    
    public function run()
    {
        $hooks = $this->hooks($this->query);
        if(isset($hooks[0])) {
            foreach($hooks as $value)
            {
                if(isset($value['vendor'])) {
                    $this->vendor = $value['vendor'];
                    if (class_exists($this->vendor)) {
                        $hook = new $this->vendor();
                        if((int)$this->print == 1) {
                            print("vendor = {$this->vendor}<br>");
                        }
                    } else {
                        //$this->logger = "{$this->vendor} - не доступен";
                        if((int)$this->print == 1) {
                            print("{$this->vendor} - не доступен<br>");
                        }
                        return false;
                    }
                    if ($this->query == 'GET') {
                        if(method_exists($this->vendor,'get')) {
                            $hook->get($this->view, $this->render);
                        }
                        if(method_exists($this->vendor,'view')) {
                            $this->view = $hook->view();
                        }
                        if(method_exists($this->vendor,'render')) {
                            $this->render = $hook->render();
                        }
                    } elseif ($this->query == 'POST') {
                        if(method_exists($this->vendor,'post')) {
                            $hook->post($this->resource, $this->name_db, $this->postQuery, $this->postArr, $this->id);
                        }
                        if(method_exists($this->vendor,'callback')) {
                            $this->callback = $hook->callback($this->callback);
                        }
                    }
                }
            }
            return true;
        } else {
            //$this->logger = $this->render;
            return false;
        }
    }
    
    public function hooks($query = null)
    {
        $hooks = [];
        $arr = [];
        $key = ''; $value = '';
        $hook = [];
        foreach($this->param['hooks']['vendor'] as $key => $value)
        {
            $run = false;
            $k = ''; $v = '';
            foreach($value as $k => $v)
            {
                if(isset($v) && !empty($v)) {
                    if($v == "all"){
                        $arr[$k] = $this->{$k};
                    } else {
                        $arr[$k] = $v;
                    }
                }
            }
            $hook = $arr;
            
            if($hook['state'] == 1){
                $keys = ''; $val = '';
                $i=0; $p=0;
                foreach($hook as $keys => $val)
                {
                    if($keys != 'state' && $keys != 'vendor' && $keys != 'config'){
                        $i+=1;
                        if($this->{$keys} == $val){
                            if((int)$this->print == 1) {
                                print("this->keys = {$this->$keys}<br>");
                            }
                            $p+=1;
                        }
                    }
                }
            }
            if($i == $p) {
                $run = true;
                if((int)$this->print == 1) {
                    print("i = {$i} -- p = {$p}<br>");
                }
            }
            if($run === true) {
                $hooks[] = $hook;
            }
        }
        
        return $hooks;
        
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
    
    public function setResource($resource = null)
    {
        if(isset($resource) && !empty($resource)) {
            $this->resource = $resource;
        }
    }
    
    public function resource()
    {
        return $this->resource;
    }
    
    public function setUrl($url = null)
    {
        if(isset($url) && !empty($url)) {
            $this->url = $url;
            $this->vendor->setUrl($url);
        }
    }
    
    public function url()
    {
        return $this->url;
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
    
    public function callback(array $callback = []): array
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

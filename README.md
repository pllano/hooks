# Hooks
Hooks - запускает выполнение ожидающих классов в начале и в конце скрипта, передает им информацию для обработки которую они могут подменять или брать из нее данные без подмены. Это дает возможность писать дополнения без вмешательства в код вашей App.
## Hooks - перехватывает и может менять:
- `Request` в `GET` и `POST` запросах
- `Response` в `GET` и `POST` запросах
- `View` - Массив для шаблонизатора в `GET` запросах
- `Render` - Название шаблона для рендера - в `GET` запросах
- `Callback` в `POST` запросах
## Конфигурация
Передать конфигурацию можно двумя способами:
- Массивом в конструктор `$config = [];`
- Из фала [`hooks.json`](https://github.com/pllano/hooks/blob/master/src/hooks.json)
### Параметры конфигурации
- `print` - Вывести отладочную информацию `1` или выключить `0`
- `vendor` - Полное название класса
- `query` - запрос при котором сработает `GET` `POST` или `all`
- `render` - шаблон для рендера
- `routers` - название роутера
- `resource` - таблица или ресурс к которому происходит обращение
- `app` - тип App - `site` `admin` - так как названия `render` могут совпадать
- `state` - статус `0` - не активен или `1` - активен
- `config` - индивидуальная конфигурация `{массив}` или `none`
```json
{
  "hooks": {
    "print": "1",
    "vendor": {
      "demo-hook-index-get": {
        "vendor": "\\Pllano\\Hooks\\HookIndexGet",
        "query": "GET",
        "render": "index.html",
        "routers": "none",
        "resource": "none",
        "app": "site",
        "state": "1",
        "config": "none"
      },
      "demo-hook-all": {
        "vendor": "\\Pllano\\Hooks\\HookAll",
        "query": "all",
        "render": "all",
        "routers": "none",
        "resource": "none",
        "app": "site",
        "state": "0",
        "config": "none"
      },
      "security": {
        "vendor": "\\Pllano\\Hooks\\Security",
        "query": "all",
        "render": "all",
        "routers": "none",
        "resource": "none",
        "app": "site",
        "state": "0",
        "config": "none"
      }
    }
  }
}
```
## Каркасы для создания пользовательских Hooks
В комплекте идет два каркаса для разработки. Сохраните и доработайте один из этих классов в зависимости от необходимости.
- Класс [`HookIndexGet`](https://github.com/pllano/hooks/blob/master/src/HookIndexGet.php) - обрабатывает только `GET` запросы. По умолчанию должен заменить `render` на `404.html` таким образом вы сможете проверить что Hooks работает.
- Класс [`HookAll`](https://github.com/pllano/hooks/blob/master/src/HookAll.php) - обрабатывает все запросы 
## Использование `GET`
```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Pllano\Hooks\Hook;
 
$app->get('/', function (Request $request, Response $response, array $args) {
    // Передать конфигурацию в конструктор
    $config = [];
    $query = 'GET';
    $app = 'site';
    $routers = null;
    // Если передать пустой массив [] возмет конфигурацию из файла hooks.json
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook($config);
    $hook->http($request, $response, $args, $query, $app, $routers);
    $request = $hook->request();
    $args = $hook->args();
    $hook->setResource('user');
 
    // Начало вашей обработки
    $view = []; // Массив для шаблонизатора
    $render = 'index.twig'; // Название файла шаблона
    // Конец вашей обработки
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($view, $render);
    // Подменяем ответ
    $response = $hook->response();
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->view->render($hook->render(), $hook->view());
});
```
## Использование `POST`
```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Pllano\Hooks\Hook;
 
$app->post('/post', function (Request $request, Response $response, array $args) {
    // Передать конфигурацию в конструктор
    $config = [];
    // Если передать пустой массив [] возмет конфигурацию из файла hooks.json
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook($config);
    $hook->http($request, $response, $args, 'POST', 'site');
    $request = $hook->request();
    $args = $hook->args();
 
    // Начало вашей обработки
    $callback = []; // Массив для вывода ответа
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Конец вашей обработки
 
    // Запись в лог
    $this->logger->info($hook->logger());
    // Подменяем ответ
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
});
```
## Установка
### Подключить с помощью Composer
```json
{
  "require": {
    "pllano/hooks": "~1.0.1"
  }
}
```
### Подключить с помощью [AutoRequire](https://github.com/pllano/auto-require)
```json
{
  "require": [{
      "namespace": "Pllano\\Hooks",
      "dir": "/pllano/hooks/src",
      "link": "https://github.com/pllano/hooks/archive/master.zip",
      "git": "https://github.com/pllano/hooks",
      "name": "hooks",
      "version": "master",
      "vendor": "pllano",
      "state": "1",
      "system_package": "1"
    }, {
      "namespace": "Psr\\Http\\Message",
      "dir": "/psr/http-message/src",
      "link": "https://github.com/php-fig/http-message/archive/1.0.zip",
      "git": "https://github.com/php-fig/http-message",
      "name": "http-message",
      "version": "1.0",
      "vendor": "psr",
      "state": "1",
      "system_package": "1"
    }
  ]
}
```

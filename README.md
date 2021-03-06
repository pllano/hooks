# Hooks
Менеджер `Pllano\Hooks\Hook` - запускает выполнение ожидающих классов в начале и в конце или в необходимом месте скрипта, передает им информацию для обработки которую они могут подменять или брать из нее данные без подмены. Это дает возможность писать дополнения без вмешательства в код вашей App. Конкретный хук не должен знать когда ему выполнятся, это должен определить менеджер Pllano\Hooks\Hook взяв параметры хука и сравнить их с текущими условиями. Если есть совпадение запустить хук. Задача хука выполнить свою миссию.
## Как и где использовать ? Примеры:
- Вы хотите подключить и вести статистику посещаемости сайта. Вы добавляете плагин в список выполнения и только берете данные из Request отдавая их назад без изменения.
- Вам необходимо контролировать все приходящие параметры через POST для того чтобы повысить безопасность. Вы создаете хук и нацеливаете его только на POST запросы. Обрабатываете  параметры из массива $post = $request->getParsedBody(); и возвращаете их проверенными и очищенными от лишнего.
- Вы хотите вести черный/белый списки посетителей чтобы повысить защиту от возможных DDoS. Для этого необходимо в первую очередь настроить максимальное кеширование и дописать плагин отсекающий IP с большим количеством обращений к сайту. Также нужен плагин который будет искать на вашем сайте все тяжелые запросы и сообщать вам о них для добавления их в кеш или оптимизацию скриптов.
- 

## Hooks - перехватывает и может менять:
- `Request` в `GET` и `POST` запросах
- `Response` в `GET` и `POST` запросах
- `View` - Массив для шаблонизатора в `GET` запросах
- `Render` - Название шаблона для рендера - в `GET` запросах
- `Callback` в `POST` запросах
## Установить список Hook для выполнения
Передать список Hook можно через param двумя способами:
- Массивом в конструктор `$param = [];`
- Из фала [`hooks.json`](https://github.com/pllano/hooks/blob/master/src/hooks.json)
### Параметры конфигурации Hook
- `print` - Вывести отладочную информацию `1` или выключить `0`
- `vendor` - Полное название класса
- `query` - запрос при котором сработает `GET` `POST` или `all` ноль использовать нельзя.
- `render` - шаблон для рендера `index.twig` или `all` или `0`
- `url` - конкретный url с `/` или `all` или `0` (планируем доработать `*` пример `/article-*.html`)
- `routers` - название роутера `index`, `article` или `0` или `all`
- `resource` - таблица или ресурс к которому происходит обращение `user` или `all` или `0`
- `app` - тип App - `site` `admin` - так как названия `render` могут совпадать или `all` ноль использовать нельзя.
- `state` - статус `0` - не активен или `1` - активен
- `config` - индивидуальная конфигурация `{массив}` или `0`
```json
{
"hooks": {
    "print": 1,
    "vendor": {
      "demo-hook-index-get": {
        "vendor": "\\Pllano\\Hooks\\HookIndexGet",
        "query": "GET",
        "render": 0,
        "routers": 0,
        "resource": 0,
        "url": "all",
        "app": "site",
        "state": 1,
        "config": 0
      },
      "demo-hook-all": {
        "vendor": "\\Pllano\\Hooks\\HookAll",
        "query": "all",
        "render": "all",
        "routers": 0,
        "resource": 0,
        "url": 0,
        "app": "site",
        "state": 0,
        "config": 0
      },
      "security": {
        "vendor": "\\Pllano\\Hooks\\Security",
        "query": "all",
        "render": "all",
        "routers": 0,
        "resource": 0,
        "url": 0,
        "app": "site",
        "state": 0,
        "config": 0
      }
    }
  }
}
```
## Каркасы для создания пользовательских Hooks
В комплекте идет два каркаса для разработки. Сохраните и доработайте один из этих классов в зависимости от необходимости.
- Класс [`HookIndexGet`](https://github.com/pllano/hooks/blob/master/src/HookIndexGet.php) - обрабатывает только `GET` запросы. По умолчанию должен заменить шаблон `render` на `hooks.html` таким образом вы сможете проверить что Hooks работает.
- Класс [`HookAll`](https://github.com/pllano/hooks/blob/master/src/HookAll.php) - обрабатывает все запросы 
## Использование `GET`
```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Pllano\Hooks\Hook;
 
$app->get('/', function (Request $request, Response $response, array $args) {
    // Передать конфигурацию в конструктор
    $param = [];
    $query = 'GET';
    $app = 'site';
    $routers = null;
    // Если передать пустой массив [] возмет конфигурацию из файла hooks.json
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook($param);
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
    $param = [];
    // Если передать пустой массив [] возмет конфигурацию из файла hooks.json
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook($param);
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

# Кеширование для вашего проекта
## Конфигурация
Передать конфигурацию можно двумя способами:
- Из фала [`hooks.json`](https://github.com/pllano/hooks/blob/master/src/hooks.json)
- Массивом в конструктор `$config = [];`
## Использование
```php
use Pllano\Hooks\Hook;
// Скоро :)
```
## Установка
### Composer
```json
{
  "require": {
    "pllano/hooks": "~1.0.1"
  }
}
```
### [AutoRequire](https://github.com/pllano/auto-require)
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

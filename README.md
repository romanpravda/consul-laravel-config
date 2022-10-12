# Библиотека для получения значений конфигурации из Consul

[![phpversion](https://img.shields.io/badge/php-7.1-blue?logo=PHP)](https://www.php.net/releases/7.1/en.php)

[//]: # ([![pipeline status]&#40;https://git.devspark.ru/coursometer/consul-laravel-config/badges/main/pipeline.svg&#41;]&#40;https://git.devspark.ru/coursometer/reports-proxy/-/commits/main&#41;)
[//]: # ([![coverage report]&#40;https://git.devspark.ru/coursometer/consul-laravel-config/badges/main/coverage.svg&#41;]&#40;https://git.devspark.ru/coursometer/reports-proxy/-/commits/main&#41;)

## О библиотеке

Данная библиотека позволяет перезаписывать (или добавлять новые) поля конфигурации приложения значениями из Consul

## Требования

* PHP 7.1
* Laravel 5.7.*
* Consul >= 0.9

## Инструкция по подключению

Выполнить команду ```composer require romanpravda/consul-laravel-config```. Параметры подключения к Consul задаются через
параметры окружения, что указаны ниже.

## Параметры окружения

| Название параметра            | Описание                           | Значение по умолчанию |
|-------------------------------|------------------------------------|-----------------------|
| CONSUL_ENABLED                | Флаг работы библиотеки             | `false`               |
| CONSUL_ADDRESS                | Адрес сервера Consul               | `server.consul.local` |
| CONSUL_PORT                   | Порт сервера Consul                | `8500`                |
| CONSUL_KEY_PREFIX             | Префикс ключей в Consul            | `null`                |
| CONSUL_USERNAME               | Имя пользователя сервера Consul    | `null`                |
| CONSUL_PASSWORD               | Пароль пользователя сервера Consul | `null`                |
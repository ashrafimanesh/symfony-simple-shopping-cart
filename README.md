Symfony Shop
============

This repository is a simple Shopping Cart with Symfony 5 

Requirements
------------

- PHP 7.2.5+
- [Composer](https://getcomposer.org/download)
- [Symfony CLI](https://symfony.com/download)
- [Docker & Docker compose](https://docs.docker.com/get-docker)

Getting started
---------------


**Installing dependencies**

```
$ composer install
```

**Starting Docker Compose(Optional)**

```
$ docker-compose up -d
```

**Loading fake Products and Users**

```
$ symfony console doctrine:fixtures:load
```

**Launching the Local Web Server**

```
$ symfony server:start -d
```

The server started on the port 8000. Open the website http://localhost:8000 in a browser.

**Users**

You can login with username: `user1` and password: `123`
# esc-command

Command Init Repository and Service

## Installation

Require this package with composer

```shell
composer require gmo-ecs/ecs-command
```

Copy base repository after installation

```shell
php artisan vendor:publish --tag="base-repository"
```

## Repository

1. Artisan command

```shell
php artisan make:repository TestRepository
```

2. Specify model for repository

```shell
php artisan make:repository TestRepository --model=Test
```

## Service

1. Artisan command

```shell
php artisan make:service TestService
```

2. Specify repository for service

```shell
php artisan make:service TestService --repository=TestRepository
```

3. Specify model for repository when create service

```shell
php artisan make:service TestService --repository=TestRepository --model=Test
```

## Controller custom when exists option --repository

1. Specify repository for controller
```shell
php artisan make:controller TestController --repository=TestRepository
```

2. Specify model for repository when create controller

```shell
php artisan make:controller TestController --repository=TestRepository --model=Test
```

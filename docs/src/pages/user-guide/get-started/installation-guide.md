# Modularity Setup
This document will discuss about installation and required configurations for installation of the package.

## Pre-requisites
The modules package requires **PHP XXX** or higher and also requires **Laravel 10** or higher.

## Creating a Laravel Project and Implementing Modularity

1. **Create a Default Laravel Project**
Using Composer build a default Laravel project to your preferred direction
```sh
$ composer create-project laravel/laravel project-name

```

2.  **Intalling Modularity**

To install via Composer, run the following shell command:
```sh
$ composer require unusualify/modularity
```
The package will automatically register a service provider and alias.
<br/><br/>

2. **Environment File Configuration**

Configuration for many variable is must to construct your Vue & Laravel app with your project configuration.

- Application Configuration
  
```sh
APP_NAME=YOUR_APP_NAME
APP_ENV=local


APP_URL=YOUR_APP_CLIENT_URL
ADMIN_APP_URL=YOUR_APP_ADMIN_PANEL_URL
ADMIN_APP_PATH=admin //admin.yourdomain.test
ADMIN_ROUTE_NAME_PREFIX=admin 
```

- Database Configuration
```sh
DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=YOUR_DB_NAME
DB_USERNAME=root
DB_PASSWORD=root
```

```sh
MEDIA_LIBRARY_ENDPOINT_TYPE=local
MEDIA_LIBRARY_IMAGE_SERVICE=Unusualify\Modularity\Services\MediaLibrary\Local
MEDIA_LIBRARY_LOCAL_PATH=uploads
```


3. **Set-up Admin Panel And Configurations**

Now you can set-up modularity and backend project with using unusual commands:
```sh
$ php artisan unusual:install
```
You will be greeted with a few simple configuration questions:

```
         Making required migrations

   WARN  The database 'documentory-app' does not exist on the 'mysql' connection.  

 ┌ Would you like to create it? ────────────────────────────────┐
 │ ○ Yes / ● No                                                 │
 └──────────────────────────────────────────────────────────────┘
```

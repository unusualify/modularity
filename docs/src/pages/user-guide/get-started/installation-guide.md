# Modularity Setup
This document will discuss about installation and required configurations for installation of the package.

## Pre-requisites
The modules package requires **PHP XXX** or higher and also requires **Laravel 10** or higher.

## Creating a Modulariy Project

1. **Create a Default Laravel Project**
Using Composer build a default Laravel project to your preferred direction
```sh
$ composer create-project laravel/laravel project-name

```

2.  **Intalling Modularity**

After creating a default Laravel project, cd to your project folder
```sh
$ cd your-project-folder
```
To install Modularity via Composer, run the following shell command:
```sh
$ composer require unusualify/modularity
```
The package will automatically register a service provider and alias.
<br/><br/>

## Environment File Configuration

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

```sh
MEDIA_LIBRARY_ENDPOINT_TYPE=local
MEDIA_LIBRARY_IMAGE_SERVICE=Unusualify\Modularity\Services\MediaLibrary\Local
MEDIA_LIBRARY_LOCAL_PATH=uploads
```


## Installation Wizard
Modularity ships with a command line installation wizard that will help on scaffolding a basic project. After installation via Composer, wizard can be started by running:
```sh
$ php artisan unusual:install
```
Wizard will be processing with simple questions to construct projects core configurations 
```
Installment process consists of two(2) main operations.
    1. Publishing Config Files: Modularity Config files manages heavily table names, jwt configurations and etc.User should customize them after publishing in order to customize table names and other opeartions
    2. Database Operations and Creating Super Admin. DO NOT select this option if you have not published vendor files to theproject. This option will only dealing with db operations
    3. Complete Installment with default configurations (√ suggested)
                 

 ┌ Select Operation ────────────────────────────────────────────┐
 │   ○ Only Vendor Publish ( Config Files, Assets and Views)    │
 │   ○ Only Database Operations                                 │
 │ › ● Complete Installment with defaults                       │
 └──────────────────────────────────────────────────────────────┘
```
::: info Installation Options
A Modularity Project heavily depends on the configration files that will be published under your-project/config directory. Modularity comes with a series of default configuration, however they can be customized before Database Operations

:::

::: tip Customization
This page will be continue with the complete installment option with the default configrations. See [Config Customization](./) to inspect other options 
:::

Starting installation with the `Complete Installment` option will,
- Create database tables for required system modules
- Deal with the migrations
- Seed default data for the system modules
automatically after publishing default assets, views and configuration files to your project. 

For the last step, intallation process includes creating a super-admin account
```
         Creating super-admin account

 E-mail configuration for super-admin account

 ┌ Do you want to use default configuration for super-admin e-mail? ┐
 │ ● YES / ○ No, enter custom e-mail                                │
 └──────────────────────────────────────────────────────────────────┘
  Default e-mail address: software-dev@unusualgrowth.com

Password configuration for super-admin account

 ┌ Do you want to use default configuration for super-admin password? ┐
 │ ● YES / ○ No, enter custom password                                │
 └────────────────────────────────────────────────────────────────────┘
  Default password is w@123456

```
::: info
You can either select the default settings or type your custom e-mail and password to reach your backend panel application.
:::

::: details Creating Super Admin
Creating one or more super-admin account with custom e-mail and password is avaliable. See [Creating Super Admin]('\')
:::

## File Structure
A `Modularity Module` is similar to a Laravel package. It has its own, configs, controllers, migrations and etc. This file structure aims to writing modular applications and have more organized project to work with. 

Assuming installment is done and a test module `Testify` is created
```
.
├─Modules
|   └─.keep
|   ├─ Testify
|       ├─ Config
|           └─ config.php
|       ├─ Database
|           ├─ factories
|           ├─ Migrations
|           ├─ Seeders
|       ├─ Entities
|           ├─ Slugs
|           └─ *.php (Entities)
|       ├─ Http
|           ├─ Controllers
|           ├─ Middleware
|           ├─ Requests
|       ├─ Providers
|       ├─ Repositories
|       ├─ Resources
|           ├─ assets
|           ├─ lang
|           ├─ views
|       ├─ Routes
|       ├─ Tests
|       ├─ Transformers
|       └─ composer.json
|       └─ module.json
|       └─ routes_statuses.json*
|       └─ .keep
├─app\
├─bootstrap\
├─..default laravel folders
|
|
└─ .env
```

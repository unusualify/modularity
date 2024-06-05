# Modularity Setup
This document will discuss about installation and required configurations for installation of the package.

## Pre-requisites
The modules package requires **PHP XXX** or higher and also requires **Laravel 10** or higher.

## Creating a Modularity Project

### Using Modularity-Laravel Boilerplate

Modularity provides a Laravel boilerplate that all the pre-required files such as config files, environment file and etc published, and the folder structure is built as Modularity does. In order to create a modularity-laravel project following ``shell``  command can be used:

After `cd` to your preferred directory for your project,

```sh
$ composer create-project unusualify/modularity-laravel your-project-name
```
::: tip
After the setup is done, you can customize the config files and follow the intallation steps with `Only Database Operations`. Please proceed with 
[Installation Wizard](#installation-wizard)
:::

### Using Default Laravel Project

1.  **Intalling Modularity**

After creating a default Laravel project, cd to your project folder
```sh
$ cd your-project-folder
```
To install Modularity via Composer, run the following shell command:
```sh
$ composer require unusualify/modularity
```
After the installation of the package is done run:
```sh
$ php artisan vendor:publish --provider='Unusualify\\Modularity\\LaravelServiceProvider'
```
This will publish the package's configuration files
<br/><br/>


## Environment File Configuration

::: warning
Configuration for many variable is ``must`` to construct your Vue & Laravel app with your project configuration before [Installation](#installation-wizard)
:::



**Administration Application Configuration**
```sh
ADMIN_APP_URL=
ADMIN_APP_PATH=DESIRED_ADMIN_APP_PATH
ADMIN_ROUTE_NAME_PREFIX=DESIRED_ADMIN_ROUTE_NAME_PREFIX
```
As mentioned, modularity aims to construct your administration panel user interface while you building your project's backend application. Given key-value pairs corresponds to 
* Your administration panel domain name
* Your admin route path as ``'yourdomain.com/admin'`` if ``ADMIN_APP_URL`` key is not set
* Your route naming prefixes for administration routes like `admin.password`

**Database Configuration**
```sh
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=YOUR_DB_NAME
DB_USERNAME=root
DB_PASSWORD=
```
The default Laravel database environment configuration must be done before installation. You should create your empty DB with the customized DB name.

**Laravel Development Variables**
```sh
# Laravel Development Variables
MEDIA_LIBRARY_ENDPOINT_TYPE=local
MEDIA_LIBRARY_IMAGE_SERVICE=Unusualify\Modularity\Services\MediaLibrary\Local
MEDIA_LIBRARY_LOCAL_PATH=uploads
```
Shown key-value pairs is aims to point out the media library constructed in the `Modularity` package. For now, they are not customizable.

```sh
ACTIVITY_LOGGER_DB_CONNECTION=mysql
```
Default system logger configuration. Again, it is not customizable for now.
```sh
DEFAULT_USER_PASSWORD=DESIRED_DEFAULT_USER_PASSWORD
```
You can set your client-users default password. It will be set as fallback password if its not set while creating user.

**Vue Development Variables**
```sh
VUE_APP_THEME=unusual
VUE_APP_LOCALE=tr
VUE_APP_FALLBACK_LOCALE=en
VUE_DEV_PORT=5173
VUE_DEV_HOST=localhost
VUE_DEV_PROXY=
```
Admin panel application user interface is highly customizable through module configs. Also you can create your own custom `Vue` components in order to use in user interface. For further information see [Vue Component Sayfası] . In summary,
* A custom theme can be constructed, its name should be defined with `VUE_APP_THEME`
* Vue app locale language and fallback language should be setted
* Vue dev port should be setted, can be same as the locale port
* Vue dev host can be your domain-name like `mytestapp.com`
* Proxy should be setted if it is in undergo like `http://nginx`
  

::: tip
You can do further custom configuration through ``config`` files which are stored in the `config` directory. See [Configs] 
:::


## Installation Wizard
Modularity ships with a command line installation wizard that will help on scaffolding a basic project. After installation via Composer, wizard can be started by running:
```sh
$ php artisan unusual:install
```
Wizard will be processing with simple questions to construct projects core configurations.
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
This page will be continue with the complete installment option with the default configrations. See [Config Customization] to inspect other options 
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
Creating one or more super-admin account with custom e-mail and password is avaliable. See [Creating Super Admin]
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

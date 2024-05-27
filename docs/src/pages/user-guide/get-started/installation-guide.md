# Modularity Setup
This document will discuss about installation and required configurations for installation of the package.

## Pre-requisites
The modules package requires **PHP XXX** or higher and also requires **Laravel 10** or higher.

## Composer install and Environment File

1.  **Using Composer**

To install through Composer, run the following shell command:
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
APP_KEY=base64:PsLSkNd8HT0bdXe8jopiGU0Da0L01DcE2CVDAauyGSk=
APP_DEBUG=true
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
DB_DATABASE=jakomeet-panel
DB_USERNAME=root
DB_PASSWORD=root
```

3. **Installing the** 

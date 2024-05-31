---
# https://vitepress.dev/reference/default-theme-home-page
layout: doc

prev:
  text: 'Introduction'
  link: '../creating-modules/introduction'



---
# Creating a Module

Creating a plain module is simple and straightforward.

```sh
$ php artisan unusual:make:module YourModuleName --plain
```
Running this command will create the module with empty module structure with a config.php file where you can configure and customize your module's user interface, CRUD form schema and etc.

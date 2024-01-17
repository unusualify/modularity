```
    php artisan vendor:publish --provider="Unusualify\Modularity\LaravelServiceProvider" --tag="config"
    
    php artisan unusual:build --noInstall --hot

    php artisan unusual:make:module Package 
        -TP 
        --schema="name:string:unique,type:enum('type'\,['POS'\,'SERVICE'])" 
        --rules="name=required|min:3|unique:payments&type=in:POS,SOCIAL"
    
    php artisan unusual:make:route Invoice Payment
        --schema="name:string:unique,type:enum('type'\,['POS'\,'SERVICE'])" 
        --rules="name=required|min:3|unique:payments&type=in:POS,SOCIAL"

    php artisan unusual:make:route Invoice Payment 
        --schema="basic_name:string:unique,payment_id:foreignId:constrained:onUpdate('cascade'):onDelete('cascade'),soft_delete,remember_token"
    
    php artisan unusual:make:route Invoice Payment 
        --schema="basic_name:string:unique,belongsTo:payment:id:payments:constrained:onUpdate('cascade'):onDelete('cascade'),soft_delete,remember_token"

    php artisan unusual:make:controller Payment Invoice

    php artisan unusual:make:model Payment Reference 
        --soft-delete --has-factory
    php artisan unusual:make:model Payment Invoice 
        --relationships="belongsTo:payments:payment_id:id,hasMany:users:user_id:id"

    php artisan unusual:make:migration Payment create_invoices_table 
        --fields="name:string:unique,payment_id:foreignId:constrained:onUpdate('cascade'):onDelete('cascade'),soft_delete,remember_token"

    php artisan unusual:make:repository Payment Reference -TMP

    php artisan unusual:make:request Payment Payment --rules="name=required|min:3|unique:payments&email=required|email|unique:payments"

    php artisan migrate --path="Modules/PressRelease/Database/Migrations"
    php artisan migrate:rollback --path="Modules/PressRelease/Database/Migrations"
    php artisan migrate:refresh --path="Modules/PressRelease/Database/Migrations"

    // MODULE MIGRATE OPERATIONS
    php artisan unusual:migrate Package
    php artisan unusual:migrate:rollback Package
    php artisan unusual:migrate:refresh Package

    php artisan iseed sp_roles

```


### REGEX

```
    Repository behaviour name change
    (['|\s|a-z])Conduct([A-Z]\w+)
    $1Handle$2
    ./crm_basic/Modules
```

### ERROR FIX COMMANDS
IF #locale column not found on imageables table
    php artisan migrate:refresh --path=vendor/unusualify/modularity/src/Database/Migrations/default/2023_05_09_000003_create_unusual_default_medias_tables.php

### REGEX FILTERS

from
    (?<=[Config::get\(|config\(])\s?'base\.
    (?<=[Config::get\(|config\(])\s?\\Illuminate\\Support\\Str::snake\(env\('UNUSUAL_BASE_NAME',\s?'Base'\)\)\s?\.\s?'\.
    (?<=[Config::get\(|config\(])\s?Str::snake\(env\('UNUSUAL_BASE_NAME',\s?'Base'\)\)\s?\.\s?'\.
    (?<=[Config::get\(|config\(])\s?getUnusualBaseKey\(\)\s?\.\s?'\.
to 
    \Illuminate\Support\Str::snake(env('UNUSUAL_BASE_NAME', 'Base')) . '.
    unusualBaseKey() . '.

from 
    ["'](base)(::[A-Za-z\$\->\.]*)["']
    (?<=")(base)(?=::[A-Za-z\$\->\.]*")
to
    "$1$2"
    "$BASE_KEY$2"
    "{$this->baseKey}$2"

for seeders
([0-9]{0,3}\s=>[\s|\n|\r\n]+)?array[\s]?\((.*) [
([\s|\n|\r\n]+)(\),)  $1],
([\s|\n|\r\n]+)('id'\s=>\s[0-9]{0,3},?)
([\s|\n|\r\n]+)('created_at'\s=>\s(.*),?)
([\s|\n|\r\n]+)('updated_at'\s=>\s(.*),?)

/**
    [$CONTROLLER_NAME::class, '$METHOD_NAME'] => '$CONTROLLER_NAME@$METHOD_NAME'
    \[([a-zA-Z]*)::class,\s'(.*)'\] => '$1@$2'
 */

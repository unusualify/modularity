```
    php artisan vendor:publish --provider="OoBook\CRM\Base\LaravelServiceProvider" --tag="config"
    
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

    PRESS_RELEASE 
    php artisan unusual:make:module Package --plain --no-migrate
    php artisan unusual:make:route Package PackageContinent --no-migrate
    php artisan unusual:make:route Package PackageRegion --schema="belongsTo:package_continent" --no-migrate
    php artisan unusual:make:route Package PackageCountry --schema="belongsTo:package_region" --no-migrate
    php artisan unusual:make:route Package PackageDistributionLanguage --schema="code:string --no-migrate -T

    php artisan unusual:make:route Package PackageFeature --schema="" --no-migrate
```


### REGEX

```
    Repository behaviour name change
    (['|\s|a-z])Conduct([A-Z]\w+)
    $1Handle$2
    ./crm_basic/Modules
```

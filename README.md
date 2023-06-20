# crm-base

# TODO
## B2press menu sidebar
### admin
    Dashboard
    Manage Press Releases
    Users
    Credits & Payments
    Packages & Prices
        Continents
        Regions
        Countries
        Packages
    Announcements
    Support
    Settings

### client
    My Account
        Dashboard
        Profile
        Credits & Invoices
    Submit Press Release
    Manage Press Release
    PR Packages & Prices
    Support


## PressRelease Module
    Model
        id
        date date
        region -> Region
        package_id -> Package
        user_id -> User
        price
        pr_headline
        status bool
        revision bool
        actions [download report]

## User Module
    Model
        ...
        company_id
        role_id (owner, representative)
        status
## Credits & Payments
    Model
        ...
        company_id
        role_id (owner, representative)
        status

## Company
    Model
        id
        title
## Packages & Prices
### Continents
    Model
        id
        title
        description
    
    ADD NEW
### Regions
    Model
        id
        title
        description
        continent_id
    
    ADD NEW
### Countries
    Model
        id
        title
        description
        region_id
    
    ADD NEW

### Packages
    Model
        id
        title
        description
        country_id
    
    ADD NEW

### PackagePrice
    id
    package_id
    package_currency_price_id
    price
### PackagePriceCurrency
    id
    title
    description
### PackageDistributionLanguage
    package_id
    distribution_language_id
### DistributionLanguage
    id
    title
    code

## Announcement


## Support

### SupportTicket
    id
    user_id
    subject
    description
    file
    status
    created_at
    updated_at

## Dashboard
### Client
    At A Glance
        pending approval:
        unapproved:
        approved:
        published:

        account type: pre-paid
        packages: 
        credits:
    Recent Revisions
        |manage releases|
    Recently published
        date pr_headline region
        |create Press Release|
    News
    



### REGEX FILTERS

from
    (?<=[Config::get\(|config\(])\s?'base\.
    (?<=[Config::get\(|config\(])\s?\\Illuminate\\Support\\Str::snake\(env\('BASE_NAME',\s?'Base'\)\)\s?\.\s?'\.
    (?<=[Config::get\(|config\(])\s?Str::snake\(env\('BASE_NAME',\s?'Base'\)\)\s?\.\s?'\.
    (?<=[Config::get\(|config\(])\s?getUnusualBaseKey\(\)\s?\.\s?'\.
to 
    \Illuminate\Support\Str::snake(env('BASE_NAME', 'Base')) . '.
    getUnusualBaseKey() . '.

from 
    ["'](base)(::[A-Za-z\$\->\.]*)["']
    (?<=")(base)(?=::[A-Za-z\$\->\.]*")
to
    "$1$2"
    "$BASE_KEY$2"
    "{$this->baseKey}$2"


# Environment

Vuetify Version: 3.3.1
Last Working Version: 3.3.0
Vue Version: 3.3.4
OS: Docker Container
Node Version: 18.14.2
Package Manager: npm@9.5.0
Compiler: vue-cli
@vue/cli Version: 5.0.8
webpack Version: 5.84.1

# Steps to reproduce
1. using vuetifyPlugin in vue.config for changing sass-variables
```
    new VuetifyPlugin({
        styles: {
            configFile: 'src/sass/themes/' + APP_THEME + '/_settings.scss'
        }
    }),
```
2. importing ./src/js/plugins/vuetify.js in a config file
```
    import { createVuetify } from 'vuetify'
    ...

    import 'styles/themes/_main.scss' // 'vuetify/styles' being imported inside this file

    ...

    import * as components from 'vuetify/lib/components'
    import * as directives from 'vuetify/lib/directives'
```
3. 
```
    *** 'styles/themes/_main.scss' file ***
    @import
        url('https://fonts.googleapis.com/css?family=Montserrat:200,400,600,800,900'),
        'abstract/variables',
        'vuetify/styles',
        ...
    *** ****
```

# Expected Behavior
It should have been compiled successfully, but the compiler occurred this error especially in 3.3.1. This isn't about npm cache or any npm issue. Mostly, I retried solution advices, but when I have downgraded to 3.3.0, it doesn't give any error. It works, just as it should be.

While I was checking out your last commits, I have realized you to add exports entry for moduleResolution=bundler. commit "89ac54c". That might be the problem.
# Actual Behavior

ERROR in ./src/js/plugins/vuetify.js 8:0-40
Module not found: Error: Default condition should be last one
Did you mean './vuetify'?
Requests that should resolve in the current directory need to start with './'.
Requests that start with a name are treated as module requests and resolve within module directories (node_modules, {_root}/{_custom_path}/vue/node_modules, {_root}/{_custom_path}/vue/node_modules/@vue/cli-service/node_modules, {_root}/node_modules).
If changing the source code is not an option there is also a resolve options called 'preferRelative' which tries to resolve these kind of requests in the current directory too.

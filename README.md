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
        name
## Packages & Prices
### Continents
    Model
        id
        name
        description
    
    ADD NEW
### Regions
    Model
        id
        name
        description
        continent_id
    
    ADD NEW
### Countries
    Model
        id
        name
        description
        region_id
    
    ADD NEW

### Packages
    Model
        id
        name
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
    name
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
    unusualBaseKey() . '.

from 
    ["'](base)(::[A-Za-z\$\->\.]*)["']
    (?<=")(base)(?=::[A-Za-z\$\->\.]*")
to
    "$1$2"
    "$BASE_KEY$2"
    "{$this->baseKey}$2"


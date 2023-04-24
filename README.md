# crm-base


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
    $this->baseKey

PunyApp
=======

[![Build Status](https://travis-ci.org/polygonplanet/PunyApp.svg?branch=master)](https://travis-ci.org/polygonplanet/PunyApp)

PunyApp is a lightweight MVC PHP framework.  
PunyApp requires PHP 5.2.0 or newer.  
It does not require the external PHP extensions.  


### Supported Databases

* MySQL
* SQLite
* Posql *[GitHub](https://github.com/polygonplanet/Posql) [(English)](http://feel.happy.nu/doc/posql/en/)


### Tutorial

#### layout

Application directory layout:  

    /app                  -> Application
      /controllers        -> App controllers
      /models             -> App models
      /views              -> App views
      /storage            -> A storage of the filebase database
      /settings           -> Application settings
        app-settings.json
        app-scheme.php
      /public             -> public web
        /css
        /js
        index.php
    /lib                  -> PunyApp library
    /vendors              -> Venders
    .htaccess
    index.php
 

### Settings

* app/settings/app-settings.json

```javascript
{
  "system": {
    "debug": true, // debug mode
    "lang": "ja", // or "en" etc.
    "charset": "utf-8",
    "timezone": "Asia/Tokyo", // or 'America/Chicago' etc.
    // Application security salt
    // Enter something characters. symbols is possible.
    "salt": "ZQJaiPPYn6Tldb2gottKwIDmGiatuSnV"
  },
  "database": {
    "engine": "sqlite", // or "mysql", "posql"
    "encoding": "utf8",
    "user": "",
    "pass": "",
    "dbname": "database_name",
    "host": "localhost",
    "port": ""
  },
  "session": {
    "engine": "sqlite", // or "mysql", "posql"
    "name": "sessid", // PHPSESSID
    "expirationDate": 365
  }
}
```

For SQLite, set to writable following files.  

 * app/storage/databases/app-database.sqlite
 * app/storage/sessions/app-session.sqlite

### Sample

* There is a sample login form in `/sample/`.

----

#### Use Posql

Posql documents [(Japanese)](http://feel.happy.nu/doc/posql/) [(English)](http://feel.happy.nu/doc/posql/en/)  

1. [Download](https://github.com/polygonplanet/Posql/tree/master/posql-2.18a) latest `posql.php`

2. Put `posql.php` in `/vendors` directory.
3. Requires posql in `app/settings/app-initialize.php`

```php
PunyApp::uses('posql', 'vendors');
```

4. Settings `app/settings/app-settings.json`  

```javascript
{
  ...
  "database": {
    "engine": "posql",
    ...
  },
  "session": {
    "engine": "posql",
    ...
  }
}
```

After accessed to the application `/sample/`, set to writable following files.  

 * app/storage/databases/app-database.posql.php
 * app/storage/sessions/app-session.posql.php

#### posqladmin

1. Download latest posqladmin [(Japanese)](https://github.com/polygonplanet/Posql/tree/master/posql-2.18a) [(English)](http://feel.happy.nu/doc/posql/en/)
2. Put `posqladmin.php` in public directory.
3. Set to writable `posqladmin.php` (e.g., `0666` permissions).
4. Access `posqladmin.php`.
5. Set id and pass in settings.

### License

The PunyApp is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)



PunyApp
=======

[![Build Status](https://travis-ci.org/polygonplanet/PunyApp.svg?branch=master)](https://travis-ci.org/polygonplanet/PunyApp)

PunyApp is a lightweight MVC PHP framework.  
PunyApp requires PHP 5.2.0 or newer.  
It does not require the external PHP extensions.  

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
    "engine": "sqlite", // or "mysql"
    "encoding": "utf8",
    "user": "",
    "pass": "",
    "dbname": "database_name",
    "host": "localhost",
    "port": ""
  },
  "session": {
    "engine": "sqlite", // or "mysql"
    "name": "sessid", // PHPSESSID
    "expirationDate": 365
  }
}
```

For SQLite, set to writable following files.  

 * app/storage/databases/app-database.sqlite
 * app/storage/sessions/app-session.sqlite

### Sample

There is a sample login form in `/sample/`.

### License

The PunyApp is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)


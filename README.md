Great escape @ Museomix 2015
============================

Technology
----------
- PHP5.5 (Silex framework)
- SCSS
- Gulp
- jQuery

Setup
-----

### Requirements
- Curl
- PHP5.5
- PHP5-curl
- Gulp

Get the codebase
```
git clone https://github.com/MuseomixCH/2015-Team-3.git
```

Create the config file and adapt it.
```
cp PATH_TO_YOUR_PROJECT/2015-Team-3/config/parameters.yml.dist PATH_TO_YOUR_PROJECT/2015-Team-3/config/parameters
```

Install vendors
```
cd 2015-Team-3
composer install
npm install
```

Webserver
```
<VirtualHost *:80>
    ServerName mu.lo
    DocumentRoot "PATH_TO_YOUR_PROJECT/2015-Team-3/web"

    <Directory "PATH_TO_YOUR_PROJECT/2015-Team-3/web" >
        DirectoryIndex index.php
        AllowOverride All
        Options All
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
```

Debugging
---------

Uncomment the line below in _web/index.php_
```
$app['debug'] = true;
error_reporting(E_ALL);
ini_set('display_errors','On');
```

Authors
-------

- Maker: Mariana Rihl
- Design: Caroline Bossy
- Facilitator: Claudia Stübi
- Content: Christian Rohner, Elke Schimanski
- Communication: Philipp Burkard
- Code: Daniel Huf, Geoffroy Perriard

License
-------
[CC-BY-SA](https://creativecommons.org/licenses/by-sa/4.0/)


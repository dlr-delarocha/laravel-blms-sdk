# Laravel-blms-sdk
This repository contains the open source PHP SDK that allows you to access the BLMS Platform from your Lumen/Laravel app.
##Lumen Configuration


### Install with composer

```sh
composer require delarocha/laravel-blms-sdk
```

#### Create blms.php file in config directory.
```
app
config
  blms.php
```


#### Add the following configuration array.    
```
return array(
    'user' => env('BLMS_USER'),
    'password' => env('BLMS_PASSWORD'),
    'domain' => env('BLMS_DOMAIN'),
);
```

#### Include blms config file file in boostrap/app   
```
$app->configure('blms');
```


#### Example
```
use BLMS\BLMS;

    $blms = new BLMS;
    $segments = $blms->getService()->get('/segments')->getItems();
```

License
----

MIT


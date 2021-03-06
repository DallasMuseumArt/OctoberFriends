DMA Friends
==============

DMA Friends is an open source plugin for [October CMS](http://octobercms.com) that encourages and recognizes visitor participation as an essential ingredient of the museum experience. 


# Installation (manual)

* Download and complete the installation for October CMS (http://octobercms.com)
* Install the Rainlab "User" Plugin
* Extract this repository into plugins/dma/friends
* In plugins/dma/friends folder run `composer install`. 
* Then run: `php artisan october:up`.
* Go to the [Friends Theme Repository](https://github.com/DallasMuseumArt/OctoberFriendsTheme) for instructions on downloading and installing the companion theme.

### Installing Rainlab plugin:
Log into to the OctoberCMS backend (default http://HOSTNAME/backend) site.
Click Settings.
Select System / Updates from the left menu.
Type rainlab.user and click Install Plugin on this page.

# Installation via Makefile 
* Download the makefile in this repository installer/Makefile (e.g. curl -O https://raw.githubusercontent.com/artsmia/OctoberFriends/master/installer/Makefile)
* Uncomment and set the lines for your Github username and the OctoberCMS database details.
* Run `make install` in your chosen install directory.

# REST API

The DMA Friends pluging also provide a REST API to allow build custom applications that can interact with the platform, for futher information about all available endpoints can be found at *plugins/dma/friends/docs/api-docs* folder.

### Enable REST API

Since version 2.6.1	 the REST API requires pass an autentication token in almost all available endpoints of the API. For that reason it is required to follow the below steps to configure the REST API.

* Create a config.php file for the Friends pluging at `<octobercms root>/config/dma/friends/`.  
  *Note: If folder structure don't exist create is manually*

* Add to the config.php file a secret key that is used to sign and authenticated all calls to the API. The config.php file show look like the below example.

```
<?php

return [
    'secret' => 'PLACE-HERE-A-NICE-LONG-SECRET-KEY'
];

```

* Log into the OctoberCMS backend.
* Create an Application API at http://your.domain/backend/dma/friends/applications/
* Configure the level of access your custom Application require.
* Use the Application Key to authenticate or register users in the platform.
 

# Documentation

[Developing custom activity types](docs/ACTIVITY-TYPES.md)

[Using custom events](docs/EVENTS.md) 

PHP API Documentation is available at [http://developer.dma.org/](http://developer.dma.org)

OctoberCMS Documentation is available at [https://octobercms.com/docs/](https://octobercms.com/docs/)


### (LEGACY) Wordpress Migrations

If you are migrating from a wordpress/badgeos installation of friends you will also
need to provide database configuration in order to migrate your data

* edit apps/config/database.php and add the following
<pre>
        'friends_wordpress' => array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => '', 
            'database'  => 'WORDPRESS_FRIENDS_DB',
            'username'  => 'WORDPRESS_USER',
            'password'  => 'WORDPRESS_PASS',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '', 
        ), 
</pre>
Substituting the appropriate database, user, and password

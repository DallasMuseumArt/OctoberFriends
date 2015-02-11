DMA Friends
==============

DMA Friends is an open source plugin for [October CMS](http://octobercms.com) that encourages and recognizes visitor participation as an essential ingredient of the museum experience. 


### NOTE
The friends plugin is currently designed to work with builds no later then 186 of October CMS

# Installation

* Download and complete the installation for October CMS (http://octobercms.com)
* Install the Rainlab "User" Plugin
* Extract this repository into plugins/dma/friends
* In plugins/dma/friends folder run `composer install`. 
* Then run: `php artisan october:up`.
* Go to the [Friends Theme Repository](https://github.com/DallasMuseumArt/OctoberFriendsTheme) for instructions on downloading and installing the companion theme.

# Documentation

[Developing custom activity types](docs/ACTIVITY-TYPES.md)

[Using custom events](docs/EVENTS.md) 

PHP API Documentation is available at [http://developer.dma.org/friends/](http://developer.dma.org/friends/)

OctoberCMS Documentation is available at [https://octobercms.com/docs/](https://octobercms.com/docs/)

### Installing Rainlab plugin:
Log into to the OctoberCMS backend (default http://HOSTNAME/backent) site.
Click Settings.
Select System / Updates from the left menu.
Type rainlab.user and click Install Plugin on this page.


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

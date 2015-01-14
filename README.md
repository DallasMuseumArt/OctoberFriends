OctoberFriends
==============

Friends is an open source plugin based on OctoberCMS that encourages and recognizes visitor participation 
as an essential ingredient of the museum experience. 
Currently, participants must enroll at kiosks in the Museum, but plans are underway to add an online sign-up option.

# Installation

* Download and complete the installation for October CMS (http://octobercms.com)
* Extract this repository into plugins/dma/friends
* In plugins/dma/friends folder run `composer install`. 
* Install the Rainlab "User" Plugin
* Enable the "Friends" Plugin

# Documentation

PHP API Documentation is available at [http://developer.dma.org/friends/](http://developer.dma.org/friends/)

OctoberCMS Documentation is available at [https://octobercms.com/docs/](https://octobercms.com/docs/)

## Wordpress Migrations

If you are migrating from a wordpress installation of friends you will also
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

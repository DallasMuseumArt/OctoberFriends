OctoberFriends
==============

An OctoberCMS implementation of friends

# Installation

* Install the Rainlab "User" Plugin
* Enable the "Friends" Plugin

## Wordpress Migrations

If you are migrating from a wordpress installation of friends you will also
need to provide database configuration in order to migrate your data

* Download and complete the installation for October CMS (http://octobercms.com)
* Extract this repository into plugins/dma/friends
* In plugins/dma/friends folder run `composer install`. 
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

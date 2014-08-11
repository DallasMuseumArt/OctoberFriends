OctoberFriends
==============

An OctoberCMS implementation of friends

# Installation

* Download and complete the installation for October CMS (http://octobercms.com)
* Extract this repository into plugins/dma/friends
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

* Install the Rainlab "User" Plugin
* Enable the "Friends" Plugin

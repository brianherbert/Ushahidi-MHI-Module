Ushahidi-MHI-Module
===================

The module Crowdmap Classic uses to connect visitors to their appropriate database using the Ushahidi v2 platform.

**This is not supported by Ushahidi and is untested.**

There's actually a very good chance that this will not work on your first try since I copied this and stripped out a bunch of seemingly unnecessary code without trying it out on my own. You are welcome to patch it up and submit pull requests to help the next person wanting to set something up.

Installation
============

What we do on Crowdmap Classic is spin up a separate database for each deployment. It's a bit of a pain to update the schema but it's not a big deal, especially if you only have a few maps. The core codebase even supports custom domains, so if you have the SSL certificates for each installed on your machine, you're in business.

If you want to replicate what we've done, you'll want to install a vanilla deployment of Ushahidi v2+. Go into the config and enable MHI. The setting is $config['enable_mhi']. MHI stands for Multiple Hosted Instances.

Set $config['enable_auto_upgrader'] to false. You will want to handle upgrades manually, otherwise your database schemas will end up out of sync.

Next, delete the database configs in your /application/config/ directory. MHI does not use this and leaving it there will cause problems.

Upload the /modules/mhi/ directory into your root directory of your vanilla install.

Upload the /media/mhi/ directory into your root directory of your vanilla install.

Upload the /application/config/mhi.php file to your deployment and add the domain name for you site here without the protocol. It will be something along the lines of "crowdmap.com". You can also add blocked subdomains here if you are using subdomains on your domain for other purposes or you want to reserve them for a later date. You can ignore the settings for "edition_subdomains".

Find the /modules/mhi/config/database.php file and add the database details for your vanilla Ushahidi install. Make sure the user you use has permission to create new databases. When someone sets up a new site, this user will be the one that creates a new database. It will use your vanilla install database name as a prefix and the subdomain used as the name. For example, if you have a db named "mhi" for this and someone creates a "coolmap" subdomain for a map, a new db called "mhi_coolmap" will be created.

Run the mhi.sql file on your vanilla database. The tables this installs will be the only relevant tables to the vanilla install. All the others can be ignored. If you want, you can do some Apache/Nginx fu to make sure people don't access the phantom Ushahidi deployment that this process creates.

Make sure your .htaccess file allows all subdomains to hit your site, otherwise you may have problems.

Want to use custom domains instead of subdomains? Point your domain at your server IP address using an A record. Then, in the database, go to the "mhi_site" table and add the domain to the "custom_domain" column. If your domain is https://helloyall.com, you would enter "helloyall.com". It's highly advised that you install SSL certificates for any domain you use on your server so you will be able to utilize SSL properly to protect your users.

Learn Something
===============

If you are just interested in the logic behind the routing and setting up of databases, check out the DBGenesis_Core class in /mhi/libraries/DBGenesis.php. If you want to roll your own setup, this might be a useful class to use to help you get started. You will also want to check out the /mhi/config/database.php file to see how the routing happens.

Important Notes
===============

Something to keep in mind is the fact that there is **no administrative interface** for managing these deployments. That is your responsibility to do on the database level.

File uploads for each deployment will happen in the /media/uploads/*subdomain_of_deployment*/ directory so proper write permissions need to be on that /media/uploads/ dir. The files written in the /application/cache,log,etc directories should be prefixed with the subdomain name.
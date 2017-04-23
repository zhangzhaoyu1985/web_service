# TagTalk Web Service

This repository contains files under /var/www/html folder. 
Currently two servers are running this files:
1) china server: ec2-54-223-152-54.cn-north-1.compute.amazonaws.com.cn
   DNS = cn.tagtalk.co
2) us server: ec2-50-18-207-106.us-west-1.compute.amazonaws.com
   DNS = www.tagtalk.co
   
The /var/www/html folder currently contains:
1) Tagtalk Landing page:
   When user hit www.tagtalk.co or cn.tagtalk.co. The entry point is index.html or index-en.html.
2) Other web service api files:
   /accounts - for activate accounts via email
   /uploads/uploads - user uploaded images.
   /uploads/copy_to_remote - uploaded images that should be synced to another server. A cron job that runs copy_to_remote_job.py will do the job.
3) /pics:
   The static images, for example winery images, wine images, etc.

=======================
/uploads folder setup
=======================
For every new server in the future, do following setup:
For every new server in the future, do following setup:

We need to setup uploads folder's permission so that remote server can copy files into it.
1. sudo usermod -a -G www-data ubuntu     -> make sure the user ubuntu is in www-data group
2. sudo chgrp -R www-data /var/www/html/uploads  -> make sure the owner of the folder is www-data instead of root
3. sudo chmod -R 775 /var/www/html/uploads   -> grant read write access to the group

Add a file in /home/ubuntu/tag_config, put in information for copy_to_remote_job.py to read.
Also copy the remote server's ssh key to the configed dir.

Try the ssh and scp command manually:
ssh -i "/home/ubuntu/tagtaglk_01_cn.pem" ubuntu@54.223.152.54
scp -q -i /home/ubuntu/tagtalk_01_cn.pem /var/www/html/uploads/copy_to_remote/* ubuntu@54.223.152.54:/var/www/html/uploads/uploads

Sometimes if the ssh key has changed, you need to remove the key in order for ssh to work:
ssh-keygen -f "/home/ubuntu/.ssh/known_hosts" -R 54.223.152.54

To setup the cron job that runs the copy_to_remote_job.py, modify crontab -e, add following line:
* * * * * /usr/bin/python /var/www/html/uploads/copy_to_remote_job.py
Add MAILTO="" at the top of crontab file, to disable cron job report every 1 min.

===========================================
Apache server setup - disable index browsing
===========================================
1. sudo vi /etc/apache2/apache2.conf 
2. Replace:
	<Directory /var/www/>
		Options Indexes FollowSymLinks
		AllowOverride None
		Require all granted
	</Directory>
	By:
	<Directory /var/www/>
	    DirectoryIndex index-en.html --------> change index to index-en for us server
        Options FollowSymLinks     ----------> disable index browsing
        AllowOverride All
        Require all granted
	</Directory>
3. sudo service apache2 restart

=======================================================
Apache server setup - setup mobile and desktop site
=======================================================
1. Take us site for example, first route both www.tagtalk.co and m.tagtalk.co to us server.
 (For cn site, we can use cn.tagtalk.co and mobile.tagtalk.co)
2. Then on us server, create separate conf file for www.tagtalk.co and m.tagtalk.co.
To route www.tagtalk.co to www/html/desktop, vi /etc/apache2/sites-available/www.tagtalk.co.conf:

<VirtualHost *:80>
	ServerAdmin webmaster@localhost
	ServerName www.tagtalk.co
	ServerAlias www.tagtalk.co
	DocumentRoot /var/www/html/desktop
	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

To route m.tagtalk.co to www/html/mobile, vi /etc/apache2/sites-available/m.tagtalk.co.conf:

<VirtualHost *:80>
	ServerAdmin webmaster@localhost
	ServerName m.tagtalk.co
	ServerAlias m.tagtalk.co
	DocumentRoot /var/www/html/mobile
	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

> sudo a2ensite m.tagtalk.co.conf
> sudo a2ensite www.tagtalk.co.conf
> sudo a2dissite api.tagtalk.co.conf  ---> remove any unused host, if there is any.
> sudo /etc/init.d/apache2 restart

3. Then re-route www.tagtalk.co to m.tagtalk.co when user is using mobile device:
  > a2enmod rewrite
  > /etc/init.d/apache2 restart
  
  Make sure we have some where (we have this already in /etc/apache2/apache2.conf )
	<Directory /var/www/>
	    ...
        AllowOverride All
		...
	</Directory>
	
  Then add reroute rule in vi /var/www/html/desktop/.htaccess:
  
	<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteCond %{HTTP_USER_AGENT} "android|blackberry|googlebot-mobile|iemobile|ipad|iphone|ipod|opera mobile|palmos|webos" [NC]
	RewriteRule ^$ http://m.example.com/ [L,R=302]
	</IfModule>
	
	DirectoryIndex index-en.html   -----------> for us site, use index-en.html

	Then restart:
	> /etc/init.d/apache2 restart
Listen 8000

<VirtualHost *:8000>
    LoadModule php5_module /home/ubuntu/.phpenv/versions/5.4.21/libexec/libphp5.so
    ServerName  test.vreasy.com
    ServerAlias www.test.vreasy.com
    ServerAdmin mauro@vreasy.com

    SetEnv APPLICATION_ENV "test"
    DocumentRoot "/home/ubuntu/vreasy/worldhomes/public/"

#    <FilesMatch "\.ph(p[2-6]?|tml)$">
#        SetHandler application/x-httpd-php
#    </FilesMatch>

    <Directory />
        Options FollowSymLinks
        AllowOverride None
    </Directory>
    <Directory /home/ubuntu/vreasy/public>
        Options FollowSymLinks MultiViews
        AllowOverride None
        Order allow,deny
        Allow from all

        RewriteEngine On

        #   Display default icon if none found
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_URI} /img/vicons/32x32px/.+_green.png
        RewriteRule ^.*$ img/vicons/32x32px/substitute_green.png [L]
        RewriteCond %{REQUEST_URI} /img/vicons/32x32px/.+_red.png
        RewriteRule ^.*$ img/vicons/32x32px/substitute_red.png [L]

        RewriteCond %{HTTP_HOST} ^vreasy\. [NC]
        RewriteRule (.*) http://www.%{HTTP_HOST}/$1 [L,R=301]

        RewriteCond %{HTTP_HOST} !^www\.vreasy\.com$ [NC]
        RewriteCond %{REQUEST_URI} /robots.txt [NC]
        RewriteRule (.*) /robots.off.txt [L]

        RewriteCond %{REQUEST_FILENAME} -s [OR]
        RewriteCond %{REQUEST_FILENAME} -l [OR]
        RewriteCond %{REQUEST_FILENAME} -d
        RewriteRule ^.*$ - [NC,L]
        RewriteRule ^.*$ index.php [NC,L]

        php_flag short_open_tag On
        php_flag xdebug.profiler_enable Off
        php_flag xdebug.profiler_enable_trigger Off
    </Directory>
</VirtualHost>

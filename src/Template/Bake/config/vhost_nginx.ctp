<%
/**
 * Cakebox-generated Nginx virtual host using generic template.
 */
%>
server {
    listen 80;
    server_name www.<%= $url %>;
    rewrite ^(.*) http://<%= $url %>$1 permanent;
}



server {
    listen 80;
    server_name <%= $url %>;

    # root directive should be global
    root <%= $webroot %>;
    index index.php;

    access_log /var/log/nginx/<%= $url %>.access.log logstash;
    error_log /var/log/nginx/<%= $url %>.error.log;

    location / {
        try_files $uri \$uri/ /index.php?\$args;
    }

    location ~ \.php$ {
        try_files $uri =404;
        include /etc/nginx/fastcgi_params;
        fastcgi_pass    unix:/var/run/php5-fpm.sock;
        fastcgi_index   index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # deny access to hidden
    location ~ /\. {
        deny all;
    }
}

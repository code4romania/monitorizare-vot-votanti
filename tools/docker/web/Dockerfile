FROM php:7.1-apache-stretch
#packages and php modules
RUN apt update
RUN apt install -f -y vim zlib1g-dev zip
RUN docker-php-ext-install pdo_mysql mbstring zip
RUN apt clean
#installing composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --filename=composer
RUN php -r "unlink('composer-setup.php');"
RUN mv composer /usr/bin
#apache mod_rewrite
RUN a2enmod rewrite
#apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
#php config
COPY config/custom-php-configs.ini $PHP_INI_DIR/conf.d/




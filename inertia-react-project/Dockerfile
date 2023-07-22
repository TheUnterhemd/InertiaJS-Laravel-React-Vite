# Verwende das offizielle PHP-Image als Basis
FROM php:8.2.4-apache

# Installiere die notwendigen PHP-Erweiterungen und andere Pakete, die du benötigst
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql zip

# Erstelle ein Verzeichnis für deine Laravel-Anwendung
RUN mkdir -p /var/www/html/laravel

# Setze das Arbeitsverzeichnis für den Container
WORKDIR /var/www/html/laravel

# Kopiere deine Laravel-Anwendung in den Container
COPY . .

# Installiere Composer und die PHP-Abhängigkeiten
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

# Kopiere deine Vhost-Konfiguration für Apache mit dem SSL-Zertifikat
COPY apache-vhost.conf /etc/apache2/sites-available/laravel.conf

# Aktiviere die Vhost-Konfiguration
RUN a2ensite laravel.conf

# Generiere ein selbstsigniertes SSL-Zertifikat
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/server.key -out /etc/ssl/certs/server.crt -subj "/C=US/ST=California/L=San Francisco/O=Local/OU=Local/CN=localhost"

# Aktiviere SSL-Modul
RUN a2enmod ssl

# Ändere den Besitzer des Laravel-Verzeichnisses auf den Apache-Benutzer und die -Gruppe
RUN chown -R www-data:www-data /var/www/html/laravel

# Ändere die Berechtigungen für den Speicher- und Cache-Ordner
RUN chmod -R 775 /var/www/html/laravel/storage
RUN chmod -R 775 /var/www/html/laravel/bootstrap/cache

# Starte den Apache-Server
CMD ["apache2-foreground"]
FROM httpd:latest

# Include the virtual host configuration file
COPY apache.conf /usr/local/apache2/conf/extra/httpd-vhosts.conf
RUN sed -i \
    -e 's/#Include\ conf\/extra\/httpd-vhosts.conf/Include\ conf\/extra\/httpd-vhosts.conf/' \
    /usr/local/apache2/conf/httpd.conf

# Enable Apache modules
RUN sed -i \
    -e '/#LoadModule alias_module/s/^#//g' \
    -e '/#LoadModule deflate_module/s/^#//g' \
    -e '/#LoadModule proxy_module/s/^#//g' \
    -e '/#LoadModule proxy_fcgi_module/s/^#//g' \
    -e '/#LoadModule rewrite_module/s/^#//g' \
    -e '/#LoadModule setenvif_module/s/^#//g' \
    /usr/local/apache2/conf/httpd.conf

# Create users, directories and update permissions
RUN groupadd app \
    && useradd -g app app \
    && mkdir -p /usr/local/apache2/logs \
    && chown -R app:app /usr/local/apache2/logs

# Change owner and group
USER app:app

# Change workdir
WORKDIR /var/www/public


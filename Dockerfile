FROM yiisoftware/yii2-php:7.3-apache
LABEL maintainer="an.andreas+dev@posteo.de"

COPY . /app
RUN chmod 777 /app/web/assets && \
    chmod 777 -R /app/runtime && \
    /usr/local/bin/composer install --prefer-dist && \
    /usr/local/bin/composer clear-cache
ENV PATH ./vendor/bin:${PATH}

ENV DB_DSN=mysql:host=localhost;port=3306;dbname=yii_db
ENV DB_USERNAME=root
ENV DB_PASSWORD=root

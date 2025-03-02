FROM php:8.3-cli
LABEL maintainer="support@wixiweb.fr"

# Met à jour le système et installe les dépendances
RUN set -xe \
    && apt update \
    && apt upgrade -y \
    && apt install -y --no-install-recommends \
      curl \
      git \
      gpg \
      openssh-client \
      rsync \
      unzip \
      libfreetype-dev \
      libjpeg62-turbo-dev \
      libpng-dev


# Installe les dépendances PHP
COPY --from=mlocati/php-extension-installer:2.7 /usr/bin/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions
RUN install-php-extensions \
    gd \
    pdo_mysql \
    pdo_pgsql \
    calendar \
    intl \
    bcmath \
    pcntl

# Installe Composer v2.8 de manière globale
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer


# Nettoie les fichiers systèmes inutiles après l'installation
RUN set -xe \
    && apt purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false \
    && apt clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

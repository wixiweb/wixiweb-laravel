#!/bin/bash

# Lancer les tests dans un conteneur éphémère
docker compose run --rm app composer test

# Arrêter tous les conteneurs après les tests
docker compose down

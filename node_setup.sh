#!/bin/sh

echo "-- Setting up node & updating nvm"

curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

nvm install
nvm use --delete-prefix --silent

# install local node packages with yarn
yarn install --frozen-lockfile

# as this is run on the server, we don't want to keep Cypress
echo "-- Remove Cypress"
rm -rf ~/.cache/Cypress

echo "-- node Setup Done"

#!/bin/sh

echo "-- Setting up node & updating nvm"

curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

nvm install
nvm use --delete-prefix --silent

# install local node packages with yarn
yarn install --frozen-lockfile

echo "-- node Setup Done"

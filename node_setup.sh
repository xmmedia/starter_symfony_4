#!/bin/sh

echo "-- Setting up node & updating nvm"

curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.8/install.sh | bash

# the installer only adds nvm to the shell profile, so load it into this shell
export NVM_DIR="$HOME/.nvm"
. "$NVM_DIR/nvm.sh"

nvm install
nvm use --delete-prefix --silent

# provides the yarn command, which runs the version in .yarn/releases
corepack enable

# install local node packages with yarn
yarn install --immutable

echo "-- node Setup Done"

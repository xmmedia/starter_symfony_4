#!/bin/sh

echo
echo '-- Setting up dev site --'
echo 'This script can be run multiple times without causing problems'
echo

echo "Paths:"
CURRENT_PATH=$PWD
if [[ $CURRENT_PATH = /chroot* ]]; then
    BASE="${CURRENT_PATH:7}";
else
    BASE=${CURRENT_PATH}
fi
RELEASES="$BASE/releases";
RELEASE="$RELEASES/1";
SHARED="$BASE/shared"
printf "Current:  ${PWD}\nRoot:     ${BASE}\nReleases: ${RELEASES}\nRelease:  ${RELEASE}\nShared:   ${SHARED}\n\n"

PHP_VERSION=$(php -v|grep --only-matching --perl-regexp "(PHP )\d+\.\\d+\.\\d+"|cut -c 5-7)
PHP_MINIMUM_VERSION=7.3
printf "Current PHP version: ${PHP_VERSION}\nMinimum PHP version: ${PHP_MINIMUM_VERSION}\n"
if [ $(echo "$PHP_VERSION >= $PHP_MINIMUM_VERSION" | bc) -eq 0 ]; then
    echo "************"
    echo "Will switch PHP version to ${PHP_MINIMUM_VERSION}"
    echo "************"
fi
echo

read -p "Are you in the domain dir, where the html dir is and the paths above are correct? (Y/n) " -n 1 -r
echo    # move to a new line after response
if [[ ! $REPLY =~ ^[Y]$ ]]; then
    echo
    echo 'Cancelled'
    echo
    [[ "$0" = "$BASH_SOURCE" ]] && exit 1 || return 1 # handle exits from shell or function but don't exit interactive shell
fi
printf "\n\n"

cd $BASE
echo "Working in: $PWD"
printf "\n\n"

echo "Install oh-my-zsh"
sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)" "" --unattended
printf "\nDISABLE_AUTO_TITLE=\"true\"" >> ~/.zshrc
printf "\n\nexport NVM_DIR=\"\$HOME/.nvm\"" >> ~/.zshrc
printf "\n[ -s \"\$NVM_DIR/nvm.sh\" ] && \. \"\$NVM_DIR/nvm.sh\"  # This loads nvm" >> ~/.zshrc
printf "\n[ -s \"\$NVM_DIR/bash_completion\" ] && \. \"\$NVM_DIR/bash_completion\"  # This loads nvm bash_completion" >> ~/.zshrc
printf "\n\n"

echo "Install nvm"
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.35.3/install.sh | bash
printf "\n\n"

if [ $(echo "$PHP_VERSION >= $PHP_MINIMUM_VERSION" | bc) -eq 0 ]; then
    echo "Switching PHP version to ${PHP_MINIMUM_VERSION}"
    printf "\nsource /opt/remi/php73/enable" >> ~/.zshrc
    printf "\nsource /opt/remi/php73/enable" >> ~/.bashrc
    source /opt/remi/php73/enable
    php -v
fi

echo "Creating dirs in ${BASE}"
cd $BASE
mkdir -p $RELEASE
mkdir -p $RELEASE/public
# @todo needed any more?
#mkdir -p $SHARED/public/uploads
#mkdir -p $SHARED/public/media/cache
#mkdir -p $RELEASE/public/media
ln -s $RELEASE current
rm -rf html
ln -s current/public html
mkdir -p $SHARED/var
ln -s $SHARED/var $RELEASE/var
# @todo needed any more?
#ln -s $SHARED/public/uploads $RELEASE/public/uploads
#ln -s $SHARED/public/media/cache $RELEASE/public/media/cache
printf "\n\n"

cd $RELEASE

echo "Install Composer"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'e5325b19b381bfd88ce90a5ddb7823406b2a38cff6bb704b0acc289a09c8128d4a8ce2bbafcd1fcbdc38666422fe2806') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
php composer.phar selfupdate
printf "\n\n"

echo "Ready! You can start uploading files to: ${$BASE}/current"
echo

echo 'To change the shell, run:'
echo 'chsh -s /bin/zsh && /bin/zsh'
echo

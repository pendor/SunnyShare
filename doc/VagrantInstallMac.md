# Installing Vagrant & VirtualBox via Homebrew

If you don't have Homebrew installed (why NOT???), get it here: http://brew.sh/

### VirtualBox

Vagrant uses [Virtualbox](https://www.virtualbox.org/) to manage virtual machines.  Install VirtualBox thusly:

	$ brew update
    $ brew cask install virtualbox

You may want to install the VirtualBox extension pack for your VirtualBox version:

    $ brew cask install virtualbox-extension-pack

The extension pack isn't necessary for Vagrant, but it enables USB which can make debugging the contents of USB sticks easier.

### Vagrant

Now install Vagrant itself:

    $ brew cask install vagrant

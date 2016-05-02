# Sunny+Share

![Sunny+Share Logo](https://raw.githubusercontent.com/pendor/SunnyShare/master/copy-to/files/www/logo.png)

Sunny+Share is a sharing platform based on the concept of PirateBox and LibraryBox.  It uses a TP-Link based router or a Raspberry Pi + WiFi card to share & allow uploading of content over a local WiFi connection.

## Why a new project?

A few reasons...

1. *The name...*  Silly as it might sound, "PirateBox" isn't the kind of thing that attracts "normal" non-hacker types to take a look.  It scares them off.  In several months of traveling around with a PirateBox based router, not a single person contributed any content to it, and I never noticed any live connections other than my own.  Others have reported similar results.  The "Sunny" part comes from my intent to run this on a solar power system when camping with a large group.  The rest of the name is just a bad pun...
2. *Updates...*  The latest PirateBox release is based on a three year old version of OpenWRT with "some backports" from an only two year old version.  I ran into several kernel bugs with the version of OpenWRT in PirateBox on a TP-Link WR842 that made it impossible to use a USB->SATA->SSD arrangement I wanted to use for storage.  OpenWRT trunk versions as of around 24-Apr-2016 fix the issues, but older releases exhibit various USB disconnect & failure to enumerate issues.
3. *Simplicity...*  The tangled series of scripts that make up the PirateBox install & boot process are far more complicated than necessary.  Sunny+Share uses a significantly abbreviated startup process & a one-step install (just flash the router & reboot w/ a USB storage device inserted).
4. *There's talk of Pi?*  After fighting with trying to get MiniDLNA running without running out of RAM on a tiny TP-Link router, I ran out of four-letter words...  Upgrade to π, still use OpenWRT?  Yes, please.

## What's this project do that PirateBox's build doesn't?

Sunny+Share's build is very simple and has minimal dependencies on the build host because it does all the actual compiling in a VM.  You need Vagrant and VirtualBox installed.  Other than that, running `./build.sh` should be enough to give you a firmware you can upload to a stock TP-Link router or `sysupgrade` on an existing OpenWRT-based install.

On RaspPi, you'll need to use `./build-sdk.sh` since many binary packages are missing from the current OpenWRT release for Pi.  It takes a little longer, but it's still all automated in a VM.

## Licensing

Code with several licenses is aggregated (not linked...) into Sunny+Share.  All of the Linux stuff is of course GPL-2+, and much of the OpenWRT and other added bits is GPL-3.  Any original code & configuration that I've added to Sunny+Share is licensed under GPL-3 only.  You're of course free to copy & use any of the other components under their original licenses.

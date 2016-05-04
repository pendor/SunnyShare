# Sunny+Share

![Sunny+Share Logo](https://raw.githubusercontent.com/pendor/SunnyShare/master/www/logo.png)

Sunny+Share is a sharing platform based on the concept of PirateBox and LibraryBox.  

## Why a new project?

A few reasons...

1. *The name...*  Silly as it might sound, "PirateBox" isn't the kind of thing that attracts "normal" non-hacker types to take a look.  It scares them off.  In several months of traveling around with a PirateBox based router, not a single person contributed any content to it, and I never noticed any live connections other than my own.  Others have reported similar results.  The "Sunny" part comes from my intent to run this on a solar power system when camping with a large group.  The rest of the name is just a bad pun...
2. *Updates...*  The latest PirateBox release is based on a three year old version of OpenWRT with "some backports" from an only two year old version.  I ran into several kernel bugs with the version of OpenWRT in PirateBox on a TP-Link WR842 that made it impossible to use a USB->SATA->SSD arrangement I wanted to use for storage.  OpenWRT trunk versions as of around 24-Apr-2016 fix the issues, but older releases exhibit various USB disconnect & failure to enumerate issues.
3. *Simplicity...*  The tangled series of scripts that make up the PirateBox install & boot process are far more complicated than necessary.  Sunny+Share uses a significantly abbreviated startup process & a one-step install (just flash the router & reboot w/ a USB storage device inserted).
4. *There's talk of Pi?*  After fighting with trying to get MiniDLNA running without running out of RAM on a tiny TP-Link router, I ran out of four-letter words...  Time for some π...

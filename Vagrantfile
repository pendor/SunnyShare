Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.boot_timeout = 1000

  config.vm.synced_folder "firmware", "/openwrt_firmware"
  config.vm.synced_folder "copy-to", "/openwrt_files"

  config.vm.provider "virtualbox" do |vb|

     vb.memory = "2048"
     vb.cpus = "2"
  end

# snapshot: https://downloads.openwrt.org/snapshots/trunk/ar71xx/generic/OpenWrt-ImageBuilder-ar71xx-generic.Linux-x86_64.tar.bz2
# release: https://downloads.openwrt.org/chaos_calmer/15.05.1/ar71xx/generic/OpenWrt-ImageBuilder-15.05.1-ar71xx-generic.Linux-x86_64.tar.bz2
  config.vm.provision "shell", inline: <<-SHELL
     apt-get update -q
     apt-get install -q -y zip make gcc joe subversion build-essential libncurses5-dev zlib1g-dev gawk git ccache gettext libssl-dev xsltproc python

     echo "Downloading OpenWRT Image Builder.  This will take a while with NO output."
     wget -nv https://downloads.openwrt.org/snapshots/trunk/ar71xx/generic/OpenWrt-ImageBuilder-ar71xx-generic.Linux-x86_64.tar.bz2

     echo "Extracting Image Builder tarball."
     tar -xf OpenWrt-ImageBuilder-ar71xx-generic.Linux-x86_64.tar.bz2
     chown -R vagrant:vagrant OpenWrt-ImageBuilder-ar71xx-generic.Linux-x86_64
     
     echo "Provisioning script done."
  SHELL
end

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.boot_timeout = 10000

  config.vm.synced_folder "firmware", "/openwrt_firmware"
  config.vm.synced_folder "copy-to", "/openwrt_files"
  config.vm.synced_folder "dl", "/openwrt_dl"

  config.vm.provider "virtualbox" do |vb|

     vb.memory = "2048"
     vb.cpus = "2"
  end

# snapshot: https://downloads.openwrt.org/snapshots/trunk/ar71xx/generic/OpenWrt-ImageBuilder-ar71xx-generic.Linux-x86_64.tar.bz2
# release: https://downloads.openwrt.org/chaos_calmer/15.05.1/ar71xx/generic/OpenWrt-ImageBuilder-15.05.1-ar71xx-generic.Linux-x86_64.tar.bz2
  config.vm.provision "shell", inline: <<-SHELL
     apt-get update -q
     apt-get install -q -y zip make gcc joe subversion build-essential libncurses5-dev zlib1g-dev gawk git ccache gettext libssl-dev xsltproc python 
     apt-get install -q -y firmware-b43-installer b43-fwcutter firmware-b43legacy-installer
     echo "Provisioning script done."
  SHELL
end

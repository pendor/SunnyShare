ext4load mmc 0 0x00000000 /boot/i2c-cmd.bin
ext4load mmc 0 0x00000020 /boot/sun.bin

i2c dev 2
i2c write 0x00000000 0x3c 0x0 0x1f
i2c write 0x00000020 0x3c 0x40 0x400 -s

if ext4load mmc 0 0x00000000 /boot/.verbose
then
setenv verbosity 7
else
setenv verbosity 1
fi
setenv bootargs "console=tty1 root=/dev/mmcblk0p1 rootwait rootfstype=ext4 cgroup_enable=memory swapaccount=1 sunxi_ve_mem_reserve=0 sunxi_g2d_mem_reserve=0 sunxi_no_mali_mem_reserve sunxi_fb_mem_reserve=16 hdmi.audio=EDID:0 disp.screen0_output_mode=1920x1080p60 panic=10 consoleblank=0 enforcing=0 loglevel=${verbosity}"
#--------------------------------------------------------------------------------------------------------------------------------
# Boot loader script to boot with different boot methods for old and new kernel
#--------------------------------------------------------------------------------------------------------------------------------
if ext4load mmc 0 0x00000000 /boot/.next || fatload mmc 0 0x00000000 .next || ext4load mmc 0 0x00000000 .next
then
# sunxi mainline kernel
#--------------------------------------------------------------------------------------------------------------------------------
ext4load mmc 0 0x49000000 /boot/dtb/${fdtfile} || fatload mmc 0 0x49000000 /dtb/${fdtfile} || ext4load mmc 0 0x49000000 /dtb/${fdtfile}
ext4load mmc 0 0x42000000 /boot/uInitrd || fatload mmc 0 0x42000000 uInitrd || ext4load mmc 0 0x42000000 uInitrd
ext4load mmc 0 0x46000000 /boot/zImage || fatload mmc 0 0x46000000 zImage || ext4load mmc 0 0x46000000 zImage
bootz 0x46000000 0x42000000 0x49000000
#bootz 0x46000000 - 0x49000000
#--------------------------------------------------------------------------------------------------------------------------------
else
# sunxi android kernel
#--------------------------------------------------------------------------------------------------------------------------------
ext4load mmc 0 0x43000000 /boot/script.bin || fatload mmc 0 0x43000000 script.bin || ext4load mmc 0 0x43000000 script.bin
ext4load mmc 0 0x42000000 /boot/uInitrd || fatload mmc 0 0x42000000 uInitrd || ext4load mmc 0 0x42000000 uInitrd
ext4load mmc 0 0x48000000 /boot/zImage || fatload mmc 0 0x48000000 zImage || ext4load mmc 0 0x48000000 zImage
bootz 0x48000000 0x42000000
#bootz 0x48000000
#--------------------------------------------------------------------------------------------------------------------------------
fi
# Recompile with:
# mkimage -C none -A arm -T script -d /boot/boot.cmd /boot/boot.scr 

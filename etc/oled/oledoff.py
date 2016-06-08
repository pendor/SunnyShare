#!/usr/bin/env python

from os.path import dirname, abspath
from oled.device import ssd1306, sh1106, const
from oled.render import canvas
from PIL import ImageDraw, ImageFont, Image

device = ssd1306(port=1, address=0x3C, skipinit=True)
font = ImageFont.load_default()
fpath = "%s/fonts/C&C Red Alert [INET].ttf" % (dirname(abspath(__file__)))
font2 = ImageFont.truetype(fpath, 18)

with canvas(device) as draw:
    draw.text((0, 0), " ", font=font2, fill=255)

device.command(const.DISPLAYOFF)

#!/usr/bin/env python

from os.path import dirname, abspath
from oled.device import ssd1306, sh1106
from oled.render import canvas
from PIL import ImageDraw, Image

device = ssd1306(port=1, address=0x3C)

with canvas(device) as draw:
    fpath = "%s/sun.png" % (dirname(abspath(__file__)))
    logo = Image.open(fpath)
    draw.bitmap((32, 0), logo, fill=1)

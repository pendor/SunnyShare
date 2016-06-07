#!/usr/bin/env python

from os.path import dirname, abspath
from oled.device import ssd1306, sh1106, const
from oled.render import canvas
from PIL import ImageDraw, Image

device = ssd1306(port=1, address=0x3C)

device.command(const.DISPLAYOFF)

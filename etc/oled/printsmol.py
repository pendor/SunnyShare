#!/usr/bin/env python

import sys
from os.path import dirname, abspath
from oled.device import ssd1306, sh1106
from oled.render import canvas
from PIL import ImageDraw, ImageFont

oled = ssd1306(port=1, address=0x3C, skipinit=True)
font = ImageFont.load_default()
fpath = "%s/fonts/C&C Red Alert [INET].ttf" % (dirname(abspath(__file__)))
font2 = ImageFont.truetype(fpath, 18)
font3 = ImageFont.truetype(fpath, 20)

# Should get about 18 chars on line 1, 12 on lines 2 & 3
with canvas(oled) as draw:
    if len(sys.argv) >= 2:
        draw.text((0, 0), sys.argv[1], font=font2, fill=255)
        
    if len(sys.argv) >= 3:
        draw.text((0, 20), sys.argv[2], font=font3, fill=255)
        
    if len(sys.argv) >= 4:
        draw.text((0, 46), sys.argv[3], font=font3, fill=255)

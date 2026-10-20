"""Generate a printable guest registration QR using the public deployment URL.

Usage: python scripts/generate_guest_qr.py https://booking.example.com
Uses the development-machine qrcode package; the Laravel app does not need Python.
"""

import sys
from pathlib import Path
from urllib.parse import urlsplit

import qrcode
import qrcode.image.svg

base = sys.argv[1].rstrip('/') if len(sys.argv) == 2 else ''
parsed = urlsplit(base)
if parsed.scheme not in ('http', 'https') or not parsed.netloc or parsed.query or parsed.fragment:
    raise SystemExit('Pass the public application URL, e.g. https://booking.example.com')

target = base + '/tamu'
output = Path(__file__).resolve().parents[1] / 'storage/app/guest-registration-qr.svg'
output.parent.mkdir(parents=True, exist_ok=True)
qrcode.make(target, image_factory=qrcode.image.svg.SvgPathImage, box_size=12, border=4).save(output)
print(f'{target}\n{output}')

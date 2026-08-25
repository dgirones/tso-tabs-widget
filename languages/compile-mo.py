#!/usr/bin/env python3
"""Compile plugin PO files to MO (dev helper)."""
import glob
import os
import polib

root = os.path.dirname(os.path.abspath(__file__))
for po_path in glob.glob(os.path.join(root, '*.po')):
    po = polib.pofile(po_path)
    mo_path = po_path[:-3] + '.mo'
    po.save_as_mofile(mo_path)
    print(f'Compiled {os.path.basename(mo_path)}')

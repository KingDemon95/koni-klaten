#!/usr/bin/env python3
from pathlib import Path
from datetime import datetime
import re
import shutil
import sys

ROOT = Path.home() / "koni-klaten-main-backup"
VIEWS = ROOT / "resources" / "views" / "admin"
GUARD = "@if(auth()->user()?->hasRole('admin_koni'))"

if not ROOT.is_dir():
    print(f"GAGAL: folder proyek tidak ditemukan: {ROOT}")
    sys.exit(1)

matches = []
for path in VIEWS.rglob("*.blade.php"):
    try:
        source = path.read_text(encoding="utf-8")
    except (OSError, UnicodeError):
        continue
    if "admin.cabor.edit" in source and "admin.cabor.destroy" in source:
        matches.append((path, source))

if len(matches) != 1:
    print("GAGAL: view daftar Cabor tidak ditemukan secara unik.")
    print(f"Jumlah file cocok: {len(matches)}")
    for path, _ in matches:
        print(f" - {path}")
    print("Tidak ada file yang diubah.")
    sys.exit(1)

path, source = matches[0]

# Match only the Edit anchor and Delete form. The table rows/cells are untouched.
edit_pattern = re.compile(
    r"<a\b(?:(?!</a>).)*admin\.cabor\.edit(?:(?!</a>).)*</a>",
    re.IGNORECASE | re.DOTALL,
)
delete_pattern = re.compile(
    r"<form\b(?:(?!</form>).)*admin\.cabor\.destroy(?:(?!</form>).)*</form>",
    re.IGNORECASE | re.DOTALL,
)

edit_matches = list(edit_pattern.finditer(source))
delete_matches = list(delete_pattern.finditer(source))

if len(edit_matches) != 1 or len(delete_matches) != 1:
    print("GAGAL: elemen tombol Edit/Hapus tidak ditemukan tepat satu kali.")
    print(f"Edit ditemukan: {len(edit_matches)}; form Hapus ditemukan: {len(delete_matches)}")
    print("Tidak ada file yang diubah.")
    sys.exit(1)

# Avoid duplicate wrappers if the installer is run again.
for label, match in (("Edit", edit_matches[0]), ("Hapus", delete_matches[0])):
    before = source[max(0, match.start() - 180):match.start()]
    if GUARD in before:
        print(f"GAGAL: tombol {label} tampaknya sudah memiliki guard role.")
        print("Periksa file secara manual; tidak ada file yang diubah.")
        sys.exit(1)

# Insert from right to left so earlier offsets remain valid.
blocks = [
    (edit_matches[0].start(), edit_matches[0].end(), edit_matches[0].group(0)),
    (delete_matches[0].start(), delete_matches[0].end(), delete_matches[0].group(0)),
]
for start, end, block in sorted(blocks, key=lambda item: item[0], reverse=True):
    wrapped = GUARD + "\n" + block + "\n@endif"
    source = source[:start] + wrapped + source[end:]

backup = path.with_name(
    path.name + ".backup-cabor-ui-v2-" + datetime.now().strftime("%Y%m%d-%H%M%S")
)
shutil.copy2(path, backup)
path.write_text(source, encoding="utf-8")

print("BERHASIL: patch V2 diterapkan.")
print("Hanya elemen tombol Edit dan form Hapus yang dibungkus guard Admin KONI.")
print("Baris data Cabor, kolom tabel, dan tombol Tambah Cabor tidak diubah.")
print(f"Backup otomatis: {backup}")
print(f"File diperbarui: {path}")

#!/usr/bin/env python3
from pathlib import Path
from datetime import datetime
import re
import shutil
import sys

ROOT = Path.home() / "koni-klaten-main-backup"
VIEWS = ROOT / "resources" / "views" / "admin"

if not ROOT.is_dir():
    print(f"GAGAL: folder proyek tidak ditemukan: {ROOT}")
    sys.exit(1)

# Temukan view yang benar secara otomatis berdasarkan route Edit Cabor.
candidates = []
for path in VIEWS.rglob("*.blade.php"):
    try:
        content = path.read_text(encoding="utf-8")
    except Exception:
        continue
    if "admin.cabor.edit" in content and "admin.cabor.destroy" in content:
        candidates.append((path, content))

if len(candidates) != 1:
    print("GAGAL: view daftar Cabang Olahraga tidak ditemukan secara unik.")
    print(f"Jumlah file yang cocok: {len(candidates)}")
    for path, _ in candidates:
        print(f" - {path}")
    print("Tidak ada file yang diubah.")
    sys.exit(1)

path, content = candidates[0]

# Temukan sel tabel (td) yang memuat tombol Edit dan Hapus.
cell_pattern = re.compile(
    r"(?P<open><td\b[^>]*>)(?P<body>.*?admin\.cabor\.edit.*?admin\.cabor\.destroy.*?)(?P<close></td>)",
    re.IGNORECASE | re.DOTALL,
)
matches = list(cell_pattern.finditer(content))

if len(matches) != 1:
    # Coba urutan route sebaliknya.
    cell_pattern = re.compile(
        r"(?P<open><td\b[^>]*>)(?P<body>(?:(?!</td>).)*admin\.cabor\.(?:edit|destroy)(?:(?!</td>).)*)(?P<close></td>)",
        re.IGNORECASE | re.DOTALL,
    )
    matches = [m for m in cell_pattern.finditer(content)
               if "admin.cabor.edit" in m.group("body")
               and "admin.cabor.destroy" in m.group("body")]

if len(matches) != 1:
    print("GAGAL: kolom aksi Edit/Hapus tidak ditemukan secara unik.")
    print("Tidak ada file yang diubah.")
    sys.exit(1)

match = matches[0]
body = match.group("body")
guard = "@if(auth()->user()?->hasRole('admin_koni'))"

if guard in body or "hasRole('admin_koni')" in body:
    print("INFO: kolom aksi sudah memiliki pembatasan Admin KONI.")
    print("Tidak ada perubahan diperlukan.")
    sys.exit(0)

# Bungkus hanya isi kolom aksi. Ketua tetap melihat tabel, tetapi tanpa tombol Edit/Hapus.
new_body = "\n\n                                    " + guard + "\n" + body.rstrip() + "\n                                    @endif\n                                "
updated = content[:match.start("body")] + new_body + content[match.end("body"):]

if updated == content:
    print("GAGAL: hasil patch tidak menghasilkan perubahan.")
    sys.exit(1)

backup = path.with_name(path.name + ".backup-cabor-ui-" + datetime.now().strftime("%Y%m%d-%H%M%S"))
shutil.copy2(path, backup)
path.write_text(updated, encoding="utf-8")

print("BERHASIL: tombol Edit dan Hapus Cabor dibatasi untuk Admin KONI.")
print("Admin KONI: tetap melihat tombol Edit dan Hapus.")
print("Ketua KONI: tombol Edit dan Hapus disembunyikan.")
print("Tombol Tambah Cabor tidak diubah karena Ketua KONI memang diizinkan menambah Cabor.")
print(f"Backup otomatis: {backup}")
print(f"File diperbarui: {path}")

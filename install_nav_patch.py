#!/usr/bin/env python3
from pathlib import Path
from datetime import datetime
import shutil
import sys

project = Path.home() / "koni-klaten-main-backup"
target = project / "resources/views/admin/partials/nav-links.blade.php"

if not target.is_file():
    sys.exit(f"ERROR: file target tidak ditemukan: {target}")

current = target.read_text(encoding="utf-8-sig")
required_markers = [
    "$isKetua = auth()->user()->hasRole('ketua_koni');",
    "'admin.cabor.index'",
    "'admin.publikasi.index'",
    "'account.password.edit'",
]
missing = [marker for marker in required_markers if marker not in current]
if missing:
    sys.exit(
        "ERROR: isi file tidak cocok dengan versi yang diperiksa. "
        "Tidak ada file yang diubah. Marker hilang: " + ", ".join(missing)
    )

backup = target.with_name(
    target.name + ".backup-" + datetime.now().strftime("%Y%m%d-%H%M%S")
)
shutil.copy2(target, backup)

replacement = Path(__file__).with_name("nav-links.blade.php").read_text(encoding="utf-8")
target.write_text(replacement, encoding="utf-8", newline="\n")

print("BERHASIL: navigasi Admin diperbarui.")
print(f"Backup asli: {backup}")
print(f"File diperbarui: {target}")
print("Langkah berikutnya: bersihkan cache view dan periksa dashboard Admin/Ketua.")

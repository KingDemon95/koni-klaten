#!/usr/bin/env python3
from pathlib import Path
from datetime import datetime
import shutil
import sys

project = Path.cwd()
target = project / "app/Http/Controllers/Admin/CabangOlahragaController.php"

if not target.is_file():
    sys.exit("GAGAL: jalankan installer dari root ~/koni-klaten-main-backup.")

source = target.read_text(encoding="utf-8")
old = "abort_unless(auth()->user()?->hasRole('admin_koni'), 403);"
new = "abort_unless(auth()->user()?->hasAnyRole(['admin_koni', 'ketua_koni']), 403);"

# Restrict replacement to create() and store() method bodies only.
methods = ["create", "store"]
updated = source
changes = []

for method in methods:
    marker = f"public function {method}("
    if updated.count(marker) != 1:
        sys.exit(f"GAGAL: method {method} tidak ditemukan tepat satu kali. Tidak ada file diubah.")

    start = updated.index(marker)
    next_method = updated.find("\n    public function ", start + len(marker))
    end = next_method if next_method != -1 else len(updated)
    block = updated[start:end]

    if block.count(old) != 1:
        sys.exit(
            f"GAGAL: guard Admin-only pada method {method} tidak ditemukan tepat satu kali. "
            "Tidak ada file diubah."
        )

    block = block.replace(old, new, 1)
    updated = updated[:start] + block + updated[end:]
    changes.append(method)

timestamp = datetime.now().strftime("%Y%m%d-%H%M%S")
backup = target.with_name(target.name + f".backup-ketua-cabor-{timestamp}")
shutil.copy2(target, backup)
target.write_text(updated, encoding="utf-8", newline="\n")

print("BERHASIL: Ketua KONI diizinkan membuat Cabang Olahraga.")
print("Method yang diubah: create(), store()")
print("Edit/update/delete Cabang Olahraga tetap khusus Admin KONI.")
print(f"Backup otomatis: {backup}")
print(f"File diperbarui: {target}")
print("Tidak ada file lain yang diubah.")

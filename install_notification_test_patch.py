#!/usr/bin/env python3
from pathlib import Path
from datetime import datetime
import shutil
import sys

project = Path.home() / "koni-klaten-main-backup"
target = project / "tests/Feature/SuratMessagingTest.php"

if not target.is_file():
    sys.exit(f"GAGAL: file tidak ditemukan: {target}")

source = target.read_text(encoding="utf-8")
old_url = "$this->assertSame('/pesan', $notification->data['url']);"
new_url = "$this->assertSame(route('cabor.surat.show', $surat, false), $notification->data['url']);"

old_redirect = "            ->assertRedirect(route('messages.index'));"
new_redirect = "            ->assertRedirect(route('cabor.surat.show', $surat));"

if source.count(old_url) != 1:
    sys.exit(
        "GAGAL: assertion URL lama tidak ditemukan tepat satu kali. "
        "Tidak ada file diubah."
    )
if source.count(old_redirect) != 1:
    sys.exit(
        "GAGAL: assertion redirect lama tidak ditemukan tepat satu kali. "
        "Tidak ada file diubah."
    )

updated = source.replace(old_url, new_url, 1).replace(old_redirect, new_redirect, 1)

backup = target.with_name(
    target.name + ".backup-" + datetime.now().strftime("%Y%m%d-%H%M%S")
)
shutil.copy2(target, backup)
target.write_text(updated, encoding="utf-8", newline="\n")

print("BERHASIL: ekspektasi test notifikasi diperbarui sesuai direct link.")
print(f"Backup: {backup}")
print(f"File diperbarui: {target}")
print("Hanya tests/Feature/SuratMessagingTest.php yang diubah.")

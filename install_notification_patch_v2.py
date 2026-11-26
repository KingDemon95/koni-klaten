#!/usr/bin/env python3
from pathlib import Path
from datetime import datetime
import shutil
import sys

project = Path.home() / "koni-klaten-main-backup"
target = project / "app/Http/Controllers/SuratController.php"

if not target.is_file():
    sys.exit(f"GAGAL: file tidak ditemukan: {target}")

source = target.read_text(encoding="utf-8")
updated = source

# Tiga notifikasi Staff -> Admin Cabor: buka detail surat dalam area Cabor.
old_direct = "route('messages.index', [], false),"
count_direct = updated.count(old_direct)
if count_direct < 3:
    sys.exit(
        "GAGAL: ditemukan hanya "
        f"{count_direct} pemanggilan route messages.index langsung; "
        "dibutuhkan minimal 3. Tidak ada file diubah."
    )
updated = updated.replace(
    old_direct,
    "route('cabor.surat.show', $surat, false),",
    3,
)

# Balasan Cabor/Pimpinan: satu notifikasi dapat dikirim ke role berbeda,
# sehingga URL harus disesuaikan dengan halaman penerima.
old_assignment = "$notificationUrl = route('messages.index', [], false);"
new_assignment = (
    "$staffNotificationUrl = route('staff.surat.show', $surat, false);\n"
    "        $caborNotificationUrl = route('cabor.surat.show', $surat, false);"
)
if updated.count(old_assignment) != 1:
    sys.exit(
        "GAGAL: assignment notificationUrl tidak ditemukan tepat satu kali. "
        "Tidak ada file diubah."
    )
updated = updated.replace(old_assignment, new_assignment, 1)

old_staff_call = "WorkflowNotifier::toUser($surat->staff, $notificationTitle, $notificationBody, $notificationUrl, 'persuratan', $user->id);"
new_staff_call = "WorkflowNotifier::toUser($surat->staff, $notificationTitle, $notificationBody, $staffNotificationUrl, 'persuratan', $user->id);"
old_staff_role_call = "WorkflowNotifier::toRole('staff_koni', $notificationTitle, $notificationBody, $notificationUrl, 'persuratan', null, $user->id);"
new_staff_role_call = "WorkflowNotifier::toRole('staff_koni', $notificationTitle, $notificationBody, $staffNotificationUrl, 'persuratan', null, $user->id);"
old_cabor_call = "WorkflowNotifier::toRole('admin_cabor', $notificationTitle, $notificationBody, $notificationUrl, 'persuratan', $surat->cabang_olahraga_id, $user->id);"
new_cabor_call = "WorkflowNotifier::toRole('admin_cabor', $notificationTitle, $notificationBody, $caborNotificationUrl, 'persuratan', $surat->cabang_olahraga_id, $user->id);"

for old, new, label in [
    (old_staff_call, new_staff_call, "notifikasi ke staff"),
    (old_staff_role_call, new_staff_role_call, "notifikasi role staff"),
    (old_cabor_call, new_cabor_call, "notifikasi ke Cabor"),
]:
    if updated.count(old) != 1:
        sys.exit(f"GAGAL: pola {label} tidak ditemukan tepat satu kali. Tidak ada file diubah.")
    updated = updated.replace(old, new, 1)

if updated == source:
    sys.exit("GAGAL: tidak ada perubahan yang dihasilkan. Tidak ada file diubah.")

backup = target.with_name(
    target.name + ".backup-" + datetime.now().strftime("%Y%m%d-%H%M%S")
)
shutil.copy2(target, backup)
target.write_text(updated, encoding="utf-8", newline="\n")

print("BERHASIL: tautan notifikasi diarahkan ke detail surat sesuai role penerima.")
print(f"Backup: {backup}")
print(f"File diperbarui: {target}")

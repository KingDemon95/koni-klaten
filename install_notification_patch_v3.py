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

# A. Tiga notifikasi Staff -> Admin Cabor. Log aktual menunjukkan
# ketiganya memakai route('messages.index', [], false).
old_cabor_route = "route('messages.index', [], false),"
if updated.count(old_cabor_route) != 3:
    sys.exit(
        "GAGAL: jumlah route messages.index berbentuk argumen baris "
        f"tidak sesuai (ditemukan {updated.count(old_cabor_route)}, perlu 3). "
        "Tidak ada file diubah."
    )
updated = updated.replace(
    old_cabor_route,
    "route('cabor.surat.show', $surat, false),",
    3,
)

# B. Notifikasi balasan dikirim ke dua jenis penerima. URL harus berbeda:
# staff menerima route staff.surat.show, Admin Cabor menerima cabor.surat.show.
old_assignment = "$notificationUrl = route('messages.index', [], false);"
new_assignment = (
    "$staffNotificationUrl = route('staff.surat.show', $surat, false);\n"
    "        $caborNotificationUrl = route('cabor.surat.show', $surat, false);"
)
if updated.count(old_assignment) != 1:
    sys.exit(
        "GAGAL: baris assignment notificationUrl tidak ditemukan tepat satu kali. "
        "Tidak ada file diubah."
    )
updated = updated.replace(old_assignment, new_assignment, 1)

# Ganti argumen URL pada empat pemanggilan balasan berdasarkan baris aktual.
calls = [
    (
        "WorkflowNotifier::toRole('staff_koni', $notificationTitle, $notificationBody, $notificationUrl, 'persuratan', null, $user->id);",
        "WorkflowNotifier::toRole('staff_koni', $notificationTitle, $notificationBody, $staffNotificationUrl, 'persuratan', null, $user->id);",
        "balasan ke role staff",
    ),
    (
        "WorkflowNotifier::toRole('admin_cabor', $notificationTitle, $notificationBody, $notificationUrl, 'persuratan', $surat->cabang_olahraga_id, $user->id);",
        "WorkflowNotifier::toRole('admin_cabor', $notificationTitle, $notificationBody, $caborNotificationUrl, 'persuratan', $surat->cabang_olahraga_id, $user->id);",
        "balasan ke Admin Cabor",
    ),
]
for old, new, label in calls:
    if updated.count(old) != 1:
        sys.exit(f"GAGAL: pola {label} tidak ditemukan tepat satu kali. Tidak ada file diubah.")
    updated = updated.replace(old, new, 1)

# Ada dua pemanggilan toUser ke Staff KONI pada cabang if/else berbeda.
old_staff_user = "WorkflowNotifier::toUser($surat->staff, $notificationTitle, $notificationBody, $notificationUrl, 'persuratan', $user->id);"
new_staff_user = "WorkflowNotifier::toUser($surat->staff, $notificationTitle, $notificationBody, $staffNotificationUrl, 'persuratan', $user->id);"
if updated.count(old_staff_user) != 2:
    sys.exit(
        "GAGAL: harus ditemukan tepat dua pemanggilan notifikasi ke staff; "
        f"ditemukan {updated.count(old_staff_user)}. Tidak ada file diubah."
    )
updated = updated.replace(old_staff_user, new_staff_user, 2)

if updated == source:
    sys.exit("GAGAL: tidak ada perubahan yang dihasilkan. Tidak ada file diubah.")

backup = target.with_name(
    target.name + ".backup-" + datetime.now().strftime("%Y%m%d-%H%M%S")
)
shutil.copy2(target, backup)
target.write_text(updated, encoding="utf-8", newline="\n")

print("BERHASIL: URL notifikasi diarahkan ke detail surat sesuai role penerima.")
print(f"Backup: {backup}")
print(f"File diperbarui: {target}")
print("Perubahan hanya pada app/Http/Controllers/SuratController.php.")

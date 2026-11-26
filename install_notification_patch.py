#!/usr/bin/env python3
from pathlib import Path
from datetime import datetime
import shutil
import sys

ROOT = Path.cwd()
TARGET = ROOT / 'app/Http/Controllers/SuratController.php'

if not TARGET.is_file():
    print('GAGAL: Jalankan installer dari root proyek Laravel (folder yang berisi app/ dan routes/).')
    sys.exit(1)

source = TARGET.read_text(encoding='utf-8')
updated = source

# Three notifications sent by Staff to Cabor admins must open that Cabor's surat.
old_cabor_url = "route('messages.index', [], false)"
count_cabor_context = updated.count(old_cabor_url)
# These occurrences are the three toCaborAdmins() calls shown in the supplied audit.
if count_cabor_context < 3:
    print(f'GAGAL: Ditemukan hanya {count_cabor_context} URL messages.index; kode tidak cocok dengan versi yang diaudit. Tidak ada file diubah.')
    sys.exit(1)

# Replace only the first three occurrences, which belong to the three toCaborAdmins calls.
for _ in range(3):
    updated = updated.replace(old_cabor_url, "route('cabor.surat.show', $surat, false)", 1)

old_url_assignment = "$notificationUrl = route('messages.index', [], false);"
new_url_assignment = "$staffNotificationUrl = route('staff.surat.show', $surat, false);\n        $caborNotificationUrl = route('cabor.surat.show', $surat, false);"
if updated.count(old_url_assignment) != 1:
    print('GAGAL: Baris pembentuk URL notifikasi balasan tidak ditemukan secara unik. Tidak ada file diubah.')
    sys.exit(1)
updated = updated.replace(old_url_assignment, new_url_assignment, 1)

replacements = [
    ("$notificationTitle, $notificationBody, $notificationUrl, 'persuratan', $user->id", "$notificationTitle, $notificationBody, $staffNotificationUrl, 'persuratan', $user->id"),
    ("$notificationTitle, $notificationBody, $notificationUrl, 'persuratan', null, $user->id", "$notificationTitle, $notificationBody, $staffNotificationUrl, 'persuratan', null, $user->id"),
    ("$notificationTitle, $notificationBody, $notificationUrl, 'persuratan', $surat->cabang_olahraga_id, $user->id", "$notificationTitle, $notificationBody, $caborNotificationUrl, 'persuratan', $surat->cabang_olahraga_id, $user->id"),
]
for old, new in replacements:
    if updated.count(old) != 1:
        print('GAGAL: Pola pemanggilan WorkflowNotifier tidak cocok atau tidak unik:')
        print(old)
        print('Tidak ada file diubah.')
        sys.exit(1)
    updated = updated.replace(old, new, 1)

if updated == source:
    print('GAGAL: Tidak ada perubahan yang dapat diterapkan.')
    sys.exit(1)

stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = TARGET.with_name(TARGET.name + f'.backup-notification-{stamp}')
shutil.copy2(TARGET, backup)
TARGET.write_text(updated, encoding='utf-8')

print('BERHASIL: URL notifikasi persuratan diperbarui.')
print(f'Backup asli: {backup}')
print(f'File diperbarui: {TARGET}')
print('Notifikasi Staff -> Admin Cabor: langsung ke detail surat Cabor.')
print('Notifikasi Admin Cabor -> Staff: langsung ke detail surat Staff.')
print('Notifikasi Ketua/Sekretaris -> Staff dan Admin Cabor: diarahkan sesuai role penerima.')
print('Berikutnya: jalankan pengujian dan bersihkan cache view (jika diperlukan).')

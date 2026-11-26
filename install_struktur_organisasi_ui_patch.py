#!/usr/bin/env python3
from pathlib import Path
from datetime import datetime
import shutil
import sys

ROOT = Path.home() / "koni-klaten-main-backup"
TARGET = ROOT / "resources/views/admin/struktur-organisasi/index.blade.php"

if not TARGET.is_file():
    print(f"GAGAL: file tidak ditemukan: {TARGET}")
    sys.exit(1)

source = TARGET.read_text(encoding="utf-8")
updated = source

marker = "@section('content')"
role_line = "\n    @php($canManageOrganization = auth()->user()?->hasRole('admin_koni'))\n"
if "$canManageOrganization" not in updated:
    if marker not in updated:
        print("GAGAL: penanda @section('content') tidak ditemukan. Tidak ada file diubah.")
        sys.exit(1)
    updated = updated.replace(marker, marker + role_line, 1)

header_button = '''        <a href="{{ route('admin.struktur-organisasi.create') }}"
           class="rounded-xl bg-[#E60012] px-5 py-3 text-center text-sm font-bold text-white shadow-lg shadow-red-100 hover:bg-red-700">

            + Tambah Pengurus

        </a>'''
header_wrapped = '''        @if($canManageOrganization)
''' + header_button + '''
        @endif'''
if header_button in updated:
    updated = updated.replace(header_button, header_wrapped, 1)
elif header_wrapped not in updated:
    print("GAGAL: tombol Tambah Pengurus di header tidak cocok. Tidak ada file diubah.")
    sys.exit(1)

actions = '''                                <div class="flex items-center justify-center gap-2">

                                    <a
                                        href="{{ route('admin.struktur-organisasi.edit', $pengurus) }}"
                                        class="rounded-lg bg-yellow-50 px-3 py-2 text-xs font-bold text-yellow-700">

                                        Edit

                                    </a>


                                    <form
                                        action="{{ route('admin.struktur-organisasi.destroy', $pengurus) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus pengurus ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-600">

                                            Hapus

                                        </button>

                                    </form>

                                </div>'''
actions_wrapped = '''                                @if($canManageOrganization)
''' + actions + '''
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif'''
if actions in updated:
    updated = updated.replace(actions, actions_wrapped, 1)
elif actions_wrapped not in updated:
    print("GAGAL: blok tombol Edit/Hapus tidak cocok. Tidak ada file diubah.")
    sys.exit(1)

empty_button = '''                                <a href="{{ route('admin.struktur-organisasi.create') }}"
                                   class="mt-5 inline-block rounded-lg bg-[#E60012] px-5 py-3 text-xs font-bold text-white">

                                    Tambah Pengurus

                                </a>'''
empty_wrapped = '''                                @if($canManageOrganization)
''' + empty_button + '''
                                @endif'''
if empty_button in updated:
    updated = updated.replace(empty_button, empty_wrapped, 1)
elif empty_wrapped not in updated:
    print("GAGAL: tombol Tambah Pengurus pada kondisi data kosong tidak cocok. Tidak ada file diubah.")
    sys.exit(1)

if updated == source:
    print("INFO: patch sudah terpasang; tidak ada perubahan baru.")
    sys.exit(0)

backup = TARGET.with_name(TARGET.name + ".backup-" + datetime.now().strftime("%Y%m%d-%H%M%S"))
shutil.copy2(TARGET, backup)
TARGET.write_text(updated, encoding="utf-8")

print("BERHASIL: tampilan Struktur Organisasi diperbarui.")
print("Admin KONI: tombol Tambah, Edit, dan Hapus tersedia.")
print("Ketua KONI: hanya melihat data; tombol pengelolaan disembunyikan.")
print(f"Backup asli: {backup}")
print(f"File diperbarui: {TARGET}")
print("Catatan: pembatasan akses server/controller tetap dipertahankan.")

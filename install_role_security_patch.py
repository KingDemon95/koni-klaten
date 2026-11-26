#!/usr/bin/env python3
from pathlib import Path
from datetime import datetime
import re
import shutil
import sys

ROOT = Path(__file__).resolve().parent
PROJECT = Path.cwd()

TARGETS = {
    "app/Http/Controllers/Admin/CabangOlahragaController.php": [
        "create", "store", "edit", "update", "destroy",
    ],
    "app/Http/Controllers/Admin/StrukturOrganisasiController.php": [
        "create", "store", "edit", "update", "destroy",
    ],
    "app/Http/Controllers/Admin/CustomFieldController.php": [
        "index", "store", "update", "destroy",
    ],
}

GUARD = "abort_unless(auth()->user()?->hasRole('admin_koni'), 403);"

def insert_guard(source: str, method: str, relative: str) -> tuple[str, bool]:
    # Match method declaration, including multiline parameter lists.
    pattern = re.compile(
        rf"(public\s+function\s+{re.escape(method)}\s*\()",
        re.MULTILINE
    )
    matches = list(pattern.finditer(source))
    if len(matches) != 1:
        raise RuntimeError(
            f"{relative}: method {method} ditemukan {len(matches)} kali; "
            "file tidak diubah."
        )

    start = matches[0].start()
    brace = source.find("{", matches[0].end())
    if brace < 0:
        raise RuntimeError(f"{relative}: body method {method} tidak ditemukan.")

    # Do not insert twice.
    body_preview = source[brace + 1:brace + 500]
    if GUARD in body_preview:
        return source, False

    line_end = source.find("\n", brace)
    if line_end < 0:
        raise RuntimeError(f"{relative}: format body method {method} tidak dikenali.")

    indent_match = re.search(r"\n([ \t]*)\S", source[brace:brace + 100])
    indent = indent_match.group(1) if indent_match else "        "
    insertion = f"\n{indent}{GUARD}"
    source = source[:brace + 1] + insertion + source[brace + 1:]
    return source, True

def main():
    changed = []
    missing = []
    timestamp = datetime.now().strftime("%Y%m%d-%H%M%S")

    for relative, methods in TARGETS.items():
        path = PROJECT / relative
        if not path.is_file():
            missing.append(str(path))
            continue

        original = path.read_text(encoding="utf-8")
        updated = original
        file_changed = False

        for method in methods:
            updated, did_change = insert_guard(updated, method, relative)
            file_changed = file_changed or did_change

        if file_changed:
            backup = path.with_name(path.name + f".backup-role-security-{timestamp}")
            shutil.copy2(path, backup)
            path.write_text(updated, encoding="utf-8")
            changed.append((relative, str(backup)))

    if missing:
        print("GAGAL: file berikut tidak ditemukan:")
        for item in missing:
            print(" -", item)
        print("Periksa apakah script dijalankan dari root proyek.")
        sys.exit(1)

    if not changed:
        print("Tidak ada perubahan: seluruh guard mungkin sudah terpasang.")
    else:
        print("BERHASIL: pembatasan role Admin KONI dipasang.")
        for relative, backup in changed:
            print(f" - {relative}")
            print(f"   Backup: {backup}")

    print("\nAturan yang dipasang:")
    print(" - Cabang Olahraga: create/store/edit/update/destroy khusus Admin KONI.")
    print(" - Struktur Organisasi: create/store/edit/update/destroy khusus Admin KONI.")
    print(" - Master Data: index/store/update/destroy khusus Admin KONI.")
    print(" - Endpoint index Cabang Olahraga dan Struktur Organisasi tetap mengikuti role group yang sudah ada.")
    print("\nLangkah berikutnya: jalankan pemeriksaan PHP dan pengujian role.")

if __name__ == "__main__":
    main()

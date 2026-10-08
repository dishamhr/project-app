#!/usr/bin/env bash
# ============================================================
#  Task Reminder Mahasiswa - uji end-to-end lewat HTTP
#  Prasyarat: server berjalan  php artisan serve --port=8000
#  Jalankan : bash scripts/e2e-check.sh
# ============================================================

BASE=${BASE:-http://127.0.0.1:8000}
# Zona waktu aplikasi (harus sama dengan config/app.php -> timezone).
APP_TZ=${APP_TZ:-Asia/Jakarta}
app_date() { # <format PHP> [offset-detik]
  php -r "date_default_timezone_set('$APP_TZ'); echo date('$1', time()+(${2:-0}));"
}
WORK=$(mktemp -d)
COOKIES="$WORK/cookies.txt"
COOKIES2="$WORK/cookies2.txt"
FAILS=0
EMAIL="uji$(date +%s)@kampus.ac.id"
EMAIL2="lain$(date +%s)@kampus.ac.id"

check() { # <label> <actual> <expected>
  if [ "$2" = "$3" ]; then echo "PASS  $1 -> $2"; else echo "FAIL  $1 -> $2 (diharapkan $3)"; FAILS=$((FAILS+1)); fi
}
contains() { # <label> <file> <teks>
  if grep -q "$3" "$2"; then echo "PASS  $1"; else echo "FAIL  $1 (tidak ada: $3)"; FAILS=$((FAILS+1)); fi
}
not_contains() { # <label> <file> <teks>
  if grep -q "$3" "$2"; then echo "FAIL  $1 (seharusnya tidak ada: $3)"; FAILS=$((FAILS+1)); else echo "PASS  $1"; fi
}
token_of() { # <file> -> token CSRF dari meta tag
  grep -o 'name="csrf-token" content="[^"]*"' "$1" | head -n1 | sed 's/.*content="//; s/"//'
}
fetch() { # <path> <outfile> -> status kode (dengan simpan cookie)
  curl -s -b "$COOKIES" -c "$COOKIES" "$BASE$1" -o "$2" -w '%{http_code}'
}

echo "=== 1. Halaman tamu ==="
code=$(fetch /register "$WORK/register.html"); check "GET /register" "$code" "200"
code=$(fetch /login "$WORK/login.html");       check "GET /login" "$code" "200"
contains "form registrasi ada" "$WORK/register.html" 'action="[^"]*/register"'
contains "form login ada" "$WORK/login.html" 'action="[^"]*/login"'
contains "field email tersedia" "$WORK/login.html" 'id="email"'

echo "=== 2. Registrasi ==="
TOKEN=$(token_of "$WORK/register.html")
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -c "$COOKIES" -X POST "$BASE/register" \
  --data-urlencode "_token=$TOKEN" \
  --data-urlencode "name=Mahasiswa Uji Coba" \
  --data-urlencode "email=$EMAIL" \
  --data-urlencode "password=password123" \
  --data-urlencode "password_confirmation=password123")
check "POST /register (akun baru, otomatis login)" "$code" "302"

echo "=== 3. Dashboard ==="
code=$(fetch /dashboard "$WORK/dash.html"); check "GET /dashboard" "$code" "200"
contains "menyapa mahasiswa" "$WORK/dash.html" "Mahasiswa Uji Coba"
contains "tombol Tambah Tugas" "$WORK/dash.html" "Tambah Tugas"
contains "ringkasan: belum selesai" "$WORK/dash.html" "Belum selesai"
contains "ringkasan: segera deadline" "$WORK/dash.html" "Segera deadline"
contains "ringkasan: sudah selesai" "$WORK/dash.html" "Sudah selesai"
contains "seksi tugas terdekat" "$WORK/dash.html" "Tugas terdekat"

echo "=== 4. Tambah tugas (contoh spesifikasi) ==="
TOKEN=$(token_of "$WORK/dash.html")
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -c "$COOKIES" -X POST "$BASE/tasks" \
  --data-urlencode "_token=$TOKEN" \
  --data-urlencode "title=Makalah Cybercrime" \
  --data-urlencode "subject=Keamanan Komputer" \
  --data-urlencode "description=Membuat makalah tentang cybercrime" \
  --data-urlencode "lecturer=Dr. Andi Pratama" \
  --data-urlencode "deadline_date=2026-10-10" \
  --data-urlencode "deadline_time=10:00" \
  --data-urlencode "priority=high" \
  --data-urlencode "reminder_minutes=1440")
check "POST /tasks" "$code" "302"

echo "=== 5. Daftar tugas + filter ==="
code=$(fetch /tasks "$WORK/tasks.html"); check "GET /tasks" "$code" "200"
contains "kartu tugas tampil" "$WORK/tasks.html" "Makalah Cybercrime"
contains "mata kuliah" "$WORK/tasks.html" "Keamanan Komputer"
contains "status awal" "$WORK/tasks.html" "Belum Dikerjakan"
contains "tombol Tandai Selesai" "$WORK/tasks.html" "Tandai Selesai"
contains "tombol Edit" "$WORK/tasks.html" "Edit"
contains "tombol Hapus" "$WORK/tasks.html" "Hapus"
contains "tombol Detail" "$WORK/tasks.html" "Detail"
TASK_ID=$(grep -o '/tasks/[0-9]\+' "$WORK/tasks.html" | head -n1 | grep -o '[0-9]\+')
echo "      id tugas: ${TASK_ID:-KOSONG}"

fetch "/tasks?filter=all" "$WORK/f_all.html" >/dev/null
contains "filter Semua" "$WORK/f_all.html" "Makalah Cybercrime"
fetch "/tasks?filter=high" "$WORK/f_high.html" >/dev/null
contains "filter Prioritas Tinggi" "$WORK/f_high.html" "Makalah Cybercrime"
fetch "/tasks?filter=not_started" "$WORK/f_ns.html" >/dev/null
contains "filter Belum Dikerjakan" "$WORK/f_ns.html" "Makalah Cybercrime"
fetch "/tasks?filter=in_progress" "$WORK/f_ip.html" >/dev/null
not_contains "filter Sedang Dikerjakan kosong" "$WORK/f_ip.html" "Makalah Cybercrime"
fetch "/tasks?filter=done" "$WORK/f_done.html" >/dev/null
not_contains "filter Selesai kosong" "$WORK/f_done.html" "Makalah Cybercrime"

echo "=== 6. Detail tugas ==="
code=$(fetch "/tasks/$TASK_ID" "$WORK/show.html"); check "GET /tasks/{id}" "$code" "200"
contains "tanggal deadline Indonesia" "$WORK/show.html" "10 Oktober 2026"
contains "jam deadline" "$WORK/show.html" "10:00"
contains "waktu pengingat" "$WORK/show.html" "1 hari sebelum deadline"
contains "prioritas" "$WORK/show.html" "Prioritas Tinggi"
contains "dosen" "$WORK/show.html" "Dr. Andi Pratama"
contains "status tugas" "$WORK/show.html" "Belum Dikerjakan"

echo "=== 7. Form edit tersedia ==="
code=$(fetch "/tasks/$TASK_ID/edit" "$WORK/edit.html"); check "GET /tasks/{id}/edit" "$code" "200"
contains "field judul terisi" "$WORK/edit.html" 'value="Makalah Cybercrime"'
contains "pilihan status" "$WORK/edit.html" "Sedang Dikerjakan"

echo "=== 8. Kalender ==="
code=$(fetch "/calendar?month=2026-10" "$WORK/cal.html"); check "GET /calendar Oktober" "$code" "200"
contains "judul bulan" "$WORK/cal.html" "Oktober 2026"
contains "penanda tugas" "$WORK/cal.html" "Makalah Cybercrime"
fetch "/calendar?month=2026-11" "$WORK/cal2.html" >/dev/null
not_contains "bulan lain kosong" "$WORK/cal2.html" "Makalah Cybercrime"
fetch "/calendar?month=basi" "$WORK/cal3.html" >/dev/null
check "parameter bulan tak valid diabaikan" "$?" "0"

echo "=== 9. Pengingat otomatis (inti aplikasi) ==="
DEADLINE=$(app_date 'H:i' 900)
TOKEN=$(token_of "$WORK/tasks.html")
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -c "$COOKIES" -X POST "$BASE/tasks" \
  --data-urlencode "_token=$TOKEN" \
  --data-urlencode "title=Uji Pengingat Segera" \
  --data-urlencode "subject=Pemrograman Web" \
  --data-urlencode "deadline_date=$(app_date 'Y-m-d')" \
  --data-urlencode "deadline_time=$DEADLINE" \
  --data-urlencode "priority=medium" \
  --data-urlencode "reminder_minutes=30")
check "tugas dengan deadline 15 menit lagi (pengingat 30 menit) tersimpan" "$code" "302"

curl -s -b "$COOKIES" -c "$COOKIES" -H 'Accept: application/json' "$BASE/reminders" -o "$WORK/rem.json"
if grep -q '"count":[1-9]' "$WORK/rem.json" && grep -q 'Uji Pengingat Segera' "$WORK/rem.json"; then
  echo "PASS  pengingat muncul otomatis (count>=1, berisi tugas uji)"
else
  echo "FAIL  pengingat tidak muncul: $(tr -d '\n' < "$WORK/rem.json")"; FAILS=$((FAILS+1))
fi
contains "judul tugas di pesan" "$WORK/rem.json" "Uji Pengingat Segera"
contains "mata kuliah di pesan" "$WORK/rem.json" "Pemrograman Web"
contains "relatif waktu" "$WORK/rem.json" "akan deadline"
contains "prioritas di pesan" "$WORK/rem.json" "Prioritas: Sedang"
contains "judul notifikasi" "$WORK/rem.json" "Pengingat Tugas"

RID=$(grep -o '"id":[0-9]\+' "$WORK/rem.json" | head -n1 | grep -o '[0-9]\+')
echo "      id reminder: ${RID:-KOSONG}"
TOKEN=$(token_of "$WORK/tasks.html")
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -X PATCH "$BASE/reminders/$RID/read" \
  --data-urlencode "_token=$TOKEN")
check "PATCH /reminders/{id}/read" "$code" "200"
curl -s -b "$COOKIES" -H 'Accept: application/json' "$BASE/reminders" -o "$WORK/rem_after.json"
if [ -n "$RID" ] && ! grep -q "\"id\":$RID" "$WORK/rem_after.json"; then
  echo "PASS  pengingat yang dibaca hilang dari daftar belum dibaca"
else
  echo "FAIL  $(tr -d '\n' < "$WORK/rem_after.json")"; FAILS=$((FAILS+1))
fi

echo "=== 10. Tandai selesai + filter berubah ==="
TOKEN=$(token_of "$WORK/tasks.html")
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -X PATCH "$BASE/tasks/$TASK_ID/complete" \
  --data-urlencode "_token=$TOKEN")
check "PATCH /tasks/{id}/complete" "$code" "302"
fetch "/tasks?filter=done" "$WORK/done.html" >/dev/null
contains "tugas pindah ke filter Selesai" "$WORK/done.html" "Makalah Cybercrime"
fetch "/tasks?filter=not_started" "$WORK/ns2.html" >/dev/null
not_contains "tugas keluar dari Belum Dikerjakan" "$WORK/ns2.html" "Makalah Cybercrime"
fetch /dashboard "$WORK/dash2.html" >/dev/null
contains "dashboard: counter selesai naik" "$WORK/dash2.html" "Sudah selesai"

echo "=== 11. Pengaturan notifikasi ==="
code=$(fetch /settings "$WORK/settings.html"); check "GET /settings" "$code" "200"
contains "opsi pengingat default tersedia" "$WORK/settings.html" "30 menit sebelum deadline"
contains "toggle notifikasi" "$WORK/settings.html" "Aktifkan notifikasi"
contains "toggle suara" "$WORK/settings.html" "Suara notifikasi"
TOKEN=$(token_of "$WORK/settings.html")
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -X PUT "$BASE/settings" \
  --data-urlencode "_token=$TOKEN" \
  --data-urlencode "notifications_enabled=1" \
  --data-urlencode "sound_enabled=0" \
  --data-urlencode "default_reminder_minutes=30")
check "PUT /settings" "$code" "302"
curl -s -b "$COOKIES" -H 'Accept: application/json' "$BASE/reminders" -o "$WORK/rem2.json"
if grep -q '"sound":false' "$WORK/rem2.json"; then echo "PASS  suara nonaktif tersimpan di API"; else echo "FAIL  $(tr -d '\n' < "$WORK/rem2.json")"; FAILS=$((FAILS+1)); fi

echo "=== 12. Keamanan antar pengguna ==="
curl -s -c "$COOKIES2" "$BASE/register" -o "$WORK/reg2.html" >/dev/null
TOKEN2=$(token_of "$WORK/reg2.html")
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES2" -c "$COOKIES2" -X POST "$BASE/register" \
  --data-urlencode "_token=$TOKEN2" --data-urlencode "name=Mahasiswa Lain" \
  --data-urlencode "email=$EMAIL2" --data-urlencode "password=password123" \
  --data-urlencode "password_confirmation=password123")
check "registrasi pengguna kedua" "$code" "302"
# Token CSRF berputar saat login -> ambil ulang dari halaman setelah login.
TOKEN2=$(curl -s -b "$COOKIES2" -c "$COOKIES2" "$BASE/dashboard" -o "$WORK/dash2b.html" \
  >/dev/null; token_of "$WORK/dash2b.html")
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES2" "$BASE/tasks/$TASK_ID")
check "tugas milik pengguna lain -> 404" "$code" "404"
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES2" -X DELETE "$BASE/tasks/$TASK_ID" \
  --data-urlencode "_token=$TOKEN2")
check "hapus tugas milik pengguna lain -> 404" "$code" "404"

echo "=== 13. Logout ==="
TOKEN=$(token_of "$WORK/dash2.html")
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" -X POST "$BASE/logout" --data-urlencode "_token=$TOKEN")
check "POST /logout" "$code" "302"
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" "$BASE/dashboard")
check "setelah logout, /dashboard dialihkan" "$code" "302"
code=$(curl -s -o /dev/null -w '%{http_code}' -b "$COOKIES" "$BASE/tasks")
check "setelah logout, /tasks dialihkan" "$code" "302"

echo ""
echo "=================================================="
if [ $FAILS -eq 0 ]; then echo "SEMUA PEMERIKSAAN HTTP LULUS ✅"; else echo "GAGAL: $FAILS pemeriksaan ❌"; fi
rm -rf "$WORK"
exit $FAILS

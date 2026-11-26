<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi {{ $session->title }} - KONI Klaten</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 px-4 py-8 text-gray-900">
    <main class="mx-auto max-w-xl space-y-5">
        <header class="rounded-2xl bg-gradient-to-r from-[#8F0010] to-[#E60012] p-6 text-white shadow-lg">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-yellow-200">KONI Kabupaten Klaten</p>
            <h1 class="mt-3 text-2xl font-black">{{ $session->title }}</h1>
            <p class="mt-2 text-sm text-red-100">{{ $session->description ?: 'Formulir konfirmasi kehadiran peserta.' }}</p>
            <div class="mt-5 grid grid-cols-2 gap-3 text-xs">
                <div class="rounded-lg bg-white/10 p-3"><p class="text-red-100">Mulai</p><p class="mt-1 font-bold">{{ $session->starts_at->format('d/m/Y H:i') }}</p></div>
                <div class="rounded-lg bg-white/10 p-3"><p class="text-red-100">Selesai</p><p class="mt-1 font-bold">{{ $session->ends_at->format('d/m/Y H:i') }}</p></div>
            </div>
            <p class="mt-3 text-xs text-red-100">{{ $session->cabangOlahraga?->nama ?? 'Semua cabang olahraga' }}</p>
        </header>

        @if(session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-4">
                <div><p class="text-xs font-bold uppercase tracking-wide text-red-600">Peserta terautentikasi</p><h2 class="mt-1 font-black">{{ auth()->user()->name }}</h2><p class="mt-1 text-xs text-gray-500">{{ auth()->user()->email }}</p></div>
                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700">Login aktif</span>
            </div>

        @if($attendanceAction === 'completed')
            <div class="py-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-2xl text-green-700">✓</div>

                <h3 class="mt-4 text-lg font-black">Absensi selesai</h3>

                <p class="mt-2 text-sm text-gray-500">
                    Check-in tercatat
                    {{ $existingRecord->checked_in_at->format('d/m/Y H:i:s') }}.
                </p>

                @if($session->attendance_mode !== 'training')
                    @if($existingRecord->checked_out_at)
                        <p class="mt-1 text-sm text-gray-500">
                            Check-out tercatat
                            {{ $existingRecord->checked_out_at->format('d/m/Y H:i:s') }}.
                        </p>
                    @else
                        <p class="mt-1 text-sm text-amber-600">
                            Menunggu check-out.
                        </p>
                    @endif
                @else
                    <p class="mt-2 text-sm font-semibold text-green-700">
                        Kehadiran latihan berhasil dicatat. Tidak diperlukan check-out.
                    </p>
                @endif
            </div>
            @elseif(!$canRecord)
                <div class="py-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-2xl text-amber-700">!</div>
                    <h3 class="mt-4 text-lg font-black">Absensi belum tersedia</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        @if($session->status !== 'open')
                            Pengelola sudah menutup sesi ini.
                        @elseif(now()->lt($session->starts_at))
                            Sesi belum dimulai. Silakan kembali saat waktu absensi dibuka.
                        @elseif(now()->gt($session->ends_at))
                            Waktu absensi sudah berakhir.
                        @else
                            Waktu absensi belum tersedia.
                        @endif
                    </p>
                    @if($existingRecord)
                        <p class="mt-3 text-xs text-gray-500">Check-in Anda tercatat {{ $existingRecord->checked_in_at->format('d/m/Y H:i:s') }}, tetapi sesi tidak lagi menerima check-out.</p>
                    @endif
                </div>
            @else
                <form id="attendance-form" method="POST" action="{{ $formAction }}" class="space-y-5 pt-5">
                    @csrf
                    <input type="hidden" name="attendance_action" value="{{ $attendanceAction }}">

                    @if($attendanceAction === 'check_out')
                        <div class="rounded-xl border border-green-100 bg-green-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-green-700">Check-in sudah tercatat</p>
                            <p class="mt-1 text-sm font-semibold text-green-900">{{ $existingRecord->checked_in_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    @endif

                    <div>
                        <h3 class="font-black">
                            @if(auth()->user()->hasRole('staff_koni'))
                                {{ $attendanceAction === 'check_in' ? 'Ambil selfie check-in' : 'Ambil selfie check-out' }}
                            @else
                                {{ $attendanceAction === 'check_in' ? 'Ambil foto selfie untuk check-in' : 'Ambil foto selfie untuk check-out' }}
                            @endif
                        </h3>
                        <p class="mt-1 text-xs leading-5 text-gray-500">QR absensi sudah tervalidasi. Kamera depan akan dicoba otomatis. Ambil selfie, periksa hasilnya, lalu tekan tombol centang untuk menyimpan kehadiran.</p>
                    </div>

                    <div class="overflow-hidden rounded-xl bg-gray-950">
                        <video id="camera-preview" autoplay playsinline muted class="aspect-[4/3] w-full object-cover"></video>
                        <canvas id="capture-canvas" class="hidden"></canvas>
                        <img id="selfie-preview" alt="Pratinjau selfie" class="hidden aspect-[4/3] w-full object-cover">
                    </div>
                    <input type="hidden" id="selfie-data" name="selfie_data">
                    <input type="file" id="device-camera-file" accept="image/*" capture="user" class="hidden">
                    <p id="camera-status" class="text-xs text-gray-500" role="status">Menyiapkan kamera depan...</p>

                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" id="start-camera" class="rounded-lg border border-gray-300 px-3 py-3 text-xs font-bold text-gray-700">Aktifkan Ulang</button>
                        <button type="button" id="capture-selfie" disabled class="rounded-lg bg-gray-300 px-3 py-3 text-xs font-bold text-gray-500">Ambil Selfie</button>
                        <button type="button" id="device-camera-button" class="rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-xs font-bold text-red-700">Kamera HP</button>
                    </div>

                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-xs leading-5 text-blue-800">Selfie wajib untuk check-in dan check-out. Jika browser tidak mengizinkan kamera otomatis, gunakan tombol Kamera HP. Kamera langsung browser membutuhkan HTTPS atau localhost.</div>

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" id="retake-selfie" class="hidden rounded-lg border border-gray-300 px-4 py-3 text-sm font-bold text-gray-700">Ambil Ulang</button>
                        <button id="submit-attendance" type="submit" disabled class="col-span-2 w-full rounded-lg bg-[#E60012] px-5 py-3.5 text-sm font-black text-white opacity-50">✓ {{ $attendanceAction === 'check_in' ? 'Konfirmasi Check-in' : 'Konfirmasi Check-out' }}</button>
                    </div>
                </form>
            @endif
        </section>
        <p class="text-center text-xs text-gray-400">KONI Kabupaten Klaten · Sistem Absensi Digital</p>
    </main>

    @if($canRecord)
        <script>
            (() => {
                const video = document.getElementById('camera-preview');
                const canvas = document.getElementById('capture-canvas');
                const preview = document.getElementById('selfie-preview');
                const selfieInput = document.getElementById('selfie-data');
                const status = document.getElementById('camera-status');
                const startButton = document.getElementById('start-camera');
                const captureButton = document.getElementById('capture-selfie');
                const deviceCameraButton = document.getElementById('device-camera-button');
                const deviceCameraInput = document.getElementById('device-camera-file');
                const submitButton = document.getElementById('submit-attendance');
                const retakeButton = document.getElementById('retake-selfie');
                let stream = null;

                const stopCamera = () => {
                    if (stream) {
                        stream.getTracks().forEach((track) => track.stop());
                        stream = null;
                    }
                };

                const useSelfie = (imageData) => {
                    selfieInput.value = imageData;
                    preview.src = imageData;
                    preview.classList.remove('hidden');
                    video.classList.add('hidden');
                    stopCamera();
                    captureButton.disabled = true;
                    retakeButton.classList.remove('hidden');
                    submitButton.classList.remove('col-span-2');
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50');
                    status.textContent = 'Periksa pratinjau selfie. Tekan tombol centang untuk mengonfirmasi absensi.';
                };

                const convertImageToJpeg = (file) => new Promise((resolve, reject) => {
                    const objectUrl = URL.createObjectURL(file);
                    const image = new Image();

                    image.onload = () => {
                        const scale = Math.min(1, 960 / Math.max(image.naturalWidth, image.naturalHeight));
                        const width = Math.max(1, Math.round(image.naturalWidth * scale));
                        const height = Math.max(1, Math.round(image.naturalHeight * scale));
                        canvas.width = width;
                        canvas.height = height;
                        const context = canvas.getContext('2d');

                        if (!context) {
                            URL.revokeObjectURL(objectUrl);
                            reject(new Error('Canvas tidak tersedia.'));
                            return;
                        }

                        context.drawImage(image, 0, 0, width, height);
                        const imageData = canvas.toDataURL('image/jpeg', 0.78);
                        URL.revokeObjectURL(objectUrl);
                        resolve(imageData);
                    };

                    image.onerror = () => {
                        URL.revokeObjectURL(objectUrl);
                        reject(new Error('Foto tidak dapat dibaca browser.'));
                    };

                    image.src = objectUrl;
                });

                deviceCameraButton.addEventListener('click', () => deviceCameraInput.click());

                deviceCameraInput.addEventListener('change', async () => {
                    const file = deviceCameraInput.files?.[0];
                    if (!file) return;

                    if (file.type && !file.type.startsWith('image/')) {
                        status.textContent = 'File harus berupa foto.';
                        deviceCameraInput.value = '';
                        return;
                    }

                    try {
                        const imageData = await convertImageToJpeg(file);
                        useSelfie(imageData);
                        deviceCameraInput.value = '';
                    } catch (error) {
                        status.textContent = 'Foto gagal dibaca. Coba ambil selfie kembali menggunakan kamera HP.';
                    }
                });

                const startCamera = async () => {
                    if (!navigator.mediaDevices?.getUserMedia || !window.isSecureContext) {
                        status.textContent = 'Kamera langsung memerlukan HTTPS atau localhost. Tekan Kamera HP untuk mengambil foto melalui kamera perangkat.';
                        return false;
                    }

                    try {
                        stopCamera();
                        selfieInput.value = '';
                        submitButton.disabled = true;
                        submitButton.classList.add('opacity-50');
                        retakeButton.classList.add('hidden');
                        submitButton.classList.add('col-span-2');
                        stream = await navigator.mediaDevices.getUserMedia({
                            audio: false,
                            video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 960 } }
                        });
                        video.srcObject = stream;
                        video.classList.remove('hidden');
                        preview.classList.add('hidden');
                        await video.play();
                        captureButton.disabled = false;
                        captureButton.className = 'rounded-lg bg-gray-900 px-3 py-3 text-xs font-bold text-white';
                        status.textContent = 'Kamera aktif. Ambil selfie untuk melanjutkan.';
                        return true;
                    } catch (error) {
                        status.textContent = 'Kamera otomatis tidak dapat diakses. Izinkan akses kamera atau gunakan tombol Kamera HP.';
                        return false;
                    }
                };

                startButton.addEventListener('click', startCamera);
                retakeButton.addEventListener('click', startCamera);

                captureButton.addEventListener('click', () => {
                    if (!stream || !video.videoWidth || !video.videoHeight) {
                        status.textContent = 'Kamera belum siap. Aktifkan kamera terlebih dahulu.';
                        return;
                    }

                    const width = Math.min(video.videoWidth, 960);
                    const height = Math.round(video.videoHeight * (width / video.videoWidth));
                    canvas.width = width;
                    canvas.height = height;
                    const context = canvas.getContext('2d');
                    context.drawImage(video, 0, 0, width, height);
                    useSelfie(canvas.toDataURL('image/jpeg', 0.78));
                });

                document.getElementById('attendance-form').addEventListener('submit', (event) => {
                    if (!selfieInput.value) {
                        event.preventDefault();
                        status.textContent = 'Ambil selfie terlebih dahulu sebelum mengirim absensi.';
                    } else {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Mengirim...';
                    }
                });

                window.addEventListener('pagehide', stopCamera);

                // The QR has already been validated before this page loads. Try to
                // open the front camera immediately; insecure LAN HTTP falls back
                // to the explicit native camera button.
                startCamera();
            })();
        </script>
    @endif
</body>
</html>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Pendaftaran Magang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            blue: '#4f6c96',      /* Biru teks judul */
                            lightblue: '#e6edf5', /* Biru muda background section */
                            orange: '#d96a47',    /* Oranye tombol */
                            text: '#8295b0'       /* Warna teks abu-abu kebiruan */
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-white text-gray-700 antialiased">

    <nav class="flex justify-between items-center py-4 px-10 bg-brand-lightblue/50">
        <div class="flex items-center">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="h-10">
        </div>
        <div class="hidden md:flex space-x-8 font-medium text-brand-text">
            <a href="#home" class="hover:text-brand-blue font-semibold text-brand-blue">Home</a>
            <a href="#cek-kuota" class="hover:text-brand-blue">Cek Kuota</a>
            <a href="#alur" class="hover:text-brand-blue">Prosedur Magang</a>
            <a href="#tentang" class="hover:text-brand-blue">Tentang</a>
            <a href="#kontak" class="hover:text-brand-blue">Kontak</a>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('register') }}"
                class="bg-brand-orange text-white px-6 py-2 rounded shadow-md font-medium hover:bg-orange-600 transition">Daftar</a>
            <a href="{{ route('login') }}"
                class="border border-gray-300 text-brand-blue px-6 py-2 rounded shadow-sm font-medium hover:bg-gray-50 transition">Login</a>
        </div>
    </nav>

    <section id="home"
        class="relative bg-brand-lightblue/50 px-10 pt-12 pb-0 md:pt-24 md:pb-0 flex items-center min-h-[400px]">

        <!-- Konten Teks (Tetap Desain Asli) -->
        <div class="md:w-1/2 space-y-4 relative z-10">
            <h1 class="text-4xl font-bold text-brand-blue leading-tight">
                Selamat Datang di Portal<br>
                Pendaftaran Magang Mahasiswa<br>
                [Nama Perusahaan]
            </h1>
            <p class="text-brand-blue font-medium text-lg">
                Website Pendaftaran Magang Untuk Mahasiswa
            </p>
            <a href="{{ route('register') }}"
                class="inline-block bg-brand-orange text-white px-8 py-3 rounded shadow-lg font-semibold hover:bg-orange-600 transition mt-4">
                Daftar Sekarang
            </a>
        </div>

        <!-- Gambar Melayang di Desktop (Menggunakan Lebar Custom w-[800px]) -->
        <div class="hidden md:block absolute right-10 bottom-[-210px] pointer-events-none">
            <img src="{{ asset('img/landingpage.png') }}" alt="Ilustrasi Mahasiswa Magang" class="w-[500px] h-auto object-contain">
        </div>

    </section>

    <section id="cek-kuota" class="px-10 py-10">
        <div class="bg-brand-lightblue rounded-xl p-8 text-center">
            <h2 class="text-2xl font-bold text-brand-blue mb-6">Cek Ketersediaan Kuota Magang</h2>

            <div class="flex flex-col md:flex-row justify-center gap-4 mb-6">
                <div class="relative md:w-1/3">
                    <select id="dept_id"
                        class="w-full bg-gray-200 text-gray-700 py-3 px-4 rounded appearance-none focus:outline-none">
                        <option value="">Pilih Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-caret-down absolute right-4 top-4 text-gray-500 pointer-events-none"></i>
                </div>
                <div class="relative md:w-1/4">
                    <input type="date" id="start_date" placeholder="Tanggal masuk"
                        class="w-full bg-gray-200 text-gray-700 py-3 px-4 rounded focus:outline-none">
                </div>
                <div class="relative md:w-1/4">
                    <input type="date" id="end_date" placeholder="Tanggal selesai"
                        class="w-full bg-gray-200 text-gray-700 py-3 px-4 rounded focus:outline-none">
                </div>
                <div class="md:w-auto flex items-stretch">
                    <button onclick="checkQuota()" id="btn-check"
                        class="w-full md:w-auto bg-brand-orange text-white px-8 py-2 rounded shadow-md font-medium hover:bg-orange-600 transition">
                        Cek Kuota
                    </button>
                </div>
            </div>

            <div id="result-area" class="hidden animate-fade-in mt-8">
                <div id="result-box" class="p-6 rounded-2xl inline-block min-w-[350px] shadow-sm backdrop-blur-sm transition-all duration-300">
                    <div class="flex items-center justify-center gap-3 mb-2">
                        <i id="result-icon" class="text-xl"></i>
                        <p id="result-message" class="font-bold text-lg"></p>
                    </div>
                    <div id="result-detail-box" class="px-4 py-2 rounded-full bg-white/50 text-xs font-medium inline-block">
                        <p id="result-detail"></p>
                    </div>
                </div>
            </div>

            <p class="text-brand-blue text-sm mt-4">
                Silahkan cek ketersediaan kuota maksimal 5 bulan
            </p>
        </div>
    </section>

    <section id="alur" class="px-10 py-24 bg-brand-lightblue/50">
        <h2 class="text-2xl font-bold text-brand-blue text-center mb-12">Alur Pendaftaran Magang</h2>

        <div class="flex flex-col md:flex-row items-start justify-center text-center gap-4 relative">

            <div class="flex-1 flex flex-col items-center">
                <div
                    class="w-16 h-16 bg-brand-blue text-white rounded-full flex items-center justify-center text-2xl font-bold mb-4">
                    1</div>
                <h3 class="font-bold text-black mb-2">Registrasi</h3>
                <p class="text-xs text-gray-500">Lorem ipsum dolor sit amet consectetur. Ac in sed orci suspendisse.
                    Amet dictum semper pellentesque ante urna.</p>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-500 hidden md:block mt-5"></i>

            <div class="flex-1 flex flex-col items-center">
                <div
                    class="w-16 h-16 bg-brand-blue text-white rounded-full flex items-center justify-center text-2xl font-bold mb-4">
                    2</div>
                <h3 class="font-bold text-black mb-2">Verifikasi</h3>
                <p class="text-xs text-gray-500">Lorem ipsum dolor sit amet consectetur. Vel sagittis in nunc massa
                    molestie. Sem cursus quis sit habitasse mauris neque sed fermentum.</p>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-500 hidden md:block mt-5"></i>

            <div class="flex-1 flex flex-col items-center">
                <div
                    class="w-16 h-16 bg-brand-blue text-white rounded-full flex items-center justify-center text-2xl font-bold mb-4">
                    3</div>
                <h3 class="font-bold text-black mb-2">Administrasi<br>Lanjutan</h3>
                <p class="text-xs text-gray-500">Lorem ipsum dolor sit amet consectetur. Lorem enim non feugiat amet
                    augue rhoncus. Faucibus erat vitae tempor turpis sit.</p>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-500 hidden md:block mt-5"></i>

            <div class="flex-1 flex flex-col items-center">
                <div
                    class="w-16 h-16 bg-brand-blue text-white rounded-full flex items-center justify-center text-2xl font-bold mb-4">
                    4</div>
                <h3 class="font-bold text-black mb-2">Pelaksanaan</h3>
                <p class="text-xs text-gray-500">Lorem ipsum dolor sit amet consectetur. Scelerisque volutpat commodo
                    amet leo leo ut hendrerit nisl. Cursus felis ultrices eget commodo tellus a commodo.</p>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-500 hidden md:block mt-5"></i>

            <div class="flex-1 flex flex-col items-center">
                <div
                    class="w-16 h-16 bg-brand-blue text-white rounded-full flex items-center justify-center text-2xl font-bold mb-4">
                    5</div>
                <h3 class="font-bold text-black mb-2">Sertifikasi</h3>
                <p class="text-xs text-gray-500">Lorem ipsum dolor sit amet consectetur. Sed pharetra vitae et porta. Eu
                    sem risus sem potenti id cras.</p>
            </div>

        </div>
    </section>

    <section id="tentang" class="px-10 py-24 text-center max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold text-brand-blue mb-4">Tentang</h2>
        <p class="text-xs text-gray-500 leading-relaxed">
            Lorem ipsum dolor sit amet consectetur. Nibh neque tortor tempus ultrices nec pharetra feugiat. Massa
            faucibus cursus venenatis purus tellus. Tempor orci nunc pretium id diam nulla. Felis euismod amet orci odio
            pellentesque mattis pharetra placerat consectetur. Ullamcorper in netus euismod ipsum. Ac egestas tellus
            venenatis in consequat.
        </p>
    </section>

    <footer id="kontak" class="bg-brand-lightblue px-10 py-12 flex flex-col md:flex-row justify-between items-start">
        <div class="md:w-1/2 space-y-8">
            <div>
                <h3 class="text-xl font-bold text-brand-blue mb-4">Contact Person</h3>
                <div class="space-y-2 text-sm font-medium">
                    <p class="flex items-center gap-3"><i class="fa-solid fa-envelope text-lg"></i> example@gmail.com
                    </p>
                    <p class="flex items-center gap-3"><i class="fa-solid fa-phone text-lg"></i> +62 8** **** ****</p>
                </div>
            </div>

            <div>
                <h3 class="text-xl font-bold text-brand-blue mb-4">Sosial Media</h3>
                <div class="flex gap-4 text-2xl">
                    <a href="#" class="text-black hover:text-brand-blue transition"><i
                            class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="text-black hover:text-brand-blue transition"><i
                            class="fa-brands fa-twitter"></i></a>
                </div>
            </div>
        </div>

        <div class="md:w-1/2 w-full mt-8 md:mt-0 flex justify-end">
            <div
                class="w-full max-w-md bg-gray-300 h-64 flex items-center justify-center text-gray-600 font-semibold tracking-widest rounded">
                MAPS
            </div>
        </div>
    </footer>

    <script>
        async function checkQuota() {
            const deptId = document.getElementById('dept_id').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const btn = document.getElementById('btn-check');
            const resultArea = document.getElementById('result-area');
            const resultBox = document.getElementById('result-box');
            const resultMessage = document.getElementById('result-message');
            const resultDetail = document.getElementById('result-detail');

            if (!deptId || !startDate || !endDate) {
                alert('Silakan lengkapi semua data pencarian.');
                return;
            }

            // Reset UI
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch animate-spin mr-2"></i> Mengecek...';
            resultArea.classList.add('hidden');

            try {
                const response = await fetch(`{{ route('quota.check') }}?department_id=${deptId}&start_date=${startDate}&end_date=${endDate}`);
                const data = await response.json();

                if (data.success) {
                    resultArea.classList.remove('hidden');
                    resultMessage.innerText = data.message;
                    const icon = document.getElementById('result-icon');
                    
                    if (data.available > 0) {
                        resultBox.className = 'p-6 rounded-2xl inline-block min-w-[350px] shadow-sm bg-white border border-brand-blue/10 text-brand-blue';
                        icon.className = 'fa-solid fa-circle-check text-green-500';
                        resultDetail.innerText = `Kapasitas: ${data.quota} | Terisi: ${data.used} | Sisa: ${data.available} Slot`;
                    } else {
                        resultBox.className = 'p-6 rounded-2xl inline-block min-w-[350px] shadow-sm bg-white border border-brand-orange/10 text-brand-orange';
                        icon.className = 'fa-solid fa-circle-exclamation text-brand-orange';
                        resultDetail.innerText = `Maaf, kapasitas ${data.quota} orang sudah penuh.`;
                    }
                } else {
                    alert('Gagal mengecek kuota. Silakan coba lagi.');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan pada server.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Cek Kuota';
            }
        }
    </script>
</body>

</html>

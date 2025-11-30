<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - QuickFix</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen flex flex-col">

    <!-- TOPBAR -->
    <header class="bg-white shadow-md py-4 px-6 flex justify-between items-center border-b">
        <h1 class="text-2xl font-extrabold text-blue-600 tracking-wide">
            Dashboard Admin
        </h1>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg font-semibold shadow transition-all">
                Logout
            </button>
        </form>
    </header>

    <!-- CONTENT -->
    <main class="flex-1 p-8">
        <div class="bg-white p-8 rounded-xl shadow-lg border">

            <div class="flex justify-between items-center mb-5">
                <h2 class="text-xl font-bold text-blue-700">
                    Daftar Perusahaan
                </h2>

                <span class="text-sm text-gray-500">
                    Terakhir diperbarui: {{ now()->format('d M Y') }}
                </span>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-300 shadow-sm">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-blue-600 text-white">
                            <th class="p-3">Nama Perusahaan</th>
                            <th class="p-3">Jumlah Member</th>
                            <th class="p-3">Jenis Paket</th>
                            <th class="p-3">Add-ons</th>
                            <th class="p-3">Status Pembayaran</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white">

                        <!-- Contoh Data -->
                        <tr class="border-b hover:bg-blue-50 transition">
                            <td class="p-4 font-medium">PT. Sukses Makmur</td>
                            <td class="p-4">42</td>
                            <td class="p-4">Enterprise</td>
                            <td class="p-4">API Access</td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold text-sm">
                                    Aktif
                                </span>
                            </td>
                        </tr>

                        <tr class="border-b hover:bg-blue-50 transition">
                            <td class="p-4 font-medium">CV. Jaya Sentosa</td>
                            <td class="p-4">12</td>
                            <td class="p-4">Basic</td>
                            <td class="p-4">-</td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 font-semibold text-sm">
                                    Trial
                                </span>
                            </td>
                        </tr>

                        <tr class="border-b hover:bg-blue-50 transition">
                            <td class="p-4 font-medium">PT. Media Karya</td>
                            <td class="p-4">58</td>
                            <td class="p-4">Pro</td>
                            <td class="p-4">Custom Branding</td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 font-semibold text-sm">
                                    Trial Habis
                                </span>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>

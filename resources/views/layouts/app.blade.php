<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Manajemen User - Bignet</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <script src="https://kit.fontawesome.com/52c63e43bb.js" crossorigin="anonymous"></script>


    <!-- Scripts -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-4343e92e.css') }}">
    <style>
        td {
            border-top: 1px solid var(--bs-border-color) !important;
            border-bottom: none !important;
        }
    </style>
</head>

<body>
    <div id="app">
        @auth
            <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                <div class="container">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="/">Data User</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/transaksi">Riwayat Transaksi</a>
                            </li>
                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a href="javascript:;" class="dropdown-item" onclick="logout()">Logout</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <main class="py-4">
                @yield('content')
            </main>
        @else
            @yield('content')
        @endauth


    </div>
    <script src="{{ asset('build/assets/app-4e78b1cc.js') }}"></script>
    <script>
        const appUrl = `{{ env('APP_URL') }}`

        function debounce(func, delay) {
            let timeoutId;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    func.apply(context, args);
                }, delay);
            };
        }

        function formatUang(angka) {
            if (angka == 0) {
                return '-'
            }
            const formattedAngka = angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            return `Rp. ${formattedAngka}`;
        }

        function logout() {
            Alert.fire({
                icon: 'warning',
                title: 'Keluar',
                text: 'Apakah Anda yakin ingin Logout?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return axios.post(`${appUrl}/logout`)
                        .then(response => {
                            window.location = `${appUrl}/login`
                        })
                        .catch(error => {
                            Alert.fire({
                                    icon: 'error',
                                    text: error.response.data.message,
                                    toast: true,
                                    position: 'top-end',
                                    timer: 1500,
                                    showConfirmButton: false
                                })
                                .then(() => {
                                    window.location.reload()
                                });
                        });
                }
            });
        }
    </script>
    @stack('js')
</body>

</html>

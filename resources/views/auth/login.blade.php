@extends('layouts.app')

@section('content')
    <div class="container vh-100">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-md-6 col-lg-4">
                <div class="card card-body">
                    <form id="form" novalidate>
                        <div class="alert alert-danger d-none" role="alert">
                            Username atau password salah
                        </div>
                        <div class="form-group mb-3">
                            <input type="text" id="username" data-name="Username" class="form-control"
                                placeholder="Username" autocomplete="username" autofocus>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-3">
                            <input type="password" id="password" class="form-control" placeholder="Password" autocomplete="current-password">
                            <div class="invalid-feedback"></div>
                        </div>
                        <button class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        const form = document.getElementById('form');
        const alert = form.querySelector('.alert');
        const input = form.querySelectorAll('input');

        let isLoading = false;
        form.addEventListener('submit', e => {
            e.preventDefault();
            btn = form.querySelector('button');
            btn.disabled = true;
            btn.innerHTML = 'Tunggu sebentar...';

            username = form.querySelector('#username');
            password = form.querySelector('#password');
            axios.post(`${appUrl}/login`, {
                    username: username.value,
                    password: password.value
                })
                .then(response => {
                    resetInput();
                    console.log(response.data);
                    window.location = `${appUrl}/`
                })
                .catch(error => {
                    resetInput();
                    if (error.response.status == 422) {
                        errors = error.response.data.errors
                        console.log(errors);
                        Object.keys(errors).forEach(key => {
                            invalidateInput(key, errors[key])
                        });
                    }
                    if (error.response.status == 401) {
                        alert.classList.remove('d-none')
                        alert.innerHTML = error.response.data.message
                    }
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Login';
                })
        })

        function invalidateInput(id, errors) {
            const el = document.getElementById(id)
            el.classList.add('is-invalid');
            let ul = '<ul>'
            errors.forEach(error => {
                ul += `<li>${error}</li>`
            });
            ul += "</ul>"
            el.nextElementSibling.innerHTML = ul;
        }

        function resetInput() {
            alert.classList.add('d-none')
            input.forEach(el => {
                el.classList.remove('is-invalid');
            })
        }
    </script>
@endpush

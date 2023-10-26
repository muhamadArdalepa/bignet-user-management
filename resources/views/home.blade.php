@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex align-items-center mb-4 gap-2">
            <h1 class="fs-4 m-0">Data User Bignet</h1>
            <select id="region_id" class="form-select form-select-lg" style="width: 10rem">
                @foreach (\App\Models\Region::all() as $region)
                    <option value="{{ $region->id }}" {{ request()->r == $region->id ? 'selected' : '' }}>
                        {{ $region->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-light ms-auto"><i class="fa-solid fa-file-arrow-up"></i></button>
            <button class="btn btn-light"><i class="fa-solid fa-file-arrow-down"></i></button>
            <button class="btn btn-primary">Tambah User</button>
        </div>

        <form id="searchForm" class="mb-3">
            <div class="d-flex gap-3 align-items-center mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input id="searchbox" type="search" class="form-control" placeholder="Cari user">
                </div>
                <a href="#offcanvasFilter" data-bs-toggle="offcanvas" role="button" class="btn btn-light flex-shrink-0">
                    <i class="fa-solid fa-filter me-2"></i>
                    Filter
                </a>
            </div>
        </form>

        <div class="d-flex mb-3 align-items-center gap-3">
            <span class="fs-5 flex-shrink-0" id="row-length">25 User</span>

            <div class="d-flex align-items-center gap-3 overflow-auto w-100" id="tag-container">

            </div>
            <button class="btn btn-outline-primary btn-sm flex-shrink-0 date-tag" onclick="removeDate()">
                <span id="gLabel"></span>
                <i class="fa-solid fa-xmark ms-2"></i>
            </button>
        </div>
        <div class="border rounded-3 overflow-auto" id="main-card">
            <div class="p-3 d-flex gap-2 align-items-center bg-white position-relative" id="menubar"
                style="z-index: 0;transition: margin-bottom 150ms ease-in-out">
                <div>Terpilih <strong id="selectedUser">0</strong> User</div>
                <div class="ms-auto">
                    <button class="btn btn-primary">Cetak kwitansi</button>
                    <button class="btn btn-success btn-aktifkan-batch" onclick="handleBatch(1)">Aktifkan</button>
                    <button class="btn btn-danger btn-isolir-batch" onclick="handleBatch(2)">Isolir</button>
                </div>
            </div>
            <table class="table bg-white position-relative" style="z-index: 2">
                <thead>
                    <tr class="">
                        <th class="bg-white">
                            <div class="d-flex gap-3 align-items-center">
                                <input type="checkbox" class="form-check" id="check-all">
                                <label style="cursor: pointer" for="check-all" class="w-100">ID User</label>
                            </div>
                        </th>
                        <th>Nomor VA</th>
                        <th>Nama</th>
                        <th>Tanggal Pasang</th>
                        <th>Tagihan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tbody">

                </tbody>
            </table>
        </div>
    </div>
@endsection
@push('js')
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFilter" aria-labelledby="offcanvasFilterLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasFilterLabel">Filter Pencarian</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <p>SERVER</p>
            <div class="d-flex gap-2 flex-column mb-3">
                @foreach (\App\Models\Server::all() as $server)
                    <input type="checkbox" name="v" class="btn-check" data-id="{{ $server->id }}"
                        id="v{{ $server->id }}" autocomplete="off">
                    <label for="v{{ $server->id }}" class="btn text-start btn-outline-primary w-100">
                        {{ $server->name }}
                    </label>
                @endforeach
            </div>
            <hr class="">
            <p>STATUS</p>
            <div class="d-flex gap-2 flex-column mb-3">
                @php
                    $statuses = ['Aktif', 'Isolir', 'Tidak aktif'];
                @endphp
                @foreach ($statuses as $i => $status)
                    <input type="checkbox" name="i" class="btn-check" data-id="{{ $i + 1 }}"
                        id="i{{ $i + 1 }}" autocomplete="off">
                    <label for="i{{ $i + 1 }}" class="btn text-start btn-outline-primary w-100">
                        {{ $status }}
                    </label>
                @endforeach
            </div>
            <hr class="">
            <p>TANGGAL PASANG</p>
            <div class="d-flex gap-2 flex-column mb-3">
                <select id="filter_date" class="form-select">
                    <option value="">Tampilkan semua</option>
                    @for ($i = 1; $i <= 31; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="mt-5 text-end ">
                <button class="btn btn-light" id="btn-reset-filter">Reset</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bayarModal" tabindex="-1" aria-labelledby="bayarModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">

            </div>
        </div>
    </div>

    <script>
        const menubar = document.getElementById('menubar');
        const menubarHeight = menubar.offsetHeight;
        const selectedUser = [];

        const tagContainer = document.getElementById('tag-container');
        const tags = [];

        const tbody = document.getElementById('tbody');

        const sEl = document.getElementById('searchbox');
        const rEl = document.getElementById('region_id');
        const vEl = document.querySelectorAll('[name="v"]');
        const iEl = document.querySelectorAll('[name="i"]');
        const gEl = document.getElementById('filter_date');

        const url = new URL(window.location.href);
        let s = url.searchParams.get('s');
        let r = url.searchParams.get('r') ?? 1;
        let v = url.searchParams.getAll('v[]');
        let i = url.searchParams.getAll('i[]');
        let g = url.searchParams.get('g') ?? `{{ date('j') }}`;

        document.addEventListener('DOMContentLoaded', () => {
            sEl.value = s;
            rEl.value = r == '' ? 1 : r;
            v.forEach(item => {
                temp = document.getElementById(`v${item}`)
                temp.checked = true;
                tagContainer.insertAdjacentHTML('beforeend', tag(temp.id, temp.nextElementSibling
                    .textContent))
            })

            i.forEach(item => {
                temp = document.getElementById(`i${item}`)
                temp.checked = true;
                tagContainer.insertAdjacentHTML('beforeend', tag(temp.id, temp.nextElementSibling
                    .textContent))
            })

            gEl.value = g
            document.getElementById('gLabel').textContent = 'Tanggal ' + g
            performSearch()
        })

        rEl.addEventListener('change', e => {
            r = e.target.value;
            performSearch()
        })

        sEl.addEventListener('input', debounce(performSearch, 300))

        vEl.forEach(item => {
            item.addEventListener('change', e => {
                if (v.includes(e.target.dataset.id)) {
                    const index = v.indexOf(e.target.dataset.id);
                    v.splice(index, 1);
                    document.querySelector(`button[data-id="${item.id}"]`).remove()
                } else {
                    v.push(e.target.dataset.id);
                    tagContainer.insertAdjacentHTML('beforeend', tag(item.id, item.nextElementSibling
                        .textContent))
                }
                performSearch()
            })
        })

        iEl.forEach(item => {
            item.addEventListener('change', e => {
                if (i.includes(e.target.dataset.id)) {
                    const index = i.indexOf(e.target.dataset.id);
                    i.splice(index, 1);
                    document.querySelector(`button[data-id="${item.id}"]`).remove()
                } else {
                    i.push(e.target.dataset.id);
                    tagContainer.insertAdjacentHTML('beforeend', tag(item.id, item.nextElementSibling
                        .textContent))
                }
                performSearch()
            })
        })

        gEl.addEventListener('change', e => {
            g = e.target.value
            if (g == '') {
                document.querySelector('.date-tag').classList.add('d-none');
            } else {
                document.querySelector('.date-tag').classList.remove('d-none');
                document.getElementById('gLabel').textContent = 'Tanggal ' + g
            }

            performSearch()
        })

        document.getElementById('btn-reset-filter').addEventListener('click', e => {
            window.location.href = appUrl
        })

        const loader = `<tr id="loader">
                <td colspan="6">
                    <div class="text-center">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        Mendapatkan data
                    </div>
                </td>
            </tr>`

        const tr = (data) => {
            return `<tr>
                    <td>
                        <div class="d-flex gap-3 align-items-stretch">
                            <input type="checkbox" class="form-check check-user" data-id="${data.id}" id="select${data.id}" style="cursor:pointer">
                            <label for="select${data.id}" style="cursor:pointer" class="w-100">${data.id}</label>
                        </div>
                    </td>
                    <td>${data.va}</td>
                    <td style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#collapse${data.id}"> <a href="javascript:;" class="text-decoration-none">${data.nama}</a></td>
                    <td>${data.tanggal}</td>
                    <td>${formatUang(data.invoice)}</td>
                    <td>
                        <small class="d-inline-flex px-2 py-1 fw-semibold text-${data.status[1]} bg-${data.status[1]} bg-opacity-10 border border-${data.status[1]} border-opacity-10 rounded-2">${data.status[0]}</small>
                    </td>
                </tr>
                <tr class="bg-light">
                    <td colspan="6" class="p-0 border-0">
                        <div class="collapse" id="collapse${data.id}">
                        <div class="p-2">
                                <div class="d-flex gap-5 mb-4 justify-content-between">
                                    <div class="w-100">
                                        <small class="text-muted">Total tagihan</small>
                                        <h3 class="mb-2">${formatUang(data.invoice)}</h3>

                                        <small class="text-muted">Pembayaran Bulanan</small>
                                        <p class="mb-2">${formatUang(data.bulanan)}</p>

                                        <small class="text-muted">Menunggak</small>
                                        <p class="mb-2">${data.tunggakan} Bulan</p>


                                    </div>

                                    <div class="w-100">
                                        <small class="text-muted">Alamat</small>
                                        <p class="mb-2">${data.alamat}</p>

                                        <small class="text-muted">Nomor HP/Whatsapp</small>
                                        <p class="mb-2">${"0" + data.no_telp.substring(2)}</p>

                                        <small class="text-muted">Email</small>
                                        <p class="mb-2">${data.email}</p>
                                    </div>

                                    <div class="w-100">
                                        <small class="text-muted">Mac</small>
                                        <p class="mb-2">${data.mac}</p>

                                        <small class="text-muted">Bandwidth</small>
                                        <p class="mb-2">${data.bandwidth}Mbps</p>

                                        <small class="text-muted">Server</small>
                                        <p class="mb-2">${data.server.name}</p>
                                    </div>


                                </div>
                                <div class="text-end">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bayarModal" onclick="modal${data.invoice > 0 ? 'Bayar' : 'Riwayat'}Show('${data.id}')">${data.invoice > 0 ? 'Bayar' : 'Riwayat Pembayaran'}</button>
                                    <button class="btn btn-primary">Cetak kwitansi</button>
                                    <button class="btn btn-${data.status[0] == 'Aktif' ? 'danger' : 'success'} btn-isolir" data-id="${data.id}" data-nama="${data.nama}" data-status="${data.status[0]}">${data.status[0] == 'Isolir' ? 'Aktifkan' : 'Isolir'}</button>
                                </div>
                            </div>
                    </td>
                </tr>`
        }

        const tag = (id, text) => {
            return `<button class="btn btn-outline-primary btn-sm btn-tag flex-shrink-0" onclick="removeTag(this)" data-id="${id}">
                    ${text}
                    <i class="fa-solid fa-xmark ms-2"></i>
                </button>`
        }

        function removeTag(el) {
            document.getElementById(el.dataset.id).click()
            el.remove()
        }

        function removeDate() {
            gEl.value = '';
            gEl.dispatchEvent(new Event('change'))
            document.querySelector('.date-tag').classList.add('d-none');
        }

        let hasSearchTag = false;
        let isFetching = false;

        function performSearch() {
            if (isFetching) {
                return;
            }
            isFetching = true
            selectedUser.length = 0
            let hasMenuBar = false;
            menubar.style.marginBottom = '-' + menubarHeight + 'px';
            document.getElementById('check-all').checked = false
            tbody.innerHTML = '';
            tbody.insertAdjacentHTML('afterbegin', loader)
            const loading = document.getElementById('loader');
            s = sEl.value
            if (sEl.value != '') {
                if (!hasSearchTag) {
                    tagContainer.insertAdjacentHTML('afterbegin', `<button class="btn btn-outline-primary btn-sm search-tag flex-shrink-0">
                            <span id="searchTagText">"${sEl.value}"</span>
                            <i class="fa-solid fa-xmark ms-2"></i>
                        </button>`)
                    hasSearchTag = true;
                } else {
                    document.querySelector('#searchTagText').innerHTML = '"' + sEl.value + '"'
                }
                if (document.querySelector('.search-tag')) {
                    document.querySelector('.search-tag').addEventListener('click', f => {
                        sEl.value = '';
                        f.target.remove()
                        hasSearchTag = false;
                        performSearch()
                    });
                }
            } else {
                if (document.querySelector('.search-tag')) {
                    document.querySelector('.search-tag').remove()
                    hasSearchTag = false;
                }
            }


            axios.get(`${appUrl}/api/pelanggan?r=${r}&s=${s}&v=${v}&i=${i}&g=${g}`)
                .then(response => {
                    document.getElementById('row-length').textContent = response.data.length + ' User';
                    if (response.data.length > 0) {
                        response.data.forEach(data => {
                            tbody.insertAdjacentHTML('beforeend', tr(data))
                        });
                    } else {
                        tbody.insertAdjacentHTML('beforeend',
                            `<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>`)
                    }
                })
                .catch(error => {
                    tbody.insertAdjacentHTML('beforeend', `<tr><td colspan="7" class="text-center">Error</td></tr>`)
                })
                .finally(response => {
                    // document.querySelector('button[data-bs-target="#bayarModal"]').click()
                    loading.remove();
                    const checkUsers = document.querySelectorAll('.check-user');
                    checkUsers.forEach(check => {
                        check.addEventListener('change', e => {
                            const idUser = e.target.dataset.id;
                            if (selectedUser.includes(idUser)) {
                                const index = selectedUser.indexOf(idUser);
                                selectedUser.splice(index, 1);
                            } else {
                                selectedUser.push(idUser);
                            }
                            if (selectedUser.length > 0 && !hasMenuBar) {
                                menubar.style.marginBottom = 0;
                                hasMenuBar = true
                            }

                            if (selectedUser.length == 0 && hasMenuBar) {
                                menubar.style.marginBottom = '-' + menubarHeight + 'px';
                                hasMenuBar = false
                            }

                            if (checkUsers.length == selectedUser.length) {
                                document.getElementById('check-all').checked = true
                            } else {
                                document.getElementById('check-all').checked = false
                            }

                            document.getElementById('selectedUser').innerHTML = selectedUser.length


                        })
                    })

                    document.getElementById('check-all').addEventListener('click', e => {
                        if (checkUsers.length == selectedUser.length) {
                            checkUsers.forEach(check => {
                                check.click()
                            })
                        } else {
                            checkUsers.forEach(check => {
                                if (!check.checked) {
                                    check.click()
                                }
                            })
                        }
                    })

                    document.querySelectorAll('.btn-isolir').forEach(btn => {
                        btn.addEventListener('click', e => {
                            Alert.fire({
                                icon: 'warning',
                                title: e.target.dataset.status == 'Aktif' ? 'Isolir' :
                                    'Aktivasi',
                                text: `Apakah anda yakin ${e.target.dataset.status == 'Aktif' ? 'isolir' : 'aktifkan'} user ${e.target.dataset.nama}?`,
                                showCancelButton: true,
                                confirmButtonText: 'Ya, lanjutkan!',
                                cancelButtonText: 'Batal',
                                reverseButtons: true,
                                showLoaderOnConfirm: true,
                            }).then(result => {
                                if (result.isConfirmed) {
                                    axios.post(
                                            `${appUrl}/api/pelanggan/isolir/${e.target.dataset.id}`, {
                                                _method: 'PATCH'
                                            })
                                        .then(response => {
                                            performSearch()
                                            Alert.fire({
                                                icon: 'success',
                                                text: response.data.message,
                                                toast: true,
                                                position: "top-end",
                                                timer: 1500,
                                                showConfirmButton: false,
                                            })
                                        })
                                        .catch(error => {
                                            Alert.fire({
                                                    icon: 'error',
                                                    text: 'Terdapat kesalahan dalam memproses data',
                                                    toast: true,
                                                    position: "top-end",
                                                    timer: 1500,
                                                    showConfirmButton: false,
                                                })
                                                .then(() => {
                                                    window.location.reload()
                                                })
                                        })
                                }
                            })
                        })
                    })
                    isFetching = false;
                })

        }

        function handleBatch(status) {
            Alert.fire({
                icon: 'warning',
                title: status == 2 ? 'Isolir' : 'Aktivasi',
                text: `Apakah anda yakin ${status == 2 ? 'isolir' : 'aktifkan'} ${selectedUser.length} user ini?`,
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
            }).then(result => {
                if (result.isConfirmed) {
                    axios.post(
                            `${appUrl}/api/pelanggan/isolir-batch`, {
                                _method: 'PATCH',
                                pelanggans: selectedUser,
                                i: status
                            })
                        .then(response => {
                            performSearch()
                            Alert.fire({
                                icon: 'success',
                                text: response.data.message,
                                toast: true,
                                position: "top-end",
                                timer: 1500,
                                showConfirmButton: false,
                            })
                        })
                        .catch(error => {
                            Alert.fire({
                                    icon: 'error',
                                    text: 'Terdapat kesalahan dalam memproses data',
                                    toast: true,
                                    position: "top-end",
                                    timer: 1500,
                                    showConfirmButton: false,
                                })
                                .then(() => {
                                    window.location.reload()
                                })
                        })
                }
            })
        }

        const bayarModalBody = (data = null) => {
            let transaksis;
            let transaksisEl = '';
            if (data) {
                transaksis = data.invoiceData.transaksis;
                if (transaksis.length > 0) {
                    transaksisEl = `
                    <div class="bg-light p-3" style="border-radius:var(--bs-modal-border-radius);">
                        <div class="mb-2">Riwayat Transaksi</div>
                        <ul class="list-group">`;
                    transaksis.forEach(item => {
                        transaksisEl += `<li class="list-group-item d-flex align-items-end bg-light">
                            <div class="">
                                <div class="small">${item.created_atFormat}</div>
                                <div class="small">${item.user.name}</div>
                            </div>
                            <h5 class="m-0 ms-auto text-end">${formatUang(item.nominal)}</h5>
                        </li>`
                    })
                    transaksisEl += `</ul>
                        </div>
                    </div>`
                }
            }

            console.log(transaksisEl);
            return `<div class="modal-body p-0 overflow-hidden">
                <div class="p-3">
                    <div>Pembayaran</div>
                    <div class="placeholder-glow mb-3">${data ? data.id +' - '+data.nama : '<span class="placeholder col-5"></span>'}</div>
                    <div class="placeholder-glow">${data ? 'Tagihan' : '<span class="placeholder col-2"></span>'}</div>
                    <h4 class="placeholder-glow">${data ? formatUang(data.invoice) : '<span class="placeholder col-6"></span>'}</h4>
                    <div class="placeholder-glow mb-3">${data ? 'Bulanan '+ formatUang(data.bulanan) +' x '+ data.tunggakan+' Bulan tunggakan' : '<span class="placeholder col-8"></span>'}</div>
                    <h4 id="nominalDisplay"></h4>
                    <form action="" class="mt-3" id="bayarModalForm" novalidate>
                        <input type="number" id="bayarModalNominal" class="form-control" placeholder="${data ? 'Nominal dibayar':''}" ${data ? '' : 'disabled'}>
                        <div id="bayarModalNominalFeedback" class="invalid-feedback"></div>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-bayar ${data ? '' : 'placeholder'}" ${data ? '' : 'disabled'}>Bayar</button>
                        </div>
                    </form>
                </div>
                ${transaksisEl != undefined ? transaksisEl : ''}`
        }

        const modalContent = document.querySelector('#bayarModal .modal-content')

        function modalBayarShow(userId) {
            modalContent.innerHTML = bayarModalBody();
            axios.get(`${appUrl}/api/pelanggan/${userId}`)
                .then(response => {
                    modalContent.innerHTML = bayarModalBody(response.data);
                    modalContent.querySelectorAll('.placeholder-glow').forEach(el => {
                        el.classList.remove('placeholder-glow')
                    })
                    let nominal;
                    document.getElementById('bayarModalNominal').addEventListener('input', e => {
                        if (e.target.value >= response.data.invoice) {
                            e.target.value = response.data.invoice
                        }

                        nominal = e.target.value
                        document.getElementById('nominalDisplay').textContent = formatUang(nominal)
                    })
                    let isLoading = false;
                    document.getElementById('bayarModalForm').addEventListener('submit', e => {
                        e.preventDefault();
                        if (!isLoading) {
                            isLoading = true;
                            const btnBayar = e.target.querySelector('.btn-bayar');
                            btnBayar.disabled = true
                            btnBayar.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin me-2"></i>Tunggu sebentar...'
                            axios.post(`${appUrl}/api/transaksi/${userId}`, {
                                    nominal: nominal
                                })
                                .then(response => {
                                    Alert.fire()
                                    document.querySelector('#bayarModal').querySelector(
                                        '[data-bs-dismiss="modal"]').click()
                                    performSearch()
                                    Alert.fire({
                                        icon: 'success',
                                        text: response.data.message,
                                        toast: true,
                                        position: "top-end",
                                        timer: 1500,
                                        showConfirmButton: false,
                                    })
                                })
                                .catch(error => {
                                    if (error.response.status == 422) {
                                        const inputNominal = document.getElementById('bayarModalNominal')
                                        inputNominal.classList.add('is-invalid')
                                        inputNominal.nextElementSibling.textContent = error.response.data
                                            .message
                                    } else {

                                    }
                                })
                                .finally(() => {
                                    isLoading = false
                                    btnBayar.disabled = false
                                    btnBayar.innerHTML = 'Bayar'
                                })
                        }

                    })
                })
                .catch(error => {
                    console.log(error);
                    modalContent.innerHTML = `<div class="alert alert-danger m-0" role="alert">
                        Gagal dalam mendapatkan data
                    </div>`
                })
        }

        const riwayatModalBody = (data = null) => {
            let glow = 'placeholder-glow';
            let name = '<span class="placeholder col-5"></span>';
            let li = `<li class="list-group-item d-flex align-items-end bg-light placeholder">
                <div class="">
                    <div class="small">&nbsp;</div>
                    <div class="small">&nbsp;</div>
                </div>
                <h5 class="m-0 ms-auto text-end">&nbsp;</h5>
            </li>`
            if (data) {
                glow = '';
                name = data.id + ' - ' + data.nama;
                li = '';
                data.invoice.forEach(item => {
                    let transaksisEl = '';
                    item.transaksis.forEach(i => {
                        transaksisEl += `<li class="list-group-item d-flex align-items-end bg-light">
                            <div class="">
                                <div class="small">${i.created_atFormat}</div>
                                <div class="small">${i.user.name}</div>
                            </div>
                            <h5 class="m-0 ms-auto text-end">${formatUang(i.nominal)}</h5>
                        </li>`
                    })
                    li += `<li class="list-group-item d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#invoiceCollapse${item.id}" style="cursor: pointer">
                        <div class="">
                            <div class="small">Tanggal Bayar</div>
                            <div class="small">${item.updated_atFormat}</div>
                        </div>
                        <div class="ms-auto text-end">
                            <small class="d-inline-flex px-2 py-1 fw-semibold text-${item.status == 0 ? 'danger' : 'success'} bg-${item.status == 0 ? 'danger' : 'success'} bg-opacity-10 border border-${item.status == 0 ? 'danger' : 'success'} border-opacity-10 rounded-2">${item.status == 0 ? 'Belum lunas' : 'Lunas'}</small>
                            <h5 class="m-0">${formatUang(item.total)}</h5>
                        </div>
                    </li>
                    <li class="list-group-item bg-light p-0">
                        <ul class="list-group list-group-flush collapse" id="invoiceCollapse${item.id}">
                            <p class="ms-3 my-2">Riwayat Transaksi</p>
                            ${transaksisEl}
                        </ul>
                    </li>`
                })
            }

            return `<div class="modal-body">
                <div>Riwayat Pembayaran</div>
                <div class="${glow} mb-2">${name}</div>
                <ul class="list-group ${glow}">
                    ${li}
                </ul>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>`
        }

        function modalRiwayatShow(userId) {
            modalContent.innerHTML = riwayatModalBody();
            axios.get(`${appUrl}/api/pelanggan/invoice/${userId}`)
                .then(response => {
                    modalContent.innerHTML = riwayatModalBody(response.data);
                })
                .catch(error => {
                    modalContent.innerHTML = `<div class="alert alert-danger m-0" role="alert">
                        Gagal dalam mendapatkan data
                    </div>`
                })
        }
    </script>
@endpush

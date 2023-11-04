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
            <button class="btn btn-light" onclick="exportToXLSX()"><i class="fa-solid fa-file-arrow-down"></i></button>
            @if (auth()->user()->role == 1)
                <button class="btn btn-primary" onclick="createPelanggan()">Tambah User</button>
                <button data-bs-toggle="modal" data-bs-target="#Modal" id="triggerModal" style="display: none"></button>
            @endif
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
                    @if (auth()->user()->role == 1)
                        <button class="btn btn-success btn-aktifkan-batch" onclick="handleBatch(1)">Aktifkan</button>
                        <button class="btn btn-danger btn-isolir-batch" onclick="handleBatch(2)">Isolir</button>
                    @endif
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
    <div class="modal fade" id="Modal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header h5 m-0">Tambah User</div>
                <div class="modal-body">
                    <form class="form" id="createUser">
                        <div class="form-group mb-2">
                            <label for="_nama">Nama</label>
                            <input type="text" id="_nama" name="_nama" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="_no_telp">No Telp</label>
                            <div class="input-group">
                                <span class="input-group-text">+</span>
                                <input type="tel" id="_no_telp" name="_no_telp" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="_email">Email</label>
                            <input type="email" id="_email" name="_email" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="_server_id">Server</label>
                            <select class="form-select" id="_server_id" id="nameid">
                                <option value="">-- Pilih Server --</option>
                                @foreach (\App\Models\Server::all() as $server)
                                    <option value="{{ $server->id }}">{{ $server->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>

                        </div>
                        <div class="form-group mb-2">
                            <label for="_mac">MAC</label>
                            <input type="text" id="_mac" name="_mac" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="_alamat">Alamat</label>
                            <textarea id="_alamat" name="_alamat" class="form-control" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="_created_at">Tanggal Pasang</label>
                            <input type="date" id="_created_at" name="_created_at" class="form-control"
                                value="{{ date('Y-m-d') }}">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="_paket_id">Bandwidth</label>
                            <select class="form-select" id="_paket_id" id="nameid">
                                <option value="">-- Pilih Bandwidth --</option>
                                @foreach (\App\Models\Paket::all() as $paket)
                                    <option value="{{ $paket->id }}">{{ $paket->bandwidth }}Mbps</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <button class="d-none"></button>
                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" id="createUserBtn">
                        <i class="fa-solid fa-spin fa-spinner me-1 d-none"></i>
                        Simpan
                    </button>
                </div>
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
        let _g = url.searchParams.get('g') ?? `{{ date('j') }}`;

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

            gEl.value = _g
            document.getElementById('gLabel').textContent = 'Tanggal ' + _g
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
            _g = e.target.value
            if (_g == '') {
                document.querySelector('.date-tag').classList.add('d-none');
            } else {
                document.querySelector('.date-tag').classList.remove('d-none');
                document.getElementById('gLabel').textContent = 'Tanggal ' + _g
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

        const _tr = (data) => {
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
                    <td>${formatUang(data.invoice.tagihan - data.invoice.total)}</td>
                    <td>
                        <small class="d-inline-flex px-2 py-1 fw-semibold text-${data.status[1]} bg-${data.status[1]} bg-opacity-10 border border-${data.status[1]} border-opacity-10 rounded-2">${data.status[0]}</small>
                    </td>
                </tr>
                <tr class="bg-light">
                    <td colspan="6" class="p-0 border-0">
                        <div class="collapse" id="collapse${data.id}" data-bs-parent="#tbody">
                        <div class="p-2">
                                <div class="d-flex gap-5 mb-4 justify-content-between">
                                    <div class="w-100">
                                        <small class="text-muted">Total tagihan</small>
                                        <h3 class="mb-2">${formatUang(data.invoice.tagihan - data.invoice.total)}</h3>

                                        <small class="text-muted">Pembayaran Bulanan</small>
                                        <p class="mb-2">${formatUang(data.invoice.paket.harga)}</p>

                                        <small class="text-muted">Menunggak</small>
                                        <p class="mb-2">${data.invoice.tunggakan} Bulan</p>


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
                                        <p class="mb-2">${data.invoice.paket.bandwidth}Mbps</p>

                                        <small class="text-muted">Server</small>
                                        <p class="mb-2">${data.server.name}</p>
                                    </div>


                                </div>
                                <div class="d-flex gap-2">
                                    @if (auth()->user()->role == 1)
                                    <button class="btn btn-warning btn-edit-pelanggan" onclick="editPelanggan(this,'${data.id}')"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <button class="btn btn-danger" onclick="deletePelanggan('${data.id}')"><i class="fa-solid fa-trash"></i></button>
                                    @endif
                                    <button class="ms-auto btn btn-primary" data-bs-toggle="modal" data-bs-target="#bayarModal" onclick="modal${(data.invoice.tagihan - data.invoice.total) > 0 ? 'Bayar' : 'Riwayat'}Show('${(data.invoice.tagihan - data.invoice.total) > 0 ? data.invoice.id : data.id}')">${(data.invoice.tagihan - data.invoice.total)  > 0 ? 'Bayar' : 'Riwayat Pembayaran'}</button>
                                    @if (auth()->user()->role == 1)
                                    <button class="btn btn-primary" onclick="cetakKwitansi('${data.id}')">Cetak kwitansi</button>
                                    <button class="btn btn-${data.status[0] == 'Aktif' ? 'danger' : 'success'} btn-isolir" data-id="${data.id}" data-nama="${data.nama}" data-status="${data.status[0]}">${data.status[0] == 'Isolir' ? 'Aktifkan' : 'Isolir'}</button>
                                    @endif
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


            axios.get(`${appUrl}/api/pelanggan?r=${r}&s=${s}&v=${v}&i=${i}&g=${_g}`)
                .then(response => {
                    document.getElementById('row-length').textContent = response.data.length + ' User';
                    if (response.data.length > 0) {

                        response.data.forEach(data => {
                            tbody.insertAdjacentHTML('beforeend', _tr(data))
                        });
                    } else {
                        tbody.insertAdjacentHTML('beforeend',
                            `<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>`)
                    }
                })
                .catch(error => {
                    console.error(error);
                    tbody.insertAdjacentHTML('beforeend', `<tr><td colspan="7" class="text-center">Error</td></tr>`)
                })
                .finally(response => {
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

                    @if (auth()->user()->role == 1)
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
                    @endif
                    isFetching = false;
                })

        }
        @if (auth()->user()->role == 1)
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
        @endif

        const bayarModalBody = (data = null) => {
            let transaksis;
            let transaksisEl = '';
            if (data) {
                transaksis = data.transaksis;
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

            return `<div class="modal-body p-0 overflow-hidden">
                <div class="p-3">
                    <div>Pembayaran</div>
                    <div class="placeholder-glow mb-3">${data ? data.pelanggan_id +' - '+data.pelanggan.nama : '<span class="placeholder col-5"></span>'}</div>
                    <div class="placeholder-glow">${data ? 'Tagihan' : '<span class="placeholder col-2"></span>'}</div>
                    <h4 class="placeholder-glow">${data ? formatUang(data.tagihan - data.total) : '<span class="placeholder col-6"></span>'}</h4>
                    <div class="placeholder-glow mb-3">${data ? 'Bulanan '+ formatUang(data.paket.harga) +' x '+ data.tunggakan+' Bulan tunggakan' : '<span class="placeholder col-8"></span>'}</div>
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
            axios.get(`${appUrl}/api/invoice/${userId}`)
                .then(response => {
                    modalContent.innerHTML = bayarModalBody(response.data);
                    modalContent.querySelectorAll('.placeholder-glow').forEach(el => {
                        el.classList.remove('placeholder-glow')
                    })
                    let nominal;
                    document.getElementById('bayarModalNominal').addEventListener('input', e => {
                        if (e.target.value >= (response.data.tagihan - response.data.total)) {
                            e.target.value = (response.data.tagihan - response.data.total)
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
                    console.error(error);
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
                console.log(data);
                glow = '';
                name = data.id + ' - ' + data.nama;
                li = '';
                data.invoices.forEach(item => {
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

        function modalRiwayatShow(pelanggan_id) {
            modalContent.innerHTML = riwayatModalBody();
            axios.get(`${appUrl}/api/invoice?pelanggan_id=${pelanggan_id}`)
                .then(response => {
                    modalContent.innerHTML = riwayatModalBody(response.data);
                })
                .catch(error => {
                    console.log(error);
                    modalContent.innerHTML = `<div class="alert alert-danger m-0" role="alert">
                        Gagal dalam mendapatkan data
                    </div>`
                })
        }

        function cetakKwitansi(sk) {
            const elemenCetak = document.querySelector("table .collapse");
            elemenCetak.print();

            // Buat jendela sumber cetakan baru
            const cetakan = window.open("", "Cetakan");

            // Isi jendela cetakan dengan HTML elemen yang ingin dicetak
            cetakan.document.open();
            cetakan.document.write("<html><head><title>Cetak</title></head><body>");
            cetakan.document.write(elemenCetak.innerHTML);
            cetakan.document.write("</body></html>");
            cetakan.document.close();

            // Cetak jendela sumber cetakan

            // Tutup jendela sumber cetakan setelah pencetakan selesai
            cetakan.close();
        }
        let isEdit = false;
        const triggerModal = document.getElementById('triggerModal');
        const formCreateUser = document.getElementById('createUser');
        triggerModal.addEventListener('click', e => {
            formCreateUser.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            })
        })

        function createPelanggan() {
            isEdit = false;
            document.querySelector('#Modal .modal-header').innerHTML = 'Tambah User';
            const inputs = formCreateUser.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.value = ''
            })
            formCreateUser.querySelector('#_no_telp').value = '62';
            formCreateUser.querySelector('#_created_at').value = `{{ now()->format('Y-m-d') }}`;
            const selects = formCreateUser.querySelectorAll('.form-control');
            selects.forEach(select => {
                select.selectedIndex = 0
            })
            triggerModal.click()
        }
        let pelangganId;

        function editPelanggan(btn, id) {
            pelangganId = id;
            isEdit = true;
            btn.innerHTML = '<i class="fa-solid fa-spin fa-spinner"></i>'
            const btns = document.querySelectorAll('.btn-edit-pelanggan');
            btns.forEach(b => {
                b.disabled = true;
            })
            axios.get(`${appUrl}/api/pelanggan/${id}/edit`)
                .then(response => {
                    document.querySelector('#Modal .modal-header').innerHTML = 'Edit User';
                    const data = response.data;
                    formCreateUser.querySelector('#_nama').value = data.nama;
                    formCreateUser.querySelector('#_no_telp').value = data.no_telp;
                    formCreateUser.querySelector('#_email').value = data.email;
                    formCreateUser.querySelector('#_server_id').value = data.server_id;
                    formCreateUser.querySelector('#_mac').value = data.mac;
                    formCreateUser.querySelector('#_alamat').value = data.alamat;
                    formCreateUser.querySelector('#_paket_id').value = data.paket_id;
                    formCreateUser.querySelector('#_created_at').value = data.created_atFormat;
                    triggerModal.click()
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
                })
                .finally(() => {
                    btn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i>'
                    btns.forEach(b => {
                        b.disabled = false;
                    })
                })
        }

        const btnCreateUser = document.getElementById('createUserBtn');
        btnCreateUser.addEventListener('click', e => {
            handleFormSubmit();
        })

        formCreateUser.addEventListener('submit', e => {
            e.preventDefault()
            handleFormSubmit()
        })

        function handleFormSubmit() {
            btnCreateUser.querySelector('.fa-spin').classList.remove('d-none')
            btnCreateUser.disabled = true
            data = {
                nama: formCreateUser.querySelector('#_nama').value,
                no_telp: formCreateUser.querySelector('#_no_telp').value,
                email: formCreateUser.querySelector('#_email').value,
                server_id: formCreateUser.querySelector('#_server_id').value,
                mac: formCreateUser.querySelector('#_mac').value,
                alamat: formCreateUser.querySelector('#_alamat').value,
                paket_id: formCreateUser.querySelector('#_paket_id').value,
                created_at: formCreateUser.querySelector('#_created_at').value,
            }

            let url = `${appUrl}/api/pelanggan`;
            if (isEdit) {
                data._method = 'PUT';
                url += '/' + pelangganId
            }
            axios.post(url, data)
                .then(response => {
                    Alert.fire({
                        icon: 'success',
                        text: response.data.message,
                        toast: true,
                        position: "top-end",
                        timer: 1500,
                        showConfirmButton: false,
                    })
                    performSearch()
                    document.querySelector('#Modal [data-bs-dismiss="modal"]').click()
                })
                .catch(error => {
                    if (error.response.status == 422) {
                        resetInput();
                        errors = error.response.data.errors
                        Object.keys(errors).forEach(key => {
                            invalidateInput(key, errors[key])
                        });
                    } else {
                        Alert.fire({
                            icon: 'error',
                            text: 'Terdapat kesalahan dalam memproses data',
                            toast: true,
                            position: "top-end",
                            timer: 1500,
                            showConfirmButton: false,
                        });
                    }
                    console.error(error);
                })
                .finally(() => {
                    btnCreateUser.querySelector('.fa-spin').classList.add('d-none')
                    btnCreateUser.disabled = false
                })
        }

        function invalidateInput(id, errors) {
            const el = document.getElementById('_' + id)
            el.classList.add('is-invalid');
            let ul = '<ul>'
            errors.forEach(error => {
                ul += `<li>${error}</li>`
            });
            ul += "</ul>"
            el.nextElementSibling.innerHTML = ul;
        }

        function resetInput() {
            formCreateUser.querySelectorAll('.form-control, .form-select').forEach(el => {
                el.classList.remove('is-invalid');
            })
        }

        function deletePelanggan(id) {
            Alert.fire({
                icon: 'warning',
                title: 'Hapus',
                text: `Apakah anda yakin ingin menghapus user ini?`,
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
            }).then(result => {
                if (result.isConfirmed) {
                    axios.delete(`${appUrl}/api/pelanggan/${id}`)
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
                            });
                        })
                }
            })
        }
    </script>
@endpush

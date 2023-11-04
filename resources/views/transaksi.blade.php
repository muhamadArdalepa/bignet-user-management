@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex align-items-center mb-4 gap-2">
            <h1 class="fs-4 m-0">Riwayat Transaksi User Bignet</h1>
            <select id="region_id" class="form-select form-select-lg py-1" style="width: unset">
                <option value="">Semua Wilayah</option>
                @foreach (\App\Models\Region::all() as $region)
                    <option value="{{ $region->id }}" {{ request()->r == $region->id ? 'selected' : '' }}>
                        {{ $region->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary ms-auto" onclick="exportTransaksis(this)"><i
                    class="fa-solid fa-file-arrow-down me-2"></i>Export</button>
        </div>

        <div class="d-flex gap-3 align-items-center mb-3">
            <div class="input-group">
                <span class="input-group-text border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input id="searchbox" type="search" class="form-control  border-start-0" placeholder="Cari. . .">
            </div>
            <select id="vEl" class="form-select" style="width: unset">
                <option value="">Tampilkan Semua</option>
                @foreach (\App\Models\Server::all() as $server)
                    <option value="{{ $server->id }}">{{ $server->name }}</option>
                @endforeach
            </select>
            <input type="date" id="tanggal" class="form-control" style="width: 12rem" value="{{ date('Y-m-d') }}">
        </div>

        <div class="border rounded-3 overflow-hidden" id="main-card">
            <table class="table bg-white">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama User</th>
                        <th>Penerima</th>
                        <th>Nominal</th>
                        <th class="text-end">Waktu Bayar</th>
                    </tr>
                </thead>
                <tbody id="tbody">

                </tbody>
            </table>
        </div>
    </div>
@endsection
@push('js')
    <div class="modal fade" id="Modal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script>
        const tbody = document.getElementById('tbody');

        const rEl = document.getElementById('region_id');
        const sEl = document.getElementById('searchbox');
        const gEl = document.getElementById('tanggal');
        const vEl = document.getElementById('vEl')

        let r = rEl.value;
        let s = sEl.value;
        let _g = gEl.value;
        let v = vEl.value;

        document.addEventListener('DOMContentLoaded', () => {
            performSearch()
        })
        rEl.addEventListener('change', e => {
            r = rEl.value
            performSearch()
        });
        sEl.addEventListener('input', debounce(performSearch, 300))
        gEl.addEventListener('change', e => {
            _g = gEl.value
            performSearch()
        });
        vEl.addEventListener('change', e => {
            v = vEl.value
            performSearch()
        })

        const _tr = (data = null) => {
            if (!data) {
                return `<tr id="loader">
                    <td colspan="7">
                        <div class="text-center">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            Mendapatkan data
                        </div>
                    </td>
                </tr>`
            }
            return `<tr style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#collapse${data.id}">
                <td>TR${data.id}</td>
                <td>${data.pelanggan.nama}</td>
                <td>${data.user.name}</td>
                <td>${formatUang(data.nominal)}</td>
                <td class="text-end">${data.created_atFormat}</td>
            </tr>
            <tr class="bg-light">
                <td colspan="7" class="p-0 border-0">
                    <div class="collapse" data-bs-parent="#tbody" id="collapse${data.id}">
                        <div class="p-2 d-flex gap-3 justify-content-between align-items-end flex-wrap">
                            <div class="d-flex gap-3 flex-wrap justify-content-between justify-content-sm-start">
                                <div>
                                    <small class="text-muted">ID Tagihan</small>
                                    <h5>IN${data.invoice_id}</h5>
                                </div>
                                <div>
                                    <small class="text-muted">Total tagihan</small>
                                    <h5>${formatUang(data.invoice.tagihan)}</h5>
                                </div>
                                <div>
                                    <small class="text-muted">Jumlah terbayar</small>
                                    <h5>${formatUang(data.invoice.total)}</h5>
                                </div>
                                <div>
                                    <small class="text-muted">Status</small>
                                    <small class="d-flex px-2 py-1 fw-semibold text-${data.invoice.status == 0 ?'danger' : 'success'} bg-${data.invoice.status == 0 ?'danger' : 'success'} bg-opacity-10 border border-${data.invoice.status == 0 ?'danger' : 'success'} border-opacity-10 rounded-2">${data.invoice.status == 0 ?'Belum Lunas' : 'Lunas'}</small>
                                </div>

                            </div>
                            @if(auth()->user()->role == 1)
                            <div class="text-end">
                                <button class="btn btn-danger ms-auto" onclick="deleteTransaksi(${data.id})">
                                    <i class="fa-solid fa-trash"></i>
                                    Hapus
                                </button>
                                <button class="btn btn-warning" onclick="editTransaksi(this, ${data.id})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Edit
                                </button>
                                <button id="btnToggleModal" style="display:none" data-bs-toggle="modal" data-bs-target="#Modal"></button>
                            </div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>`
        }
        let isFetching = false;

        let params;

        function performSearch() {
            tbody.innerHTML = _tr()
            if (isFetching) return;
            isFetching = true;
            s = sEl.value;
            params = `r=${r}&s=${s}&v=${v}&g=${_g}`;
            axios.get(`${appUrl}/api/transaksi?${params}`)
                .then(response => {
                    tbody.innerHTML = ''
                    if (response.data < 1) {
                        tbody.insertAdjacentHTML('beforeend', `<tr>
                            <td colspan="7">
                                <div class="text-center">
                                    Tidak ada data
                                </div>
                            </td>
                        </tr>`)
                    } else {
                        response.data.forEach(item => {
                            tbody.insertAdjacentHTML('beforeend', _tr(item))
                        })
                    }

                })
                .catch(error => {
                    console.error(error);
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center">Error</td></tr>`
                })
                .finally(response => {
                    isFetching = false;
                })
        }

        @if(auth()->user()->role == 1)
        function deleteTransaksi(id) {
            Alert.fire({
                icon: 'warning',
                title: 'Hapus',
                text: 'Apakah Anda yakin ingin menghapus transaksi ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return axios.delete(`${appUrl}/api/transaksi/${id}`, {
                            _method: 'DELETE'
                        })
                        .then(response => {
                            performSearch();
                            Alert.fire({
                                icon: 'success',
                                text: response.data.message,
                                toast: true,
                                position: 'top-end',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        })
                        .catch(error => {
                            Alert.fire({
                                    icon: 'error',
                                    text: 'Terdapat kesalahan dalam memproses data',
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

        const modalBody = (data) => {
            return `<div>
                <div>Pembayaran</div>
                <div class="mb-3">${data.pelanggan.id +' - '+data.pelanggan.nama}</div>
                <div>Tagihan</div>
                <h4>${formatUang(data.invoice.tagihan + data.nominal - data.invoice.total)}</h4>
                <div class="mb-3">Bulanan ${formatUang(data.invoice.paket.harga) +' x '+ data.invoice.tunggakan} Bulan tunggakan</div>
                <h4 id="nominalDisplay">${formatUang(data.nominal)}</h4>
                <form class="mt-3" id="bayarModalForm" novalidate>
                    <input type="number" id="bayarModalNominal" class="form-control" placeholder="Nominal dibayar" value="${data.nominal}">
                    <div id="bayarModalNominalFeedback" class="invalid-feedback"></div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-bayar">Simpan</button>
                    </div>
                </form>
            </div>`
        }
        const modal = document.querySelector('#Modal .modal-body');
        function editTransaksi(btn, id) {
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Loading...'
            btn.disabled = true
            axios.get(`${appUrl}/api/transaksi/${id}/edit`)
                .then(response => {
                    console.log(response.data);
                    modal.innerHTML = modalBody(response.data)
                    document.getElementById('btnToggleModal').click()
                    let nominal;
                    document.getElementById('bayarModalNominal').addEventListener('input', e => {
                        if (e.target.value >= (response.data.invoice.tagihan - response.data.invoice.total +
                                response.data.nominal)) {
                            e.target.value = (response.data.invoice.tagihan - response.data.invoice.total +
                                response.data.nominal)
                        }

                        nominal = e.target.value
                        document.getElementById('nominalDisplay').textContent = formatUang(nominal)
                    })
                    let isLoading = false;
                    const editForm = document.getElementById('bayarModalForm');
                    editForm.addEventListener('submit', e => {
                        e.preventDefault();
                        if (!isLoading) {
                            isLoading = true;
                            const btnBayar = e.target.querySelector('.btn-bayar');
                            btnBayar.disabled = true
                            btnBayar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengubah...'
                            axios.post(`${appUrl}/api/transaksi/${id}`, {
                                    _method: 'PATCH',
                                    nominal: nominal
                                })
                                .then(response => {
                                    console.log(response.data);
                                    Alert.fire()
                                    document.querySelector('#Modal').querySelector(
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
                                        console.error(error);
                                        Alert.fire({
                                            icon: 'error',
                                            text: 'Terdapat kesalahan dalam mengubah data',
                                            toast: true,
                                            position: 'top-end',
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                    }
                                })
                                .finally(() => {
                                    isLoading = false
                                    btnBayar.disabled = false
                                    btnBayar.innerHTML = 'Bayar'
                                })
                        }
                    });
                })
                .catch(error => {
                    console.error(error);
                    Alert.fire({
                        icon: 'error',
                        text: 'Terdapat kesalahan dalam memproses data',
                        toast: true,
                        position: 'top-end',
                        timer: 1500,
                        showConfirmButton: false
                    });
                })
                .finally(() => {
                    btn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i>Edit'
                    btn.disabled = false
                })
        }
        @endif

        function exportTransaksis(btn) {
            btn.disabled = true
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Mendapatkan data...'
            axios.get(`${appUrl}/api/transaksi/export?${params}`)
                .then(response => {
                    const obj = response.data;
                    const header = Object.keys(obj[0]);
                    const main = obj.map(item => {
                        return Object.values(item);
                    })
                    const body = [header, ...main];
                    console.log(body);

                    const ws = XLSX.utils.aoa_to_sheet(Object.values(body));
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, "Data");
                    XLSX.writeFile(wb, `Rekap Pembayaran User Bignet ${_g}.xlsx`);
                })
                .catch(error => {
                    console.error(error);
                    Alert.fire({
                        icon: 'error',
                        text: 'Terdapat kesalahan dalam memproses data',
                        toast: true,
                        position: 'top-end',
                        timer: 1500,
                        showConfirmButton: false
                    });
                })
                .finally(() => {
                    btn.disabled = false
                    btn.innerHTML = '<i class="fa-solid fa-file-arrow-down me-2"></i>Export'
                })
        }
    </script>
@endpush

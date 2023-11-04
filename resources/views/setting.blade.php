@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="fs-4 mb-3">Pengaturan</h1>
        <ul class="list-group" id="listGroup">
            <li class="list-group-item p-0">
                <div class="d-flex align-items-center justify-content-between p-3" style="cursor: pointer"
                    data-bs-target="#collapseWilayah" data-bs-toggle="collapse">
                    <h5>
                        Pengaturan Wilayah
                    </h5>
                    <i class="fa-solid fa-chevron-down" style="transition: transform 300ms"></i>
                </div>

                <div class="collapse" data-bs-parent="#listGroup" id="collapseWilayah">
                    <div class="p-3">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="input-group m-0 input-group-sm">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" id="cariWilayah" class="form-control">
                            </div>
                            <button class="btn btn-primary ms-auto btn-sm" onclick="createRegion()"
                                data-bs-toggle="modal"data-bs-target="#Modal">Tambah</button>
                        </div>

                        <ul class="list-group" id="regionUl">

                        </ul>
                    </div>
                </div>
            </li>
            <li class="list-group-item p-0">
                <div class="d-flex align-items-center justify-content-between p-3" style="cursor: pointer"
                    data-bs-target="#collapseServer" data-bs-toggle="collapse">
                    <h5>
                        Pengaturan Server
                    </h5>
                    <i class="fa-solid fa-chevron-down" style="transition: transform 300ms"></i>
                </div>

                <div class="collapse" data-bs-parent="#listGroup" id="collapseServer">
                    <div class="p-3">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="input-group m-0 input-group-sm">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" id="cariServer" class="form-control">
                            </div>
                            <button class="btn btn-primary ms-auto btn-sm" onclick="createServer()"
                                data-bs-toggle="modal"data-bs-target="#Modal">Tambah</button>
                        </div>

                        <ul class="list-group" id="serverUl">

                        </ul>
                    </div>
                </div>
            </li>
            <li class="list-group-item p-0">
                <div class="d-flex align-items-center justify-content-between p-3" style="cursor: pointer"
                    data-bs-target="#collapsePaket" data-bs-toggle="collapse">
                    <h5>
                        Pengaturan Paket
                    </h5>
                    <i class="fa-solid fa-chevron-down" style="transition: transform 300ms"></i>
                </div>

                <div class="collapse" data-bs-parent="#listGroup" id="collapsePaket">
                    <div class="p-3">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="input-group m-0 input-group-sm">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" id="cariPaket" class="form-control">
                            </div>
                            <button class="btn btn-primary ms-auto btn-sm" onclick="createPaket()"
                                data-bs-toggle="modal"data-bs-target="#Modal">Tambah</button>
                        </div>

                        <ul class="list-group" id="paketUl">

                        </ul>
                    </div>
                </div>
            </li>
        </ul>
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

    <script>
        const regionUl = document.getElementById('regionUl');
        const serverUl = document.getElementById('serverUl');
        const paketUl = document.getElementById('paketUl');

        const regionLi = data => {
            return `<li class="list-group-item bg-transparent">
                <div class="d-flex align-items-center gap-1">
                    <div>${data.name}</div>
                    <button class="btn btn-sm btn-warning ms-auto" data-bs-target="#Modal" data-bs-toggle="modal" onclick="editRegion('${data.id}','${data.name}')">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="destroy('region','${data.id}',searchRegion)">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </li>`
        }

        const serverLi = data => {
            return `<li class="list-group-item">
                <div class="d-flex align-items-center gap-1">
                    <div>${data.id}. </div>
                    <span class="badge bg-primary">${data.kode}</span>
                    <div>${data.name} - <small>${data.region.name}</small></div>
                    <button class="btn btn-sm btn-warning ms-auto" data-bs-target="#Modal" data-bs-toggle="modal" onclick="editServer('${data.id}')">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="destroy('server','${data.id}',searchServer)">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </li>`
        }

        const paketLi = data => {
            return `<li class="list-group-item">
                <div class="d-flex align-items-start gap-1">
                    <div class="d-flex flex-column">
                        <div>${(data.name != null && data.name != '') ? data.name+' - ' : ''}<strong>${data.bandwidth}</strong> Mbps</div>
                        <div>Harga <strong>${formatUang(data.harga)}</strong></div>
                    </div>
                    <button class="btn btn-sm btn-warning ms-auto" data-bs-target="#Modal" data-bs-toggle="modal" onclick="editPaket('${data.id}','${data.name}','${data.bandwidth}','${data.harga}')">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="destroy('paket','${data.id}',searchPaket)">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </li>`
        }


        const loader = `<li class="list-group-item text-center">
            <i class="fa-solid fa-spinner fa-spin me-1"></i>
            Mendapatkan data...
        </li>`

        function draw(parent, lists, context) {
            parent.innerHTML = loader;
            axios.get(`${appUrl}/api/${context}`)
                .then(response => {
                    parent.innerHTML = '';
                    if (response.data.length < 1) {
                        parent.innerHTML = ` <li class="list-group-item text-center">
                            Tidak ada data
                        </li>`;
                    }
                    response.data.forEach(data => {
                        parent.innerHTML += lists(data);
                    })
                })
                .catch(error => {
                    console.error(error);
                    parent.innerHTML = ` <li class="list-group-item text-center">
                        Tidak ada data
                    </li>`;
                })
        }

        const cariRegion = document.getElementById('cariWilayah');
        let regionTerm = cariRegion.value;

        function searchRegion() {
            regionTerm = cariRegion.value;
            draw(regionUl, regionLi, 'region?term=' + regionTerm)
        }
        cariRegion.addEventListener('input', debounce(searchRegion, 250))

        const cariServer = document.getElementById('cariServer');
        let serverTerm = cariServer.value;

        function searchServer() {
            serverTerm = cariServer.value;
            draw(serverUl, serverLi, 'server?term=' + serverTerm)
        }
        cariServer.addEventListener('input', debounce(searchServer, 250))

        const cariPaket = document.getElementById('cariPaket');
        let paketTerm = cariPaket.value;

        function searchPaket() {
            paketTerm = cariPaket.value;
            draw(paketUl, paketLi, 'paket?term=' + paketTerm)
        }
        cariPaket.addEventListener('input', debounce(searchPaket, 250))

        document.addEventListener('DOMContentLoaded', () => {
            searchRegion()
            searchServer()
            searchPaket()
        })

        document.querySelectorAll('.list-group-item .collapse').forEach(e => {
            e.addEventListener('show.bs.collapse', el => {
                const icon = el.target.previousElementSibling.querySelector('i');
                icon.style.transform = 'rotate(180deg)';
            });
            e.addEventListener('hide.bs.collapse', el => {
                const icon = el.target.previousElementSibling.querySelector('i');
                icon.style.transform = 'rotate(0deg)';
            });
        });

        let idEdit;
        const modalBody = document.querySelector('.modal-body');
        const formRegion = (data = null) => {
            return `<h5 class="mb-3">${data ? 'Edit' : 'Tambah'} Wilayah</h5>
            <form onsubmit="event.preventDefault();submitRegion(this)" data-context="region">
                <div class="form-group mb-3">
                    <label for="name">Nama Wilayah</label>
                    <input type="text" id="name" name="name" class="form-control" value="${data ?? ''}">
                    <div id="nameFeedback" class="invalid-feedback"></div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" id="btnSubmit">Simpan</button>
                </div>
            </form>`
        }

        const formServer = (data = null, regions) => {
            let regionsEl = '';
            regions.forEach(region => {
                regionsEl +=
                    `<option ${data ? (data.region_id == region.id ? 'selected' : '') : ''} value="${region.id}">${region.name}</option>`
            })
            return `<h5 class="mb-3">${data ? 'Edit' : 'Tambah'} Server</h5>
            <form onsubmit="event.preventDefault();submitServer(this)" data-context="server">
                <div class="row">
                    <div class="col-8">
                        <div class="form-group">
                            <label for="name">Nama Server</label>
                            <input type="text" id="name" name="name" class="form-control" value="${data ? data.name : ''}">
                            <div id="nameFeedback" class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="name">Kode</label>
                            <input type="text" id="kode" name="kode" class="form-control" value="${data ? data.kode : ''}">
                            <div id="kodeFeedback" class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group my-3">
                    <label for="name">Nama Wilayah</label>
                    <select class="form-select" id="region_id">
                        ${regionsEl}
                    </select>
                    <div id="Feedback" class="invalid-feedback"></div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" id="btnSubmit">Simpan</button>
                </div>
            </form>`
        }

        const formPaket = (name = null, harga = null, bandwidth = null) => {
            return `<h5 class="mb-3">${name ? 'Edit' : 'Tambah'} Paket</h5>
            <form onsubmit="event.preventDefault();submitPaket(this)" data-context="paket">
                <div class="form-group mb-3">
                    <label for="name">Nama Paket <sup class="text-muted">(opsional)</sup></label>
                    <input type="text" id="name" name="name" class="form-control" value="${name ?? ''}">
                    <div id="nameFeedback" class="invalid-feedback"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-8">
                        <div class="form-group">
                            <label for="harga">Harga</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">Rp.</span>
                                <input type="text" id="harga" name="harga" class="form-control" value="${harga ?? ''}">
                                <div id="hargaFeedback" class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label for="bandwidth">Bandwidth</label>
                            <div class="input-group  has-validation">
                                <input type="text" id="bandwidth" name="bandwidth" class="form-control" value="${bandwidth  ?? ''}">
                                <span class="input-group-text">Mbps</span>
                                <div id="bandwidthFeedback" class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" id="btnSubmit">Simpan</button>
                </div>
            </form>`
        }

        function createRegion() {
            idEdit = undefined;
            modalBody.innerHTML = formRegion();
        }


        function createServer() {
            idEdit = undefined;
            modalBody.innerHTML = `<div class="text-center">
                <i class="fa-solid fa-spinner fa-spin me-1"></i>
                Mendapatkan data
            </div>`
            axios.get(`${appUrl}/api/region`)
                .then(response => {
                    modalBody.innerHTML = formServer(null, response.data);
                })
        }

        function createPaket() {
            idEdit = undefined;
            modalBody.innerHTML = formPaket()
        }

        function submitRegion(form) {
            store(form, {
                name: form.querySelector('#name').value
            }, searchRegion)
        }

        function submitServer(form) {
            store(form, {
                name: form.querySelector('#name').value,
                kode: form.querySelector('#kode').value,
                region_id: form.querySelector('#region_id').value
            }, searchServer)
        }

        function submitPaket(form) {
            store(form, {
                name: form.querySelector('#name').value,
                harga: form.querySelector('#harga').value,
                bandwidth: form.querySelector('#bandwidth').value
            }, searchPaket)
        }

        function editRegion(id, name) {
            idEdit = id;
            modalBody.innerHTML = formRegion(name)
        }

        function editServer(id) {
            idEdit = id;
            modalBody.innerHTML = `<div class="text-center">
                <i class="fa-solid fa-spinner fa-spin me-1"></i>
                Mendapatkan data
            </div>`
            axios.get(`${appUrl}/api/server/${id}/edit`)
                .then(response => {
                    console.log(response);
                    modalBody.innerHTML = formServer(response.data.server, response.data.regions);
                })
        }

        function editPaket(id, name, bandwidth, harga) {
            idEdit = id;
            modalBody.innerHTML = formPaket(name, harga, bandwidth)
        }


        function store(form, data, call) {
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spin fa-spinner me-1"></i>Loading...'
            if (idEdit) data.id = idEdit
            axios.post(`${appUrl}/api/${form.dataset.context}`, data)
                .then(response => {
                    Alert.fire({
                        icon: 'success',
                        text: response.data.message,
                        toast: true,
                        position: "top-end",
                        timer: 1500,
                        showConfirmButton: false,
                    })
                    document.querySelector('#Modal [data-bs-dismiss="modal"]').click()
                    call()
                })
                .catch(error => {
                    if (error.response.status == 422) {
                        resetInput(form);
                        errors = error.response.data.errors
                        Object.keys(errors).forEach(key => {
                            invalidateInput(key, errors[key])
                        });
                    } else {
                        Alert.fire({
                            icon: 'error',
                            text: error.response.data.message,
                            toast: true,
                            position: "top-end",
                            timer: 1500,
                            showConfirmButton: false,
                        });
                    }
                    console.error(error);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Simpan'
                })
        }

        function invalidateInput(id, errors) {
            const el = document.getElementById(id)
            const fEl = document.getElementById(id + 'Feedback')
            el.classList.add('is-invalid');
            let ul = '<ul>'
            errors.forEach(error => {
                ul += `<li>${error}</li>`
            });
            ul += "</ul>"
            fEl.innerHTML = ul;
        }

        function resetInput(form) {
            form.querySelectorAll('.form-control, .form-select').forEach(el => {
                el.classList.remove('is-invalid');
            })
        }

        function destroy(context, id, call) {
            Alert.fire({
                icon: 'warning',
                title: 'Hapus',
                text: `Apakah Anda yakin ingin menghapus ${context} ini?`,
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return axios.delete(`${appUrl}/api/${context}/${id}`, {
                            _method: 'DELETE'
                        })
                        .then(response => {
                            call();
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
                            console.log(error);
                            Alert.fire({
                                icon: 'error',
                                text: error.response.data.message,
                                toast: true,
                                position: 'top-end',
                                timer: 1500,
                                showConfirmButton: false
                            })
                        });
                }
            });
        }
    </script>
@endpush

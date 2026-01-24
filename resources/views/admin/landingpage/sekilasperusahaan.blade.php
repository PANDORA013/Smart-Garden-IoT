@extends($layout)

@section('content')
<div class="container">
    <h2>Sekilas Perusahaan</h2>
    
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
            });
        </script>
    @endif

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Tambah Sekilas Perusahaan</button>
    
    <table class="table table-bordered">
        <tr>
            <th>
                Deskripsi
                <div class="btn-group ms-2" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary active" onclick="toggleLanguage('id')">Indonesia</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleLanguage('en')">English</button>
                </div>
            </th>
            <th>Alignment</th>
            <th width="280px">Aksi</th>
        </tr>
        @foreach ($data as $item)
        <tr>
            <td style="text-align: {{ $item->text_align }}; white-space: pre-wrap;">
                <div class="content-id">{!! nl2br(e($item->maintext['id'])) !!}</div>
                <div class="content-en" style="display: none;">{!! nl2br(e($item->maintext['en'])) !!}</div>
            </td>
            <td>{{ ucfirst($item->text_align) }}</td>
            <td>
                <button class="btn btn-warning editBtn" 
                    data-id="{{ $item->Id_sekilas }}" 
                    data-maintext-id="{{ $item->maintext['id'] }}"
                    data-maintext-en="{{ $item->maintext['en'] }}"
                    data-text-align="{{ $item->text_align }}" 
                    data-bs-toggle="modal" 
                    data-bs-target="#editModal">Edit</button>
                <button class="btn btn-danger deleteBtn" data-id="{{ $item->Id_sekilas }}">Hapus</button>
            </td>
        </tr>
        @endforeach
    </table>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Tambah Sekilas Perusahaan</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addForm">
                        @csrf
                        <div class="form-group">
                            <label for="maintext">Deskripsi:</label>
                            <textarea class="form-control" id="maintext" name="maintext" rows="4" required style="white-space: pre-wrap;"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="text_align">Text Alignment:</label>
                            <div class="btn-group d-flex" role="group" aria-label="Text alignment">
                                <input type="radio" class="btn-check" name="text_align" id="addAlignLeft" value="left" required>
                                <label class="btn btn-outline-primary" for="addAlignLeft">
                                    <i class="fa-solid fa-align-left"></i> Left
                                </label>
                        
                                <input type="radio" class="btn-check" name="text_align" id="addAlignCenter" value="center">
                                <label class="btn btn-outline-primary" for="addAlignCenter">
                                    <i class="fa-solid fa-align-center"></i> Center
                                </label>
                        
                                <input type="radio" class="btn-check" name="text_align" id="addAlignRight" value="right">
                                <label class="btn btn-outline-primary" for="addAlignRight">
                                    <i class="fa-solid fa-align-right"></i> Right
                                </label>
                        
                                <input type="radio" class="btn-check" name="text_align" id="addAlignJustify" value="justify">
                                <label class="btn btn-outline-primary" for="addAlignJustify">
                                    <i class="fa-solid fa-align-justify"></i> Justify
                                </label>
                            </div>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Sekilas Perusahaan</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editId" name="id">
                        <div class="form-group">
                            <label for="editMaintext">Deskripsi:</label>
                            <textarea class="form-control" id="editMaintext" name="maintext" rows="4" required style="white-space: pre-wrap;"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="editTextAlign">Text Alignment:</label>
                            <div class="btn-group d-flex" role="group" aria-label="Text alignment">
                                <input type="radio" class="btn-check" name="text_align" id="editAlignLeft" value="left" autocomplete="off" required>
                                <label class="btn btn-outline-primary" for="editAlignLeft">
                                    <i class="fa-solid fa-align-left"></i> Left
                                </label>
                        
                                <input type="radio" class="btn-check" name="text_align" id="editAlignCenter" value="center" autocomplete="off">
                                <label class="btn btn-outline-primary" for="editAlignCenter">
                                    <i class="fa-solid fa-align-center"></i> Center
                                </label>
                        
                                <input type="radio" class="btn-check" name="text_align" id="editAlignRight" value="right" autocomplete="off">
                                <label class="btn btn-outline-primary" for="editAlignRight">
                                    <i class="fa-solid fa-align-right"></i> Right
                                </label>
                        
                                <input type="radio" class="btn-check" name="text_align" id="editAlignJustify" value="justify" autocomplete="off">
                                <label class="btn btn-outline-primary" for="editAlignJustify">
                                    <i class="fa-solid fa-align-justify"></i> Justify
                                </label>
                            </div>
                        </div><br>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Hapus Data</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus data ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleLanguage(lang) {
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        if (lang === 'id') {
            document.querySelectorAll('.content-id').forEach(el => el.style.display = '');
            document.querySelectorAll('.content-en').forEach(el => el.style.display = 'none');
        } else {
            document.querySelectorAll('.content-id').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.content-en').forEach(el => el.style.display = '');
        }
    }

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var maintextId = this.getAttribute('data-maintext-id');
            var maintextEn = this.getAttribute('data-maintext-en');
            var textAlign = this.getAttribute('data-text-align');

            document.getElementById('editForm').reset();
            document.getElementById('editModalLabel').textContent = 'Edit Sekilas Perusahaan';
            document.getElementById('editId').value = id;
            document.getElementById('editMaintext').value = maintextId;
            document.getElementById('edit' + textAlign.charAt(0).toUpperCase() + textAlign.slice(1)).checked = true;
        });
    });

    document.getElementById('addForm').addEventListener('submit', function(event) {
        event.preventDefault();
        var form = document.getElementById('addForm');
        var formData = new FormData(form);

        fetch(`{{ route('admin.landingpage.sekilas.store') }}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('addModal'));
                modal.hide();
                
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'An unexpected error occurred.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });

    document.getElementById('editForm').addEventListener('submit', function(event) {
        event.preventDefault();
        var id = document.getElementById('editId').value;
        var form = document.getElementById('editForm');
        var formData = new FormData(form);

        fetch(`{{ route('admin.landingpage.sekilas.update', '') }}/${id}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                modal.hide();
                
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'An unexpected error occurred.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });

    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ route('admin.landingpage.sekilas.destroy', '') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Deleted!',
                                data.message,
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to delete');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            error.message || 'An unexpected error occurred.',
                            'error'
                        );
                    });
                }
            });
        });
    });
</script>
@endsection
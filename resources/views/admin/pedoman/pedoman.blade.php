@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Manajemen Pedoman</h2>

    <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#pedomanModal" id="addPedomanBtn">
        Tambah Pedoman
    </button>

    <table class="table mt-4">
        <thead>
            <tr>
                <th>Gambar</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedomans as $pedoman)
            <tr>
                <td>
                    @if($pedoman->gambar)
                        <img src="{{ asset('storage/images/' . $pedoman->gambar) }}" 
                             alt="Preview" style="max-width: 100px;">
                    @endif
                </td>
                <td>{{ $pedoman->judul }}</td>
                <td style="text-align: {{ $pedoman->text_align }};">
                    {{ $pedoman->deskripsi }}
                </td>
                <td><a href="{{ asset('storage/documents/' . $pedoman->file) }}" target="_blank">Lihat File</a></td>
                <td>
                    <button type="button" class="btn btn-sm btn-warning editBtn" 
                        data-id="{{ $pedoman->id }}" 
                        data-judul="{{ $pedoman->judul }}" 
                        data-file="{{ $pedoman->file }}"
                        data-gambar="{{ $pedoman->gambar }}"
                        data-deskripsi="{{ $pedoman->deskripsi }}"
                        data-text-align="{{ $pedoman->text_align }}">
                        Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger deleteBtn" data-id="{{ $pedoman->id }}">Hapus</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal Pedoman -->
<div class="modal fade" id="pedomanModal" tabindex="-1" role="dialog" aria-labelledby="pedomanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pedomanModalLabel">Tambah Pedoman</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="pedomanForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="id" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="gambar">Gambar</label>
                        <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                        <img id="previewImage" src="#" alt="Preview" style="max-width: 100%; margin-top: 10px; display: none;">
                    </div>
                    <div id="currentImage" style="display: none;">
                        <img id="previewImage" src="" alt="Preview" style="max-width: 200px; margin: 10px 0;">
                    </div>
                    <div class="form-group">
                        <label for="judul">Judul</label>
                        <input type="text" class="form-control" id="judul" name="judul" required>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="text_align">Text Alignment</label>
                        <div class="btn-group d-flex" role="group" aria-label="Text alignment">
                            <input type="radio" class="btn-check" name="text_align" id="alignLeft" value="left" required>
                            <label class="btn btn-outline-primary" for="alignLeft">
                                <i class="fa-solid fa-align-left"></i> Left
                            </label>

                            <input type="radio" class="btn-check" name="text_align" id="alignCenter" value="center">
                            <label class="btn btn-outline-primary" for="alignCenter">
                                <i class="fa-solid fa-align-center"></i> Center
                            </label>

                            <input type="radio" class="btn-check" name="text_align" id="alignRight" value="right">
                            <label class="btn btn-outline-primary" for="alignRight">
                                <i class="fa-solid fa-align-right"></i> Right
                            </label>

                            <input type="radio" class="btn-check" name="text_align" id="alignJustify" value="justify">
                            <label class="btn btn-outline-primary" for="alignJustify">
                                <i class="fa-solid fa-align-justify"></i> Justify
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="file">File (PDF, Word, Excel)</label>
                        <input type="file" class="form-control-file" id="file" name="file">
                    </div>
                    <div id="currentFile" style="display: none;">
                        <p>File saat ini: <span id="currentFileName"></span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    let isEditing = false;
    let editId = null;

    document.getElementById('addPedomanBtn').addEventListener('click', function() {
        isEditing = false;
        editId = null;
        document.getElementById('pedomanForm').reset();
        document.getElementById('pedomanModalLabel').textContent = 'Tambah Pedoman';
        document.getElementById('currentFile').style.display = 'none';
        document.getElementById('id').value = '';
        document.getElementById('file').required = true;
        document.getElementById('previewImage').src = '#';
        document.getElementById('previewImage').style.display = 'none';
    });

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            isEditing = true;
            editId = this.getAttribute('data-id');
            var judul = this.getAttribute('data-judul');
            var file = this.getAttribute('data-file');
            var gambar = this.getAttribute('data-gambar');
            var deskripsi = this.getAttribute('data-deskripsi');
            var textAlign = this.getAttribute('data-text-align');

            document.getElementById('pedomanModalLabel').textContent = 'Edit Pedoman';
            document.getElementById('id').value = editId;
            document.getElementById('judul').value = judul;
            document.getElementById('deskripsi').value = deskripsi;
            
            // Set text alignment radio button
            const alignmentRadio = document.querySelector(`input[name="text_align"][value="${textAlign}"]`);
            if (alignmentRadio) {
                alignmentRadio.checked = true;
            }

            document.getElementById('currentFileName').textContent = file;
            document.getElementById('currentFile').style.display = 'block';
            document.getElementById('file').required = false;

            // Update preview image
            if (gambar) {
                document.getElementById('previewImage').src = '/storage/images/' + gambar;
                document.getElementById('previewImage').style.display = 'block';
                document.getElementById('currentImage').style.display = 'block';
            } else {
                document.getElementById('previewImage').style.display = 'none';
                document.getElementById('currentImage').style.display = 'none';
            }

            $('#pedomanModal').modal('show');
        });
    });

    document.getElementById('pedomanForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        
        let url = isEditing 
            ? `{{ route('admin.pedoman.pedoman.update', '') }}/${editId}`
            : '{{ route('admin.pedoman.pedoman.store') }}';

        if (isEditing) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                if (!response.ok) {
                    if (response.status === 422) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        throw new Error(errorMessages);
                    }
                    throw new Error(data.message || 'Network response was not ok');
                }
                return data;
            } else {
                throw new Error('Response was not JSON');
            }
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success'
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
                text: error.message || 'An unexpected error occurred. Please try again.',
                icon: 'error'
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
                    fetch(`{{ route('admin.pedoman.pedoman.destroy', '') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
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
                                'Your file has been deleted.',
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to delete item');
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
                }
            });
        });
    });

    document.getElementById('gambar').addEventListener('change', function() {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('previewImage').style.display = 'block';
        };
        if (this.files[0]) {
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>

@endsection

@extends($layout)

@section('content')
<div class="container">
    <h1>Manage Digital Solution Images</h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Gambar 1</h5>
                </div>
                <div class="card-body">
                    @if($gambards && $gambards->gambar1)
                        <img src="{{ asset('storage/' . $gambards->gambar1) }}" class="img-fluid mb-2" alt="Gambar 1">
                        <div>
                            <button class="btn btn-warning editBtn" data-id="{{ $gambards->id }}" data-gambar="gambar1" data-image="{{ asset('storage/' . $gambards->gambar1) }}" data-bs-toggle="modal" data-bs-target="#imageModal">Edit</button>
                            <button class="btn btn-danger deleteBtn" data-id="{{ $gambards->id }}" data-gambar="gambar1">Delete</button>
                        </div>
                    @else
                        <p>No image uploaded</p>
                        <button class="btn btn-primary addBtn" data-gambar="gambar1" data-bs-toggle="modal" data-bs-target="#imageModal">Add Image</button>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Gambar 2</h5>
                </div>
                <div class="card-body">
                    @if($gambards && $gambards->gambar2)
                        <img src="{{ asset('storage/' . $gambards->gambar2) }}" class="img-fluid mb-2" alt="Gambar 2">
                        <div>
                            <button class="btn btn-warning editBtn" data-id="{{ $gambards->id }}" data-gambar="gambar2" data-image="{{ asset('storage/' . $gambards->gambar2) }}" data-bs-toggle="modal" data-bs-target="#imageModal">Edit</button>
                            <button class="btn btn-danger deleteBtn" data-id="{{ $gambards->id }}" data-gambar="gambar2">Delete</button>
                        </div>
                    @else
                        <p>No image uploaded</p>
                        <button class="btn btn-primary addBtn" data-gambar="gambar2" data-bs-toggle="modal" data-bs-target="#imageModal">Add Image</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="imageForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Add/Edit Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id" name="id" value="{{ $gambards ? $gambards->id : '' }}">
                    <input type="hidden" id="gambarType" name="gambarType">
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" class="form-control" id="image" name="image">
                        <img id="previewImage" src="#" alt="Image Preview" style="width: 100%; margin-top: 10px; display: none;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    document.querySelectorAll('.addBtn, .editBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id') || '';
            var gambar = this.getAttribute('data-gambar');
            var image = this.getAttribute('data-image') || '';

            document.getElementById('imageForm').reset();
            document.getElementById('imageModalLabel').textContent = id ? 'Edit Image' : 'Add Image';
            document.getElementById('id').value = id;
            document.getElementById('gambarType').value = gambar;
            document.getElementById('previewImage').src = image;
            document.getElementById('previewImage').style.display = image ? 'block' : 'none';
        });
    });

    document.getElementById('imageForm').addEventListener('submit', function(event) {
        event.preventDefault();
        var id = document.getElementById('id').value;
        var form = document.getElementById('imageForm');
        var formData = new FormData(form);
        var url = id ? `{{ route('admin.digitalsolution.gambards.update', '') }}/${id}` : `{{ route('admin.digitalsolution.gambards.store') }}`;
        var method = id ? 'PUT' : 'POST';

        if (method === 'PUT') {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: 'POST', // Tetap menggunakan 'POST' di sini
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('imageModal'));
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
            var gambar = this.getAttribute('data-gambar');
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
                    fetch(`{{ route('admin.digitalsolution.gambards.destroy', '') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ gambar: gambar })
                    })
                    .then(response => response.json())
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

    document.getElementById('image').addEventListener('change', function() {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('previewImage').style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
    });
</script>
@endsection
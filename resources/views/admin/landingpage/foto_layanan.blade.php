@extends($layout)

@section('content')
<div class="container">
    <h1>Manage Foto Layanan</h1>

    <div class="row">
        @foreach(['gambar_direksi_1' => 'Gambar Direksi 1', 'gambar_direksi_2' => 'Gambar Direksi 2', 'jejak_langkah' => 'Layanan Area'] as $key => $label)
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>{{ $label }}</h5>
                    </div>
                    <div class="card-body">
                        @if($fotoLayanan && $fotoLayanan->$key)
                            <img src="{{ asset('storage/' . $fotoLayanan->$key) }}" class="img-fluid mb-2" alt="{{ $label }}">
                            <div>
                                <button class="btn btn-warning editBtn" data-id="{{ $fotoLayanan->id }}" data-image="{{ $key }}" data-src="{{ asset('storage/' . $fotoLayanan->$key) }}" data-bs-toggle="modal" data-bs-target="#fotoLayananModal">Edit</button>
                                <button class="btn btn-danger deleteBtn" data-id="{{ $fotoLayanan->id }}" data-image="{{ $key }}">Delete</button>
                            </div>
                        @else
                            <p>No image uploaded</p>
                            <button class="btn btn-primary addBtn" data-image="{{ $key }}" data-bs-toggle="modal" data-bs-target="#fotoLayananModal">Add Image</button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="fotoLayananModal" tabindex="-1" aria-labelledby="fotoLayananModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="fotoLayananForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="fotoLayananModalLabel">Add/Edit Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id" name="id" value="{{ $fotoLayanan ? $fotoLayanan->id : '' }}">
                    <input type="hidden" id="imageType" name="imageType">
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
            var image = this.getAttribute('data-image');
            var src = this.getAttribute('data-src') || '';

            document.getElementById('fotoLayananForm').reset();
            document.getElementById('fotoLayananModalLabel').textContent = id ? 'Edit Image' : 'Add Image';
            document.getElementById('id').value = id;
            document.getElementById('imageType').value = image;
            document.getElementById('previewImage').src = src;
            document.getElementById('previewImage').style.display = src ? 'block' : 'none';
        });
    });

    document.getElementById('fotoLayananForm').addEventListener('submit', function(event) {
        event.preventDefault();
        var id = document.getElementById('id').value;
        var form = document.getElementById('fotoLayananForm');
        var formData = new FormData(form);
        var url = id ? `/fotolayanan/update/${id}` : '/fotolayanan/store';

        formData.append('_method', id ? 'PUT' : 'POST');

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })  
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('fotoLayananModal'));
                modal.hide();
                
                Swal.fire({
                    title: 'Success!',
                    text: 'Image saved successfully.',
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
            var image = this.getAttribute('data-image');
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
                    fetch(`/fotolayanan/destroy/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ image: image })
                    })
                    .then(response => response.json())
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
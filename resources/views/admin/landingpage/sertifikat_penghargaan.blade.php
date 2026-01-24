@extends($layout)

@section('content')
<div class="container">
    <h1>Manage Sertifikat & Penghargaan</h1>
    <button class="btn btn-primary" id="addCertificateBtn" data-bs-toggle="modal" data-bs-target="#certificateModal">Add Sertifikat/Penghargaan</button>
    <table class="table mt-4">
        <thead>
            <tr>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td><img src="{{ asset('storage/' . $item->image) }}" width="100" height="50" /></td>
                <td>
                    <button class="btn btn-warning editBtn" data-id="{{ $item->id }}" data-image="{{ asset('storage/' . $item->image) }}" data-bs-toggle="modal" data-bs-target="#certificateModal">Edit</button>
                    <button class="btn btn-danger deleteBtn" data-id="{{ $item->id }}">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="certificateModal" tabindex="-1" aria-labelledby="certificateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="certificateForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="certificateModalLabel">Add/Edit Sertifikat/Penghargaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function handleResponse(response) {
        if (!response.ok) {
            return response.json().then(errorData => {
                if (response.status === 422 && errorData.message && errorData.message.image) {
                    throw new Error('Maaf file yang anda unggah lebih dari 20 MB');
                } else {
                    throw new Error(`HTTP error! status: ${response.status}, message: ${JSON.stringify(errorData)}`);
                }
            });
        }
        return response.json();
    }

    document.getElementById('addCertificateBtn').addEventListener('click', function() {
        document.getElementById('certificateForm').reset();
        document.getElementById('certificateModalLabel').textContent = 'Add Sertifikat/Penghargaan';
        document.getElementById('previewImage').style.display = 'none';
        document.getElementById('id').value = '';
    });

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var image = this.getAttribute('data-image');

            document.getElementById('certificateModalLabel').textContent = 'Edit Sertifikat/Penghargaan';
            document.getElementById('id').value = id;
            document.getElementById('previewImage').src = image;
            document.getElementById('previewImage').style.display = 'block';
        });
    });

    document.getElementById('certificateForm').addEventListener('submit', function(event) {
        event.preventDefault();
        var id = document.getElementById('id').value;
        var form = document.getElementById('certificateForm');
        var formData = new FormData(form);
        var url = id 
            ? "{{ route('admin.landingpage.sertifikat-penghargaan.update', ':id') }}".replace(':id', id)
            : "{{ route('admin.landingpage.sertifikat-penghargaan.store') }}";
        var method = id ? 'POST' : 'POST';

        if (id) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: method,
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('certificateModal'));
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
                throw new Error(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Peringatan!',
                text: error.message,
                icon: 'warning',
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
                    var url = "{{ route('admin.landingpage.sertifikat-penghargaan.destroy', ':id') }}".replace(':id', id);
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(handleResponse)
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
                            throw new Error(data.message || 'An error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: error.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
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



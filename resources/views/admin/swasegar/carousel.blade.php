@extends($layout)

@section('content')
<div class="container">
    <h1>Manage Swasegar Carousel</h1>
    <button class="btn btn-primary" id="addCarouselBtn" data-bs-toggle="modal" data-bs-target="#carouselModal">Add Carousel</button>
    <table class="table mt-4">
        <thead>
            <tr>
                <th>Title</th>
                <th>Image</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($carousels as $carousel)
            <tr>
                <td>{{ $carousel->title }}</td>
                <td><img src="{{ asset('storage/'.$carousel->image) }}" alt="{{ $carousel->title }}" width="100" height="50"></td>
                <td>{{ $carousel->description }}</td>
                <td>
                    <button class="btn btn-warning editBtn" data-id="{{ $carousel->id }}" data-title="{{ $carousel->title }}" data-description="{{ $carousel->description }}" data-image="{{ asset('storage/'.$carousel->image) }}">Edit</button>
                    <button class="btn btn-danger deleteBtn" data-id="{{ $carousel->id }}">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="carouselModal" tabindex="-1" aria-labelledby="carouselModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="carouselForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="carouselModalLabel">Add/Edit Carousel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Title (Optional)</label>
                        <input type="text" class="form-control" id="title" name="title">
                    </div>
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" class="form-control" id="image" name="image">
                        <img id="previewImage" src="#" alt="Image Preview" style="width: 100%; margin-top: 10px; display: none;">
                    </div>
                    <div class="form-group">
                        <label for="description">Description (Optional)</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    document.getElementById('addCarouselBtn').addEventListener('click', function() {
        document.getElementById('carouselForm').reset();
        document.getElementById('carouselModalLabel').textContent = 'Add Carousel';
        document.getElementById('previewImage').style.display = 'none';
        document.getElementById('id').value = '';
    });

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var title = this.getAttribute('data-title');
            var description = this.getAttribute('data-description');
            var image = this.getAttribute('data-image');

            document.getElementById('carouselModalLabel').textContent = 'Edit Carousel';
            document.getElementById('id').value = id;
            document.getElementById('title').value = title;
            document.getElementById('description').value = description;
            document.getElementById('previewImage').src = image;
            document.getElementById('previewImage').style.display = 'block';

            var modal = new bootstrap.Modal(document.getElementById('carouselModal'));
            modal.show();
        });
    });

    document.getElementById('carouselForm').addEventListener('submit', function(event) {
        event.preventDefault();
        var id = document.getElementById('id').value;
        var form = document.getElementById('carouselForm');
        var formData = new FormData(form);
        var url = id ? `{{ route('admin.swasegar.carousel.update', '') }}/${id}` : `{{ route('admin.swasegar.carousel.store') }}`;
        var method = id ? 'POST' : 'POST';

        if (id) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: method,
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Menggunakan jQuery untuk menutup modal (pastikan jQuery dimuat)
                $('#carouselModal').modal('hide');
                
                Swal.fire({
                    title: 'Success!',
                    text: 'Carousel saved successfully.',
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
                    fetch(`{{ route('admin.swasegar.carousel.destroy', '') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Deleted!',
                                'Your carousel has been deleted.',
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to delete carousel');
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

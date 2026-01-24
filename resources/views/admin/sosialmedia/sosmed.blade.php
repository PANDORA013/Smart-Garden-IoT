@extends($layout)

@section('content')
<div class="container">
    <h1>Social Media Links</h1>
    <button class="btn btn-primary" id="addSocialBtn" data-bs-toggle="modal" data-bs-target="#socialModal">Tambah Social Media</button>
    
    <table class="table mt-4">
        <thead>
            <tr>
                <th>Tipe</th>
                <th>URL</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if($social->facebook)
            <tr>
                <td>Facebook</td>
                <td>{{ $social->facebook }}</td>
                <td>
                    <button class="btn btn-warning editBtn" 
                        data-id="{{ $social->id }}"
                        data-type="facebook"
                        data-url="{{ $social->facebook }}"
                        data-bs-toggle="modal" 
                        data-bs-target="#socialModal">Edit</button>
                    <button class="btn btn-danger deleteBtn" 
                        data-id="{{ $social->id }}"
                        data-type="facebook">Delete</button>
                </td>
            </tr>
            @endif

            @if($social->youtube)
            <tr>
                <td>YouTube</td>
                <td>{{ $social->youtube }}</td>
                <td>
                    <button class="btn btn-warning editBtn" 
                        data-id="{{ $social->id }}"
                        data-type="youtube"
                        data-url="{{ $social->youtube }}"
                        data-bs-toggle="modal" 
                        data-bs-target="#socialModal">Edit</button>
                    <button class="btn btn-danger deleteBtn" 
                        data-id="{{ $social->id }}"
                        data-type="youtube">Delete</button>
                </td>
            </tr>
            @endif

            @if($social->youtube_landing)
            <tr>
                <td>YouTube Landing</td>
                <td>{{ $social->youtube_landing }}</td>
                <td>
                    <button class="btn btn-warning editBtn" 
                        data-id="{{ $social->id }}"
                        data-type="youtube_landing"
                        data-url="{{ $social->youtube_landing }}"
                        data-bs-toggle="modal" 
                        data-bs-target="#socialModal">Edit</button>
                    <button class="btn btn-danger deleteBtn" 
                        data-id="{{ $social->id }}"
                        data-type="youtube_landing">Delete</button>
                </td>
            </tr>
            @endif

            @if($social->whatsapp)
            <tr>
                <td>WhatsApp</td>
                <td>{{ $social->whatsapp }}</td>
                <td>
                    <button class="btn btn-warning editBtn" 
                        data-id="{{ $social->id }}"
                        data-type="whatsapp"
                        data-url="{{ $social->whatsapp }}"
                        data-bs-toggle="modal" 
                        data-bs-target="#socialModal">Edit</button>
                    <button class="btn btn-danger deleteBtn" 
                        data-id="{{ $social->id }}"
                        data-type="whatsapp">Delete</button>
                </td>
            </tr>
            @endif

            @if($social->instagram)
            <tr>
                <td>Instagram</td>
                <td>{{ $social->instagram }}</td>
                <td>
                    <button class="btn btn-warning editBtn" 
                        data-id="{{ $social->id }}"
                        data-type="instagram"
                        data-url="{{ $social->instagram }}"
                        data-bs-toggle="modal" 
                        data-bs-target="#socialModal">Edit</button>
                    <button class="btn btn-danger deleteBtn" 
                        data-id="{{ $social->id }}"
                        data-type="instagram">Delete</button>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="socialModal" tabindex="-1" aria-labelledby="socialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="socialForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="socialModalLabel">Add Social Media</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="form-group">
                        <label for="type">Tipe</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="facebook">Facebook</option>
                            <option value="youtube">YouTube</option>
                            <option value="youtube_landing">YouTube Landing</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="instagram">Instagram</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="url">URL</label>
                        <input type="url" class="form-control" id="url" name="url" required>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let isEditing = false;
    let editId = null;

    document.getElementById('addSocialBtn').addEventListener('click', function() {
        isEditing = false;
        editId = null;
        document.getElementById('socialForm').reset();
        document.getElementById('socialModalLabel').textContent = 'Add Social Media';
        document.getElementById('id').value = '';
    });

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            isEditing = true;
            editId = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            const url = this.getAttribute('data-url');

            document.getElementById('socialModalLabel').textContent = 'Edit Social Media';
            document.getElementById('id').value = editId;
            document.getElementById('type').value = type;
            document.getElementById('url').value = url;
        });
    });

    document.getElementById('socialForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        
        let url = isEditing 
            ? '{{ route("admin.sosialmedia.sosmed.update", ":id") }}'.replace(':id', editId)
            : '{{ route("admin.sosialmedia.sosmed.store") }}';

        if (isEditing) {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
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
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'An unexpected error occurred',
                timer: 3000,
                showConfirmButton: false
            });
        });
    });

    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            
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
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('type', type);

                    fetch(`{{ route('admin.sosialmedia.sosmed.destroy', ':id') }}`.replace(':id', id), {
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message,
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Failed to delete');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: error.message || 'An unexpected error occurred',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection

@extends($layout)

@section('content')
<div class="container">
    <h2>Kelola FAQ</h2>
    
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Tambah FAQ Baru</button>
    
    <table class="table table-bordered">
        <tr>
            <th>No.</th>
            <th>
                Pertanyaan & Jawaban
                <div class="btn-group ms-2" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary active" onclick="toggleLanguage('id')">Indonesia</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleLanguage('en')">English</button>
                </div>
            </th>
            <th>Alignment</th>
            <th width="280px">Action</th>
        </tr>
        @foreach ($faqs as $faq)
        <tr>
            <td>{{ $faq->id }}</td>
            <td style="text-align: {{ $faq->text_align }};">
                <div class="content-id">
                    <strong>Q: </strong>{!! nl2br(e($faq->content['id']['pertanyaan'])) !!}<br>
                    <strong>A: </strong>{!! nl2br(e($faq->content['id']['jawaban'])) !!}
                </div>
                <div class="content-en" style="display: none;">
                    <strong>Q: </strong>{!! nl2br(e($faq->content['en']['pertanyaan'])) !!}<br>
                    <strong>A: </strong>{!! nl2br(e($faq->content['en']['jawaban'])) !!}
                </div>
            </td>
            <td>{{ ucfirst($faq->text_align) }}</td>
            <td>
                <button class="btn btn-warning" data-toggle="modal" data-target="#editModal{{ $faq->id }}">Edit</button>
                <button class="btn btn-danger" onclick="confirmDelete('{{ route('admin.kontakkami.faq.destroy', $faq->id) }}')">Delete</button>
            </td>
        </tr>

        <!-- Modal Edit -->
        <div class="modal fade" id="editModal{{ $faq->id }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit FAQ</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.kontakkami.faq.update', $faq->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Pertanyaan:</label>
                                <textarea class="form-control" name="pertanyaan" rows="3" required>{{ $faq->content['id']['pertanyaan'] }}</textarea>
                            </div>
                            <div class="form-group mt-3">
                                <label>Jawaban:</label>
                                <textarea class="form-control" name="jawaban" rows="5" required>{{ $faq->content['id']['jawaban'] }}</textarea>
                            </div>
                            <div class="form-group mt-3">
                                <label>Text Alignment:</label>
                                <div class="btn-group d-flex" role="group">
                                    <input type="radio" class="btn-check" name="text_align" id="alignLeft{{ $faq->id }}" value="left" {{ $faq->text_align == 'left' ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-primary" for="alignLeft{{ $faq->id }}">
                                        <i class="fas fa-align-left"></i> Left
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="text_align" id="alignCenter{{ $faq->id }}" value="center" {{ $faq->text_align == 'center' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="alignCenter{{ $faq->id }}">
                                        <i class="fas fa-align-center"></i> Center
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="text_align" id="alignRight{{ $faq->id }}" value="right" {{ $faq->text_align == 'right' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="alignRight{{ $faq->id }}">
                                        <i class="fas fa-align-right"></i> Right
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="text_align" id="alignJustify{{ $faq->id }}" value="justify" {{ $faq->text_align == 'justify' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="alignJustify{{ $faq->id }}">
                                        <i class="fas fa-align-justify"></i> Justify
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </table>

    <!-- Modal Add -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah FAQ Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.kontakkami.faq.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Pertanyaan:</label>
                            <textarea class="form-control" name="pertanyaan" rows="3" required></textarea>
                        </div>
                        <div class="form-group mt-3">
                            <label>Jawaban:</label>
                            <textarea class="form-control" name="jawaban" rows="5" required></textarea>
                        </div>
                        <div class="form-group mt-3">
                            <label>Text Alignment:</label>
                            <div class="btn-group d-flex" role="group">
                                <input type="radio" class="btn-check" name="text_align" id="addAlignLeft" value="left" required>
                                <label class="btn btn-outline-primary" for="addAlignLeft">
                                    <i class="fas fa-align-left"></i> Left
                                </label>
                                
                                <input type="radio" class="btn-check" name="text_align" id="addAlignCenter" value="center">
                                <label class="btn btn-outline-primary" for="addAlignCenter">
                                    <i class="fas fa-align-center"></i> Center
                                </label>
                                
                                <input type="radio" class="btn-check" name="text_align" id="addAlignRight" value="right">
                                <label class="btn btn-outline-primary" for="addAlignRight">
                                    <i class="fas fa-align-right"></i> Right
                                </label>
                                
                                <input type="radio" class="btn-check" name="text_align" id="addAlignJustify" value="justify">
                                <label class="btn btn-outline-primary" for="addAlignJustify">
                                    <i class="fas fa-align-justify"></i> Justify
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(url) {
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
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    Swal.fire(
                        'Deleted!',
                        response.success,
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire(
                        'Error!',
                        xhr.responseJSON.error,
                        'error'
                    );
                }
            });
        }
    })
}

// Check for flash messages and display SweetAlert2
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: "{{ session('success') }}",
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: "{{ session('error') }}",
    });
@endif

// Fungsi untuk form submission
$('form').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.success,
            }).then(() => {
                location.reload();
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: xhr.responseJSON.error || 'Something went wrong!',
            });
        }
    });
});

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
</script>
@endpush

@endsection

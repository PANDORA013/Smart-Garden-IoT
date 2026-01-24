@extends($layout)
@section('content')
<div class="container">
    <h2>Manage Texts</h2>
    
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Add New Text</button>
    
    <table class="table table-bordered">
        <tr>
            {{-- <th>ID</th> --}}
            <th>Text</th>
            <th>Link</th>
            <th>Alignment</th>
            <th width="280px">Action</th>
        </tr>
        @foreach ($texts as $text)
        <tr>
            {{-- <td>{{ $text->id }}</td> --}}
            <td style="text-align: {{ $text->text_align }}; white-space: pre-wrap;">{!! nl2br(e($text->content)) !!}</td>
            <td><a href="{{ $text->link }}" target="_blank">{{ $text->link }}</a></td>
            <td>{{ ucfirst($text->text_align) }}</td>
            <td>
                <button class="btn btn-warning" data-toggle="modal" data-target="#editModal{{ $text->id }}">Edit</button>
                <button class="btn btn-danger" onclick="confirmDelete('{{ route('admin.kontakkami.textkk.destroy', $text->id) }}')">Delete</button>
            </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal{{ $text->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $text->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel{{ $text->id }}">Edit Text</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.kontakkami.textkk.update', $text->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="content{{ $text->id }}">Text:</label>
                                <textarea class="form-control" id="content{{ $text->id }}" name="content" rows="4" required style="white-space: pre-wrap;">{{ $text->content }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="link{{ $text->id }}">Link:</label>
                                <input type="url" class="form-control" id="link{{ $text->id }}" name="link" value="{{ $text->link }}" required>
                            </div>
                            <div class="form-group">
                                <label for="text_align{{ $text->id }}">Text Alignment:</label>
                                <div class="btn-group d-flex" role="group" aria-label="Text alignment">
                                    <input type="radio" class="btn-check" name="text_align" id="alignLeft{{ $text->id }}" value="left" {{ $text->text_align == 'left' ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-primary" for="alignLeft{{ $text->id }}">
                                        <i class="fa-solid fa-align-left"></i> Left
                                    </label>
                            
                                    <input type="radio" class="btn-check" name="text_align" id="alignCenter{{ $text->id }}" value="center" {{ $text->text_align == 'center' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="alignCenter{{ $text->id }}">
                                        <i class="fa-solid fa-align-center"></i> Center
                                    </label>
                            
                                    <input type="radio" class="btn-check" name="text_align" id="alignRight{{ $text->id }}" value="right" {{ $text->text_align == 'right' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="alignRight{{ $text->id }}">
                                        <i class="fa-solid fa-align-right"></i> Right
                                    </label>
                            
                                    <input type="radio" class="btn-check" name="text_align" id="alignJustify{{ $text->id }}" value="justify" {{ $text->text_align == 'justify' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="alignJustify{{ $text->id }}">
                                        <i class="fa-solid fa-align-justify"></i> Justify
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </table>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Add New Text</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.kontakkami.textkk.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="content">Text:</label>
                            <textarea class="form-control" id="content" name="content" rows="4" required style="white-space: pre-wrap;"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="link">Link:</label>
                            <input type="url" class="form-control" id="link" name="link" required>
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
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
                    <h5 class="modal-title" id="deleteModalLabel">Delete Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this data?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Show success message if exists in session
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: "{{ session('success') }}",
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    // Confirm delete
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
                // Create a form and submit it
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Show SweetAlert2 for successful form submission
    $('form').submit(function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            },
        });
        form.submit();
    });
</script>
@endpush
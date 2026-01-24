@extends($layout)

@section('content')
<div class="container">
    <h2>Manage Texts</h2>
    
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Add New Text</button>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>
                    Text
                    <div class="btn-group ms-2" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary active" onclick="toggleLanguage('id')">Indonesia</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleLanguage('en')">English</button>
                    </div>
                </th>
                <th>Alignment</th>
                <th width="280px">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($texts as $text)
            <tr>
                <td>{{ $text->id }}</td>
                <td style="text-align: {{ $text->text_align }}; white-space: pre-wrap;">
                    <div class="content-id">{!! nl2br(e($text->content['id'])) !!}</div>
                    <div class="content-en" style="display: none;">{!! nl2br(e($text->content['en'])) !!}</div>
                </td>
                <td>{{ ucfirst($text->text_align) }}</td>
                <td>
                    <button class="btn btn-warning" data-toggle="modal" data-target="#editModal{{ $text->id }}">Edit</button>
                    <button class="btn btn-danger" onclick="confirmDelete('{{ route('admin.swaacademy.textSA.destroy', $text->id) }}')">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
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
                    <form action="{{ route('admin.swaacademy.textSA.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="text">Text:</label>
                            <textarea class="form-control" id="text" name="text" rows="4" required style="white-space: pre-wrap;"></textarea>
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
                document.getElementById('deleteForm').action = url;
                document.getElementById('deleteForm').submit();
            }
        });
    }

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
        });
    @endif

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
@endsection
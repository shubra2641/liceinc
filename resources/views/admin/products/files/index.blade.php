@extends('layouts.admin')

@section('title', trans('app.Product File Management') . ' - ' . $product->name)

@section('admin-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-file-upload mr-2"></i>
                            {{ trans('app.Product File Management') }}: {{ $product->name }}
                        </h3>
                        <div>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>
                                {{ trans('app.Back to Products') }}
                            </a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-info">
                                <i class="fas fa-edit mr-1"></i>
                                {{ trans('app.Edit Product') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Files List -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-files mr-2"></i>
                                        {{ trans('app.Product Files') }} ({{ $files->count() }})
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($files->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>{{ trans('app.File Name') }}</th>
                                                        <th>{{ trans('app.File Type') }}</th>
                                                        <th>{{ trans('app.File Size') }}</th>
                                                        <th>{{ trans('app.Description') }}</th>
                                                        <th>{{ trans('app.Download Count') }}</th>
                                                        <th>{{ trans('app.Status') }}</th>
                                                        <th>{{ trans('app.Upload Date') }}</th>
                                                        <th>{{ trans('app.Actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="filesTableBody">
                                                    @foreach($files as $file)
                                                        <tr data-file-id="{{ $file->id }}">
                                                            <td>
                                                                <i class="fas fa-file mr-2"></i>
                                                                {{ $file->original_name }}
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-info">{{ $file->file_type }}</span>
                                                            </td>
                                                            <td>{{ $file->formatted_size }}</td>
                                                            <td>{{ $file->description ?? '-' }}</td>
                                                            <td>
                                                                <span class="badge badge-success">{{ $file->download_count }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-{{ $file->is_active ? 'success' : 'danger' }}">
                                                                    {{ $file->is_active ? trans('app.Active') : trans('app.Inactive') }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $file->created_at->format('Y-m-d H:i') }}</td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="{{ route('admin.product-files.download', $file) }}" 
                                                                       class="btn btn-sm btn-info" title="{{ trans('app.Download') }}" target="_blank">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                    <button type="button" class="btn btn-sm btn-warning edit-file-btn" 
                                                                            data-file-id="{{ $file->id }}" 
                                                                            data-description="{{ $file->description }}"
                                                                            data-is-active="{{ $file->is_active }}" title="{{ trans('app.Edit') }}">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-danger delete-file-btn" 
                                                                            data-file-id="{{ $file->id }}" 
                                                                            data-filename="{{ $file->original_name }}" title="{{ trans('app.Delete') }}">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">{{ trans('app.No files uploaded') }}</h5>
                                            <p class="text-muted">{{ trans('app.Start by uploading the first file for this product') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit File Modal -->
<div class="modal fade" id="editFileModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('app.Edit File') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editFileForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_description">{{ trans('app.File Description') }}</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">
                                {{ trans('app.File is active and available for download') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('app.Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ trans('app.Save Changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/user/js/product-files.js') }}"></script>
<script>
$(document).ready(function() {
    // Edit File
    $('.edit-file-btn').on('click', function() {
        var fileId = $(this).data('file-id');
        var description = $(this).data('description');
        var isActive = $(this).data('is-active');
        
        $('#editFileForm').data('file-id', fileId);
        $('#edit_description').val(description);
        $('#edit_is_active').prop('checked', isActive == 1);
        
        $('#editFileModal').modal('show');
    });

    $('#editFileForm').on('submit', function(e) {
        e.preventDefault();
        
        var fileId = $(this).data('file-id');
        var formData = $(this).serialize();
        
        $.ajax({
            url: '{{ url("admin/product-files") }}/' + fileId,
            type: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#editFileModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    Object.keys(errors).forEach(function(key) {
                        toastr.error(errors[key][0]);
                    });
                } else {
                    toastr.error('{{ trans("app.File update failed") }}');
                }
            }
        });
    });

    // Delete File
    $('.delete-file-btn').on('click', function() {
        var fileId = $(this).data('file-id');
        var filename = $(this).data('filename');
        
        if (confirm('{{ trans("app.Are you sure you want to delete the file") }} "' + filename + '"?\n\n{{ trans("app.This action cannot be undone.") }}')) {
            $.ajax({
                url: '{{ url("admin/product-files") }}/' + fileId,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('tr[data-file-id="' + fileId + '"]').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    var response = xhr.responseJSON;
                    if (response && response.message) {
                        toastr.error(response.message);
                    } else {
                        toastr.error('{{ trans("app.File deletion failed") }}');
                    }
                }
            });
        }
    });
});
</script>
@endsection



<?php $__env->startSection('title', trans('app.Product File Management') . ' - ' . $product->name); ?>

<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-file-upload mr-2"></i>
                            <?php echo e(trans('app.Product File Management')); ?>: <?php echo e($product->name); ?>

                        </h3>
                        <div>
                            <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>
                                <?php echo e(trans('app.Back to Products')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="btn btn-info">
                                <i class="fas fa-edit mr-1"></i>
                                <?php echo e(trans('app.Edit Product')); ?>

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
                                        <?php echo e(trans('app.Product Files')); ?> (<?php echo e($files->count()); ?>)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if($files->count() > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo e(trans('app.File Name')); ?></th>
                                                        <th><?php echo e(trans('app.File Type')); ?></th>
                                                        <th><?php echo e(trans('app.File Size')); ?></th>
                                                        <th><?php echo e(trans('app.Description')); ?></th>
                                                        <th><?php echo e(trans('app.Download Count')); ?></th>
                                                        <th><?php echo e(trans('app.Status')); ?></th>
                                                        <th><?php echo e(trans('app.Upload Date')); ?></th>
                                                        <th><?php echo e(trans('app.Actions')); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="filesTableBody">
                                                    <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr data-file-id="<?php echo e($file->id); ?>">
                                                            <td>
                                                                <i class="fas fa-file mr-2"></i>
                                                                <?php echo e($file->original_name); ?>

                                                            </td>
                                                            <td>
                                                                <span class="badge badge-info"><?php echo e($file->file_type); ?></span>
                                                            </td>
                                                            <td><?php echo e($file->formatted_size); ?></td>
                                                            <td><?php echo e($file->description ?? '-'); ?></td>
                                                            <td>
                                                                <span class="badge badge-success"><?php echo e($file->download_count); ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-<?php echo e($file->is_active ? 'success' : 'danger'); ?>">
                                                                    <?php echo e($file->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                                                                </span>
                                                            </td>
                                                            <td><?php echo e($file->created_at->format('Y-m-d H:i')); ?></td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="<?php echo e(route('admin.product-files.download', $file)); ?>" 
                                                                       class="btn btn-sm btn-info" title="<?php echo e(trans('app.Download')); ?>" target="_blank">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                    <button type="button" class="btn btn-sm btn-warning edit-file-btn" 
                                                                            data-file-id="<?php echo e($file->id); ?>" 
                                                                            data-description="<?php echo e($file->description); ?>"
                                                                            data-is-active="<?php echo e($file->is_active); ?>" title="<?php echo e(trans('app.Edit')); ?>">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-danger delete-file-btn" 
                                                                            data-file-id="<?php echo e($file->id); ?>" 
                                                                            data-filename="<?php echo e($file->original_name); ?>" title="<?php echo e(trans('app.Delete')); ?>">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted"><?php echo e(trans('app.No files uploaded')); ?></h5>
                                            <p class="text-muted"><?php echo e(trans('app.Start by uploading the first file for this product')); ?></p>
                                        </div>
                                    <?php endif; ?>
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
                <h5 class="modal-title"><?php echo e(trans('app.Edit File')); ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editFileForm">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_description"><?php echo e(trans('app.File Description')); ?></label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">
                                <?php echo e(trans('app.File is active and available for download')); ?>

                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(trans('app.Cancel')); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo e(trans('app.Save Changes')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('assets/user/js/product-files.js')); ?>"></script>
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
            url: '<?php echo e(url("admin/product-files")); ?>/' + fileId,
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
                    toastr.error('<?php echo e(trans("app.File update failed")); ?>');
                }
            }
        });
    });

    // Delete File
    $('.delete-file-btn').on('click', function() {
        var fileId = $(this).data('file-id');
        var filename = $(this).data('filename');
        
        if (confirm('<?php echo e(trans("app.Are you sure you want to delete the file")); ?> "' + filename + '"?\n\n<?php echo e(trans("app.This action cannot be undone.")); ?>')) {
            $.ajax({
                url: '<?php echo e(url("admin/product-files")); ?>/' + fileId,
                type: 'DELETE',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>'
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
                        toastr.error('<?php echo e(trans("app.File deletion failed")); ?>');
                    }
                }
            });
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/admin/products/files/index.blade.php ENDPATH**/ ?>
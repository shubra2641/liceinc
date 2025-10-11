<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid products-form">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-edit text-primary me-2"></i>
                                <?php echo e(trans('app.Edit Product')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($product->name); ?></p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Products')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <form method="post" action="<?php echo e(route('admin.products.update', $product)); ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo e(trans('app.Basic Information')); ?>

                            <span class="badge bg-light text-primary ms-2"><?php echo e(trans('app.Required')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-tag text-primary me-1"></i>
                                    <?php echo e(trans('app.Product Name')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="name" name="name" value="<?php echo e(old('name', $product->name)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter Product Name')); ?>" required>
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-folder text-success me-1"></i>
                                    <?php echo e(trans('app.Category')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="category_id" name="category_id" required>
                                    <option value=""><?php echo e(trans('app.Select Category')); ?></option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>"
                                        <?php echo e(old('category_id', $product->category_id) == $category->id ? 'selected' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="programming_language" class="form-label">
                                    <i class="fas fa-code text-purple me-1"></i>
                                    <?php echo e(trans('app.Programming Language')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['programming_language'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="programming_language" name="programming_language" required>
                                    <option value=""><?php echo e(trans('app.Select Programming Language')); ?></option>
                                    <?php $__currentLoopData = $programmingLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($language->id); ?>"
                                        <?php echo e(old('programming_language', $product->programming_language) == $language->id ? 'selected' : ''); ?>>
                                        <i class="<?php echo e($language->icon ?? 'fas fa-code'); ?> me-2"></i><?php echo e($language->name); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['programming_language'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-globe text-warning me-1"></i>
                                    <?php echo e(trans('app.Requires Domain')); ?> <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_domain" 
                                               id="requires_domain_yes" value="1"
                                               <?php echo e(old('requires_domain', $product->requires_domain) == 1 ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="requires_domain_yes">
                                            <?php echo e(trans('app.Yes')); ?>

                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_domain" 
                                               id="requires_domain_no" value="0"
                                               <?php echo e(old('requires_domain', $product->requires_domain) == 0 ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="requires_domain_no">
                                            <?php echo e(trans('app.No')); ?>

                                        </label>
                                    </div>
                                </div>
                                <?php $__errorArgs = ['requires_domain'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger small"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left text-secondary me-1"></i>
                                <?php echo e(trans('app.Product Description')); ?>

                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="description" name="description" rows="4"
                                      data-summernote="true" data-toolbar="standard"
                                      data-placeholder="<?php echo e(trans('app.Enter Product Description')); ?>"
                                      placeholder="<?php echo e(trans('app.Enter Product Description')); ?>"><?php echo e(old('description', $product->description)); ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                <?php echo e(trans('app.Use the rich text editor to format your product description with headings, lists, links, and more.')); ?>

                            </div>
                            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus-circle me-2"></i>
                            <?php echo e(trans('app.Additional Information')); ?>

                            <span class="badge bg-light text-info ms-2"><?php echo e(trans('app.Optional')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label">
                                    <i class="fas fa-dollar-sign text-success me-1"></i>
                                    <?php echo e(trans('app.Price')); ?>

                                </label>
                                <input type="number" class="form-control <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="price" name="price" value="<?php echo e(old('price', $product->price)); ?>" 
                                       step="0.01" min="0" placeholder="0.00">
                                <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="slug" class="form-label">
                                    <i class="fas fa-link text-info me-1"></i>
                                    <?php echo e(trans('app.Slug')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="slug" name="slug" value="<?php echo e(old('slug', $product->slug)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter product slug')); ?>">
                                <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="version" class="form-label">
                                    <i class="fas fa-code-branch text-primary me-1"></i>
                                    <?php echo e(trans('app.Version')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['version'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="version" name="version" value="<?php echo e(old('version', $product->version)); ?>" 
                                       placeholder="1.0.0">
                                <?php $__errorArgs = ['version'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="envato_item_id" class="form-label">
                                    <i class="fab fa-envato text-warning me-1"></i>
                                    <?php echo e(trans('app.Envato Item ID')); ?>

                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control <?php $__errorArgs = ['envato_item_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="envato_item_id" name="envato_item_id" 
                                           value="<?php echo e(old('envato_item_id', $product->envato_item_id)); ?>"
                                           placeholder="<?php echo e(trans('app.Product Envato Item ID')); ?>">
                                    <button type="button" class="btn btn-outline-secondary" id="fetch-envato-data">
                                        <i class="fas fa-download me-1"></i><?php echo e(trans('app.Fetch')); ?>

                                    </button>
                                </div>
                                <div id="envato-loading" class="hidden mt-2">
                                    <i class="fas fa-spinner fa-spin text-primary me-2"></i>
                                    <?php echo e(trans('app.Fetching data from Envato...')); ?>

                                </div>
                                <div id="envato-error" class="hidden mt-2 text-danger small"></div>
                                <?php $__errorArgs = ['envato_item_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="purchase_url_envato" class="form-label">
                                    <i class="fab fa-envato text-warning me-1"></i>
                                    <?php echo e(trans('app.Purchase on Envato URL')); ?>

                                </label>
                                <input type="url" class="form-control <?php $__errorArgs = ['purchase_url_envato'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="purchase_url_envato" name="purchase_url_envato" 
                                       value="<?php echo e(old('purchase_url_envato', $product->purchase_url_envato)); ?>"
                                       placeholder="https://themeforest.net/item/...">
                                <?php $__errorArgs = ['purchase_url_envato'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="purchase_url_buy" class="form-label">
                                    <i class="fas fa-shopping-cart text-success me-1"></i>
                                    <?php echo e(trans('app.Buy Now URL')); ?>

                                </label>
                                <input type="url" class="form-control <?php $__errorArgs = ['purchase_url_buy'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="purchase_url_buy" name="purchase_url_buy" 
                                       value="<?php echo e(old('purchase_url_buy', $product->purchase_url_buy)); ?>"
                                       placeholder="https://yourshop.example/checkout/...">
                                <?php $__errorArgs = ['purchase_url_buy'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="support_days" class="form-label">
                                    <i class="fas fa-headset text-danger me-1"></i>
                                    <?php echo e(trans('app.Support Days')); ?>

                                </label>
                                <input type="number" class="form-control <?php $__errorArgs = ['support_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="support_days" name="support_days" 
                                       value="<?php echo e(old('support_days', $product->support_days)); ?>" 
                                       min="0" placeholder="180">
                                <div class="form-text"><?php echo e(trans('app.Number of Support Days')); ?></div>
                                <?php $__errorArgs = ['support_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="stock_quantity" class="form-label">
                                    <i class="fas fa-boxes text-warning me-1"></i>
                                    <?php echo e(trans('app.Stock Quantity')); ?>

                                </label>
                                <input type="number" class="form-control <?php $__errorArgs = ['stock_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="stock_quantity" name="stock_quantity" 
                                       value="<?php echo e(old('stock_quantity', $product->stock_quantity)); ?>" 
                                       min="-1" placeholder="-1">
                                <div class="form-text">-1 = <?php echo e(trans('app.Unlimited Stock')); ?></div>
                                <?php $__errorArgs = ['stock_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="license_type" class="form-label">
                                    <i class="fas fa-key text-primary me-1"></i>
                                    <?php echo e(trans('app.License Type')); ?>

                                </label>
                                <select class="form-select <?php $__errorArgs = ['license_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="license_type" name="license_type">
                                    <option value=""><?php echo e(trans('app.Select License Type')); ?></option>
                                    <option value="single" <?php echo e(old('license_type', $product->license_type) == 'single' ? 'selected' : ''); ?>><?php echo e(trans('app.Single Site')); ?></option>
                                    <option value="multi" <?php echo e(old('license_type', $product->license_type) == 'multi' ? 'selected' : ''); ?>><?php echo e(trans('app.Multi Site')); ?></option>
                                    <option value="developer" <?php echo e(old('license_type', $product->license_type) == 'developer' ? 'selected' : ''); ?>><?php echo e(trans('app.Developer')); ?></option>
                                    <option value="extended" <?php echo e(old('license_type', $product->license_type) == 'extended' ? 'selected' : ''); ?>><?php echo e(trans('app.Extended')); ?></option>
                                </select>
                                <?php $__errorArgs = ['license_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="renewal_price" class="form-label">
                                    <i class="fas fa-redo text-success me-1"></i>
                                    <?php echo e(trans('app.Renewal Price')); ?>

                                </label>
                                <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['renewal_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="renewal_price" name="renewal_price" 
                                       value="<?php echo e(old('renewal_price', $product->renewal_price)); ?>"
                                       placeholder="0.00">
                                <div class="form-text"><?php echo e(trans('app.Leave empty to use regular price')); ?></div>
                                <?php $__errorArgs = ['renewal_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="renewal_period" class="form-label">
                                    <i class="fas fa-calendar text-info me-1"></i>
                                    <?php echo e(trans('app.Renewal Period')); ?>

                                </label>
                                <select class="form-select <?php $__errorArgs = ['renewal_period'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="renewal_period" name="renewal_period">
                                    <option value=""><?php echo e(trans('app.Select Renewal Period')); ?></option>
                                    <option value="monthly" <?php echo e(old('renewal_period', $product->renewal_period) == 'monthly' ? 'selected' : ''); ?>><?php echo e(trans('app.Monthly')); ?></option>
                                    <option value="quarterly" <?php echo e(old('renewal_period', $product->renewal_period) == 'quarterly' ? 'selected' : ''); ?>><?php echo e(trans('app.Quarterly')); ?></option>
                                    <option value="semi-annual" <?php echo e(old('renewal_period', $product->renewal_period) == 'semi-annual' ? 'selected' : ''); ?>><?php echo e(trans('app.Semi-Annual')); ?></option>
                                    <option value="annual" <?php echo e(old('renewal_period', $product->renewal_period) == 'annual' ? 'selected' : ''); ?>><?php echo e(trans('app.Annual')); ?></option>
                                    <option value="three-years" <?php echo e(old('renewal_period', $product->renewal_period) == 'three-years' ? 'selected' : ''); ?>><?php echo e(trans('app.Three Years')); ?></option>
                                    <option value="lifetime" <?php echo e(old('renewal_period', $product->renewal_period) == 'lifetime' ? 'selected' : ''); ?>><?php echo e(trans('app.Lifetime')); ?></option>
                                </select>
                                <?php $__errorArgs = ['renewal_period'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="duration_days" class="form-label">
                                    <i class="fas fa-clock text-warning me-1"></i>
                                    <?php echo e(trans('app.License Duration (Days)')); ?>

                                </label>
                                <input type="number" class="form-control <?php $__errorArgs = ['duration_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="duration_days" name="duration_days" 
                                       value="<?php echo e(old('duration_days', $product->duration_days)); ?>" min="1"
                                       placeholder="365">
                                <div class="form-text"><?php echo e(trans('app.License validity period in days')); ?></div>
                                <?php $__errorArgs = ['duration_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tax_rate" class="form-label">
                                    <i class="fas fa-percentage text-danger me-1"></i>
                                    <?php echo e(trans('app.Tax Rate (%)')); ?>

                                </label>
                                <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['tax_rate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="tax_rate" name="tax_rate" 
                                       value="<?php echo e(old('tax_rate', $product->tax_rate)); ?>" min="0" max="100"
                                       placeholder="0.00">
                                <div class="form-text"><?php echo e(trans('app.Tax percentage applied to product price')); ?></div>
                                <?php $__errorArgs = ['tax_rate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="extended_support_price" class="form-label">
                                    <i class="fas fa-headset text-info me-1"></i>
                                    <?php echo e(trans('app.Extended Support Price')); ?>

                                </label>
                                <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['extended_support_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="extended_support_price" name="extended_support_price" 
                                       value="<?php echo e(old('extended_support_price', $product->extended_support_price)); ?>"
                                       placeholder="0.00">
                                <div class="form-text"><?php echo e(trans('app.Price for extended support')); ?></div>
                                <?php $__errorArgs = ['extended_support_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="extended_support_days" class="form-label">
                                    <i class="fas fa-calendar-plus text-success me-1"></i>
                                    <?php echo e(trans('app.Extended Support Days')); ?>

                                </label>
                                <input type="number" class="form-control <?php $__errorArgs = ['extended_support_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="extended_support_days" name="extended_support_days" 
                                       value="<?php echo e(old('extended_support_days', $product->extended_support_days)); ?>" min="0"
                                       placeholder="365">
                                <div class="form-text"><?php echo e(trans('app.Additional support days')); ?></div>
                                <?php $__errorArgs = ['extended_support_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="renewal_reminder_days" class="form-label">
                                    <i class="fas fa-bell text-warning me-1"></i>
                                    <?php echo e(trans('app.Renewal Reminder Days')); ?>

                                </label>
                                <input type="number" class="form-control <?php $__errorArgs = ['renewal_reminder_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="renewal_reminder_days" name="renewal_reminder_days" 
                                       value="<?php echo e(old('renewal_reminder_days', $product->renewal_reminder_days)); ?>" min="1"
                                       placeholder="30">
                                <div class="form-text"><?php echo e(trans('app.Days before expiry to send reminder')); ?></div>
                                <?php $__errorArgs = ['renewal_reminder_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    <?php echo e(trans('app.Status')); ?>

                                </label>
                                <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        id="status" name="status">
                                    <option value=""><?php echo e(trans('app.Select Status')); ?></option>
                                    <option value="active" <?php echo e(old('status', $product->status) == 'active' ? 'selected' : ''); ?>><?php echo e(trans('app.Active')); ?></option>
                                    <option value="inactive" <?php echo e(old('status', $product->status) == 'inactive' ? 'selected' : ''); ?>><?php echo e(trans('app.Inactive')); ?></option>
                                    <option value="draft" <?php echo e(old('status', $product->status) == 'draft' ? 'selected' : ''); ?>><?php echo e(trans('app.Draft')); ?></option>
                                    <option value="archived" <?php echo e(old('status', $product->status) == 'archived' ? 'selected' : ''); ?>><?php echo e(trans('app.Archived')); ?></option>
                                </select>
                                <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">
                                    <i class="fas fa-warehouse text-warning me-1"></i>
                                    <?php echo e(trans('app.Stock')); ?>

                                </label>
                                <input type="number" class="form-control <?php $__errorArgs = ['stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="stock" name="stock" 
                                       value="<?php echo e(old('stock', $product->stock)); ?>" min="-1"
                                       placeholder="-1">
                                <div class="form-text">-1 = <?php echo e(trans('app.Unlimited Stock')); ?></div>
                                <?php $__errorArgs = ['stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="supported_until" class="form-label">
                                    <i class="fas fa-calendar-times text-danger me-1"></i>
                                    <?php echo e(trans('app.Supported Until')); ?>

                                </label>
                                <input type="date" class="form-control <?php $__errorArgs = ['supported_until'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="supported_until" name="supported_until" 
                                       value="<?php echo e(old('supported_until', $product->supported_until ? $product->supported_until->format('Y-m-d') : '')); ?>" readonly>
                                <div class="form-text"><?php echo e(trans('app.Support end date')); ?> (<?php echo e(trans('app.Auto-calculated based on support days')); ?>)</div>
                                <?php $__errorArgs = ['supported_until'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="extended_supported_until" class="form-label">
                                    <i class="fas fa-calendar-plus text-success me-1"></i>
                                    <?php echo e(trans('app.Extended Supported Until')); ?>

                                </label>
                                <input type="date" class="form-control <?php $__errorArgs = ['extended_supported_until'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="extended_supported_until" name="extended_supported_until" 
                                       value="<?php echo e(old('extended_supported_until', $product->extended_supported_until ? $product->extended_supported_until->format('Y-m-d') : '')); ?>" readonly>
                                <div class="form-text">
                                    <?php echo e(trans('app.Extended support end date')); ?> (<?php echo e(trans('app.Auto-calculated based on renewal period')); ?>)
                                    <br><small class="text-muted"><?php echo e(trans('app.For lifetime renewal, this field will be empty')); ?></small>
                                </div>
                                <?php $__errorArgs = ['extended_supported_until'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features and Requirements -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star me-2"></i>
                            <?php echo e(trans('app.Features Requirements')); ?>

                            <span class="badge bg-light text-warning ms-2"><?php echo e(trans('app.Optional')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="features" class="form-label">
                                    <i class="fas fa-list-check text-success me-1"></i>
                                    <?php echo e(trans('app.Features')); ?>

                                </label>
                                <textarea class="form-control <?php $__errorArgs = ['features'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                          id="features" name="features" rows="6"
                                          data-summernote="true" data-toolbar="basic"
                                          data-placeholder="<?php echo e(trans('app.List product features with formatting')); ?>"
                                          placeholder="<?php echo e(trans('app.Features')); ?>"><?php echo e(old('features', is_array($product->features) ? implode("\n", $product->features) : $product->features)); ?></textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Use lists and formatting to highlight product features.')); ?>

                                </div>
                                <?php $__errorArgs = ['features'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="requirements" class="form-label">
                                    <i class="fas fa-clipboard-check text-primary me-1"></i>
                                    <?php echo e(trans('app.Requirements')); ?>

                                </label>
                                <textarea class="form-control <?php $__errorArgs = ['requirements'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                          id="requirements" name="requirements" rows="6"
                                          data-summernote="true" data-toolbar="basic"
                                          data-placeholder="<?php echo e(trans('app.List system requirements with formatting')); ?>"
                                          placeholder="<?php echo e(trans('app.Requirements')); ?>"><?php echo e(old('requirements', is_array($product->requirements) ? implode("\n", $product->requirements) : $product->requirements)); ?></textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Use lists and formatting to clearly show system requirements.')); ?>

                                </div>
                                <?php $__errorArgs = ['requirements'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="installation_guide" class="form-label">
                                <i class="fas fa-book text-purple me-1"></i>
                                <?php echo e(trans('app.Installation Guide')); ?>

                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['installation_guide'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="installation_guide" name="installation_guide" rows="4"
                                      data-summernote="true" data-toolbar="standard"
                                      data-placeholder="<?php echo e(trans('app.Create step-by-step installation guide')); ?>"
                                      placeholder="<?php echo e(trans('app.Step By Step Installation')); ?>"><?php echo e(old('installation_guide', is_array($product->installation_guide) ? implode("\n", $product->installation_guide) : $product->installation_guide)); ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                <?php echo e(trans('app.create_detailed_installation_instructions_with_headings_lists_and_formatting.')); ?>

                            </div>
                            <?php $__errorArgs = ['installation_guide'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <!-- Product Files -->
                <div class="card mb-4 product-files-section <?php echo e($product->is_downloadable ? 'show' : ''); ?>" id="product-files-section">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-upload me-2"></i>
                            <?php echo e(trans('app.Product Files')); ?>

                            <span class="badge bg-light text-success ms-2"><?php echo e(trans('app.Required for Downloadable Products')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo e(trans('app.Product files will be encrypted and stored securely. You can manage files from the product files page.')); ?>

                            <br><small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                <?php echo e(trans('app.Tip: Enable "Downloadable" option above to show this section.')); ?>

                            </small>
                        </div>
                        
                        <?php if($product->files && $product->files->count() > 0): ?>
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="fas fa-files me-2"></i>
                                <?php echo e(trans('app.Existing Files')); ?> (<?php echo e($product->files->count()); ?>)
                            </h6>
                            <div class="row">
                                <?php $__currentLoopData = $product->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file me-2 text-primary"></i>
                                        <div class="flex-grow-1">
                                            <strong><?php echo e($file->original_name); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo e($file->formatted_size); ?> • 
                                                <?php echo e($file->download_count); ?> <?php echo e(trans('app.downloads')); ?> • 
                                                <span class="badge badge-<?php echo e($file->is_active ? 'success' : 'danger'); ?> badge-sm">
                                                    <?php echo e($file->is_active ? trans('app.Active') : trans('app.Inactive')); ?>

                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="product_files" class="form-label">
                                    <i class="fas fa-file-upload me-1"></i>
                                    <?php echo e(trans('app.Add New Files')); ?>

                                </label>
                                <input type="file" class="form-control <?php $__errorArgs = ['product_files'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="product_files" name="product_files[]" accept=".zip,.rar,.pdf,.php,.js,.css,.html,.json,.xml,.sql,.jpg,.jpeg,.png,.gif,.svg" multiple>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Allowed file types: ZIP, RAR, PDF, PHP, JS, CSS, HTML, JSON, XML, SQL, Images')); ?>

                                    <br>
                                    <i class="fas fa-shield-alt me-1"></i>
                                    <?php echo e(trans('app.Maximum file size: 50MB per file')); ?>

                                </div>
                                <?php $__errorArgs = ['product_files'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        <strong><?php echo e(trans('app.Tip:')); ?></strong> <?php echo e(trans('app.You can manage all product files from the dedicated files management page.')); ?>

                                    </div>
                                    <a href="<?php echo e(route('admin.products.files.index', $product)); ?>" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-cog me-1"></i>
                                        <?php echo e(trans('app.Manage Files')); ?>

                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media and Assets -->
                <div class="card mb-4">
                    <div class="card-header bg-pink text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-images me-2"></i>
                            <?php echo e(trans('app.Media and Assets')); ?>

                            <span class="badge bg-light text-pink ms-2"><?php echo e(trans('app.Optional')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">
                                    <i class="fas fa-image text-primary me-1"></i>
                                    <?php echo e(trans('app.Main Image')); ?>

                                </label>
                                <input type="file" class="form-control <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="image" name="image" accept="image/*">
                                <div class="form-text"><?php echo e(trans('app.Recommended Size')); ?></div>
                                <?php if($product->image): ?>
                                <div class="mt-2">
                                    <img src="<?php echo e(Storage::url($product->image)); ?>" alt="<?php echo e(trans('app.Current Image')); ?>"
                                         class="img-thumbnail product-image">
                                    <p class="text-muted small mt-1"><?php echo e(trans('app.Current Image Will Be Replaced')); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="gallery_images" class="form-label">
                                    <i class="fas fa-images text-success me-1"></i>
                                    <?php echo e(trans('app.Gallery Images')); ?>

                                </label>
                                <input type="file" class="form-control <?php $__errorArgs = ['gallery_images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="gallery_images" name="gallery_images[]" accept="image/*" multiple>
                                <div class="form-text"><?php echo e(trans('app.Select Multiple Images')); ?></div>
                                <?php if($product->gallery_images && count($product->gallery_images) > 0): ?>
                                <div class="mt-2">
                                    <p class="text-muted small mb-2"><?php echo e(trans('app.Current Gallery Images')); ?>:</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php $__currentLoopData = $product->gallery_images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $galleryImage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <img src="<?php echo e(Storage::url($galleryImage)); ?>" alt="<?php echo e(trans('app.Gallery Image')); ?>"
                                             class="img-thumbnail product-gallery-image">
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <p class="text-muted small mt-1"><?php echo e(trans('app.New Images Will Be Added')); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php $__errorArgs = ['gallery_images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Optimization -->
                <div class="card mb-4">
                    <div class="card-header bg-indigo text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-search me-2"></i>
                            <?php echo e(trans('app.SEO')); ?>

                            <span class="badge bg-light text-indigo ms-2"><?php echo e(trans('app.Optional')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="meta_title" class="form-label">
                                    <i class="fas fa-heading text-primary me-1"></i>
                                    <?php echo e(trans('app.Meta Title')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['meta_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="meta_title" name="meta_title" 
                                       value="<?php echo e(old('meta_title', $product->meta_title)); ?>" 
                                       maxlength="255" placeholder="<?php echo e(trans('app.SEO Title Placeholder')); ?>">
                                <?php $__errorArgs = ['meta_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tags" class="form-label">
                                    <i class="fas fa-tags text-warning me-1"></i>
                                    <?php echo e(trans('app.Tags')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['tags'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="tags" name="tags" 
                                       value="<?php echo e(old('tags', is_array($product->tags) ? implode(', ', $product->tags) : $product->tags)); ?>"
                                       placeholder="<?php echo e(trans('app.Tags Comma Separated')); ?>">
                                <?php $__errorArgs = ['tags'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="meta_description" class="form-label">
                                <i class="fas fa-file-alt text-success me-1"></i>
                                <?php echo e(trans('app.Meta Description')); ?>

                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['meta_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="meta_description" name="meta_description" rows="3" maxlength="500"
                                      placeholder="<?php echo e(trans('app.SEO Description Placeholder')); ?>"><?php echo e(old('meta_description', $product->meta_description)); ?></textarea>
                            <?php $__errorArgs = ['meta_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Product Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            <?php echo e(trans('app.Product Settings')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   <?php echo e(old('is_active', $product->is_active) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active">
                                <i class="fas fa-toggle-on text-success me-1"></i>
                                <?php echo e(trans('app.Active')); ?>

                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"
                                   <?php echo e(old('is_featured', $product->is_featured) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_featured">
                                <i class="fas fa-star text-warning me-1"></i>
                                <?php echo e(trans('app.Featured')); ?>

                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_downloadable" name="is_downloadable" value="1"
                                   <?php echo e(old('is_downloadable', $product->is_downloadable) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_downloadable">
                                <i class="fas fa-download text-info me-1"></i>
                                <?php echo e(trans('app.Downloadable')); ?>

                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" value="1"
                                   <?php echo e(old('is_popular', $product->is_popular) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_popular">
                                <i class="fas fa-fire text-warning me-1"></i>
                                <?php echo e(trans('app.Popular')); ?>

                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="auto_renewal" name="auto_renewal" value="1"
                                   <?php echo e(old('auto_renewal', $product->auto_renewal) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="auto_renewal">
                                <i class="fas fa-sync text-success me-1"></i>
                                <?php echo e(trans('app.Auto Renewal')); ?>

                            </label>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            <?php echo e(trans('app.Quick Stats')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-primary"><?php echo e($product->licenses()->count()); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Licenses')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success"><?php echo e($product->invoices()->count()); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Invoices')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- License Integration -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-code me-2"></i>
                            <?php echo e(trans('app.License Integration')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if($product->programmingLanguage): ?>
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-check-circle me-1"></i>
                                <?php echo e(trans('app.Integration File Generated')); ?>

                            </h6>
                            <p class="mb-2">
                                <strong><?php echo e(trans('app.Language')); ?>:</strong> <?php echo e($product->programmingLanguage->name); ?><br>
                                <strong><?php echo e(trans('app.File')); ?>:</strong> <?php echo e(basename($product->integration_file_path ?? trans('app.Not generated'))); ?>

                            </p>
                            <div class="d-grid gap-2">
                                <?php if($product->integration_file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->integration_file_path)): ?>
                                <a href="<?php echo e(route('admin.products.download-integration', $product)); ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i><?php echo e(trans('app.Download')); ?>

                                </a>
                                <?php endif; ?>
                                <button type="submit" form="regenerate-form" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="fas fa-sync me-1"></i><?php echo e(trans('app.Regenerate')); ?>

                                </button>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <?php echo e(trans('app.Programming Language Required')); ?>

                            </h6>
                            <p class="mb-0"><?php echo e(trans('app.Set Programming Language Message')); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>


                <!-- Danger Zone -->
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo e(trans('app.Danger Zone')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3"><?php echo e(trans('app.Delete Product Warning')); ?></p>
                        <button type="submit" form="delete-form" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-1"></i><?php echo e(trans('app.Delete Product')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i><?php echo e(trans('app.Cancel')); ?>

                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?php echo e(trans('app.Update Product')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <!-- Hidden forms for separate actions -->
    <form id="regenerate-form" method="post" action="<?php echo e(route('admin.products.regenerate-integration', $product)); ?>" class="hidden">
        <?php echo csrf_field(); ?>
    </form>
    
    
    <form id="delete-form" method="post" action="<?php echo e(route('admin.products.destroy', $product)); ?>" data-confirm="delete-product" class="hidden">
        <?php echo csrf_field(); ?>
        <?php echo method_field('DELETE'); ?>
    </form>
</div>

<!-- Create Test License Section - Separate Form -->
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        <?php echo e(trans('app.Create Test License')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3"><?php echo e(trans('app.Create Test License Description')); ?></p>
                    <form method="post" action="<?php echo e(route('admin.products.generate-license', $product)); ?>" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="test-domain" class="form-label">
                                        <i class="fas fa-globe text-primary me-1"></i>
                                        <?php echo e(trans('app.Domain')); ?> <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="test-domain" name="domain" 
                                           placeholder="example.com" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="test-email" class="form-label">
                                        <i class="fas fa-envelope text-success me-1"></i>
                                        <?php echo e(trans('app.Customer Email')); ?> <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="test-email" name="email" 
                                           placeholder="customer@example.com" required>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-purple">
                                <i class="fas fa-plus me-1"></i><?php echo e(trans('app.Create Test License')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\products\edit.blade.php ENDPATH**/ ?>
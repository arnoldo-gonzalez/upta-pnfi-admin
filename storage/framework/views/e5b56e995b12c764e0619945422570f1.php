<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (isset($component)) { $__componentOriginal98df7be4d2d83ea3b787e656b1f8d7eb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal98df7be4d2d83ea3b787e656b1f8d7eb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.heading','data' => ['level' => 'h1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['level' => 'h1']); ?><?php echo e(__('My Assigned Evaluations')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal98df7be4d2d83ea3b787e656b1f8d7eb)): ?>
<?php $attributes = $__attributesOriginal98df7be4d2d83ea3b787e656b1f8d7eb; ?>
<?php unset($__attributesOriginal98df7be4d2d83ea3b787e656b1f8d7eb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal98df7be4d2d83ea3b787e656b1f8d7eb)): ?>
<?php $component = $__componentOriginal98df7be4d2d83ea3b787e656b1f8d7eb; ?>
<?php unset($__componentOriginal98df7be4d2d83ea3b787e656b1f8d7eb); ?>
<?php endif; ?>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mt-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase"><?php echo e(__('Project')); ?></th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase"><?php echo e(__('Stage')); ?></th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase"><?php echo e(__('Actions')); ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $evaluations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evaluation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($evaluation->project->title); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($evaluation->project->section->name); ?> - <?php echo e($evaluation->project->section->trayecto->name); ?></div>
                                <div class="text-xs text-gray-500">
                                    Envío #<?php echo e($evaluation->document->submission_number ?? 1); ?> - 
                                    <?php echo e($evaluation->project->section->name); ?>

                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo e($evaluation->stage->name); ?>

                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="<?php echo e(route('evaluations.teacher.review', $evaluation)); ?>" class="bg-blue-600 text-white px-3 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 transition">
                                    <?php echo e(__('Review')); ?>

                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="px-6 py-10 text-center text-gray-500"><?php echo e(__('No pending evaluations assigned to you.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200"><?php echo e($evaluations->links()); ?></div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\Sebas\Documents\University things\Proyecto 2do año\Proyecto_Código\resources\views/evaluations/teacher/index.blade.php ENDPATH**/ ?>
<?php use \App\Enums\UserStatus; ?>
<?php use \App\Enums\TutorRequestStatus; ?>
<?php use \App\Models\ProjectEvaluation; ?>
<?php use \App\Models\AcademicActivity; ?>

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
    <div class="max-w-5xl mx-auto py-6 sm:px-6 lg:px-8">

        <?php if(count($receivedInvitations) > 0): ?>
            <div class="mb-6 bg-yellow-50 border border-yellow-200 shadow-md rounded-lg p-6">
                <h3 class="text-lg font-bold text-yellow-800 mb-4"><?php echo e(__('Pending Invitations')); ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php $__currentLoopData = $receivedInvitations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invite): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white p-4 rounded border border-yellow-100 shadow-sm flex flex-col justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1"><?php echo e(__('Invitation to join:')); ?></p>
                                <p class="text-base font-bold text-gray-900"><?php echo e($invite->project->title); ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?php echo e(__('Sent by:')); ?> <?php echo e($invite->sender->student->name); ?></p>
                            </div>
                            <div class="flex space-x-2 mt-4">
                                <form action="<?php echo e(route('invitations.accept', $invite)); ?>" method="POST" class="flex-1">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="w-full bg-green-500 text-white text-sm px-3 py-2 rounded hover:bg-green-600 transition"><?php echo e(__('Accept')); ?></button>
                                </form>
                                <form action="<?php echo e(route('invitations.reject', $invite)); ?>" method="POST" class="flex-1">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="w-full bg-red-500 text-white text-sm px-3 py-2 rounded hover:bg-red-600 transition"><?php echo e(__('Reject')); ?></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if(!$project): ?>
            <div class="text-center bg-white p-12 rounded-lg shadow-sm border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>

                <h3 class="mt-2 text-sm font-medium text-gray-900"><?php echo e(__('No Project Found')); ?></h3>

                <?php if($enrollment->section->is_active): ?>
                
                    <p class="mt-1 text-sm text-gray-500"><?php echo e(__('You are not registered in any project yet. You can create one or wait for an invitation.')); ?></p>
                
                    <div class="mt-6">
                        <a href="<?php echo e(route('projects.create')); ?>" class="inline-flex items-center px-4 py-2 bg-upta-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-upta-700 focus:outline-none transition ease-in-out duration-150">
                            <?php echo e(__('Register My Project')); ?>

                        </a>
                    </div>

                <?php else: ?>
                    <p class="mt-2 text-sm text-red-500 font-medium"><?php echo e(__('The academic period is closed. You cannot create new projects.')); ?></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="md:col-span-2 space-y-6">

                    <!-- Info del proyecto -->
                    <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h2 class="text-2xl font-bold text-upta-900"><?php echo e($project->title); ?></h2>
                            <?php if($project->status_id === UserStatus::DRAFT->value): ?>
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold"><?php echo e(__('Draft')); ?></span>
                            <?php elseif($project->status_id === UserStatus::PENDING->value): ?>
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold"><?php echo e(__('In Review')); ?></span>
                            <?php elseif($project->status_id === UserStatus::NEEDS_CORRECTIONS->value): ?>
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold"><?php echo e(__('Needs Corrections')); ?></span>
                            <?php elseif($project->status_id === UserStatus::ACTIVE->value): ?>
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold"><?php echo e(__('In Tutoring Phase')); ?></span>
                            <?php elseif($project->status_id === UserStatus::READY_FOR_EXPERTS->value): ?>
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold"><?php echo e(__('Ready for Experts')); ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-600 mb-2"><strong><?php echo e(__('Line:')); ?></strong> <?php echo e($project->investigationLine->name); ?></p>
                        <p class="text-sm text-gray-600"><strong><?php echo e(__('Justification:')); ?></strong> <?php echo e($project->justification); ?></p>

                        <?php if($project->tutor_evaluation_status_id !== UserStatus::PENDING->value && $project->tutor_evaluation_status_id !== null): ?>
                            <div class="mt-6 border-t border-gray-100 pt-4">
                                <p class="text-sm font-bold text-gray-800 mb-3"><?php echo e(__('Tutor Evaluation Result')); ?></p>
                                <div class="flex space-x-4">
                                    <div class="bg-gray-50 p-3 rounded border border-gray-200 flex-1">
                                        <p class="text-xs text-gray-500"><?php echo e(__('Written Report')); ?></p>
                                        <p class="text-xl font-bold <?php echo e($project->written_score >= 80 ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e($project->written_score); ?>%</p>
                                    </div>
                                    <div class="bg-gray-50 p-3 rounded border border-gray-200 flex-1">
                                        <p class="text-xs text-gray-500"><?php echo e(__('Technical Dev')); ?></p>
                                        <p class="text-xl font-bold <?php echo e($project->technical_score >= ($project->section->trayecto->number == 3 ? 95 : 80) ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e($project->technical_score); ?>%</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Equipo -->
                    <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo e(__('Team Members')); ?> (<?php echo e(count($teamMembers)); ?>/<?php echo e($project->section->max_team_size); ?>)</h3>
                        <ul class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $teamMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?php echo e($member->enrollment->student->name); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo e($member->enrollment->student->email); ?></p>
                                    </div>
                                    <?php if($member->enrollment_id === $enrollment->id): ?>
                                        <span class="text-xs text-upta-600 font-bold"><?php echo e(__('You')); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>

                    <!-- Historial de Correcciones -->
                    <?php
                        $documents = $project->documents()->whereHas('type', function($q) {
                            $q->where('name', 'Protocolo');
                        })->orderBy('created_at', 'desc')->get();
                    ?>

                    <?php if($documents->count() > 0): ?>
                        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo e(__('Submission History')); ?></h3>
                            
                            <div class="space-y-6">
                                <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="relative pl-6 border-l-2 border-upta-200">
                                        <div class="absolute -left-[9px] top-1 h-4 w-4 rounded-full bg-upta-500 border-2 border-white"></div>
                                        
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-sm font-bold text-gray-900"><?php echo e(__('Submission')); ?> #<?php echo e($documents->count() - $index); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo e($doc->created_at->format('d/m/Y h:i A')); ?></p>
                                                <a href="<?php echo e(route('documents.download', $doc)); ?>" target="_blank" class="text-xs text-upta-600 hover:underline mt-1 inline-block">
                                                    <?php echo e(__('Download File')); ?>

                                                </a>
                                            </div>
                                        </div>

                                        <?php
                                            $evals = ProjectEvaluation::where('project_document_id', $doc->id)->with('teacher')->get();
                                        ?>
                                        
                                        <?php if($evals->count() > 0): ?>
                                            <div class="mt-3 space-y-2">
                                                <?php $__currentLoopData = $evals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($eval->status_id === UserStatus::ACTIVE->value): ?>
                                                        <?php $bgClass = 'bg-green-50 text-green-800'; $statusText = __('Approved'); ?>
                                                    <?php elseif($eval->status_id === UserStatus::PENDING->value): ?>
                                                        <?php $bgClass = 'bg-yellow-50 text-yellow-800'; $statusText = __('Pending'); ?>
                                                    <?php else: ?>
                                                        <?php $bgClass = 'bg-red-50 text-red-800'; $statusText = __('Rejected'); ?>
                                                    <?php endif; ?>

                                                    <div class="text-xs p-2 rounded <?php echo e($bgClass); ?>">
                                                        <strong><?php echo e($eval->teacher->name); ?>:</strong> 
                                                        <?php echo e($statusText); ?>

                                                        
                                                        <?php if($eval->observations && $eval->status_id !== UserStatus::PENDING->value): ?>
                                                            <br><em>"<?php echo e($eval->observations); ?>"</em>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Columna Lateral (Acciones e Invitaciones) -->
                <div class="space-y-6">

                    <?php
                        $pendingCount = $sentInvitations->where('status_id', UserStatus::PENDING->value)->count();
                        $totalOccupied = count($teamMembers) + $pendingCount;
                        $hasSpace = $totalOccupied < $project->section->max_team_size;

                        $isDeadlineOpen = AcademicActivity::isOpen('protocol_submission');
                        $lateRequest = $project->lateSubmissionRequests()->latest()->first();
                        $hasApprovedLateRequest = $lateRequest && $lateRequest->status_id === UserStatus::ACTIVE->value;
                        
                        $canSubmitProtocol = $isDeadlineOpen || $hasApprovedLateRequest;
                    ?>

                    <!-- 1. FORMULARIO DE SUBIDA Y ALERTAS -->
                    <?php if($project->status_id === UserStatus::DRAFT->value || $project->status_id === UserStatus::NEEDS_CORRECTIONS->value): ?>
                        
                        <?php if($canSubmitProtocol): ?>
                            <!-- ALERTA DE OBSERVACIONES -->
                            <?php if($project->status_id === UserStatus::NEEDS_CORRECTIONS->value): ?>
                                <?php
                                    $rejections = $project->evaluations()->where('status_id', UserStatus::REJECTED->value)->whereNotNull('observations')->with('teacher')->get();
                                    $protocolDocLatest = $project->documents()->whereHas('type', function($q) {
                                        $q->where('name', 'Protocolo');
                                    })->latest()->first();
                                    $fieldCorrectionsLatest = $protocolDocLatest ? $protocolDocLatest->fieldCorrections()->with('evaluator')->get() : collect();
                                ?>
                                <div class="bg-red-50 border border-red-200 shadow-md rounded-lg p-6 mb-6">
                                    <h3 class="text-sm font-bold text-red-800 mb-3 uppercase tracking-wider flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <?php echo e(__('Corrections Required')); ?>

                                    </h3>
                                    <ul class="space-y-3 mb-4">
                                        <?php $__currentLoopData = $rejections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rejection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="bg-white p-3 rounded border border-red-100 text-sm">
                                                <p class="text-gray-800 italic">"<?php echo e($rejection->observations); ?>"</p>
                                                <p class="text-xs text-gray-500 mt-2 font-bold">- <?php echo e($rejection->teacher->name); ?></p>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>

                                    <?php if($fieldCorrectionsLatest->count() > 0): ?>
                                        <h4 class="text-xs font-bold text-red-800 mb-2 uppercase tracking-wider"><?php echo e(__('Field Corrections')); ?></h4>
                                        <div class="space-y-2">
                                            <?php $__currentLoopData = $fieldCorrectionsLatest; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="bg-white p-2 rounded border border-red-100 text-sm">
                                                    <div class="flex justify-between items-start mb-1">
                                                        <span class="text-xs font-bold text-gray-600"><?php echo e($fc->placeholder_key); ?></span>
                                                        <span class="text-[10px] text-red-500 uppercase"><?php echo e($fc->evaluator->name ?? __('Teacher')); ?></span>
                                                    </div>
                                                    <p class="text-gray-800 italic ml-0">"<?php echo e($fc->correction); ?>"</p>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- AVISO SI TIENE PERMISO EXTEMPORÁNEO -->
                            <?php if(!$isDeadlineOpen && $hasApprovedLateRequest): ?>
                                <div class="bg-green-50 p-4 rounded text-sm text-green-800 border border-green-200 mb-4">
                                    <?php echo e(__('Your late submission request was approved. You can upload your protocol now.')); ?>

                                </div>
                            <?php endif; ?>

                            <!-- FORMULARIO DE SUBIDA (REEMPLAZA PDF POR FORMULARIO) -->
                            <div class="bg-upta-50 border border-upta-200 shadow-md rounded-lg p-6">
                                <h3 class="text-sm font-bold text-upta-900 mb-3 uppercase tracking-wider">
                                    <?php echo e($project->status_id === UserStatus::NEEDS_CORRECTIONS->value ? __('Resubmit Protocol') : __('Submit Protocol')); ?>

                                </h3>

                                <p class="text-sm text-gray-600 mb-4">
                                    <?php echo e(__('Completa el formulario del protocolo, previsualiza el documento y envíalo para su revisión.')); ?>

                                </p>

                            <div class="flex space-x-2">
                                    <a href="<?php echo e(route('projects.protocol.fill', $project)); ?>"
                                       class="flex-1 text-center bg-upta-600 text-white text-sm font-semibold px-4 py-2 rounded hover:bg-upta-700 transition shadow-sm">
                                        <?php echo e(__('Fill Protocol Form')); ?>

                                    </a>
                                    <form action="<?php echo e(route('projects.protocol.preview', $project)); ?>" method="POST" class="flex-1">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="w-full bg-white text-upta-700 text-sm font-semibold px-4 py-2 rounded hover:bg-upta-100 transition border border-upta-300">
                                            <?php echo e(__('Preview Protocol')); ?>

                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- BLOQUE DE SOLICITUD EXTEMPORÁNEA -->
                            <?php if($lateRequest && $lateRequest->status_id === UserStatus::PENDING->value): ?>
                                <div class="bg-yellow-50 p-4 rounded text-sm text-yellow-800 border border-yellow-200">
                                    <?php echo e(__('Your late submission request is under review by the coordination.')); ?>

                                </div>
                            <?php elseif($lateRequest && $lateRequest->status_id === UserStatus::REJECTED->value): ?>
                                <div class="bg-red-50 p-4 rounded text-sm text-red-800 border border-red-200">
                                    <p class="font-bold mb-1"><?php echo e(__('Your late submission request was rejected.')); ?></p>
                                    <p class="text-xs italic">"<?php echo e($lateRequest->reason); ?>"</p>
                                </div>
                            <?php else: ?>
                                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                                    <h4 class="font-bold text-gray-800 text-sm mb-2"><?php echo e(__('The deadline has passed')); ?></h4>
                                    <p class="text-xs text-gray-600 mb-3"><?php echo e(__('If you need to modify your protocol, you must provide a valid justification for the coordination to review.')); ?></p>
                                    <form action="<?php echo e(route('late-submission.store', $project)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <textarea name="reason" rows="3" class="w-full text-sm rounded border-gray-300 mb-2" placeholder="<?php echo e(__('Explain the reasons for the late submission...')); ?>" required></textarea>
                                        <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mb-2 font-bold"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <button type="submit" class="bg-upta-600 text-white px-4 py-2 rounded text-xs font-bold"><?php echo e(__('Request Authorization')); ?></button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- 2. VER DOCUMENTO (Si ya fue enviado) -->
                    <?php if($project->status_id === UserStatus::PENDING->value): ?>
                        <?php
                            $protocolDoc = $project->documents()->whereHas('type', function($q) {
                                $q->where('name', 'Protocolo');
                            })->first();
                        ?>
                        
                        <?php if($protocolDoc): ?>
                            <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                                <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wider"><?php echo e(__('Your Protocol')); ?></h3>
                                <div class="flex items-center p-3 bg-gray-50 rounded border border-gray-200">
                                    <svg class="h-8 w-8 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900"><?php echo e(__('Protocol Document')); ?></p>
                                            <a href="<?php echo e(route('projects.protocol.download', $project)); ?>" target="_blank" class="text-xs text-upta-600 hover:underline mt-1 inline-block">
                                                <?php echo e(__('Download / View Docx')); ?>

                                            </a>
                                        </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- 3. Invitar Estudiantes -->
                    <?php if($project->status_id === UserStatus::DRAFT->value && $hasSpace && $project->section->is_active): ?>
                        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                            <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wider"><?php echo e(__('Invite Classmates')); ?></h3>
                            
                            <form action="<?php echo e(route('projects.invite')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                
                                <div class="mb-3 relative" 
                                    x-data="{
                                        open: false,
                                        search: '',
                                        selectedId: '',
                                        students: <?php echo e(Js::from($studentsJson)); ?>, 
                                        get filteredStudents() {
                                            if (this.search === '') return this.students;
                                            return this.students.filter(s => 
                                                s.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                                s.credential.includes(this.search)
                                            );
                                        },
                                        selectStudent(id, name) {
                                            this.selectedId = id;
                                            this.search = name;
                                            this.open = false;
                                        }
                                    }" 
                                    @click.away="open = false">
                                    
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            x-model="search" 
                                            @focus="open = true"
                                            @keydown.escape="open = false"
                                            placeholder="<?php echo e(__('Search by name or ID...')); ?>" 
                                            class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500"
                                            autocomplete="off"
                                        >
                                        
                                        <input type="hidden" name="receiver_enrollment_id" x-model="selectedId" required>

                                        <ul x-show="open" 
                                            x-transition 
                                            class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" 
                                            style="display: none;">
                                            
                                            <template x-for="student in filteredStudents" :key="student.id">
                                                <li @click="selectStudent(student.id, student.name)" 
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-upta-50 text-gray-900">
                                                    <span x-text="student.name" class="font-medium"></span>
                                                    <span x-text="'(' + student.credential + ')'" class="text-gray-500 text-xs ml-1"></span>
                                                </li>
                                            </template>
                                            
                                            <li x-show="filteredStudents.length === 0" class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 text-sm">
                                                <?php echo e(__('No students found.')); ?>

                                            </li>
                                        </ul>
                                    </div>

                                    <button type="submit" 
                                            :disabled="!selectedId"
                                            class="mt-3 w-full bg-upta-600 text-white text-sm font-semibold px-4 py-2 rounded hover:bg-upta-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                        <?php echo e(__('Send Invitation')); ?>

                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- 4. Invitaciones Enviadas -->
                    <?php if(count($sentInvitations) > 0): ?>
                        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4"><?php echo e(__('Sent Invitations')); ?></h3>
                            <ul class="divide-y divide-gray-100">
                                <?php $__currentLoopData = $sentInvitations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invitation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="py-3 flex justify-between items-center">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900"><?php echo e($invitation->receiver->student->name); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo e($invitation->receiver->student->email); ?></p>
                                        </div>
                                        
                                        <!-- Estado de la invitación -->
                                        <?php if($invitation->status_id === UserStatus::PENDING->value): ?>
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                                <?php echo e(__('Pending')); ?>

                                            </span>
                                        <?php elseif($invitation->status_id === UserStatus::ACTIVE->value): ?>
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                                <?php echo e(__('Accepted')); ?>

                                            </span>
                                        <?php elseif($invitation->status_id === UserStatus::REJECTED->value): ?>
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                                <?php echo e(__('Rejected')); ?>

                                            </span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- GESTIÓN DE TUTOR -->
                    <?php if($project->section->trayecto->number === 1): ?>
                        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6 mt-6">
                            <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wider"><?php echo e(__('Academic Tutor')); ?></h3>
                            <p class="text-sm text-gray-500"><?php echo e(__('Trayecto 1 students do not require an academic tutor. Your subject teacher will guide you.')); ?></p>
                        </div>
                    <?php elseif($project->status_id === UserStatus::ACTIVE->value): ?>
                        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                            <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wider"><?php echo e(__('Academic Tutor')); ?></h3>
                            <p class="text-xs text-gray-600 mb-4"><?php echo e(__('Manage your tutor request and view your tutoring logbook.')); ?></p>
                            <a href="<?php echo e(route('tutors.student.index')); ?>" class="block text-center w-full bg-upta-50 text-upta-700 border border-upta-200 text-sm font-semibold px-4 py-2 rounded hover:bg-upta-100 transition">
                                <?php echo e(__('Go to My Tutor')); ?>

                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>
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
<?php endif; ?><?php /**PATH C:\Users\Sebas\Documents\University things\Proyecto 2do año\Proyecto_Código\resources\views/projects/index.blade.php ENDPATH**/ ?>
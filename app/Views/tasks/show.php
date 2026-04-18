<?php
declare(strict_types=1);

$status = (string)($task['status'] ?? '');
$estimated = (int)($task['estimated_minutes'] ?? 0);
$due = (string)($task['due_date'] ?? '');
$assigneeId = (string)($task['assignee_id'] ?? '');
?>

<div class="flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold"><?= e($task['title'] ?? '') ?></h1>
        <div class="text-sm text-slate-600 mt-1">
            Estado: <span class="font-medium"><?= e($status) ?></span>
            <?php if ($due !== ''): ?>
                · Vence: <?= e($due) ?>
            <?php endif; ?>
            · Asignado a: <?= e($assigneeId) ?>
        </div>
        <?php if (!empty($task['description'])): ?>
            <div class="text-slate-700 mt-3 whitespace-pre-line"><?= e($task['description']) ?></div>
        <?php endif; ?>
    </div>
    <a class="underline text-sm" href="/tasks">Volver</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
    <div class="bg-white border rounded p-4">
        <div class="text-sm text-slate-500">Tiempo real</div>
        <div class="text-2xl font-semibold"><?= e((int)$minutesReal) ?> min</div>
    </div>
    <div class="bg-white border rounded p-4">
        <div class="text-sm text-slate-500">Tiempo estimado</div>
        <div class="text-2xl font-semibold"><?= e($estimated) ?> min</div>
    </div>
    <div class="bg-white border rounded p-4">
        <div class="text-sm text-slate-500">Progreso</div>
        <div class="text-2xl font-semibold"><?= e($status) ?></div>
    </div>
</div>

<?php if ($isAssignee): ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="bg-white border rounded p-4">
            <h2 class="font-semibold mb-3">Actualizar estado</h2>
            <form method="post" action="/tasks/<?= e($task['id'] ?? '') ?>/status" class="space-y-3">
                <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>En progreso</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completada</option>
                </select>
                <button class="bg-slate-900 text-white rounded px-4 py-2">Guardar</button>
            </form>
        </div>

        <div class="bg-white border rounded p-4">
            <h2 class="font-semibold mb-3">Registrar tiempo</h2>
            <form method="post" action="/tasks/<?= e($task['id'] ?? '') ?>/time" class="space-y-3">
                <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
                <div>
                    <label class="block text-sm mb-1">Minutos</label>
                    <input name="minutes" type="number" min="1" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Notas (opcional)</label>
                    <input name="notes" class="w-full border rounded px-3 py-2">
                </div>
                <button class="bg-slate-900 text-white rounded px-4 py-2">Agregar</button>
            </form>
        </div>

        <div class="bg-white border rounded p-4">
            <h2 class="font-semibold mb-3">Subir evidencia</h2>
            <form method="post" action="/tasks/<?= e($task['id'] ?? '') ?>/evidence" enctype="multipart/form-data" class="space-y-3">
                <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
                <input type="file" name="file" class="w-full" required>
                <button class="bg-slate-900 text-white rounded px-4 py-2">Subir</button>
            </form>
            <p class="text-xs text-slate-500 mt-2">Las evidencias se guardan en Supabase Storage (bucket task-evidences).</p>
        </div>
    </div>
<?php else: ?>
    <div class="mt-6 p-3 bg-slate-100 border rounded text-sm text-slate-700">
        Solo el usuario asignado puede actualizar estado, registrar tiempo y subir evidencias.
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white border rounded">
        <div class="p-4 border-b">
            <h2 class="font-semibold">Historial de tiempo</h2>
        </div>
        <?php if (empty($timeEntries)): ?>
            <div class="p-4 text-sm text-slate-600">Aún no hay registros.</div>
        <?php else: ?>
            <div class="divide-y">
                <?php foreach ($timeEntries as $e): ?>
                    <div class="p-4 text-sm">
                        <div class="flex items-center justify-between">
                            <div class="font-medium"><?= e((int)($e['minutes'] ?? 0)) ?> min</div>
                            <div class="text-slate-500"><?= e($e['created_at'] ?? '') ?></div>
                        </div>
                        <?php if (!empty($e['notes'])): ?>
                            <div class="text-slate-700 mt-1"><?= e($e['notes']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white border rounded">
        <div class="p-4 border-b">
            <h2 class="font-semibold">Evidencias</h2>
        </div>
        <?php if (empty($evidences)): ?>
            <div class="p-4 text-sm text-slate-600">Aún no hay evidencias.</div>
        <?php else: ?>
            <div class="divide-y">
                <?php foreach ($evidences as $ev): ?>
                    <?php $path = (string)($ev['storage_path'] ?? ''); ?>
                    <div class="p-4 text-sm flex items-center justify-between gap-3">
                        <div>
                            <div class="font-medium"><?= e($ev['filename'] ?? '') ?></div>
                            <div class="text-slate-500"><?= e($ev['created_at'] ?? '') ?></div>
                        </div>
                        <?php if ($isAssignee && isset($signedUrls[$path])): ?>
                            <a class="underline" target="_blank" rel="noopener" href="<?= e($signedUrls[$path]) ?>">Descargar</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>


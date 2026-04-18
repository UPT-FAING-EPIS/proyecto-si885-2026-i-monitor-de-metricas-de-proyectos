<?php declare(strict_types=1); ?>

<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Mis tareas</h1>
</div>

<?php if (is_string($loadError) && $loadError !== ''): ?>
    <div class="mb-4 p-3 rounded bg-amber-50 border border-amber-200 text-amber-800"><?= e($loadError) ?></div>
<?php endif; ?>

<div class="bg-white border rounded p-4 mb-4">
    <form method="get" action="/tasks" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-sm mb-1">Estado</label>
            <select name="status" class="w-full border rounded px-3 py-2">
                <option value="">Todos</option>
                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>En progreso</option>
                <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completada</option>
            </select>
        </div>
        <div>
            <label class="block text-sm mb-1">Desde</label>
            <input type="date" name="from" value="<?= e($filters['from'] ?? '') ?>" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm mb-1">Hasta</label>
            <input type="date" name="to" value="<?= e($filters['to'] ?? '') ?>" class="w-full border rounded px-3 py-2">
        </div>
        <button class="bg-slate-900 text-white rounded px-4 py-2">Filtrar</button>
    </form>
</div>

<?php if (empty($tasks)): ?>
    <div class="bg-white border rounded p-4 text-sm text-slate-600">Sin tareas con esos filtros.</div>
<?php else: ?>
    <div class="bg-white border rounded divide-y">
        <?php foreach ($tasks as $t): ?>
            <div class="p-4 flex items-start justify-between gap-4">
                <div>
                    <a class="font-medium underline" href="/tasks/<?= e($t['id'] ?? '') ?>"><?= e($t['title'] ?? '') ?></a>
                    <div class="text-sm text-slate-600 mt-1">
                        Estado: <?= e($t['status'] ?? '') ?>
                        <?php if (!empty($t['due_date'])): ?>
                            · Vence: <?= e($t['due_date']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-sm text-slate-500">
                    Est.: <?= e((int)($t['estimated_minutes'] ?? 0)) ?> min
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


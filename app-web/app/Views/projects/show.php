<?php
declare(strict_types=1);

$m = $metrics ?? [];
$progress = (int)($m['progress'] ?? 0);
$total = (int)($m['total'] ?? 0);
$completed = (int)($m['completed'] ?? 0);
$pending = (int)($m['pending'] ?? 0);
$estimated = (int)($m['estimated'] ?? 0);
$real = (int)($m['real'] ?? 0);
?>

<div class="flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold"><?= e($project['name'] ?? '') ?></h1>
        <?php if (!empty($project['description'])): ?>
            <div class="text-slate-600 mt-1"><?= e($project['description']) ?></div>
        <?php endif; ?>
    </div>
    <a class="underline text-sm" href="/projects">Volver</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
    <div class="bg-white border rounded p-4">
        <div class="text-sm text-slate-500">Avance</div>
        <div class="text-2xl font-semibold"><?= e($progress) ?>%</div>
        <div class="mt-2 h-2 bg-slate-100 rounded">
            <div class="h-2 bg-emerald-500 rounded" style="width: <?= e($progress) ?>%"></div>
        </div>
    </div>
    <div class="bg-white border rounded p-4">
        <div class="text-sm text-slate-500">Tareas</div>
        <div class="text-2xl font-semibold"><?= e($completed) ?>/<?= e($total) ?></div>
        <div class="text-sm text-slate-600">Completadas vs total</div>
    </div>
    <div class="bg-white border rounded p-4">
        <div class="text-sm text-slate-500">Tiempo</div>
        <div class="text-2xl font-semibold"><?= e($real) ?> / <?= e($estimated) ?> min</div>
        <div class="text-sm text-slate-600">Real vs estimado</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white border rounded p-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-semibold">Métricas</h2>
        </div>
        <canvas id="projectChart" height="140"></canvas>
    </div>
    <div class="bg-white border rounded p-4">
        <h2 class="font-semibold mb-3">Crear tarea</h2>
        <form method="post" action="/projects/<?= e($project['id'] ?? '') ?>/tasks" class="space-y-3">
            <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
            <div>
                <label class="block text-sm mb-1">Título</label>
                <input name="title" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Descripción</label>
                <textarea name="description" class="w-full border rounded px-3 py-2" rows="3"></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm mb-1">Tiempo estimado (min)</label>
                    <input name="estimated_minutes" type="number" min="0" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Fecha límite</label>
                    <input name="due_date" type="date" class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div>
                <label class="block text-sm mb-1">Asignar a (email)</label>
                <input name="assignee_email" type="email" class="w-full border rounded px-3 py-2" required>
            </div>
            <button class="bg-slate-900 text-white rounded px-4 py-2">Crear y asignar</button>
        </form>
        <p class="text-xs text-slate-500 mt-3">
            La asignación busca el usuario por email (función get_user_id_by_email en Supabase).
        </p>
    </div>
</div>

<div class="bg-white border rounded mt-6">
    <div class="p-4 border-b flex items-center justify-between">
        <h2 class="font-semibold">Tareas del proyecto</h2>
        <div class="text-sm text-slate-600"><?= e($pending) ?> pendientes</div>
    </div>
    <?php if (empty($tasks)): ?>
        <div class="p-4 text-sm text-slate-600">Aún no hay tareas.</div>
    <?php else: ?>
        <div class="divide-y">
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
</div>

<script>
const projectCtx = document.getElementById('projectChart');
new Chart(projectCtx, {
  type: 'bar',
  data: {
    labels: ['Completadas', 'Pendientes'],
    datasets: [{
      label: 'Tareas',
      data: [<?= (int)$completed ?>, <?= (int)$pending ?>],
      backgroundColor: ['#10b981', '#94a3b8']
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, precision: 0 } }
  }
});
</script>


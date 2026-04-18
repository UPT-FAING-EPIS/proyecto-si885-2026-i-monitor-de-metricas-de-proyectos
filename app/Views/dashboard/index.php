<?php
declare(strict_types=1);

$assigned = (int)($stats['assigned'] ?? 0);
$completed = (int)($stats['completed'] ?? 0);
$pending = max(0, $assigned - $completed);
$minutes = (int)($stats['minutes'] ?? 0);
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white border rounded p-4">
        <div class="text-sm text-slate-500">Tareas asignadas</div>
        <div class="text-2xl font-semibold"><?= e($assigned) ?></div>
    </div>
    <div class="bg-white border rounded p-4">
        <div class="text-sm text-slate-500">Tareas completadas</div>
        <div class="text-2xl font-semibold"><?= e($completed) ?></div>
    </div>
    <div class="bg-white border rounded p-4">
        <div class="text-sm text-slate-500">Tiempo invertido</div>
        <div class="text-2xl font-semibold"><?= e($minutes) ?> min</div>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="mt-4 bg-amber-50 border border-amber-200 rounded p-3 text-amber-800">
        <?php foreach ($errors as $err): ?>
            <div><?= e($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white border rounded p-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-semibold">Mi progreso</h2>
        </div>
        <canvas id="tasksChart" height="140"></canvas>
    </div>
    <div class="bg-white border rounded p-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-semibold">Notificaciones</h2>
        </div>
        <div class="space-y-3 text-sm">
            <div>
                <div class="font-medium">Vencidas</div>
                <?php if (empty($notifications['overdue'])): ?>
                    <div class="text-slate-600">Sin tareas vencidas.</div>
                <?php else: ?>
                    <ul class="list-disc ml-4">
                        <?php foreach ($notifications['overdue'] as $t): ?>
                            <li><a class="underline" href="/tasks/<?= e($t['id'] ?? '') ?>"><?= e($t['title'] ?? '') ?></a> (<?= e($t['due_date'] ?? '') ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div>
                <div class="font-medium">Próximas a vencer (48h)</div>
                <?php if (empty($notifications['dueSoon'])): ?>
                    <div class="text-slate-600">Sin tareas próximas a vencer.</div>
                <?php else: ?>
                    <ul class="list-disc ml-4">
                        <?php foreach ($notifications['dueSoon'] as $t): ?>
                            <li><a class="underline" href="/tasks/<?= e($t['id'] ?? '') ?>"><?= e($t['title'] ?? '') ?></a> (<?= e($t['due_date'] ?? '') ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white border rounded p-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-semibold">Mis proyectos</h2>
            <a class="px-3 py-1 rounded bg-slate-900 text-white text-sm" href="/projects/new">Nuevo</a>
        </div>
        <?php if (empty($projects)): ?>
            <div class="text-sm text-slate-600">Aún no tienes proyectos.</div>
        <?php else: ?>
            <ul class="divide-y">
                <?php foreach ($projects as $p): ?>
                    <li class="py-2">
                        <a class="font-medium underline" href="/projects/<?= e($p['id'] ?? '') ?>"><?= e($p['name'] ?? '') ?></a>
                        <?php if (!empty($p['description'])): ?>
                            <div class="text-sm text-slate-600"><?= e($p['description']) ?></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="bg-white border rounded p-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-semibold">Tareas asignadas</h2>
            <a class="underline text-sm" href="/tasks">Ver todas</a>
        </div>
        <?php if (empty($assignedTasks)): ?>
            <div class="text-sm text-slate-600">Sin tareas asignadas.</div>
        <?php else: ?>
            <ul class="divide-y text-sm">
                <?php foreach (array_slice($assignedTasks, 0, 8) as $t): ?>
                    <li class="py-2 flex items-center justify-between gap-3">
                        <a class="underline" href="/tasks/<?= e($t['id'] ?? '') ?>"><?= e($t['title'] ?? '') ?></a>
                        <span class="text-slate-600"><?= e($t['status'] ?? '') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<script>
const ctx = document.getElementById('tasksChart');
new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ['Completadas', 'Pendientes'],
    datasets: [{
      data: [<?= (int)$completed ?>, <?= (int)$pending ?>],
      backgroundColor: ['#10b981', '#94a3b8']
    }]
  },
  options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>


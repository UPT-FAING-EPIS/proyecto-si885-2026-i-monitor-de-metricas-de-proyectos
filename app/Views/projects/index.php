<?php declare(strict_types=1); ?>

<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Proyectos</h1>
    <a class="px-3 py-2 rounded bg-slate-900 text-white text-sm" href="/projects/new">Nuevo proyecto</a>
</div>

<?php if (is_string($loadError) && $loadError !== ''): ?>
    <div class="mb-4 p-3 rounded bg-amber-50 border border-amber-200 text-amber-800"><?= e($loadError) ?></div>
<?php endif; ?>

<?php if (empty($projects)): ?>
    <div class="bg-white border rounded p-4 text-sm text-slate-600">Aún no tienes proyectos.</div>
<?php else: ?>
    <div class="bg-white border rounded divide-y">
        <?php foreach ($projects as $p): ?>
            <div class="p-4">
                <a class="font-medium underline" href="/projects/<?= e($p['id'] ?? '') ?>"><?= e($p['name'] ?? '') ?></a>
                <?php if (!empty($p['description'])): ?>
                    <div class="text-sm text-slate-600 mt-1"><?= e($p['description']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


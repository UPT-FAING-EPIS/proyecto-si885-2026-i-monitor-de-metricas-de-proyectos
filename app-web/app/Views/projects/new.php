<?php declare(strict_types=1); ?>

<div class="max-w-xl bg-white border rounded p-6">
    <h1 class="text-xl font-semibold mb-4">Nuevo proyecto</h1>
    <form method="post" action="/projects/new" class="space-y-3">
        <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
        <div>
            <label class="block text-sm mb-1">Nombre</label>
            <input name="name" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block text-sm mb-1">Descripción</label>
            <textarea name="description" class="w-full border rounded px-3 py-2" rows="3"></textarea>
        </div>
        <button class="bg-slate-900 text-white rounded px-4 py-2">Crear</button>
    </form>
</div>


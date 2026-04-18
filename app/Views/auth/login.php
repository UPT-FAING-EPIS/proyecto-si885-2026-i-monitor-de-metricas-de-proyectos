<?php declare(strict_types=1); ?>

<div class="max-w-md mx-auto bg-white border rounded p-6">
    <h1 class="text-xl font-semibold mb-4">Iniciar sesión</h1>
    <form method="post" action="/login" class="space-y-3">
        <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
        <div>
            <label class="block text-sm mb-1">Email</label>
            <input name="email" type="email" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block text-sm mb-1">Contraseña</label>
            <input name="password" type="password" class="w-full border rounded px-3 py-2" required>
        </div>
        <button class="w-full bg-slate-900 text-white rounded px-3 py-2">Entrar</button>
    </form>
    <p class="text-sm text-slate-600 mt-3">
        ¿No tienes cuenta? <a class="underline" href="/register">Regístrate</a>
    </p>
</div>


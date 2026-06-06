type Theme = 'light' | 'dark';

const qs = <T extends Element>(selector: string, root: ParentNode = document) => root.querySelector(selector) as T | null;

const setTheme = (theme: Theme) => {
  document.documentElement.classList.toggle('dark', theme === 'dark');
  localStorage.setItem('pm:theme', theme);
};

const getTheme = (): Theme => {
  const stored = localStorage.getItem('pm:theme');
  if (stored === 'light' || stored === 'dark') return stored;
  const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches ?? false;
  return prefersDark ? 'dark' : 'light';
};

const updateThemeUI = () => {
  const label = qs<HTMLSpanElement>('#themeLabel');
  if (!label) return;
  label.textContent = document.documentElement.classList.contains('dark') ? 'Light mode' : 'Dark mode';
};

const validateEmail = (value: string): string | null => {
  const v = value.trim();
  if (!v) return 'Ingresa tu correo electrónico.';
  const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  return ok ? null : 'Ingresa un correo válido.';
};

const validatePassword = (value: string): string | null => {
  if (!value) return 'Ingresa tu contraseña.';
  return null;
};

const setFieldState = (input: HTMLInputElement, errorEl: HTMLElement, error: string | null) => {
  input.classList.remove('is-valid', 'is-invalid');
  if (error) {
    input.classList.add('is-invalid');
    input.setAttribute('aria-invalid', 'true');
    errorEl.textContent = error;
    errorEl.classList.remove('hidden');
  } else {
    input.classList.add('is-valid');
    input.removeAttribute('aria-invalid');
    errorEl.textContent = '';
    errorEl.classList.add('hidden');
  }
};

const setLoading = (loading: boolean) => {
  const btn = qs<HTMLButtonElement>('#submitBtn');
  const label = qs<HTMLSpanElement>('#submitLabel');
  const spinner = qs<HTMLSpanElement>('#submitSpinner');
  if (!btn || !label || !spinner) return;
  btn.disabled = loading;
  spinner.classList.toggle('hidden', !loading);
  label.textContent = loading ? 'Validando…' : 'Iniciar sesión';
};

const init = () => {
  const themeToggle = qs<HTMLButtonElement>('#themeToggle');
  if (themeToggle) {
    updateThemeUI();
    themeToggle.addEventListener('click', () => {
      const next: Theme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
      setTheme(next);
      updateThemeUI();
    });
  }

  const togglePassword = qs<HTMLButtonElement>('#togglePassword');
  const passwordInput = qs<HTMLInputElement>('#password');
  if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', () => {
      const visible = passwordInput.type === 'text';
      passwordInput.type = visible ? 'password' : 'text';
      togglePassword.textContent = visible ? 'Mostrar' : 'Ocultar';
      togglePassword.setAttribute('aria-pressed', (!visible).toString());
      togglePassword.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });
  }

  const form = qs<HTMLFormElement>('#loginForm');
  const emailInput = qs<HTMLInputElement>('#email');
  const emailError = qs<HTMLElement>('#emailError');
  const passwordError = qs<HTMLElement>('#passwordError');
  if (!form || !emailInput || !passwordInput || !emailError || !passwordError) return;

  const runEmailValidation = () => {
    const error = validateEmail(emailInput.value);
    setFieldState(emailInput, emailError, error);
    return !error;
  };

  const runPasswordValidation = () => {
    const error = validatePassword(passwordInput.value);
    setFieldState(passwordInput, passwordError, error);
    return !error;
  };

  emailInput.addEventListener('blur', runEmailValidation);
  emailInput.addEventListener('input', () => {
    if (!emailError.classList.contains('hidden')) runEmailValidation();
  });

  passwordInput.addEventListener('blur', runPasswordValidation);
  passwordInput.addEventListener('input', () => {
    if (!passwordError.classList.contains('hidden')) runPasswordValidation();
  });

  form.addEventListener('submit', (e) => {
    const ok = runEmailValidation() && runPasswordValidation();
    if (!ok) {
      e.preventDefault();
      const firstInvalid = qs<HTMLInputElement>('.is-invalid', form);
      firstInvalid?.focus();
      return;
    }
    setLoading(true);
    window.setTimeout(() => setLoading(false), 1200);
  });
};

try {
  setTheme(getTheme());
  updateThemeUI();
  window.addEventListener('DOMContentLoaded', init);
} catch {}

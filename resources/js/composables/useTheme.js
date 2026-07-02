import { computed, onMounted, ref, watch } from 'vue';

const STORAGE_KEY = 'faithassistqr.theme';

function getSystemTheme() {
  if (typeof window === 'undefined' || typeof window.matchMedia !== 'function') {
    return 'light';
  }

  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function getInitialTheme() {
  if (typeof window === 'undefined') {
    return 'light';
  }

  let savedTheme = null;

  try {
    savedTheme = window.localStorage.getItem(STORAGE_KEY);
  } catch {
    return getSystemTheme();
  }

  return savedTheme === 'dark' || savedTheme === 'light' ? savedTheme : getSystemTheme();
}

export function applyTheme(theme) {
  if (typeof document === 'undefined') {
    return;
  }

  document.documentElement.dataset.theme = theme;
  document.documentElement.classList.toggle('dark', theme === 'dark');
  document.documentElement.style.colorScheme = theme;
}

export function useTheme() {
  const theme = ref(getInitialTheme());

  const isDark = computed(() => theme.value === 'dark');

  const toggleTheme = () => {
    theme.value = theme.value === 'dark' ? 'light' : 'dark';
  };

  const syncTheme = (value) => {
    applyTheme(value);

    if (typeof window !== 'undefined') {
      try {
        window.localStorage.setItem(STORAGE_KEY, value);
      } catch {}
    }
  };

  onMounted(() => {
    syncTheme(theme.value);
  });

  watch(theme, (value) => {
    syncTheme(value);
  });

  return {
    theme,
    isDark,
    toggleTheme,
  };
}

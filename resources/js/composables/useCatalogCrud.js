import { ref } from 'vue';
import { useAlerts } from './useAlerts';

export function useCatalogCrud({ baseUrl, storeUrl }) {
  const loading = ref(false);
  const alerts = useAlerts();

  const addErrors = ref({});
  const editErrors = ref({});

  const addGeneralError = ref('');
  const editGeneralError = ref('');

  const getCsrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  const apiFetch = async (url, method, data = null) => {
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrf(),
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: data ? JSON.stringify(data) : undefined,
    });

    const json = await response.json();

    if (!response.ok) {
      throw {
        status: response.status,
        errors: json.errors,
        message: json.message,
      };
    }

    return json;
  };

  const parseErrors = (err) => {
    const fieldErrors = err?.errors ?? {};
    const hasFieldErrors = Object.keys(fieldErrors).length > 0;

    return {
      fieldErrors,
      generalError: hasFieldErrors ? '' : (err?.message ?? 'Ocurrió un error inesperado.'),
    };
  };

  const clearAddErrors = () => {
    addErrors.value = {};
    addGeneralError.value = '';
  };

  const clearEditErrors = () => {
    editErrors.value = {};
    editGeneralError.value = '';
  };

  const createRow = async (payload) => {
    clearAddErrors();
    loading.value = true;

    try {
      const json = await apiFetch(storeUrl, 'POST', payload);

      void alerts.success(json.message ?? 'Registro creado correctamente.');

      return json.data;
    } catch (err) {
      const { fieldErrors, generalError } = parseErrors(err);

      addErrors.value = fieldErrors;
      addGeneralError.value = generalError;

      return null;
    } finally {
      loading.value = false;
    }
  };

  const updateRow = async (id, payload) => {
    clearEditErrors();
    loading.value = true;

    try {
      const json = await apiFetch(`${baseUrl}/${id}`, 'PUT', payload);

      alerts.success(json.message ?? 'Registro actualizado correctamente.');

      return json.data;
    } catch (err) {
      const { fieldErrors, generalError } = parseErrors(err);

      editErrors.value = fieldErrors;
      editGeneralError.value = generalError;

      return null;
    } finally {
      loading.value = false;
    }
  };

  const deleteRow = async (row) => {
    const result = await alerts.confirmDelete();

    if (!result.isConfirmed) return false;

    loading.value = true;

    try {
      const json = await apiFetch(`${baseUrl}/${row.id}`, 'DELETE');

      void alerts.success(json.message ?? 'Registro eliminado correctamente.');

      return true;
    } catch (err) {
      void alerts.error(err?.message ?? 'Ocurrió un error inesperado.');
      return false;
    } finally {
      loading.value = false;
    }
  };

  return {
    loading,

    addErrors,
    editErrors,

    addGeneralError,
    editGeneralError,

    clearAddErrors,
    clearEditErrors,

    createRow,
    updateRow,
    deleteRow,
  };
}

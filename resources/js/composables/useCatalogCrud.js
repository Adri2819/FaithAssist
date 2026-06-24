import { ref } from 'vue';
import Swal from 'sweetalert2';

export function useCatalogCrud({ baseUrl, storeUrl }) {
  const loading = ref(false);

  const addErrors = ref({});
  const editErrors = ref({});

  const addGeneralError = ref('');
  const editGeneralError = ref('');

  const getCsrf = () =>
    document.querySelector('meta[name="csrf-token"]')?.content ?? '';

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

  const toast = (icon, title) => {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon,
      title,
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true,
    });
  };

  const parseErrors = (err) => {
    const fieldErrors = err?.errors ?? {};
    const hasFieldErrors = Object.keys(fieldErrors).length > 0;

    return {
      fieldErrors,
      generalError: hasFieldErrors
        ? ''
        : (err?.message ?? 'Ocurrió un error inesperado.'),
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

      toast('success', json.message ?? 'Registro creado correctamente.');

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

      toast('success', json.message ?? 'Registro actualizado correctamente.');

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
    const result = await Swal.fire({
      title: 'Eliminar registro',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
    });

    if (!result.isConfirmed) return false;

    loading.value = true;

    try {
      const json = await apiFetch(`${baseUrl}/${row.id}`, 'DELETE');

      toast('success', json.message ?? 'Registro eliminado correctamente.');

      return true;
    } catch (err) {
      toast('error', err?.message ?? 'Ocurrió un error inesperado.');
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
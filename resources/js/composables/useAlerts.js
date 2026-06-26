const toastDefaults = {
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2500,
  timerProgressBar: true,
};

const modalDefaults = {
  confirmButtonColor: '#0f172a',
};

const confirmDefaults = {
  ...modalDefaults,
  icon: 'warning',
  showCancelButton: true,
  confirmButtonText: 'Confirmar',
  cancelButtonText: 'Cancelar',
  cancelButtonColor: '#64748b',
};

const getSwal = async () => {
  const module = await import('sweetalert2');

  return module.default;
};

export function useAlerts() {
  const toast = async ({ icon = 'info', title, text, ...options }) => {
    const Swal = await getSwal();

    return Swal.fire({
      ...toastDefaults,
      icon,
      title,
      text,
      ...options,
    });
  };

  const modal = async ({ icon = 'info', title, text, ...options }) => {
    const Swal = await getSwal();

    return Swal.fire({
      ...modalDefaults,
      icon,
      title,
      text,
      ...options,
    });
  };

  const success = (title, options = {}) =>
    toast({
      icon: 'success',
      title,
      ...options,
    });

  const error = (title = 'Ocurrió un error inesperado.', options = {}) =>
    toast({
      icon: 'error',
      title,
      ...options,
    });

  const warning = (title, options = {}) =>
    modal({
      icon: 'warning',
      title,
      ...options,
    });

  const info = (title, options = {}) =>
    modal({
      icon: 'info',
      title,
      ...options,
    });

  const confirm = async (options = {}) => {
    const Swal = await getSwal();

    return Swal.fire({
      ...confirmDefaults,
      ...options,
    });
  };

  const confirmDelete = (options = {}) =>
    confirm({
      title: 'Eliminar registro',
      text: 'Esta acción no se puede deshacer.',
      confirmButtonColor: '#ef4444',
      confirmButtonText: 'Sí, eliminar',
      ...options,
    });

  return {
    toast,
    modal,
    success,
    error,
    warning,
    info,
    confirm,
    confirmDelete,
  };
}

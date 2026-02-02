import { writable } from 'svelte/store';

function createToasts() {
  const { subscribe, update } = writable([]);

  function remove(id) {
    update((items) => items.filter((t) => t.id !== id));
  }

  function push(message, options = {}) {
    const {
      type = 'success', // success | error | info | warning
      timeout = 3500,
    } = options;

    const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
    update((items) => [...items, { id, message, type }]);

    if (timeout && timeout > 0) {
      setTimeout(() => remove(id), timeout);
    }

    return id;
  }

  return { subscribe, push, remove };
}

export const toasts = createToasts();


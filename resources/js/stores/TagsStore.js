// TagsStore.js
import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';

export const useTagsStore = defineStore('tags', () => {
  // state
  const tags = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    total: 0,
    prev_page_url: null,
    next_page_url: null
  });

  const search = ref('');
  const perPage = ref(10);

  const loading = ref(false);
  const forms = ref(false);
  const formData = reactive({ name: '', slug: '' });
  const errors = reactive({});
  const buttonSaveUpdate = ref(false);
  const sort = reactive({
    column: 'created_at',
    direction: 'desc'
  });

  // CORE FUNCTION
  function goToPage(p = 1) {
    const payload = {
      page: p,
      per_page: perPage.value,
      sort_by: sort.column,
      sort_dir: sort.direction,
    };

    if (search.value.trim() !== '') {
      payload.search = search.value;
    }

    router.post('/tags', payload, {
      preserveState: false,
      preserveScroll: true,
      replace: true,
      only: ['tags'],
      onStart: () => (loading.value = true),
      onFinish: () => (loading.value = false),
      onSuccess: (page) => {
        tags.value = page.props.tags;
      },
    });
  }

  // WRAPPER FUNCTIONS
  function searchTags() {
    goToPage(1); // selalu balik ke page 1 saat search
  }

  function changePageSize() {
    goToPage(1); // reset ke page 1
  }

  function resetFilters() {
    search.value = '';
    perPage.value = 10;
    sort.column = 'created_at';
    sort.direction = 'desc';
    goToPage(1);
  }

  function toggleSort(column) {
    if (sort.column === column) {
      sort.direction = sort.direction === 'asc' ? 'desc' : 'asc';
    } else {
      sort.column = column;
      sort.direction = 'asc';
    }
    goToPage(1);
  }

  function changeSort() {
    goToPage(1); // kalau ganti sort di select
  }

  const sortDirectionIcon = (col) => {
    if (sort.column !== col) return "fas fa-sort";
    return sort.direction === "asc"
      ? "fas fa-arrow-up-wide-short"
      : "fas fa-arrow-down-wide-short";
  };

  function rowNumber(index) {
  return (tags.value.current_page - 1) * perPage.value + (index + 1);
}


  function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return 'Belum Pernah update';
    return date.toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'long',
      day: '2-digit',
    });
  }




const saveTag = async (formData, callbacks = {}) => {
  try {
    const res = await axios.post("/tags-store", formData);

    // update state langsung
    if (tags.value && tags.value.data) {
      tags.value.data.unshift(res.data.data);
    }

    callbacks.onSuccess && callbacks.onSuccess(res.data);
  } catch (error) {
    if (error.response?.status === 422) {
      callbacks.onError && callbacks.onError(error.response.data.errors);
    } else {
      callbacks.onError && callbacks.onError({ general: [error.response?.data?.message || "Something went wrong"] });
    }
  } finally {
    callbacks.onFinish && callbacks.onFinish();
  }
};



const updateTag = async (formData, callbacks = {}) => {
  try {
    const res = await axios.put(`/update-tag/${formData.id}`, formData, {
      params: { preserveScroll: true, preserveState: true }
    });

    // update state langsung
    if (tags.value && tags.value.data) {
      const idx = tags.value.data.findIndex(tag => tag.id === formData.id);
      if (idx !== -1) {
        tags.value.data[idx] = res.data.data;
      }
    }

    callbacks.onSuccess && callbacks.onSuccess(res.data);
  } catch (error) {
    if (error.response?.status === 422) {
      callbacks.onError && callbacks.onError(error.response.data.errors);
    } else {
      callbacks.onError && callbacks.onError({ general: [error.response?.data?.message || "Something went wrong"] });
    }
  } finally {
    callbacks.onFinish && callbacks.onFinish();
  }
};



const deleteTag = async (id, callbacks = {}) => {
  try {
    await axios.delete(`/delete-tag/${id}`);
    // update state lokal (biar tabel gak reload)
    tags.value.data = tags.value.data.filter(item => item.id !== id);

    callbacks.onSuccess && callbacks.onSuccess();
  } catch (error) {
    if (error.response?.data?.errors) {
      callbacks.onError && callbacks.onError(error.response.data.errors);
    }
  } finally {
    callbacks.onFinish && callbacks.onFinish();
  }
};



  return {
    // state
    tags,
    search,
    perPage,
    sort,
    loading,
    forms,
    formData,
    errors,
    buttonSaveUpdate,

    // actions
    goToPage,
    searchTags,
    changePageSize,
    resetFilters,
    toggleSort,
    changeSort,
    sortDirectionIcon,
    rowNumber,
    formatDate,
    saveTag,
    updateTag,
    deleteTag
  };
});

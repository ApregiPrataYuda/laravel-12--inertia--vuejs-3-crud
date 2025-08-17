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
  })


  // actions
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

  function changePageSize() {
    goToPage(1);
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
  goToPage(1) // reset ke page 1 supaya konsisten
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

  return {
    tags,
    search,
    perPage,
    sort,
    loading,
    forms,
    formData,
    errors,
    buttonSaveUpdate,
    goToPage,
    changePageSize,
    resetFilters,
    toggleSort,
    changeSort,
    formatDate,
  };
});

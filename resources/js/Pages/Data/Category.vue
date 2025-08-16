<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  category: Object,
  filters: Object,
});

// reactive
const search  = ref(props.filters?.search || "");
const perPage = ref(props.filters?.per_page || 10);
const sort    = ref({
  column: props.filters?.sort_by || "created_at",
  direction: props.filters?.sort_dir || "desc",
});
const loading = ref(false);

// fetch data
function goToPage(p = 1) {
  router.get(
    "/category",
    {
      page: p,
      search: search.value,
      per_page: perPage.value,
      sort_by: sort.value.column,
      sort_dir: sort.value.direction,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ["category"],
      onStart: () => (loading.value = true),
      onFinish: () => (loading.value = false),
      onSuccess: () => {
        window.history.replaceState({}, "", "/category");
      },
    }
  );
}

// realtime search
watch(search, () => goToPage(1));

// ubah jumlah data per halaman
function changePageSize() {
  goToPage(1);
}

// watch sort untuk realtime fetch
watch(sort, () => goToPage(1), { deep: true });


function resetFilters() {
  search.value  = '';
  perPage.value = 10;
  sort.value = {
    column: 'created_at',
    direction: 'desc'
  };

  goToPage(1); // refresh data ke page 1
}


// Toggle sorting saat header diklik
function toggleSort(column) {
  if (sort.value.column === column) {
    // ubah arah jika kolom sama
    sort.value.direction = sort.value.direction === "asc" ? "desc" : "asc";
  } else {
    sort.value.column = column;
    sort.value.direction = "asc"; // default arah baru
  }
  goToPage(1); // fetch ulang page 1
}

// icon untuk sorting
const sortDirectionIcon = (col) => {
  if (sort.value.column !== col) return "fas fa-sort";
  return sort.value.direction === "asc"
    ? "fas fa-arrow-up-wide-short"
    : "fas fa-arrow-down-wide-short";
};



</script>

<template>
  <div class="container mt-4">
    <h2>Category</h2>


        <div class="d-flex flex-wrap gap-2 mt-1 mb-2">
        <button class="btn btn-sm btn-outline-secondary" @click="resetFilters">
            <i class="fas fa-undo me-1"></i> Reset
        </button>
        </div>


    <!-- Search box -->
    <input
      type="text"
      class="form-control mb-3"
      v-model="search"
      placeholder="Search category..."
    />

    <!-- Per Page + Sorting -->
    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
      <!-- Per Page -->
      <div class="d-flex align-items-center gap-2">
        <label class="mb-0">Tampilkan:</label>
        <select class="form-select form-select-sm" v-model="perPage" @change="changePageSize">
          <option value="10">10 data</option>
          <option value="25">25 data</option>
          <option value="50">50 data</option>
          <option value="100">100 data</option>
        </select>
      </div>

      <!-- Sorting -->
      <div class="d-flex align-items-center gap-2">
        <label class="mb-0">Urutkan:</label>
        <select class="form-select form-select-sm w-auto" v-model="sort.column">
          <option value="name">Nama Category</option>
          <option value="created_at">Tanggal Dibuat</option>
        </select>

        <select class="form-select form-select-sm w-auto" v-model="sort.direction">
          <option value="asc">Naik</option>
          <option value="desc">Turun</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <table class="table table-bordered">
      <thead>
         <tr>
      <th>No</th>
      <th @click="toggleSort('name')" style="cursor:pointer">
        <i :class="sortDirectionIcon('name')" class="ml-0" title="Klik untuk urutkan"></i>
        Nama Category
      </th>
      <th scope="col">Deskripsi Category</th>
      <th @click="toggleSort('created_at')" style="cursor:pointer">
        <i :class="sortDirectionIcon('created_at')" class="ml-0" title="Klik untuk urutkan"></i>
        Created At
      </th>
      <th scope="col">Updated At</th>
    </tr>
      </thead>

      <!-- Loader -->
      <tbody v-if="loading">
        <tr>
          <td colspan="5" class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </td>
        </tr>
      </tbody>

      <!-- Data -->
      <tbody v-else>
        <tr v-for="(cat, index) in category?.data ?? []" :key="cat.id">
          <td>
            {{
              ((category?.current_page ?? 1) - 1) *
                (category?.per_page ?? 10) +
              index +
              1
            }}
          </td>
          <td>{{ cat.name }}</td>
          <td>{{ cat.slug }}</td>
          <td>{{ cat.created_at }}</td>
          <td>{{ cat.updated_at }}</td>
        </tr>
      </tbody>

      <!-- No Data -->
      <tbody v-if="!loading && (category?.data ?? []).length === 0">
        <tr>
          <td colspan="5" class="text-center">
            <div class="d-flex flex-column align-items-center justify-content-center">
              <img
                src="https://cdn.dribbble.com/users/285475/screenshots/2083086/dribbble_1.gif"
                alt="No data found"
                style="max-width: 300px; height: auto"
                class="mb-3"
              />
              <p class="text-danger text-capitalize font-weight-bold h6 fst-italic">
                <i class="fa fa-exclamation-circle mr-1"></i>
                Data tidak ditemukan.
              </p>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center" v-if="category">
      <button
        class="btn btn-outline-primary"
        :disabled="loading || category.current_page === 1"
        @click="goToPage(category.current_page - 1)"
      >
        Prev
      </button>

      <!-- <span>
        Page {{ category.current_page }} of {{ category.last_page }}
        (Total: {{ category.total }} data)
      </span> -->

      <span class="btn btn-outline-secondary position-relative">
        Pages {{ category.current_page }} |  of | {{ category.last_page }} | Pages
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
            Total: | {{ category.total }} | data
            <!-- <span class="visually-hidden">unread messages</span> -->
        </span>
        </span>

      <button
        class="btn btn-outline-primary"
        :disabled="loading || category.current_page === category.last_page"
        @click="goToPage(category.current_page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>

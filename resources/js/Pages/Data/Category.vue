<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  category: Object,
  filters: Object,
});

// reactive
const pageTitle = ref('Category')
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



  const formatDate = (dateStr) => {
     if (!dateStr) return '-'
     const date = new Date(dateStr)
     if (isNaN(date.getTime())) {
     return 'Belum Di Pernah update'
     }
     const options = {
     year: 'numeric',
     month: 'long',
     day: '2-digit'
    }
    return date.toLocaleDateString('id-ID', options)
  }


  // code add
   const forms = ref(false)
  const formData  = ref({})
  const errors = ref({})
  const buttonSaveUpdate = ref(false)

  function AddActionCategory() {
      if(forms.value) {
    // tutup form
    forms.value = false
  } else {
    // buka form
    forms.value = true
    formData.value = {
      name: '',
      description: ''
    }
  }

}

</script>

<template>
  <AppLayout>
  <div class="container mt-4">
    <h2>Category</h2>




    




     <!-- card 1 -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body d-flex flex-wrap gap-2">

          <!-- Import Excel -->
          <label class="btn btn-sm btn-outline-success mb-0">
            <i class="fas fa-file-import me-1"></i> Import Excel
            <input type="file" accept=".xlsx, .xls" hidden>
          </label>

          <!-- Export Excel -->
          <button class="btn btn-sm btn-outline-success">
            <i class="fa fa-file-excel me-1"></i> Export Excel
          </button>

          <!-- Export PDF -->
          <button class="btn btn-sm btn-outline-danger">
            <i class="fa fa-file-pdf me-1"></i> Export PDF
          </button>

          <!-- Import CSV -->
          <label class="btn btn-sm btn-outline-secondary mb-0">
            <i class="fa fa-file-import me-1"></i> Import CSV
            <input type="file" accept=".csv" hidden >
          </label>

          <!-- Export CSV -->
          <button class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-file-export me-1"></i> Export CSV
          </button>

        </div>
      </div>


      <!-- card 2 -->
       <div class="card mb-3 shadow-sm">
          <div class="card-body">

            <!-- Baris Atas: Tampilkan dan Pencarian -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">

              <!-- Kiri: Tampilkan + Tombol Excel -->
              <div class="d-flex flex-column gap-2">

                <!-- Tampilkan per halaman -->
                <div class="d-flex align-items-center flex-wrap gap-2">
                  <label class="mb-0">
                    <i class="fas fa-list-ul me-1"></i> Tampilkan:
                  </label>
                  <select class="form-select form-select-sm w-auto" v-model="perPage" @change="changePageSize">
                    <option value="10">10 data per halaman</option>
                    <option value="25">25 data per halaman</option>
                    <option value="50">50 data per halaman</option>
                    <option value="100">100 data per halaman</option>
                  </select>
                </div>

                <!-- Tombol Reset -->
                <div class="d-flex flex-wrap gap-2 mt-1">
                  <button class="btn btn-sm btn-outline-white" @click="resetFilters">
                    <i class="fas fa-undo me-1"></i> Reset
                  </button>
                  
                </div>

              </div>

              <!-- Kanan: Search + Sort -->
              <div class="d-flex flex-column gap-2 align-items-end" style="min-width: 250px;">
                <!-- Pencarian -->
                <div class="input-group input-group-sm">
                  <input type="text"
                        class="form-control"
                        placeholder="Cari Category"
                         v-model="search">
                  <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                </div>

                <!-- Urutkan -->
                <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                  <label class="mb-0">Urutkan:</label>
                  <select class="form-select form-select-sm w-auto"
                          v-model="sort.column">
                    <option value="name">Nama Category</option>
                    <option value="created_at">Tanggal Dibuat</option>
                  </select>

                  <select class="form-select form-select-sm w-auto"
                       v-model="sort.direction">
                    <option value="asc">Naik</option>
                    <option value="desc">Turun</option>
                  </select>
                </div>
              </div>

            </div>

          </div>
        </div>



        <transition name="fade-slide">
        <div v-if="forms" class="card shadow-sm mb-3 border rounded">
      <div class="card-body">
         <h5 class="mb-4">Tambah {{ pageTitle }}</h5>   
              <div class="p-3 border rounded">
                <div class="row justify-content-center">
                  <div class="col-md-6">

                    
                    <div class="mb-2 text-start">
                      <label class="form-label">Name Category</label>
                      <input type="text" class="form-control" name="name">
                      <div class="invalid-feedback">{{ errors.name }}</div>
                    </div>

                    <div class="mb-2 text-start">
                      <label class="form-label">Name Category</label>
                     <textarea name="description" id="description" class="form-control"></textarea>
                      <div class="invalid-feedback">{{ errors.name }}</div>
                    </div>

            
                  
                    <!-- Tombol Simpan -->
                    <div class="text-end">
                      <button 
                        type="submit" 
                        class="btn btn-sm btn-outline-secondary"
                        @click="saveUpdateVariant"
                      >
                        Simpan
                      </button>
                    </div>
             </div>
          </div>
        </div>
    </div>
  </div>
</transition>

        
    

    

    <!-- Table -->
  <div class="card mb-3 shadow-sm">
   <div class="card-body">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                  <h5 class="mb-0 fw-semibold">List {{ pageTitle }}</h5>

                  <!-- <button class="btn btn-sm btn-outline-secondary" @click="AddActionCategory">
                    <i class="fas fa-plus me-1"></i> Add  {{ pageTitle }}
                  </button> -->

                  <button 
                    class="btn btn-sm"
                    :class="forms ? 'btn-outline-danger' : 'btn-outline-secondary'" 
                    @click="AddActionCategory"
                   >
                    <i :class="forms ? 'fas fa-times me-1' : 'fas fa-plus me-1'"></i>
                    {{ forms ? 'Tutup ' + pageTitle : 'Add ' + pageTitle }}
                  </button>

    </div>

    <div class="table-responsive">
    <table class="table table-bordered">
      <thead>
         <tr>
      <th style="width: 3%;">No</th>
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
      <th style="width: 8%;">
          Action
      </th>
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
          <td>{{ formatDate(cat.created_at) }}</td>
          <td>{{ formatDate(cat.updated_at) }}</td>
          <td>
             <button class="btn btn-outline-warning btn-sm me-2"><i class="fa fa-edit"></i></button>
             <button class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button>
          </td>
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
   </div>
  </div>
 </div>

  

    <!-- Pagination -->
     <div class="card mb-3 shadow-sm">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center" v-if="category">
      
      <!-- Prev button (kiri) -->
      <button
        class="btn btn-outline-secondary"
        :disabled="loading || category.current_page === 1"
        @click="goToPage(category.current_page - 1)"
      >
        <i class="fa-solid fa-circle-chevron-left"></i> Prev
      </button>

      

      <!-- Info halaman (tengah) -->
       <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
        <!-- Halaman saat ini -->
        <span class="badge border border-secondary text-secondary px-3 py-2 rounded-pill">
          Page <strong>{{ category.current_page }}</strong> of <strong>{{ category.last_page }}</strong>
        </span>

        <!-- Total data -->
        <span class="badge border border-secondary text-secondary px-3 py-2 rounded-pill">
          Total: <strong>{{ category.total }}</strong> data
        </span>
      </div>



      <!-- Next button (kanan) -->
      <button
        class="btn btn-outline-secondary"
        :disabled="loading || category.current_page === category.last_page"
        @click="goToPage(category.current_page + 1)"
      >
        Next  <i class="fa-solid fa-circle-chevron-right"></i>
      </button>

    </div>
  </div>
</div>


    
  </div>
  </AppLayout>
</template>


<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.5s ease;
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}
.fade-slide-enter-to {
  opacity: 1;
  transform: translateY(0);
}
.fade-slide-leave-from {
  opacity: 1;
  transform: translateY(0);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>

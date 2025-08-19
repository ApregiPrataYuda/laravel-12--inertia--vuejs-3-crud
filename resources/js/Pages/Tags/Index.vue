

<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTagsStore } from '@/stores/TagsStore';
import { notify } from "@kyvg/vue3-notification"
import Swal from "sweetalert2";

const pageTitle = ref('Tags');
const store = useTagsStore();
const { props } = usePage();
const page = usePage()
let timer = null;

// Inisialisasi store dengan data dari server
// Cukup panggil goToPage(1) jika tidak ada props awal
onMounted(() => {
    if (props.tags) {
        store.tags = props.tags; 
    } else {
        store.goToPage(1);
    }
});



watch(() => store.search, () => {
  clearTimeout(timer);
  timer = setTimeout(() => {
    store.searchTags(); // panggil wrapper, bukan langsung goToPage
  }, 500);
});



// CODE variable untuk FORM ADD dan edit
  const formControl = ref(false);
  const formData  = ref({})
  const saving = ref(false)
  const errors = ref({})



  // 
  function AddActionTags() {
      if(formControl.value) {
    // tutup form
    formControl.value = false
  } else {
    // buka form
    formControl.value = true
    formData.value = {
      name: '',
    }
  }
}

// code untuk validtion dari frontend
function isFormValid() {
  errors.value = {} // reset error

  // Wajib diisi
  if (!formData.value.name) {
    errors.value.name = 'Nama tags wajib diisi.'
  } else if (!/^[A-Za-z\s]+$/.test(formData.value.name)) {
    errors.value.name = 'Nama tags hanya boleh huruf dan spasi.'
  }

  return Object.keys(errors.value).length === 0
}


const editTag = (tag) => {
  // isi form dengan data yang dipilih
  formData.value = {
    id: tag.id,
    name: tag.name
  }
  formControl.value = true // buka modal form
}



// code save/update
const saveUpdate = async () => {
  if (!isFormValid()) return;

  saving.value = true; // ✅ aktifkan loading sebelum request

  // cek apakah edit atau tambah
  const isEdit = !!formData.value.id;

  await store[isEdit ? 'updateTag' : 'saveTag'](formData.value, {
    onSuccess: () => {
  formControl.value = false
  formData.value = {}



  notify({
    type: "success",
    title: "Berhasil",
    text: isEdit ? "Data berhasil diupdate!" : "Data berhasil disimpan!",
    duration: 5000
  })
},
    onError: (err) => {

      errors.value = err
      saving.value = false;

      if (err && err.errors) {
        Object.keys(err.errors).forEach(field => {
          notify({
            type: "warn",
            title: "Peringatan",
            text: err.errors[field], // ✅ langsung string, gak perlu loop array
            duration: 6000
          })
        })
      } else if (err.error) {
        notify({
          type: "error",
          title: "Error",
          text: err.error, // error umum dari server
          duration: 6000
        })
      }
    },
    onFinish: () => {
      saving.value = false;
    }
  })
}




const handleDelete = async (id) => {
  Swal.fire({
    title: "Yakin mau hapus?",
    text: "Data yang sudah dihapus tidak bisa dikembalikan!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal"
  }).then(async (result) => {
    if (result.isConfirmed) {
      await store.deleteTag(id, {
        onSuccess: () => {
          notify({
            type: "success",
            title: "Berhasil",
            text: "Data berhasil dihapus!",
            duration: 5000,
          });
        },
        onError: (err) => {
          for (const field in err) {
            err[field].forEach((msg) => {
              notify({
                type: "error",
                title: "Gagal",
                text: msg,
                duration: 6000,
              });
            });
          }
        },
      });
    }
  });
};


</script>

<template>
     <AppLayout>
     
  <div class="container mt-4">
    <!-- Header -->
    <div class="mb-4">
      <h6 class="text-muted mb-1">Page</h6>
      <h2 class="fw-bold">{{ pageTitle }}</h2>
    </div>

  

    <!-- code card 1 -->
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

          <!-- code card 2 -->
        <div class="card mb-3 shadow-sm">
          <div class="card-body">

            <!-- Baris Atas: Tampilkan dan Pencarian -->
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
              <div class="d-flex flex-column gap-2">
                <!-- Tampilkan per halaman -->
                <div class="d-flex align-items-center flex-wrap gap-2">
                <label class="mb-0">
                    <i class="fas fa-list-ul me-1"></i> Tampilkan:
                </label>
                <select
                    class="form-select form-select-sm w-auto"
                    v-model="store.perPage"
                    @change="store.changePageSize"
                >
                    <option value="10">10 data per halaman</option>
                    <option value="25">25 data per halaman</option>
                    <option value="50">50 data per halaman</option>
                    <option value="100">100 data per halaman</option>
                </select>
                </div>


                <!-- Tombol Reset -->
                <div class="d-flex flex-wrap gap-2 mt-1">
                  <button class="btn btn-sm btn-outline-white"  @click="store.resetFilters()">
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
                        placeholder="Cari Tags"
                        v-model="store.search">
                  <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                </div>

                <!-- Urutkan -->
               <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                    <label class="mb-0">Urutkan:</label>
                    <!-- sort by -->
                    <select
                        class="form-select form-select-sm w-auto"
                        v-model="store.sort.column"
                        @change="store.changeSort"
                    >
                        <option value="name">Nama {{ pageTitle }}</option>
                        <option value="created_at">Tanggal Dibuat</option>
                    </select>

                    <!-- sort direction -->
                    <select
                        class="form-select form-select-sm w-auto"
                        v-model="store.sort.direction"
                        @change="store.changeSort"
                    >
                        <option value="asc">Naik</option>
                        <option value="desc">Turun</option>
                    </select>
                    </div>
              </div>
            </div>
          </div>
        </div>


        <!-- start code form -->
          <transition name="fade-slide">
        <div v-if="formControl" class="card shadow-sm mb-3 border rounded">
      <div class="card-body">
         <h5 class="mb-4">{{ formData.id ? 'Edit Tag' : 'Tambah Tag' }} </h5>  
          
              <div class="p-3 border rounded">
                <div class="row justify-content-center">
                  <div class="col-md-6">

                    
                    <div class="mb-2 text-start">
                      <label class="form-label">Name Tags</label>
                      <input type="text" class="form-control"  :class="{ 'is-invalid': errors.name }" v-model="formData.name"  name="name">
                      <div class="invalid-feedback">{{ errors.name }}</div>
                    </div>

            
                  
                    <!-- Tombol Simpan -->
                    <div class="text-end">
                       <button type="button"  class="btn btn-sm btn-outline-secondary" @click="saveUpdate" :disabled="saving">
                        {{ saving ? 'Menyimpan...' : 'Simpan' }}
                      </button>
                    </div>
             </div>
          </div>
        </div>
    </div>
  </div>
</transition>
        <!-- end code form -->



     
     <!-- code card 3 -->
              <div class="card shadow-sm">
            <div class="card-body">
              <!-- Card wrapper -->
              <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                  <h5 class="mb-0 fw-semibold">List {{ pageTitle }}</h5>

                  <!-- <button class="btn btn-sm btn-outline-secondary" >
                    <i class="fas fa-plus me-1"></i> Add {{ pageTitle }}
                  </button> -->

                   <button 
                    class="btn btn-sm"
                    :class="formControl ? 'btn-outline-danger' : 'btn-outline-secondary'" 
                    @click="AddActionTags"
                   >
                    <i :class="formControl ? 'fas fa-times me-1' : 'fas fa-plus me-1'"></i>
                    {{ formControl ? 'Tutup Form' + pageTitle : 'Add ' + pageTitle }}
                  </button>

                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                      <thead class="table-light">
                        <tr class="table-secondary">
                          <th scope="col" class="tw1">No</th>
                          <!-- <th ></th> -->
                          <th @click="store.toggleSort('name')" style="cursor:pointer">
                            <i :class="store.sortDirectionIcon('name')" class="ml-0" title="Klik untuk urutkan"></i>
                            Name tags
                          </th>
                          <th >Slug tags</th>
                          <th @click="store.toggleSort('created_at')" style="cursor:pointer">
                            <i :class="store.sortDirectionIcon('created_at')" class="ml-0" title="Klik untuk urutkan"></i>
                            Created AT
                          </th>
                          <th>Last Updated</th>
                          <th scope="col" class="tw2">Handle</th>
                        </tr>
                      </thead>
                    
                          <tbody v-if="store.loading">
                            <tr>
                            <td colspan="6" class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                            </tr>
                        </tbody>

                            <tbody v-else-if="store.tags?.data?.length > 0">
                                <tr v-for="(tag, index) in store.tags.data" :key="tag.id">
                                <td class="th1">{{ store.rowNumber(index) }}.</td>
                                <td>{{ tag.name }}</td>
                                <td>{{ tag.slug }}</td>
                                <td>{{ store.formatDate(tag.created_at) }}</td>
                                <td>{{ store.formatDate(tag.updated_at) }}</td>
                                <td class="th2">
                                    <button class="btn btn-outline-warning btn-sm me-2" @click="editTag(tag)">
                                    <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" @click="handleDelete(tag.id)">
                                    <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                                </tr>
                            </tbody>

                            <tbody v-else>
                                <tr>
                                <td colspan="6" class="text-center">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                    <img
                                        src="https://cdn.dribbble.com/users/285475/screenshots/2083086/dribbble_1.gif"
                                        alt="No data found"
                                        style="max-width: 300px; height: auto"
                                        class="mb-3"
                                    />
                                    <p class="text-danger text-capitalize fw-bold h6 fst-italic">
                                        <i class="fa fa-exclamation-circle me-1"></i>
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
            </div>
            </div>

           <div class="card shadow-sm mt-2 mb-4">
                    <div class="card-body">
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                        
                        <!-- Tombol Prev -->
                        <button
                            class="btn btn-outline-secondary"
                            :disabled="!store.tags?.prev_page_url || store.loading"
                            @click="store.tags?.prev_page_url && store.goToPage(store.tags.current_page - 1)"
                           >
                            <i class="fa-solid fa-chevron-left"></i> Prev
                        </button>

                        <!-- Info halaman (tengah) -->
                        <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                            <!-- Halaman saat ini -->
                            <span class="badge border border-secondary text-secondary px-3 py-2 rounded-pill">
                            Page <strong>{{ store.tags?.current_page }}</strong> of <strong>{{ store.tags?.last_page }}</strong>
                            </span>

                            <!-- Total data -->
                            <span class="badge border border-secondary text-secondary px-3 py-2 rounded-pill">
                            Total: <strong>{{ store.tags?.total }}</strong> data
                            </span>
                        </div>

                        <!-- Tombol Next -->
                        <button
                            class="btn btn-outline-secondary"
                            :disabled="!store.tags?.next_page_url || store.loading"
                            @click="store.tags?.next_page_url && store.goToPage(store.tags.current_page + 1)"
                        >
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </button>

                        </div>
                    </div>
                    </div>
          </div>
          </AppLayout>
</template>


<style scoped>
.th1 {
    width: 3%;
}

.th2 {
    width: 8%;
}
</style>
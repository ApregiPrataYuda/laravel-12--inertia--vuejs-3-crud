<script setup>
import { ref, onMounted, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useTagsStore } from '@/stores/StoreTag.js'
import { notify } from "@kyvg/vue3-notification"
import Swal from 'sweetalert2'

const store = useTagsStore()
const pageTitle = ref('Tags')
const modalOpen = ref(false)
const saving = ref(false)
const errorsFront = ref({}) 


// code export
const exportModalOpen = ref(false)
const exportType = ref('month') // 'month', 'date', 'year'
const selectedMonth = ref(new Date().getMonth() + 1)
const selectedYear = ref(new Date().getFullYear())
const startDate = ref('')
const endDate = ref('')

const years = ref([])
const generateYears = () => {
  const currentYear = new Date().getFullYear();
  for (let i = currentYear; i >= 2000; i--) {
    years.value.push(i);
  }
}

// load data awal
onMounted(() => {
  store.fetchTags()
   generateYears();
})

const openExportModal = () => {
    exportModalOpen.value = true
}

// debounce search
let timer = null
watch(() => store.search, () => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    store.fetchTags(1)
  }, 500)
})

// icon sort
const sortDirectionIcon = (col) => {
  if (store.sort.column !== col) return 'fas fa-sort'
  return store.sort.direction === 'asc'
    ? 'fas fa-arrow-up-wide-short'
    : 'fas fa-arrow-down-wide-short'
}

// buka modal tambah
const addTag = () => {
  store.formData = { id: null, name: '' }
  store.errors = {}
  modalOpen.value = true
}

// buka modal edit
const editTag = (tag) => {
  store.formData = { ...tag }
  store.errors = {}
  modalOpen.value = true
}

// code untuk validtion dari frontend
function isFormValid() {
  errorsFront.value = {} // reset error

  if (!store.formData.name) {
    errorsFront.value.name = 'Nama tags wajib diisi.'
  } else if (!/^[A-Za-z\s]+$/.test(store.formData.name)) {
    errorsFront.value.name = 'Nama tags hanya boleh huruf dan spasi.'
  }

  return Object.keys(errorsFront.value).length === 0
}



const saveUpdate = async () => {
      if (!isFormValid()) return;
  saving.value = true
  try {
    if (store.formData.id) {
      await store.updateTag()
      notify({
        type: "success",
        title: "Berhasil",
        text: "Data berhasil diupdate!",
        duration: 5000
      })
    } else {
      await store.saveTag()
      notify({
        type: "success",
        title: "Berhasil",
        text: "Data berhasil disimpan!",
        duration: 5000
      })
    }
    modalOpen.value = false
  } catch (err) {
    console.error(err)
    notify({
      type: "error",
      title: "Gagal",
      text: err.response?.data?.message || "Terjadi kesalahan!",
      duration: 6000
    })
  } finally {
    saving.value = false
  }
}

// delete
const handleDelete = (id) => {
  Swal.fire({
    title: 'Yakin hapus?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        await store.deleteTag(id)
        notify({
          type: "success",
          title: "Berhasil",
          text: "Data berhasil dihapus!",
          duration: 5000
        })
      } catch (err) {
        console.error(err)
        notify({
          type: "error",
          title: "Gagal",
          text: err.response?.data?.message || "Terjadi kesalahan saat menghapus!",
          duration: 6000
        })
      }
    }
  })
}

const selectedTags = ref([]) // array untuk menampung id tag yang dicentang
const handleDeleteMany = async () => {
  if (!selectedTags.value.length) return

  Swal.fire({
    title: `Yakin mau hapus ${selectedTags.value.length} tag?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal",
  }).then(async (res) => {
    if (res.isConfirmed) {
      try {
        await store.deleteMany(selectedTags.value)
        notify({
          type: "success",
          title: "Berhasil",
          text: `${selectedTags.value.length} tag berhasil dihapus!`,
          duration: 5000,
        })
        selectedTags.value = [] // reset selection
      } catch (err) {
        console.error(err)
        notify({
          type: "error",
          title: "Gagal",
          text: err.response?.data?.message || "Terjadi kesalahan saat menghapus!",
          duration: 6000,
        })
      }
    }
  })
}


const viewDetail = async (id) => {
  modalOpen.value = true;
  await store.fetchTagDetail(id);
}




const handleExport = async () => {
    let params = {};
    if (exportType.value === 'month') {
        params = {
            type: 'month',
            month: selectedMonth.value,
            year: selectedYear.value
        };
    } else if (exportType.value === 'date') {
        params = {
            type: 'date',
            start_date: startDate.value,
            end_date: endDate.value
        };
    } else if (exportType.value === 'year') {
        params = {
            type: 'year',
            year: selectedYear.value
        };
    }

    try {
        const response = await store.exportTags(params);

        // Jika berhasil, unduh file dan tampilkan notifikasi sukses
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'tags-report.xlsx');
        document.body.appendChild(link);
        link.click();
        
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        notify({
            type: "success",
            title: "Berhasil",
            text: "Laporan berhasil diunduh!",
            duration: 5000
        });

        exportModalOpen.value = false;
        
    } catch (err) {
        // Tangani error yang dilempar dari store
        if (err.response && err.response.status === 404) {
            // Jika respons 404, baca blob sebagai teks untuk mendapatkan pesan error
            const reader = new FileReader();
            reader.onload = function() {
                try {
                    const message = JSON.parse(reader.result).message;
                    notify({
                        type: "warning",
                        title: "Perhatian",
                        text: message,
                        duration: 6000
                    });
                } catch (jsonErr) {
                    notify({
                        type: "warning",
                        title: "Perhatian",
                        text: "Tidak ada data laporan.",
                        duration: 6000
                    });
                }
            };
            reader.readAsText(err.response.data);
            
        } else {
            notify({
                type: "error",
                title: "Gagal",
                text: err.response?.data?.message || "Terjadi kesalahan saat ekspor laporan!",
                duration: 6000
            });
        }
    }
};

</script>

<template>
<AppLayout>
  <div class="container py-3">
    <h2>{{ pageTitle }}</h2>

   <div class="card shadow-sm mt-2 mb-4">
  <div class="card-body">

    <!-- Bar 1: Import / Export -->
   <div class="card mb-3 shadow-sm">
        <div class="card-body d-flex flex-wrap gap-2">

          <!-- Import Excel -->
          

          <!-- Export Excel -->
          <button class="btn btn-sm btn-outline-success" @click="openExportModal">
                <i class="fa fa-file-excel me-1"></i> Export Laporan
            </button>

          <!-- Export PDF -->
          <button class="btn btn-sm btn-outline-danger">
            <i class="fa fa-file-pdf me-1"></i> Export PDF
          </button>

          <!-- Import CSV -->
          
          <!-- Export CSV -->
          <button class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-file-export me-1"></i> Export CSV
          </button>

        </div>
      </div>

    <!-- Bar 2: Per Page, Reset, Search, Sort -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

      <!-- Left: Per Page & Reset -->
      <div class="d-flex gap-2 flex-wrap align-items-center">
        <select v-model="store.perPage" @change="store.changePageSize" class="form-select form-select-sm">
          <option value="10">10 data per halaman</option>
          <option value="25">25 data per halaman</option>
          <option value="50">50 data per halaman</option>
          <option value="100">100 data per halaman</option>
        </select>
        <button class="btn btn-outline-secondary btn-sm" @click="store.resetFilters">Reset</button>
        <button
     class="btn btn-sm btn-danger" :disabled="!selectedTags.length" @click="handleDeleteMany"> Hapus Banyak
    </button>
      </div>

      <!-- Right: Search & Sort -->
      <div class="d-flex gap-2 flex-wrap align-items-center">
        <div class="input-group input-group-sm">
                  <input type="text"
                        class="form-control"
                        placeholder="Cari Tags"
                      v-model="store.search">
                  <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                </div>
    

            <div class="d-flex align-items-center flex-wrap gap-3">
                    <label class="mb-0">Urutkan:</label>
                    <!-- sort by -->
                    <select
                        class="form-select form-select-sm w-auto"
                        v-model="store.sort.column" @change="store.changeSort"
                    >
                        <option value="name">Nama </option>
                        <option value="created_at">Tanggal Dibuat</option>
                    </select>

                    <!-- sort direction -->
                    <select
                        class="form-select form-select-sm w-auto"
                        v-model="store.sort.direction" @change="store.changeSort"
                    >
                        <option value="asc">Naik</option>
                        <option value="desc">Turun</option>
                    </select>
            </div>
      </div>

    </div>

  </div>
</div>

    
    
    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th class="ths">No</th>
            <th class="ths">Check</th>
            <th @click="store.toggleSort('name')" style="cursor:pointer">
              Name <i :class="sortDirectionIcon('name')"></i>
            </th>
            <th>Slug</th>
            <th @click="store.toggleSort('created_at')" style="cursor:pointer">
              Created At <i :class="sortDirectionIcon('created_at')"></i>
            </th>
            <th class="thsa">Action</th>
          </tr>
        </thead>

        <!-- Loading -->
        <tbody v-if="store.loading">
          <tr><td colspan="6" class="text-center py-3">
            <div class="spinner-border text-primary"></div>
          </td></tr>
        </tbody>

        <!-- Ada data -->
        <tbody v-else-if="store.tags?.data?.length">
          <tr v-for="(tag, i) in store.tags.data" :key="tag.id">
            <td>{{ (store.tags.current_page - 1) * store.perPage + i + 1 }}.</td>
            <td>
            <input type="checkbox" :value="tag.id" v-model="selectedTags">
            </td>
            <td>{{ tag.name }}</td>
            <td>{{ tag.slug }}</td>
            <td>{{ store.formatDate(tag.created_at) }}</td>
            <td>
              <button class="btn btn-sm btn-outline-warning me-1" @click="editTag(tag)"><i class="fa fa fa-edit"></i></button>
              <button class="btn btn-sm btn-outline-danger me-1" @click="handleDelete(tag.id)"><i class="fa fa fa-trash"></i></button>
              <button class="btn btn-outline-info btn-sm" @click="viewDetail(tag.id)">
                <i class="fa fa-eye"></i>
              </button>
            </td>
          </tr>
        </tbody>

        <!-- Tidak ada data -->
        <tbody v-else>
          <tr><td colspan="5" class="text-center">
            <div class="text-center text-danger mt-3">
              <i class="fa fa-exclamation-circle"></i> Data tidak ditemukan
            </div>
          </td></tr>
        </tbody>
      </table>
    </div>


    <!-- Pagination -->

     <div class="card shadow-sm mt-2 mb-4">
  <div class="card-body">
    <!-- Pagination -->
    <div v-if="store.tags" class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      
      <!-- Prev Button -->
      <button
        class="btn btn-sm btn-outline-primary"
        :disabled="!store.tags.prev_page_url"
        @click="store.fetchTags(store.tags.current_page - 1)"
      >
        <i class="fa-solid fa-chevron-left me-1"></i> Prev
      </button>

      <!-- Info Halaman -->
      <div class="text-center flex-grow-1">
        Page {{ store.tags.current_page }} of {{ store.tags.last_page }} | Total: {{ store.tags.total }}
      </div>

      <!-- Next Button -->
      <button
        class="btn btn-sm btn-outline-primary"
        :disabled="!store.tags.next_page_url"
        @click="store.fetchTags(store.tags.current_page + 1)"
      >
        Next <i class="fa-solid fa-chevron-right ms-1"></i>
      </button>

    </div>
  </div>
</div>





    <!-- Modal Form -->
    <div v-if="modalOpen" class="modal-backdrop fade show"></div>
        <div v-if="modalOpen" class="modal d-block" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ store.formData.id ? 'Edit Tag' : 'Tambah Tag' }}</h5>
                <button type="button" class="btn-close" @click="modalOpen=false"></button>
            </div>
            <div class="modal-body">
                <input type="text" v-model="store.formData.name"  :class="{ 'is-invalid': errorsFront.name }" class="form-control" placeholder="Nama Tag"/>
                <div v-if="store.errors.name" class="text-danger">{{ store.errors.name[0] }}</div>
                <div class="invalid-feedback">{{ errorsFront.name }}</div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" @click="modalOpen=false">Batal</button>
                <button class="btn btn-primary btn-sm" :disabled="saving" @click="saveUpdate">
                {{ saving ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
            </div>
        </div>
        </div>
  </div>



  <div v-if="modalOpen" class="modal-backdrop fade show"></div>
<div v-if="modalOpen" class="modal d-block" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Tag</h5>
        <button type="button" class="btn-close" @click="modalOpen=false"></button>
      </div>
      <div class="modal-body">
        <p><strong>ID:</strong> {{ store.formData.id }}</p>
        <p><strong>Nama:</strong> {{ store.formData.name }}</p>
        <p><strong>Slug:</strong> {{ store.formData.slug }}</p>
        <p><strong>Created At:</strong> {{ store.formatDate(store.formData.created_at) }}</p>
        <p><strong>Updated At:</strong> {{ store.formatDate(store.formData.updated_at) }}</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" @click="modalOpen=false">Tutup</button>
      </div>
    </div>
  </div>
</div>



<!-- ---
### Modal Export Laporan -->

<div v-if="exportModalOpen" class="modal-backdrop fade show"></div>
<div v-if="exportModalOpen" class="modal d-block" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ekspor Laporan Tags</h5>
        <button type="button" class="btn-close" @click="exportModalOpen=false"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Pilih Tipe Ekspor</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" v-model="exportType" value="month" id="exportByMonth">
              <label class="form-check-label" for="exportByMonth">Bulan</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" v-model="exportType" value="date" id="exportByDate">
              <label class="form-check-label" for="exportByDate">Tanggal</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" v-model="exportType" value="year" id="exportByYear">
              <label class="form-check-label" for="exportByYear">Tahun</label>
            </div>
          </div>
        </div>

        <div v-if="exportType === 'month'" class="row g-2 mb-3">
          <div class="col">
            <label class="form-label">Bulan</label>
            <select v-model="selectedMonth" class="form-select">
              <option value="1">Januari</option>
              <option value="2">Februari</option>
              <option value="3">Maret</option>
              <option value="4">April</option>
              <option value="5">Mei</option>
              <option value="6">Juni</option>
              <option value="7">Juli</option>
              <option value="8">Agustus</option>
              <option value="9">September</option>
              <option value="10">Oktober</option>
              <option value="11">November</option>
              <option value="12">Desember</option>
            </select>
          </div>
          <div class="col">
            <label class="form-label">Tahun</label>
            <select v-model="selectedYear" class="form-select">
              <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>
        </div>

        <div v-if="exportType === 'date'" class="row g-2 mb-3">
          <div class="col">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" v-model="startDate" class="form-control" />
          </div>
          <div class="col">
            <label class="form-label">Tanggal Akhir</label>
            <input type="date" v-model="endDate" class="form-control" />
          </div>
        </div>
        
        <div v-if="exportType === 'year'" class="mb-3">
          <label class="form-label">Pilih Tahun</label>
          <select v-model="selectedYear" class="form-select">
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="exportModalOpen=false">Batal</button>
        <button class="btn btn-primary" @click="handleExport">Ekspor</button>
      </div>
    </div>
  </div>
</div>
</AppLayout>
</template>


<style scoped>
    .ths {
        width: 3%;
    }
     .thsa {
        width: 11%;
    }
</style>
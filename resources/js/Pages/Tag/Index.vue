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

// load data awal
onMounted(() => {
  store.fetchTags()
})

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

</script>

<template>
<AppLayout>
  <div class="container py-3">
    <h2>{{ pageTitle }}</h2>

    <!-- Per page, search, sort -->
    <div class="d-flex justify-content-between mb-2 flex-wrap gap-2">
      <div class="d-flex gap-2">
        <select v-model="store.perPage" @change="store.changePageSize" class="form-select form-select-sm">
          <option value="10">10 / halaman</option>
          <option value="25">25 / halaman</option>
          <option value="50">50 / halaman</option>
          <option value="100">100 / halaman</option>
        </select>
        <button class="btn btn-outline-secondary btn-sm" @click="store.resetFilters">Reset</button>
        
      </div>

      <button
      class="btn btn-sm btn-danger" :disabled="!selectedTags.length" @click="handleDeleteMany"
       >
        Hapus Banyak
      </button>

      <div class="d-flex gap-2">
        <input type="text" placeholder="Cari Tags" v-model="store.search" class="form-control form-control-sm"/>
        <select v-model="store.sort.column" @change="store.changeSort" class="form-select form-select-sm">
          <option value="name">Nama</option>
          <option value="created_at">Tanggal Dibuat</option>
        </select>
        <select v-model="store.sort.direction" @change="store.changeSort" class="form-select form-select-sm">
          <option value="asc">Naik</option>
          <option value="desc">Turun</option>
        </select>
      </div>

      <button class="btn btn-sm btn-primary" @click="addTag">Add Tag</button>

      
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
            <td>{{ (store.tags.current_page - 1) * store.perPage + i + 1 }}</td>
            <td>
            <input type="checkbox" :value="tag.id" v-model="selectedTags">
            </td>
            <td>{{ tag.name }}</td>
            <td>{{ tag.slug }}</td>
            <td>{{ store.formatDate(tag.created_at) }}</td>
            <td>
              <button class="btn btn-sm btn-warning me-1" @click="editTag(tag)"><i class="fa fa fa-edit"></i></button>
              <button class="btn btn-sm btn-danger" @click="handleDelete(tag.id)"><i class="fa fa fa-trash"></i></button>
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
    <div v-if="store.tags" class="d-flex justify-content-between align-items-center mt-2">
      <button class="btn btn-sm btn-outline-primary" :disabled="!store.tags.prev_page_url" @click="store.fetchTags(store.tags.current_page - 1)">Prev</button>
      <div>
        Page {{ store.tags.current_page }} of {{ store.tags.last_page }} | Total: {{ store.tags.total }}
      </div>
      <button class="btn btn-sm btn-outline-primary" :disabled="!store.tags.next_page_url" @click="store.fetchTags(store.tags.current_page + 1)">Next</button>
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
</AppLayout>
</template>


<style scoped>
    .ths {
        width: 3%;
    }
     .thsa {
        width: 8%;
    }
</style>
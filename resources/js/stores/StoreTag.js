import { defineStore } from 'pinia'
import axios from 'axios'

export const useTagsStore = defineStore('tags', {
  state: () => ({
    tags: null,          // data tabel
    search: '',          // kata kunci search
    perPage: 10,         // jumlah data per halaman
    sort: { column: 'created_at', direction: 'desc' },
    loading: false,      // spinner
    formData: { id: null, name: '' },  // untuk modal form
    errors: {},          // validasi form
  }),
  actions: {
    // fetch data dari server
    async fetchTags(page = 1) {
      this.loading = true
      try {
        const res = await axios.get('/tag/data', {
          params: {
            page,
            per_page: this.perPage,
            search: this.search,
            sort_by: this.sort.column,
            sort_dir: this.sort.direction
          }
        })
        this.tags = res.data
      } finally {
        this.loading = false
      }
    },

    // simpan data baru
    async saveTag() {
      try {
        const res = await axios.post('/tag', { name: this.formData.name })
        await this.fetchTags(1) // reload page 1
        return res.data
      } catch (err) {
        if (err.response?.status === 422) {
          this.errors = err.response.data.errors
        }
        throw err
      }
    },

    // update data
    async updateTag() {
      try {
        const res = await axios.put(`/tag/${this.formData.id}`, { name: this.formData.name })
        await this.fetchTags(this.tags?.current_page || 1)
        return res.data
      } catch (err) {
        if (err.response?.status === 422) {
          this.errors = err.response.data.errors
        }
        throw err
      }
    },

    // delete data
    async deleteTag(id) {
      await axios.delete(`/tag/${id}`)
      await this.fetchTags(this.tags?.current_page || 1)
    },

     // Hapus banyak sekaligus
    async deleteMany(ids = []) {
      if (!ids.length) return
      try {
        // opsi 1: kirim semua ID ke backend sekaligus
        await axios.post('/tag/delete-many', { ids })
        
        // atau opsi 2: hapus per ID secara paralel
        // await Promise.all(ids.map(id => axios.delete(`/tag/${id}`)))

        await this.fetchTags(this.tags?.current_page || 1)
      } catch (err) {
        console.error(err)
      }
    },

    // toggle sort saat klik header
    toggleSort(column) {
      if (this.sort.column === column) {
        this.sort.direction = this.sort.direction === 'asc' ? 'desc' : 'asc'
      } else {
        this.sort.column = column
        this.sort.direction = 'asc'
      }
      this.fetchTags(this.tags?.current_page || 1)
    },

    // ganti sort dari dropdown
    changeSort() {
      this.fetchTags(this.tags?.current_page || 1)
    },

    // ganti jumlah data per halaman
    changePageSize() {
      this.fetchTags(1)
    },

    // reset semua filter
    resetFilters() {
      this.search = ''
      this.perPage = 10
      this.sort = { column: 'created_at', direction: 'desc' }
      this.fetchTags(1)
    },

       formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return 'Belum Pernah update';
    return date.toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'long',
      day: '2-digit',
    });
  },


    async fetchTagDetail(id) {
    this.loading = true;
    try {
        const res = await axios.get(`/tag/${id}`);
        this.formData = res.data; // bisa langsung tampil di modal/detail component
    } catch (err) {
        console.error(err);
    } finally {
        this.loading = false;
    }
    },

async exportTags(params) {
            try {
                // Panggilan API untuk ekspor laporan dengan responseType 'blob'
                const response = await axios.get('/tags/export', { 
                    params: params,
                    responseType: 'blob' 
                });
                
                // Mengembalikan respons sukses ke komponen
                return response;

            } catch (err) {
                // Melempar error ke komponen untuk penanganan UI
                throw err;
            }
        }



  }
})

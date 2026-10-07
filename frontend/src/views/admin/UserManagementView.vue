<template>
  <main class="main-viewport">
    <div class="page-container">
      <div class="header-section mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="page-title">User Management</h1>
          <p class="page-subtitle">Manage system users, roles, and office assignments.</p>
        </div>
        <div class="flex items-center gap-3">
          <button @click="toggleSubmissionLimit()" class="btn-secondary flex items-center gap-2" :class="adSubmissionLimitEnabled ? '!bg-green-600 !border-green-700 !text-white hover:!bg-green-700' : '!bg-red-600 !border-red-700 !text-white hover:!bg-red-700'">
            <span class="material-symbols-outlined">{{ adSubmissionLimitEnabled ? 'toggle_on' : 'toggle_off' }}</span>
            Limit AD Submissions (Mon-Fri): {{ adSubmissionLimitEnabled ? 'ON' : 'OFF' }}
          </button>
          <button @click="openModal()" class="btn-primary flex items-center gap-2">
            <span class="material-symbols-outlined">person_add</span>
            Add User
          </button>
        </div>
      </div>

      <div class="layout-stacked">
        
        <!-- TWG Users Card -->
        <section class="user-card glass-card">
          <div class="card-header">
            <div class="flex items-center justify-between w-full flex-wrap gap-4">
              <div class="flex items-center gap-3">
                <div class="icon-box bg-purple-500/20 text-purple-400 border border-purple-500/30">
                  <span class="material-symbols-outlined">group</span>
                </div>
                <h2 class="text-xl font-bold text-white">TWG Users <span class="badge ml-2 bg-purple-500/20 text-purple-300 border border-purple-500/30">{{ twgUsers.length }}</span></h2>
              </div>
              <div class="flex gap-3 flex-1 md:flex-none justify-end">
                <div class="relative w-full md:w-64">
                  <input v-model="searchTwg" type="text" placeholder="Search..." class="search-input">
                </div>
                <select v-model="filterTwgOffice" class="filter-select">
                  <option value="">All Offices</option>
                  <option v-for="office in offices" :key="office.unit_id" :value="office.unit_name">{{ office.unit_name }}</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-body custom-scrollbar">
            <div v-if="twgUsers.length === 0" class="empty-state">No TWG users found.</div>
            <div v-else class="user-grid">
              <div v-for="user in twgUsers" :key="user.id" class="user-item">
                <div class="user-info">
                  <div class="user-name">{{ user.full_name || 'N/A' }}</div>
                  <div class="user-meta">{{ user.email }}</div>
                  <div class="user-meta text-purple-300/80 mt-1">{{ user.office_name || 'No Office' }}</div>
                      <div class="user-meta mt-2 flex flex-wrap items-center gap-2">
                        <span class="text-blue-300 flex items-center gap-1"><span class="material-symbols-outlined text-xs" style="font-size: 14px;">login</span> Last login: {{ formatLastLogin(user.last_login) }}</span>
                      </div>
                </div>
                <div class="user-actions mt-auto">
                  <button @click="openModal(user)" class="btn-edit" title="Edit User">
                    <span class="material-symbols-outlined text-sm">edit</span> Edit
                  </button>
                  <button @click="suspendUser(user.id)" class="btn-suspend" title="Suspend User">
                    <span class="material-symbols-outlined text-sm">block</span> Suspend
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Non-TWG Users Card -->
        <section class="user-card glass-card">
          <div class="card-header">
            <div class="flex items-center justify-between w-full flex-wrap gap-4">
              <div class="flex items-center gap-3">
                <div class="icon-box bg-purple-500/20 text-purple-400 border border-purple-500/30">
                  <span class="material-symbols-outlined">person</span>
                </div>
                <h2 class="text-xl font-bold text-white">Proponents <span class="badge ml-2 bg-purple-500/20 text-purple-300 border border-purple-500/30">{{ nonTwgUsers.length }}</span></h2>
              </div>
              <div class="flex gap-3 flex-1 md:flex-none justify-end">
                <div class="relative w-full md:w-64">
                  <input v-model="searchNonTwg" type="text" placeholder="Search..." class="search-input">
                </div>
                <select v-model="filterNonTwgOffice" class="filter-select">
                  <option value="">All Offices</option>
                  <option v-for="office in offices" :key="office.unit_id" :value="office.unit_name">{{ office.unit_name }}</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-body custom-scrollbar" style="display: flex; flex-direction: column; gap: 2rem;">
            <div v-if="nonTwgUsers.length === 0" class="empty-state">No Proponents found.</div>
            <template v-else>
              <!-- Users With Submissions Section -->
              <div class="sub-section">
                <h3 class="text-lg font-semibold text-purple-300 mb-3 flex items-center gap-2">
                  <span class="material-symbols-outlined text-sm">assignment_turned_in</span> 
                  Users with submissions 
                  <span class="badge bg-purple-500/20 text-purple-300 border border-purple-500/30 text-xs">{{ nonTwgWithSubmissions.length }}</span>
                </h3>
                <div v-if="nonTwgWithSubmissions.length === 0" class="text-sm text-slate-400 italic mb-4">No users with submissions.</div>
                <div v-else class="user-grid">
                  <div v-for="user in nonTwgWithSubmissions" :key="user.id" class="user-item">
                    <div class="user-info">
                      <div class="user-name">{{ user.full_name || 'N/A' }}</div>
                      <div class="user-meta">{{ user.email }}</div>
                      <div class="user-meta text-purple-300/80 mt-1">{{ user.office_name || 'No Office' }}</div>
                      <div class="user-meta mt-2 flex flex-wrap items-center gap-3">
                        <span class="text-emerald-400 flex items-center gap-1"><span class="material-symbols-outlined text-xs" style="font-size: 14px;">calendar_today</span> {{ daysOnSystem(user.created_at) }} days on system</span>
                        <span class="text-blue-300 flex items-center gap-1"><span class="material-symbols-outlined text-xs" style="font-size: 14px;">login</span> Last login: {{ formatLastLogin(user.last_login) }}</span>
                      </div>
                    </div>
                    <div class="user-actions mt-auto">
                      <button @click="openModal(user)" class="btn-edit" title="Edit User">
                        <span class="material-symbols-outlined text-sm">edit</span> Edit
                      </button>
                      <button @click="suspendUser(user.id)" class="btn-suspend" title="Suspend User">
                        <span class="material-symbols-outlined text-sm">block</span> Suspend
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Users Without Submissions Section -->
              <div class="sub-section">
                <h3 class="text-lg font-semibold text-slate-300 mb-3 flex items-center gap-2">
                  <span class="material-symbols-outlined text-sm">assignment_late</span> 
                  Users without submissions
                  <span class="badge bg-slate-500/20 text-slate-300 border border-slate-500/30 text-xs">{{ nonTwgWithoutSubmissions.length }}</span>
                </h3>
                <div v-if="nonTwgWithoutSubmissions.length === 0" class="text-sm text-slate-400 italic">No users without submissions.</div>
                <div v-else class="user-grid">
                  <div v-for="user in nonTwgWithoutSubmissions" :key="user.id" class="user-item">
                    <div class="user-info">
                      <div class="user-name">{{ user.full_name || 'N/A' }}</div>
                      <div class="user-meta">{{ user.email }}</div>
                      <div class="user-meta text-purple-300/80 mt-1">{{ user.office_name || 'No Office' }}</div>
                      <div class="user-meta mt-2 flex flex-wrap items-center gap-3">
                        <span class="text-emerald-400 flex items-center gap-1"><span class="material-symbols-outlined text-xs" style="font-size: 14px;">calendar_today</span> {{ daysOnSystem(user.created_at) }} days on system</span>
                        <span class="text-blue-300 flex items-center gap-1"><span class="material-symbols-outlined text-xs" style="font-size: 14px;">login</span> Last login: {{ formatLastLogin(user.last_login) }}</span>
                      </div>
                    </div>
                    <div class="user-actions mt-auto">
                      <button @click="openModal(user)" class="btn-edit" title="Edit User">
                        <span class="material-symbols-outlined text-sm">edit</span> Edit
                      </button>
                      <button @click="suspendUser(user.id)" class="btn-suspend" title="Suspend User">
                        <span class="material-symbols-outlined text-sm">block</span> Suspend
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </section>

        <!-- Suspended Accounts Card -->
        <section class="user-card glass-card border-red-500/30">
          <div class="card-header">
            <div class="flex items-center justify-between w-full flex-wrap gap-4">
              <div class="flex items-center gap-3">
                <div class="icon-box bg-red-500/20 text-red-400 border border-red-500/30">
                  <span class="material-symbols-outlined">no_accounts</span>
                </div>
                <h2 class="text-xl font-bold text-white">Suspended Accounts <span class="badge ml-2 bg-red-500/20 text-red-300 border border-red-500/30">{{ suspendedUsers.length }}</span></h2>
              </div>
              <div class="flex gap-3 flex-1 md:flex-none justify-end">
                <div class="relative w-full md:w-64">
                  <input v-model="searchSuspended" type="text" placeholder="Search..." class="search-input !border-red-500/30 focus:!border-red-500/60">
                </div>
                <select v-model="filterSuspendedOffice" class="filter-select !border-red-500/30 focus:!border-red-500/60">
                  <option value="">All Offices</option>
                  <option v-for="office in offices" :key="office.unit_id" :value="office.unit_name">{{ office.unit_name }}</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-body custom-scrollbar relative">

            <div v-if="suspendedUsers.length === 0" class="empty-state !text-red-400/50">No suspended users.</div>
            <div v-else class="user-grid">
              <div v-for="user in suspendedUsers" :key="user.id" class="user-item !border-red-500/20 hover:!border-red-500/40 hover:!bg-red-500/10">
                <div class="user-info">
                  <div class="user-name text-red-200">{{ user.full_name || 'N/A' }}</div>
                  <div class="user-meta">{{ user.email }}</div>
                  <div class="user-meta text-red-300/80 mt-1">{{ user.office_name || 'No Office' }}</div>
                      <div class="user-meta mt-2 flex flex-wrap items-center gap-2">
                        <span class="text-blue-300 flex items-center gap-1"><span class="material-symbols-outlined text-xs" style="font-size: 14px;">login</span> Last login: {{ formatLastLogin(user.last_login) }}</span>
                      </div>
                  <div class="user-meta mt-2 text-xs italic text-red-400">Suspended on: {{ formatDate(user.deleted_at) }}</div>
                </div>
                <div class="user-actions mt-auto flex gap-2">
                  <button @click="restoreUser(user.id)" class="btn-restore flex-1" title="Restore User">
                    <span class="material-symbols-outlined text-sm">restore</span> Restore
                  </button>
                  <button @click="deleteUser(user.id)" class="btn-delete flex-1 bg-red-900/50 text-red-300 hover:bg-red-800 border border-red-800 rounded-lg flex items-center justify-center gap-1 py-2 text-xs font-semibold transition-colors" title="Delete Credentials">
                    <span class="material-symbols-outlined text-sm">delete_forever</span> Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

      </div>

    </div>

    <!-- User Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-container glass-card custom-scrollbar">
        <div class="modal-header border-b border-purple-900/30 p-6 flex justify-between items-center">
          <h2 class="text-xl font-bold text-white">{{ isEdit ? 'Edit User' : 'Add New User' }}</h2>
          <button @click="closeModal" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="modal-body p-6">
          <form @submit.prevent="submitForm" class="flex flex-col gap-4">
            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input type="text" v-model="form.full_name" required class="form-input" placeholder="Enter full name" />
            </div>
            
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" v-model="form.email" required class="form-input" placeholder="Enter email address" />
            </div>
            
            <div class="form-group">
              <label class="form-label">Password</label>
              <input type="password" v-model="form.password" :required="!isEdit" class="form-input" :placeholder="isEdit ? 'Leave blank to keep current' : 'Enter password'" minlength="6" />
            </div>
            
            <div class="form-group">
              <label class="form-label">Role</label>
              <select v-model="form.user_role" required class="form-input">
                <option value="Non-TWG">Proponent</option>
                <option value="TWG">TWG</option>
                <option value="Staff">Staff</option>
                <option value="Director">Director (Admin)</option>
              </select>
            </div>
            
            <div class="form-group">
              <label class="form-label">Office Unit</label>
              <select v-model="form.office_id" required class="form-input">
                <option value="" disabled>Select Office</option>
                <option v-for="office in offices" :key="office.unit_id" :value="office.unit_id">
                  {{ office.unit_name }}
                </option>
              </select>
            </div>
            
            <div class="modal-actions mt-4 flex justify-end gap-3">
              <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="isSubmitting">
                {{ isSubmitting ? 'Saving...' : (isEdit ? 'Update User' : 'Create User') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import api from '../../api';

const router = useRouter();
const users = ref([]);
const offices = ref([]);
const adSubmissionLimitEnabled = ref(true);

const toggleSubmissionLimit = async () => {
  try {
    const newVal = !adSubmissionLimitEnabled.value;
    await api.post('settings/system', { ad_submission_limit_enabled: newVal });
    adSubmissionLimitEnabled.value = newVal;
    Swal.fire({
      icon: 'success',
      title: 'Settings Updated',
      text: `AD Submission Mon-Fri Limit is now ${newVal ? 'ON' : 'OFF'}`,
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    });
  } catch (err) {
    console.error('Failed to update settings:', err);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update system settings' });
  }
};

const fetchSettings = async () => {
  try {
    const res = await api.get('settings/system');
    adSubmissionLimitEnabled.value = res.data.ad_submission_limit_enabled ?? true;
  } catch (err) {
    console.error('Failed to fetch system settings:', err);
  }
};

// Individual search/filter states
const searchTwg = ref('');
const filterTwgOffice = ref('');

const searchNonTwg = ref('');
const filterNonTwgOffice = ref('');

const searchSuspended = ref('');
const filterSuspendedOffice = ref('');

const showModal = ref(false);
const isEdit = ref(false);
const isSubmitting = ref(false);
const form = ref({
  id: null,
  full_name: '',
  email: '',
  password: '',
  user_role: 'Non-TWG',
  office_id: ''
});

const openModal = (user = null) => {
  if (user) {
    isEdit.value = true;
    form.value = {
      id: user.id,
      full_name: user.full_name || '',
      email: user.email || '',
      password: '',
      user_role: user.user_role || 'Non-TWG',
      office_id: user.office_id || ''
    };
  } else {
    isEdit.value = false;
    form.value = {
      id: null,
      full_name: '',
      email: '',
      password: '',
      user_role: 'Non-TWG',
      office_id: ''
    };
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitForm = async () => {
  isSubmitting.value = true;
  try {
    const url = isEdit.value ? `users/update/${form.value.id}` : `users/create`;
    const res = await api.post(url, form.value);
    
    if (res.data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: res.data.message,
        timer: 1500,
        showConfirmButton: false
      });
      closeModal();
      fetchUsers();
    } else {
      throw new Error(res.data.message || 'Operation failed');
    }
  } catch (err) {
    let msg = err.response?.data?.message || err.response?.data?.messages || err.message || 'An error occurred';
    if (typeof msg === 'object') {
      msg = Object.values(msg).join(', ');
    }
    Swal.fire({ icon: 'error', title: 'Error', text: msg });
  } finally {
    isSubmitting.value = false;
  }
};

const fetchOffices = async () => {
  try {
    const res = await api.get('office_units');
    offices.value = res.data;
  } catch (err) {
    console.error('Failed to fetch offices:', err);
  }
};

const fetchUsers = async () => {
  try {
    const res = await api.get('users');
    users.value = res.data;
  } catch (err) {
    console.error('Failed to fetch users:', err);
  }
};

const filterUserList = (userList, search, office) => {
  return userList.filter(user => {
    if (user.role === 'admin' || user.user_role === 'Director') return false;

    const matchesSearch = !search || 
      (user.full_name?.toLowerCase().includes(search.toLowerCase()) || 
       user.email?.toLowerCase().includes(search.toLowerCase()) ||
       user.user_role?.toLowerCase().includes(search.toLowerCase()));
       
    const matchesOffice = !office || user.office_name === office;

    return matchesSearch && matchesOffice;
  });
};

const twgUsers = computed(() => {
  const baseList = users.value.filter(u => !u.deleted_at && u.user_role === 'TWG' && u.role !== 'deleted');
  return filterUserList(baseList, searchTwg.value, filterTwgOffice.value);
});

const nonTwgUsers = computed(() => {
  const baseList = users.value.filter(u => !u.deleted_at && u.user_role === 'Non-TWG' && u.role !== 'deleted');
  return filterUserList(baseList, searchNonTwg.value, filterNonTwgOffice.value);
});

const nonTwgWithSubmissions = computed(() => {
  return nonTwgUsers.value.filter(u => parseInt(u.ad_count || 0) > 0 || parseInt(u.ar_count || 0) > 0);
});

const nonTwgWithoutSubmissions = computed(() => {
  return nonTwgUsers.value.filter(u => parseInt(u.ad_count || 0) === 0 && parseInt(u.ar_count || 0) === 0);
});

const daysOnSystem = (dateString) => {
  if (!dateString) return 0;
  const created = new Date(dateString.endsWith('Z') ? dateString : dateString + 'Z');
  const now = new Date();
  const diffTime = Math.abs(now - created);
  return Math.floor(diffTime / (1000 * 60 * 60 * 24));
};

const formatLastLogin = (dateString) => {
  if (!dateString) return 'Never';
  const login = new Date(dateString.endsWith('Z') ? dateString : dateString + 'Z');
  const now = new Date();
  const diffTime = Math.abs(now - login);
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays === 0) {
    const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
    if (diffHours === 0) {
      const diffMins = Math.floor(diffTime / (1000 * 60));
      return diffMins <= 1 ? 'Just now' : `${diffMins} mins ago`;
    }
    return `${diffHours} hours ago`;
  }
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays} days ago`;
  
  return login.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const suspendedUsers = computed(() => {
  const baseList = users.value.filter(u => !!u.deleted_at && u.role !== 'deleted');
  return filterUserList(baseList, searchSuspended.value, filterSuspendedOffice.value);
});

const suspendUser = async (id) => {
  const result = await Swal.fire({
    title: 'Suspend User?',
    text: "This user will not be able to log in. Their account will be moved to Suspended Accounts.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#f59e0b',
    cancelButtonColor: '#475569',
    confirmButtonText: 'Yes, suspend them!'
  });

  if (result.isConfirmed) {
    try {
      const res = await api.post(`users/suspend/${id}`);
      if (res.data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Suspended!',
          text: 'User has been suspended.',
          timer: 1500,
          showConfirmButton: false
        });
        fetchUsers();
      } else {
        throw new Error(res.data.message || 'Failed to suspend');
      }
    } catch (err) {
      Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to suspend user.' });
    }
  }
};

const restoreUser = async (id) => {
  try {
    const res = await api.post(`users/restore/${id}`);
    if (res.data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Restored!',
        text: 'User account has been restored.',
        timer: 1500,
        showConfirmButton: false
      });
      fetchUsers();
    } else {
      throw new Error(res.data.message || 'Failed to restore');
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to restore user.' });
  }
};

const deleteUser = async (id) => {
  const result = await Swal.fire({
    title: 'Delete Credentials?',
    text: "This will permanently delete the user's login credentials. Their submitted reports will be kept for data privacy and integrity.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#475569',
    confirmButtonText: 'Yes, delete it!'
  });

  if (result.isConfirmed) {
    try {
      const res = await api.post(`users/delete/${id}`);
      if (res.data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'User credentials have been deleted.',
          timer: 1500,
          showConfirmButton: false
        });
        fetchUsers();
      } else {
        throw new Error(res.data.message || 'Failed to delete credentials');
      }
    } catch (err) {
      Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to delete credentials.' });
    }
  }
};


const formatDate = (dateString) => {
  if (!dateString) return '---';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
};

onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!user.id || (user.role !== 'admin' && user.role !== 'gad_staff')) {
    router.push('/login');
    return;
  }
  fetchOffices();
  fetchUsers();
  fetchSettings();
});
</script>

<style scoped>
.main-viewport { flex: 1; overflow-y: auto; background: transparent; }
.page-container { min-height: 100vh; padding: 1rem; max-width: 1200px; margin: 0 auto; }
.page-title {
  font-size: 2rem;
  color: #16213e;
  font-weight: 900;
  letter-spacing: -0.025em;
  margin-bottom: 0.5rem;
}
.page-subtitle {
  color: #475569;
  font-size: 0.95rem;
}
.glass-card { 
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); 
  backdrop-filter: blur(24px); 
  border-radius: 1.5rem; 
  border: 1px solid rgba(185, 121, 204, 0.25); 
  box-shadow: 0 20px 40px rgba(0,0,0,0.15), 0 0 30px rgba(185, 121, 204, 0.05); 
}

.layout-stacked {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.user-card {
  display: flex;
  flex-direction: column;
  min-height: 250px;
  max-height: 500px;
  overflow: hidden;
}

.card-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(185, 121, 204, 0.15);
  background: rgba(0, 0, 0, 0.2);
}

.search-input {
  width: 100%;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(185, 121, 204, 0.3);
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  color: white;
  font-size: 0.875rem;
  outline: none;
  transition: all 0.2s;
}
.search-input:focus {
  border-color: rgba(185, 121, 204, 0.6);
  background: rgba(0, 0, 0, 0.4);
}

.filter-select {
  background: rgba(0, 0, 0, 0.3) url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23b979cc' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E") no-repeat right 1rem center/1.25rem 1.25rem;
  appearance: none;
  border: 1px solid rgba(185, 121, 204, 0.3);
  border-radius: 0.5rem;
  padding: 0.5rem 2.5rem 0.5rem 1rem;
  color: white;
  font-size: 0.875rem;
  outline: none;
  transition: all 0.2s;
  cursor: pointer;
}
.filter-select:focus {
  border-color: rgba(185, 121, 204, 0.6);
  background: rgba(0, 0, 0, 0.4);
}
.filter-select option {
  background: #1e293b;
  color: white;
}

.icon-box {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 800;
}

.card-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #b979cc;
  opacity: 0.7;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
  padding: 2rem;
}

.user-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.user-item {
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(185, 121, 204, 0.15);
  border-radius: 1rem;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  transition: all 0.2s ease;
  min-height: 140px;
}

.user-item:hover {
  background: rgba(0, 0, 0, 0.4);
  border-color: rgba(185, 121, 204, 0.4);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-name {
  font-weight: 700;
  color: #f8fafc;
  font-size: 1rem;
}

.user-meta {
  color: #94a3b8;
  font-size: 0.75rem;
  font-weight: 500;
}

.user-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(185, 121, 204, 0.15);
}

.btn-suspend, .btn-restore, .btn-delete {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  flex: 1;
  padding: 0.5rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  transition: all 0.2s;
  border: 1px solid transparent;
}

.btn-suspend {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
  border-color: rgba(245, 158, 11, 0.2);
}

.btn-suspend:hover {
  background: rgba(245, 158, 11, 0.2);
  border-color: rgba(245, 158, 11, 0.4);
}

.btn-restore {
  background: rgba(34, 197, 94, 0.1);
  color: #22c55e;
  border-color: rgba(34, 197, 94, 0.2);
}

.btn-restore:hover {
  background: rgba(34, 197, 94, 0.2);
  border-color: rgba(34, 197, 94, 0.4);
}

.btn-delete {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border-color: rgba(239, 68, 68, 0.2);
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.4);
}

.warning-note {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #fca5a5;
  padding: 0.75rem;
  border-radius: 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: linear-gradient(135deg, #b979cc 0%, #9b59b6 100%);
  color: white;
  padding: 0.6rem 1.25rem;
  border-radius: 0.75rem;
  font-weight: 600;
  border: none;
  transition: all 0.2s;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(185, 121, 204, 0.3);
}
.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(185, 121, 204, 0.4);
}
.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: #e2e8f0;
  padding: 0.6rem 1.25rem;
  border-radius: 0.75rem;
  font-weight: 600;
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.2s;
  cursor: pointer;
}
.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
}

.btn-edit {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  flex: 1;
  padding: 0.5rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  transition: all 0.2s;
  border: 1px solid transparent;
  background: rgba(168, 85, 247, 0.1);
  color: #c084fc;
  border-color: rgba(168, 85, 247, 0.2);
}
.btn-edit:hover {
  background: rgba(168, 85, 247, 0.2);
  border-color: rgba(168, 85, 247, 0.4);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-container {
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  color: #cbd5e1;
  font-size: 0.875rem;
  font-weight: 500;
}

.form-input {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(185, 121, 204, 0.3);
  color: white;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  font-size: 0.95rem;
  outline: none;
  transition: all 0.2s;
}
.form-input:focus {
  border-color: #b979cc;
  background: rgba(0, 0, 0, 0.5);
}
select.form-input {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23b979cc' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1.25rem 1.25rem;
  padding-right: 2.5rem;
}
.form-input option {
  background: #1e293b;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255,255,255,0.02);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(185, 121, 204, 0.3);
  border-radius: 10px;
}
</style>

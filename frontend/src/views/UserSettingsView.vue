<template>
  <div class="settings-container">
    <!-- Header with Profile Overview -->
    <div class="settings-header">
      <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 relative z-10">
        <!-- Avatar Preview / Upload Trigger -->
        <div class="relative group">
          <div class="w-24 h-24 rounded-2xl overflow-hidden bg-purple-900/40 border-2 border-purple-400/40 shadow-xl flex items-center justify-center text-3xl font-extrabold text-white uppercase select-none">
            <img v-if="avatarPreview || user.profile_picture" :src="avatarPreview || getAvatarUrl(user.profile_picture)" alt="Avatar" class="w-full h-full object-cover" />
            <span v-else>{{ userInitials }}</span>
          </div>
          <label class="absolute -bottom-2 -right-2 bg-purple-600 hover:bg-purple-500 text-white p-2 rounded-xl cursor-pointer shadow-lg transition-transform group-hover:scale-110 flex items-center justify-center border border-purple-400/30" title="Change profile picture">
            <span class="material-symbols-outlined text-sm">photo_camera</span>
            <input type="file" accept="image/*" class="hidden" @change="handleAvatarSelected" />
          </label>
        </div>

        <div class="flex-1 text-center sm:text-left">
          <div class="inline-flex items-center gap-2 bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full border border-purple-500/30 text-xs font-bold uppercase tracking-wider mb-2">
            <span>{{ user.user_role || (user.role === 'non-twg' ? 'Proponent' : 'User') }}</span>
            <span v-if="user.office_acronym" class="text-purple-200">({{ user.office_acronym }})</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold text-white leading-tight">
            {{ user.full_name || user.username || 'User Profile' }}
          </h1>
          <p class="text-purple-200/80 text-sm mt-1 flex flex-wrap items-center justify-center sm:justify-start gap-3">
            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">business</span> {{ user.office_name || 'No Office Assigned' }}</span>
            <span class="text-purple-400/60">•</span>
            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">location_on</span> {{ user.location || 'La Trinidad Campus' }}</span>
          </p>
        </div>
      </div>
    </div>

    <div class="settings-content mt-6">
      
      <!-- 1. PERSONAL INFORMATION -->
      <div class="settings-card">
        <div class="card-header">
          <span class="material-symbols-outlined text-purple-400">badge</span>
          <h2 class="card-title">Personal Information</h2>
        </div>
        
        <form @submit.prevent="savePersonalInfo" class="form-group">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="input-wrapper">
              <label class="input-label">First Name <span class="text-red-400">*</span></label>
              <input type="text" v-model="personalForm.first_name" class="custom-input" required placeholder="First name" />
            </div>
            <div class="input-wrapper">
              <label class="input-label">Middle Name</label>
              <input type="text" v-model="personalForm.middle_name" class="custom-input" placeholder="Middle name (optional)" />
            </div>
            <div class="input-wrapper">
              <label class="input-label">Last Name <span class="text-red-400">*</span></label>
              <input type="text" v-model="personalForm.last_name" class="custom-input" required placeholder="Last name" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="input-wrapper">
              <label class="input-label">Sex</label>
              <select v-model="personalForm.sex" class="custom-input">
                <option value="" class="bg-[#1a1a2e]">Select Sex</option>
                <option value="Male" class="bg-[#1a1a2e]">Male</option>
                <option value="Female" class="bg-[#1a1a2e]">Female</option>
                <option value="Prefer not to say" class="bg-[#1a1a2e]">Prefer not to say</option>
              </select>
            </div>
            <div class="input-wrapper">
              <label class="input-label">Current Display Name</label>
              <input type="text" :value="computedFullName" disabled class="custom-input !bg-white/5 opacity-70 cursor-not-allowed" />
            </div>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="isSavingPersonal">
              <span v-if="isSavingPersonal" class="material-symbols-outlined animate-spin text-sm mr-2">refresh</span>
              {{ isSavingPersonal ? 'Saving...' : 'Save Personal Information' }}
            </button>
          </div>
          <p v-if="personalSuccess" class="success-msg">{{ personalSuccess }}</p>
          <p v-if="personalError" class="error-msg">{{ personalError }}</p>
        </form>
      </div>

      <!-- 2. DESIGNATION & ACADEMIC DETAILS -->
      <div class="settings-card">
        <div class="card-header">
          <span class="material-symbols-outlined text-blue-400">domain</span>
          <h2 class="card-title">Designation & Affiliation</h2>
        </div>
        
        <form @submit.prevent="saveDesignation" class="form-group">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="input-wrapper">
              <label class="input-label">Campus Location</label>
              <select 
                v-model="designationForm.campus_location" 
                @change="handleCampusChange" 
                class="custom-input cursor-pointer"
              >
                <option value="La Trinidad Campus" class="bg-[#1a1a2e]">La Trinidad Campus</option>
                <option value="Buguias Campus" class="bg-[#1a1a2e]">Buguias Campus</option>
                <option value="Bokod Campus" class="bg-[#1a1a2e]">Bokod Campus</option>
              </select>
            </div>
            <div class="input-wrapper">
              <label class="input-label">College / Office</label>
              <select 
                v-model="designationForm.office_id" 
                class="custom-input cursor-pointer"
                required
              >
                <option value="" disabled class="bg-[#1a1a2e]">Select College / Office</option>
                <option 
                  v-for="unit in officesForSelectedCampus" 
                  :key="unit.unit_id" 
                  :value="unit.unit_id"
                  class="bg-[#1a1a2e]"
                >
                  {{ unit.unit_name }}{{ unit.office_acronym ? ' (' + unit.office_acronym + ')' : '' }}
                </option>
                <option value="add_new" class="bg-[#1a1a2e] text-purple-300 font-bold">+ Not in the list? Add new office...</option>
              </select>
            </div>
          </div>

          <div v-if="designationForm.office_id === 'add_new'" class="input-wrapper">
            <label class="input-label text-purple-400">New College / Office Name</label>
            <input 
              type="text" 
              v-model="designationForm.new_office_name" 
              class="custom-input !border-purple-500/50" 
              placeholder="Enter full new college or office name" 
              required
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="input-wrapper">
              <label class="input-label">Department</label>
              <input type="text" v-model="designationForm.department" class="custom-input" placeholder="e.g. Department of Information Technology (optional)" />
              <span class="text-[11px] text-slate-400">Academic unit or subdivision within your office/college</span>
            </div>
            <div class="input-wrapper">
              <label class="input-label">Position / Title</label>
              <input type="text" v-model="designationForm.position" class="custom-input" placeholder="e.g. Instructor, Student, GAD Coordinator" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="input-wrapper">
              <label class="input-label">Student ID / Employee ID</label>
              <input type="text" v-model="designationForm.student_id" class="custom-input" placeholder="e.g. 2022-12345 (optional)" />
            </div>
            <div class="input-wrapper">
              <label class="input-label">Year Level (For Students)</label>
              <select v-model="designationForm.year_level" class="custom-input">
                <option value="" class="bg-[#1a1a2e]">None / Faculty / Staff</option>
                <option value="1st Year" class="bg-[#1a1a2e]">1st Year</option>
                <option value="2nd Year" class="bg-[#1a1a2e]">2nd Year</option>
                <option value="3rd Year" class="bg-[#1a1a2e]">3rd Year</option>
                <option value="4th Year" class="bg-[#1a1a2e]">4th Year</option>
                <option value="Graduate" class="bg-[#1a1a2e]">Graduate Student</option>
              </select>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="isSavingDesignation">
              <span v-if="isSavingDesignation" class="material-symbols-outlined animate-spin text-sm mr-2">refresh</span>
              {{ isSavingDesignation ? 'Saving...' : 'Save Designation' }}
            </button>
          </div>
          <p v-if="designationSuccess" class="success-msg">{{ designationSuccess }}</p>
          <p v-if="designationError" class="error-msg">{{ designationError }}</p>
        </form>
      </div>

      <!-- 3. SECURITY & ACCOUNT -->
      <div class="settings-card">
        <div class="card-header">
          <span class="material-symbols-outlined text-pink-400">lock</span>
          <h2 class="card-title">Security & Account</h2>
        </div>
        
        <!-- Email Section -->
        <form @submit.prevent="updateEmail" class="form-group mb-8 pb-8 border-b border-slate-700/50">
          <div class="input-wrapper mb-2">
            <label class="input-label">Current Email</label>
            <div class="text-white font-medium bg-slate-800/50 p-3 rounded-lg border border-slate-700/50">{{ user.email || 'Loading...' }}</div>
          </div>
          
          <div class="input-wrapper">
            <label class="input-label">New Email Address</label>
            <input 
              type="email" 
              v-model="emailForm.email" 
              class="custom-input" 
              required
              placeholder="Enter your new email"
            />
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="isUpdatingEmail">
              <span v-if="isUpdatingEmail" class="material-symbols-outlined animate-spin text-sm mr-2">refresh</span>
              {{ isUpdatingEmail ? 'Updating...' : 'Update Email' }}
            </button>
          </div>
          <p v-if="emailSuccess" class="success-msg">{{ emailSuccess }}</p>
          <p v-if="emailError" class="error-msg">{{ emailError }}</p>
        </form>

        <!-- Password Section -->
        <form @submit.prevent="updatePassword" class="form-group">
          <div class="input-wrapper">
            <label class="input-label">Current Password</label>
            <input 
              type="password" 
              v-model="passwordForm.currentPassword" 
              class="custom-input" 
              required
              placeholder="Enter current password"
            />
          </div>
          <div class="input-wrapper">
            <label class="input-label">New Password</label>
            <input 
              type="password" 
              v-model="passwordForm.newPassword" 
              class="custom-input" 
              required
              placeholder="Enter new password (min. 6 characters)"
            />
          </div>
          <div class="input-wrapper">
            <label class="input-label">Confirm New Password</label>
            <input 
              type="password" 
              v-model="passwordForm.confirmPassword" 
              class="custom-input" 
              required
              placeholder="Confirm new password"
            />
          </div>
          
          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="isUpdatingPassword">
              <span v-if="isUpdatingPassword" class="material-symbols-outlined animate-spin text-sm mr-2">refresh</span>
              {{ isUpdatingPassword ? 'Updating...' : 'Update Password' }}
            </button>
          </div>
          <p v-if="passwordSuccess" class="success-msg">{{ passwordSuccess }}</p>
          <p v-if="passwordError" class="error-msg">{{ passwordError }}</p>
        </form>
      </div>

      <!-- 4. DATA RETENTION POLICIES (Admin/Staff Only) -->
      <div v-if="isAdminOrStaff" class="settings-card">
        <div class="card-header">
          <span class="material-symbols-outlined text-amber-400">schedule</span>
          <h2 class="card-title">System Data Retention Policies</h2>
        </div>
        
        <form @submit.prevent="updateRetentionSettings" class="form-group">
          <p class="text-sm text-slate-300 mb-2">Configure automated scheduled cleanup periods (in days) for temporary and archived records.</p>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="input-wrapper">
              <label class="input-label">Trash Bin (Days)</label>
              <input type="number" min="1" max="365" v-model.number="retentionForm.trash_ttl_days" class="custom-input" required />
            </div>
            <div class="input-wrapper">
              <label class="input-label">Messages (Days)</label>
              <input type="number" min="1" max="1825" v-model.number="retentionForm.messages_ttl_days" class="custom-input" required />
            </div>
            <div class="input-wrapper">
              <label class="input-label">Activity Logs (Days)</label>
              <input type="number" min="1" max="1825" v-model.number="retentionForm.activity_logs_ttl_days" class="custom-input" required />
            </div>
            <div class="input-wrapper">
              <label class="input-label">Archived Documents (Days)</label>
              <input type="number" min="30" max="3650" v-model.number="retentionForm.archived_documents_ttl_days" class="custom-input" required />
            </div>
          </div>
          
          <div class="form-actions mt-4">
            <button type="submit" class="btn-primary" :disabled="isUpdatingRetention">
              <span v-if="isUpdatingRetention" class="material-symbols-outlined animate-spin text-sm mr-2">refresh</span>
              {{ isUpdatingRetention ? 'Saving...' : 'Save Retention Policies' }}
            </button>
          </div>
          <p v-if="retentionSuccess" class="success-msg">{{ retentionSuccess }}</p>
          <p v-if="retentionError" class="error-msg">{{ retentionError }}</p>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../api';
import Swal from 'sweetalert2';

const user = ref({});

const isAdminOrStaff = computed(() => {
  const role = user.value.role ? user.value.role.toLowerCase() : '';
  return role === 'admin' || role === 'gad_staff' || role === 'superadmin' || role === 'director';
});

// Personal Form State
const personalForm = ref({
  first_name: '',
  middle_name: '',
  last_name: '',
  sex: ''
});
const isSavingPersonal = ref(false);
const personalSuccess = ref('');
const personalError = ref('');

// Designation Form State
const designationForm = ref({
  campus_location: 'La Trinidad Campus',
  office_id: '',
  department: '',
  position: '',
  student_id: '',
  year_level: '',
  new_office_name: ''
});
const isSavingDesignation = ref(false);
const designationSuccess = ref('');
const designationError = ref('');

// Offices & Campus Affiliation
const officeUnits = ref([]);
const fetchOffices = async () => {
  try {
    const res = await api.get('office_units');
    officeUnits.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
  } catch (err) {
    console.error("Fetch offices error:", err);
  }
};

const officesForSelectedCampus = computed(() => {
  if (!designationForm.value.campus_location) return officeUnits.value;
  return officeUnits.value.filter(u => !u.location || u.location === designationForm.value.campus_location);
});

const handleCampusChange = () => {
  const currentOfficeBelongs = officesForSelectedCampus.value.some(
    u => String(u.unit_id) === String(designationForm.value.office_id)
  );
  if (!currentOfficeBelongs && officesForSelectedCampus.value.length > 0) {
    designationForm.value.office_id = officesForSelectedCampus.value[0].unit_id;
  }
};

// Avatar Upload
const avatarPreview = ref('');

// Email & Password State
const emailForm = ref({ email: '' });
const isUpdatingEmail = ref(false);
const emailSuccess = ref('');
const emailError = ref('');

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});
const isUpdatingPassword = ref(false);
const passwordSuccess = ref('');
const passwordError = ref('');

// Retention State
const retentionForm = ref({
  trash_ttl_days: 30,
  messages_ttl_days: 365,
  activity_logs_ttl_days: 365,
  operational_logs_ttl_days: 90,
  archived_documents_ttl_days: 1825,
  drafts_ttl_days: 365
});
const isUpdatingRetention = ref(false);
const retentionSuccess = ref('');
const retentionError = ref('');

const computedFullName = computed(() => {
  return [personalForm.value.first_name, personalForm.value.middle_name, personalForm.value.last_name]
    .filter(Boolean)
    .join(' ')
    .trim() || user.value.full_name || 'N/A';
});

const userInitials = computed(() => {
  const name = user.value.full_name || user.value.username || 'U';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
});

const getAvatarUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api/', '/') || 'http://localhost:8080/';
  return `${baseUrl.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
};

const handleAvatarSelected = async (e) => {
  const file = e.target.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) {
    Swal.fire({ icon: 'error', title: 'File Too Large', text: 'Profile picture must be under 2MB.' });
    return;
  }

  // Preview locally
  avatarPreview.value = URL.createObjectURL(file);

  const formData = new FormData();
  formData.append('profile_picture', file);

  try {
    const res = await api.post('/users/profile/update', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (res.data.success) {
      user.value.profile_picture = res.data.avatar_url;
      const stored = JSON.parse(localStorage.getItem('user') || '{}');
      stored.profile_picture = res.data.avatar_url;
      localStorage.setItem('user', JSON.stringify(stored));
      window.dispatchEvent(new CustomEvent('user-updated', { detail: stored }));

      Swal.fire({ icon: 'success', title: 'Updated', text: 'Profile picture updated successfully!' });
    }
  } catch (err) {
    console.error('Avatar upload failed:', err);
    Swal.fire({ icon: 'error', title: 'Upload Failed', text: 'Failed to upload profile picture.' });
  }
};

const fetchProfile = async () => {
  try {
    const res = await api.get('/users/profile');
    if (res.data.success) {
      const u = res.data.user;
      user.value = { ...user.value, ...u };

      personalForm.value = {
        first_name: u.first_name || '',
        middle_name: u.middle_name || '',
        last_name: u.last_name || '',
        sex: u.sex || ''
      };

      designationForm.value = {
        campus_location: u.location || 'La Trinidad Campus',
        office_id: u.office_id || '',
        department: u.department || '',
        position: u.position || '',
        student_id: u.student_id || '',
        year_level: u.year_level || '',
        new_office_name: ''
      };

      emailForm.value.email = u.email || '';

      const storedUser = JSON.parse(localStorage.getItem('user') || '{}');
      const updatedUser = { ...storedUser, ...u };
      localStorage.setItem('user', JSON.stringify(updatedUser));
      window.dispatchEvent(new CustomEvent('user-updated', { detail: updatedUser }));
    }
  } catch (error) {
    console.error("Failed to fetch profile", error);
  }
};

onMounted(async () => {
  const storedUser = JSON.parse(localStorage.getItem('user') || '{}');
  user.value = storedUser;
  
  await fetchOffices();
  await fetchProfile();

  if (isAdminOrStaff.value) {
    try {
      const res = await api.get('/settings/system');
      if (res.data) {
        retentionForm.value = {
          trash_ttl_days: res.data.trash_ttl_days ?? 30,
          messages_ttl_days: res.data.messages_ttl_days ?? 365,
          activity_logs_ttl_days: res.data.activity_logs_ttl_days ?? 365,
          operational_logs_ttl_days: res.data.operational_logs_ttl_days ?? 90,
          archived_documents_ttl_days: res.data.archived_documents_ttl_days ?? 1825,
          drafts_ttl_days: res.data.drafts_ttl_days ?? 365
        };
      }
    } catch (error) {
      console.error("Failed to fetch system settings", error);
    }
  }
});

const savePersonalInfo = async () => {
  isSavingPersonal.value = true;
  personalSuccess.value = '';
  personalError.value = '';

  try {
    const res = await api.post('/users/profile/update', personalForm.value);
    if (res.data.success) {
      personalSuccess.value = 'Personal information saved successfully.';
      await fetchProfile();
    } else {
      personalError.value = res.data.message || 'Failed to save personal info.';
    }
  } catch (err) {
    personalError.value = err.response?.data?.message || 'Error saving personal info.';
  } finally {
    isSavingPersonal.value = false;
  }
};

const saveDesignation = async () => {
  isSavingDesignation.value = true;
  designationSuccess.value = '';
  designationError.value = '';

  try {
    const payload = { ...designationForm.value };
    if (payload.office_id === 'add_new') {
      if (!payload.new_office_name || !payload.new_office_name.trim()) {
        designationError.value = 'Please enter the new college or office name.';
        isSavingDesignation.value = false;
        return;
      }
    }
    const res = await api.post('/users/profile/update', payload);
    if (res.data.success) {
      designationSuccess.value = 'Designation and office affiliation saved successfully.';
      await fetchOffices();
      await fetchProfile();
    } else {
      designationError.value = res.data.message || 'Failed to save designation.';
    }
  } catch (err) {
    designationError.value = err.response?.data?.message || 'Error saving designation.';
  } finally {
    isSavingDesignation.value = false;
  }
};

const updateEmail = async () => {
  if (emailForm.value.email === user.value.email) {
    emailError.value = 'New email is the same as the current email.';
    return;
  }

  const result = await Swal.fire({
    title: 'Are you sure?',
    text: "Do you want to update your email address?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#9333ea',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, update it!'
  });

  if (!result.isConfirmed) return;

  isUpdatingEmail.value = true;
  emailSuccess.value = '';
  emailError.value = '';
  
  try {
    const res = await api.post('/users/profile/update', { email: emailForm.value.email });
    if (res.data.success) {
      emailSuccess.value = 'Email updated successfully.';
      const storedUser = JSON.parse(localStorage.getItem('user') || '{}');
      storedUser.email = emailForm.value.email;
      localStorage.setItem('user', JSON.stringify(storedUser));
      user.value.email = emailForm.value.email;
    } else {
      emailError.value = res.data.message || 'Failed to update email.';
    }
  } catch (err) {
    emailError.value = err.response?.data?.message || 'An error occurred while updating email.';
  } finally {
    isUpdatingEmail.value = false;
  }
};

const updatePassword = async () => {
  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    passwordError.value = 'New passwords do not match.';
    return;
  }

  const result = await Swal.fire({
    title: 'Are you sure?',
    text: "Do you want to update your password?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#9333ea',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, update it!'
  });

  if (!result.isConfirmed) return;

  isUpdatingPassword.value = true;
  passwordSuccess.value = '';
  passwordError.value = '';
  
  try {
    const res = await api.post('/users/profile/update', { 
      current_password: passwordForm.value.currentPassword,
      new_password: passwordForm.value.newPassword 
    });
    if (res.data.success) {
      passwordSuccess.value = 'Password updated successfully.';
      passwordForm.value = { currentPassword: '', newPassword: '', confirmPassword: '' };
    } else {
      passwordError.value = res.data.message || 'Failed to update password.';
    }
  } catch (err) {
    passwordError.value = err.response?.data?.message || 'An error occurred while updating password.';
  } finally {
    isUpdatingPassword.value = false;
  }
};

const updateRetentionSettings = async () => {
  isUpdatingRetention.value = true;
  retentionSuccess.value = '';
  retentionError.value = '';
  
  try {
    const res = await api.post('/settings/system', retentionForm.value);
    if (res.status === 200 || res.status === 201 || (res.data && res.data.message)) {
      retentionSuccess.value = 'Data retention policies updated successfully.';
      try {
        api.post('/settings/trigger-cleanup').catch(e => console.log('Silent background cleanup error', e));
      } catch (e) {}
    } else {
      retentionError.value = 'Failed to update retention policies.';
    }
  } catch (err) {
    retentionError.value = err.response?.data?.message || 'An error occurred while updating policies.';
  } finally {
    isUpdatingRetention.value = false;
  }
};
</script>

<style scoped>
.settings-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-bottom: 2rem;
}

.settings-header {
  width: 100%;
  max-width: 800px;
  background: linear-gradient(135deg, #2e1065, #1e1b4b);
  padding: 2rem;
  border-radius: 1.25rem;
  border: 1px solid rgba(168, 85, 247, 0.2);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(168, 85, 247, 0.1);
  position: relative;
  overflow: hidden;
}

.settings-header::before {
  content: '';
  position: absolute;
  top: -50px;
  right: -50px;
  width: 150px;
  height: 150px;
  background: rgba(168, 85, 247, 0.1);
  border-radius: 50%;
  filter: blur(20px);
  pointer-events: none;
}

.settings-content {
  width: 100%;
  max-width: 800px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

.settings-card {
  border-radius: 1rem;
  border: 1px solid rgba(147, 51, 234, 0.15);
  background: linear-gradient(135deg, #0f172a, #020617);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  padding: 2rem;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding-bottom: 1rem;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #f8fafc;
  margin: 0;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.input-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.input-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #cbd5e1;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.custom-input {
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(147, 51, 234, 0.2);
  color: white;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.2s ease;
  outline: none;
}

.custom-input:focus {
  border-color: #c084fc;
  box-shadow: 0 0 0 2px rgba(192, 132, 252, 0.2);
}

.form-actions {
  margin-top: 0.5rem;
}

.btn-primary {
  background: linear-gradient(135deg, #9333ea, #c084fc);
  color: white;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s ease, transform 0.1s ease;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.success-msg {
  color: #34d399;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

.error-msg {
  color: #f87171;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}
</style>

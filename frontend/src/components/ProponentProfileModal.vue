<template>
  <div v-if="isOpen" class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-container">
      
      <!-- Modal Header -->
      <div class="modal-header">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-purple-400">account_circle</span>
          <h3 class="text-lg font-bold text-white">Proponent Profile</h3>
        </div>
        <button type="button" @click="$emit('close')" class="close-btn" title="Close">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="py-12 flex flex-col items-center justify-center gap-3">
        <span class="material-symbols-outlined text-purple-400 text-3xl animate-spin">refresh</span>
        <span class="text-sm text-purple-200">Loading proponent information...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="py-8 px-4 text-center">
        <span class="material-symbols-outlined text-red-400 text-3xl mb-2">error</span>
        <p class="text-sm text-red-200">{{ error }}</p>
        <button @click="fetchData" class="mt-4 px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-semibold">Try Again</button>
      </div>

      <!-- Profile Content -->
      <div v-else-if="profile" class="modal-body">
        
        <!-- User Top Card -->
        <div class="profile-hero">
          <div class="avatar-box">
            <img v-if="profile.profile_picture" :src="getAvatarUrl(profile.profile_picture)" alt="Avatar" class="w-full h-full object-cover" />
            <span v-else>{{ initials }}</span>
          </div>

          <div class="hero-details">
            <h4 class="text-xl font-bold text-white flex items-center gap-2 flex-wrap">
              {{ profile.full_name || 'N/A' }}
            </h4>
            <div class="flex items-center gap-2 flex-wrap mt-1">
              <span class="badge-role">{{ profile.user_role || (profile.role === 'non-twg' ? 'Proponent' : 'TWG') }}</span>
              <span v-if="profile.position" class="badge-position">{{ profile.position }}</span>
            </div>
            <p v-if="profile.email" class="text-xs text-purple-200/80 mt-1 flex items-center gap-1">
              <span class="material-symbols-outlined text-xs">mail</span>
              <a :href="'mailto:' + profile.email" class="hover:underline text-purple-300">{{ profile.email }}</a>
            </p>
          </div>
        </div>

        <!-- Detail Grids -->
        <div class="details-grid">
          
          <div class="detail-item">
            <span class="detail-label">Campus Location</span>
            <span class="detail-val flex items-center gap-1">
              <span class="material-symbols-outlined text-xs text-purple-400">location_on</span>
              {{ profile.location || 'La Trinidad Campus' }}
            </span>
          </div>

          <div class="detail-item">
            <span class="detail-label">College / Office</span>
            <span class="detail-val flex items-center gap-1">
              <span class="material-symbols-outlined text-xs text-blue-400">business</span>
              {{ profile.office_name || 'N/A' }}
              <span v-if="profile.office_acronym" class="text-purple-300 font-mono text-xs">({{ profile.office_acronym }})</span>
            </span>
          </div>

          <div class="detail-item" v-if="profile.department">
            <span class="detail-label">Department</span>
            <span class="detail-val">{{ profile.department }}</span>
          </div>

          <div class="detail-item" v-if="profile.student_id">
            <span class="detail-label">Student ID / ID Number</span>
            <span class="detail-val font-mono">{{ profile.student_id }}</span>
          </div>

          <div class="detail-item" v-if="profile.year_level">
            <span class="detail-label">Year Level</span>
            <span class="detail-val">{{ profile.year_level }}</span>
          </div>

          <div class="detail-item" v-if="profile.sex && profile.sex !== 'Not specified'">
            <span class="detail-label">Sex</span>
            <span class="detail-val">{{ profile.sex }}</span>
          </div>

        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" @click="$emit('close')" class="close-action-btn">Close</button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import api from '../api';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  userId: {
    type: [Number, String],
    default: null
  }
});

defineEmits(['close']);

const profile = ref(null);
const loading = ref(false);
const error = ref('');

const initials = computed(() => {
  if (!profile.value?.full_name) return 'U';
  return profile.value.full_name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
});

const getAvatarUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api/', '/') || 'http://localhost:8080/';
  return `${baseUrl.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
};

const fetchData = async () => {
  if (!props.userId) return;
  loading.value = true;
  error.value = '';
  try {
    const res = await api.get(`users/profile/${props.userId}`);
    if (res.data?.success) {
      profile.value = res.data.data;
    } else {
      error.value = res.data?.message || 'Proponent profile not found.';
    }
  } catch (err) {
    console.error('Failed to fetch proponent profile:', err);
    error.value = err.response?.data?.message || err.message || 'Unable to load profile.';
  } finally {
    loading.value = false;
  }
};

watch(() => props.isOpen, (newVal) => {
  if (newVal && props.userId) {
    fetchData();
  } else if (!newVal) {
    profile.value = null;
  }
});

watch(() => props.userId, (newVal) => {
  if (props.isOpen && newVal) {
    fetchData();
  }
});
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background-color: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  animation: fadeIn 0.2s ease-out;
}

.modal-container {
  width: 100%;
  max-width: 520px;
  background: linear-gradient(135deg, #131021 0%, #0d0b17 100%);
  border: 1px solid rgba(168, 85, 247, 0.25);
  border-radius: 1.25rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 30px rgba(168, 85, 247, 0.15);
  overflow: hidden;
  animation: scaleUp 0.2s ease-out;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.02);
}

.close-btn {
  color: #94a3b8;
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
  border-radius: 0.5rem;
  transition: all 0.2s;
}

.close-btn:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.1);
}

.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.profile-hero {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  background: rgba(255, 255, 255, 0.03);
  padding: 1.25rem;
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.avatar-box {
  width: 4.5rem;
  height: 4.5rem;
  border-radius: 1rem;
  background: linear-gradient(135deg, #6b21a8, #3b0764);
  border: 2px solid rgba(192, 132, 252, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 800;
  font-size: 1.5rem;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

.hero-details {
  flex: 1;
  min-width: 0;
}

.badge-role {
  background: rgba(168, 85, 247, 0.2);
  color: #d8b4fe;
  border: 1px solid rgba(168, 85, 247, 0.3);
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 0.375rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-position {
  background: rgba(59, 130, 246, 0.2);
  color: #93c5fd;
  border: 1px solid rgba(59, 130, 246, 0.3);
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  border-radius: 0.375rem;
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.875rem;
}

@media (max-width: 480px) {
  .details-grid {
    grid-template-columns: 1fr;
  }
}

.detail-item {
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8;
}

.detail-val {
  font-size: 0.9rem;
  font-weight: 600;
  color: #f1f5f9;
  word-break: break-word;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: flex-end;
  background: rgba(255, 255, 255, 0.01);
}

.close-action-btn {
  padding: 0.5rem 1.25rem;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #e2e8f0;
  font-weight: 600;
  font-size: 0.875rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.close-action-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scaleUp {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>

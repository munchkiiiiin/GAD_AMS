<template>
      <main class="twg-view-wrapper">
        <div class="max-w-3xl mx-auto py-10 px-6 w-full">
          <div class="mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight form-main-title">Submit Activity Design</h1>
            <p class="text-xs text-slate-400 mt-1.5">Fill out the activity design form below. All fields marked with * are required.</p>
          </div>

          <div class="form-container-box">
            <form @submit.prevent="submitActivityDesign" class="space-y-6">
              
              <div class="space-y-2">
                <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Nature of Transaction *</label>
                <select 
                  v-model="form.nature" 
                  required 
                  class="custom-input-field select-arrow-fix"
                >
                  <option value="" disabled class="dark-option">Select transaction type...</option>
                  <option value="inset" class="dark-option">INSET Training</option>
                  <option value="extension" class="dark-option">Extension Program</option>
                  <option value="employee" class="dark-option">Employee Training</option>
                </select>
              </div>

              <div class="space-y-2">
                <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Activity Title *</label>
                <textarea 
                  v-model="form.activity_title" 
                  required 
                  rows="3" 
                  class="custom-input-field resize-none"
                  placeholder="Enter the complete title of the activity"
                ></textarea>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Start Date of Implementation *</label>
                  <input 
                    type="date" 
                    v-model="form.start_date" 
                    required 
                    class="custom-input-field code-icon-calendar"
                  >
                </div>
                <div class="space-y-2">
                  <label class="block text-11px font-bold uppercase tracking-wider label-highlight">End Date of Implementation *</label>
                  <input 
                    type="date" 
                    v-model="form.end_date" 
                    required 
                    class="custom-input-field code-icon-calendar"
                  >
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Start Time *</label>
                  <input 
                    type="time" 
                    v-model="form.start_time" 
                    required 
                    class="custom-input-field code-icon-clock"
                  >
                </div>
                <div class="space-y-2">
                  <label class="block text-11px font-bold uppercase tracking-wider label-highlight">End Time *</label>
                  <input 
                    type="time" 
                    v-model="form.end_time" 
                    required 
                    class="custom-input-field code-icon-clock"
                  >
                </div>
              </div>

              <div class="space-y-2">
                <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Venue *</label>
                <input 
                  type="text" 
                  v-model="form.venue" 
                  required 
                  class="custom-input-field"
                  placeholder="e.g., Convention Center, Main Hall"
                >
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Target Participants *</label>
                  <input 
                    type="number" 
                    v-model="form.target_participants" 
                    required 
                    class="custom-input-field"
                    placeholder="Enter total participants"
                    min="0"
                  >
                </div>
                <div class="space-y-2">
                  <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Proposed Budget (PHP) *</label>
                  <input 
                    type="number" 
                    v-model="form.proposed_budget" 
                    step="0.01" 
                    required 
                    class="custom-input-field" 
                    placeholder="0.00"
                    min="0"
                  >
                </div>
              </div>

              <div class="space-y-3">
                <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Upload Activity Design (PDF) *</label>
                <div 
                  class="upload-dropzone-box group"
                  @click="$refs.fileInput.click()"
                >
                  <input 
                    ref="fileInput"
                    type="file" 
                    @change="handleFileUpload"
                    accept=".pdf" 
                    required 
                    class="hidden"
                  >
                  <span class="text-3xl mb-2 group-hover:scale-110 transition-transform duration-200">📤</span>
                  <p class="text-xs font-semibold text-white group-hover:text-[#b979cc] transition-colors">Upload Activity Design Document</p>
                  <p class="text-10px text-slate-400 mt-1">PDF format allowed (Max 10MB)</p>
                  
                  <div v-if="designFile" class="mt-4 w-full" @click.stop>
                    <div class="uploaded-file-tag">
                      <span class="truncate">📄 {{ designFile.name }}</span>
                      <button type="button" @click="removeFile" class="text-rose-400 font-bold hover:text-rose-500 text-xs ml-2 flex-shrink-0">Remove</button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-between items-center pt-6">
                <button 
                  type="button"
                  @click="goBack" 
                  class="px-6 py-3 text-11px font-bold uppercase tracking-widest label-highlight hover:bg-white/5 rounded-xl transition-all"
                >
                  ← Back
                </button>
                <button 
                  type="submit" 
                  class="submit-action-btn"
                >
                  Submit Design →
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import api from '../../api';

const router = useRouter();
const route = useRoute();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

const menuItems = computed(() => {
  if (route.path.includes('/college')) return collegeMenu;
  return [];
});

const form = ref({
  nature: '',
  activity_title: '',
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  venue: '',
  target_participants: '',
  proposed_budget: ''
});

const designFile = ref(null);
const fileInput = ref(null);

const handleFileUpload = (event) => {
  if (event.target.files.length > 0) {
    designFile.value = event.target.files[0];
  }
};

const removeFile = () => {
  designFile.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const submitActivityDesign = async () => {
  try {
    const formData = new FormData();
    Object.keys(form.value).forEach(key => {
      formData.append(key, form.value[key]);
    });
    if (designFile.value) {
      formData.append('design_file', designFile.value);
    }
    formData.append('user_id', user.value.id);

    const response = await api.post('submit-activity-design', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (response.data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Submitted Successfully!',
        text: 'Activity Design submitted successfully!',
        confirmButtonColor: '#b979cc'
      }).then(() => {
        router.push('/college/submitted-list');
      });
    }
  } catch (error) {
    console.error('Submission error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Submission Failed',
      text: 'Failed to submit activity design. Please double check all details.',
      confirmButtonColor: '#b979cc'
    });
  }
};

const goBack = () => {
  router.push('/college/submit');
};

const handleLogout = async () => {
  try {
    await api.get('logout');
    localStorage.removeItem('user');
    router.push('/login');
  } catch (err) {
    localStorage.removeItem('user');
    router.push('/login');
  }
};

onMounted(() => {
  if (!user.value.id || user.value.role !== 'college') {
    router.push('/login');
  }
});
</script>

<style scoped>
.twg-view-wrapper {
  flex: 1;
  overflow-y: auto;
  display: flex;
  background: transparent;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.text-11px { font-size: 11px; }
.text-10px { font-size: 10px; }
.text-3xl { font-size: 26px; }

.form-main-title {
  color: #16213e;
  letter-spacing: -0.02em;
}

.form-container-box {
  background: linear-gradient(145deg, #1a1a2e 0%, #16213e 100%);
  border: 1px solid rgba(185, 121, 204, 0.2);
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 20px 40px rgba(10, 10, 20, 0.4);
}

.label-highlight {
  color: #b979cc; 
  letter-spacing: 0.05em;
}

.custom-input-field {
  width: 100%;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 14px 20px;
  font-size: 14px;
  color: #ffffff;
  transition: all 0.2s ease;
}

.custom-input-field:focus {
  background: rgba(255, 255, 255, 0.05);
  border-color: #b979cc;
  outline: none;
  box-shadow: 0 0 0 2px rgba(153, 13, 209, 0.2);
}

.custom-input-field::placeholder {
  color: #64748b;
}

.dark-option {
  background-color: #16213e;
  color: #ffffff;
}

.code-icon-calendar::-webkit-calendar-picker-indicator,
.code-icon-clock::-webkit-calendar-picker-indicator {
  filter: invert(1);
  cursor: pointer;
  opacity: 0.7;
}

.code-icon-calendar::-webkit-calendar-picker-indicator:hover,
.code-icon-clock::-webkit-calendar-picker-indicator:hover {
  opacity: 1;
}

.upload-dropzone-box {
  border: 2px dashed rgba(185, 121, 204, 0.3);
  background: rgba(185, 121, 204, 0.02);
  border-radius: 14px;
  padding: 30px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.upload-dropzone-box:hover {
  border-color: #b979cc;
  background: rgba(185, 121, 204, 0.06);
}

.uploaded-file-tag {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 8px 14px;
  border-radius: 8px;
  color: #cbd5e1;
  font-size: 12px;
  width: 100%;
}

.submit-action-btn {
  background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%);
  color: #ffffff;
  padding: 14px 40px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  border: none;
  box-shadow: 0 4px 14px rgba(153, 13, 209, 0.3);
  transition: all 0.3s ease;
}

.submit-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(153, 13, 209, 0.45);
  background: linear-gradient(135deg, #b979cc 0%, #990dd1 100%);
}

.resize-none {
  resize: none;
}

.select-arrow-fix {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23b979cc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 20px center;
  background-size: 16px;
}
</style>
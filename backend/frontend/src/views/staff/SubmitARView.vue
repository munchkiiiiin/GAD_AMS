<template>
      <main class="twg-view-wrapper">
        <div class="max-w-3xl mx-auto py-10 px-6 w-full">
          <div class="mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight form-main-title">Submit Accomplishment Report</h1>
            <p class="text-xs text-slate-400 mt-1.5">Fill out the accomplishment report form below. All fields marked with * are required.</p>
          </div>

          <div class="form-container-box">
            <form @submit.prevent="submitReport" class="space-y-6">
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

              <div class="space-y-2">
                <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Activity Design Control Number *</label>
                <select 
                  v-model="form.control_number" 
                  required 
                  class="custom-input-field select-arrow-fix"
                >
                  <option value="" class="dark-option">Select approved activity design...</option>
                  <option v-for="control in approvedControls" :key="control.control_number" :value="control.control_number" class="dark-option">
                    {{ control.control_number }} - {{ control.activity_title }}
                  </option>
                </select>
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

              <div class="space-y-2">
                <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Number of Attendees *</label>
                <input 
                  type="number" 
                  v-model="form.attendees" 
                  required 
                  min="0"
                  class="custom-input-field cursor-not-allowed opacity-70"
                  placeholder="0"
                  readonly
                >
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Male Participants *</label>
                  <input 
                    type="number" 
                    v-model="form.male" 
                    required 
                    min="0"
                    class="custom-input-field"
                    placeholder="0"
                  >
                </div>
                <div class="space-y-2">
                  <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Female Participants *</label>
                  <input 
                    type="number" 
                    v-model="form.female" 
                    required 
                    min="0"
                    class="custom-input-field"
                    placeholder="0"
                  >
                </div>
              </div>

              <div class="space-y-2">
                <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Activity Rating (Percentage) *</label>
                <input 
                  type="number" 
                  v-model="form.rating" 
                  min="0" 
                  max="100" 
                  step="1" 
                  required 
                  class="custom-input-field"
                  placeholder="0-100%"
                >
                
                <div class="mt-4 rating-guide-panel">
                  <p class="text-11px font-bold text-slate-200 mb-2.5 tracking-wide uppercase">Rating Guide:</p>
                  <div class="space-y-2 text-10px line-height-relaxed text-slate-300">
                    <p><span class="font-bold text-emerald-400">81%-100% - Excellent:</span> Activity was completed successfully, highly engaging, impactful, and well-organized. Exceeded expectations.</p>
                    <p><span class="font-bold text-teal-400">61%-80% - Very Satisfactory:</span> Activity was completed with minor issues but was still productive, engaging, and met most objectives.</p>
                    <p><span class="font-bold text-cyan-400">41%-60% - Satisfactory:</span> Activity had some issues or lacked full engagement but met basic objectives.</p>
                    <p><span class="font-bold text-amber-400">21%-40% - Needs Improvement:</span> Activity faced significant challenges, lacked engagement or clear outcomes. Did not fully meet objectives.</p>
                    <p><span class="font-bold text-rose-400">1%-20% - Unsatisfactory:</span> Activity had major issues, minimal engagement, and failed to meet most objectives.</p>
                    <p><span class="font-bold text-rose-500">0% - Canceled:</span> Activity was canceled or did not occur at all.</p>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <label class="block text-11px font-bold uppercase tracking-wider label-highlight">Upload Documents (PDF/ZIP - Multiple files allowed) *</label>
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
                  <span class="text-3xl mb-2 group-hover:scale-110 transition-transform duration-200">upload</span>
                  <p class="text-xs font-semibold text-white group-hover:text-[#b979cc] transition-colors">Upload Accomplishment Report & Attachments</p>
                  <p class="text-10px text-slate-400 mt-1">Multiple files allowed (PDF, ZIP)</p>
                  
                  <div v-if="uploadedFiles.length > 0" class="mt-4 w-full space-y-1.5" @click.stop>
                    <div v-for="(file, index) in uploadedFiles" :key="index" class="uploaded-file-tag">
                      <span class="truncate">📄 {{ file.name }}</span>
                      <span class="text-10px opacity-60 flex-shrink-0">({{ (file.size / 1024).toFixed(2) }} KB)</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end pt-6">
                <button 
                  type="submit" 
                  class="submit-action-btn"
                >
                  Submit Report →
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import api from '../../api';

const router = useRouter();
const route = useRoute();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

const menuItems = computed(() => {
  if (route.path.includes('/staff')) return staffMenu;
  return [];
});

const form = ref({
  activity_title: '',
  control_number: '',
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  venue: '',
  attendees: '',
  male: '',
  female: '',
  rating: ''
});

const approvedControls = ref([]);

const fetchApprovedControls = async () => {
  try {
    const res = await api.get(`approved-controls/${user.value.id}`);
    if (res.data.success) {
      approvedControls.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching approved controls:', error);
  }
};

watch(() => form.value.control_number, (newVal) => {
  const selected = approvedControls.value.find(c => c.control_number === newVal);
  if (selected) {
    form.value.activity_title = selected.activity_title;
    form.value.start_date = selected.start_date;
    form.value.end_date = selected.end_date;
    form.value.start_time = selected.start_time;
    form.value.end_time = selected.end_time;
    form.value.venue = selected.venue;
  }
});

watch([() => form.value.male, () => form.value.female], ([newMale, newFemale]) => {
  const m = parseInt(newMale) || 0;
  const f = parseInt(newFemale) || 0;
  form.value.attendees = m + f;
});

const uploadedFiles = ref([]);
const fileInput = ref(null);

const handleFileUpload = (event) => {
  if (event.target.files.length > 0) {
    uploadedFiles.value = [event.target.files[0]];
  }
};

const submitReport = async () => {
  try {
    const formData = new FormData();
    
    Object.keys(form.value).forEach(key => {
      formData.append(key, form.value[key]);
    });
    
    uploadedFiles.value.forEach(file => {
      formData.append('attachment', file);
    });
    
    formData.append('user_id', user.value.id);
    
    const response = await api.post('submit-activity-report', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    if (response.data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Submitted Successfully!',
        text: 'Accomplishment report submitted successfully!',
        confirmButtonColor: '#b979cc'
      }).then(() => {
        router.push('/staff/submitted-list');
      });
      form.value = {
        activity_title: '',
        control_number: '',
        start_date: '',
        end_date: '',
        start_time: '',
        end_time: '',
        venue: '',
        attendees: '',
        male: '',
        female: '',
        rating: ''
      };
      uploadedFiles.value = [];
      if (fileInput.value) fileInput.value.value = '';
    }
  } catch (error) {
    console.error('Submission error:', error);
    alert('Failed to submit report. Please try again.');
  }
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
  if (!user.value.id) {
    router.push('/login');
  } else {
    fetchApprovedControls();
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

.rating-guide-panel {
  background: rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 18px;
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
</style>
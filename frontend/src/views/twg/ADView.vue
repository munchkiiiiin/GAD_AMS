<template>
  <main class="flex-1 overflow-y-auto bg-transparent">
    <div v-if="loading" class="flex justify-center items-center min-h-[400px]">
      <div class="loading-spinner"></div>
    </div>

    <div v-else-if="error" class="max-w-3xl mx-auto py-10 px-6">
      <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
        <p class="text-red-700 font-bold">Error Loading Data</p>
        <p class="text-red-600 text-sm">{{ error }}</p>
        <button @click="router.back()" class="mt-4 text-sm font-bold text-red-700 hover:underline">← Go Back</button>
      </div>
    </div>

    <div v-else class="p-10 main-flex max-w-7xl mx-auto">
      <section class="flex-06">
        <div class="bg-white rounded-xl flex flex-col overflow-hidden shadow-sm border">
          <div class="title-block">
            <div class="flex items-center gap-2 mb-4">
              <div class="status-badge" :class="getStatusClass(design.status)">
                <div class="status-dot"></div>
                <span class="status-text">{{ design.status }}</span>
              </div>
              <span class="control-number">{{ design.control || 'NO CONTROL NUMBER' }}</span>
            </div>
            <h2 class="report-title">{{ design.activity_title }}</h2>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Date Submitted</span>
                <span class="info-value">{{ design.date || '---' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Category</span>
                <span class="info-value">Activity Design</span>
              </div>
              <div class="info-item">
                <span class="info-label">Form Type</span>
                <span class="info-value uppercase">{{ design.form_type }}</span>
              </div>
            </div>
          </div>

          <div class="p-8 space-y-6">
            <div class="section-card">
              <div class="section-title">
                <span class="material-symbols-outlined">calendar_month</span>
                <h3>Schedule & Venue</h3>
              </div>
              <div class="grid-2">
                <div>
                  <label class="info-label">Implementation Period</label>
                  <p class="text-sm text-slate-800 font-medium mt-1">
                    {{ formatDate(design.start_date) }} — {{ formatDate(design.end_date) }}
                  </p>
                </div>
                <div>
                  <label class="info-label">Time</label>
                  <p class="text-sm text-slate-800 font-medium mt-1">
                    {{ formatTime(design.start_time) }} to {{ formatTime(design.end_time) }}
                  </p>
                </div>
                <div class="col-span-2 mt-4">
                  <label class="info-label">Venue</label>
                  <p class="text-sm text-slate-800 font-medium mt-1">{{ design.venue }}</p>
                </div>
              </div>
            </div>

            <div class="section-card">
              <div class="section-title">
                <span class="material-symbols-outlined">payments</span>
                <h3>Budget & Participants</h3>
              </div>
              <div class="grid-2">
                <div>
                  <label class="info-label">Total Proposed Budget</label>
                  <p class="text-xl font-bold text-slate-900">₱{{ formatCurrency(design.proposed_budget) }}</p>
                </div>
                <div>
                  <label class="info-label">Target Participants</label>
                  <p class="text-lg font-bold text-slate-900 mt-1">{{ design.target_participants }} participants</p>
                </div>
              </div>
            </div>

            <div v-if="design.attachment" class="section-card">
              <div class="section-title">
                <span class="material-symbols-outlined">description</span>
                <h3>Supporting Documents</h3>
              </div>
              <div class="doc-item">
                <div class="doc-info">
                  <span class="material-symbols-outlined text-red-500 text-3xl">picture_as_pdf</span>
                  <div>
                    <p class="doc-title">Activity Design Framework.pdf</p>
                    <p class="doc-meta">Reference: {{ design.attachment }}</p>
                  </div>
                </div>
                <div class="flex gap-2">
                  <button class="preview-btn flex items-center gap-1" @click="previewFile(design.attachment)">
                    <span class="material-symbols-outlined text-sm">visibility</span> Preview
                  </button>
                  <button class="preview-btn flex items-center gap-1 border-slate-300 text-slate-600 hover:bg-slate-100" @click="downloadFile(design.attachment)">
                    <span class="material-symbols-outlined text-sm">download</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="flex-04 sticky-sidebar">
        <div class="sidebar-card">
          <div class="flex items-center gap-3 mb-4">
            <div class="status-icon">
              <span class="material-symbols-outlined">verified_user</span>
            </div>
            <div>
              <p class="status-title">{{ design.status }}</p>
              <p class="status-date">Design Status</p>
            </div>
          </div>
          <div class="info-row">
            <label class="info-row-label">Control Number</label>
            <p class="info-row-value">{{ design.control || 'Pending' }}</p>
          </div>
        </div>

        <div class="sidebar-card">
          <h3 class="font-bold text-sm uppercase tracking-widest text-slate-800 mb-4">Submission Status</h3>
          <div class="history-container">
            <div class="history-item">
              <div class="history-dot history-dot-purple"></div>
              <p class="history-title">Submitted for Review</p>
              <p class="history-date">{{ design.date || '---' }}</p>
            </div>
            <div v-if="design.status === 'Approved'" class="history-item">
              <div class="history-dot history-dot-emerald"></div>
              <p class="history-title history-title-emerald">Final Approval</p>
              <p class="history-date">Approved by GAD Office</p>
            </div>
          </div>
        </div>

        <div class="info-card">
          <span class="material-symbols-outlined">info</span>
          <div>
            <p class="info-card-title">Read-Only View</p>
            <p class="info-card-text">This activity design is currently in the institutional record. You can only view the submitted details.</p>
          </div>
        </div>

        <button @click="router.back()" class="back-btn">← Back to Submissions</button>
      </section>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { apiUrl, backendUrl } from '../../http';

const route = useRoute();
const router = useRouter();
const design = ref({});
const loading = ref(true);
const error = ref(null);

const fetchDesignDetails = async () => {
  try {
    const id = route.params.id;
    const response = await axios.get(apiUrl(`/activity-design/${id}`));
    
    if (response.data.success) {
      const data = response.data.data;
      if (data) {
        design.value = data;
      } else {
        error.value = "Design not found.";
      }
    }
  } catch (err) {
    console.error('Error:', err);
    error.value = "Failed to retrieve activity design details.";
  } finally {
    loading.value = false;
  }
};

const formatDate = (date) => {
  if (!date) return '---';
  return new Date(date).toLocaleDateString('en-US', { 
    month: 'long', 
    day: 'numeric', 
    year: 'numeric' 
  });
};

const formatTime = (time) => {
  if (!time) return '---';
  const [hours, minutes] = time.split(':');
  const period = hours >= 12 ? 'PM' : 'AM';
  const h = hours % 12 || 12;
  return `${h}:${minutes} ${period}`;
};

const formatCurrency = (amount) => {
  if (!amount) return '0.00';
  return parseFloat(amount).toLocaleString(undefined, { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  });
};

const getStatusClass = (status) => {
  if (!status) return '';
  const s = status.toLowerCase();
  if (s === 'approved') return 'status-approved';
  if (s === 'pending') return 'status-pending';
  return 'status-revision';
};

const previewFile = (fileName) => {
  if (!fileName) return;
  window.open(backendUrl(`/uploads/${fileName}`), '_blank');
};

const downloadFile = (fileName) => {
  if (!fileName) return;
  const link = document.createElement('a');
  link.href = backendUrl(`/uploads/${fileName}`);
  link.download = fileName;
  link.click();
};

onMounted(() => {
  fetchDesignDetails();
});
</script>

<style scoped>
.main-flex {
  display: flex;
  gap: 32px;
}

.flex-06 { flex: 0.62; }
.flex-04 { flex: 0.38; }

.title-block {
  padding: 32px;
  border-bottom: 1px solid #e2e8f0;
  background-color: rgba(153, 13, 209, 0.02);
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: #f1f5f9;
  color: #475569;
  padding: 4px 12px;
  border-radius: 9999px;
}

.status-badge.status-approved {
  background-color: #d1fae5;
  color: #047857;
}

.status-badge.status-pending {
  background-color: #fef3c7;
  color: #d97706;
}

.status-dot {
  width: 8px;
  height: 8px;
  background-color: currentColor;
  border-radius: 9999px;
}

.status-text {
  font-size: 10px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.control-number {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-left: 12px;
  font-family: monospace;
}

.report-title {
  font-family: 'Times New Roman', serif;
  font-size: 28px;
  color: #1a1a2e;
  line-height: 1.25;
  margin-bottom: 16px;
  margin-top: 16px;
}

.info-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}

.info-item { display: flex; flex-direction: column; }

.info-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #64748b;
  font-weight: 700;
  margin-bottom: 4px;
}

.info-value {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
}

.section-card {
  background-color: #f8fafc;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  border: 1px solid #f1f5f9;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}

.section-title h3 {
  font-weight: 800;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #990dd1;
}

.grid-2 {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.doc-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  background-color: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.doc-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.doc-title {
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
}

.doc-meta {
  font-size: 11px;
  color: #94a3b8;
  margin-top: 2px;
}

.preview-btn {
  color: #990dd1;
  font-size: 13px;
  font-weight: 700;
  background: none;
  border: 1px solid #990dd1;
  cursor: pointer;
  padding: 6px 12px;
  border-radius: 8px;
  transition: all 0.2s;
}

.preview-btn:hover {
  background-color: #990dd1;
  color: white;
}

.sidebar-card {
  background-color: white;
  border-radius: 20px;
  padding: 28px;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
  margin-bottom: 20px;
}

.status-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background-color: rgba(153, 13, 209, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-icon span { color: #990dd1; }

.status-title {
  font-size: 14px;
  font-weight: 800;
  color: #1e293b;
}

.status-date {
  font-size: 11px;
  color: #64748b;
}

.info-row {
  margin-top: 16px;
  padding-top: 12px;
  border-top: 1px solid #f1f5f9;
}

.info-row-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
}

.info-row-value {
  font-size: 16px;
  font-family: monospace;
  font-weight: 700;
  color: #990dd1;
  margin-top: 4px;
}

.history-container {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.history-item {
  position: relative;
  padding-left: 28px;
}

.history-dot {
  position: absolute;
  left: 0;
  top: 2px;
  width: 12px;
  height: 12px;
  border-radius: 9999px;
}

.history-dot-purple { background-color: #990dd1; }
.history-dot-emerald { background-color: #059669; }

.history-title { font-size: 12px; font-weight: 700; color: #1e293b; }
.history-title-emerald { color: #059669; }
.history-date { font-size: 10px; color: #94a3b8; margin-top: 2px; }

.info-card {
  background-color: rgba(81, 112, 255, 0.1);
  padding: 16px;
  border-radius: 12px;
  border: 1px solid rgba(81, 112, 255, 0.2);
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
}

.info-card span { color: #5170ff; }
.info-card-title { font-size: 11px; font-weight: 800; color: #5170ff; text-transform: uppercase; }
.info-card-text { font-size: 11px; color: #1e293b; margin-top: 2px; }

.back-btn {
  display: block;
  width: 100%;
  padding: 14px;
  background-color: #f1f5f9;
  color: #475569;
  border-radius: 14px;
  font-weight: 800;
  font-size: 13px;
  text-align: center;
  transition: all 0.2s;
}

.back-btn:hover { background-color: #e2e8f0; color: #1e293b; }

.sticky-sidebar {
  position: sticky;
  top: 20px;
  align-self: flex-start;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #990dd1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

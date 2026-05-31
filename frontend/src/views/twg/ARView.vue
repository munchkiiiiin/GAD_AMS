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
      <!-- LEFT SECTION - Report Details -->
      <section class="flex-06">
        <div class="bg-white rounded-xl flex flex-col overflow-hidden shadow-sm border">
          <!-- Title Block -->
          <div class="title-block">
            <div class="flex items-center gap-2 mb-4">
              <div class="status-badge" :class="getStatusClass(report.status)">
                <div class="status-dot"></div>
                <span class="status-text">{{ report.status }}</span>
              </div>
              <span class="control-number">{{ report.control || 'NO CONTROL NUMBER' }}</span>
            </div>
            <h2 class="report-title">{{ report.activity_title }}</h2>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Date Submitted</span>
                <span class="info-value">{{ report.date || '---' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Category</span>
                <span class="info-value">Accomplishment Report</span>
              </div>
              <div class="info-item">
                <span class="info-label">Form Type</span>
                <span class="info-value uppercase">{{ report.formLabel || 'N/A' }}</span>
              </div>
            </div>
          </div>

          <div class="p-8 space-y-6">
            <!-- Schedule & Venue Section -->
            <div class="section-card">
              <div class="section-title">
                <span class="material-symbols-outlined">event_available</span>
                <h3>Execution Period & Venue</h3>
              </div>
              <div class="grid-2">
                <div>
                  <label class="info-label">Activity Date</label>
                  <p class="text-sm text-slate-800 font-medium mt-1">
                    {{ formatDate(report.start_date) }} — {{ formatDate(report.end_date) }}
                  </p>
                </div>
                <div>
                  <label class="info-label">Actual Time</label>
                  <p class="text-sm text-slate-800 font-medium mt-1">
                    {{ formatTime(report.start_time) }} to {{ formatTime(report.end_time) }}
                  </p>
                </div>
                <div class="col-span-2 mt-4">
                  <label class="info-label">Venue</label>
                  <p class="text-sm text-slate-800 font-medium mt-1">{{ report.venue }}</p>
                </div>
              </div>
            </div>

            <!-- Participation Section -->
            <div class="section-card">
              <div class="section-title">
                <span class="material-symbols-outlined">groups</span>
                <h3>Participation & Rating</h3>
              </div>
              <div class="grid-3">
                <div class="stats-box">
                  <label class="info-label">Total Attendees</label>
                  <p class="text-2xl font-bold text-slate-900">{{ report.attendees }}</p>
                </div>
                <div class="stats-box">
                  <label class="info-label">Male / Female</label>
                  <p class="text-lg font-bold text-blue-600">{{ report.male }} <span class="text-slate-300 mx-1">/</span> <span class="text-pink-500">{{ report.female }}</span></p>
                </div>
                <div class="stats-box">
                  <label class="info-label">Performance Rating</label>
                  <p class="text-2xl font-bold text-amber-500">{{ report.rating }}<span class="text-sm text-slate-400 font-normal"> / 5</span></p>
                </div>
              </div>
            </div>

            <!-- Uploaded Evidence Section -->
            <div v-if="report.attachment" class="section-card">
              <div class="section-title">
                <span class="material-symbols-outlined">attachment</span>
                <h3>Evidence of Completion</h3>
              </div>
              <div class="doc-item">
                <div class="doc-info">
                  <span class="material-symbols-outlined text-red-500 text-3xl">picture_as_pdf</span>
                  <div>
                    <p class="doc-title">Accomplishment_Report_File.pdf</p>
                    <p class="doc-meta">Evidence identifier: {{ report.attachment }}</p>
                  </div>
                </div>
                <div class="flex gap-2">
                  <button class="preview-btn flex items-center gap-1" @click="previewFile(report.attachment)">
                    <span class="material-symbols-outlined text-sm">visibility</span> Preview PDF
                  </button>
                  <button class="preview-btn flex items-center gap-1 border-slate-300 text-slate-600 hover:bg-slate-100" @click="downloadFile(report.attachment)">
                    <span class="material-symbols-outlined text-sm">download</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- RIGHT SECTION - Sidebar -->
      <section class="flex-04 sticky-sidebar">
        <div class="sidebar-card">
          <div class="flex items-center gap-3 mb-4">
            <div class="status-icon bg-blue-50">
              <span class="material-symbols-outlined text-blue-600">task_alt</span>
            </div>
            <div>
              <p class="status-title">{{ report.status }}</p>
              <p class="status-date">Verification Status</p>
            </div>
          </div>
          <div class="info-row">
            <label class="info-row-label">Associated Control</label>
            <p class="info-row-value">{{ report.control }}</p>
          </div>
        </div>

        <div class="sidebar-card">
          <h3 class="font-bold text-sm uppercase tracking-widest text-slate-800 mb-4">Submission Timeline</h3>
          <div class="history-container">
            <div class="history-item">
              <div class="history-dot history-dot-blue"></div>
              <p class="history-title">Report Filed</p>
              <p class="history-date">{{ report.date }}</p>
            </div>
            <div v-if="report.status === 'Verified'" class="history-item">
              <div class="history-dot history-dot-cyan"></div>
              <p class="history-title history-title-cyan">Verified</p>
              <p class="history-date">Confirmed by GAD Compliance</p>
            </div>
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
const report = ref({});
const loading = ref(true);
const error = ref(null);

const fetchReportDetails = async () => {
  try {
    const id = route.params.id;
    const response = await axios.get(apiUrl(`/activity-report/${id}`));
    
    if (response.data.success) {
      report.value = response.data.data;
    }
  } catch (err) {
    console.error('Error:', err);
    error.value = "Failed to retrieve accomplishment report details.";
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

const getStatusClass = (status) => {
  if (!status) return '';
  const s = status.toLowerCase();
  if (s === 'verified') return 'status-verified';
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
  fetchReportDetails();
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
  background-color: rgba(30, 41, 59, 0.02);
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

.status-badge.status-verified {
  background-color: #ecfeff;
  color: #0891b2;
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
  color: #0891b2;
}

.grid-2 {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.stats-box {
  background: white;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  text-align: center;
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
  color: #0891b2;
  font-size: 13px;
  font-weight: 700;
  background: none;
  border: 1px solid #0891b2;
  cursor: pointer;
  padding: 6px 12px;
  border-radius: 8px;
  transition: all 0.2s;
}

.preview-btn:hover {
  background-color: #0891b2;
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
  display: flex;
  align-items: center;
  justify-content: center;
}

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
  color: #0891b2;
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

.history-dot-blue { background-color: #3b82f6; }
.history-dot-cyan { background-color: #0891b2; }

.history-title { font-size: 12px; font-weight: 700; color: #1e293b; }
.history-title-cyan { color: #0891b2; }
.history-date { font-size: 10px; color: #94a3b8; margin-top: 2px; }

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
  border-top: 3px solid #0891b2;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

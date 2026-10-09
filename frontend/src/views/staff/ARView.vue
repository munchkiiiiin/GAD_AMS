<template>
  <div style="width: 100%;">
    <div style="width: 100%; min-height: 100vh;">
  <main class="main-viewport">
    <div v-if="loading" class="loading-wrapper">
      <div class="loading-spinner"></div>
    </div>

    <div v-else-if="error" class="error-container">
      <div class="error-box">
        <p class="error-title">Error Loading Data</p>
        <p class="error-message">{{ error }}</p>
        <button @click="router.back()" class="error-back-btn">← Go Back</button>
      </div>
    </div>

    <div v-else class="page-container">
      <div class="layout-vertical">
        <!-- LEFT SECTION -->
        <section class="flex-full glass-card">
          <div class="report-header">
            <div class="meta-header">
              <div style="display: flex; gap: 8px; align-items: center;">
                <div class="status-badge-view" :class="getStatusClass(report.status)">
                  <span class="status-text">{{ formatStatus(report.status) }}</span>
                </div>
                <div v-if="report.revision_count > 0" class="status-badge-view" style="background: rgba(234,179,8,0.1); border-color: rgba(234,179,8,0.2); padding: 4px 10px;">
                  <span class="status-text" style="color: #facc15; font-size: 11px; font-weight: bold;">Rev: {{ report.revision_count }}</span>
                </div>
              </div>
              <span class="control-number">{{ report.control || 'NO CONTROL NUMBER' }}</span>
            </div>

            <h2 class="report-title">{{ report.activity_title }}</h2>

            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Submitted By</span>
                <button 
                  type="button" 
                  @click="openProponentModal(report.user_id)" 
                  class="flex items-center gap-1.5 text-purple-300 hover:text-purple-200 hover:underline transition-colors cursor-pointer text-left font-semibold text-sm bg-transparent border-0 p-0"
                  title="Click to view proponent profile"
                >
                  <span>{{ report.submitter_name || 'N/A' }}</span>
                  <span class="material-symbols-outlined text-[15px] text-purple-400">account_circle</span>
                </button>
              </div>
              <div class="info-item">
                <span class="info-label">Office</span>
                <span class="info-value-white">{{ report.office }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Date Submitted</span>
                <span class="info-value-white">{{ report.date || '---' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Category</span>
                <span class="info-value-white">Accomplishment Report</span>
              </div>
            </div>
          </div>

          
          <div class="report-body">
            <div class="ar-horizontal-layout">
<!-- Approved Activity Design Details -->
            <div class="section-card" v-if="report.activity_design">
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">info</span>
                <h3 class="section-title">Approved Activity Design Details</h3>
              </div>
              <div class="grid-2">
                <div class="full-width-info" v-if="report.activity_design.submitter_name">
                  <label class="info-label">Submitted By</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.submitter_name }}</p>
                </div>
                <div class="full-width-info" v-if="report.activity_design.office">
                  <label class="info-label">Office / Unit</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.office }}</p>
                </div>
                <div>
                  <label class="info-label">Date Submitted</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.date ? formatDate(report.activity_design.date) : '---' }}</p>
                </div>

                <div class="full-width-info">
                  <label class="info-label">Control Number</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.control_number || 'N/A' }}</p>
                </div>
                <div class="full-width-info">
                  <label class="info-label">Title</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.activity_title }}</p>
                </div>
                <div class="full-width-info">
                  <label class="info-label">Activity Classification</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.activity_classification || '---' }}</p>
                </div>
                <div>
                  <label class="info-label">Form Type</label>
                  <p class="text-sm-light mt-1 uppercase">{{ report.activity_design.form_type_name || report.activity_design.form_type || '---' }}</p>
                </div>
                                <div class="full-width-info" v-if="report.activity_design">
                  <label class="info-label">Gender Issue / GAD Mandate</label>
                  <div v-if="report.activity_design.gad_mandate" class="mandate-boxes">
                    <span v-for="(mandate, index) in report.activity_design.gad_mandate.split(';;;')" :key="'m'+index" class="mandate-box">
                      {{ mandate.trim() }}
                    </span>
                  </div>
                  <p v-else class="text-sm-light mt-1">---</p>
                </div>
                <div class="full-width-info" v-if="report.activity_design">
                  <label class="info-label">Cause of Gender Issue</label>
                  <div v-if="report.activity_design.gender_issue" class="mandate-boxes">
                    <span v-for="(issue, index) in report.activity_design.gender_issue.split(';;;')" :key="'gi'+index" class="mandate-box">
                      {{ issue.trim() }}
                    </span>
                  </div>
                  <p v-else class="text-sm-light mt-1">---</p>
                </div>
                <div class="full-width-info">
                  <label class="info-label">Venue</label>
                  <div v-if="report.activity_design.venues_list && report.activity_design.venues_list.length > 0" class="flex flex-col gap-3 mt-2">
                    <div v-for="v in report.activity_design.venues_list" :key="v.venue_id" class="flex flex-col items-start pb-2 border-b border-white/5 last:border-0 last:pb-0">
                      <p class="text-sm-light !mb-1">{{ v.venue_name }}</p>
                      <span :class="v.is_inside_bsu == 1 || v.is_inside_bsu === true ? 'venue-badge inside-bsu' : 'venue-badge outside-bsu'">
                        {{ v.is_inside_bsu == 1 || v.is_inside_bsu === true ? '🏫 Inside BSU' : '🌐 Outside BSU' }}
                      </span>
                    </div>
                  </div>
                  <div v-else class="mt-2 pb-2">
                    <p class="text-sm-light !mb-1">{{ report.activity_design.venue_name || report.activity_design.venue }}</p>
                    <span :class="report.activity_design.is_inside_bsu == 1 || report.activity_design.is_inside_bsu === true ? 'venue-badge inside-bsu' : 'venue-badge outside-bsu'">
                      {{ report.activity_design.is_inside_bsu == 1 || report.activity_design.is_inside_bsu === true ? '🏫 Inside BSU' : '🌐 Outside BSU' }}
                    </span>
                  </div>
                </div>
                <div class="full-width-info" v-if="report.activity_design">
                  <label class="info-label">Overall Expected Attendance (Auto-calculated)</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.target_participants }}</p>
                </div>
                <div>
                  <label class="info-label">Calculated Start Date</label>
                  <p class="text-sm-light mt-1">{{ formatDate(report.activity_design.start_date) }}</p>
                </div>
                <div>
                  <label class="info-label">Calculated End Date</label>
                  <p class="text-sm-light mt-1">{{ formatDate(report.activity_design.end_date) }}</p>
                </div>
                <div class="full-width-info">
                  <label class="info-label">Full Schedule</label>
                  <details class="schedule-dropdown" v-if="report.activity_design.schedules && report.activity_design.schedules.length > 0">
                    <summary class="schedule-summary">View Full Schedule</summary>
                    <div class="mt-2">
                      <div v-for="(sch, i) in report.activity_design.schedules" :key="i" class="p-2 mb-2" style="background: rgba(255,255,255,0.05); border-radius: 4px; border: 1px solid rgba(255,255,255,0.1);">
                        <div class="text-sm-light">
                          <strong>{{ formatDate(sch.schedule_date) }}</strong>: {{ formatTime(sch.start_time) }} - {{ formatTime(sch.end_time) }}
                          <div class="mt-1" v-if="parseMeals(sch.meals_and_snacks).length > 0">
                            <span v-for="meal in parseMeals(sch.meals_and_snacks)" :key="meal" class="bbudget-selected-item" style="margin-right: 4px; display: inline-block; margin-top: 4px;">{{ meal }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </details>
                  <div class="mt-2 text-sm-light" v-else>
                    {{ formatTime(report.activity_design.start_time) }} - {{ formatTime(report.activity_design.end_time) }}
                  </div>
                </div>
                <div>
                  <label class="info-label">Proposed Budget</label>
                  <p class="text-sm-light mt-1">PHP {{ Number(aDBudget?.grand_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</p>
                </div>
                <div>
                  <label class="info-label">Assessment Date</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.assessment_date ? formatDate(report.activity_design.assessment_date) : 'N/A' }}</p>
                </div>
                <div>
                  <label class="info-label">Accomplishment Deadline</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.accomplishment_deadline ? formatDate(report.activity_design.accomplishment_deadline) : '---' }}</p>
                </div>
                <div class="full-width-info" v-if="report.activity_design.remarks">
                  <label class="info-label">Reviewer Remarks</label>
                  <div class="read-only-remarks mt-1">{{ report.activity_design.remarks }}</div>
                </div>
              </div>

              <!-- Approved Budget Breakdown -->
              <div class="full-width-info mt-4" v-if="report.activity_design && (report.activity_design.budget_items_raw || report.activity_design.budget_items)">
                <label class="info-label mb-2">Approved Budget Breakdown</label>
                <ActivityDesignBudget :design="report.activity_design" :report="report" />
              </div>

              <!-- AD Attachment -->
              <div v-if="report.activity_design && report.activity_design.attachment && parseAttachments(report.activity_design.attachment).length > 0" class="attachments-list mt-4" style="width:100%;">
                <label class="info-label mb-2">Approved Design Attachments</label>
                <div v-for="(file, index) in parseAttachments(report.activity_design.attachment)" :key="'ad-'+index" class="doc-item mb-2">
                  <div class="doc-info">
                    <span class="material-symbols-outlined doc-pdf-icon">picture_as_pdf</span>
                    <div>
                      <p class="doc-title">{{ file.split('_').slice(1).join('_') || file }}</p>
                      <p class="doc-meta">Reference: {{ file }}</p>
                    </div>
                  </div>
                  <div class="doc-actions">
                    <button @click="previewFile(file, 'archived')" class="preview-btn">Preview</button>
                    <button @click="downloadFile(file, 'archived', 'Activity_Design')" class="download-btn-icon">
                      <span class="material-symbols-outlined">download</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actual Accomplishment Details -->
            <div class="section-card">
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">fact_check</span>
                <h3 class="section-title">Actual Accomplishment Details</h3>
              </div>
              <div class="grid-2">
                <div class="full-width-info">
                  <label class="info-label">Submitted By</label>
                  <p class="text-sm-light mt-1">{{ report.submitter_name || report.user_name || 'N/A' }}</p>
                </div>
                <div class="full-width-info">
                  <label class="info-label">Office / Unit</label>
                  <p class="text-sm-light mt-1">{{ report.office || report.office_name || 'N/A' }}</p>
                </div>
                <div class="full-width-info">
                  <label class="info-label">Date Submitted</label>
                  <p class="text-sm-light mt-1">{{ report.created_at || report.date || 'N/A' }}</p>
                </div>

                <div class="full-width-info">
                  <label class="info-label">Control Number</label>
                  <p class="text-sm-light mt-1">{{ report.control_number || report.control || 'N/A' }}</p>
                </div>
                <div class="full-width-info">
                  <label class="info-label">Actual Activity Title</label>
                  <p class="text-sm-light mt-1">{{ report.activity_title }}</p>
                </div>
                <div class="full-width-info" v-if="report.activity_design">
                  <label class="info-label">Activity Classification</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.activity_classification || '---' }}</p>
                </div>
                <div class="full-width-info" v-if="report.activity_design">
                  <label class="info-label">Form Type</label>
                  <p class="text-sm-light mt-1 uppercase">{{ report.activity_design.form_type_name || report.activity_design.form_type || '---' }}</p>
                </div>
                                <div class="full-width-info" v-if="report.activity_design">
                  <label class="info-label">Gender Issue / GAD Mandate</label>
                  <div v-if="report.activity_design.gad_mandate" class="mandate-boxes">
                    <span v-for="(mandate, index) in report.activity_design.gad_mandate.split(';;;')" :key="'m'+index" class="mandate-box">
                      {{ mandate.trim() }}
                    </span>
                  </div>
                  <p v-else class="text-sm-light mt-1">---</p>
                </div>
                <div class="full-width-info" v-if="report.activity_design">
                  <label class="info-label">Cause of Gender Issue</label>
                  <div v-if="report.activity_design.gender_issue" class="mandate-boxes">
                    <span v-for="(issue, index) in report.activity_design.gender_issue.split(';;;')" :key="'gi'+index" class="mandate-box">
                      {{ issue.trim() }}
                    </span>
                  </div>
                  <p v-else class="text-sm-light mt-1">---</p>
                </div>
                <div>
                  <label class="info-label">Overall Expected Attendance (Auto-calculated)</label>
                  <p class="text-sm-light mt-1">{{ report.activity_design.target_participants }}</p>
                </div>
                <div>
                  <label class="info-label">Calculated Start Date</label>
                  <p class="text-sm-light mt-1">{{ formatDate(report.start_date) }}</p>
                </div>
                <div>
                  <label class="info-label">Calculated End Date</label>
                  <p class="text-sm-light mt-1">{{ formatDate(report.end_date) }}</p>
                </div>
                <div class="full-width-info">
                  <label class="info-label">Full Schedule</label>
                  <details class="schedule-dropdown" v-if="(report.schedules && report.schedules.length > 0) || (report.activity_design && report.activity_design.schedules && report.activity_design.schedules.length > 0)">
                    <summary class="schedule-summary">View Full Schedule</summary>
                    <div class="mt-2">
                      <div v-for="(sch, i) in (report.schedules && report.schedules.length > 0 ? report.schedules : report.activity_design.schedules)" :key="i" class="p-2 mb-2" style="background: rgba(255,255,255,0.05); border-radius: 4px; border: 1px solid rgba(255,255,255,0.1);">
                        <div class="text-sm-light">
                          <strong>{{ formatDate(sch.schedule_date) }}</strong>: {{ formatTime(sch.start_time) }} - {{ formatTime(sch.end_time) }}
                          <div class="mt-1" v-if="parseMeals(sch.meals_and_snacks).length > 0">
                            <span v-for="meal in parseMeals(sch.meals_and_snacks)" :key="meal" class="bbudget-selected-item" style="margin-right: 4px; display: inline-block; margin-top: 4px;">{{ meal }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </details>
                  <div class="mt-2 text-sm-light" v-else>
                    {{ formatTime(report.start_time) }} - {{ formatTime(report.end_time) }}
                  </div>
                </div>
                <div class="full-width-info">
                  <label class="info-label">Venue</label>
                  <div v-if="report.activity_design && report.activity_design.venues_list && report.activity_design.venues_list.length > 0" class="flex flex-col gap-3 mt-2">
                    <div v-for="v in report.activity_design.venues_list" :key="v.venue_id" class="flex flex-col items-start pb-2 border-b border-white/5 last:border-0 last:pb-0">
                      <p class="text-sm-light !mb-1">{{ v.venue_name }}</p>
                      <span :class="v.is_inside_bsu == 1 || v.is_inside_bsu === true ? 'venue-badge inside-bsu' : 'venue-badge outside-bsu'">
                        {{ v.is_inside_bsu == 1 || v.is_inside_bsu === true ? '🏫 Inside BSU' : '🌐 Outside BSU' }}
                      </span>
                    </div>
                  </div>
                  <div v-else class="mt-2 pb-2">
                    <p class="text-sm-light !mb-1">{{ report.venue }}</p>
                    <span :class="report.is_inside_bsu == 1 || report.is_inside_bsu === true ? 'venue-badge inside-bsu' : 'venue-badge outside-bsu'">
                      {{ report.is_inside_bsu == 1 || report.is_inside_bsu === true ? '🏫 Inside BSU' : '🌐 Outside BSU' }}
                    </span>
                  </div>
                </div>
                <div>
                  <label class="info-label">Number of Attendees</label>
                  <p class="text-sm-light mt-1">{{ report.attendees }}</p>
                </div>
                <div>
                  <label class="info-label">Male / Female Participants</label>
                  <p class="text-sm-light mt-1"><span class="male-val">{{ report.male }} Male</span> / <span class="female-val">{{ report.female }} Female</span></p>
                </div>
              </div>

              <!-- Actual Budget Expenditure -->
              <div class="full-width-info mt-4" v-if="report.budget_expenditures_raw && report.budget_expenditures_raw.length > 0">
                <label class="info-label mb-2">Actual Budget Expenditure</label>
                <ActivityDesignBudget :budget-items="report.budget_expenditures_raw" :design="report.activity_design" :report="report" />
              </div>

              <!-- Evaluation Results -->
              <div class="full-width-info mt-4" v-if="parsedAREval && parsedAREval.length > 0">
                <label class="info-label mb-2">Evaluation Results</label>
                <div class="table-responsive">
                  <table class="custom-table">
                    <thead>
                      <tr>
                        <th>Area of Evaluation</th>
                        <th class="text-center">Average Rating</th>
                        <th>Interpretation</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in parsedAREval" :key="index">
                        <td>{{ item.area }}</td>
                        <td class="text-center">{{ item.rating ? Number(item.rating).toFixed(2) : '-' }}</td>
                        <td>
                          <span :class="`interpretation-tag-ar ${getInterpretationClass(item.rating)}`">
                            {{ getInterpretation(item.rating) }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td class="font-bold text-white">Total Average Rating</td>
                        <td class="font-bold text-white text-center">{{ computedTotalRating ? Number(computedTotalRating).toFixed(2) : '-' }}</td>
                        <td class="font-bold text-white">
                          <span :class="`interpretation-tag-ar ${getInterpretationClass(computedTotalRating)}`">
                            {{ getInterpretation(computedTotalRating) }}
                          </span>
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

                            <!-- AR Attachment -->
              <div v-if="report.attachment && parseAttachments(report.attachment).length > 0" class="attachments-list mt-4" style="width:100%;">
                <label class="info-label mb-2">Accomplishment Report Attachments</label>
                <div v-for="(file, index) in parseAttachments(report.attachment)" :key="'ar-'+index" class="doc-item mb-2">
                  <div class="doc-info">
                    <span class="material-symbols-outlined doc-pdf-icon">picture_as_pdf</span>
                    <div>
                      <p class="doc-title">{{ file.split('_').slice(1).join('_') || file }}</p>
                      <p class="doc-meta">Reference: {{ file }}</p>
                    </div>
                  </div>
                  <div class="doc-actions">
                    <button @click="previewFile(file, Number(report.is_archived) === 1 ? 'archived' : 'drafts')" class="preview-btn">Preview</button>
                    <button @click="downloadFile(file, Number(report.is_archived) === 1 ? 'archived' : 'drafts', 'Accomplishment_Report')" class="download-btn-icon">
                      <span class="material-symbols-outlined">download</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

                    </div>
  </section>

        <!-- RIGHT SECTION - Assessment Sidebar -->
        <section class="flex-full">
          <div class="assessment-card-custom">
            <div class="assessment-header">
              <div class="assessment-icon">📋</div>
              <div class="assessment-title">Assessment Record</div>
            </div>

            <div class="assessment-form">
              <div class="info-item">
                <span class="info-label">Assessor Remarks</span>
                <div class="read-only-remarks">
                  {{ report.remarks || 'No remarks recorded for this accomplishment report.' }}
                </div>
              </div>

              <div class="action-buttons">
                <button @click="handleTrash" class="btn-trash">
                  <span class="material-symbols-outlined">delete</span> MOVE TO TRASH
                </button>
                <button @click="router.back()" class="btn-back">
                  ← Back
                </button>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- PDF Preview Modal -->
    <PdfPreviewModal :isOpen="isPdfModalOpen" :fileUrl="pdfFileUrl" @close="closePdfModal" />

    <!-- Proponent Profile Modal -->
    <ProponentProfileModal 
      :is-open="isProponentModalOpen" 
      :user-id="selectedProponentId" 
      @close="isProponentModalOpen = false" 
    />
  </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import Swal from 'sweetalert2';
import PdfPreviewModal from '../../components/PdfPreviewModal.vue';
import ProponentProfileModal from '../../components/ProponentProfileModal.vue';

const isProponentModalOpen = ref(false);
const selectedProponentId = ref(null);

const openProponentModal = (userId) => {
  if (!userId) return;
  selectedProponentId.value = userId;
  isProponentModalOpen.value = true;
};
import ActivityDesignBudget from '../../components/ActivityDesignBudget.vue';


const route = useRoute();
const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));
const report = ref({});
const loading = ref(true);
const error = ref(null);

const handleTrash = async () => {
  if (report.value.status !== 'Pending' || report.value.is_viewed_by_admin == 1) {
    Swal.fire({
      icon: 'error',
      title: 'Action Denied',
      text: 'You cannot move this document to trash because it is already being processed or has been viewed by an admin.',
      confirmButtonColor: '#3085d6'
    });
    return;
  }
  const result = await Swal.fire({
    title: 'Move to Trash?',
    text: 'This document will be moved to the trash bin.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#334155',
    confirmButtonText: 'Yes, move it'
  });

  if (result.isConfirmed) {
    try {
      const response = await api.delete(`accomplishment-reports/trash/${route.params.id}`);
      if (response.data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Moved to Trash',
          text: 'Document has been moved to trash.',
          timer: 1500,
          showConfirmButton: false
        });
        router.push(Number(report.value.is_archived) === 1 ? '/staff/archive' : '/staff/ar-list');
      } else {
        throw new Error(response.data.message || 'Failed to move to trash');
      }
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: err.message || 'An error occurred while moving to trash'
      });
    }
  }
};

const fetchReportDetails = async () => {
  loading.value = true;
  try {
    const id = route.params.id;
    const response = await api.get(`activity-report/${id}`);
    if (response.data.success) report.value = response.data.data;
    else error.value = "Accomplishment report not found.";
  } catch (err) {
    error.value = "Failed to load report data.";
  } finally {
    loading.value = false;
  }
};


const parseMeals = (str) => {
  if (!str) return [];
  try {
    let m = typeof str === 'string' ? JSON.parse(str) : str;
    if (Array.isArray(m)) return m;
    if (typeof m === 'object' && m !== null) {
      const selected = [];
      if (m.breakfast) selected.push('Breakfast');
      if (m.am_snack) selected.push('AM Snack');
      if (m.lunch) selected.push('Lunch');
      if (m.pm_snack) selected.push('PM Snack');
      if (m.dinner) selected.push('Dinner');
      return selected;
    }
    return [];
  } catch(e) {
    return [];
  }
};

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : '---';
const formatTime = (time) => {
  if (!time) return '---';
  const [h, m] = time.split(':');
  return `${h % 12 || 12}:${m} ${h >= 12 ? 'PM' : 'AM'}`;
};

const formatStatus = (status) => {
  if (!status) return 'Unknown';
  if (status.toLowerCase() === 'revision required') return 'For Revision';
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const getStatusClass = (status) => {
  const s = (status || '').toLowerCase();
  if (s === 'pending') return 'pending';
  if (s === 'approved') return 'approved';
  if (s === 'completed' || s === 'archived') return 'completed';
  if (s === 'cancelled') return 'cancelled';
  if (s === 'revision required' || s === 'revision') return 'revision';
  return 'completed';
};

const isPdfModalOpen = ref(false);
const pdfFileUrl = ref('');

const closePdfModal = () => {
  isPdfModalOpen.value = false;
};


const aDBudget = computed(() => {
  if (!report.value.activity_design || !report.value.activity_design.budget_items || report.value.activity_design.budget_items.length === 0) return null;
  const b = report.value.activity_design.budget_items[0];
  let ob = [];
  if (b.materials_others_breakdown) { try { ob = JSON.parse(b.materials_others_breakdown); } catch(e){} }
  const mealsT = Number(b.meals_total) || 0;
  const snacksT = Number(b.snacks_total) || 0;
  const combined = Number(b.meals_and_snacks) || 0;
  const othersTotal = Number(b.others_total) || ob.reduce((s, o) => s + Number(o.amount || 0), 0);
  const grandTotal = (mealsT === 0 && snacksT === 0 && combined > 0 ? combined : mealsT) + snacksT +
    Number(b.function_room_venue || 0) + Number(b.accommodation || 0) + Number(b.equipment_rental || 0) +
    Number(b.transportation || 0) + Number(b.professional_fee_honoria || 0) + Number(b.tokens || 0) +
    Number(b.materials_and_supplies || 0) + othersTotal;
  return {
    meals_total: (mealsT === 0 && snacksT === 0 && combined > 0) ? combined : mealsT,
    snacks_total: snacksT,
    breakfast_selected: b.breakfast_selected,
    lunch_selected: b.lunch_selected,
    dinner_selected: b.dinner_selected,
    am_snack_selected: b.am_snack_selected,
    pm_snack_selected: b.pm_snack_selected,
    function_room_venue: b.function_room_venue,
    accommodation: b.accommodation,
    equipment_rental: b.equipment_rental,
    transportation: b.transportation,
    professional_fee_honoria: b.professional_fee_honoria,
    tokens: b.tokens,
    pf_pax: b.pf_pax,
    tokens_pax: b.tokens_pax,
    materials_and_supplies: b.materials_and_supplies,
    others_total: othersTotal,
    othersBreakdown: ob,
    grand_total: grandTotal
  };
});

const aRBudget = computed(() => {
  if (!report.value.budget_items || report.value.budget_items.length === 0) return null;
  const b = report.value.budget_items[0];
  let ob = [];
  if (b.materials_others_breakdown) { try { ob = JSON.parse(b.materials_others_breakdown); } catch(e){} }
  const mealsT = Number(b.meals_total) || 0;
  const snacksT = Number(b.snacks_total) || 0;
  const combined = Number(b.meals_and_snacks) || 0;
  const othersTotal = Number(b.others_total) || ob.reduce((s, o) => s + Number(o.amount || 0), 0);
  const grandTotal = (mealsT === 0 && snacksT === 0 && combined > 0 ? combined : mealsT) + snacksT +
    Number(b.function_room_venue || 0) + Number(b.accommodation || 0) + Number(b.equipment_rental || 0) +
    Number(b.transportation || 0) + Number(b.professional_fee_honoria || 0) + Number(b.tokens || 0) +
    Number(b.materials_and_supplies || 0) + othersTotal;
  return {
    meals_total: (mealsT === 0 && snacksT === 0 && combined > 0) ? combined : mealsT,
    snacks_total: snacksT,
    breakfast_selected: b.breakfast_selected,
    lunch_selected: b.lunch_selected,
    dinner_selected: b.dinner_selected,
    am_snack_selected: b.am_snack_selected,
    pm_snack_selected: b.pm_snack_selected,
    function_room_venue: b.function_room_venue,
    accommodation: b.accommodation,
    equipment_rental: b.equipment_rental,
    transportation: b.transportation,
    professional_fee_honoria: b.professional_fee_honoria,
    tokens: b.tokens,
    pf_pax: b.pf_pax,
    tokens_pax: b.tokens_pax,
    materials_and_supplies: b.materials_and_supplies,
    others_total: othersTotal,
    othersBreakdown: ob,
    grand_total: grandTotal
  };
});

const parsedAREval = computed(() => {
  if (!report.value.evaluation_results || report.value.evaluation_results.length === 0) return [];
  const e = report.value.evaluation_results[0];
  const reverseMap = {
    "time_management": "Time Management",
    "orderliness_and_program_flow": "Orderliness and Program Flow",
    "appropriateness_of_venue": "Appropriateness of the Venue",
    "sound_system_and_hall_preparation": "Sound System and Hall Preparation",
    "restrooms": "Restroom/s",
    "food_and_drinks": "Food and Drinks"
  };
  
  const standardKeys = ["time_management", "orderliness_and_program_flow", "appropriateness_of_venue", "sound_system_and_hall_preparation", "restrooms", "food_and_drinks"];
  const results = [];
  
  standardKeys.forEach(key => {
    if (e[key] !== undefined && e[key] !== null) {
      results.push({ area: reverseMap[key], rating: e[key] });
    }
  });
  
  for (const [key, rating] of Object.entries(e)) {
    if (!standardKeys.includes(key) && rating != null) {
      const area = key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
      results.push({ area, rating });
    }
  }
  return results;
});

const computedTotalRating = computed(() => {
  if (parsedAREval.value && parsedAREval.value.length > 0) {
    let sum = 0;
    let count = 0;
    parsedAREval.value.forEach(item => {
      const r = Number(item.rating);
      if (r > 0) {
        sum += r;
        count++;
      }
    });
    if (count > 0) {
      return sum / count;
    }
  }
  return report.value ? Number(report.value.rating || 0) : 0;
});

const getInterpretation = (rating) => {
  const val = parseFloat(rating);
  if (isNaN(val) || val === 0) return '-';
  if (val >= 4.51) return 'Outstanding';
  if (val >= 4.01) return 'Very Good';
  if (val >= 3.51) return 'Good';
  if (val >= 3.01) return 'Average';
  if (val >= 2.51) return 'Fair';
  if (val >= 2.01) return 'Poor';
  return 'Very Poor';
};

const getInterpretationClass = (rating) => {
  const val = parseFloat(rating);
  if (isNaN(val) || val === 0) return '';
  if (val >= 4.51) return 'text-emerald-400';
  if (val >= 4.01) return 'text-teal-400';
  if (val >= 3.51) return 'text-cyan-400';
  if (val >= 3.01) return 'text-amber-400';
  if (val >= 2.51) return 'text-rose-400';
  if (val >= 2.01) return 'text-rose-500';
  return 'text-rose-600';
};

const formatBudgetName = (name) => {
  if (!name) return '';
  return name.replace(/(\([^\)]+\))/g, '<span style="opacity:0.7;font-size:11px;">$1</span>');
};

const previewFile = (filename, folder) => {
  if (!filename) return;
  const base = (import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '') : 'https://gad-ams-2-1.onrender.com');
  pdfFileUrl.value = `${base}/api/files/${folder}/${filename}`;
  isPdfModalOpen.value = true;
};

const downloadFile = (filename, folder, prefix) => {
  if (!filename) return;
  const base = (import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '') : 'https://gad-ams-2-1.onrender.com');
  const url = `${base}/api/files/${folder}/${filename}`;
  window.open(url, '_blank');
};


onMounted(() => {
  if (!user.value.id || user.value.role !== 'gad_staff') router.push('/login');
  else fetchReportDetails();
});
</script>

<style scoped>
.budget-breakdown-block { display: flex; flex-direction: column; gap: 0; border: 1px solid rgba(185,121,204,0.2); border-radius: 10px; overflow: hidden; }
.bbudget-group { border-bottom: 1px solid rgba(185,121,204,0.15); }
.bbudget-group:last-child { border-bottom: none; }
.bbudget-group-header { background: rgba(185,121,204,0.12); padding: 8px 14px; font-size: 12px; font-weight: 700; color: #b979cc; text-transform: uppercase; letter-spacing: 0.06em; }
.bbudget-subitem { padding: 0 0 4px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
.bbudget-subitem:last-child { border-bottom: none; padding-bottom: 0; }
.bbudget-subitem-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 14px 7px 22px; gap: 8px; }
.bbudget-subitem-label { font-size: 13px; color: #cbd5e1; }
.bbudget-subitem-value { font-size: 13px; color: #f1f5f9; font-weight: 600; white-space: nowrap; }
.bbudget-checks { display: flex; flex-wrap: wrap; gap: 10px; padding: 4px 14px 8px 30px; }
.bbudget-check-label { display: flex; align-items: center; gap: 5px; font-size: 12px; color: #94a3b8; cursor: default; user-select: none; }
.bbudget-checkbox { accent-color: #b979cc; transform: scale(0.9); cursor: default; }
.bbudget-meta { font-size: 12px; color: #94a3b8; padding: 2px 14px 8px 30px; }
.bbudget-meta strong { color: #e2e8f0; }
.bbudget-others-breakdown { padding: 4px 14px 8px 30px; display: flex; flex-direction: column; gap: 3px; }
.bbudget-others-row { display: flex; justify-content: space-between; font-size: 12px; color: #94a3b8; }
.bbudget-total-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: rgba(185,121,204,0.18); font-size: 13px; font-weight: 700; color: #f1f5f9; }
.main-viewport { flex: 1; min-height: 100vh; background: transparent; }
.loading-wrapper { display: flex; justify-content: center; align-items: center; min-height: 400px; }
.error-container { max-width: 48rem; margin: 0 auto; padding: 2.5rem; }
.error-box { background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; padding: 1rem; border-radius: 0.75rem; }
.error-title { color: #ef4444; font-weight: 700; }
.error-message { color: #cbd5e1; font-size: 1.1rem; }
.error-back-btn { margin-top: 1rem; font-size: 1.1rem; font-weight: 700; color: #ef4444; background: transparent; border: none; cursor: pointer; }
.page-container { min-height: 100vh;  }
.glass-card { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); backdrop-filter: blur(24px); border-radius: 1.5rem; border: 1px solid rgba(185, 121, 204, 0.2); }

.layout-grid { display: flex; gap: 32px; padding: 2.5rem; max-width: 80rem; margin: 0 auto; }
.flex-055 { flex: 0.55; display: flex; flex-direction: column; overflow: hidden; }
.flex-045-sidebar { flex: 0.45; position: sticky; top: 20px; align-self: flex-start; }

.report-header { padding: 2rem; border-bottom: 1px solid rgba(185, 121, 204, 0.15); background: rgba(0, 0, 0, 0.2); }
.meta-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
.report-title { font-size: 26px; color: white; line-height: 1.25; margin: 1rem 0; }
.status-badge-view { padding: 4px 12px; border-radius: 9999px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; }
.status-badge-view.completed { background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); }
.status-badge-view.cancelled { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
.status-badge-view.pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }
.status-badge-view.approved { background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }
.status-badge-view.revision { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
.control-number { font-size: 11px; font-weight: 700; color: #b979cc; text-transform: uppercase; margin-left: 12px; font-family: monospace; }
.info-grid { display: flex; flex-wrap: wrap; gap: 24px; padding-top: 16px; border-top: 1px solid rgba(185, 121, 204, 0.1); }
.info-item { display: flex; flex-direction: column; }
.info-label { font-size: 10px; text-transform: uppercase; color: #cbd5e1; font-weight: 700; margin-bottom: 4px; }
.info-value-white { font-size: 14px; font-weight: 600; color: white; }
.info-value-purple { font-size: 14px; font-weight: 600; color: #b979cc; }
.report-body { padding: 2rem; }
.report-body > * + * { margin-top: 1.5rem; }
.section-card { background-color: rgba(0, 0, 0, 0.2); border-radius: 16px; padding: 24px; border: 1px solid rgba(185, 121, 204, 0.15); min-width: 0; max-width: 100%; }
.section-header-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
.section-title { font-weight: 800; font-size: 13px; text-transform: uppercase; color: #b979cc; }
.icon-pink { color: #b979cc; }
.text-sm-light { font-size: 1.1rem; color: #cbd5e1; font-weight: 500; }
.grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.divider { color: rgba(203, 213, 225, 0.4); margin: 0 0.25rem; }
.male-val { color: #60a5fa; }
.female-val { color: #f472b6; }
.metric-split { display: flex; align-items: center; justify-content: center; }
.metric-box { background: rgba(0, 0, 0, 0.3); border-radius: 12px; padding: 16px; text-align: center; border: 1px solid rgba(185, 121, 204, 0.1); }
.metric-value { font-size: 24px; font-weight: 700; color: white; }
.metric-label { font-size: 10px; color: #cbd5e1; text-transform: uppercase; margin-top: 4px; }
.doc-item { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 16px; background: rgba(0, 0, 0, 0.3); border-radius: 12px; border: 1px solid rgba(185, 121, 204, 0.15); overflow-x: auto; }
.doc-info { display: flex; align-items: center; gap: 12px; }
.doc-pdf-icon { font-size: 1.875rem; color: #ef4444; }
.doc-title { font-size: 13px; font-weight: 700; color: white; white-space: nowrap; }
.doc-meta { font-size: 11px; color: #cbd5e1; margin-top: 2px; white-space: nowrap; }
.preview-btn { color: #b979cc; font-size: 11px; padding: 6px 16px; border-radius: 8px; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(185, 121, 204, 0.15); font-weight: 700; cursor: pointer; transition: all 0.2s; }
.preview-btn:hover { border-color: #b979cc; color: white; background: rgba(185, 121, 204, 0.1); }
.download-btn-icon { background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(185, 121, 204, 0.15); color: #cbd5e1; padding: 6px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.download-btn-icon:hover { border-color: #b979cc; color: white; }

.assessment-card-custom {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(185, 121, 204, 0.2);
}
.assessment-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid rgba(185, 121, 204, 0.15); }
.assessment-icon { width: 44px; height: 44px; background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: white; font-size: 22px; }
.assessment-title { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #b979cc; }
.assessment-form { display: flex; flex-direction: column; }

.read-only-remarks {
  width: 100%;
  border: 1px solid rgba(185, 121, 204, 0.2);
  border-radius: 12px;
  padding: 14px 16px;
  font-size: 13px;
  background: rgba(0, 0, 0, 0.3);
  color: #cbd5e1;
  min-height: 100px;
  line-height: 1.5;
}

.action-buttons { margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(185, 121, 204, 0.15); }
.btn-back { width: 100%; padding: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #cbd5e1; border-radius: 12px; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(185, 121, 204, 0.15); cursor: pointer; transition: all 0.2s; }
.btn-back:hover { color: white; border-color: #b979cc; background: rgba(185, 121, 204, 0.05); }

.btn-trash {
  width: 100%;
  padding: 12px;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #ef4444;
  border-radius: 12px;
  font-weight: 800;
  text-transform: uppercase;
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 0.75rem;
}
.btn-trash:hover { background: rgba(239, 68, 68, 0.2); border-color: #ef4444; color: #fca5a5; }

@media (max-width: 1024px) {
  .layout-grid { flex-direction: column; padding: 1rem; }
  .flex-06, .flex-055, .flex-04-sidebar, .flex-045-sidebar { flex: 1 !important; width: 100% !important; max-width: 100% !important; position: relative !important; top: 0 !important; }
}

@media (max-width: 768px) {
  .grid-2, .grid-3 { grid-template-columns: 1fr !important; }
  .info-grid { flex-direction: column !important; gap: 12px !important; }
}

.mt-1 { margin-top: 0.25rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-4 { margin-top: 1.5rem; }
.mb-2 { margin-bottom: 0.5rem; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.font-bold { font-weight: 700; }
.text-white { color: white; }

.table-responsive {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid rgba(185, 121, 204, 0.15);
  background: rgba(0, 0, 0, 0.2);
}
.custom-table {
  width: 100%;
  min-width: 500px;
  border-collapse: collapse;
  font-size: 13px;
}
.custom-table th {
  background: rgba(185, 121, 204, 0.1);
  color: #b979cc;
  font-weight: 700;
  text-transform: uppercase;
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid rgba(185, 121, 204, 0.15);
}
.custom-table td {
  padding: 12px 16px;
  color: #cbd5e1;
  border-bottom: 1px solid rgba(185, 121, 204, 0.05);
}
.custom-table tbody tr:last-child td {
  border-bottom: none;
}
.custom-table tfoot td {
  border-top: 1px solid rgba(185, 121, 204, 0.15);
  background: rgba(0, 0, 0, 0.3);
}

.interpretation-tag-ar {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  background: rgba(255,255,255,0.05);
  display: inline-block;
}
.text-emerald-400 { color: #34d399; }
.text-teal-400 { color: #2dd4bf; }
.text-cyan-400 { color: #22d3ee; }
.text-amber-400 { color: #fbbf24; }
.text-rose-400 { color: #fb7185; }
.text-rose-500 { color: #f43f5e; }
.text-rose-600 { color: #e11d48; }


.ar-horizontal-layout {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

@media (min-width: 1280px) {
  .ar-horizontal-layout {
    flex-direction: row;
    align-items: flex-start;
  }
  .ar-horizontal-layout > .section-card {
    flex: 1;
    width: 50%;
    margin-bottom: 0;
  }
}


.layout-vertical {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.flex-full {
  flex: 1;
  width: 100%;
}


.mandate-boxes {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 5px;
}
.mandate-box {
  background: rgba(185, 121, 204, 0.15);
  border: 1px solid rgba(185, 121, 204, 0.3);
  color: #f1f5f9;
  padding: 5px 12px;
  border-radius: 6px;
  font-size: 12px;
}

.venue-badge {
  display: inline-block;
  margin-top: 6px;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.03em;
}
.venue-badge.inside-bsu {
  background: rgba(56, 189, 248, 0.15);
  color: #38bdf8;
  border: 1px solid rgba(56, 189, 248, 0.3);
}
.venue-badge.outside-bsu {
  background: rgba(251, 146, 60, 0.15);
  color: #fb923c;
  border: 1px solid rgba(251, 146, 60, 0.3);
}

.schedule-dropdown summary {
  cursor: pointer;
  font-size: 13px;
  color: #b979cc;
  outline: none;
  font-weight: 500;
  margin-top: 4px;
}
.schedule-dropdown summary:hover {
  text-decoration: underline;
}
.schedule-dropdown[open] summary {
  margin-bottom: 8px;
}
.bbudget-selected-item { 
  font-size: 12px; 
  color: #b979cc; 
  background: rgba(185, 121, 204, 0.1); 
  padding: 2px 8px; 
  border-radius: 12px; 
  border: 1px solid rgba(185, 121, 204, 0.2); 
}
</style>

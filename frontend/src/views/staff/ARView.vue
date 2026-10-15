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
        <!-- TOP: Assessment Record / Evaluator Remarks Banner -->
        <div v-if="report.remarks || report.status === 'Returned' || report.status === 'Rejected'" class="assessment-card-custom mb-6">
          <div class="assessment-header">
            <div class="assessment-icon">📋</div>
            <div>
              <div class="assessment-title">Assessment &amp; Review Remarks</div>
              <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Official evaluator feedback for this accomplishment report.</p>
            </div>
          </div>

          <div class="assessment-form">
            <div class="info-item mb-0">
              <span class="info-label">Assessor Remarks</span>
              <div class="read-only-remarks" style="white-space: pre-wrap; font-size: 13px; line-height: 1.6;">
                {{ report.remarks || 'No remarks recorded for this accomplishment report.' }}
              </div>
            </div>
          </div>
        </div>

        <!-- MAIN SECTION - Accomplishment Report Details -->
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
                  v-if="report.user_id" 
                  type="button" 
                  @click="openProponentModal(report.user_id)" 
                  class="info-value-purple proponent-btn inline-flex items-center gap-1.5 hover:underline cursor-pointer group text-left transition-colors"
                  :title="'View ' + (report.submitter_name || 'proponent') + '\'s profile'"
                >
                  <span>{{ report.submitter_name || '---' }}</span>
                  <span class="material-symbols-outlined text-[15px] opacity-70 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all">open_in_new</span>
                </button>
                <span v-else class="info-value-purple">{{ report.submitter_name || '---' }}</span>
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
                  <p class="text-sm-light mt-1">PHP {{ Number(aDBudget?.grand_total || report.activity_design?.proposed_budget || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</p>
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

        <!-- Sticky Bottom Action Dock -->
        <div class="action-dock-card">
          <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #94a3b8; margin-right: auto;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #b979cc;">info</span>
            <span>Accomplishment Report View &bull; Control: {{ report.control || 'NO CONTROL NUMBER' }}</span>
          </div>
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button @click="handleTrash" class="btn-trash" style="padding: 8px 14px;">
              <span class="material-symbols-outlined" style="font-size: 1.1rem; margin-right: 4px;">delete</span> Move to Trash
            </button>
            <button @click="router.back()" class="btn-back" style="padding: 8px 16px;">
              &larr; Back
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- PDF Preview Modal -->
    <PdfPreviewModal :isOpen="isPdfModalOpen" :fileUrl="pdfFileUrl" @close="closePdfModal" />

    <!-- Proponent Profile Modal -->
    <ProponentProfileModal 
      :isOpen="showProponentModal" 
      :userId="selectedProponentId" 
      @close="showProponentModal = false" 
    />
  </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import Swal from 'sweetalert2';
import PdfPreviewModal from '../../components/PdfPreviewModal.vue';
import ProponentProfileModal from '../../components/ProponentProfileModal.vue';
import ActivityDesignBudget from '../../components/ActivityDesignBudget.vue';

const isDarkMode = ref(document.documentElement.classList.contains('dark'));
let themeObserver = null;

const showProponentModal = ref(false);
const selectedProponentId = ref(null);

const openProponentModal = (userId) => {
  if (!userId) return;
  selectedProponentId.value = userId;
  showProponentModal.value = true;
};

const parseAttachments = (attachmentString) => {
  if (!attachmentString) return [];
  if (Array.isArray(attachmentString)) return attachmentString;
  try {
    let parsed = attachmentString;
    if (typeof parsed === 'string') {
      try { parsed = JSON.parse(parsed); } catch(e) {}
    }
    if (typeof parsed === 'string') {
      try { parsed = JSON.parse(parsed); } catch(e) {}
    }
    if (Array.isArray(parsed)) {
      return parsed;
    }
    return [attachmentString];
  } catch (e) {
    return [attachmentString];
  }
};

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
  if (!report.value.activity_design) return null;
  const ad = report.value.activity_design;
  const b = (ad.budget_items && ad.budget_items[0]) || {};
  let ob = [];
  if (b.materials_others_breakdown) { try { ob = JSON.parse(b.materials_others_breakdown); } catch(e){} }
  const mealsT = Number(b.meals_total) || 0;
  const snacksT = Number(b.snacks_total) || 0;
  const combined = Number(b.meals_and_snacks) || 0;
  const othersTotal = Number(b.others_total) || ob.reduce((s, o) => s + Number(o.amount || 0), 0);
  let flatGrandTotal = (mealsT === 0 && snacksT === 0 && combined > 0 ? combined : mealsT) + snacksT +
    Number(b.function_room_venue || 0) + Number(b.accommodation || 0) + Number(b.equipment_rental || 0) +
    Number(b.transportation || 0) + Number(b.professional_fee_honoria || 0) + Number(b.tokens || 0) +
    Number(b.materials_and_supplies || 0) + othersTotal;

  let rawTotal = 0;
  const rawList = Array.isArray(ad.budget_items_raw) && ad.budget_items_raw.length > 0
    ? ad.budget_items_raw
    : (Array.isArray(ad.budget_items) ? ad.budget_items : []);
  if (rawList.length > 0 && rawList.some(i => i.amount !== undefined)) {
    rawTotal = rawList.reduce((s, i) => s + (Number(i.amount) || 0), 0);
  }

  const grandTotal = rawTotal > 0
    ? rawTotal
    : (flatGrandTotal > 0 ? flatGrandTotal : (Number(ad.proposed_budget) || 0));

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
  if (!report.value) return null;
  let rawExpendituresTotal = 0;
  const rawList = Array.isArray(report.value.budget_expenditures_raw) && report.value.budget_expenditures_raw.length > 0
    ? report.value.budget_expenditures_raw
    : (Array.isArray(report.value.budget_items) ? report.value.budget_items : []);
  if (rawList.length > 0 && rawList.some(i => i.amount !== undefined)) {
    rawExpendituresTotal = rawList.reduce((s, i) => s + (Number(i.amount) || 0), 0);
  }

  const b = (report.value.budget_items && report.value.budget_items[0]) || {};
  let ob = [];
  if (b.materials_others_breakdown) { try { ob = JSON.parse(b.materials_others_breakdown); } catch(e){} }
  const mealsT = Number(b.meals_total) || 0;
  const snacksT = Number(b.snacks_total) || 0;
  const combined = Number(b.meals_and_snacks) || 0;
  const othersTotal = Number(b.others_total) || ob.reduce((s, o) => s + Number(o.amount || 0), 0);
  let flatGrandTotal = (mealsT === 0 && snacksT === 0 && combined > 0 ? combined : mealsT) + snacksT +
    Number(b.function_room_venue || 0) + Number(b.accommodation || 0) + Number(b.equipment_rental || 0) +
    Number(b.transportation || 0) + Number(b.professional_fee_honoria || 0) + Number(b.tokens || 0) +
    Number(b.materials_and_supplies || 0) + othersTotal;

  const grandTotal = rawExpendituresTotal > 0
    ? rawExpendituresTotal
    : (flatGrandTotal > 0 ? flatGrandTotal : (Number(report.value.actual_cost) || 0));

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

  isDarkMode.value = document.documentElement.classList.contains('dark');
  themeObserver = new MutationObserver(() => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
  });
  themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onBeforeUnmount(() => {
  if (themeObserver) {
    themeObserver.disconnect();
  }
});
</script>

<style scoped src="../../assets/ar-view-styles.css"></style>




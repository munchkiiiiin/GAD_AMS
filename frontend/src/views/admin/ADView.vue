<template>
  <div style="width: 100%; overflow-x: hidden;">
    <div style="min-height: 100vh; width: 100%;">
  <main class="main-viewport">
    <div v-if="loading" class="loading-wrapper">
      <div class="loading-spinner"></div>
    </div>

    <div v-else-if="error" class="min-h-[60vh] flex items-center justify-center p-6">
      <div class="bg-black/80 backdrop-blur-3xl rounded-3xl border-2 border-red-500/40 max-w-md w-full text-center p-10 relative overflow-hidden flex flex-col items-center shadow-2xl shadow-red-900/20">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-64 bg-red-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="w-24 h-24 rounded-full bg-red-500/20 border-2 border-red-500/50 flex items-center justify-center mb-6 relative z-10 shadow-lg shadow-red-500/20">
          <span class="material-symbols-outlined text-5xl text-red-400 drop-shadow-md" v-if="error.includes('Access Denied')">gpp_bad</span>
          <span class="material-symbols-outlined text-5xl text-red-400 drop-shadow-md" v-else>error</span>
        </div>
        <h2 class="text-3xl font-headline font-black text-white mb-3 relative z-10 tracking-tight drop-shadow-md">
          {{ error.includes('Access Denied') ? 'Access Restricted' : 'Error Loading Data' }}
        </h2>
        <p class="text-slate-200 font-body text-base font-medium mb-10 relative z-10 leading-relaxed px-2">
          {{ error }}
        </p>
        <button @click="router.back()" class="relative z-10 bg-red-600 hover:bg-red-500 text-white shadow-lg shadow-red-900/50 px-10 py-4 rounded-full font-label text-sm font-extrabold tracking-widest uppercase transition-all hover:-translate-y-1 active:translate-y-0 flex items-center gap-3 group">
          <span class="material-symbols-outlined text-base group-hover:-translate-x-1 transition-transform font-bold">arrow_back</span>
          Go Back
        </button>
      </div>
    </div>

    <div v-else class="page-container">
      <div class="layout-vertical-ad">
        <!-- TOP: Assessment & Review Record Banner -->
        <div v-if="design.assessment_date || design.accomplishment_deadline || design.remarks || design.modification_request_status === 'pending'" class="assessment-card-custom">
          <div class="assessment-header">
            <div class="assessment-icon">📋</div>
            <div>
              <div class="assessment-title">Assessment &amp; Review Record</div>
              <p style="font-size: 12px; color: #94a3b8; margin: 2px 0 0 0;">Official evaluation timeline and reviewer notes.</p>
            </div>
          </div>

          <div class="assessment-form">
            <div v-if="design.modification_request_status === 'pending'" class="staff-mod-box mb-4">
              <div class="staff-mod-title"><span class="material-symbols-outlined">warning</span> Modification Requested</div>
              <div class="staff-mod-body">{{ design.modification_remarks || 'No reason provided.' }}</div>
            </div>

            <div style="display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 16px;">
              <div class="info-item mb-0" style="flex: 1; min-width: 200px;">
                <span class="info-label">Assessment Date</span>
                <span class="info-value-white">{{ formatDate(design.assessment_date) || '---' }}</span>
              </div>

              <div class="info-item mb-0" style="flex: 1; min-width: 200px;">
                <span class="info-label" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                  Accomplishment Deadline
                  <button v-if="!(design.is_archived == 1 && design.accomplishment_report_count > 0)" @click="editDeadline" class="edit-btn" title="Edit Deadline" style="background: none; border: none; cursor: pointer; color: #b979cc; padding: 0;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">edit</span>
                  </button>
                </span>
                <span class="info-value-white">{{ formatDate(design.accomplishment_deadline) || '---' }}</span>
              </div>
            </div>

            <div v-if="design.remarks" class="info-item">
              <span class="info-label">Reviewer Remarks</span>
              <div class="read-only-remarks" style="white-space: pre-wrap; font-size: 13px; line-height: 1.6;">
                {{ design.remarks }}
              </div>
            </div>
          </div>
        </div>

        <!-- MAIN SECTION - Design Preview -->
        <section class="glass-card">
          <div class="report-header">
            <div class="meta-header">
              <div style="display: flex; gap: 8px; align-items: center;">
                <div class="status-badge-view" :class="getStatusClass(design.status)">
                  <span class="status-text">{{ formatStatus(design.status) }}</span>
                </div>
                <div v-if="design.revision_count > 0" class="status-badge-rev">
                  <span>Rev: {{ design.revision_count }}</span>
                </div>
                <div v-if="design.modification_count > 0" class="status-badge-mod">
                  <span>Mod: {{ design.modification_count }}</span>
                </div>
              </div>
              <span class="control-number">{{ design.control || 'PENDING ASSIGNMENT' }}</span>
            </div>

            <h2 class="report-title">{{ design.activity_title }}</h2>

            <div class="info-grid">
              <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Activity Title</span>
                <span class="info-value-white">{{ design.activity_title }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Submitted By</span>
                <button 
                  v-if="design.user_id" 
                  type="button" 
                  @click="openProponentModal(design.user_id)" 
                  class="info-value-purple proponent-btn inline-flex items-center gap-1.5 hover:underline cursor-pointer group text-left transition-colors"
                  :title="'View ' + (design.submitter_name || 'proponent') + '\'s profile'"
                >
                  <span>{{ design.submitter_name || '---' }}</span>
                  <span class="material-symbols-outlined text-[15px] opacity-70 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all">open_in_new</span>
                </button>
                <span v-else class="info-value-purple">{{ design.submitter_name || '---' }}</span>
              </div>
            <div class="info-item">
                <span class="info-label">Office / Unit</span>
                <span class="info-value-purple">{{ design.office }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Date Submitted</span>
                <span class="info-value-white">{{ design.date || '---' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Category</span>
                <span class="info-value-white">Activity Design</span>
              </div>
              <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Activity Classification</span>
                <span class="info-value-white">{{ design.activity_classification || '---' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Form Type</span>
                <span class="info-value-white uppercase">{{ design.form_type_name || formatFormType(design.form_type) || '---' }}</span>
              </div>
              <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Gender Issue / GAD Mandate</span>
                <div v-if="design.gad_mandate" class="mandate-boxes">
                  <span v-for="(mandate, index) in design.gad_mandate.split(';;;')" :key="'m'+index" class="mandate-box">
                    {{ mandate.trim() }}
                  </span>
                </div>
                <span v-else class="info-value-white">---</span>
              </div>
              <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Cause of Gender Issue</span>
                <div v-if="design.gender_issue" class="mandate-boxes">
                  <span v-for="(issue, index) in design.gender_issue.split(';;;')" :key="'i'+index" class="mandate-box">
                    {{ issue.trim() }}
                  </span>
                </div>
                <span v-else class="info-value-white">---</span>
              </div>
            </div>
          </div>

          <div class="report-body">
            <div class="section-card">
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">calendar_month</span>
                <h3 class="section-title">Schedule & Venue</h3>
              </div>
              <div class="grid-2">
                                <div class="full-width-info" style="grid-column: span 2;">
                  <div class="flex flex-col md:flex-row gap-4 mb-4">
                    <div class="calc-date-box calc-date-box-pink group">
                      <div class="calc-date-overlay"></div>
                      <label class="calc-date-label-pink">Calculated Start Date</label>
                      <p class="calc-date-val"><span class="material-symbols-outlined calc-date-icon-pink">calendar_month</span> {{ formatDate(design.start_date) || 'Awaiting schedule...' }}</p>
                    </div>
                    <div class="calc-date-box calc-date-box-purple group">
                      <div class="calc-date-overlay"></div>
                      <label class="calc-date-label-purple">Calculated End Date</label>
                      <p class="calc-date-val"><span class="material-symbols-outlined calc-date-icon-purple">event</span> {{ formatDate(design.end_date) || 'Awaiting schedule...' }}</p>
                    </div>
                  </div>
                  
                  <div v-if="design.schedules && design.schedules.length" class="schedules-container">
                    <div class="schedules-header-row" @click="isSchedulesExpanded = !isSchedulesExpanded">
                      <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                          <label class="section-title !mb-0 flex items-center gap-2" style="cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">schedule</span>
                            Activity Schedules
                          </label>
                          <div class="schedule-type-badge-wrapper">
                            <span class="schedule-type-badge-tag">
                              {{ design.schedule_type === 'staggered' ? 'Non Consecutive / Custom' : 'Consecutive Daily' }}
                            </span>
                          </div>
                      </div>
                      <button type="button" @click.stop="isSchedulesExpanded = !isSchedulesExpanded" class="schedule-expand-btn">
                        {{ isSchedulesExpanded ? 'Hide Schedules' : 'View Schedules' }} <span class="material-symbols-outlined" style="font-size: 18px;">{{ isSchedulesExpanded ? 'expand_less' : 'expand_more' }}</span>
                      </button>
                    </div>
                    
                    <transition name="fade">
                    <div v-if="isSchedulesExpanded" style="margin-top: 16px;">
                    <div v-for="(sch, index) in design.schedules" :key="index" class="schedule-item-card">
                      <div class="schedule-date-col">
                        <span class="material-symbols-outlined schedule-date-icon">calendar_today</span>
                        <span class="schedule-date-text">{{ formatDate(sch.schedule_date || sch.date) }}</span>
                      </div>
                      <div class="schedule-time-pill">
                        <span class="material-symbols-outlined schedule-time-icon">schedule</span>
                        <span class="schedule-time-text">{{ formatTime(sch.start_time) }} - {{ formatTime(sch.end_time) }}</span>
                      </div>

                    </div>
                    </div>
                    </transition>
                  </div>
                  
                  <div v-else class="schedules-container">
                    <div class="schedules-header-row" @click="isSchedulesExpanded = !isSchedulesExpanded">
                      <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                          <label class="section-title !mb-0 flex items-center gap-2" style="cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">schedule</span>
                            Activity Schedules
                          </label>
                          <div class="schedule-type-badge-wrapper">
                            <span class="schedule-type-badge-tag">
                              Legacy Format
                            </span>
                          </div>
                      </div>
                      <button type="button" @click.stop="isSchedulesExpanded = !isSchedulesExpanded" class="schedule-expand-btn">
                        {{ isSchedulesExpanded ? 'Hide Schedules' : 'View Schedules' }} <span class="material-symbols-outlined" style="font-size: 18px;">{{ isSchedulesExpanded ? 'expand_less' : 'expand_more' }}</span>
                      </button>
                    </div>
                    
                    <transition name="fade">
                    <div v-if="isSchedulesExpanded" class="schedule-legacy-card">
                      <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 200px;">
                        <span class="material-symbols-outlined schedule-date-icon">calendar_month</span>
                        <span class="legacy-date-text">{{ formatDate(design.start_date) }} <span class="text-slate-500 mx-1">to</span> {{ formatDate(design.end_date) }}</span>
                      </div>
                      <div class="schedule-time-pill">
                        <span class="material-symbols-outlined schedule-time-icon">schedule</span>
                        <span class="legacy-time-text">{{ formatTime(design.start_time) }} - {{ formatTime(design.end_time) }}</span>
                      </div>
                      <div style="flex-basis: 100%; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-top: 12px; padding-top: 12px; border-top: 1px dashed rgba(255,255,255,0.1);">
                        <span style="font-size: 10px; text-transform: uppercase; font-weight: bold; color: #94a3b8; margin-right: 8px;" class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">restaurant</span> Meals Needed:</span>
                        <span class="text-[11px] text-slate-400 italic">Not specified (Submitted prior to detailed schedules feature)</span>
                      </div>
                    </div>
                    </transition>
                  </div>
                </div>
                <div class="w-full flex flex-col md:flex-row gap-4 mt-6">
                  <div class="flex-1">
                    <label class="info-label">Venue</label>
                    <div v-if="design.venues_list && design.venues_list.length > 0" class="flex flex-col gap-3 mt-2">
                      <div v-for="v in design.venues_list" :key="v.venue_id" class="venue-list-row flex flex-col items-start">
                        <p class="info-value-white !mb-1">{{ v.venue_name }}</p>
                        <span :class="v.is_inside_bsu == 1 || v.is_inside_bsu === true ? 'venue-badge inside-bsu' : 'venue-badge outside-bsu'">
                          {{ v.is_inside_bsu == 1 || v.is_inside_bsu === true ? '🏫 Inside BSU' : '🌐 Outside BSU' }}
                        </span>
                      </div>
                    </div>
                    <div v-else class="mt-2 pb-2">
                      <p class="info-value-white !mb-1">{{ design.venue }}</p>
                      <span :class="design.is_inside_bsu == 1 || design.is_inside_bsu === true ? 'venue-badge inside-bsu' : 'venue-badge outside-bsu'">
                        {{ design.is_inside_bsu == 1 || design.is_inside_bsu === true ? '🏫 Inside BSU' : '🌐 Outside BSU' }}
                      </span>
                    </div>
                  </div>
                  <div class="flex-1">
                    <label class="info-label">Overall Expected Attendance (Auto-calculated)</label>
                    <div class="flex items-center gap-3 mt-2 pb-2">
                      <p class="attendance-val">{{ design.target_participants }} <span class="attendance-unit">individuals</span></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="section-card">
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">payments</span>
                <h3 class="section-title">Proposed Budgetary Requirements</h3>
              </div>
              <div v-if="parsedBudget.length" class="budget-groups-container">
                <div v-for="(venue, vIdx) in parsedBudget" :key="vIdx" class="venue-budget-container">
                  <div @click="toggleVenueBudget(vIdx)" class="venue-budget-header">
                    <div class="flex items-center gap-3">
                      <span class="material-symbols-outlined text-purple-400">location_on</span>
                      <h4 class="venue-budget-title">{{ venue.venue_name }}</h4>
                    </div>
                    <div class="flex items-center gap-4">
                      <span class="venue-budget-total">₱{{ formatCurrency(venue.total) }}</span>
                      <span class="material-symbols-outlined venue-expand-icon" :class="{ 'rotate-180': venueExpandedState[vIdx] !== false }">expand_more</span>
                    </div>
                  </div>
                  
                  <div v-show="venueExpandedState[vIdx] !== false" class="venue-budget-content">
                    <div v-for="(group, gIdx) in venue.groups" :key="gIdx" class="budget-group-card">
                      <div class="budget-group-header">
                        <span class="budget-group-icon">{{ group.icon }}</span>
                        <span class="budget-group-title">{{ group.name }}</span>
                      </div>
                      <div class="budget-group-content">
                        <div v-for="(child, cIdx) in group.children" :key="cIdx" class="budget-row-item">
                          <div class="budget-row-header">
                            <div class="budget-item-info">
                              <div class="budget-item-title" v-html="formatBudgetName(child.name)"></div>
                              <div v-if="child.formula || child.computation" class="budget-formula-text">
                                {{ child.formula || child.computation }}
                              </div>
                              <div v-else-if="child.sub_item && child.name !== child.sub_item" class="budget-sub-item-text">
                                {{ child.sub_item }}
                              </div>
                              <div v-if="child.othersBreakdown && child.othersBreakdown.length" class="budget-others-breakdown-container">
                                <div v-for="(other, otherIdx) in child.othersBreakdown" :key="otherIdx" class="budget-others-breakdown-row">
                                  {{ other.name || 'Unnamed Item' }}
                                </div>
                              </div>
                            </div>
                            <div class="budget-item-value">
                              <span class="budget-currency-symbol">₱</span>
                              <div class="budget-card-input-readonly">{{ formatCurrency(child.value) }}</div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="grand-total-banner-card mt-4">
                  <div class="grand-total-label-banner">Grand Total (PHP)</div>
                  <div class="grand-total-value-banner">
                    ₱{{ formatCurrency(grandTotal) }}
                  </div>
                </div>
              </div>
              <div v-else class="empty-budget-notice">
                No budgetary requirements were specified for this design.
              </div>
            </div>

            <div v-if="design.attachment" class="section-card">
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">description</span>
                <h3 class="section-title">Supporting Documents</h3>
              </div>
              <div class="doc-item">
                <div class="doc-info">
                  <span class="material-symbols-outlined doc-pdf-icon">picture_as_pdf</span>
                  <div>
                    <p class="doc-title">{{ design.attachment }}</p>
                    <p class="doc-meta">Document Reference</p>
                  </div>
                </div>
                <button @click="previewFile(design.attachment)" class="preview-btn">Preview</button>
              </div>
            </div>
          </div>
        </section>

        <!-- Sticky Bottom Action Dock -->
        <div class="action-dock-card">
          <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #94a3b8; margin-right: auto;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #b979cc;">info</span>
            <span>Activity Design View &bull; Control: {{ design.control || 'PENDING' }}</span>
          </div>
          <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <template v-if="design.modification_request_status === 'pending'">
              <button @click="approveModRequest" class="btn-primary" style="background: #4ade80; color: #064e3b; border: none; padding: 8px 16px;">
                Approve Request
              </button>
              <button @click="openRejectModModal" class="btn-primary" style="background: #f87171; color: #450a0a; border: none; padding: 8px 16px;">
                Reject Request
              </button>
            </template>
            <button v-if="design.status === 'Approved'" @click="router.push(`/admin/ad-revision/${design.act_design_id}`)" class="btn-primary" style="padding: 8px 16px;">
              <span class="material-symbols-outlined" style="font-size: 1.1rem; margin-right: 4px;">edit</span> Modify Design
            </button>
            <button v-if="design.is_archived == 1" @click="handleTrash" class="btn-trash" style="padding: 8px 14px;">
              <span class="material-symbols-outlined" style="font-size: 1.1rem; margin-right: 4px;">delete</span> Move to Trash
            </button>
            <button @click="router.back()" class="btn-back" style="padding: 8px 16px;">
              &larr; Back to Archive
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- PDF Preview Modal -->
    <PdfPreviewModal :isOpen="isPdfModalOpen" :fileUrl="pdfFileUrl" @close="closePdfModal" />

    <!-- Edit Deadline Modal -->
    <div v-if="isEditDeadlineModalOpen" class="deadline-modal-overlay">
      <div class="deadline-modal-box">
        <h3 class="deadline-modal-header">Edit Accomplishment Deadline</h3>
        <div class="deadline-modal-body">
          <VueDatePicker 
            :dark="isDarkMode" 
            v-model="editDeadlineValue" 
            :min-date="minDeadlineDate"
            :max-date="maxDeadlineDate"
            :disabled-dates="isDisabledDate"
            model-type="yyyy-MM-dd" 
            :enable-time-picker="false" 
            format="MM/dd/yyyy" 
            auto-apply 
            input-class-name="deadline-datepicker-input"
          >
            <template #dp-input="{ value }">
              <input type="text" :value="value ? String(value).replace(',', '').trim().split(' ')[0] : ''" class="deadline-datepicker-input" readonly placeholder="Select Date" />
            </template>
          </VueDatePicker>
        </div>
        <div class="modal-actions" style="display: flex; gap: 12px; justify-content: flex-end;">
          <button @click="closeEditDeadlineModal" class="btn-cancel">Cancel</button>
          <button @click="submitEditDeadline" class="btn-submit">Save</button>
        </div>
      </div>
    </div>

    <!-- Reject Mod Request Modal -->
    <div v-if="isRejectModModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3 class="modal-title">Reject Modification</h3>
        <p class="modal-desc">Please provide a reason for rejecting this modification request.</p>
        <textarea v-model="rejectModRemarks" class="modal-input" rows="4" placeholder="Enter reason..."></textarea>
        <div class="modal-actions">
          <button @click="closeRejectModModal" class="btn-cancel">Cancel</button>
          <button @click="rejectModRequest" class="btn-submit" style="background: #f87171; color: white;">Reject Request</button>
        </div>
      </div>
    </div>

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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import PdfPreviewModal from '../../components/PdfPreviewModal.vue';
import ProponentProfileModal from '../../components/ProponentProfileModal.vue';
import { useHolidays } from '../../utils/useHolidays';

const isDarkMode = ref(document.documentElement.classList.contains('dark'));
let themeObserver = null;

const showProponentModal = ref(false);
const selectedProponentId = ref(null);

const openProponentModal = (userId) => {
  if (!userId) return;
  selectedProponentId.value = userId;
  showProponentModal.value = true;
};

const escapeHtml = (text) => {
  if (!text) return '';
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
};

const formatBudgetName = (name) => {
  if (!name) return '';
  const safe = escapeHtml(name);
  return safe.replace(/(\([^)]+\))/g, '<span class="budget-item-subtext">$1</span>');
};

const parsedBudget = computed(() => {
  const d = design.value;
  if (!d || !d.act_design_id) return [];

  // If there are detailed budget items from the new multiple-venue system
  if (d.budget_items && Array.isArray(d.budget_items) && d.budget_items.length > 0) {
    const venuesMap = {};
    
    // Group by venue_id
    d.budget_items.forEach(item => {
      const vid = item.venue_id || 'Legacy';
      if (!venuesMap[vid]) {
        let vName = vid === 'Other' ? 'Other Venue' : `Venue ${vid}`;
        if (vid === 'Legacy') vName = 'General Budget';
        
        venuesMap[vid] = {
          venue_id: vid,
          venue_name: vName,
          items: [],
          totals: {
            meals: 0, snacks: 0, 
            venue: 0, accommodation: 0, equipment: 0, transportation: 0,
            pf: 0, tokens: 0, pf_pax: 0, tokens_pax: 0,
            materials: 0, others: 0
          },
          othersBreakdown: [],
          computations: {},
          cateringItems: []
        };
      }
      
      const vData = venuesMap[vid];
      const amt = Number(item.amount) || 0;
      
      if (item.formula || (typeof item.sub_item === 'string' && !item.sub_item.startsWith('{'))) {
         vData.computations[item.item_name] = item.formula || item.sub_item;
      }
      
      if (['Breakfast', 'Lunch', 'Dinner', 'AM Snack', 'PM Snack'].includes(item.item_name)) {
        vData.cateringItems.push({
          name: item.item_name,
          value: amt,
          computation: vData.computations[item.item_name],
          sub_item: item.sub_item
        });
        if (['Breakfast', 'Lunch', 'Dinner'].includes(item.item_name)) {
          vData.totals.meals += amt;
        } else {
          vData.totals.snacks += amt;
        }
      }
      else if (item.item_name === 'Meals') {
        vData.totals.meals += amt;
        try {
          const parsed = JSON.parse(item.sub_item);
          if (parsed && typeof parsed === 'object') vData.mealsPax = parsed;
        } catch(e) {}
      }
      else if (item.item_name === 'Snacks') {
        vData.totals.snacks += amt;
        try {
          const parsed = JSON.parse(item.sub_item);
          if (parsed && typeof parsed === 'object') vData.snacksPax = parsed;
        } catch(e) {}
      }
      else if (item.item_name === 'Function Room/Venue') vData.totals.venue += amt;
      else if (item.item_name === 'Accommodation') vData.totals.accommodation += amt;
      else if (item.item_name === 'Equipment Rental') vData.totals.equipment += amt;
      else if (item.item_name === 'Transportation') vData.totals.transportation += amt;
      else if (item.item_name === 'Professional Fee/Honoraria') {
        vData.totals.pf += amt;
        vData.totals.pf_pax += Number(item.pax || 0);
      }
      else if (item.item_name === 'Token/s') {
        vData.totals.tokens += amt;
        vData.totals.tokens_pax += Number(item.pax || 0);
      }
      else if (item.item_name === 'Materials and Supplies') vData.totals.materials += amt;
      else {
        vData.totals.others += amt;
        vData.othersBreakdown.push({
          name: item.item_name === 'Others' ? (item.sub_item || 'Unnamed Item') : item.item_name,
          amount: amt,
          computation: vData.computations[item.item_name]
        });
      }
    });

    const result = [];
    for (const vid in venuesMap) {
      const v = venuesMap[vid];
      
      const groups = [
        {
          name: 'Catering & Hospitality', icon: '🍽️',
          total: v.totals.meals + v.totals.snacks,
          children: v.cateringItems.length ? v.cateringItems : [
            { name: 'Meals', value: v.totals.meals, pax: v.mealsPax, computation: v.computations?.['Meals'] },
            { name: 'Snacks', value: v.totals.snacks, pax: v.snacksPax, computation: v.computations?.['Snacks'] }
          ]
        },
        {
          name: 'Venue & Logistics', icon: '🏛️',
          total: v.totals.venue + v.totals.accommodation + v.totals.equipment + v.totals.transportation,
          children: [
            { name: 'Function Room/Venue', value: v.totals.venue, computation: v.computations?.['Function Room/Venue'] },
            { name: 'Accommodation', value: v.totals.accommodation, computation: v.computations?.['Accommodation'] },
            { name: 'Equipment Rental', value: v.totals.equipment, computation: v.computations?.['Equipment Rental'] },
            { name: 'Transportation', value: v.totals.transportation, computation: v.computations?.['Transportation'] }
          ]
        },
        {
          name: 'Program & Speakers', icon: '🎤',
          total: v.totals.pf + v.totals.tokens,
          children: [
            { name: `Professional Fee/Honoraria ${v.totals.pf > 0 ? `(Number of Speakers: ${v.totals.pf_pax})` : ''}`, value: v.totals.pf, computation: v.computations?.['Professional Fee/Honoraria'] },
            { name: `Token/s ${v.totals.tokens > 0 ? `(Number of Recipients: ${v.totals.tokens_pax})` : ''}`, value: v.totals.tokens, computation: v.computations?.['Token/s'] }
          ]
        },
        {
          name: 'Materials & Miscellaneous', icon: '📦',
          total: v.totals.materials + v.totals.others,
          children: [
            { name: 'Materials and Supplies', value: v.totals.materials, computation: v.computations?.['Materials and Supplies'] },
            { name: 'Others', value: v.totals.others, othersBreakdown: v.othersBreakdown }
          ]
        }
      ];
      
      const venueTotal = groups.reduce((sum, g) => sum + g.total, 0);
      
      result.push({
        venue_id: v.venue_id,
        venue_name: v.venue_id === 'Legacy' ? 'General Budget (Legacy Format)' : `Venue: ${v.venue_id}`,
        isExpanded: false,
        groups: groups,
        total: venueTotal
      });
    }
    
    if (Array.isArray(d.venues_list)) {
      try {
        result.forEach(r => {
          if (r.venue_id !== 'Legacy' && r.venue_id !== 'Other') {
             const venue = d.venues_list.find(v => String(v.venue_id) === String(r.venue_id));
             r.venue_name = venue ? venue.venue_name : `Venue: ${r.venue_id}`;
          }
        });
      } catch(e){}
    }
    
    return result;
  }

  // LEGACY FORMAT FALLBACK
  const dbMeals = Number(d.meals_total || 0);
  const dbSnacks = Number(d.snacks_total || 0);
  const legacyMealsSnacks = Number(d.meals_and_snacks || 0);

  let mealsVal = 0;
  let snacksVal = 0;
  if (dbMeals === 0 && dbSnacks === 0 && legacyMealsSnacks > 0) {
      mealsVal = legacyMealsSnacks;
  } else {
      mealsVal = dbMeals;
      snacksVal = dbSnacks;
  }

  const dbMat = Number(d.materials_total || 0);
  let ob = [];
  if (d.materials_others_breakdown) {
    try { ob = JSON.parse(d.materials_others_breakdown); } catch(e){}
  }
  const dbOthers = Number(d.others_total) || ob.reduce((s, o) => s + Number(o.amount || 0), 0);
  const legacyMatOthers = Number(d.materials_and_supplies || 0);

  let matVal = 0;
  let othersVal = 0;
  if (dbMat === 0 && dbOthers === 0 && legacyMatOthers > 0) {
      matVal = legacyMatOthers;
  } else {
      matVal = dbMat;
      othersVal = dbOthers;
  }

  const items = [
    {
      name: 'Catering & Hospitality', icon: '🍽️',
      total: mealsVal + snacksVal,
      children: [
        { name: 'Meals', value: mealsVal },
        { name: 'Snacks', value: snacksVal }
      ]
    },
    {
      name: 'Venue & Logistics', icon: '🏛️',
      total: Number(d.function_room_venue || 0) + Number(d.accommodation || 0) + Number(d.equipment_rental || 0) + Number(d.transportation || 0),
      children: [
        { name: 'Function Room/Venue', value: Number(d.function_room_venue || 0) },
        { name: 'Accommodation', value: Number(d.accommodation || 0) },
        { name: 'Equipment Rental', value: Number(d.equipment_rental || 0) },
        { name: 'Transportation', value: Number(d.transportation || 0) }
      ]
    },
    {
      name: 'Program & Speakers', icon: '🎤',
      total: Number(d.professional_fee_honoria || 0) + Number(d.tokens || 0),
      children: [
        { name: `Professional Fee/Honoraria ${Number(d.professional_fee_honoria || 0) > 0 ? `(Number of Speakers: ${d.pf_pax || 0})` : ''}`, value: Number(d.professional_fee_honoria || 0) },
        { name: `Token/s ${Number(d.tokens || 0) > 0 ? `(Number of Recipients: ${d.tokens_pax || 0})` : ''}`, value: Number(d.tokens || 0) }
      ]
    },
    {
      name: 'Materials & Miscellaneous', icon: '📦',
      total: matVal + othersVal,
      children: [
        { name: 'Materials and Supplies', value: matVal },
        { name: 'Others', value: othersVal, othersBreakdown: ob }
      ]
    }
  ];
  
  return [
    {
      venue_id: 'Legacy',
      venue_name: 'General Budget',
      isExpanded: true,
      groups: items,
      total: items.reduce((s, g) => s + g.total, 0)
    }
  ];
});

const venueExpandedState = ref({});
const toggleVenueBudget = (venueIndex) => {
  venueExpandedState.value[venueIndex] = !venueExpandedState.value[venueIndex];
};

const grandTotal = computed(() => {
  return parsedBudget.value.reduce((sum, v) => sum + (Number(v.total) || 0), 0);
});

const route = useRoute();
const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));
const design = ref({});
const { getWorkingDaysDiff, isDisabledDate } = useHolidays();
const isSchedulesExpanded = ref(false);
const loading = ref(true);
const error = ref(null);

const fetchDesignDetails = async () => {
  loading.value = true;
  try {
    const id = route.params.id;
    const response = await api.get(`activity-design/${id}`);
    if (response.data.success) design.value = response.data.data;
    else error.value = "Activity design not found.";
  } catch (err) {
    error.value = "Failed to load activity design.";
  } finally {
    loading.value = false;
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

const isNonConsecutive = (schedules) => {
  if (!schedules || schedules.length <= 1) return false;
  for (let i = 0; i < schedules.length - 1; i++) {
    const d1 = new Date(schedules[i].schedule_date || schedules[i].date);
    const d2 = new Date(schedules[i + 1].schedule_date || schedules[i + 1].date);
    const diffDays = Math.round(Math.abs((d2 - d1) / (1000 * 60 * 60 * 24)));
    if (diffDays !== 1) {
      const wdDiff = getWorkingDaysDiff(d1, d2);
      if (wdDiff !== 1) return true;
    }
    if (schedules[i].start_time !== schedules[i+1].start_time || schedules[i].end_time !== schedules[i+1].end_time) return true;
  }
  return false;
};


const formatFormType = (type) => {
  if (!type) return '---';
  const map = {
    'employee': 'Employee Training',
    'inset': 'INSET Training',
    'extension': 'Extension Program',
    'student': 'Student Activity'
  };
  return map[type] || type;
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

const formatCurrency = (amt) => amt ? parseFloat(amt).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '0.00';

const isPdfModalOpen = ref(false);
const pdfFileUrl = ref('');

const previewFile = (fileName) => {
  if (!fileName) return;
  const base = (import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '') : 'https://gad-ams-2-1.onrender.com');
  const folder = Number(design.value.is_archived) === 1 ? 'archived' : 'drafts';
  pdfFileUrl.value = `${base}/api/files/${folder}/${fileName}`;
  isPdfModalOpen.value = true;
};

const closePdfModal = () => {
  isPdfModalOpen.value = false;
  pdfFileUrl.value = '';
};

import Swal from 'sweetalert2';

const isRejectModModalOpen = ref(false);
const rejectModRemarks = ref('');

const isEditDeadlineModalOpen = ref(false);
const editDeadlineValue = ref(null);
const minDeadlineDate = ref(null);
const maxDeadlineDate = ref(null);

const closeEditDeadlineModal = () => {
  isEditDeadlineModalOpen.value = false;
};

const openRejectModModal = () => {
  isRejectModModalOpen.value = true;
  rejectModRemarks.value = '';
};

const closeRejectModModal = () => {
  isRejectModModalOpen.value = false;
};

const approveModRequest = async () => {
  const result = await Swal.fire({
    title: 'Approve Modification?',
    text: 'Are you sure you want to approve this modification request?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#4ade80',
    cancelButtonColor: '#334155',
    confirmButtonText: 'Yes, approve'
  });
  if (!result.isConfirmed) return;
  try {
    Swal.fire({
      title: 'Processing Request',
      text: 'Please wait while we notify the college...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });
    const res = await api.post(`activity-designs/${route.params.id}/approve-modification`);
    if (res.data.success) {
      Swal.fire({ icon: 'success', title: 'Approved', text: 'Modification request approved.', timer: 1500, showConfirmButton: false });
      fetchDesignDetails();
    } else {
      Swal.fire({ icon: 'error', title: 'Failed', text: res.data.message });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Error approving modification request.' });
  }
};

const rejectModRequest = async () => {
  if (!rejectModRemarks.value || !rejectModRemarks.value.trim()) {
    Swal.fire({
      icon: 'warning',
      title: 'Reason Required',
      text: 'Please provide a reason for rejecting the modification.',
      confirmButtonColor: '#f59e0b'
    });
    return;
  }
  try {
    Swal.fire({
      title: 'Processing Request',
      text: 'Please wait while we notify the college...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });
    const res = await api.post(`activity-designs/${route.params.id}/reject-modification`, { remarks: rejectModRemarks.value });
    if (res.data.success) {
      closeRejectModModal();
      Swal.fire({ icon: 'success', title: 'Rejected', text: 'Modification request rejected.', timer: 1500, showConfirmButton: false });
      fetchDesignDetails();
    } else {
      Swal.fire({ icon: 'error', title: 'Failed', text: res.data.message });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Error rejecting modification request.' });
  }
};

const editDeadline = () => {
  const currentYear = new Date().getFullYear();
  const currentMonth = new Date().getMonth();
  // Get first day of current month
  const firstDay = new Date(currentYear, currentMonth, 1);
  const minCurrentMonth = `${firstDay.getFullYear()}-${String(firstDay.getMonth() + 1).padStart(2, '0')}-${String(firstDay.getDate()).padStart(2, '0')}`;
  const lastDay = new Date(currentYear, 11, 31);
  const maxYear = `${lastDay.getFullYear()}-${String(lastDay.getMonth() + 1).padStart(2, '0')}-${String(lastDay.getDate()).padStart(2, '0')}`;
  
  const endD = design.value.end_date ? design.value.end_date.split(' ')[0] : minCurrentMonth;
  // Use the later date between end_date and first day of current month as min
  const finalMin = endD > minCurrentMonth ? endD : minCurrentMonth;

  minDeadlineDate.value = new Date(finalMin);
  maxDeadlineDate.value = new Date(maxYear);
  editDeadlineValue.value = design.value.accomplishment_deadline ? design.value.accomplishment_deadline : null;
  
  isEditDeadlineModalOpen.value = true;
};

const submitEditDeadline = async () => {
  if (!editDeadlineValue.value) {
    Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select a date' });
    return;
  }
  
  const selected = new Date(editDeadlineValue.value);
  if (isDisabledDate(selected)) {
    Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Weekends and holidays are not allowed.' });
    return;
  }
  
  const current = new Date();
  if (selected.getFullYear() !== current.getFullYear()) {
    Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Deadline must be within the current year' });
    return;
  }
  if (selected.getMonth() < current.getMonth() && selected.getFullYear() === current.getFullYear()) {
    Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Deadline cannot be in a previous month' });
    return;
  }
  
  const minDate = design.value.end_date ? new Date(design.value.end_date.split(' ')[0]) : null;
  if (minDate) {
    // Zero out times just to be strictly comparing dates
    selected.setHours(0,0,0,0);
    minDate.setHours(0,0,0,0);
    
    if (selected.getTime() === minDate.getTime()) {
      Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Deadline cannot be the exact same date as the activity end date' });
      return;
    } else if (selected.getTime() < minDate.getTime()) {
      Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Deadline cannot be before the activity end date' });
      return;
    }
  }

  const formValues = editDeadlineValue.value;
  
  const endD = design.value.end_date ? new Date(design.value.end_date.split(' ')[0]) : null;
  if (endD) {
    const diffDays = getWorkingDaysDiff(endD, selected);
    
    if (diffDays !== 15) {
      const isMore = diffDays > 15;
      const confirmExtra = await Swal.fire({
        title: 'Deadline Validation',
        text: `The selected accomplishment deadline is ${isMore ? 'more' : 'less'} than 15 working days from the activity end date. Do you want to proceed?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#9333ea',
        confirmButtonText: 'Yes, proceed'
      });
      if (!confirmExtra.isConfirmed) return;
    }
  }

  try {
    Swal.fire({
      title: 'Updating...',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });
    
    const response = await api.post(`update-deadline/${design.value.act_design_id || route.params.id}`, {
      deadline: formValues,
      is_archived: design.value.is_archived
    });
    if (response.data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Accomplishment deadline updated.',
        confirmButtonColor: '#9333ea',
        timer: 1500,
        showConfirmButton: false
      });
      design.value.accomplishment_deadline = formValues;
      closeEditDeadlineModal();
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: response.data.message || 'Update failed' });
    }
  } catch (err) {
    console.error(err);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update deadline.' });
  }
};

const handleTrash = async () => {
  const result = await Swal.fire({
    title: 'Move to Trash?',
    text: 'This document will be moved to the trash bin. You can restore it within 30 days.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#334155',
    confirmButtonText: 'Yes, move it'
  });
  if (result.isConfirmed) {
    try {
      const response = await api.delete(`activity-designs/trash/${route.params.id}`);
      if (response.data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Moved to Trash',
          text: 'Document has been moved to trash.',
          timer: 1500,
          showConfirmButton: false
        });
        router.push(design.value.is_archived == 1 ? '/admin/archive' : '/admin/ad-list');
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

onMounted(() => {
  if (!user.value.id || user.value.role !== 'admin') router.push('/login');
  else fetchDesignDetails();

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

<style scoped src="../../assets/ad-view-styles.css"></style>

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

            <div class="report-title-row">
              <h2 class="report-title">{{ report.activity_title }}</h2>

              <div v-if="aRBudget && aRBudget.grand_total > Number(aDBudget?.grand_total || 0)" class="budget-status-banner budget-exceeded-banner">
                <h4 class="budget-status-title">
                  <span class="material-symbols-outlined">warning</span>
                  Budget Limit Exceeded
                </h4>
                <p class="budget-status-text">
                  The actual spending grand total (<strong>₱{{ Number(aRBudget.grand_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</strong>) exceeds the originally approved proposed budget (<strong>₱{{ Number(aDBudget?.grand_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</strong>).
                </p>
              </div>
              <div v-else-if="report.activity_design && aRBudget && aRBudget.grand_total < Number(aDBudget?.grand_total || 0)" class="budget-status-banner budget-underutilized-banner">
                <h4 class="budget-status-title">
                  <span class="material-symbols-outlined">savings</span>
                  Budget Underutilized
                </h4>
                <p class="budget-status-text">
                  The actual spending grand total (<strong>₱{{ Number(aRBudget.grand_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</strong>) is lower than the originally approved proposed budget (<strong>₱{{ Number(aDBudget?.grand_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</strong>).
                </p>
              </div>
            </div>

            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Submitted By</span>
                <button 
                  type="button" 
                  @click="openProponentModal(report.user_id)" 
                  class="proponent-btn"
                  title="Click to view proponent profile"
                >
                  <span class="proponent-name">{{ report.submitter_name || 'N/A' }}</span>
                  <span class="material-symbols-outlined proponent-icon">account_circle</span>
                </button>
              </div>
              <div class="info-item">
                <span class="info-label">Office</span>
                <span class="info-value-white">{{ report.office }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Date Submitted</span>
                <div class="flex items-center gap-2" style="display: flex; align-items: center; gap: 8px;">
                  <span class="info-value-white">{{ report.date || '---' }}</span>
                  <span v-if="isLateSubmission" class="late-badge" style="background-color: #ef4444; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">Late Submission</span>
                </div>
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
                <div class="full-width-info" v-if="report.activity_design">
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

        <section class="flex-full">
          <div class="assessment-card-custom">
            <div class="assessment-header">
              <div class="assessment-icon">📋</div>
              <div class="assessment-title">Assessment & Approval</div>
            </div>

            <div class="assessment-form">
              <div>
                <label class="form-label">Reviewer's Remarks</label>
                <textarea 
                  v-model="assessmentRemarks"
                  rows="4" 
                  class="form-textarea" 
                  placeholder="Add your comments, suggestions, or observations..."
                ></textarea>
                <p class="input-hint">These remarks will be shared with the proponent.</p>
              </div>

              <div class="action-buttons">
                <button @click="handleApprove" class="btn-approve" :disabled="submitting">
                  <span class="material-symbols-outlined">check_circle</span> 
                  {{ submitting ? 'Processing...' : 'Approve & Archive Report' }}
                </button>
                <button @click="showRevisionModal = true" class="btn-revision">
                  <span class="material-symbols-outlined">edit_note</span> REVISION
                </button>
                <button @click="handleTrash" class="btn-trash" >
                  <span class="material-symbols-outlined">delete</span> MOVE TO TRASH
                </button>
                <button @click="router.back()" class="btn-back">
                  ← Back to List
                </button>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <div v-if="showRevisionModal" class="revision-modal show">
      <div class="revision-modal-content">
        <div class="revision-modal-header">
          <h3><span class="material-symbols-outlined">edit_note</span> Request Revision</h3>
        </div>
        <div class="revision-modal-body">
          <div class="activity-preview">
            <p>Target Activity:</p>
            <h4>{{ report.activity_title }}</h4>
          </div>

          <div class="form-group">
            <label>Revision Remarks / Comments or <span style="font-weight: bold; color: #b979cc;">You can also put your remarks/comments in the <a :href="getPdfjsUrl()" target="_blank" style="color: #007bff; text-decoration: underline; cursor: pointer;">pdf</a> file itself before sending revision</span></label>
            <textarea 
              v-model="revisionRemarks"
              class="modal-textarea"
              rows="4" 
              placeholder="Please provide detailed comments on what needs to be revised..."
            ></textarea>
          </div>

          <div class="form-group">
            <label>Revision Deadline</label>
            <VueDatePicker 
                v-model="revisionDeadline" 
                dark
                :min-date="todayDate" 
                :max-date="maxDate"
                :disabled-dates="isDisabledDate" 
                format="MM/dd/yyyy"
                auto-apply 
                input-class-name="modal-input" 
                @update:model-value="validateRevisionDeadline"
              >
                <template #dp-input="{ value }">
                  <input type="text" :value="value ? String(value).replace(',', '').trim().split(' ')[0] : ''" class="modal-input" readonly placeholder="Select Date" />
                </template>
              </VueDatePicker>
            <p class="input-hint">Proponent must resubmit by this date.</p>
          </div>
        </div>
        <div class="revision-modal-footer">
          <button @click="showRevisionModal = false" class="btn-cancel-modal">Cancel</button>
          <button @click="handleSendRevision" class="btn-send">
            <span class="material-symbols-outlined">send</span> Send Revision Request
          </button>
        </div>
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import api from '../../api';
import { useHolidays } from '../../utils/useHolidays';
import PdfPreviewModal from '../../components/PdfPreviewModal.vue';
import ActivityDesignBudget from '../../components/ActivityDesignBudget.vue';
import ProponentProfileModal from '../../components/ProponentProfileModal.vue';

const isProponentModalOpen = ref(false);
const selectedProponentId = ref(null);

const openProponentModal = (userId) => {
  if (!userId) return;
  selectedProponentId.value = userId;
  isProponentModalOpen.value = true;
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
const submitting = ref(false);
const error = ref(null);

const assessmentRemarks = ref('');
const showRevisionModal = ref(false);
const revisionRemarks = ref('');
const revisionDeadline = ref('');

const isLateSubmission = computed(() => {
  if (!report.value || !report.value.date || !report.value.activity_design || !report.value.activity_design.accomplishment_deadline) {
    return false;
  }
  const submittedDate = new Date(report.value.date);
  const deadlineDate = new Date(report.value.activity_design.accomplishment_deadline);
  return submittedDate > deadlineDate;
});

const getTodayDate = () => {
  const d = new Date();
  const utc = d.getTime() + (d.getTimezoneOffset() * 60000);
  const phDate = new Date(utc + (3600000 * 8));
  const year = phDate.getUTCFullYear();
  const month = String(phDate.getUTCMonth() + 1).padStart(2, '0');
  const day = String(phDate.getUTCDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};
const todayDate = ref(getTodayDate());

const getMaxDate = () => {
  const d = new Date();
  const utc = d.getTime() + (d.getTimezoneOffset() * 60000);
  const phDate = new Date(utc + (3600000 * 8));
  const year = phDate.getUTCFullYear();
  return `${year}-12-31`;
};
const maxDate = ref(getMaxDate());
  const { isDisabledDate } = useHolidays();

const validateRevisionDeadline = () => {
  if (revisionDeadline.value) {
    const deadline = new Date(revisionDeadline.value);
    const today = new Date(todayDate.value);
    const endOfYear = new Date(maxDate.value);
    
    deadline.setHours(0,0,0,0);
    today.setHours(0,0,0,0);
    endOfYear.setHours(0,0,0,0);

    if (deadline < today) {
      Swal.fire('Invalid Date', 'Revision deadline cannot be in the past.', 'warning');
      revisionDeadline.value = '';
    } else if (deadline > endOfYear) {
      Swal.fire('Invalid Date', 'Revision deadline must be within the current month.', 'warning');
      revisionDeadline.value = '';
    }
  }
};

const handleBeforeUnload = () => {
  if (report.value && report.value.id && report.value.status === 'Pending') {
    const url = `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080/api/'}accomplishment-report/unmark-viewed/${report.value.id}`;
    navigator.sendBeacon(url);
  }
};

onMounted(() => {
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(async () => {
  window.removeEventListener('beforeunload', handleBeforeUnload);
  if (report.value && report.value.id && report.value.status === 'Pending') {
    try {
      await api.post(`accomplishment-report/unmark-viewed/${report.value.id}`);
    } catch (e) {
      console.error('Failed to unmark viewed:', e);
    }
  }
});

const fetchReportDetails = async () => {
  loading.value = true;
  try {
    const id = route.params.id;
    const response = await api.get(`activity-report/${id}`);
    if (response.data.success) {
      report.value = response.data.data;
      if (report.value.status === 'Pending' && Number(report.value.is_viewed_by_admin) === 0) {
        try {
          await api.post(`accomplishment-report/mark-viewed/${id}`);
          report.value.is_viewed_by_admin = 1;
        } catch (e) {
          console.error('Failed to mark as viewed:', e);
        }
      }
    } else {
      error.value = "Accomplishment report not found.";
    }
  } catch (err) {
    console.error('Error fetching report:', err);
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

const handleApprove = async () => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: 'You are about to approve this report. It will be moved to the archive.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#22c55e',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Yes, approve it!'
  });

  if (!result.isConfirmed) return;
  
  Swal.fire({
    title: 'Processing...',
    text: 'Please wait while we process this request and dispatch email notifications.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  submitting.value = true;
  try {
    const id = report.value.id;
    const response = await api.post(`approve-report/${id}`, {
      remarks: assessmentRemarks.value
    });
    if (response.data.success) {
      Swal.fire({ icon: 'success', title: 'Approved!', text: 'Report approved and successfully moved to archive.', confirmButtonColor: '#b979cc' }).then(() => {
        router.push('/admin/ar-list');
      });
    } else {
      Swal.fire({ icon: 'error', title: 'Approval Failed', text: response.data.message || 'Failed to approve report.', confirmButtonColor: '#b979cc' });
    }
  } catch (err) {
    console.error('Error approving report:', err);
    Swal.fire({ icon: 'error', title: 'Approval Failed', text: 'Failed to approve report.', confirmButtonColor: '#b979cc' });
  } finally {
    submitting.value = false;
  }
};

const handleSendRevision = async () => {
  if (!revisionRemarks.value || !revisionDeadline.value) {
    Swal.fire({ icon: 'warning', title: 'Missing Info', text: 'Please provide both remarks and a deadline.', confirmButtonColor: '#b979cc' });
    return;
  }

  Swal.fire({
    title: 'Processing...',
    text: 'Please wait while we process this request and dispatch email notifications.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  submitting.value = true;
  try {
    const id = report.value.id || report.value.acc_report_id;
    const response = await api.post(`revision-report/${id}`, {
      remarks: revisionRemarks.value,
      deadline: revisionDeadline.value
    });
    
    if (response.data.success) {
      Swal.fire({ icon: 'success', title: 'Revision Sent', text: 'Revision request sent to the proponent.', confirmButtonColor: '#b979cc' }).then(() => {
        showRevisionModal.value = false;
        router.push('/admin/ar-list');
      });
    } else {
      Swal.fire({ icon: 'error', title: 'Failed', text: response.data.message || 'Failed to send revision request.', confirmButtonColor: '#b979cc' });
    }
  } catch (err) {
    console.error('Error requesting revision:', err);
    Swal.fire({ icon: 'error', title: 'Failed', text: 'Failed to send revision request.', confirmButtonColor: '#b979cc' });
  } finally {
    submitting.value = false;
  }
};

const handleTrash = async () => {
  // Admin can trash anytime
  const result = await Swal.fire({
    title: 'Move to Trash?',
    text: 'This accomplishment report will be moved to the trash bin.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, move to trash'
  });

  if (!result.isConfirmed) return;
  
  submitting.value = true;
  try {
    const id = report.value.id || report.value.acc_report_id;
    const response = await api.delete(`accomplishment-reports/trash/${id}`);
    
    if (response.data.success) {
      Swal.fire({ icon: 'success', title: 'Trashed', text: 'Accomplishment report moved to trash.', confirmButtonColor: '#b979cc' }).then(() => {
        router.push('/admin/ar-list');
      });
    } else {
      Swal.fire({ icon: 'error', title: 'Failed', text: response.data.message || 'Failed to trash report.', confirmButtonColor: '#b979cc' });
    }
  } catch (err) {
    console.error('Error trashing report:', err);
    Swal.fire({ icon: 'error', title: 'Failed', text: 'Failed to trash report.', confirmButtonColor: '#b979cc' });
  } finally {
    submitting.value = false;
  }
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
    breakfast_selected: b.breakfast_selected, lunch_selected: b.lunch_selected, dinner_selected: b.dinner_selected,
    am_snack_selected: b.am_snack_selected, pm_snack_selected: b.pm_snack_selected,
    function_room_venue: b.function_room_venue, accommodation: b.accommodation,
    equipment_rental: b.equipment_rental, transportation: b.transportation,
    professional_fee_honoria: b.professional_fee_honoria, tokens: b.tokens,
    pf_pax: b.pf_pax,
    tokens_pax: b.tokens_pax,
    materials_and_supplies: b.materials_and_supplies,
    others_total: othersTotal, othersBreakdown: ob,
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
    breakfast_selected: b.breakfast_selected, lunch_selected: b.lunch_selected, dinner_selected: b.dinner_selected,
    am_snack_selected: b.am_snack_selected, pm_snack_selected: b.pm_snack_selected,
    function_room_venue: b.function_room_venue, accommodation: b.accommodation,
    equipment_rental: b.equipment_rental, transportation: b.transportation,
    professional_fee_honoria: b.professional_fee_honoria, tokens: b.tokens,
    pf_pax: b.pf_pax,
    tokens_pax: b.tokens_pax,
    materials_and_supplies: b.materials_and_supplies,
    others_total: othersTotal, othersBreakdown: ob, grand_total: grandTotal
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

const getPdfjsUrl = () => {
  if (!report.value || !report.value.attachment) return '#';
  const attachments = parseAttachments(report.value.attachment);
  if (attachments.length === 0) return '#';
  const firstFile = attachments[0];
  const folder = Number(report.value.is_archived) === 1 ? 'archived' : 'drafts';
  const base = (import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '') : 'https://gad-ams-2-1.onrender.com');
  const fileUrl = `${base}/api/files/${folder}/${firstFile}`;
  const userRole = user.value?.role || user.value?.user_role || '';
  return `/pdfjs/web/viewer.html?file=${encodeURIComponent(fileUrl)}&role=${encodeURIComponent(userRole)}`;
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
  if (!user.value.id || user.value.role !== 'admin') {
    router.push('/login');
  } else {
    fetchReportDetails();
  }
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

.error-container { max-width: 48rem; margin: 0 auto; padding: 2.5rem 1.5rem; }
.error-box { background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 1rem; border-radius: 0 0.75rem 0.75rem 0; }
.error-title { color: #b91c1c; font-weight: 700; }
.error-message { color: #dc2626; font-size: 1.1rem; }
.error-back-btn { margin-top: 1rem; font-size: 1.1rem; font-weight: 700; color: #b91c1c; background: transparent; border: none; cursor: pointer; }
.error-back-btn:hover { text-decoration: underline; }

.layout-grid { display: flex; gap: 32px; padding: 2.5rem; max-width: 80rem; margin: 0 auto; }
.flex-055 { flex: 0.55; display: flex; flex-direction: column; overflow: hidden; }
.flex-045-sidebar { flex: 0.45; position: sticky; top: 20px; align-self: flex-start; }

button { transition: all 0.2s ease-in-out; cursor: pointer; }

.page-container {
  min-height: 100vh;
}

.glass-card {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  backdrop-filter: blur(24px);
  border-radius: 1.5rem;
  border: 1px solid rgba(185, 121, 204, 0.2);
}

.report-header { padding: 2rem; border-bottom: 1px solid rgba(185, 121, 204, 0.15); background: rgba(0, 0, 0, 0.2); }
.meta-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
.status-badge-view { padding: 4px 12px; border-radius: 9999px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; }
.status-badge-view.completed { background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); }
.status-badge-view.cancelled { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
.status-badge-view.pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }
.status-badge-view.approved { background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }
.status-badge-view.revision { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
.report-body { flex: 1; overflow-y: visible; padding: 2rem; }
.report-body > * + * { margin-top: 1.5rem; }
.assessment-form { display: flex; flex-direction: column; gap: 1rem; }

.status-badge-review {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: rgba(236, 210, 36, 0.15);
  color: #a16207;
  padding: 4px 12px;
  border-radius: 9999px;
  border: 1px solid rgba(236, 210, 36, 0.3);
}

.status-dot-pulse {
  width: 8px;
  height: 8px;
  background-color: #ecd224;
  border-radius: 9999px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(0.8); }
  100% { opacity: 1; transform: scale(1); }
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
  color: #b979cc;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-left: 12px;
  font-family: monospace;
}

.report-title-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
  margin: 16px 0;
}

.report-title {
  flex: 1;
  min-width: 0;
  font-size: 26px;
  color: white;
  line-height: 1.25;
  margin: 0;
}

.budget-status-banner {
  flex: 0 0 360px;
  max-width: 42%;
  padding: 14px 16px;
  border-radius: 8px;
}

.budget-exceeded-banner {
  background: rgba(239, 68, 68, 0.1);
  border-left: 4px solid #ef4444;
}

.budget-underutilized-banner {
  background: rgba(59, 130, 246, 0.1);
  border-left: 4px solid #3b82f6;
}

.budget-status-title {
  margin: 0 0 8px 0;
  font-weight: bold;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.budget-exceeded-banner .budget-status-title {
  color: #ef4444;
}

.budget-underutilized-banner .budget-status-title {
  color: #3b82f6;
}

.budget-status-title .material-symbols-outlined {
  font-size: 18px;
}

.budget-status-text {
  margin: 0;
  color: #cbd5e1;
  font-size: 13px;
  line-height: 1.45;
}

.info-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  padding-top: 16px;
  border-top: 1px solid rgba(185, 121, 204, 0.1);
}

.icon-pink { color: #b979cc; }
.text-sm-light { font-size: 1.1rem; color: #cbd5e1; font-weight: 500; margin-top: 0.25rem; }
.full-width-info { grid-column: span 2; margin-top: 1rem; }

.info-item { display: flex; flex-direction: column; }
.info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #cbd5e1; font-weight: 700; margin-bottom: 4px; }
.info-value { font-size: 14px; font-weight: 600; color: #cbd5e1; }
.info-value-white { font-size: 14px; font-weight: 600; color: white; }
.info-value-purple { font-size: 14px; font-weight: 600; color: #b979cc; }

.section-card { background: rgba(0, 0, 0, 0.2); border-radius: 16px; padding: 24px; border: 1px solid rgba(185, 121, 204, 0.15); min-width: 0; max-width: 100%; margin-bottom: 24px; }
.section-header-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
.section-title { font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.1em; color: #b979cc; }

.divider { color: rgba(203, 213, 225, 0.4); margin: 0 0.25rem; }
.male-val { color: #60a5fa; }
.female-val { color: #f472b6; }

.grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }

.grid-3 {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}
.metric-split { display: flex; align-items: center; justify-content: center; }

.metric-box { background-color: rgba(0, 0, 0, 0.3); border-radius: 12px; padding: 16px; text-align: center; border: 1px solid rgba(185, 121, 204, 0.1); }
.metric-value { font-size: 24px; font-weight: 700; color: white; }
.metric-label { font-size: 10px; color: #cbd5e1; text-transform: uppercase; margin-top: 4px; }

.doc-item { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 16px; background: rgba(0, 0, 0, 0.3); border-radius: 12px; border: 1px solid rgba(185, 121, 204, 0.15); overflow-x: auto; }
.doc-info { display: flex; align-items: center; gap: 12px; }
.doc-pdf-icon { font-size: 1.875rem; color: #ef4444; }
.doc-actions { display: flex; gap: 0.5rem; }
.doc-actions span { font-size: 1.1rem; }
.doc-title { font-size: 13px; font-weight: 700; color: white; white-space: nowrap; }
.doc-meta { font-size: 11px; color: #cbd5e1; margin-top: 2px; white-space: nowrap; }
.preview-btn { color: #b979cc; font-size: 11px; padding: 6px 12px; border-radius: 8px; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(185, 121, 204, 0.15); font-weight: 700; cursor: pointer; }
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

.form-label { display: block; font-size: 10px; font-weight: 800; text-transform: uppercase; color: #cbd5e1; letter-spacing: 1px; margin-bottom: 8px; }
.form-textarea { width: 100%; border: 1px solid rgba(185, 121, 204, 0.2); border-radius: 12px; padding: 14px 16px; font-size: 13px; font-family: inherit; transition: all 0.2s; background: rgba(0, 0, 0, 0.3); color: white; resize: vertical; }
.form-textarea:focus { outline: none; border-color: #b979cc; box-shadow: 0 0 0 3px rgba(153, 13, 209, 0.08); }
.input-hint { font-size: 9px; color: #cbd5e1; opacity: 0.6; margin-top: 8px; }
.input-hint-modal { font-size: 11px; color: #cbd5e1; opacity: 0.6; margin-top: 6px; }

.action-buttons { display: flex; flex-direction: column; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(185, 121, 204, 0.15); }

.btn-approve {
  width: 100%;
  background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%);
  color: white;
  border: none;
  border-radius: 14px;
  padding: 14px;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.2s;
}

.btn-approve:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-approve:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(153, 13, 209, 0.25); }

.btn-revision {
  width: 100%;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(185, 121, 204, 0.3);
  color: white;
  border-radius: 14px;
  padding: 14px;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.btn-revision:hover { background: rgba(0,0,0,0.5); border-color: rgba(185, 121, 204, 0.5); }

.btn-trash {
  width: 100%; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444;
  border-radius: 14px; padding: 14px; font-size: 12px; font-weight: 800; text-transform: uppercase;
  display: flex; align-items: center; justify-content: center; gap: 10px;
}
.btn-trash:hover { background: rgba(239, 68, 68, 0.2); border-color: #ef4444; color: #fca5a5; }

.btn-back { display: block; width: 100%; padding: 12px; font-size: 11px; color: #cbd5e1; text-align: center; border-radius: 12px; background: transparent; border: 1px solid rgba(185, 121, 204, 0.15); margin-top: 8px; }
.btn-back:hover { color: white; border-color: #b979cc; background: rgba(185, 121, 204, 0.05); }

/* Revision Modal */
.revision-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.7); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; z-index: 1000; display: none; }
.revision-modal.show { display: flex; }
.revision-modal-content { background: #1a1a2e; border: 1px solid rgba(185, 121, 204, 0.3); border-radius: 24px; max-width: 520px; width: 90%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; }
.revision-modal-header { background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%); padding: 24px 28px; color: white; }
.revision-modal-header h3 { font-size: 22px; font-weight: 800; display: flex; align-items: center; gap: 12px; }
.revision-modal-body { padding: 28px; }
.revision-modal-footer { padding: 20px 28px; background: rgba(0, 0, 0, 0.2); display: flex; gap: 14px; justify-content: flex-end; border-top: 1px solid rgba(185, 121, 204, 0.15); }

.activity-preview { background: rgba(0, 0, 0, 0.3); padding: 20px; border-radius: 16px; margin-bottom: 24px; border: 1px solid rgba(185, 121, 204, 0.1); }
.activity-preview p { font-size: 11px; color: #cbd5e1; opacity: 0.6; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 1px; }
.activity-preview h4 { font-size: 16px; font-weight: 700; color: white; }

.form-group { margin-bottom: 24px; }
.form-group label { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #cbd5e1; letter-spacing: 1px; margin-bottom: 10px; }
.modal-textarea { width: 100%; padding: 14px 18px; border: 1px solid rgba(185, 121, 204, 0.2); background: rgba(0, 0, 0, 0.4); color: white; border-radius: 14px; font-size: 13px; font-family: inherit; }
.modal-textarea:focus { outline: none; border-color: #b979cc; box-shadow: 0 0 0 3px rgba(185, 121, 204, 0.1); }
.modal-input { width: 100%; padding: 12px 18px; border: 1px solid rgba(185, 121, 204, 0.2); background: rgba(0, 0, 0, 0.4); color: white; border-radius: 12px; font-size: 13px; }
.modal-input:focus { outline: none; border-color: #b979cc; }

.btn-send { background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%); color: white; border: none; padding: 14px 28px; border-radius: 14px; font-weight: 800; font-size: 12px; text-transform: uppercase; cursor: pointer; display: flex; align-items: center; gap: 10px; }
.btn-send:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(153, 13, 209, 0.3); }
.btn-cancel-modal { background: rgba(0, 0, 0, 0.3); color: #cbd5e1; border: 1px solid rgba(185, 121, 204, 0.15); padding: 14px 28px; border-radius: 14px; font-weight: 800; font-size: 12px; text-transform: uppercase; cursor: pointer; }
.btn-cancel-modal:hover { background: rgba(0, 0, 0, 0.5); border-color: #b979cc; color: white; }

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

@media (max-width: 1024px) {
  .layout-grid { flex-direction: column; padding: 1rem; }
  .flex-06, .flex-055, .flex-04-sidebar, .flex-045-sidebar { flex: 1 !important; width: 100% !important; max-width: 100% !important; position: relative !important; top: 0 !important; }
}

@media (max-width: 768px) {
  .grid-2, .grid-3 { grid-template-columns: 1fr !important; }
  .info-grid { flex-direction: column !important; gap: 12px !important; }
  .report-title-row { flex-direction: column; gap: 16px; }
  .budget-status-banner { flex: 1 1 auto; max-width: 100%; width: 100%; }
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

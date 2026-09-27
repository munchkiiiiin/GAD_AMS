<template>
  <div style="width: 100%;">
    <div style="width: 100%; min-height: 100vh;">
  <main class="main-viewport">
    <div v-if="loading" class="loading-wrapper">
      <div class="loading-spinner"></div>
    </div>

    <div v-else class="page-container">

      <!-- Rejection/Revision Note Alert -->
      <div v-if="(existingReport?.modification_remarks)" class="mb-6 p-4 rounded-xl" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
          <span class="material-symbols-outlined" style="color: #f87171; font-size: 24px; margin-top: 2px;">error</span>
          <div>
            <h4 style="color: #fca5a5; font-size: 14px; font-weight: bold; margin: 0 0 6px 0; font-family: 'Inter', sans-serif; letter-spacing: 0.5px;">Rejection / Revision Note</h4>
            <p style="color: #fecaca; font-size: 13px; margin: 0; font-family: 'Inter', sans-serif; line-height: 1.5; white-space: pre-wrap;">{{ existingReport?.modification_remarks }}</p>
          </div>
        </div>
      </div>

      <div class="layout-vertical">
        <!-- TOP: Revision Remarks -->
        <section class="flex-full">
          <div class="assessment-card-custom">
            <div class="assessment-header">
              <div class="assessment-icon">📋</div>
              <div class="assessment-title">Evaluator's Remarks / Comments</div>
            </div>

            <div class="assessment-form">
              <div class="info-item">
                <span class="info-label">Evaluator's Remarks / Comments</span>
                <div class="read-only-remarks">
                  {{ existingReport?.remarks || 'No remarks recorded for this accomplishment report.' }}
                </div>
              </div>

              <div class="action-buttons">
                <button type="button" @click="router.back()" class="btn-back">
                  ← Back
                </button>
              </div>
            </div>
          </div>
        </section>
        
        <section class="flex-full glass-card">
          <div class="report-header">
            <div class="meta-header">
              <div style="display: flex; gap: 8px; align-items: center;">
                <div class="status-badge-view" :class="getStatusClass(existingReport?.status)">
                  <span class="status-text">{{ formatStatus(existingReport?.status) }}</span>
                </div>
                <div v-if="existingReport?.revision_count > 0" class="status-badge-view" style="background: rgba(234,179,8,0.1); border-color: rgba(234,179,8,0.2); padding: 4px 10px;">
                  <span class="status-text" style="color: #facc15; font-size: 11px; font-weight: bold;">Rev: {{ existingReport?.revision_count }}</span>
                </div>
              </div>
              <span class="control-number">{{ existingReport?.control || 'NO CONTROL NUMBER' }}</span>
            </div>

            <h2 class="report-title">{{ existingReport?.activity_title || 'Revise Accomplishment Report' }}</h2>

            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Submitted By</span>
                <span class="info-value-purple">{{ existingReport?.submitter_name || '' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Office</span>
                <span class="info-value-white">{{ existingReport?.office }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Date Submitted</span>
                <span class="info-value-white">{{ existingReport?.date || '---' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Category</span>
                <span class="info-value-white">Accomplishment Report</span>
              </div>
            </div>

          </div>

          <form @submit.prevent="submitReport" class="report-body">
            <div class="ar-horizontal-layout">

              <!-- LEFT: Approved Activity Design Details (read-only) -->
              <div class="section-card" v-if="existingReport && existingReport.activity_design">
                <div class="section-header-row">
                  <span class="material-symbols-outlined icon-pink">info</span>
                  <h3 class="section-title">Approved Activity Design Details</h3>
                </div>
                <div class="grid-2">
                  <div class="full-width-info">
                    <label class="info-label">Title</label>
                    <p class="text-sm-light mt-1">{{ existingReport.activity_design.activity_title || '---' }}</p>
                  </div>
                  <div>
                    <label class="info-label">Control Number</label>
                    <p class="text-sm-light mt-1">{{ existingReport.activity_design.control_number || '---' }}</p>
                  </div>
                  <div>
                    <label class="info-label">Classification</label>
                    <p class="text-sm-light mt-1">{{ existingReport.activity_design.activity_classification || '---' }}</p>
                  </div>
                  <div class="full-width-info">
                    <label class="info-label">Submitted By</label>
                    <p class="text-sm-light mt-1">{{ existingReport.activity_design.submitter_name || '---' }}</p>
                  </div>
                  <div>
                    <label class="info-label">Office/Unit</label>
                    <p class="text-sm-light mt-1">{{ existingReport.activity_design.office || '---' }}</p>
                  </div>
                  <div>
                    <label class="info-label">Date Submitted</label>
                    <p class="text-sm-light mt-1">{{ (existingReport.created_at || existingReport.date) ? new Date(existingReport.created_at || existingReport.date).toLocaleDateString('en-PH', { timeZone: 'Asia/Manila', year: 'numeric', month: 'long', day: 'numeric' }) : '---' }}</p>
                  </div>
                  <div>
                    <label class="info-label">Form Type</label>
                    <p class="text-sm-light mt-1 uppercase">{{ existingReport.activity_design.form_type_name || existingReport.activity_design.form_type || '---' }}</p>
                  </div>
                  <div class="full-width-info" v-if="existingReport.activity_design">
                    <label class="info-label">Gender Issue / GAD Mandate</label>
                    <div v-if="existingReport.activity_design.gad_mandate" class="mandate-boxes">
                      <span v-for="(mandate, index) in existingReport.activity_design.gad_mandate.split(';;;')" :key="'m'+index" class="mandate-box">
                        {{ mandate.trim() }}
                      </span>
                    </div>
                    <p v-else class="text-sm-light mt-1">---</p>
                  </div>
                  <div class="full-width-info" v-if="existingReport.activity_design">
                    <label class="info-label">Cause of Gender Issue</label>
                    <div v-if="existingReport.activity_design.gender_issue" class="mandate-boxes">
                      <span v-for="(issue, index) in existingReport.activity_design.gender_issue.split(';;;')" :key="'gi'+index" class="mandate-box">
                        {{ issue.trim() }}
                      </span>
                    </div>
                    <p v-else class="text-sm-light mt-1">---</p>
                  </div>
                  <div class="full-width-info">
                    <label class="info-label">Venue</label>
                    <div v-if="existingReport.activity_design.venues_list && existingReport.activity_design.venues_list.length > 0" class="flex flex-col gap-3 mt-2">
                      <div v-for="v in existingReport.activity_design.venues_list" :key="v.venue_id" class="flex flex-col items-start pb-2 border-b border-white/5 last:border-0 last:pb-0">
                        <p class="text-sm-light !mb-1">{{ v.venue_name }}</p>
                        <span :class="v.is_inside_bsu == 1 || v.is_inside_bsu === true ? 'venue-badge inside-bsu' : 'venue-badge outside-bsu'">
                          {{ v.is_inside_bsu == 1 || v.is_inside_bsu === true ? '📍 Inside BSU' : '📍 Outside BSU' }}
                        </span>
                      </div>
                    </div>
                    <div v-else class="mt-2 pb-2">
                      <p class="text-sm-light !mb-1">{{ existingReport.activity_design.venue_name || existingReport.activity_design.venue }}</p>
                      <span :class="existingReport.activity_design.is_inside_bsu == 1 || existingReport.activity_design.is_inside_bsu === true ? 'venue-badge inside-bsu' : 'venue-badge outside-bsu'">
                        {{ existingReport.activity_design.is_inside_bsu == 1 || existingReport.activity_design.is_inside_bsu === true ? '📍 Inside BSU' : '📍 Outside BSU' }}
                      </span>
                    </div>
                  </div>
                  <div class="full-width-info" v-if="existingReport.activity_design">
                    <label class="info-label">Overall Expected Attendance (Auto-calculated)</label>
                    <p class="text-sm-light mt-1">{{ existingReport.activity_design.target_participants }}</p>
                  </div>
                  <div>
                    <label class="info-label">Calculated Start Date</label>
                    <p class="text-sm-light mt-1">{{ formatDate(existingReport.activity_design.start_date) }}</p>
                  </div>
                  <div>
                    <label class="info-label">Calculated End Date</label>
                    <p class="text-sm-light mt-1">{{ formatDate(existingReport.activity_design.end_date) }}</p>
                  </div>
                  <div class="full-width-info">
                    <label class="info-label">Full Schedule</label>
                    <details class="schedule-dropdown" v-if="existingReport.activity_design.schedules && existingReport.activity_design.schedules.length > 0">
                      <summary class="schedule-summary">View Full Schedule</summary>
                      <div class="mt-2">
                        <div v-for="(sch, i) in existingReport.activity_design.schedules" :key="i" class="p-2 mb-2" style="background: rgba(255,255,255,0.05); border-radius: 4px; border: 1px solid rgba(255,255,255,0.1);">
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
                      {{ formatTime(existingReport.activity_design.start_time) }} - {{ formatTime(existingReport.activity_design.end_time) }}
                    </div>
                  </div>
                  <div>
                    <label class="info-label">Proposed Budget</label>
                    <p class="text-sm-light mt-1">PHP {{ Number(aDBudget?.grand_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</p>
                  </div>
                  <div>
                    <label class="info-label">Assessment Date</label>
                    <p class="text-sm-light mt-1">{{ existingReport.activity_design.assessment_date ? formatDate(existingReport.activity_design.assessment_date) : 'N/A' }}</p>
                  </div>
                  <div>
                    <label class="info-label">Accomplishment Deadline</label>
                    <p class="text-sm-light mt-1">{{ existingReport.activity_design.accomplishment_deadline ? formatDate(existingReport.activity_design.accomplishment_deadline) : '---' }}</p>
                  </div>
                  <div class="full-width-info" v-if="existingReport.activity_design.remarks">
                    <label class="info-label">Reviewer Remarks</label>
                    <div class="read-only-remarks mt-1">{{ existingReport.activity_design.remarks }}</div>
                  </div>
                </div>

                <!-- Approved Budget Breakdown -->
                <div class="full-width-info mt-4" v-if="existingReport.activity_design && (existingReport.activity_design.budget_items_raw || existingReport.activity_design.budget_items)">
                  <label class="info-label mb-2">Approved Budget Breakdown</label>
                  <ActivityDesignBudget :design="existingReport.activity_design" />
                </div>

                <!-- AD Attachment -->
                <div v-if="existingReport.activity_design && existingReport.activity_design.attachment && parseAttachments(existingReport.activity_design.attachment).length > 0" class="attachments-list mt-4" style="width:100%;">
                  <label class="info-label mb-2">Approved Design Attachments</label>
                  <div v-for="(file, index) in parseAttachments(existingReport.activity_design.attachment)" :key="'ad-'+index" class="doc-item mb-2">
                    <div class="doc-info">
                      <span class="material-symbols-outlined doc-pdf-icon">picture_as_pdf</span>
                      <div>
                        <p class="doc-title">{{ file.split('_').slice(1).join('_') || file }}</p>
                        <p class="doc-meta">Reference: {{ file }}</p>
                      </div>
                    </div>
                    <div class="doc-actions">
                      <button type="button" @click="previewFile(file, 'archived')" class="preview-btn">Preview</button>
                      <button type="button" @click="downloadFile(file, 'archived', 'Activity_Design')" class="download-btn-icon">
                        <span class="material-symbols-outlined">download</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- RIGHT: Actual Accomplishment Details (editable) -->
              <div class="section-card">
                <div class="section-header-row">
                  <span class="material-symbols-outlined icon-pink">edit_document</span>
                  <h3 class="section-title">Actual Accomplishment Details</h3>
                </div>
                <div class="grid-2">
                  <div class="full-width-info">
                    <label class="info-label">Submitted By</label>
                    <p class="text-sm-light mt-1">{{ existingReport.submitter_name || existingReport.user_name || 'N/A' }}</p>
                  </div>
                  <div class="full-width-info">
                    <label class="info-label">Office / Unit</label>
                    <p class="text-sm-light mt-1">{{ existingReport.office || existingReport.office_name || 'N/A' }}</p>
                  </div>
                  <div class="full-width-info">
                    <label class="info-label">Date Submitted</label>
                    <p class="text-sm-light mt-1">{{ (existingReport.created_at || existingReport.date) ? new Date(existingReport.created_at || existingReport.date).toLocaleDateString('en-PH', { timeZone: 'Asia/Manila', year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A' }}</p>
                  </div>

                  <div class="full-width-info">
                    <label class="info-label">Control Number</label>
                    <p class="text-sm-light mt-1">{{ existingReport.control_number || existingReport.control || 'N/A' }}</p>
                  </div>

                  <div class="full-width-info">
                    <label class="info-label">Actual Activity Title *</label>
                    <textarea v-model="form.activity_title" rows="2" class="custom-input-field textarea-no-resize mt-1" placeholder="Enter the actual title of the activity"></textarea>
                  </div>
                  <div class="full-width-info">
                    <label class="info-label">Activity Classification</label>
                    <select v-model="form.activity_classification_id" @change="handleClassificationChange" class="custom-input-field select-arrow-fix mt-1">
                      <option value="" disabled class="dark-option">Select Classification</option>
                      <option v-for="cls in ActClassification" :key="cls.id" :value="cls.id" class="dark-option">
                        {{ cls.classification_name }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="info-label">Form Type</label>
                    <select v-model="form.form_type" class="custom-input-field select-arrow-fix mt-1">
                      <option value="" disabled class="dark-option">Select Form Type</option>
                      <option v-for="ft in formTypes" :key="ft.id" :value="String(ft.id)" class="dark-option">
                        {{ ft.name }}
                      </option>
                    </select>
                  </div>
                  <div class="full-width-info">
                    <label class="info-label">Gender Issue / GAD Mandate</label>
                    <div class="checkbox-group-container custom-input-field mt-1" style="max-height: 200px; overflow-y: auto; overflow-x: hidden; padding: 8px; display: flex; flex-direction: column; gap: 8px;">
                      <label v-for="mandate in GADMandates" :key="mandate.id" class="mandate-checkbox-label" style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; color: #ffffff;">
                        <input type="radio" @change="handleMandateChange" v-model="form.gad_mandate_id" :value="mandate.id.toString()" style="margin-top: 2px; accent-color: #b979cc; transform: scale(1.1);" />
                        <span style="font-size: 13px; color: #fff; line-height: 1.4; word-break: break-word; min-width: 0; flex: 1;">{{ mandate.code }} - {{ mandate.title }}</span>
                      </label>
                      </div>
                  </div>
                  <div class="full-width-info">
                    <label class="info-label">Cause of Gender Issue</label>
                    <div class="checkbox-group-container custom-input-field mt-1" style="max-height: 200px; overflow-y: auto; overflow-x: hidden; padding: 8px; display: flex; flex-direction: column; gap: 8px;">
                      <label v-for="issue in genderIssues" :key="issue.id" class="mandate-checkbox-label" style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; color: #ffffff;">
                        <input type="radio" v-model="form.gender_issue_id" :value="issue.id.toString()" style="margin-top: 2px; accent-color: #b979cc; transform: scale(1.1);" />
                        <span style="font-size: 13px; color: #fff; line-height: 1.4; word-break: break-word; min-width: 0; flex: 1;">{{ issue.title }}</span>
                      </label>
                      <p v-if="!form.gad_mandate_id || form.gad_mandate_id.length === 0" style="color: #94a3b8; font-size: 13px; font-style: italic; margin: 0;">Select a mandate first to see gender issues.</p>
                    </div>
                  </div>

                  <!-- Computed Global Dates -->
                  <div class="form-sub-grid-ar mb-4 mt-4" style="grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="input-group-ar">
                      <div class="label-container">
                        <label class="info-label">Calculated Start Date</label>
                      </div>
                      <div class="custom-input-field" style="display: flex; align-items: center; gap: 8px; opacity: 0.8; cursor: not-allowed; margin-top: 4px;">
                        <span class="material-symbols-outlined" style="font-size: 16px; color: #b979cc;">calendar_month</span>
                        {{ computedStartDate || 'Awaiting schedule...' }}
                      </div>
                    </div>
                    <div class="input-group-ar">
                      <label class="info-label">Calculated End Date</label>
                      <div class="custom-input-field" style="display: flex; align-items: center; gap: 8px; opacity: 0.8; cursor: not-allowed; margin-top: 4px;">
                        <span class="material-symbols-outlined" style="font-size: 16px; color: #b979cc;">event</span>
                        {{ computedEndDate || 'Awaiting schedule...' }}
                      </div>
                    </div>
                  </div>
                  <div style="grid-column: 1 / -1; width: 100%; box-sizing: border-box;">
<div class="form.schedules-container" style="box-sizing: border-box; width: 100%; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(185, 121, 204, 0.2); border-radius: 20px; padding: 24px; margin-bottom: 24px;">
                    <div class="flex justify-between items-center mb-4 flex-wrap gap-4">
                      <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                          <label class="form-label-ar !mb-0 flex items-center gap-2" style="white-space: nowrap;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">schedule</span>
                            Activity Schedules *
                          </label>
                          <div class="schedule-type-toggle-container">
                            <button type="button" @click.prevent="handleScheduleTypeChange('staggered')" class="schedule-type-toggle-btn" :style="{ background: scheduleType === 'staggered' ? 'rgba(185, 121, 204, 0.2)' : 'transparent', color: scheduleType === 'staggered' ? '#e9d5ff' : '#94a3b8' }">Non Consecutive</button>
                            <button type="button" @click.prevent="handleScheduleTypeChange('continuous')" class="schedule-type-toggle-btn" :style="{ background: scheduleType === 'continuous' ? 'rgba(185, 121, 204, 0.2)' : 'transparent', color: scheduleType === 'continuous' ? '#e9d5ff' : '#94a3b8' }">Consecutive</button>
                          </div>
                      </div>
                      <button type="button" v-if="scheduleType === 'staggered'" @click.prevent="addSchedule" style="background: rgba(185, 121, 204, 0.2); color: #e9d5ff; border: 1px solid rgba(185, 121, 204, 0.3); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.2s;">
                        <span class="material-symbols-outlined" style="font-size: 14px;">add</span> Add Schedule
                      </button>
                    </div>
                    
                    <div v-if="!form.schedules?.length" style="color: #94a3b8; font-size: 13px; font-style: italic; margin-bottom: 8px;">
                      Please add at least one schedule.
                    </div>
                    
                    
                    <!-- Continuous Config UI -->
                    <div v-if="scheduleType === 'continuous'" class="schedule-row mb-3 p-4 bg-white border border-slate-200 rounded-lg relative" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);">
                      <div class="schedule-inputs-wrapper" style="margin-bottom: 16px;">
                        <div class="flex-1">
                          <label class="text-[10px] uppercase font-bold text-slate-500 mb-1 block">Start Date</label>
                          <VueDatePicker dark v-model="continuousConfig.start_date" :min-date="minStartDate" :disabled-dates="isDisabledDate" model-type="yyyy-MM-dd" :enable-time-picker="false" format="MM/dd/yyyy" auto-apply required input-class-name="custom-input-field dp-custom-transparent" :max-date="maxDateLimit" >
<template #dp-input="{ value }">
<input type="text" :value="value ? String(value).replace(',', '').trim().split(' ')[0] : ''" class="custom-input-field dp-custom-transparent !text-xs !p-2" readonly placeholder="Select Date" />
</template>
</VueDatePicker>
                        </div>
                        <div class="flex-1">
                          <label class="text-[10px] uppercase font-bold text-slate-500 mb-1 block">End Date</label>
                          <VueDatePicker dark v-model="continuousConfig.end_date" :min-date="continuousConfig.start_date || minStartDate" :disabled-dates="isDisabledDate" model-type="yyyy-MM-dd" :enable-time-picker="false" format="MM/dd/yyyy" auto-apply required input-class-name="custom-input-field dp-custom-transparent" :max-date="maxDateLimit" >
<template #dp-input="{ value }">
<input type="text" :value="value ? String(value).replace(',', '').trim().split(' ')[0] : ''" class="custom-input-field dp-custom-transparent !text-xs !p-2" readonly placeholder="Select Date" />
</template>
</VueDatePicker>
                        </div>
                        <div class="flex-1">
                          <div class="label-container" style="margin-bottom: 4px;">
                            <label class="text-[10px] uppercase font-bold text-slate-500 mb-0">Time From</label>
                            <div class="info-btn-wrapper">
                              <span class="material-symbols-outlined" @click.stop="toggleHelp('startTime')" style="font-size: 14px; cursor: pointer; color: #94a3b8; transition: color 0.2s;" onmouseover="this.style.color='#b979cc'" onmouseout="this.style.color='#94a3b8'">info</span>
                              <transition name="fade-pop"><div v-if="helpState.startTime" class="simple-popup" style="width:160px; font-size:10px; font-weight:normal;">Valid times: 04:00 AM - 08:00 PM</div></transition>
                            </div>
                          </div>
                          <input type="time" v-model="continuousConfig.start_time" min="04:00" max="20:00" required class="custom-input-field" style="color-scheme: dark; cursor: pointer;" @change="handleTimeChange(continuousConfig)">
                        </div>
                        <div class="flex-1">
                          <div class="label-container" style="margin-bottom: 4px;">
                            <label class="text-[10px] uppercase font-bold text-slate-500 mb-0">Time To</label>
                            <div class="info-btn-wrapper">
                              <span class="material-symbols-outlined" @click.stop="toggleHelp('endTime')" style="font-size: 14px; cursor: pointer; color: #94a3b8; transition: color 0.2s;" onmouseover="this.style.color='#b979cc'" onmouseout="this.style.color='#94a3b8'">info</span>
                              <transition name="fade-pop"><div v-if="helpState.endTime" class="simple-popup" style="width:160px; font-size:10px; font-weight:normal;">Valid times: 04:00 AM - 08:00 PM</div></transition>
                            </div>
                          </div>
                          <input type="time" v-model="continuousConfig.end_time" min="04:00" max="20:00" required class="custom-input-field" style="color-scheme: dark; cursor: pointer;" @change="handleTimeChange(continuousConfig)">
                        </div>
                      </div>

                    </div>

                    <!-- Expanded Schedules UI -->
                    <div v-if="scheduleType === 'continuous' && form.schedules?.length > 0" style="margin-top: 16px; margin-bottom: 8px; color: #b979cc; font-size: 11px; font-weight: bold; display: flex; align-items: center; gap: 4px;">
                      <span class="material-symbols-outlined" style="font-size: 14px;">info</span>
                      You can customize the Time and Meals for specific days (e.g., half-day on the last day) below:
                    </div>
                    <div v-for="(sch, index) in form.schedules" :key="index" style="display: flex; align-items: flex-end; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; background: rgba(0,0,0,0.2); padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); position: relative;">
                      <div style="flex: 1; min-width: 130px;">
                        <label style="color: #94a3b8; font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 6px; display: block;">Date</label>
                        <VueDatePicker dark v-model="sch.date" @update:model-value="handleScheduleDateChange($event, index)" :disabled="scheduleType === 'continuous'" :min-date="minStartDate" :disabled-dates="isDisabledDate" model-type="yyyy-MM-dd" :enable-time-picker="false" format="MM/dd/yyyy" auto-apply required input-class-name="custom-input-field dp-custom-transparent" :max-date="maxDateLimit" >
<template #dp-input="{ value }">
<input type="text" :value="value ? String(value).replace(',', '').trim().split(' ')[0] : ''" class="custom-input-field dp-custom-transparent !text-xs !p-2" readonly placeholder="Select Date" />
</template>
</VueDatePicker>
                      </div>
                      <div style="flex: 1; min-width: 130px;">
                        <div class="label-container" style="margin-bottom: 6px;">
                          <label style="color: #94a3b8; font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 0;">Start Time</label>
                          <div class="info-btn-wrapper">
                            <span class="material-symbols-outlined" @click.stop="toggleHelp('startTime')" style="font-size: 14px; cursor: pointer; color: #94a3b8; transition: color 0.2s;" onmouseover="this.style.color='#b979cc'" onmouseout="this.style.color='#94a3b8'">info</span>
                            <transition name="fade-pop"><div v-if="helpState.startTime" class="simple-popup" style="width:160px; font-size:10px; font-weight:normal;">Valid times: 04:00 AM - 08:00 PM</div></transition>
                          </div>
                        </div>
                        <input type="time" v-model="sch.start_time" min="04:00" max="20:00" required class="custom-input-field" style="color-scheme: dark; cursor: pointer;" @change="validateScheduleTime(index)">
                      </div>
                      <div style="flex: 1; min-width: 130px;">
                        <div class="label-container" style="margin-bottom: 6px;">
                          <label style="color: #94a3b8; font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 0;">End Time</label>
                          <div class="info-btn-wrapper">
                            <span class="material-symbols-outlined" @click.stop="toggleHelp('endTime')" style="font-size: 14px; cursor: pointer; color: #94a3b8; transition: color 0.2s;" onmouseover="this.style.color='#b979cc'" onmouseout="this.style.color='#94a3b8'">info</span>
                            <transition name="fade-pop"><div v-if="helpState.endTime" class="simple-popup" style="width:160px; font-size:10px; font-weight:normal;">Valid times: 04:00 AM - 08:00 PM</div></transition>
                          </div>
                        </div>
                        <input type="time" v-model="sch.end_time" min="04:00" max="20:00" required class="custom-input-field" style="color-scheme: dark; cursor: pointer;" @change="validateScheduleTime(index)">
                      </div>
                      <button type="button" v-if="scheduleType === 'staggered' && form.schedules?.length > 1" @click.prevent="removeSchedule(index)" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" title="Remove Schedule">
                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                      </button>

                    </div>
                    
                    
                  </div>
</div>
                  <div class="full-width-info">
                    <label class="info-label">Venue/s *</label>
                    
                    <div class="venue-multiselect-container">
                      <div class="venue-multiselect-header" @click="toggleVenueDropdown">
                        <span v-if="form.venues.length === 0" class="venue-placeholder">Select venues...</span>
                        <div v-else class="venue-chips">
                          <span v-for="v in form.venues" :key="v" class="venue-chip">
                            {{ getVenueName(v) }}
                            <button type="button" class="venue-chip-remove" @click.stop="removeVenue(v)">×</button>
                          </span>
                        </div>
                        <span class="venue-dropdown-icon"></span>
                      </div>

                      <div v-if="showVenueDropdown" class="venue-dropdown-menu">
                        <div 
                          v-for="v in filteredVenues" 
                          :key="v.venue_id"
                          class="venue-dropdown-item"
                          @click="toggleVenueSelection(String(v.venue_id))"
                        >
                          <input type="checkbox" :checked="form.venues.includes(String(v.venue_id))" class="venue-checkbox" @click.stop="toggleVenueSelection(String(v.venue_id))">
                          <span>{{ v.venue_name }}</span>
                        </div>
                        
                        <div class="venue-dropdown-item other-venue-item">
                          <input type="checkbox" :checked="form.venues.includes('Other')" class="venue-checkbox" @change="toggleVenueSelection('Other')">
                          <span>Other (Please specify)</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div v-if="form.venues.includes('Other')" class="full-width-info mt-3 p-3 rounded" style="background: rgba(185, 121, 204, 0.05); border: 1px dashed rgba(185, 121, 204, 0.3);">
                    <label class="info-label" style="color: #d8a0eb;">Specify Other Venue(s) *</label>
                    <p class="text-xs mb-2" style="color: #a0aec0;">If you used multiple unlisted venues, you can separate them with commas.</p>
                    <input 
                      type="text" 
                      v-model="customVenue" 
                      required 
                      class="custom-input-field mt-1"
                      placeholder="e.g. Hotel ABC, XYZ Convention Center"
                    >
                  </div>
                  <div>
                    <label class="info-label">Number of Attendees</label>
                    <input type="number" v-model="form.attendees" min="0" class="custom-input-field mt-1" readonly style="opacity:0.6;cursor:not-allowed;">
                  </div>
                  <div>
                    <label class="info-label">Male Participants *</label>
                    <input type="number" v-model="form.male" min="0" class="custom-input-field mt-1" placeholder="0">
                  </div>
                  <div>
                    <label class="info-label">Female Participants *</label>
                    <input type="number" v-model="form.female" min="0" class="custom-input-field mt-1" placeholder="0">
                  </div>
                </div>

                                                <!-- Actual Budgetary Expenditure (editable) -->
                <div class="budget-section mt-4">
                  <label class="form-label-ar">Actual Budgetary Expenditure *</label>
                  
                  <!-- Grouped Budget Divisions -->
                  <div class="budget-section">
                    <BudgetBuilder 
                      ref="budgetBuilder"
                      v-model:venueBudgets="form.venue_budgets" 
                      :venues="form.venues" 
                      :baselineSettings="baselineSettings"
                      :isOutsideBsu="isOutsideBsu"
                      :computedDays="computedDays"
                      :filteredVenues="venues"
                      :label="''"
                    />
                  </div>

                    <!-- Overall Target Participants Banner -->
                    <div class="grand-total-banner-card" style="background: rgba(30,41,59,0.7); margin-bottom: 12px; border-color: #334155; padding: 12px 20px;">
                      <div class="grand-total-label-banner" style="color: #94a3b8; font-size: 13px;">Overall Expected Attendance (Auto-calculated)</div>
                      <div class="grand-total-value-banner" style="color: #cbd5e1; font-size: 16px;">
                        {{ form.target_participants || 0 }} Pax
                      </div>
                    </div>

                    <!-- Grand Total Banner Card -->
                    <div class="grand-total-banner-card">
                      <div class="grand-total-label-banner">Actual Total Expenditures</div>
                      <div class="grand-total-value-banner">
                        ₱{{ Number(form.proposed_budget || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                      </div>
                    </div>
                  </div>

                <!-- Evaluation (editable) -->
                <div class="full-width-info mt-4">
                  <label class="info-label mb-2">Evaluation Results *</label>
                  <div class="table-responsive">
                    <table class="custom-table">
                      <thead>
                        <tr>
                          <th>Area of Evaluation</th>
                          <th class="text-center" style="width:140px;">Average Rating</th>
                          <th class="text-center" style="width:180px;">Interpretation</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(item, index) in form.evaluation_items" :key="index">
                          <td>
                            <template v-if="!item.isCustom">
                              {{ item.area }}
                            </template>
                            <template v-else>
                              <input type="text" v-model="item.area" class="custom-input-field" placeholder="Enter evaluation criteria..." style="width: 100%;">
                            </template>
                          </td>
                          <td class="text-center">
                            <input type="number" v-model="item.rating" @input="if(item.rating > 5) item.rating = 5; if(item.rating < 0) item.rating = 0;" @blur="item.rating = Math.min(5, Math.max(0, Number(item.rating) || 0))" class="custom-input-field text-center mx-auto" placeholder="0" min="0" max="5" step="0.01" style="width:80px;">
                          </td>
                          <td class="text-center" style="position: relative; padding-right: 56px;">
                            <span :class="getInterpretationClass(item.rating)" style="font-weight: 500;">{{ getInterpretation(item.rating) }}</span>
                            <button type="button" @click="removeEvaluationItem(index)" class="btn-remove-other" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 24px; height: 24px; font-size: 14px; padding: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,0,0,0.2); color: white; border: none; border-radius: 4px; cursor: pointer;" title="Remove">x</button>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="3" style="padding: 12px 16px; border-bottom: none; background: transparent;">
                            <button type="button" @click="addEvaluationItem" class="btn-add-other" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px dashed rgba(255,255,255,0.2); padding: 12px; border-radius: 8px; font-weight: 500; cursor: pointer;">
                              <span>+</span> Add Evaluation Item
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Rating -->
                <div class="full-width-info mt-4" style="display:flex;align-items:center;gap:12px;">
                  <label class="info-label">Total Average Rating *</label>
                  <input type="number" :value="form.rating" readonly class="custom-input-field" placeholder="0.00" style="width:120px; background: rgba(255,255,255,0.05);">
                  <span :class="getInterpretationClass(form.rating)" style="font-weight: 500;">{{ getInterpretation(form.rating) }}</span>
                </div>

                <!-- File Upload -->
                <div class="full-width-info mt-4">
                  <label class="info-label mb-2">Proof of Activity / Attachments *</label>
                  <!-- Existing attachments from previously submitted report -->
                  <div v-if="existingReport && existingReport.attachment && filteredExistingAttachments.length > 0" class="mt-4">
                    <p class="info-label mb-2">Current Submitted Attachments:</p>
                    <div v-for="(file, index) in filteredExistingAttachments" :key="'existing-'+index" class="doc-item mb-2">
                      <div class="doc-info">
                        <span class="material-symbols-outlined doc-pdf-icon">picture_as_pdf</span>
                        <div>
                          <p class="doc-title">{{ file.split('_').slice(1).join('_') || file }}</p>
                          <p class="doc-meta">Previously submitted</p>
                        </div>
                      </div>
                      <div class="doc-actions">
                        <button type="button" @click="previewFile(file, Number(existingReport.is_archived) === 1 ? 'archived' : 'drafts')" class="preview-btn">Preview</button>
                        <button type="button" @click="downloadFile(file, Number(existingReport.is_archived) === 1 ? 'archived' : 'drafts', 'Accomplishment_Report')" class="download-btn-icon">
                          <span class="material-symbols-outlined">download</span>
                        </button>
                        <button type="button" @click="removeExistingAttachment(file)" class="download-btn-icon text-rose-400 hover:text-rose-300" title="Remove file">
                          <span class="material-symbols-outlined">delete</span>
                        </button>
                      </div>
                    </div>
                    <!-- Inline iframe preview of existing file -->
                    <div class="document-previews" style="margin-top:15px;">
                      <div v-for="(file, index) in filteredExistingAttachments" :key="'prev-'+index" style="margin-bottom:20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                          <p style="color:#b979cc;font-size:13px;font-weight:700;margin: 0;">Current File Preview:</p>
                          <button type="button" @click.prevent="expandToNewTab(getExistingFileURL(file))" style="background: transparent; border: 1px solid rgba(185,121,204,0.4); color: #b979cc; padding: 4px 12px; border-radius: 4px; font-size: 11px; cursor: pointer; display: flex; align-items: center;">
                            <span class="material-symbols-outlined" style="font-size: 14px; margin-right: 4px;">open_in_new</span> Expand
                          </button>
                        </div>
                        <iframe :src="getPdfViewerUrl(getExistingFileURL(file))" width="100%" height="400px" style="border:1px solid rgba(185,121,204,0.3);border-radius:8px;"></iframe>
                      </div>
                    </div>
                  </div>

                  <div class="doc-upload-area" @dragover.prevent @drop.prevent="handleFileDrop" @click="triggerFileInput">
                    <span class="material-symbols-outlined upload-icon">cloud_upload</span>
                    <p class="upload-text">Click or drag to upload new files</p>
                    <p class="upload-hint">PDF files only (Max: 10MB per file)</p>
                    <input type="file" ref="fileInput" multiple @change="handleFileUpload" style="display:none" accept=".pdf">
                  </div>
                  <div v-if="uploadedFiles.length > 0" class="mt-4">
                    <p class="info-label mb-2">New Files to Upload:</p>
                    <div v-for="(file, index) in uploadedFiles" :key="index" class="doc-item mb-2">
                      <div class="doc-info">
                        <span class="material-symbols-outlined doc-pdf-icon" :class="{'text-blue-400': file.type && file.type.includes('image')}">{{ file.type && file.type.includes('image') ? 'image' : 'description' }}</span>
                        <div>
                          <p class="doc-title">{{ file.name }}</p>
                          <p class="doc-meta">{{ formatFileSize(file.size) }}</p>
                        </div>
                      </div>
                      <button type="button" @click.prevent="removeFile(index)" class="download-btn-icon text-rose-400 hover:text-rose-300" title="Remove file">
                        <span class="material-symbols-outlined">delete</span>
                      </button>
                    </div>
                    <!-- Inline preview of newly selected files -->
                    <div class="document-previews" style="margin-top:10px;">
                      <div v-for="(file, index) in uploadedFiles" :key="'newprev-'+index" style="margin-bottom:16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                          <p style="color:#b979cc;font-size:13px;font-weight:700;margin: 0;">New File Preview: {{ file.name }}</p>
                          <button type="button" @click.prevent="expandToNewTab(getFileURL(file))" style="background: transparent; border: 1px solid rgba(185,121,204,0.4); color: #b979cc; padding: 4px 12px; border-radius: 4px; font-size: 11px; cursor: pointer; display: flex; align-items: center;">
                            <span class="material-symbols-outlined" style="font-size: 14px; margin-right: 4px;">open_in_new</span> Expand
                          </button>
                        </div>
                        <iframe :src="getPdfViewerUrl(getFileURL(file))" width="100%" height="400px" style="border:1px solid #b979cc;border-radius:8px;"></iframe>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Submit -->
                <div class="full-width-info mt-4">
                  <button type="submit" class="submit-action-btn w-full" :disabled="isSubmitting">
                    {{ isSubmitting ? 'Resubmitting...' : 'Resubmit Report' }}
                  </button>
                </div>
              </div>

            </div>
          </form>
        </section>
      </div>
    </div>

    <!-- PDF Preview Modal -->
    <PdfPreviewModal :isOpen="isPdfModalOpen" :fileUrl="pdfFileUrl" @close="closePdfModal" />
  </main>
    </div>
  </div>
</template>

<script setup>
import { useHolidays } from '../../utils/useHolidays';
const { isDisabledDate, fetchHolidays } = useHolidays();
import PdfPreviewModal from '../../components/PdfPreviewModal.vue';
import ActivityDesignBudget from '../../components/ActivityDesignBudget.vue';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import api from '../../api';
import BudgetBuilder from '../../components/BudgetBuilder.vue';

const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));
const userRole = user.value?.role || user.value?.user_role || '';

const getPdfViewerUrl = (url) => {
  if (!url) return '';
  return `/pdfjs/web/viewer.html?file=${encodeURIComponent(url)}&role=${encodeURIComponent(userRole)}`;
};

const expandToNewTab = (url) => {
  if (url) {
    window.open(getPdfViewerUrl(url), '_blank');
  }
};

const router = useRouter();
const route = useRoute();

const goBack = () => {
  router.back();
};


const menuItems = computed(() => {
  if (route.path.includes('/college')) return collegeMenu;
  return [];
});

const venues = ref([]);
const customVenue = ref('');
const showVenueDropdown = ref(false);

const toggleVenueDropdown = () => {
  showVenueDropdown.value = !showVenueDropdown.value;
};

const loading = ref(true);

const form = ref({
  activity_classification_id: '',
    form_type: '',
  gad_mandate_id: '',
  gender_issue_id: '',
  activity_title: '',
  control_number: '',
  act_design_id: null,
  start_date: '',
  end_date: '',
  schedule_type: 'continuous',
  schedules: [],
  start_time: '',
  end_time: '',
  venues: [],
  venue_budgets: {},
  custom_others: {},
  target_participants: '',
  is_inside_bsu: true,
  attendees: '',
  male: '',
  female: '',
  proposed_budget: 0,
  evaluation_items: [
    { area: 'Time Management', rating: '' },
    { area: 'Orderliness and Program Flow', rating: '' },
    { area: 'Appropriateness of the Venue', rating: '' },
    { area: 'Sound System and Hall Preparation', rating: '' },
    { area: 'Restroom/s', rating: '' },
    { area: 'Food and Drinks', rating: '' }
  ],
  rating: 0
});
const snacksSelected = ref({ am: false, pm: false });
const mealsSelected = ref({ breakfast: false, amSnack: false, lunch: false, pmSnack: false, dinner: false });
const addOtherItem = (vId) => {
  if (!form.value.custom_others) form.value.custom_others = {};
  if (!form.value.custom_others[vId]) form.value.custom_others[vId] = [];
  form.value.custom_others[vId].push({ name: '', amount: 0 });
};

const removeOtherItem = (vId, index) => {
  if (form.value.custom_others && form.value.custom_others[vId]) {
    form.value.custom_others[vId].splice(index, 1);
  }
};

const customMandate = ref('');
const customGenderIssue = ref('');

const getVenueName = (vid) => {
  if (vid === 'Other') {
    return `Other Venue: ${customVenue.value || 'Not specified'}`;
  }
  const found = venues.value.find(v => String(v.venue_id) === String(vid) || v.venue_name === vid);
  return found ? found.venue_name : (vid || 'Unknown Venue');
};

const getVenueTotal = (vid) => {
  if (!form.value.venue_budgets || !form.value.venue_budgets[vid]) return 0;
  
  const b = form.value.venue_budgets[vid];
  let total = 0;
  for (let i = 0; i <= 8; i++) {
    if (b[i] && b[i].total) {
      total += Number(b[i].total) || 0;
    }
  }
  if (b[9] && b[9].total) {
     total += Number(b[9].total) || 0;
  }
  return total;
};

const resolveVenueId = (venue) => {
  if (!venue) return venue;
  if (venue === 'Other') return 'Other';
  return venues.value.find(v => v.venue_name === venue)?.venue_id
    || venues.value.find(v => String(v.venue_id) === String(venue))?.venue_id
    || venue;
};

watch(() => form.value.evaluation_items, (newItems) => {
  if (newItems && newItems.length > 0) {
    let sum = 0;
    let count = 0;
    newItems.forEach(item => {
      const r = Number(item.rating);
      if (r > 0) {
        sum += r;
        count++;
      }
    });
    if (count > 0) {
      form.value.rating = (sum / count).toFixed(2);
    } else {
      form.value.rating = 0;
    }
  } else {
    form.value.rating = 0;
  }
}, { deep: true });

const removeVenue = (venueId) => {
  const index = form.value.venues.indexOf(venueId);
  if (index > -1) {
    form.value.venues.splice(index, 1);
    const vId = venueId;
    if (vId && form.value.venue_budgets && form.value.venue_budgets[vId]) {
      delete form.value.venue_budgets[vId];
    }
    if (form.value.venue_budgets && form.value.venue_budgets[venueName]) {
      delete form.value.venue_budgets[venueName];
    }
  }
};

const toggleVenueSelection = (venueId) => {
  if (form.value.venues.includes(venueId)) {
    removeVenue(venueId);
  } else if (venueId) {
    const tempId = venueId;
    if (!tempId) return;

    if (!form.value.venues) form.value.venues = [];
    form.value.venues.push(venueId);

    if (!form.value.venue_budgets) {
      form.value.venue_budgets = {};
    }
    const budgetKey = tempId;
    if (!form.value.venue_budgets[budgetKey]) {
      form.value.venue_budgets[budgetKey] = [];
    }

    if (!form.value.custom_others) {
      form.value.custom_others = {};
    }
    if (!form.value.custom_others[budgetKey]) {
      form.value.custom_others[budgetKey] = [];
    }
  }
};

const filteredVenues = computed(() => {
  return venues.value.filter(v => 
    (v.is_inside_bsu == 1 || v.is_inside_bsu === true) === form.value.is_inside_bsu &&
    v.venue_name && v.venue_name.trim() !== ''
  );
});






const approvedControls = ref([]);
const loadingControls = ref(false);

const ActClassification = ref([]);
const baselineSettings = ref({});

const helpState = ref({
  startDate: false,
  endDate: false,
  startTime: false,
  endTime: false,
  targetParticipants: false
});

const toggleHelp = (field) => {
  const currentVal = helpState.value[field];
  Object.keys(helpState.value).forEach(key => {
    helpState.value[key] = false;
  });
  helpState.value[field] = !currentVal;
};

const closeAllHelp = () => {
  Object.keys(helpState.value).forEach(key => {
    helpState.value[key] = false;
  });
};

const formTypes = ref([]);
const GADMandates = ref([]);
const genderIssues = ref([]);


const fetchGADMandates = async () => {
  try {
    let url = 'get-gad-mandates';
    if (form.value && form.value.activity_classification_id) {
        url += '?classification=' + form.value.activity_classification_id;
    }
    const res = await api.get(url);
    GADMandates.value = res.data;
  } catch (error) {
    console.error('Error fetching GAD mandates:', error);
  }
};

const handleClassificationChange = async () => {
  form.value.gad_mandate_id = '';
  form.value.gender_issue_id = '';
  await fetchGADMandates();
  await fetchGenderIssues();
};

const handleMandateChange = async () => {
  form.value.gender_issue_id = '';
  await fetchGenderIssues(form.value.gad_mandate_id);
};


const fetchData = async () => {
  try {
    await fetchHolidays();
    const [actRes, formRes] = await Promise.all([
      api.get('get-activity-classifications'),
      api.get('get-form-types')
    ]);
    ActClassification.value = actRes.data;
    formTypes.value = formRes.data;
    await fetchGADMandates();
  } catch (error) {
    console.error('Error fetching dropdown data:', error);
  }
};

const fetchGenderIssues = async (mandateIds) => {
    let ids = mandateIds || form.value?.gad_mandate_id;
    if (!ids || ids.length === 0 ) {
      genderIssues.value = [];
      return;
    }
    if (!Array.isArray(ids)) {
      ids = [ids];
    }
    try {
      const idString = ids.join(',');
      let url = `get-gender-issues?mandates=${idString}`;
      if (form.value && form.value.activity_classification_id) {
          url += '&classification=' + form.value.activity_classification_id;
      }
      const res = await api.get(url);
      genderIssues.value = res.data;
    } catch (error) {
      console.error('Error fetching gender issues:', error);
    }
};

// watcher removed in favor of @change

// watcher removed in favor of @change

const fetchApprovedControls = async () => {
  loadingControls.value = true;
  try {
    // Fetch all values from the control_number table
    const res = await api.get(`approved-controls/${user.value.id}`);
    if (res.data.success) {
      approvedControls.value = res.data.data;
    }
  isSubmitting.value = false;
    } catch (error) {
      isSubmitting.value = false;
    console.error('Error fetching approved controls:', error);
        loading.value = false;
  } finally {
    loadingControls.value = false;
  }
};

watch(() => form.value.control_number, (newVal) => {
  const selected = approvedControls.value.find(c => c.control_number === newVal);
  if (selected) {
    form.value.act_design_id = selected.act_design_id;
    form.value.activity_title = selected.activity_title;
    form.value.start_date = selected.start_date;
    form.value.end_date = selected.end_date;
    form.value.start_time = selected.start_time;
    form.value.end_time = selected.end_time;
    form.value.venue = selected.venue;
  }
});

const formatBudgetName = (name) => {
  if (!name) return '';
  return name.replace(/(\(.*\))/g, '<span class="budget-item-subtext">$1</span>');
};

watch(() => form.value.venue_budgets, (newBudgets) => {
  let total = 0;
  if (newBudgets) {
    Object.values(newBudgets).forEach(items => {
      total += items.reduce((sum, item) => {
        const mults = item.mult ? item.mult.reduce((p, m) => p * (Number(m.q) || 0), 1) : 0;
        return sum + ((Number(item.rate) || 0) * mults);
      }, 0);
    });
  }
  form.value.proposed_budget = total;
}, { deep: true });

const fetchVenues = async () => {
  try {
    const response = await api.get('venues');
    const fetched = Array.isArray(response.data)
      ? response.data
      : (response.data && response.data.success ? response.data.data || [] : []);
    
    // Merge: add fetched venues, then re-append any already-injected ones that aren't in the DB list
    const existing = venues.value.filter(ev =>
      !fetched.some(fv => String(fv.venue_id) === String(ev.venue_id))
    );
    venues.value = [...fetched, ...existing];
  } catch (error) {
    console.error('Error fetching venues:', error);
  }
};



watch(() => form.value.is_inside_bsu, () => {
  if (loadingData.value) return;
  if (form.value.venue && form.value.venue !== 'Other') {
    const isValid = filteredVenues.value.some(v => v.venue_name === form.value.venue);
    if (!isValid) {
      form.value.venue = '';
    }
  }
});

const loadingData = ref(false);

// watcher removed in favor of @change

  watch([() => form.value.male, () => form.value.female], ([newMale, newFemale]) => {
  const m = parseInt(newMale) || 0;
  const f = parseInt(newFemale) || 0;
  form.value.attendees = m + f;
});


const minDate = computed(() => {
  if (existingReport.value?.activity_design?.start_date) {
    return existingReport.value.activity_design.start_date.substring(0, 10);
  }
  const currentYear = new Date().getFullYear();
  return `${currentYear}-01-01`;
});
const maxDate = computed(() => {
  const currentYear = new Date().getFullYear();
  return `${currentYear}-12-31`;
});

const isCurrentYear = (dateString) => {
  const date = new Date(dateString + 'T00:00:00');
  const manilaTime = new Date().toLocaleString("en-US", { timeZone: "Asia/Manila" });
  const currentYear = new Date(manilaTime).getFullYear();
  return date.getFullYear() === currentYear;
};

const isValidTime = (timeStr) => {
  if (!timeStr) return true;
  const [h, m] = timeStr.split(':').map(Number);
  if (h < 4 || h > 20 || (h === 20 && m > 0)) {
    return false;
  }
  return true;
};

watch(() => form.value.start_date, (newDate) => {
  if (loadingData.value) return;
  if (newDate) {
    if (newDate.substring(0, 10) < minDate.value) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Start date cannot be earlier than the approved Activity Design start date.', confirmButtonColor: '#b979cc' });
      form.value.start_date = '';
      return;
    }
    if (!isCurrentYear(newDate.substring(0, 10))) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Activity must be within the current year.', confirmButtonColor: '#b979cc' });
      form.value.start_date = '';
      return;
    }
    if (form.value.end_date && form.value.end_date.substring(0, 10) < newDate.substring(0, 10)) {
        document.activeElement?.blur();
        Swal.fire({ icon: 'warning', title: 'Invalid Duration', text: 'End date cannot be before start date.', confirmButtonColor: '#b979cc' });
        form.value.start_date = '';
    }
  }
});

watch(() => form.value.end_date, (newDate) => {
  if (loadingData.value) return;
  if (newDate) {
    if (newDate.substring(0, 10) < minDate.value) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'End date cannot be earlier than the approved Activity Design start date.', confirmButtonColor: '#b979cc' });
      form.value.end_date = '';
      return;
    }
    if (!isCurrentYear(newDate.substring(0, 10))) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Activity must be within the current year.', confirmButtonColor: '#b979cc' });
      form.value.end_date = '';
      return;
    }
    if (form.value.start_date && newDate.substring(0, 10) < form.value.start_date.substring(0, 10)) {
        document.activeElement?.blur();
        Swal.fire({ icon: 'warning', title: 'Invalid Duration', text: 'End date cannot be before start date.', confirmButtonColor: '#b979cc' });
        form.value.end_date = '';
    }
  }
});

watch(() => form.value.start_time, (newTime) => {
  if (newTime && !isValidTime(newTime)) {
    document.activeElement?.blur();
    Swal.fire({ icon: 'warning', title: 'Invalid Time', text: 'Must be set between 04:00 AM and 08:00 PM.', confirmButtonColor: '#b979cc' });
    form.value.start_time = '';
  }
  if (scheduleType.value === 'staggered') return;
  if (form.value.start_time && form.value.end_time && (!form.value.start_date || !form.value.end_date || form.value.start_date === form.value.end_date)) {
    const startTimeParts = form.value.start_time.split(':');
    const endTimeParts = form.value.end_time.split(':');
    const startMinutes = parseInt(startTimeParts[0]) * 60 + parseInt(startTimeParts[1]);
    const endMinutes = parseInt(endTimeParts[0]) * 60 + parseInt(endTimeParts[1]);
    
    if (endMinutes <= startMinutes) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Time Range', text: 'End time must be after start time.', confirmButtonColor: '#b979cc' });
      form.value.start_time = '';
    } else if ((endMinutes - startMinutes) < 60) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Time Range', text: 'The activity duration must be at least 1 hour.', confirmButtonColor: '#b979cc' });
      form.value.start_time = '';
    }
  }

  const paxBased = /(breakfast|lunch|dinner|snack|professional fee|honoraria|token)/i;
  for (const venueId of form.value.venues || []) {
    const items = form.value.venue_budgets?.[venueId] || [];
    let venueTotal = 0;
    for (const item of items) {
      const multipliers = Array.isArray(item.mult) ? item.mult.filter(multiplier => multiplier && typeof multiplier === 'object') : [];
      const amount = (Number(item.rate) || 0) * multipliers.reduce((total, multiplier) => total * (Number(multiplier.q) || 0), 1);
      const paxMultiplier = multipliers.find(multiplier => /^(pax|person|persons|head|heads)$/i.test(String(multiplier.u || '').trim()));
      const pax = paxMultiplier ? Number(paxMultiplier.q) : 0;
      if (amount < 0 || pax < 0 || (amount === 0 && pax > 0)) {
        isSubmitting.value = false;
        Swal.fire({ icon: 'warning', title: 'Invalid Budget', text: 'Each budget line must have a non-negative amount, and a zero-cost line cannot have a positive pax count.', confirmButtonColor: '#b979cc' });
        return;
      }
      if (amount > 0 && paxBased.test(String(item.name || '')) && pax <= 0) {
        isSubmitting.value = false;
        Swal.fire({ icon: 'warning', title: 'Invalid Budget', text: `${item.name} requires a pax count greater than zero when it has an amount.`, confirmButtonColor: '#b979cc' });
        return;
      }
      venueTotal += amount;
    }
    if (venueTotal <= 0) {
      isSubmitting.value = false;
      Swal.fire({ icon: 'warning', title: 'Empty Venue Budget', text: `Please enter at least one positive budget amount for ${getVenueName(venueId)}.`, confirmButtonColor: '#b979cc' });
      return;
    }
  }
});

watch(() => form.value.end_time, (newTime) => {
  if (newTime && !isValidTime(newTime)) {
    document.activeElement?.blur();
    Swal.fire({ icon: 'warning', title: 'Invalid Time', text: 'Must be set between 04:00 AM and 08:00 PM.', confirmButtonColor: '#b979cc' });
    form.value.end_time = '';
  }
  if (scheduleType.value === 'staggered') return;
  if (form.value.start_time && form.value.end_time && (!form.value.start_date || !form.value.end_date || form.value.start_date === form.value.end_date)) {
    const startTimeParts = form.value.start_time.split(':');
    const endTimeParts = form.value.end_time.split(':');
    const startMinutes = parseInt(startTimeParts[0]) * 60 + parseInt(startTimeParts[1]);
    const endMinutes = parseInt(endTimeParts[0]) * 60 + parseInt(endTimeParts[1]);
    
    if (endMinutes <= startMinutes) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Time Range', text: 'End time must be after start time.', confirmButtonColor: '#b979cc' });
      form.value.end_time = '';
    } else if ((endMinutes - startMinutes) < 60) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Time Range', text: 'The activity duration must be at least 1 hour.', confirmButtonColor: '#b979cc' });
      form.value.end_time = '';
    }
  }
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

watch(() => form.value.evaluation_items, (items) => {
  const valid = items.filter(i => i.rating !== '' && !isNaN(parseFloat(i.rating)));
  if (valid.length === 0) {
    form.value.rating = 0;
  } else {
    const sum = valid.reduce((acc, curr) => acc + parseFloat(curr.rating), 0);
    form.value.rating = (sum / items.length).toFixed(2);
  }
}, { deep: true });

const uploadedFiles = ref([]);
const removedAttachments = ref([]);
const fileInput = ref(null);

const removeExistingAttachment = (file) => {
  removedAttachments.value.push(file);
};

const handleFileUpload = async (event) => {
  if (event.target.files.length > 0) {
    const newFiles = Array.from(event.target.files);
    const duplicateFiles = [];
    const uniqueFiles = [];
    
    newFiles.forEach(file => {
      const existsInUploaded = uploadedFiles.value.some(uploadedFile => {
        return uploadedFile.name === file.name;
      });
      
      if (existsInUploaded) {
        duplicateFiles.push(file);
      } else {
        uniqueFiles.push(file);
      }
    });

    if (duplicateFiles.length > 0) {
      document.activeElement?.blur();
      const result = await Swal.fire({
        icon: 'warning',
        title: 'Duplicate File Detected',
        text: 'One or more files have the same name as an already uploaded file. Do you want to add them anyway?',
        showCancelButton: true,
        confirmButtonText: 'Yes, add them',
        cancelButtonText: 'No, ignore duplicates',
        confirmButtonColor: '#b979cc',
        cancelButtonColor: '#6c757d'
      });
      
      if (result.isConfirmed) {
        uploadedFiles.value = [...uploadedFiles.value, ...newFiles];
      } else {
        uploadedFiles.value = [...uploadedFiles.value, ...uniqueFiles];
      }
    } else {
      uploadedFiles.value = [...uploadedFiles.value, ...uniqueFiles];
    }
    
    if (fileInput.value) {
      fileInput.value.value = '';
    }
  }
};

const isSubmitting = ref(false);

const triggerFileInput = () => {
  if (fileInput.value) fileInput.value.click();
};

const handleFileDrop = async (event) => {
  if (event.dataTransfer.files.length > 0) {
    const newFiles = Array.from(event.dataTransfer.files);
    const duplicateFiles = [];
    const uniqueFiles = [];
    
    newFiles.forEach(file => {
      const existsInUploaded = uploadedFiles.value.some(uploadedFile => {
        return uploadedFile.name === file.name;
      });
      
      if (existsInUploaded) {
        duplicateFiles.push(file);
      } else {
        uniqueFiles.push(file);
      }
    });

    if (duplicateFiles.length > 0) {
      document.activeElement?.blur();
      const result = await Swal.fire({
        icon: 'warning',
        title: 'Duplicate File Detected',
        text: 'One or more files have the same name as an already uploaded file. Do you want to add them anyway?',
        showCancelButton: true,
        confirmButtonText: 'Yes, add them',
        cancelButtonText: 'No, ignore duplicates',
        confirmButtonColor: '#b979cc',
        cancelButtonColor: '#6c757d'
      });
      
      if (result.isConfirmed) {
        uploadedFiles.value = [...uploadedFiles.value, ...newFiles];
      } else {
        uploadedFiles.value = [...uploadedFiles.value, ...uniqueFiles];
      }
    } else {
      uploadedFiles.value = [...uploadedFiles.value, ...uniqueFiles];
    }
  }
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const filteredExistingAttachments = computed(() => {
  if (!existingReport.value || !existingReport.value.attachment) return [];
  const allAttachments = parseAttachments(existingReport.value.attachment);
  return allAttachments.filter(file => !removedAttachments.value.includes(file));
});

const removeFile = (index) => {
  uploadedFiles.value.splice(index, 1);
  if (uploadedFiles.value.length === 0 && fileInput.value) {
    fileInput.value.value = '';
  }
};


const scheduleType = ref('continuous');
const continuousConfig = ref({
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  meals_and_snacks: { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false }
});

const computedDays = computed(() => {
  let totalDays = 0;
  (form.value.schedules || []).forEach(schedule => {
    if (!schedule.date) return;
    if (!schedule.start_time || !schedule.end_time) {
      totalDays += 1;
      return;
    }

    const [startHour, startMinute] = schedule.start_time.split(':').map(Number);
    const [endHour, endMinute] = schedule.end_time.split(':').map(Number);
    const hours = (endHour + endMinute / 60) - (startHour + startMinute / 60);
    totalDays += hours > 0 && hours <= 4 ? 0.5 : 1;
  });
  return totalDays > 0 ? totalDays : 1;
});


const handleScheduleTypeChange = (newType) => {
  if (scheduleType.value === newType) return;
  scheduleType.value = newType;
  
  continuousConfig.value = {
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    meals_and_snacks: { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false }
  };

  const handleScheduleDateChange = (date, index) => {
    if (scheduleType.value !== 'staggered' || !date) return;
    const duplicate = form.value.schedules.some((schedule, scheduleIndex) => scheduleIndex !== index && schedule.date === date);
    if (duplicate) {
      form.value.schedules[index].date = '';
      Swal.fire({ icon: 'warning', title: 'Duplicate Schedule Date', text: 'Each non-consecutive schedule must use a different date.', confirmButtonColor: '#b979cc' });
    }
  };

  form.schedules = [{
    date: '',
    start_time: '',
    end_time: '',
    meals_and_snacks: { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false }
  }];
};



watch(continuousConfig, () => {
  if (scheduleType.value === 'continuous') {
    recalculateSchedules();
  }
}, { deep: true });

const recalculateSchedules = () => {
  if (scheduleType.value === 'continuous') {
    if (continuousConfig.value.start_date && continuousConfig.value.end_date) {
      const start = new Date(continuousConfig.value.start_date);
      const end = new Date(continuousConfig.value.end_date);
      
      const schedules = [];
      let current = new Date(start);
      while (current <= end) {
        // Skip weekends
        const day = current.getDay();
        if (!isDisabledDate || !isDisabledDate(current)) {
            schedules.push({
              date: current.toISOString().split('T')[0],
              start_time: continuousConfig.value.start_time,
              end_time: continuousConfig.value.end_time,
              meals_and_snacks: { ...continuousConfig.value.meals_and_snacks }
            });
        }
        current.setDate(current.getDate() + 1);
      }
      form.value.schedules = schedules;
      form.value.start_date = continuousConfig.value.start_date;
      form.value.end_date = continuousConfig.value.end_date;
    } else {
      form.value.schedules = [];
      form.value.start_date = '';
      form.value.end_date = '';
    }
  } else {
    // If switched to staggered and schedules is empty, add one
    if (!form.value.schedules || form.value.schedules.length === 0) {
      form.value.schedules = [{
        date: '',
        start_time: '',
        end_time: '',
        meals_and_snacks: { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false }
      }];
    }
    // Update main start/end dates based on schedules
    if (form.value.schedules.length > 0) {
      const dates = form.value.schedules.map(s => s.date).filter(d => d);
      if (dates.length > 0) {
        dates.sort();
        form.value.start_date = dates[0];
        form.value.end_date = dates[dates.length - 1];
      }
    }
  }
};

const addSchedule = () => {
  form.value.schedules.push({
    date: '',
    start_time: '',
    end_time: '',
    meals_and_snacks: { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false }
  });
};

const removeSchedule = (index) => {
  form.value.schedules.splice(index, 1);
  recalculateSchedules();
};

watch(() => form.value.schedules, () => {
    if (scheduleType.value === 'staggered') {
        recalculateSchedules();
    }
}, { deep: true });



const totalBreakfastDays = computed(() => {
  return form.value.schedules?.filter(s => s.meals_and_snacks?.breakfast).length || 0;
});
const totalLunchDays = computed(() => {
  return form.value.schedules?.filter(s => s.meals_and_snacks?.lunch).length || 0;
});
const totalDinnerDays = computed(() => {
  return form.value.schedules?.filter(s => s.meals_and_snacks?.dinner).length || 0;
});
const totalAMSnackDays = computed(() => {
  return form.value.schedules?.filter(s => s.meals_and_snacks?.am_snack).length || 0;
});
const totalPMSnackDays = computed(() => {
  return form.value.schedules?.filter(s => s.meals_and_snacks?.pm_snack).length || 0;
});

const isOutsideBsu = computed(() => form.value.is_inside_bsu === false || form.value.is_inside_bsu === 'false');

const addEvaluationItem = () => {
  form.value.evaluation_items.push({ area: '', rating: '', isCustom: true });
};

const removeEvaluationItem = (index) => {
  form.value.evaluation_items.splice(index, 1);
};

const submitReport = async () => {
  isSubmitting.value = true;
  
  if (scheduleType.value === 'continuous') {
    if (!continuousConfig.value.start_date || !continuousConfig.value.end_date || !continuousConfig.value.start_time || !continuousConfig.value.end_time) {
      isSubmitting.value = false;
      Swal.fire({
        icon: 'warning',
        title: 'Missing Schedule Data',
        text: 'Please complete all required fields for the continuous schedule.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
    const startDateObj = new Date(continuousConfig.value.start_date + 'T00:00:00');
    const endDateObj = new Date(continuousConfig.value.end_date + 'T00:00:00');
    if (endDateObj < startDateObj) {
      isSubmitting.value = false;
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Duration',
        text: 'End date cannot be before start date.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
    
    const generated = [];
    let curr = new Date(startDateObj);
    while (curr <= endDateObj) {
      const day = curr.getDay();
      if (!isDisabledDate || !isDisabledDate(curr)) {
        generated.push({
          date: curr.toISOString().split('T')[0],
          start_time: continuousConfig.value.start_time,
          end_time: continuousConfig.value.end_time,
          meals_and_snacks: { ...continuousConfig.value.meals_and_snacks }
        });
      }
      curr.setDate(curr.getDate() + 1);
    }
    if (generated.length === 0) {
      isSubmitting.value = false;
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Schedule',
        text: 'No valid working days found in the selected date range.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
    form.value.schedules = generated;
  }

  if (scheduleType.value === 'staggered') {
    const selectedDates = form.value.schedules.map(schedule => schedule.date).filter(Boolean);
    if (new Set(selectedDates).size !== selectedDates.length) {
      isSubmitting.value = false;
      Swal.fire({
        icon: 'warning',
        title: 'Duplicate Schedule Date',
        text: 'Each non-consecutive schedule must use a different date.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
  }

  if (uploadedFiles.value.length === 0) {
    const confirm = await Swal.fire({
      title: 'No new file selected',
      text: 'Are you sure you want to resubmit without changing the document upload?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#b979cc',
      cancelButtonColor: '#ef4444',
      confirmButtonText: 'Yes, proceed'
    });
    if (!confirm.isConfirmed) { isSubmitting.value = false; return; }
  }

  // Validate participants
  if (Number(form.value.male) < 1 || Number(form.value.female) < 1) {
    isSubmitting.value = false;
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Participants',
      text: 'Male and Female participants must each be at least 1.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  if (form.value.start_time && form.value.end_time && (!form.value.start_date || !form.value.end_date || form.value.start_date === form.value.end_date)) {
    const startTimeParts = form.value.start_time.split(':');
    const endTimeParts = form.value.end_time.split(':');
    const startMinutes = parseInt(startTimeParts[0]) * 60 + parseInt(startTimeParts[1]);
    const endMinutes = parseInt(endTimeParts[0]) * 60 + parseInt(endTimeParts[1]);
    
    if (endMinutes <= startMinutes) {
      isSubmitting.value = false;
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Time Range',
        text: 'End time must be after start time on the same day.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
    
    if ((endMinutes - startMinutes) < 60) {
      isSubmitting.value = false;
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Time Range',
        text: 'The activity duration must be at least 1 hour.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
  }

  // Validate Cause of Gender Issue
  const hasBlankEval = form.value.evaluation_items && form.value.evaluation_items.some(i => !i.area || i.rating === '' || i.rating === null);
  if (hasBlankEval) {
    isSubmitting.value = false;
    Swal.fire({
      icon: 'warning',
      title: 'Missing Evaluation Details',
      text: 'Please ensure all evaluation areas and ratings are filled in before submitting.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }
  if (!form.value.gender_issue_id || form.value.gender_issue_id.length === 0) {
    isSubmitting.value = false;
    Swal.fire({
      icon: 'warning',
      title: 'Missing Field',
      text: 'Please select at least one Cause of Gender Issue before submitting.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  Swal.fire({
    title: 'Processing...',
    text: 'Please wait while we submit your revision and dispatch email notifications.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  try {
    const formData = new FormData();
    
    formData.append('venues', JSON.stringify(form.value.venues || []));
    
    const customVenuesToSave = venues.value.filter(v => String(v.venue_id).startsWith('custom_'));
    if (customVenuesToSave.length > 0) {
       formData.append('custom_venues', JSON.stringify(customVenuesToSave));
    }
    
    // Legacy venue support for single venue display
    const firstVenue = getVenueName(form.value.venues[0]);
    formData.append('venue', firstVenue);
    const normalizedBudgetItems = [];
    form.value.venues.forEach(venue => {
      const vId = resolveVenueId(venue);
      const venueBudget = form.value.venue_budgets[vId] || form.value.venue_budgets[venue];
      if (venueBudget) {
        venueBudget.forEach(item => {
          const lineTotalAmount = (Number(item.rate) || 0) * (item.mult ? item.mult.reduce((p, m) => p * (Number(m.q) || 0), 1) : 1);
          if (item.custom && (!String(item.name).trim() || lineTotalAmount <= 0)) return;
          const isOther = item.custom && item.group === 'materials';
          
          const paxOfItem = Number(((item.mult || []).find(m => /^(pax|person|persons|head|heads)$/i.test(String(m.u || '').trim())) || {}).q) || 0;
          const lineFormulaStr = [('₱' + (Number(item.rate) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })), ...(item.mult ? item.mult.map(m => `${Number(m.q) || 0} ${String(m.u || '').trim()}`.trim()) : [])].join(' x ');
          
          normalizedBudgetItems.push({
            venue_id: vId,
            category_id: null,
            item_name: isOther ? 'Others' : item.name,
            sub_item: isOther ? item.name : lineFormulaStr,
            pax: paxOfItem || null,
            amount: lineTotalAmount
          });
        });
      }
    });

    formData.append('budget_items', JSON.stringify(normalizedBudgetItems));

    const evalMap = {
      "Time Management": "time_management",
      "Orderliness and Program Flow": "orderliness_and_program_flow",
      "Appropriateness of the Venue": "appropriateness_of_venue",
      "Sound System and Hall Preparation": "sound_system_and_hall_preparation",
      "Restroom/s": "restrooms",
      "Food and Drinks": "food_and_drinks"
    };
    const evalObj = {};
    form.value.evaluation_items.forEach(item => {
      if (!item.area) return;
      const dbKey = evalMap[item.area] || item.area.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/(^_|_$)/g, '');
      if (dbKey) {
        evalObj[dbKey] = Number(item.rating) || 0;
      }
    });
        formData.append('evaluation_results', JSON.stringify(evalObj));
    formData.append('schedules', JSON.stringify(form.value.schedules || []));

        if (form.value.activity_classification_id) formData.append('activity_classification_id', form.value.activity_classification_id);
    if (form.value.form_type) formData.append('form_type', form.value.form_type);
    
    formData.append('gad_mandate_id', Array.isArray(form.value.gad_mandate_id) ? form.value.gad_mandate_id.join(',') : form.value.gad_mandate_id);
    formData.append('custom_gad_mandate', customMandate.value);
    
    formData.append('gender_issue_id', Array.isArray(form.value.gender_issue_id) ? form.value.gender_issue_id.join(',') : form.value.gender_issue_id);
    formData.append('custom_gender_issue', customGenderIssue.value);

    Object.keys(form.value).forEach(key => {
      if (key !== 'budget_items' && key !== 'evaluation_items' && key !== 'venue' && key !== 'is_inside_bsu' && key !== 'schedules') {
        formData.append(key, form.value[key]);
      }
    });
    formData.append('is_inside_bsu', form.value.is_inside_bsu ? 1 : 0);
    
    uploadedFiles.value.forEach(file => {
        formData.append('attachments[]', file);
      });
      formData.append('removed_attachments', JSON.stringify(removedAttachments.value));
    
    formData.append('user_id', user.value.id);
    formData.append('status', 'Pending');
    
    const id = route.params.id;
    const response = await api.post(`update-report/${id}`, formData, {
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
        router.push('/staff/ar-list');
      });

    }
  isSubmitting.value = false;
    } catch (error) {
      isSubmitting.value = false;
    console.error('Submission error:', error);
        loading.value = false;
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



const existingReport = ref(null);

const parseAttachments = (attachmentData) => {
  if (!attachmentData) return [];
  try {
    const parsed = JSON.parse(attachmentData);
    return Array.isArray(parsed) ? parsed : [attachmentData];
  } catch (e) {
    return [attachmentData];
  }
};

const parsedARAttachments = computed(() => {
  if (!existingReport.value || !existingReport.value.attachment) return [];
  return parseAttachments(existingReport.value.attachment);
});


const isPdfModalOpen = ref(false);
const pdfFileUrl = ref('');

const closePdfModal = () => {
  isPdfModalOpen.value = false;
};

const getFileURL = (file) => {
  if (!file) return '';
  return URL.createObjectURL(file);
};

const getExistingFileURL = (filename) => {
  if (!filename) return '';
  const base = (import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '') : 'https://gad-ams-2-1.onrender.com');
  // Ensuring no double slashes before api
  const formattedBase = base.endsWith('/') ? base.slice(0, -1) : base;
  return `${formattedBase}/api/files/drafts/${filename}`;
};

const previewFile = (filename, folder) => {
  if (!filename) return;
  const base = (import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '') : 'https://gad-ams-2-1.onrender.com');
  pdfFileUrl.value = `${base}/api/files/${folder}/${filename}`;
  isPdfModalOpen.value = true;
};

const previewNewFile = (file) => {
  if (!file) return;
  pdfFileUrl.value = URL.createObjectURL(file);
  isPdfModalOpen.value = true;
};

const downloadFile = (filename, folder, prefix) => {
  if (!filename) return;
  const base = (import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '') : 'https://gad-ams-2-1.onrender.com');
  const url = `${base}/api/files/${folder}/${filename}`;
  window.open(url, '_blank');
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

const checkTransportationLimit = () => {
  const limit = Number(baselineSettings.value?.transportation_limit || 20000);
  const budgets = form.value.venue_budgets || {};
  for (const vId of Object.keys(budgets)) {
    const transItem = budgets[vId]?.[8];
    if (transItem && Number(transItem.total) > limit) {
      transItem.total = limit;
      const role = user.value?.role || 'staff';
      Swal.fire({
        icon: 'warning',
        title: 'Limit Exceeded',
        html: `Transportation budget cannot exceed the baseline limit of ₱${limit.toLocaleString('en-US')}.<br><br>
               If you need to request an exemption, please <a href="/${role}/messages" style="color: #b979cc; text-decoration: underline; font-weight: bold;">message the GAD Director/Staff</a>.`,
        confirmButtonColor: '#b979cc'
      });
    }
  }
};

const computedStartDate = computed(() => {
  return form.value.start_date ? formatDate(form.value.start_date) : '';
});
const computedEndDate = computed(() => {
  return form.value.end_date ? formatDate(form.value.end_date) : '';
});

const formatTime = (time) => {
  if (!time) return '---';
  const [h, m] = time.split(':');
  return `${h % 12 || 12}:${m} ${h >= 12 ? 'PM' : 'AM'}`;
};


const aDBudget = computed(() => {
  if (!existingReport.value?.activity_design || !existingReport.value.activity_design.budget_items || existingReport.value.activity_design.budget_items.length === 0) return null;
  const b = existingReport.value.activity_design.budget_items[0];
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
    ...b,
    grand_total: grandTotal
  };
});

const parsedADBudget = computed(() => {
  if (!existingReport.value?.activity_design || !existingReport.value.activity_design.budget_items || existingReport.value.activity_design.budget_items.length === 0) return [];
  const b = existingReport.value.activity_design.budget_items[0];
  const items = [
    { name: 'Meals and Snacks (AM/PM)', total: b.meals_and_snacks },
    { name: 'Function Room/Venue', total: b.function_room_venue },
    { name: 'Accommodation', total: b.accommodation },
    { name: 'Equipment Rental', total: b.equipment_rental },
    { name: 'Professional Fee/Honoraria', total: b.professional_fee_honoria },
    { name: 'Token/s', total: b.tokens },
    { name: 'Materials and Supplies', total: b.materials_and_supplies },
    { name: 'Transportation', total: b.transportation }
  ];
  return items.filter(i => parseFloat(i.total) > 0);
});

const fetchReportDetails = async () => {
  try {
    const id = route.params.id;
    const response = await api.get(`activity-report/${id}`);
    if (response.data.success) {
      loadingData.value = true; // Suppress watchers during data population
      existingReport.value = response.data.data;
      
      if (Number(existingReport.value.user_id) !== Number(user.value.id)) {
        Swal.fire({
          icon: 'error',
          title: 'Access Denied',
          text: 'You are not authorized to view or edit this document.',
          confirmButtonColor: '#b979cc'
        }).then(() => {
          router.push('/staff/ar-list');
        });
        loadingData.value = false;
        return;
      }
      
      const r = response.data.data;
      
      if (r.activity_design) {
        form.value.activity_classification_id = r.activity_design.classification_id || '';
        form.value.target_participants = r.activity_design.target_participants || 0;
        await fetchGADMandates();
        
        let ftype = r.activity_design.form_type || '';
        if (ftype && isNaN(ftype)) {
          const found = formTypes.value.find(ft => ft.name === ftype);
          if (found) ftype = found.id;
        }
        form.value.form_type = ftype ? String(ftype) : '';
        
        const savedMandates = r.activity_design.gad_mandate_id ? String(r.activity_design.gad_mandate_id).split(',').map(s=>s.trim()) : [];
        form.value.gad_mandate_id = GADMandates.value.filter(m => {
           const mIds = String(m.id).split(',');
           return mIds.some(id => savedMandates.includes(id));
        }).map(m => String(m.id))[0] || '';
        if (savedMandates.includes('Other') && form.value.gad_mandate_id !== 'Other') {
            form.value.gad_mandate_id = 'Other';
        }

        if (form.value.gad_mandate_id.length > 0) {
          await fetchGenderIssues(form.value.gad_mandate_id);
        }
        
        const savedIssues = r.activity_design.gender_issue_id ? String(r.activity_design.gender_issue_id).split(',').map(s=>s.trim()) : [];
        form.value.gender_issue_id = genderIssues.value.filter(m => {
           const mIds = String(m.id).split(',');
           return mIds.some(id => savedIssues.includes(id));
        }).map(m => String(m.id))[0] || '';
        if (savedIssues.includes('Other') && form.value.gender_issue_id !== 'Other') {
            form.value.gender_issue_id = 'Other';
        }
      }

      form.value.activity_title = r.activity_title || '';
      form.value.start_date = r.start_date || '';
      form.value.end_date = r.end_date || '';
      form.value.start_time = r.start_time || '';
      form.value.end_time = r.end_time || '';
      form.value.venue = r.venue || '';
      form.value.is_inside_bsu = r.is_inside_bsu == 1 || r.is_inside_bsu === true;

      form.value.attendees = r.number_of_attendees || r.attendees || '';
      form.value.male = r.male_participants || r.male || '';
      form.value.female = r.female_participants || r.female || '';
      
      // Populate budget_items from raw expenditures
      if (r.budget_expenditures_raw && Array.isArray(r.budget_expenditures_raw)) {
        // Collect all distinct venue_ids
        let venueIds = Array.from(new Set(r.budget_expenditures_raw.map(b => b.venue_id == null ? 'Other' : String(b.venue_id))));
        
        // Ensure custom venues from activity design (or previously saved) are in local venues state
        const savedVenueLists = [
          ...(r.activity_design?.venues_list || []),
          ...(r.ar_venues_list || [])
        ];
        if (savedVenueLists.length) {
          savedVenueLists.forEach(v => {
            const exists = venues.value.find(lv => String(lv.venue_id) === String(v.venue_id));
            if (!exists) {
              venues.value.push({ venue_id: String(v.venue_id), venue_name: v.venue_name, is_inside_bsu: v.is_inside_bsu });
            }
          });
          
          if (venueIds.length === 0) {
            venueIds = savedVenueLists.map(v => String(v.venue_id));
          }
        }
        
        form.value.venues = venueIds.map(String);
        form.value.venue_budgets = {};
        form.value.custom_others = {};
        
        venueIds.forEach(vid => {
          form.value.venue_budgets[vid] = [];
          form.value.custom_others[vid] = [];
        });

        r.budget_expenditures_raw.forEach(dbItem => {
          let vid = dbItem.venue_id == null ? 'Other' : String(dbItem.venue_id);
          if (!form.value.venue_budgets[vid]) {
            form.value.venue_budgets[vid] = [];
          }
          
          let parsedMult = [];
          try {
            if (dbItem.multipliers) {
              const parsed = typeof dbItem.multipliers === 'string' ? JSON.parse(dbItem.multipliers) : dbItem.multipliers;
              if (Array.isArray(parsed)) {
                parsedMult = parsed.map(m => ({ q: Number(m.value) || 0, u: String(m.label || '').trim() }));
              }
            }
          } catch(e) {}

          const savedAmount = Number(dbItem.amount) || 0;
          let savedRate = Number(dbItem.unit_cost) || 0;
          if (!parsedMult.length && typeof dbItem.sub_item === 'string') {
            const formulaParts = dbItem.sub_item.split(/\s+x\s+/i);
            const formulaRate = formulaParts[0]?.replace(/[₱,\s]/g, '');
            if (formulaParts.length > 1 && Number(formulaRate) >= 0) {
              savedRate = Number(formulaRate);
              parsedMult = formulaParts.slice(1).map(part => {
                const match = part.trim().match(/^(-?\d+(?:\.\d+)?)\s*(.*)$/);
                return match ? { q: Number(match[1]) || 0, u: match[2].trim() } : null;
              }).filter(Boolean);
            }
          }
          if (!savedRate && savedAmount > 0) savedRate = savedAmount;
          if (!parsedMult.length) parsedMult = [{ q: 1, u: 'item' }];
          
          const isCatering = ['Breakfast', 'Lunch', 'Dinner', 'AM Snack', 'PM Snack'].includes(dbItem.item_name);
          const isLogistics = ['Function Room/Venue', 'Accommodation', 'Equipment Rental', 'Transportation'].includes(dbItem.item_name);
          const isProgram = ['Professional Fee/Honoraria', 'Token/s'].includes(dbItem.item_name);
          
          let group = 'materials';
          if (isCatering) group = 'catering';
          else if (isLogistics) group = 'logistics';
          else if (isProgram) group = 'program';
          
          let baseKey = null;
          if (isCatering) baseKey = dbItem.item_name.includes('Snack') ? 'snacks' : 'meals';
          else if (dbItem.item_name === 'Professional Fee/Honoraria') baseKey = 'pf_honoraria';
          else if (dbItem.item_name === 'Token/s') baseKey = 'tokens';
          
          let base = 0;
          if (baseKey === 'meals') base = isOutsideBsu.value ? baselineSettings.value.meals_outside : baselineSettings.value.meals_inside;
          else if (baseKey === 'snacks') base = isOutsideBsu.value ? baselineSettings.value.snacks_outside : baselineSettings.value.snacks_inside;
          else if (baseKey) base = baselineSettings.value[baseKey];
          
          form.value.venue_budgets[vid].push({
            id: Date.now() + Math.random(),
            group,
            name: dbItem.item_name === 'Others' ? dbItem.sub_item : dbItem.item_name,
            rate: savedRate,
            mult: parsedMult,
            custom: dbItem.item_name === 'Others' || isCatering,
            baseKey,
            base,
            capKey: dbItem.item_name === 'Transportation' ? 'transportation_limit' : null
          });
        });
      }

      // Populate evaluation_items from nested array
      const evalMapping = {
        'Time Management': 'time_management',
        'Orderliness and Program Flow': 'orderliness_and_program_flow',
        'Appropriateness of the Venue': 'appropriateness_of_venue',
        'Sound System and Hall Preparation': 'sound_system_and_hall_preparation',
        'Restroom/s': 'restrooms',
        'Food and Drinks': 'food_and_drinks'
      };
      
      if (r.evaluation_results && Array.isArray(r.evaluation_results) && r.evaluation_results.length > 0) {
        const e = r.evaluation_results[0];
        
        const reverseEvalMapping = Object.entries(evalMapping).reduce((acc, [key, value]) => {
          acc[value] = key;
          return acc;
        }, {});

        const newEvalItems = [];
        Object.keys(e).forEach(dbKey => {
           let areaName = reverseEvalMapping[dbKey];
           let isCustom = false;
           if (!areaName) {
              areaName = dbKey.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
              isCustom = true;
           }
           newEvalItems.push({ area: areaName, rating: e[dbKey], isCustom });
        });
        
        if (newEvalItems.length > 0) {
           form.value.evaluation_items = newEvalItems;
        } else {
           form.value.evaluation_items.forEach(item => item.rating = '');
        }
        
        // Compute average rating
        const valid = form.value.evaluation_items.filter(i => i.rating !== '' && !isNaN(parseFloat(i.rating)));
        if (valid.length > 0) {
          const sum = valid.reduce((acc, curr) => acc + parseFloat(curr.rating), 0);
          form.value.rating = (sum / form.value.evaluation_items.length).toFixed(2);
        } else {
          form.value.rating = 0;
        }
      }
      // Populate schedules from nested array
      if (r.schedules && r.schedules.length > 0) {
        let parsedSchedules = r.schedules.map(s => {
          let meals = { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false };
          try {
            if (typeof s.meals_and_snacks === 'string') {
               meals = JSON.parse(s.meals_and_snacks);
            } else if (typeof s.meals_and_snacks === 'object' && s.meals_and_snacks !== null) {
               meals = s.meals_and_snacks;
            }
          } catch(e) {}
          return {
            date: s.schedule_date || s.date,
            start_time: s.start_time ? s.start_time.substring(0, 5) : '',
            end_time: s.end_time ? s.end_time.substring(0, 5) : '',
            meals_and_snacks: meals
          };
        });
        
        // Use the saved schedule_type from the database directly instead of
        // re-detecting it heuristically (which was fragile and caused the
        // consecutive → non-consecutive mismatch when holidays weren't yet loaded).
        const savedScheduleType = r.schedule_type || 'continuous';
        
        if (savedScheduleType === 'continuous' && parsedSchedules.length > 0) {
            scheduleType.value = 'continuous';
            continuousConfig.value = {
                start_date: parsedSchedules[0].date,
                end_date: parsedSchedules[parsedSchedules.length - 1].date,
                start_time: parsedSchedules[0].start_time,
                end_time: parsedSchedules[0].end_time,
                meals_and_snacks: { ...parsedSchedules[0].meals_and_snacks }
            };
            form.value.schedules = parsedSchedules;
        } else {
            scheduleType.value = 'staggered';
            form.value.schedules = parsedSchedules;
        }
      }

      setTimeout(() => { loadingData.value = false; }, 100); // Re-enable watchers
    }
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
    setTimeout(() => { loadingData.value = false; }, 100);
  }
};


const fetchBaselineSettings = async () => {
  try {
    const res = await api.get('settings/baseline');
    if (res.data) {
      baselineSettings.value = res.data;
    }
  } catch (err) {
    console.error('Error fetching baseline settings:', err);
  }
};

onMounted(async () => {
    fetchBaselineSettings();
  if (!user.value.id || user.value.role !== 'gad_staff') {
    router.push('/login');
  } else {
    await fetchData();
    await fetchVenues();
    await fetchReportDetails();
  }
  document.addEventListener('click', closeAllHelp);
});

onUnmounted(() => {
  document.removeEventListener('click', closeAllHelp);
});

</script>

<style scoped>
* { box-sizing: border-box; }
.main-viewport { flex: 1; min-height: 100vh; background: transparent; }
.loading-wrapper { display: flex; justify-content: center; align-items: center; min-height: 400px; }
.loading-spinner { border: 4px solid rgba(255,255,255,0.1); border-left-color: #b979cc; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

.page-container { min-height: 100vh; padding: 2rem; max-width: 80rem; margin: 0 auto; color: #cbd5e1; }
.layout-vertical { display: flex; flex-direction: column; gap: 24px; }
.flex-full { flex: 1; width: 100%; }

.glass-card { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); backdrop-filter: blur(24px); border-radius: 1.5rem; border: 1px solid rgba(185,121,204,0.2); color: #cbd5e1; }

.report-header { padding: 2rem; border-bottom: 1px solid rgba(185,121,204,0.15); background: rgba(0,0,0,0.2); border-radius: 1.5rem 1.5rem 0 0; color: #cbd5e1; }
.meta-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
.report-title { font-size: 26px; color: white; line-height: 1.25; margin: 1rem 0; }
.control-number { font-size: 11px; font-weight: 700; color: #b979cc; text-transform: uppercase; margin-left: 12px; font-family: monospace; }

.status-badge-view { padding: 4px 12px; border-radius: 9999px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; }
.status-badge-view.pending { background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); }
.status-badge-view.approved { background: rgba(59,130,246,0.15); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); }
.status-badge-view.completed { background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); }
.status-badge-view.cancelled { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
.status-badge-view.revision { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }

.info-grid { display: flex; flex-wrap: wrap; gap: 24px; padding-top: 16px; border-top: 1px solid rgba(185,121,204,0.1); }
.info-item { display: flex; flex-direction: column; }
.info-label { font-size: 10px; text-transform: uppercase; color: #cbd5e1; font-weight: 700; margin-bottom: 4px; display: block; }
.info-value-white { font-size: 14px; font-weight: 600; color: white; }
.info-value-purple { font-size: 14px; font-weight: 600; color: #b979cc; }

/* Revision remarks banner */
.revision-remarks-banner { display: flex; align-items: flex-start; gap: 12px; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); border-radius: 12px; padding: 14px 18px; }
.remarks-icon { color: #f87171; font-size: 22px; margin-top: 2px; flex-shrink: 0; }
.remarks-label { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #f87171; margin-bottom: 4px; }
.remarks-text { font-size: 14px; color: #fca5a5; line-height: 1.5; }

.report-body { padding: 2rem; }

/* Side by side layout */
.ar-horizontal-layout { display: flex; flex-direction: column; gap: 24px; }
@media (min-width: 1280px) {
  .ar-horizontal-layout { flex-direction: row; align-items: flex-start; }
  .ar-horizontal-layout > .section-card { flex: 1; min-width: 0; }
}

.section-card { box-sizing: border-box; background: rgba(0,0,0,0.2); border-radius: 16px; padding: 24px; border: 1px solid rgba(185,121,204,0.15); min-width: 0; max-width: 100%; margin-bottom: 24px; }
.section-header-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; }
.section-title { font-weight: 800; font-size: 13px; text-transform: uppercase; color: #b979cc; }
.icon-pink { color: #b979cc; }

.grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.grid-2 > div { min-width: 0; }
@media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
.full-width-info { grid-column: 1 / -1; min-width: 0; }
.text-sm-light { font-size: 14px; color: #cbd5e1; font-weight: 500; }

/* Mandate badge boxes */
.mandate-boxes { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 5px; }
.mandate-box { background: rgba(185,121,204,0.15); border: 1px solid rgba(185,121,204,0.3); color: #f1f5f9; padding: 5px 12px; border-radius: 6px; font-size: 12px; }

/* Editable mandate checkboxes */
.mandate-checkbox-label { display: flex; align-items: center; gap: 6px; cursor: pointer; background: rgba(185,121,204,0.1); border: 1px solid rgba(185,121,204,0.25); padding: 5px 12px; border-radius: 6px; font-size: 12px; color: #f1f5f9; transition: background 0.15s; }
.mandate-checkbox-label:hover { background: rgba(185,121,204,0.2); }
.mandate-checkbox { accent-color: #b979cc; }

/* Input fields */
.custom-input-field { box-sizing: border-box; max-width: 100%; width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px 16px; font-size: 14px; color: #ffffff; transition: all 0.2s ease; }
.custom-input-field:focus { background: rgba(255,255,255,0.05); border-color: #b979cc; outline: none; box-shadow: 0 0 0 3px rgba(185,121,204,0.15); }
.custom-input-field::placeholder { color: #64748b; }
.textarea-no-resize { resize: none; }
.select-arrow-fix { appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23cbd5e1' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
.dark-option { background: #1e293b; color: #fff; }
.code-icon-calendar::-webkit-calendar-picker-indicator,
.code-icon-clock::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; opacity: 0.7; }

/* Tables */
.table-responsive { border-radius: 12px; border: 1px solid rgba(185,121,204,0.15); background: rgba(0,0,0,0.2); overflow-x: auto; }
.custom-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 500px; }
.custom-table th { background: rgba(185,121,204,0.1); color: #b979cc; font-weight: 700; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid rgba(185,121,204,0.15); }
.custom-table td { padding: 12px 16px; color: #cbd5e1; border-bottom: 1px solid rgba(185,121,204,0.05); }
.custom-table tbody tr:last-child td { border-bottom: none; }
.custom-table tfoot td { border-top: 1px solid rgba(185,121,204,0.2); background: rgba(185,121,204,0.08); padding: 12px 16px; }

/* Docs */
.doc-item { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px; background: rgba(0,0,0,0.3); border-radius: 10px; border: 1px solid rgba(185,121,204,0.12); overflow-x: auto; }
.doc-info { display: flex; align-items: center; gap: 10px; }
.doc-pdf-icon { font-size: 28px; color: #ef4444; }
.doc-title { font-size: 13px; font-weight: 700; color: white; word-break: break-all; }
.doc-meta { font-size: 11px; color: #64748b; margin-top: 2px; }
.doc-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.preview-btn { color: #b979cc; font-size: 11px; padding: 6px 14px; border-radius: 8px; background: rgba(0,0,0,0.3); border: 1px solid rgba(185,121,204,0.2); font-weight: 700; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
.preview-btn:hover { border-color: #b979cc; color: white; }
.download-btn-icon { background: rgba(0,0,0,0.3); border: 1px solid rgba(185,121,204,0.15); color: #cbd5e1; padding: 6px 8px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.download-btn-icon:hover { border-color: #b979cc; color: white; }

/* File upload zone */
.doc-upload-area { border: 2px dashed rgba(185,121,204,0.4); border-radius: 12px; padding: 28px 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; cursor: pointer; transition: all 0.2s; background: rgba(185,121,204,0.03); }
.doc-upload-area:hover { border-color: #b979cc; background: rgba(185,121,204,0.07); }
.upload-icon { font-size: 36px; color: #b979cc; }
.upload-text { color: #cbd5e1; font-size: 14px; font-weight: 600; }
.upload-hint { color: #64748b; font-size: 12px; }

/* Submit */
.submit-action-btn { display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%); color: #fff; border: none; border-radius: 12px; padding: 14px 24px; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.2s; width: 100%; }
.submit-action-btn:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(153,13,209,0.35); }
.submit-action-btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }

/* Assessment sidebar */
.assessment-card-custom { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 1.5rem; padding: 2rem; border: 1px solid rgba(185,121,204,0.2); }
.read-only-remarks { width: 100%; border: 1px solid rgba(185,121,204,0.2); border-radius: 12px; padding: 14px 16px; font-size: 13px; background: rgba(0,0,0,0.3); color: #cbd5e1; min-height: 80px; line-height: 1.5; }
.action-buttons { margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(185,121,204,0.15); display: flex; flex-direction: column; gap: 8px; }
.btn-back { width: 100%; padding: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #cbd5e1; border-radius: 12px; background: rgba(0,0,0,0.3); border: 1px solid rgba(185,121,204,0.15); cursor: pointer; transition: all 0.2s; }
.btn-back:hover { color: white; border-color: #b979cc; }

/* Helpers */
.mt-1 { margin-top: 4px; }
.mt-4 { margin-top: 16px; }
.mb-2 { margin-bottom: 8px; }
.mb-4 { margin-bottom: 16px; }
.w-full { width: 100%; }
.mx-auto { margin-left: auto; margin-right: auto; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.font-bold { font-weight: 700; }
.text-white { color: white; }
.text-rose-400 { color: #f472b6; }
.hover\:text-rose-300:hover { color: #fda4af; }
.text-blue-400 { color: #60a5fa; }
.uppercase { text-transform: uppercase; }
.interpretation-tag-ar { font-weight: 600; }
.text-emerald-400 { color: #34d399; }
.text-teal-400 { color: #2dd4bf; }
.text-cyan-400 { color: #22d3ee; }
.text-amber-400 { color: #fbbf24; }
.text-rose-500 { color: #f43f5e; }
.text-rose-600 { color: #e11d48; }
.attachments-list { display: flex; flex-direction: column; gap: 8px; }

/* Budget section styles from SubmitARView */
.budget-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.budget-groups-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 10px;
}
.budget-group-card {
  background: rgba(30, 41, 59, 0.45);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 20px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}
.budget-group-card:hover {
  border-color: rgba(185, 121, 204, 0.3);
  background: rgba(30, 41, 59, 0.6);
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}
.budget-group-header {
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 12px;
  margin-bottom: 16px;
}
.budget-group-icon {
  font-size: 18px;
}
.budget-group-title {
  font-size: 13px;
  font-weight: 700;
  color: #b979cc;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.budget-group-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.budget-row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.budget-row-item:last-child {
  padding-bottom: 0;
  border-bottom: none;
}
.budget-item-info {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex-grow: 1;
}
.budget-item-title {
  font-weight: 600;
  color: #f1f5f9;
  font-size: 14px;
}
.budget-item-subtext {
  font-size: 11px;
  color: #64748b;
}

.budget-sub-number-input {
  width: 50px;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  border-radius: 4px;
  padding: 4px;
  font-size: 11px;
  text-align: center;
}
.budget-sub-number-input:focus {
  outline: none;
  border-color: #b979cc;
}

.budget-item-value {
  display: flex;
  align-items: center;
  gap: 6px;
  width: 240px;
  flex-shrink: 0;
  justify-content: flex-end;
}
.budget-currency-symbol {
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
}
.budget-card-input {
  background-color: rgba(15, 23, 42, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  color: #ffffff;
  font-size: 14px;
  padding: 8px 12px;
  width: 100%;
  text-align: right;
  transition: all 0.2s ease;
  font-weight: 600;
}
.budget-card-input:focus {
  border-color: #b979cc;
  background-color: rgba(15, 23, 42, 0.5);
  box-shadow: 0 0 0 2px rgba(185, 121, 204, 0.2);
  outline: none;
}
.grand-total-banner-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, rgba(185, 121, 204, 0.1) 0%, rgba(153, 13, 209, 0.1) 100%);
  border: 1px solid rgba(185, 121, 204, 0.3);
  border-radius: 14px;
  padding: 20px;
  margin-top: 20px;
  box-shadow: 0 4px 15px -3px rgba(185, 121, 204, 0.1);
}
.grand-total-label-banner {
  font-size: 13px;
  font-weight: 700;
  color: #ffffff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.grand-total-value-banner {
  font-size: 20px;
  font-weight: 800;
  color: #b979cc;
  text-shadow: 0 0 10px rgba(185, 121, 204, 0.2);
}
/* Others breakdown styles */
.others-section-wrapper {
  width: 100%;
}
.others-breakdown-container {
  margin-top: 10px;
  padding: 12px;
  background-color: rgba(0, 0, 0, 0.25);
  border-radius: 10px;
  border: 1px dashed rgba(185, 121, 204, 0.2);
}
.others-breakdown-row {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
  align-items: center;
}
.others-input-name {
  flex: 1;
  background-color: rgba(26, 26, 46, 0.6);
  border: 1px solid rgba(185, 121, 204, 0.2);
  border-radius: 8px;
  padding: 6px 10px;
  color: #ffffff;
  font-size: 12px;
  outline: none;
  box-sizing: border-box;
}
.others-input-amount {
  width: 110px;
  background-color: rgba(26, 26, 46, 0.6);
  border: 1px solid rgba(185, 121, 204, 0.2);
  border-radius: 8px;
  padding: 6px 10px;
  color: #ffffff;
  font-size: 12px;
  outline: none;
  box-sizing: border-box;
}
.others-input-name:focus,
.others-input-amount:focus {
  border-color: #b979cc;
  box-shadow: 0 0 0 2px rgba(185, 121, 204, 0.15);
}
.btn-remove-other {
  background: transparent;
  border: none;
  color: #f43f5e;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4px;
  transition: color 0.2s;
}
.btn-remove-other:hover {
  color: #fda4af;
}
.btn-add-other {
  background-color: rgba(185, 121, 204, 0.1);
  border: 1px solid rgba(185, 121, 204, 0.25);
  color: #b979cc;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  margin-top: 4px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s ease;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}
.btn-add-other:hover {
  background-color: rgba(185, 121, 204, 0.2);
  transform: translateY(-0.5px);
}
.others-total-badge {
  background-color: rgba(185, 121, 204, 0.15);
  border: 1px solid rgba(185, 121, 204, 0.3);
  color: #b979cc;
  padding: 6px 12px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  display: inline-block;
}
/* Form label for budget */
.form-label-ar {
  display: block;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #b979cc;
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

.schedule-inputs-wrapper {
  display: flex;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 16px;
}

.schedule-inputs-wrapper > * {
  min-width: 0;
  width: 100%;
}

@media (max-width: 1024px) {
  .schedule-inputs-wrapper {
    flex-direction: column;
    align-items: stretch;
  }
}
.schedule-type-toggle-container {
  display: flex; 
  background: rgba(0,0,0,0.3); 
  border-radius: 8px; 
  padding: 4px; 
  border: 1px solid rgba(255,255,255,0.05);
  gap: 4px;
}

.schedule-type-toggle-btn {
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  flex: 1;
  text-align: center;
}

@media (max-width: 480px) {
  .schedule-type-toggle-container {
    flex-direction: column;
    align-items: stretch;
  }
}
@media (max-width: 768px) {
  .grand-total-banner-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  .grand-total-value-banner {
    word-break: break-word;
    font-size: 18px;
  }
}
@media (max-width: 768px) {
  .budget-row-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .budget-item-value {
    width: 100%;
    justify-content: flex-start;
  }
  .budget-sub-controls {
    flex-wrap: wrap;
  }
  .others-breakdown-row {
    flex-wrap: wrap;
    gap: 8px;
  }
  .others-input-name {
    width: 100%;
    flex: none;
  }
  .others-input-amount {
    flex: 1;
  }
  .grand-total-banner-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  .grand-total-value-banner {
    word-break: break-word;
    font-size: 18px;
  }
}
/* Venue Multiselect */
.venue-multiselect-container { position: relative; width: 100%; font-family: inherit; }
.venue-multiselect-header { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px 16px; min-height: 48px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.2s ease; }
.venue-multiselect-header:hover, .venue-multiselect-header:focus { background: rgba(255,255,255,0.05); border-color: #b979cc; box-shadow: 0 0 0 3px rgba(185,121,204,0.15); }
.venue-placeholder { color: #64748b; font-size: 14px; }
.venue-chips { display: flex; flex-wrap: wrap; gap: 6px; flex-grow: 1; }
.venue-chip { background: rgba(185,121,204,0.15); border: 1px solid rgba(185,121,204,0.3); color: #e2e8f0; border-radius: 6px; padding: 4px 8px; font-size: 13px; display: inline-flex; align-items: center; }
.venue-chip-remove { background: none; border: none; color: #fca5a5; margin-left: 6px; cursor: pointer; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; padding: 0; width: 16px; height: 16px; border-radius: 50%; transition: color 0.2s; }
.venue-chip-remove:hover { color: #ef4444; }
.venue-dropdown-icon { width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 5px solid #64748b; margin-left: 10px; }
.venue-dropdown-menu { position: absolute; top: calc(100% + 4px); left: 0; width: 100%; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); z-index: 1000; max-height: 250px; overflow-y: auto; padding: 8px 0; }
.venue-dropdown-item { padding: 10px 16px; display: flex; align-items: center; cursor: pointer; color: #e2e8f0; font-size: 14px; transition: background 0.15s; }
.venue-dropdown-item:hover { background: rgba(255,255,255,0.05); }
.venue-checkbox { margin-right: 12px; accent-color: #b979cc; cursor: pointer; width: 16px; height: 16px; }
.other-venue-item { border-top: 1px solid rgba(255,255,255,0.05); margin-top: 4px; padding-top: 10px; }
</style>


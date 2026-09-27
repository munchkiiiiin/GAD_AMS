<template>
  <div style="width: 100%; overflow-x: hidden;">
    <div style="min-height: 100vh; width: 100%;">
  <main class="main-viewport">
    <div v-if="loading" class="loading-wrapper">
      <div class="loading-spinner"></div>
    </div>

    <div v-else-if="error" class="error-view-wrapper">
      <div class="error-card">
        <div class="error-glow"></div>
        <div class="error-icon-container">
          <span class="material-symbols-outlined error-icon" v-if="error.includes('Access Denied')">gpp_bad</span>
          <span class="material-symbols-outlined error-icon" v-else>error</span>
        </div>
        <h2 class="error-heading">
          {{ error.includes('Access Denied') ? 'Access Restricted' : 'Error Loading Data' }}
        </h2>
        <p class="error-text">
          {{ error }}
        </p>
        <button @click="router.back()" class="error-btn-red">
          <span class="material-symbols-outlined btn-icon">arrow_back</span>
          Go Back
        </button>
      </div>
    </div>

    <div v-else class="page-container">
      <!-- Rejection/Revision Note Alert -->
      <div v-if="(design?.modification_remarks || existingReport?.modification_remarks)" class="mb-6 p-5 rounded-xl" style="background: rgba(45, 25, 60, 0.9); border: 1px solid rgba(239, 68, 68, 0.4); border-left: 5px solid #ef4444; box-shadow: 0 8px 24px rgba(0,0,0,0.3); width: 100%; max-width: 80rem; margin: 0 auto 20px auto; box-sizing: border-box;">
        <div style="display: flex; align-items: flex-start; gap: 16px;">
          <span class="material-symbols-outlined" style="color: #ef4444; font-size: 28px; margin-top: 2px;">error</span>
          <div style="flex: 1;">
            <h4 style="color: #ffffff; font-size: 15px; font-weight: 700; margin: 0 0 6px 0; font-family: 'Inter', sans-serif; letter-spacing: 0.5px; text-transform: uppercase;">Rejection / Revision Note</h4>
            <p style="color: #e2e8f0; font-size: 14px; margin: 0; font-family: 'Inter', sans-serif; line-height: 1.6; white-space: pre-wrap;">{{ design?.modification_remarks || existingReport?.modification_remarks }}</p>
          </div>
        </div>
      </div>

      <div class="layout-grid">

        <!-- LEFT SECTION - Edit Form -->
        <section :class="design.status === 'Approved' ? 'flex-100' : 'flex-06'" class="glass-card">
          <div class="report-header">
            <div class="meta-header">
              <div class="status-badge-revision">
                <div class="status-dot-pulse"></div>
                <span class="status-text">Revision Mode</span>
              </div>
              <span class="control-number">{{ design.control || 'PENDING ASSIGNMENT' }}</span>
            </div>

            <div class="form-group-top">
              <label class="form-label">Activity Title</label>
              <input 
                v-model="formData.activity_title" 
                type="text" 
                class="modal-input title-input" 
                placeholder="Enter Activity Title"
              >
            </div>

            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Office / Unit</span>
                <span class="info-value-white">{{ formData.office }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Form Type</span>
                <select v-model="formData.form_type" class="modal-input select-input select-arrow-fix">
                  <option value="" disabled>Select form type...</option>
                  <option v-for="ft in formTypes" :key="ft.id" :value="String(ft.id)" class="dark-option">{{ ft.name }}</option>
                </select>
              </div>
              <div class="info-item">
                <span class="info-label">Activity Classification</span>
                <select v-model="formData.activity_classification" class="modal-input select-input select-arrow-fix">
                  <option value="" disabled>Select classification...</option>
                  <option v-for="c in activityClassifications" :key="c.id" :value="String(c.id)" class="dark-option">{{ c.classification_name }}</option>
                </select>
              </div>
              <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Gender Issue / GAD Mandate *</span>
                <div class="checkbox-group-container modal-input" style="min-height: 120px; max-height: 250px; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 10px;">
                  <label v-for="mandate in gadMandates" :key="mandate.id" class="checkbox-label" style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; color: #ffffff;">
                    <input type="radio" v-model="formData.gad_mandate" :value="mandate.id.toString()" style="margin-top: 2px; accent-color: #b979cc; transform: scale(1.1);" />
                    <span style="font-size: 14px; line-height: 1.4;">{{ mandate.code }} - {{ mandate.title }}</span>
                  </label>
                  
                </div>
                
              </div>
              <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Cause of Gender Issue *</span>
                <div class="checkbox-group-container modal-input" style="min-height: 120px; max-height: 250px; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 10px;">
                  <label v-for="issue in genderIssues" :key="issue.id" class="checkbox-label" style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; color: #ffffff;">
                    <input type="radio" v-model="formData.gender_issue" :value="issue.id.toString()" style="margin-top: 2px; accent-color: #b979cc; transform: scale(1.1);" />
                    <span style="font-size: 14px; line-height: 1.4;">{{ issue.title }}</span>
                  </label>
                  
                  <p v-if="!formData.gad_mandate || formData.gad_mandate.length === 0" style="color: #94a3b8; font-size: 13px; font-style: italic; margin: 0;">Select a mandate first to see gender issues.</p>
                </div>
                
              </div>
            </div>
          </div>

          <div class="report-body">
            <div class="section-card">
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">calendar_month</span>
                <h3 class="section-title">Schedule & Venue</h3>
              </div>

                  <!-- Computed Global Dates -->
                  <div class="form-sub-grid mb-4 mt-4">
                    <div class="input-group">
                      <div class="label-container">
                        <label class="form-label">Calculated Start Date</label>
                        <div class="info-btn-wrapper">
                          <button type="button" class="info-btn" @click.stop="toggleHelp('startDate')">
                            i
                          </button>
                          <transition name="fade-pop">
                            <div v-if="helpState.startDate" class="simple-popup">
                              Ideal submission is 15 working days before this date. Strict minimum is 3 working days.
                            </div>
                          </transition>
                        </div>
                      </div>
                      <div class="custom-input-field" style="display: flex; align-items: center; gap: 8px; opacity: 0.8; cursor: not-allowed;">
                        <span class="material-symbols-outlined" style="font-size: 16px; color: #b979cc;">calendar_month</span>
                        {{ computedStartDate || 'Awaiting schedule...' }}
                      </div>
                    </div>
                    <div class="input-group">
                      <label class="form-label">Calculated End Date</label>
                      <div class="custom-input-field" style="display: flex; align-items: center; gap: 8px; opacity: 0.8; cursor: not-allowed;">
                        <span class="material-symbols-outlined" style="font-size: 16px; color: #b979cc;">event</span>
                        {{ computedEndDate || 'Awaiting schedule...' }}
                      </div>
                    </div>
                  </div>

<!-- Staggered Schedules Section -->
                  <div class="schedules-container" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(185, 121, 204, 0.2); border-radius: 20px; padding: 24px; margin-bottom: 24px;">
                    <div class="flex justify-between items-center mb-4 flex-wrap gap-4">
                      <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                          <label class="form-label !mb-0 flex items-center gap-2" style="white-space: nowrap;">
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
                    
                    <div v-if="schedules.length === 0" style="color: #94a3b8; font-size: 13px; font-style: italic; margin-bottom: 8px;">
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
                    <div v-if="scheduleType === 'continuous' && schedules.length > 0" style="margin-top: 16px; margin-bottom: 8px; color: #b979cc; font-size: 11px; font-weight: bold; display: flex; align-items: center; gap: 4px;">
                      <span class="material-symbols-outlined" style="font-size: 14px;">info</span>
                      You can customize the Time and Meals for specific days (e.g., half-day on the last day) below:
                    </div>
                    <div v-for="(sch, index) in schedules" :key="index" class="schedule-inputs-wrapper" style="margin-bottom: 16px; background: rgba(0,0,0,0.2); padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); position: relative;">
                      <div style="flex: 1; min-width: 0;">
                        <label style="color: #94a3b8; font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 6px; display: block;">Date</label>
                        <VueDatePicker dark v-model="sch.date" @update:model-value="handleScheduleDateChange($event, index)" :disabled="scheduleType === 'continuous'" :min-date="minStartDate" :disabled-dates="isDisabledDate" model-type="yyyy-MM-dd" :enable-time-picker="false" format="MM/dd/yyyy" auto-apply required input-class-name="custom-input-field dp-custom-transparent" :max-date="maxDateLimit" >
<template #dp-input="{ value }">
<input type="text" :value="value ? String(value).replace(',', '').trim().split(' ')[0] : ''" class="custom-input-field dp-custom-transparent !text-xs !p-2" readonly placeholder="Select Date" />
</template>
</VueDatePicker>
                      </div>
                      <div style="flex: 1; min-width: 0;">
                        <div class="label-container" style="margin-bottom: 6px;">
                          <label style="color: #94a3b8; font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 0;">Start Time</label>
                          <div class="info-btn-wrapper">
                            <span class="material-symbols-outlined" @click.stop="toggleHelp('startTime')" style="font-size: 14px; cursor: pointer; color: #94a3b8; transition: color 0.2s;" onmouseover="this.style.color='#b979cc'" onmouseout="this.style.color='#94a3b8'">info</span>
                            <transition name="fade-pop"><div v-if="helpState.startTime" class="simple-popup" style="width:160px; font-size:10px; font-weight:normal;">Valid times: 04:00 AM - 08:00 PM</div></transition>
                          </div>
                        </div>
                        <input type="time" v-model="sch.start_time" min="04:00" max="20:00" required class="custom-input-field" style="color-scheme: dark; cursor: pointer;" @change="validateScheduleTime(index)">
                      </div>
                      <div style="flex: 1; min-width: 0;">
                        <div class="label-container" style="margin-bottom: 6px;">
                          <label style="color: #94a3b8; font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 0;">End Time</label>
                          <div class="info-btn-wrapper">
                            <span class="material-symbols-outlined" @click.stop="toggleHelp('endTime')" style="font-size: 14px; cursor: pointer; color: #94a3b8; transition: color 0.2s;" onmouseover="this.style.color='#b979cc'" onmouseout="this.style.color='#94a3b8'">info</span>
                            <transition name="fade-pop"><div v-if="helpState.endTime" class="simple-popup" style="width:160px; font-size:10px; font-weight:normal;">Valid times: 04:00 AM - 08:00 PM</div></transition>
                          </div>
                        </div>
                        <input type="time" v-model="sch.end_time" min="04:00" max="20:00" required class="custom-input-field" style="color-scheme: dark; cursor: pointer;" @change="validateScheduleTime(index)">
                      </div>
                      <button type="button" v-if="scheduleType === 'staggered' && schedules.length > 1" @click.prevent="removeSchedule(index)" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" title="Remove Schedule">
                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                      </button>
                      
                    </div>
                    
                    
                  </div>

              <div class="venue-participants-row">
                <div class="venue-col">
                  <label class="form-label">Venue *</label>
                  <div class="custom-multiselect-container">
                    <div v-if="venueDropdownOpen" class="multiselect-backdrop" @click="venueDropdownOpen = false"></div>
                    <div class="custom-input-field multiselect-trigger" @click="venueDropdownOpen = !venueDropdownOpen" :class="{ 'is-open': venueDropdownOpen }">
                      <span v-if="!formData.venues || formData.venues.length === 0" class="placeholder-text">Select venues...</span>
                      <span v-else class="selected-text">{{ formData.venues.length }} venue(s) selected</span>
                      <span class="dropdown-arrow">&#9660;</span>
                    </div>
                    <div v-if="venueDropdownOpen" class="multiselect-menu">
                      <label v-for="v in filteredVenues" :key="v.venue_id" class="multiselect-option">
                        <input type="checkbox" :value="v.venue_id" v-model="formData.venues" class="multiselect-checkbox" />
                        <span>{{ v.venue_name }}</span>
                      </label>
                      <div class="multiselect-divider"></div>
                      <button type="button" class="btn-add-custom-venue" @click.prevent="addCustomVenue">+ Add Custom Venue</button>
                    </div>
                    <div class="chips-container" v-if="formData.venues && formData.venues.length > 0">
                      <div v-for="vid in formData.venues" :key="vid" class="venue-chip">
                        <span class="chip-text">{{ getVenueName(vid) }}</span>
                        <button type="button" class="chip-remove" @click.stop="removeVenue(vid)">x</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="venue-col">
                  <label class="form-label">Venue Location *</label>
                  <div class="toggle-container" style="display: flex; gap: 1rem; align-items: center; height: 42px;">
                    <label style="color: #cbd5e1; font-size: 14px; cursor: pointer;">
                      <input type="radio" :value="true" v-model="formData.is_inside_bsu" style="accent-color: #b979cc; transform: scale(1.1); margin-right: 5px;" /> Inside BSU
                    </label>
                    <label style="color: #cbd5e1; font-size: 14px; cursor: pointer;">
                      <input type="radio" :value="false" v-model="formData.is_inside_bsu" style="accent-color: #b979cc; transform: scale(1.1); margin-right: 5px;" /> Outside BSU
                    </label>
                  </div>
                </div></div></div>

            
            <div class="section-card">
              <!-- Overall Target Participants Banner -->
              <div class="grand-total-banner-card" style="background: rgba(30,41,59,0.7); margin-bottom: 12px; border-color: #334155; padding: 12px 20px;">
                <div class="grand-total-label-banner" style="color: #94a3b8; font-size: 13px;">Overall Expected Attendance (Auto-calculated)</div>
                <div class="grand-total-value-banner" style="color: #cbd5e1; font-size: 16px;">
                  {{ formData.target_participants || 0 }} Pax
                </div>
              </div>
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">payments</span>
                <h3 class="section-title">Proposed Budgetary Requirements</h3>
              </div>

              <!-- Proposed Budgetary Requirements Table (Refactored Inline) -->
              <div v-if="formData.venues && formData.venues.length" class="budget-content">
                    <BudgetBuilder 
                      ref="budgetBuilder"
                      v-model:venueBudgets="formData.venue_budgets" 
                      :venues="formData.venues" 
                      :baselineSettings="baselineSettings"
                      :isOutsideBsu="isOutsideBsu" 
                      :computedDays="computedDays"
                      :filteredVenues="venues"
                      :customVenuesList="customVenuesList"
                      label=""
                    />

                    <!-- Overall Target Participants Banner -->
                    <div class="grand-total-banner-card" style="background: rgba(30,41,59,0.7); margin-bottom: 12px; border-color: #334155; padding: 12px 20px;">
                      <div class="grand-total-label-banner" style="color: #94a3b8; font-size: 13px;">Overall Expected Attendance (Auto-calculated)</div>
                      <div class="grand-total-value-banner" style="color: #cbd5e1; font-size: 16px;">
                        {{ formData.target_participants || 0 }} Pax
                      </div>
                    </div>

                    <!-- Grand Total Banner Card -->
                    <div class="grand-total-banner-card">
                      <div class="grand-total-label-banner">Grand Total (PHP)</div>
                      <div class="grand-total-value-banner">
                        ₱{{ Number(formData.proposed_budget || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                      </div>
                    </div>
              </div>
              <div v-else class="empty-budget-notice">
                No budgetary requirements were specified for this design.
              </div>
            </div>

            <div class="section-card">
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">description</span>
                <h3 class="section-title">Supporting Documents</h3>
              </div>
              <div class="doc-item">
                <div class="doc-info">
                  <span class="material-symbols-outlined doc-pdf-icon">picture_as_pdf</span>
                  <div>
                    <p class="doc-title" v-if="!newFile">{{ design.attachment || 'No file uploaded' }}</p>
                    <p class="doc-title" v-else>{{ newFile.name }}</p>
                    <p class="doc-meta" v-if="design.attachment && !newFile">Current File</p>
                  </div>
                </div>
                <label class="preview-btn">
                  <span>Change File</span>
                  <input type="file" @change="handleFileChange" class="file-input-hidden" accept=".pdf,.doc,.docx">
                </label>
              </div>

              <!-- Document Previews -->
              <div class="document-previews" style="margin-top: 15px;">
                <div v-if="design.attachment" style="margin-bottom: 20px;">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <p style="color: #cbd5e1; font-size: 13px; font-weight: bold; margin: 0;">Previous Document:</p>
                    <button type="button" @click.prevent="expandToNewTab(`${api.defaults.baseURL}/files/${Number(design.is_archived) === 1 ? 'archived' : 'drafts'}/${design.attachment}`)" style="background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #cbd5e1; padding: 4px 12px; border-radius: 4px; font-size: 11px; cursor: pointer; display: flex; align-items: center;">
                      <span class="material-symbols-outlined" style="font-size: 14px; margin-right: 4px;">open_in_new</span> Expand
                    </button>
                  </div>
                  <iframe :src="getPdfViewerUrl(`${api.defaults.baseURL}/files/${Number(design.is_archived) === 1 ? 'archived' : 'drafts'}/${design.attachment}`)" width="100%" height="400px" style="border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;"></iframe>
                </div>
                <div v-if="newFileURL">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <p style="color: #b979cc; font-size: 13px; font-weight: bold; margin: 0;">New Document Preview:</p>
                    <button type="button" @click.prevent="expandToNewTab(newFileURL)" style="background: transparent; border: 1px solid rgba(185,121,204,0.4); color: #b979cc; padding: 4px 12px; border-radius: 4px; font-size: 11px; cursor: pointer; display: flex; align-items: center;">
                      <span class="material-symbols-outlined" style="font-size: 14px; margin-right: 4px;">open_in_new</span> Expand
                    </button>
                  </div>
                  <iframe :src="getPdfViewerUrl(newFileURL)" width="100%" height="400px" style="border: 1px solid #b979cc; border-radius: 8px;"></iframe>
                </div>
              </div>
            </div>
            
            <div v-if="design.status === 'Approved'" class="assessment-card-custom" style="margin-top: 24px;">
              <div class="assessment-header">
                <div class="assessment-icon">📋</div>
                <div class="assessment-title">Modification Actions</div>
              </div>
              <div class="assessment-form">
                <div class="action-buttons" style="margin-top: 0; padding-top: 0; border-top: none;">
                  <button @click="handleUpdate" class="btn-approve" :disabled="submitting">
                    <span class="material-symbols-outlined">send</span> 
                    {{ submitting ? 'Updating...' : 'Save and Update Document' }}
                  </button>
                  <button @click="router.back()" class="btn-back">
                    Cancel Changes
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section v-if="design.status !== 'Approved'" class="flex-04-sidebar">
          <div class="assessment-card-custom">
            <div class="assessment-header">
              <div class="assessment-icon">📋</div>
              <div class="assessment-title">Reviewer Feedback</div>
            </div>

            <div class="assessment-form">
              <div v-if="design.accomplishment_deadline" class="info-item assessment-date-display">
                <span class="info-label">Accomplishment Deadline</span>
                <span class="info-value-white">{{ formatDate(design.accomplishment_deadline) }}</span>
              </div>

              <div class="info-item assessment-date-display">
                <span class="info-label">Date of Assessment</span>
                <span class="info-value-white">{{ formatDate(design.assessment_date) }}</span>
              </div>

              <div class="info-item feedback-remarks">
                <span class="info-label">Previous Remarks</span>
                <div class="read-only-remarks">
                  {{ design.remarks || 'No remarks provided.' }}
                </div>
              </div>

              <div class="action-buttons">
                <button @click="handleUpdate" class="btn-approve" :disabled="submitting">
                  <span class="material-symbols-outlined">send</span> 
                  {{ submitting ? 'Updating...' : 'Resubmit for Review' }}
                </button>
                <button @click="router.back()" class="btn-back">
                  Cancel Changes
                </button>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </main>
    </div>
  </div>
</template>

<script setup>
import { useHolidays } from '../../utils/useHolidays';
const { isDisabledDate, fetchHolidays, getWorkingDaysDiff, addWorkingDays } = useHolidays();
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import PdfPreviewModal from '../../components/PdfPreviewModal.vue';
import Swal from 'sweetalert2';
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

const route = useRoute();
const router = useRouter();
const design = ref({});
const loading = ref(true);
const submitting = ref(false);
const error = ref(null);
const venues = ref([]);


const scheduleType = ref('staggered');
const continuousConfig = ref({
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  meals_and_snacks: { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false }
});

const validateTimeBounds = (timeStr, fieldName, schObj, fieldKey) => {
  if (!timeStr) return true;
  const [h, m] = timeStr.split(':').map(Number);
  if (h < 4 || h > 20 || (h === 20 && m > 0)) {
    document.activeElement?.blur();
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Time',
      text: `${fieldName} must be between 04:00 AM and 08:00 PM.`,
      confirmButtonColor: '#b979cc'
    });
    schObj[fieldKey] = '';
    return false;
  }
  return true;
};

const handleTimeChange = (sch) => {
  if (sch.start_time && !validateTimeBounds(sch.start_time, 'Start Time', sch, 'start_time')) return;
  if (sch.end_time && !validateTimeBounds(sch.end_time, 'End Time', sch, 'end_time')) return;
  
  if (sch.start_time && sch.end_time) {
    const startTimeParts = sch.start_time.split(':');
    const endTimeParts = sch.end_time.split(':');
    const startMinutes = parseInt(startTimeParts[0]) * 60 + parseInt(startTimeParts[1]);
    const endMinutes = parseInt(endTimeParts[0]) * 60 + parseInt(endTimeParts[1]);
    
    if (endMinutes <= startMinutes) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Time Range', text: 'End time must be after start time.', confirmButtonColor: '#b979cc' });
      sch.end_time = '';
      return;
    } else if ((endMinutes - startMinutes) < 60) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Time Range', text: 'The activity duration must be at least 1 hour.', confirmButtonColor: '#b979cc' });
      sch.end_time = '';
      return;
    }
  }

  if (!sch.start_time || !sch.end_time) return;
  const [startH] = sch.start_time.split(':').map(Number);
  const [endH, endM] = sch.end_time.split(':').map(Number);
  if (startH >= 13) {
    sch.meals_and_snacks.breakfast = false;
    sch.meals_and_snacks.am_snack = false;
  }
  if (endH < 12 || (endH === 12 && endM === 0)) {
    sch.meals_and_snacks.pm_snack = false;
    sch.meals_and_snacks.dinner = false;
  }
};

const generateConsecutiveSchedules = () => {
  if (scheduleType.value !== 'continuous') return;
  if (!continuousConfig.value.start_date || !continuousConfig.value.end_date) {
    return;
  }
  
  const start = new Date(continuousConfig.value.start_date);
  const end = new Date(continuousConfig.value.end_date);
  
  if (start > end) return;
  
  const generated = [];
  let current = new Date(start);
  while (current <= end) {
    if (isDisabledDate(current)) {
      current.setDate(current.getDate() + 1);
      continue;
    }
    const dateStr = current.toISOString().split('T')[0];
    
    // Update existing schedules with the latest master time, overriding any manual edits to ensure consistency when the master config changes.
    const existing = schedules.value.find(s => s.date === dateStr);
    if (existing) {
      generated.push({ 
        ...existing,
        start_time: continuousConfig.value.start_time,
        end_time: continuousConfig.value.end_time,
        meals_and_snacks: { ...continuousConfig.value.meals_and_snacks }
      });
    } else {
      generated.push({
        date: dateStr,
        start_time: continuousConfig.value.start_time,
        end_time: continuousConfig.value.end_time,
        meals_and_snacks: { ...continuousConfig.value.meals_and_snacks }
      });
    }
    
    current.setDate(current.getDate() + 1);
  }
  schedules.value = generated;
};

const handleScheduleTypeChange = (newType) => {
  if (scheduleType.value === newType) return;
  scheduleType.value = newType;
  
  continuousConfig.value = {
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    meals_and_snacks: { breakfast: false, am_snack: false, pm_snack: false, dinner: false }
  };
  
  schedules.value = [{
    date: '',
    start_time: '',
    end_time: '',
    meals_and_snacks: { breakfast: false, am_snack: false, pm_snack: false, dinner: false }
  }];
};

const handleScheduleDateChange = (date, index) => {
  if (scheduleType.value !== 'staggered' || !date) return;
  const duplicate = schedules.value.some((schedule, scheduleIndex) => scheduleIndex !== index && schedule.date === date);
  if (duplicate) {
    schedules.value[index].date = '';
    Swal.fire({ icon: 'warning', title: 'Duplicate Schedule Date', text: 'Each non-consecutive schedule must use a different date.', confirmButtonColor: '#b979cc' });
  }
};

watch(continuousConfig, () => {
  if (loadingData.value) return;
  generateConsecutiveSchedules();
}, { deep: true });



const computedStartDate = computed(() => {
  if (!schedules.value || schedules.value.length === 0) return '';
  const validDates = schedules.value.map(s => s.date).filter(Boolean);
  if (validDates.length === 0) return '';
  return validDates.sort((a, b) => new Date(a) - new Date(b))[0];
});

const computedEndDate = computed(() => {
  if (!schedules.value || schedules.value.length === 0) return '';
  const validDates = schedules.value.map(s => s.date).filter(Boolean);
  if (validDates.length === 0) return '';
  return validDates.sort((a, b) => new Date(a) - new Date(b))[validDates.length - 1];
});

const addSchedule = () => {
  schedules.value.push({ date: '', start_time: '', end_time: '', meals_and_snacks: { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false } });
};

const removeSchedule = (index) => {
  if (schedules.value.length > 1) {
    schedules.value.splice(index, 1);
  }
};

const validateScheduleTime = (index) => {
  const sch = schedules.value[index];
  handleTimeChange(sch);
};

// Also we need schedules and activeSchedules if not present
const schedules = ref([]);


const helpState = ref({
  startTime: false,
  endTime: false,
  startDate: false
});

const toggleHelp = (key) => {
  helpState.value[key] = !helpState.value[key];
  setTimeout(() => { helpState.value[key] = false; }, 3000);
};

const activeSchedules = computed(() => {
    return schedules.value.filter(s => s.date && s.start_time && s.end_time);
});

const customVenue = ref('');
const venueDropdownOpen = ref(false);
const customVenuesList = ref([]);
const venueOthersList = ref({});

const getVenueName = (id) => {
  if (String(id).startsWith('temp_')) {
    const custom = customVenuesList.value.find(x => String(x.venue_id) === String(id));
    return custom ? custom.venue_name : 'Custom Venue';
  }
  const v = filteredVenues.value.find(x => String(x.venue_id) === String(id));
  return v ? v.venue_name : 'Unknown Venue';
};

const removeVenue = (vid) => {
  formData.value.venues = formData.value.venues.filter(id => String(id) !== String(vid));
};

const addCustomVenue = async () => {
  venueDropdownOpen.value = false;
  const { value: venueName } = await Swal.fire({
    title: 'Add Custom Venue',
    input: 'text',
    inputLabel: 'Enter the complete venue name',
    inputPlaceholder: 'e.g. Hotel ABC',
    showCancelButton: true,
    confirmButtonColor: '#b979cc',
    cancelButtonColor: '#64748b',
    inputValidator: (value) => {
      if (!value) return 'You need to write something!';
    }
  });
  if (venueName) {
    const tempId = 'temp_' + Date.now();
    customVenuesList.value.push({ venue_id: tempId, venue_name: venueName });
    if (!formData.value.venues) formData.value.venues = [];
    formData.value.venues.push(tempId);
  }
};

const addOtherItem = (vId) => {
  if (!venueOthersList.value[vId]) venueOthersList.value[vId] = [];
  venueOthersList.value[vId].push({ name: '', amount: '' });
};

const removeOtherItem = (vId, index) => {
  if (venueOthersList.value[vId]) {
    venueOthersList.value[vId].splice(index, 1);
  }
};
const newFile = ref(null);
const newFileURL = ref(null);

const formTypes = ref([]);
const activityClassifications = ref([]);
const gadMandates = ref([]);
const genderIssues = ref([]);


const maxDateLimit = computed(() => {
  const currentYear = new Date().getFullYear();
  return `${currentYear}-12-31`;
});
const minStartDate = computed(() => {
  const today = new Date(new Date().toLocaleString("en-US", { timeZone: "Asia/Manila" }));
  today.setHours(0, 0, 0, 0);
  const targetDate = addWorkingDays(today, 3);
  const yearStr = targetDate.getFullYear();
  const monthStr = String(targetDate.getMonth() + 1).padStart(2, '0');
  const dayStr = String(targetDate.getDate()).padStart(2, '0');
  return `${yearStr}-${monthStr}-${dayStr}`;
});

const formData = ref({
  activity_title: '',
  office: '',
  form_type: '',
  activity_classification: '',
  gad_mandate: '',
  gender_issue: '',
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  venues: [],
  venue_budgets: {},
  venue: '',
  is_inside_bsu: true,
  target_participants: 0,
  proposed_budget: 0,
  budget_items: [
    { name: 'Meals', total: 0 },
    { name: 'Snacks', total: 0 },
    { name: 'Function Room/Venue', total: 0 },
    { name: 'Accommodation', total: 0 },
    { name: 'Equipment Rental', total: 0 },
    { name: 'Professional Fee/Honoraria', total: 0 },
    { name: 'Token/s', total: 0 },
    { name: 'Materials and Supplies', total: 0 },
    { name: 'Transportation', total: 0 },
    { name: 'Others', total: 0 }
  ]
});



// Watch venue_budgets for grand total + auto-calculated participants
watch(() => formData.value.venue_budgets, (newBudgets) => {
  if (loadingData.value) return;
  let grandTotal = 0;
  let maxOverallPax = 0;
  
  if (newBudgets) {
    Object.values(newBudgets).forEach(items => {
      grandTotal += items.reduce((sum, item) => {
        const mults = item.mult ? item.mult.reduce((p, m) => p * (Number(m.q) || 0), 1) : 0;
        return sum + ((Number(item.rate) || 0) * mults);
      }, 0);
      
      let venueMaxPax = 0;
      items.forEach(item => {
        const paxMult = item.mult ? item.mult.find(m => /^(pax|person|persons|head|heads)$/i.test(String(m.u || '').trim())) : null;
        if (paxMult && Number(paxMult.q) > venueMaxPax) {
          venueMaxPax = Number(paxMult.q);
        }
      });
      maxOverallPax += venueMaxPax;
    });
  }
  
  formData.value.proposed_budget = grandTotal;
  formData.value.target_participants = maxOverallPax > 0 ? maxOverallPax : '';
}, { deep: true });

// Computed Properties for Auto-calculation

// Computed Properties for Auto-calculation




const totalBreakfastDays = computed(() => activeSchedules.value.filter(s => s.meals_and_snacks?.breakfast).length);
const totalAMSnackDays = computed(() => activeSchedules.value.filter(s => s.meals_and_snacks?.am_snack).length);
const totalLunchDays = computed(() => activeSchedules.value.filter(s => s.meals_and_snacks?.lunch).length);
const totalPMSnackDays = computed(() => activeSchedules.value.filter(s => s.meals_and_snacks?.pm_snack).length);
const totalDinnerDays = computed(() => activeSchedules.value.filter(s => s.meals_and_snacks?.dinner).length);
const totalMealsDays = computed(() => activeSchedules.value.filter(s => s.meals_and_snacks?.breakfast || s.meals_and_snacks?.lunch || s.meals_and_snacks?.dinner).length);
const totalSnacksDays = computed(() => activeSchedules.value.filter(s => s.meals_and_snacks?.am_snack || s.meals_and_snacks?.pm_snack).length);

const computedDays = computed(() => {
  let totalDays = 0;
  activeSchedules.value.forEach(s => {
    if (!s.date) return;

    if (!s.start_time || !s.end_time) {
      totalDays += 1;
      return;
    }

    const [h1, m1] = s.start_time.split(':').map(Number);
    const [h2, m2] = s.end_time.split(':').map(Number);
    const hours = (h2 + m2 / 60) - (h1 + m1 / 60);

    if (hours > 0 && hours <= 4) {
      totalDays += 0.5;
    } else {
      totalDays += 1;
    }
  });
  return totalDays > 0 ? totalDays : 1;
});

const baselineSettings = ref({
  meals_inside: 220,
  meals_outside: 350,
  snacks_inside: 60,
  snacks_outside: 100,
  pf_honoraria: 2258.25,
  tokens: 1000,
  materials: 120,
  transportation_limit: 20000
});

const fetchBaselineSettings = async () => {
  try {
    const res = await api.get('settings/baseline');
    if (res.data) {
      baselineSettings.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load baseline settings:', err);
  }
};

onMounted(() => {
  fetchBaselineSettings();
});

const isOutsideBsu = computed(() => {
  return !formData.value.is_inside_bsu;
});

// Reactive Sub-controls State


const pfPax = ref('');
const tokensPax = ref('');
const othersList = ref([]);
const customMandate = ref('');
const customGenderIssue = ref('');
const loadingData = ref(false);

// Reactive Auto-computation Watchers
watch(
  [
    () => formData.value.venue_budgets,
    () => isOutsideBsu.value,
    () => baselineSettings.value,
    computedDays
  ],
  ([newBudgets, isOutside, settings, days]) => {
    if (loadingData.value || !newBudgets) return;
    
    if (typeof newBudgets !== 'object') return;
    
    const mealsRate = isOutside ? settings.meals_outside : settings.meals_inside;
    const snacksRate = isOutside ? settings.snacks_outside : settings.snacks_inside;
    
    Object.values(newBudgets).forEach(budgetItems => {
      // Find Meals item
      const mealsItem = budgetItems.find(i => i.name === 'Meals');
      if (mealsItem && mealsItem.meals_needed) {
        const { breakfast, lunch, dinner } = mealsItem.meals_needed;
        const bPax = Number(breakfast) || 0;
        const lPax = Number(lunch) || 0;
        const dPax = Number(dinner) || 0;
        const calculated = ((bPax + lPax + dPax) * mealsRate * (days || 1));
        mealsItem.total = calculated || '';
      }

      // Find Snacks item
      const snacksItem = budgetItems.find(i => i.name === 'Snacks');
      if (snacksItem && snacksItem.meals_needed) {
        const { am_snack, pm_snack } = snacksItem.meals_needed;
        const amPax = Number(am_snack) || 0;
        const pmPax = Number(pm_snack) || 0;
        const calculated = ((amPax + pmPax) * snacksRate * (days || 1));
        snacksItem.total = calculated || '';
      }
    });
  },
  { deep: true }
);

watch([pfPax, baselineSettings], ([newPax, _]) => {
  if (loadingData.value) return;
  const item = formData.value.budget_items.find(i => i.name === 'Professional Fee/Honoraria');
  if (item) {
    item.total = (Number(newPax) * baselineSettings.value.pf_honoraria) || '';
  }
});

watch([tokensPax, baselineSettings], ([newPax, _]) => {
  if (loadingData.value) return;
  const item = formData.value.budget_items.find(i => i.name === 'Token/s');
  if (item) {
    item.total = (Number(newPax) * baselineSettings.value.tokens) || '';
  }
});

watch([() => formData.value.target_participants, baselineSettings], ([newPax, _]) => {
  if (loadingData.value) return;
  const item = formData.value.budget_items.find(i => i.name === 'Materials and Supplies');
  if (item) {
    item.total = (Number(newPax) * baselineSettings.value.materials) || '';
  }
});

watch(
  othersList,
  (newList) => {
    if (loadingData.value) return;
    const item = formData.value.budget_items.find(i => i.name === 'Others');
    if (item) {
      const sum = newList.reduce((sum, i) => sum + (Number(i.amount) || 0), 0);
      item.total = sum || '';
    }
  },
  { deep: true }
);

const fetchVenues = async () => {
  try {
    const response = await api.get('venues');
    venues.value = response.data || [];
  } catch (err) {
    console.error('Error fetching venues:', err);
  }
};

const filteredVenues = computed(() => {
  return venues.value.filter(v => 
    (v.is_inside_bsu == 1 || v.is_inside_bsu === true) === formData.value.is_inside_bsu &&
    v.venue_name && v.venue_name.trim() !== ''
  );
});

watch(() => formData.value.is_inside_bsu, () => {
  if (loadingData.value) return;
  if (formData.value.venues && formData.value.venues.length > 0) {
    const validIds = new Set(filteredVenues.value.map(v => String(v.venue_id)));
    formData.value.venues = formData.value.venues.filter(id => String(id).startsWith('temp_') || validIds.has(String(id)));
  }
});

watch(() => formData.value.is_inside_bsu, () => {
  if (loadingData.value) return;
  if (formData.value.venue && formData.value.venue !== 'Other') {
    const isValid = filteredVenues.value.some(v => v.venue_id == formData.value.venue);
    if (!isValid) {
      formData.value.venue = '';
    }
  }
});

const fetchFormTypes = async () => {
  try {
    const response = await api.get('get-form-types');
    if (response.data) formTypes.value = response.data;
  } catch (error) {
    console.error('Error fetching form types:', error);
  }
};

const fetchActivityClassifications = async () => {
  try {
    const response = await api.get('get-activity-classifications');
    if (response.data) activityClassifications.value = response.data;
  } catch (error) {
    console.error('Error fetching classifications:', error);
  }
};

const fetchGADMandates = async () => {
    try {
      let url = 'get-gad-mandates';
      if (formData.value && formData.value.activity_classification) {
          url += '?classification=' + formData.value.activity_classification;
      }
      const response = await api.get(url);
    if (response.data) gadMandates.value = response.data;
  } catch (error) {
    console.error('Error fetching mandates:', error);
  }
};

const fetchGenderIssues = async (mandateIds) => {
  let rawIds = mandateIds || formData.value?.gad_mandate || [];
  if (typeof rawIds === 'string' || typeof rawIds === 'number') rawIds = [rawIds];
  const ids = rawIds.filter(id => id !== 'Other');
  if (!ids || !Array.isArray(ids) || ids.length === 0) {
    genderIssues.value = [];
    return;
  }
  try {
    const idString = ids.join(',');
    let url = `get-gender-issues?mandates=${encodeURIComponent(idString)}`;
    if (formData.value && formData.value.activity_classification) {
      url += '&classification=' + formData.value.activity_classification;
    }
    const res = await api.get(url);
    genderIssues.value = res.data || [];
  } catch (error) {
    console.error('Error fetching gender issues:', error);
    genderIssues.value = [];
  }
};

const fetchDesignDetails = async () => {
  loading.value = true;
  loadingData.value = true;
  try {
    const id = route.params.id;
    const response = await api.get(`activity-design/${id}`);
    if (response.data.success) {
      design.value = response.data.data;
      
      if (Number(design.value.user_id) !== Number(user.value.id)) {
        Swal.fire({
          icon: 'error',
          title: 'Access Denied',
          text: 'You are not authorized to view or edit this document.',
          confirmButtonColor: '#b979cc'
        }).then(() => {
          router.push('/college/ad-list');
        });
        return;
      }
      // Initialize default structures
              const dbMealsItem = design.value.budget_items?.find(i => i.item_name === 'Meals') || {};
        const dbSnacksItem = design.value.budget_items?.find(i => i.item_name === 'Snacks') || {};
        const mealsSub = dbMealsItem.sub_item || '';
        const snacksSub = dbSnacksItem.sub_item || '';
        
        
        
      pfPax.value = Number(design.value.pf_pax) || '';
      tokensPax.value = Number(design.value.tokens_pax) || '';
      if (design.value.materials_others_breakdown) {
        try {
          othersList.value = JSON.parse(design.value.materials_others_breakdown);
        } catch(e) {
          othersList.value = [];
        }
      } else {
        othersList.value = [];
      }

      const dbMeals = Number(design.value.meals_total || dbMealsItem.amount || 0);
      const dbSnacks = Number(design.value.snacks_total || dbSnacksItem.amount || 0);
      const legacyMealsSnacks = Number(design.value.meals_and_snacks || 0);
      let mealsTotal = (dbMeals === 0 && dbSnacks === 0 && legacyMealsSnacks > 0) ? legacyMealsSnacks : dbMeals;
      let snacksTotal = dbSnacks || '';

      const getBudgetItemAmount = (itemName) => {
        const item = design.value.budget_items?.find(i => i.item_name === itemName);
        return Number(item?.amount || 0);
      };

      const dbMat = Number(design.value.materials_total || getBudgetItemAmount('Materials and Supplies') || 0);
      let ob = [];
      if (design.value.materials_others_breakdown) {
        try { ob = JSON.parse(design.value.materials_others_breakdown); } catch(e){}
      }
      const dbOthers = Number(design.value.others_total) || ob.reduce((s, o) => s + Number(o.amount || 0), 0) || getBudgetItemAmount('Others');
      const legacyMatOthers = Number(design.value.materials_and_supplies || 0);
      let materialsSupplies = (dbMat === 0 && dbOthers === 0 && legacyMatOthers > 0) ? legacyMatOthers : dbMat;
      let othersTotal = dbOthers || '';

      let functionRoomVenue = Number(design.value.function_room_venue) || getBudgetItemAmount('Function Room/Venue') || '';
      let accommodation = Number(design.value.accommodation) || getBudgetItemAmount('Accommodation') || '';
      let equipmentRental = Number(design.value.equipment_rental) || getBudgetItemAmount('Equipment Rental') || '';
      let professionalFee = Number(design.value.professional_fee_honoria) || getBudgetItemAmount('Professional Fee/Honoraria') || '';
      let tokensVal = Number(design.value.tokens) || getBudgetItemAmount('Token/s') || '';
      let transportation = Number(design.value.transportation) || getBudgetItemAmount('Transportation') || '';

      
      if (design.value.schedules && design.value.schedules.length > 0) {
        let parsedSchedules = design.value.schedules.map(s => {
          let meals = { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false };
          try {
            if (typeof s.meals_and_snacks === 'string') {
              meals = JSON.parse(s.meals_and_snacks);
            } else if (typeof s.meals_and_snacks === 'object' && s.meals_and_snacks !== null) {
              meals = s.meals_and_snacks;
            }
          } catch(e) {}
          return {
            date: s.schedule_date,
            start_time: s.start_time,
            end_time: s.end_time,
            meals_and_snacks: meals
          };
        });
        schedules.value = parsedSchedules;

        // Use the saved schedule_type from the database directly instead of
        // re-detecting it heuristically (which was fragile and caused the
        // consecutive → non-consecutive mismatch when holidays fell between dates).
        const savedScheduleType = design.value.schedule_type || 'continuous';
        
        if (savedScheduleType === 'continuous' && parsedSchedules.length > 0) {
            scheduleType.value = 'continuous';
            continuousConfig.value = {
                start_date: parsedSchedules[0].date,
                end_date: parsedSchedules[parsedSchedules.length - 1].date,
                start_time: parsedSchedules[0].start_time,
                end_time: parsedSchedules[0].end_time,
                meals_and_snacks: { ...parsedSchedules[0].meals_and_snacks }
            };
        } else {
            scheduleType.value = 'staggered';
        }
      } else {
         schedules.value = [{
            date: design.value.start_date,
            start_time: design.value.start_time,
            end_time: design.value.end_time,
            meals_and_snacks: { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false }
         }];
      }
      let ftype = design.value.form_type || '';
      if (ftype && isNaN(ftype)) {
        const found = formTypes.value.find(ft => ft.name === ftype);
        if (found) ftype = found.id;
      }
      
      // Build venues and venue_budgets from design data
      let savedVenues = [];
      let savedVenueBudgets = {};
      let savedCustomVenues = [];

      if (design.value.venues_list && Array.isArray(design.value.venues_list) && design.value.venues_list.length > 0) {
        // New multi-venue format
        savedVenues = design.value.venues_list.map(v => String(v.venue_id));
        if (design.value.custom_venues) {
          try { savedCustomVenues = JSON.parse(design.value.custom_venues); } catch(e) {}
        }
        savedVenues.forEach(vid => {
          const items = design.value.budget_items?.filter(i => String(i.venue_id) === String(vid)) || [];
          let lineSeq = 1;
          let parsedVenueBudget = [];
          
          items.forEach(dbItem => {
            const isCatering = ['Breakfast', 'Lunch', 'Dinner', 'AM Snack', 'PM Snack'].includes(dbItem.item_name);
            const isLogistics = ['Function Room/Venue', 'Accommodation', 'Equipment Rental', 'Transportation'].includes(dbItem.item_name);
            const isProgram = ['Professional Fee/Honoraria', 'Token/s'].includes(dbItem.item_name);
            
            let group = 'materials';
            if (isCatering) group = 'catering';
            else if (isLogistics) group = 'logistics';
            else if (isProgram) group = 'program';
            
            let mult = [];
            try {
              if (dbItem.multipliers) {
                const parsed = typeof dbItem.multipliers === 'string' ? JSON.parse(dbItem.multipliers) : dbItem.multipliers;
                if (Array.isArray(parsed)) {
                  mult = parsed.map(m => ({ q: Number(m.value) || 0, u: String(m.label || '').trim() }));
                }
              }
            } catch(e) {}
            
            let baseKey = null;
            if (isCatering) baseKey = dbItem.item_name.includes('Snack') ? 'snacks' : 'meals';
            else if (dbItem.item_name === 'Professional Fee/Honoraria') baseKey = 'pf_honoraria';
            else if (dbItem.item_name === 'Token/s') baseKey = 'tokens';
            
            let base = 0;
            if (baseKey === 'meals') base = isOutsideBsu.value ? baselineSettings.value.meals_outside : baselineSettings.value.meals_inside;
            else if (baseKey === 'snacks') base = isOutsideBsu.value ? baselineSettings.value.snacks_outside : baselineSettings.value.snacks_inside;
            else if (baseKey) base = baselineSettings.value[baseKey];
            
            parsedVenueBudget.push({
              id: lineSeq++,
              group,
              name: dbItem.item_name === 'Others' ? dbItem.sub_item : dbItem.item_name,
              rate: Number(dbItem.unit_cost) || 0,
              mult,
              custom: dbItem.item_name === 'Others' || isCatering,
              baseKey,
              base,
              capKey: dbItem.item_name === 'Transportation' ? 'transportation_limit' : null
            });
          });
          
          savedVenueBudgets[vid] = parsedVenueBudget;
        });
        customVenuesList.value = savedCustomVenues;
      } else if (design.value.venue_id || design.value.venue) {
        // Legacy single-venue format migration (fallback if budget_items are missing)
        const legacyVid = design.value.venue_id ? String(design.value.venue_id) : 'Other';
        if (legacyVid === 'Other') {
          const tempId = 'temp_' + Date.now();
          customVenuesList.value = [{ venue_id: tempId, venue_name: design.value.venue || 'Custom Venue' }];
          savedVenues = [tempId];
        } else {
          savedVenues = [legacyVid];
        }
        const theVid = savedVenues[0];
        
        let parsedVenueBudget = [];
        if (mealsTotal > 0) parsedVenueBudget.push({ id: Date.now()+1, group: 'catering', name: 'Meals', rate: mealsTotal, mult: [{q:1, u:'lot'}] });
        if (snacksTotal > 0) parsedVenueBudget.push({ id: Date.now()+2, group: 'catering', name: 'Snacks', rate: snacksTotal, mult: [{q:1, u:'lot'}] });
        if (functionRoomVenue > 0) parsedVenueBudget.push({ id: Date.now()+3, group: 'logistics', name: 'Function Room/Venue', rate: functionRoomVenue, mult: [{q:1, u:'lot'}] });
        if (accommodation > 0) parsedVenueBudget.push({ id: Date.now()+4, group: 'logistics', name: 'Accommodation', rate: accommodation, mult: [{q:1, u:'lot'}] });
        if (equipmentRental > 0) parsedVenueBudget.push({ id: Date.now()+5, group: 'logistics', name: 'Equipment Rental', rate: equipmentRental, mult: [{q:1, u:'lot'}] });
        if (professionalFee > 0) parsedVenueBudget.push({ id: Date.now()+6, group: 'program', name: 'Professional Fee/Honoraria', rate: professionalFee, mult: [{q: pfPax.value || 1, u:'speaker'}] });
        if (tokensVal > 0) parsedVenueBudget.push({ id: Date.now()+7, group: 'program', name: 'Token/s', rate: tokensVal, mult: [{q: tokensPax.value || 1, u:'pax'}] });
        if (materialsSupplies > 0) parsedVenueBudget.push({ id: Date.now()+8, group: 'materials', name: 'Materials and Supplies', rate: materialsSupplies, mult: [{q:1, u:'lot'}] });
        if (transportation > 0) parsedVenueBudget.push({ id: Date.now()+9, group: 'logistics', name: 'Transportation', rate: transportation, mult: [{q:1, u:'lot'}], capKey: 'transportation_limit' });
        
        othersList.value.forEach((o, i) => {
          if (o.amount > 0) {
            parsedVenueBudget.push({ id: Date.now()+10+i, group: 'materials', name: o.name || 'Others', rate: o.amount, mult: [{q:1, u:'lot'}], custom: true });
          }
        });
        
        savedVenueBudgets[theVid] = parsedVenueBudget;
      }

      formData.value = {
        activity_title: design.value.activity_title,
        office: design.value.office,
        form_type: ftype ? String(ftype) : '',
        activity_classification: design.value.classification_id ? String(design.value.classification_id) : '',
        gad_mandate: design.value.gad_mandate_ids ? String(design.value.gad_mandate_ids).split(',').map(s=>s.trim()) : [],
        gender_issue: design.value.gender_issue_ids ? String(design.value.gender_issue_ids).split(',').map(s=>s.trim()) : [],
        start_date: design.value.start_date,
        end_date: design.value.end_date,
        start_time: design.value.start_time,
        end_time: design.value.end_time,
        venues: savedVenues,
        venue_budgets: savedVenueBudgets,
        venue: design.value.venue_id || '',
        is_inside_bsu: design.value.is_inside_bsu == 1 || design.value.is_inside_bsu === true,
        proposed_budget: design.value.proposed_budget,
        target_participants: design.value.target_participants,
        budget_items: []
      };
    
        await fetchGADMandates();
        const mapLoadedMandates = () => {
            if (gadMandates.value.length > 0 && formData.value.gad_mandate.length > 0) {
                const savedMandates = formData.value.gad_mandate.map(String);
                const mappedIds = gadMandates.value
                    .filter(m => {
                        const mIds = String(m.id).split(',');
                        return mIds.some(id => savedMandates.includes(id));
                    })
                    .map(m => m.id.toString())[0] || '';
                if (mappedIds.length > 0) {
                    formData.value.gad_mandate = mappedIds;
                }
                if (savedMandates.includes('Other') && formData.value.gad_mandate !== 'Other') {
                    formData.value.gad_mandate = 'Other';
                }
            }
        };
        mapLoadedMandates();
        if (formData.value.gad_mandate.length > 0) {
            await fetchGenderIssues(formData.value.gad_mandate);
            const savedIssues = formData.value.gender_issue.map(String);
            formData.value.gender_issue = genderIssues.value
                .filter(m => {
                    const mIds = String(m.id).split(',');
                    return mIds.some(id => savedIssues.includes(id));
                })
                .map(m => m.id.toString())[0] || '';
            if (savedIssues.includes('Other') && formData.value.gender_issue !== 'Other') {
                formData.value.gender_issue = 'Other';
            }
        }
      } else {
        error.value = "Activity design not found.";
    }
  } catch (err) {
    error.value = "Failed to load activity design details.";
  } finally {
    loading.value = false;
    // We add a tiny delay to allow Vue to process reactive updates before enabling watchers
    setTimeout(() => {
      loadingData.value = false;
    }, 100);
  }
};

const handleFileChange = (e) => {
  newFile.value = e.target.files[0];
  if (newFile.value) {
    newFileURL.value = URL.createObjectURL(newFile.value);
  } else {
    newFileURL.value = null;
  }
};

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : '---';

const formatBudgetName = (name) => { // Added formatBudgetName
  if (!name) return '';
  return name.replace(/(\(.*\))/g, '<span class="budget-item-subtext">$1</span>');
};

const formatCurrency = (amt) => amt ? parseFloat(amt).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '0.00'; // Added formatCurrency

const isCurrentYear = (dateString) => {
  const date = new Date(dateString + 'T00:00:00');
  const manilaTime = new Date().toLocaleString("en-US", { timeZone: "Asia/Manila" });
  const currentYear = new Date(manilaTime).getFullYear();
  return date.getFullYear() === currentYear;
};

const isValidActivityDate = (dateString, checkLeadTime = false) => {
  if (!isCurrentYear(dateString)) {
    const currentYear = new Date().getFullYear();
    return { valid: false, reason: `Activities can only be scheduled in ${currentYear}. Please select a date within the current year.` };
  }
  if (checkLeadTime) {
    const targetDate = new Date(dateString + 'T00:00:00');
    const today = new Date(new Date().toLocaleString("en-US", { timeZone: "Asia/Manila" }));
    today.setHours(0, 0, 0, 0);
    const diffDays = getWorkingDaysDiff(today, targetDate);
    
    if (diffDays < 3) {
       return { valid: false, reason: `Activities must be scheduled at least 3 working days in advance.` };
    } else if (diffDays < 15) {
       return { valid: true, reason: `Activities should ideally be scheduled at least 15 working days in advance.`, isWarning: true };
    }
  }
  return { valid: true, reason: '' };
};

const isValidActivityDuration = (startDateString, endDateString) => {
  if (!startDateString || !endDateString) {
    return { valid: true, reason: '', isWarning: false };
  }
  const startDate = new Date(startDateString + 'T00:00:00');
  const endDate = new Date(endDateString + 'T00:00:00');
  
  if (endDate < startDate) {
    return { valid: false, reason: 'End date cannot be before start date.', isWarning: false };
  }
  
  const diffTime = endDate - startDate;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays > 31) {
    return { valid: true, reason: 'Are you sure if the activity is more than 1 month?', isWarning: true };
  }
  
  return { valid: true, reason: '', isWarning: false };
};

const isValidTime = (timeStr) => {
  if (!timeStr) return true;
  const [h, m] = timeStr.split(':').map(Number);
  if (h < 4 || h > 20 || (h === 20 && m > 0)) {
    return false;
  }
  return true;
};

watch(() => computedStartDate.value, (newDate, oldDate) => {
    if (loadingData.value) return;
    if (newDate && newDate !== oldDate) {
      const validation = isValidActivityDate(newDate, true);
      if (!validation.valid) {
        document.activeElement?.blur();
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Date',
          text: validation.reason,
          confirmButtonColor: '#b979cc'
        });
        return;
      } else if (validation.isWarning) {
        document.activeElement?.blur();
        Swal.fire({
          icon: 'info',
          title: 'Lead Time Warning',
          text: validation.reason,
          confirmButtonText: 'Ignore',
          confirmButtonColor: '#b979cc'
        });
      }
    }
  });
  
  // Disable the old duration check since it's computed dynamically now
  watch(() => 'dummy', () => {
  if (loadingData.value) return;
  if (newDate) {
    const validation = isValidActivityDate(newDate, true);
    if (!validation.valid) {
      document.activeElement?.blur();
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Date',
        text: validation.reason,
        confirmButtonColor: '#b979cc'
      });
      formData.value.start_date = '';
      return;
    } else if (validation.isWarning) {
      document.activeElement?.blur();
      Swal.fire({
        icon: 'info',
        title: 'Lead Time Warning',
        text: validation.reason,
        confirmButtonText: 'Ignore',
        confirmButtonColor: '#b979cc'
      });
    }
    if (formData.value.end_date) {
      const durationValidation = isValidActivityDuration(newDate, formData.value.end_date);
      if (!durationValidation.valid) {
        document.activeElement?.blur();
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Duration',
          text: durationValidation.reason,
          confirmButtonColor: '#b979cc'
        });
        formData.value.start_date = '';
      } else if (durationValidation.isWarning) {
        document.activeElement?.blur();
        Swal.fire({
          icon: 'info',
          title: 'Long Duration',
          text: durationValidation.reason,
          confirmButtonColor: '#b979cc'
        });
      }
    }
  }
});

// watch(() => formData.value.end_date, (newDate) => {
//   if (loadingData.value) return;
//   if (newDate) {
//     const validation = isValidActivityDate(newDate, false);
//     if (!validation.valid) {
//       document.activeElement?.blur();
//       Swal.fire({
//         icon: 'warning',
//         title: 'Invalid Date',
//         text: validation.reason,
//         confirmButtonColor: '#b979cc'
//       });
//       formData.value.end_date = '';
//       return;
//     }
//     if (formData.value.start_date) {
//       const durationValidation = isValidActivityDuration(formData.value.start_date, newDate);
//       if (!durationValidation.valid) {
//         document.activeElement?.blur();
//         Swal.fire({
//           icon: 'warning',
//           title: 'Invalid Duration',
//           text: durationValidation.reason,
//           confirmButtonColor: '#b979cc'
//         });
//         formData.value.end_date = '';
//       } else if (durationValidation.isWarning) {
//         document.activeElement?.blur();
//         Swal.fire({
//           icon: 'info',
//           title: 'Long Duration',
//           text: durationValidation.reason,
//           confirmButtonColor: '#b979cc'
//         });
//       }
//     }
//   }
// });

// watch(() => formData.value.start_time, (newTime) => {
//   if (loadingData.value) return;
//   if (newTime && !isValidTime(newTime)) {
//     document.activeElement?.blur();
//     Swal.fire({
//       icon: 'warning',
//       title: 'Invalid Time',
//       text: 'Must be set between 04:00 AM and 08:00 PM.',
//       confirmButtonColor: '#b979cc'
//     });
//     formData.value.start_time = '';
//   }
// });

// watch(() => formData.value.end_time, (newTime) => {
//   if (loadingData.value) return;
//   if (newTime && !isValidTime(newTime)) {
//     document.activeElement?.blur();
//     Swal.fire({
//       icon: 'warning',
//       title: 'Invalid Time',
//       text: 'Must be set between 04:00 AM and 08:00 PM.',
//       confirmButtonColor: '#b979cc'
//     });
//     formData.value.end_time = '';
//   }
// });

watch([() => formData.value.start_time, () => formData.value.end_time], ([newStart, newEnd]) => {
  if (loadingData.value) return;
  if (scheduleType.value === 'staggered') return;
  if (formData.value.start_date && formData.value.end_date && formData.value.start_date !== formData.value.end_date) return;
  if (newStart && newEnd) {
    if (!formData.value.start_date || !formData.value.end_date || formData.value.start_date === formData.value.end_date) {
      const startTimeParts = newStart.split(':');
      const endTimeParts = newEnd.split(':');
      const startMinutes = parseInt(startTimeParts[0]) * 60 + parseInt(startTimeParts[1]);
      const endMinutes = parseInt(endTimeParts[0]) * 60 + parseInt(endTimeParts[1]);
      
      if (endMinutes <= startMinutes) {
        document.activeElement?.blur();
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Time Range',
          text: 'End time must be after start time on the same day.',
          confirmButtonColor: '#b979cc'
        });
        formData.value.end_time = '';
      } else if ((endMinutes - startMinutes) < 60) {
        document.activeElement?.blur();
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Time Range',
          text: 'The activity duration must be at least 1 hour.',
          confirmButtonColor: '#b979cc'
        });
        formData.value.end_time = '';
      }
    }
  }
});

const handleUpdate = async () => {
  if (scheduleType.value === 'staggered') {
    const selectedDates = schedules.value.map(schedule => schedule.date).filter(Boolean);
    if (new Set(selectedDates).size !== selectedDates.length) {
      Swal.fire({
        icon: 'warning',
        title: 'Duplicate Schedule Date',
        text: 'Each non-consecutive schedule must use a different date.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
  }

  const sortedSchedules = [...schedules.value].sort((a, b) => new Date(a.date) - new Date(b.date));
  formData.value.start_date = sortedSchedules[0]?.date || '';
  formData.value.end_date = sortedSchedules[sortedSchedules.length - 1]?.date || '';
  formData.value.start_time = sortedSchedules[0]?.start_time || '08:00';
  formData.value.end_time = sortedSchedules[sortedSchedules.length - 1]?.end_time || '17:00';


  // Validate Cause of Gender Issue
  if (!formData.value.gender_issue || formData.value.gender_issue.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Field',
      text: 'Please select at least one Cause of Gender Issue before submitting.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }



  // Validate target participants
  if (Number(formData.value.target_participants) <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Participants',
      text: 'Target participants must be at least 1.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  // Validate start date
  const startValidation = isValidActivityDate(formData.value.start_date, true);
  if (!startValidation.valid) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Start Date',
      text: startValidation.reason,
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  // Validate end date
  const endValidation = isValidActivityDate(formData.value.end_date, false);
  if (!endValidation.valid) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid End Date',
      text: endValidation.reason,
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  // Validate activity duration
  const durationValidation = isValidActivityDuration(formData.value.start_date, formData.value.end_date);
  if (!durationValidation.valid) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Duration',
      text: durationValidation.reason,
      confirmButtonColor: '#b979cc'
    });
    return;
  }
  if (durationValidation.isWarning) {
    const result = await Swal.fire({
      icon: 'warning',
      title: 'Long Duration',
      text: durationValidation.reason,
      showCancelButton: true,
      confirmButtonText: 'Yes, proceed',
      cancelButtonText: 'No, cancel',
      confirmButtonColor: '#b979cc'
    });
    if (!result.isConfirmed) {
      return;
    }
  }

  // Validate start time and end time
  if (!isValidTime(formData.value.start_time) || !isValidTime(formData.value.end_time)) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Time',
      text: 'Must be set between 04:00 AM and 08:00 PM.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }
  if (formData.value.start_time && formData.value.end_time && (!formData.value.start_date || !formData.value.end_date || formData.value.start_date === formData.value.end_date)) {
    const startTimeParts = formData.value.start_time.split(':');
    const endTimeParts = formData.value.end_time.split(':');
    const startMinutes = parseInt(startTimeParts[0]) * 60 + parseInt(startTimeParts[1]);
    const endMinutes = parseInt(endTimeParts[0]) * 60 + parseInt(endTimeParts[1]);
    
    if (endMinutes <= startMinutes) {
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Time Range',
        text: 'End time must be after start time on the same day.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
    
    if ((endMinutes - startMinutes) < 60) {
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Time Range',
        text: 'The activity duration must be at least 1 hour.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
  }

  const paxBased = /(breakfast|lunch|dinner|snack|professional fee|honoraria|token)/i;
  for (const venueId of formData.value.venues || []) {
    const items = formData.value.venue_budgets?.[venueId] || [];
    let venueTotal = 0;
    for (const item of items) {
      const multipliers = Array.isArray(item.mult) ? item.mult.filter(multiplier => multiplier && typeof multiplier === 'object') : [];
      const amount = (Number(item.rate) || 0) * multipliers.reduce((total, multiplier) => total * (Number(multiplier.q) || 0), 1);
      const paxMultiplier = multipliers.find(multiplier => /^(pax|person|persons|head|heads)$/i.test(String(multiplier.u || '').trim()));
      const pax = paxMultiplier ? Number(paxMultiplier.q) : 0;
      if (amount < 0 || pax < 0 || (amount === 0 && pax > 0)) {
        Swal.fire({ icon: 'warning', title: 'Invalid Budget', text: 'Each budget line must have a non-negative amount, and a zero-cost line cannot have a positive pax count.', confirmButtonColor: '#b979cc' });
        return;
      }
      if (amount > 0 && paxBased.test(String(item.name || '')) && pax <= 0) {
        Swal.fire({ icon: 'warning', title: 'Invalid Budget', text: `${item.name} requires a pax count greater than zero when it has an amount.`, confirmButtonColor: '#b979cc' });
        return;
      }
      venueTotal += amount;
    }
    if (venueTotal <= 0) {
      Swal.fire({ icon: 'warning', title: 'Empty Venue Budget', text: `Please enter at least one positive budget amount for ${getVenueName(venueId)}.`, confirmButtonColor: '#b979cc' });
      return;
    }
  }

  const submitConfirm = await Swal.fire({
    title: 'Confirm Submission',
    text: 'Are you sure you want to submit this revised Activity Design?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Submit',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#b979cc'
  });

  if (!submitConfirm.isConfirmed) {
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

  submitting.value = true;
  try {
    const id = route.params.id;
    const submitData = new FormData();
    
    // Aligning keys with ActivityDesignController expectations
    // Aligning keys with ActivityDesignController expectations
    submitData.append('activity_title', formData.value.activity_title);
    submitData.append('form_type', formData.value.form_type);
    submitData.append('activity_classification_id', formData.value.activity_classification);
    submitData.append('gad_mandate_id', Array.isArray(formData.value.gad_mandate) ? formData.value.gad_mandate.join(',') : formData.value.gad_mandate);
    if (formData.value.gad_mandate === 'Other') {
      submitData.append('custom_gad_mandate', customMandate.value);
    }
    submitData.append('gender_issue_id', Array.isArray(formData.value.gender_issue) ? formData.value.gender_issue.join(',') : formData.value.gender_issue);
    if (formData.value.gender_issue === 'Other') {
      submitData.append('custom_gender_issue', customGenderIssue.value);
    }
    submitData.append('start_date', formData.value.start_date);
    submitData.append('end_date', formData.value.end_date);
    submitData.append('start_time', formData.value.start_time);
    submitData.append('end_time', formData.value.end_time);
    submitData.append('proposed_budget', formData.value.proposed_budget);
    submitData.append('target_participants', formData.value.target_participants);
    submitData.append('schedule_type', scheduleType.value);
    submitData.append('schedules', JSON.stringify(schedules.value));

    // Multi-Venue Logic
    submitData.append('venues', JSON.stringify(formData.value.venues));
    submitData.append('custom_venues', JSON.stringify(customVenuesList.value));
    submitData.append('is_inside_bsu', formData.value.is_inside_bsu ? 1 : 0);

    // Check transportation limit per venue
    let exceedsTransportLimit = false;
    const normalizedBudgetItems = [];
    
    formData.value.venues.forEach(vid => {
      (formData.value.venue_budgets[vid] || []).forEach(item => {
        const lineTotalAmount = (Number(item.rate) || 0) * (item.mult ? item.mult.reduce((p, m) => p * (Number(m.q) || 0), 1) : 1);
        if (item.capKey && lineTotalAmount > Number(baselineSettings.value[item.capKey])) exceedsTransportLimit = true;
        if (item.custom && (!String(item.name).trim() || lineTotalAmount <= 0)) return;
        const isOther = item.custom && item.group === 'materials';
        
        const paxOfItem = Number(((item.mult || []).find(m => /^(pax|person|persons|head|heads)$/i.test(String(m.u || '').trim())) || {}).q) || 0;
        const lineFormulaStr = [('₱' + (Number(item.rate) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })), ...(item.mult ? item.mult.map(m => `${Number(m.q) || 0} ${String(m.u || '').trim()}`.trim()) : [])].join(' × ');
        
        normalizedBudgetItems.push({
          venue_id: vid === 'Other' ? 'Other' : vid,
          category_id: null,
          item_name: isOther ? 'Others' : item.name,
          sub_item: isOther ? item.name : lineFormulaStr,
          pax: paxOfItem || null,
          unit_cost: Number(item.rate) || 0,
          multipliers: item.mult ? item.mult.map(m => ({ label: String(m.u || '').trim(), value: Number(m.q) || 0 })) : [],
          formula: lineFormulaStr,
          amount: lineTotalAmount
        });
      });
    });

    if (exceedsTransportLimit) {
      Swal.fire({
        icon: 'warning',
        title: 'Limit Exceeded',
        text: `Transportation budget cannot exceed ₱${Number(baselineSettings.value.transportation_limit).toLocaleString()}.`,
        confirmButtonColor: '#b979cc'
      });
      return;
    }

    submitData.append('budget_items', JSON.stringify(normalizedBudgetItems));

    submitData.append('status', 'Pending'); // Reset status so admin can review again
    
    if (newFile.value) {
      submitData.append('attachment', newFile.value);
    }

    const response = await api.post(`update-design/${id}`, submitData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (response.data.success) {
      Swal.fire({ icon: 'success', title: 'Resubmitted!', text: 'Activity Design updated and resubmitted successfully.', confirmButtonColor: '#b979cc' }).then(() => {
        router.push('/college/submitted-list');
      });
    } else {
      Swal.fire({ icon: 'error', title: 'Update Failed', text: response.data.message || 'Failed to update activity design.', confirmButtonColor: '#b979cc' });
    }
  } catch (err) {
    console.error('Error updating design:', err);
    const errorMsg = err.response?.data?.message || 'Failed to update activity design. Please check your network or server logs.';
    Swal.fire({ icon: 'error', title: 'Update Failed', text: errorMsg, confirmButtonColor: '#b979cc' });
  } finally {
    submitting.value = false;
  }
};


  watch(() => formData.value.activity_classification, (newVal) => {
      if (typeof loadingData !== 'undefined' && loadingData.value) return;
      formData.value.gad_mandate = '';
      formData.value.gender_issue = '';
      fetchGADMandates();
  });
  
  watch(() => formData.value.gad_mandate, (newVal) => {
      if (typeof loadingData !== 'undefined' && loadingData.value) return;
      if (newVal && newVal.length > 0) {
          formData.value.gender_issue = '';
          fetchGenderIssues(newVal);
      } else {
          genderIssues.value = [];
          formData.value.gender_issue = '';
      }
  }, { deep: true });
  
  onMounted(async () => {
  if (!user.value.id || !['twg', 'non-twg'].includes(user.value.role)) {
    router.push('/login');
  } else {
    formData.value.office = user.value.office || '';
    fetchHolidays();
    fetchBaselineSettings();
    await fetchFormTypes();
    await fetchActivityClassifications();
    await fetchVenues();
    await fetchDesignDetails();
  }
});

const checkTransportationLimit = (vId) => {
  const limit = Number(baselineSettings.value?.transportation_limit || 20000);
  
  let transTotal = 0;
  Object.values(formData.value.venue_budgets || {}).forEach(items => {
    const tItem = items?.find(i => i.name === 'Transportation');
    if (tItem) transTotal += Number(tItem.total) || 0;
  });
  
  if (transTotal > limit) {
    const excess = transTotal - limit;
    const transItem = formData.value.venue_budgets[vId]?.find(i => i.name === 'Transportation');
    if (transItem) {
      transItem.total = Math.max(0, (Number(transItem.total) || 0) - excess);
    }

    const role = user.value?.role || 'college';
    Swal.fire({
      icon: 'warning',
      title: 'Limit Exceeded',
      html: `Overall Transportation budget cannot exceed the baseline limit of ₱${limit.toLocaleString('en-US')}.<br><br>
             If you need to request an exemption, please <a href="/${role}/messages" style="color: #b979cc; text-decoration: underline; font-weight: bold;">message the GAD Director/Staff</a>.`,
      confirmButtonColor: '#b979cc'
    });
  }
};

</script>

<style scoped>
.main-viewport { flex: 1; min-height: 100vh; background: transparent; }
.loading-wrapper { display: flex; justify-content: center; align-items: center; min-height: 400px; }

.error-view-wrapper { flex: 1; display: flex; align-items: center; justify-content: center; padding: 24px; min-height: 400px; }
.error-card { position: relative; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(24px); border-radius: 24px; border: 1px solid rgba(239, 68, 68, 0.2); padding: 40px; width: 100%; max-width: 448px; text-align: center; overflow: hidden; display: flex; flex-direction: column; align-items: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
.error-glow { position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 256px; height: 256px; background: rgba(239, 68, 68, 0.1); border-radius: 50%; filter: blur(64px); pointer-events: none; }
.error-icon-container { width: 96px; height: 96px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); display: flex; align-items: center; justify-content: center; margin-bottom: 24px; position: relative; z-index: 10; }
.error-icon { font-size: 48px; color: #f87171; }
.error-heading { font-size: 24px; font-weight: 900; color: white; margin-bottom: 12px; position: relative; z-index: 10; letter-spacing: -0.025em; }
.error-text { color: #e2e8f0; font-size: 16px; margin-bottom: 40px; position: relative; z-index: 10; line-height: 1.6; }
.error-btn-red { position: relative; z-index: 10; background: #ef4444; color: white; padding: 12px 32px; border-radius: 9999px; font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; transition: all 0.2s; display: flex; align-items: center; gap: 8px; border: none; cursor: pointer; }
.error-btn-red:hover { background: #dc2626; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.4); }
.btn-icon { font-weight: bold; }

.page-container { min-height: 100vh; }
.layout-grid { display: flex; gap: 15px; max-width: 80rem; margin: 0 auto; }
.flex-06 { flex: 0.65; display: flex; flex-direction: column; overflow: hidden; }
.flex-100 { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.flex-04-sidebar { flex: 0.35; position: sticky; top: 120px; align-self: flex-start; }

.glass-card { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); backdrop-filter: blur(24px); border-radius: 1.5rem; border: 1px solid rgba(185, 121, 204, 0.2); }
.report-header { padding: 2rem; border-bottom: 1px solid rgba(185, 121, 204, 0.15); background: rgba(0, 0, 0, 0.2); }
.meta-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
.report-body { flex: 1; overflow-y: visible; padding: 2rem; }
.report-body > * + * { margin-top: 1.5rem; }

.status-badge-revision { display: inline-flex; align-items: center; gap: 8px; background-color: rgba(239, 68, 68, 0.15); color: #ef4444; padding: 4px 12px; border-radius: 9999px; border: 1px solid rgba(239, 68, 68, 0.3); }
.status-dot-pulse { width: 8px; height: 8px; background-color: #ef4444; border-radius: 9999px; animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
.status-text { font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; }

.control-number { font-size: 13px; font-weight: 700; color: #b979cc; text-transform: uppercase; margin-left: 12px; font-family: monospace; }
.form-group-top { margin-bottom: 1.5rem; }
.title-input { font-size: 20px !important; font-family: 'Times New Roman', serif; font-weight: 700; }

.info-grid { display: flex; flex-wrap: wrap; gap: 24px; padding-top: 16px; border-top: 1px solid rgba(185, 121, 204, 0.1); }
.info-item { display: flex; flex-direction: column; }
.info-label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; color: #b979cc; font-weight: 700; margin-bottom: 4px; }
.info-value-white { font-size: 14px; font-weight: 600; color: white; }

.assessment-date-display { margin-bottom: 1.5rem; }
.icon-pink { color: #b979cc; }
.full-width-info { grid-column: span 2; margin-top: 1rem; }

.section-card { background-color: rgba(0, 0, 0, 0.2); border-radius: 16px; padding: 24px; border: 1px solid rgba(185, 121, 204, 0.15); }
.section-header-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
.section-title { font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.1em; color: #b979cc; }

.grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.venue-participants-row {
  display: grid;
  grid-template-columns: 2.25fr 1fr;
  gap: 16px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid rgba(185, 121, 204, 0.1);
}

.doc-item { display: flex; align-items: center; justify-content: space-between; padding: 16px; background-color: rgba(0, 0, 0, 0.3); border-radius: 12px; border: 1px solid rgba(185, 121, 204, 0.15); }
.doc-info { display: flex; align-items: center; gap: 12px; color: white; font-size: 12px;}
.doc-pdf-icon { font-size: 1.875rem; color: #ef4444; }
.doc-title { font-size: 13px; font-weight: 700; color: b979cc; }
.doc-meta { font-size: 11px; color: #cbd5e1; margin-top: 2px; }
.preview-btn { color: #b979cc; font-size: 11px; padding: 6px 12px; border-radius: 8px; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(185, 121, 204, 0.15); font-weight: 700; text-align: center; cursor: pointer; }
.preview-btn:hover { border-color: #b979cc; color: white; background: rgba(185, 121, 204, 0.1); }

.assessment-card-custom { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 1.5rem; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); border: 1px solid rgba(185, 121, 204, 0.2); }
.assessment-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid rgba(185, 121, 204, 0.15); }
.assessment-icon { width: 44px; height: 44px; background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: white; font-size: 22px; }
.assessment-title { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #b979cc; }
.assessment-form { display: flex; flex-direction: column; }

.read-only-remarks { width: 100%; border: 1px solid rgba(185, 121, 204, 0.2); border-radius: 12px; padding: 14px 16px; font-size: 13px; background: rgba(0, 0, 0, 0.3); color: #cbd5e1; min-height: 100px; line-height: 1.5; }
.feedback-remarks { margin-bottom: 1rem; }

.form-label { display: block; font-size: 12px; font-weight: 800; text-transform: uppercase; color: #b979cc; letter-spacing: 1px; margin-bottom: 8px; }
.modal-input { width: 100%; padding: 12px 18px; border: 1px solid rgba(185, 121, 204, 0.2); background: rgba(0, 0, 0, 0.4); color: white; border-radius: 12px; font-size: 13px; }
.modal-input:focus { outline: none; border-color: #b979cc; }
.modal-input-center { text-align: center; }
.select-input { appearance: none; cursor: pointer; }
.select-arrow-fix {
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23b979cc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 16px center;
  background-size: 14px;
}
.dark-option { background-color: #16213e; color: #ffffff; }
.custom-venue-wrapper { margin-top: 1rem; }
.file-input-hidden { display: none; }

.action-buttons { display: flex; flex-direction: column; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(185, 121, 204, 0.15); }
.btn-approve { width: 100%; background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%); color: white; border: none; border-radius: 14px; padding: 14px; font-size: 12px; font-weight: 800; text-transform: uppercase; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; }
.btn-approve:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-approve:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(153, 13, 209, 0.25); }

.btn-back { display: block; width: 100%; padding: 12px; font-size: 11px; color: #cbd5e1; text-align: center; border-radius: 12px; background: transparent; border: 1px solid rgba(185, 121, 204, 0.15); margin-top: 8px; cursor: pointer; }
.btn-back:hover { color: white; border-color: #b979cc; background: rgba(185, 121, 204, 0.05); }

.loading-spinner { width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid #990dd1; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

.budget-table-wrapper {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background-color: rgba(0, 0, 0, 0.2);
}

.budget-table {
  width: 100%;
  text-align: left;
  border-collapse: collapse;
}

.budget-table-header {
  background-color: rgba(255, 255, 255, 0.05);
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #b979cc;
}

.table-header-cell {
  padding: 10px 16px;
  font-weight: 700;
}

.budget-col-total {
  width: 128px; /* Adjust as needed */
}

.budget-table-body {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.budget-table-row {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.budget-item-name {
  padding: 12px 16px;
  color: #b979cc;
  line-height: 1.25;
  font-size: 13px;
}

.budget-item-subtext {
  display: block;
  font-size: 12px;
  color: #64748b;
  font-weight: 400;
  margin-top: 2px;
}

.budget-item-input-cell {
  padding: 8px 16px;
}

.budget-input-field {
  background-color: transparent;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  outline: none;
  width: 100%;
  color: #ffffff;
  font-size: 14px;
  padding-top: 4px;
  padding-bottom: 4px;
  text-align: right; /* Align budget input to the right */
}
.budget-input-field:focus {
  border-color: #b979cc;
}

.budget-total-field {
  font-weight: 600;
}

.budget-table-footer {
  background-color: rgba(255, 255, 255, 0.05);
}

.grand-total-label {
  padding: 12px 16px;
  font-size: 12px;
  font-weight: 700;
  color: #b979cc;
  text-align: right;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.grand-total-value-white {
  padding: 12px 16px;
  font-size: 14px;
  font-weight: 700;
  color: white;
  text-align: right; /* Align grand total value to the right */
}

.empty-budget-notice {
  color: #64748b;
  font-size: 13px;
  font-style: italic;
  padding: 16px;
  text-align: center;
}

/* GAD Budget Table Inline Styles */
.budget-sub-controls {
  margin-top: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  font-size: 11px;
  color: #94a3b8;
}

.budget-checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  user-select: none;
  color: #cbd5e1;
  font-size: 13px;
  font-weight: 500;
  transition: color 0.2s ease;
}

.budget-checkbox-label:hover {
  color: #ffffff;
}

.budget-checkbox {
  appearance: none;
  -webkit-appearance: none;
  width: 18px;
  height: 18px;
  background-color: rgba(15, 23, 42, 0.3);
  border: 2px solid rgba(185, 121, 204, 0.4);
  border-radius: 5px;
  display: inline-grid;
  place-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  margin: 0;
  position: relative;
}

.budget-checkbox:hover {
  border-color: #b979cc;
  background-color: rgba(185, 121, 204, 0.1);
  box-shadow: 0 0 0 2px rgba(185, 121, 204, 0.2);
}

.budget-checkbox:checked {
  background-color: #b979cc;
  border-color: #b979cc;
  box-shadow: 0 0 8px rgba(185, 121, 204, 0.4);
}

.budget-checkbox:checked::before {
  content: "";
  width: 10px;
  height: 10px;
  background-color: #ffffff;
  clip-path: polygon(14% 44%, 0 58%, 38% 95%, 100% 23%, 86% 9%, 38% 68%);
}

.budget-number-input-label {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #cbd5e1;
  font-size: 13px;
  font-weight: 500;
}

.budget-sub-number-input {
  background-color: rgba(15, 23, 42, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  padding: 8px 12px;
  width: 90px;
  color: #ffffff;
  font-size: 14px;
  outline: none;
  box-sizing: border-box;
  text-align: center;
  transition: all 0.2s ease;
  font-weight: 600;
}

.budget-sub-number-input:focus {
  border-color: #b979cc;
  background-color: rgba(15, 23, 42, 0.5);
  box-shadow: 0 0 0 2px rgba(185, 121, 204, 0.2);
}

.budget-warning-inline {
  margin-top: 6px;
  font-size: 11px;
  color: #fbbf24;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 4px;
}

.budget-error-inline {
  margin-top: 6px;
  font-size: 11px;
  color: #f43f5e;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Others Breakdown Styles */
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
  .budget-sub-controls {
    flex-wrap: wrap;
    width: 100%;
  }
  .budget-item-info {
    width: 100%;
  }
  .budget-card-input {
    width: 100%;
  }
  .doc-item, .doc-item-custom {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .doc-info {
    width: 100%;
    word-break: break-word;
  }
  .preview-btn {
    align-self: flex-start;
    width: 100%;
    box-sizing: border-box;
  }
}

.custom-input-field { width: 100%;
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
  color: #94a3b8;
}


.label-container {
  display: flex;
  align-items: center;
  gap: 6px;
}

.info-btn {
  background: rgba(185, 121, 204, 0.08);
  border: 1px solid rgba(185, 121, 204, 0.35);
  color: #b979cc;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  font-size: 10px;
  font-weight: bold;
  font-family: serif;
  line-height: 1;
  transition: all 0.25s ease;
}

.info-btn:hover {
  background: #b979cc;
  color: #16213e;
  border-color: #b979cc;
  transform: scale(1.15);
  box-shadow: 0 0 8px rgba(185, 121, 204, 0.4);
}

.info-btn-wrapper {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.simple-popup {
  position: absolute;
  bottom: calc(100% + 10px);
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  background: #1a1a2e;
  border: 1px solid #b979cc;
  border-radius: 8px;
  padding: 10px 14px;
  color: #ffffff;
  font-size: 12px;
  width: 240px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
  line-height: 1.45;
  pointer-events: auto;
  text-transform: none;
  white-space: normal;
}

.simple-popup::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border-width: 6px;
  border-style: solid;
  border-color: #1a1a2e transparent transparent transparent;
}

.simple-popup::before {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border-width: 7px;
  border-style: solid;
  border-color: #b979cc transparent transparent transparent;
  z-index: -1;
}

.fade-pop-enter-active,
.fade-pop-leave-active {
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-pop-enter-from,
.fade-pop-leave-to {
  opacity: 0;
  transform: translate(-50%, 8px) scale(0.95);
}

.fade-pop-enter-to,
.fade-pop-leave-from {
  opacity: 1;
  transform: translate(-50%, 0) scale(1);
}


.form-sub-grid {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 20px;
}
@media (min-width: 768px) {
  .form-sub-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

  .input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  @media (max-width: 1024px) {
    .layout-grid { flex-direction: column; padding: 1rem; }
    .flex-06, .flex-100, .flex-04-sidebar { flex: 1 !important; width: 100% !important; max-width: 100% !important; position: relative !important; top: 0 !important; }
  }

  @media (max-width: 768px) {
    .info-grid { flex-direction: column !important; gap: 12px !important; }
    .venue-participants-row { flex-direction: column; gap: 12px; }
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

/* Custom Multi-select Styles */
.custom-multiselect-container {
  position: relative;
  width: 100%;
}

.multiselect-backdrop {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  z-index: 99;
}

.multiselect-trigger {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  user-select: none;
  background-color: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.75rem 1rem;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.multiselect-trigger:hover, .multiselect-trigger.is-open {
  border-color: #b979cc;
  box-shadow: 0 0 0 3px rgba(185, 121, 204, 0.1);
}

.placeholder-text {
  color: #94a3b8;
}

.selected-text {
  color: #f8fafc;
  font-weight: 500;
}

.dropdown-arrow {
  color: #94a3b8;
  font-size: 0.8rem;
  transition: transform 0.3s ease;
  margin-left: auto;
}
.multiselect-trigger.is-open .dropdown-arrow {
  transform: rotate(180deg);
}

.multiselect-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 0.5rem;
  background-color: #1e293b;
  border: 1px solid rgba(185, 121, 204, 0.3);
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
  max-height: 250px;
  overflow-y: auto;
  z-index: 100;
  padding: 0.5rem 0;
  display: flex;
  flex-direction: column;
}

.multiselect-option {
  display: flex;
  align-items: center;
  padding: 0.6rem 1rem;
  cursor: pointer;
  color: #e2e8f0;
  transition: background-color 0.2s;
  margin: 0;
  width: 100%;
}

.multiselect-option:hover {
  background-color: rgba(185, 121, 204, 0.15);
}

.multiselect-checkbox {
  margin-right: 0.75rem;
  width: 16px;
  height: 16px;
  cursor: pointer;
  accent-color: #b979cc;
}

.multiselect-divider {
  height: 1px;
  background-color: rgba(255, 255, 255, 0.1);
  margin: 0.5rem 0;
}

/* Chips Styles */
.chips-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.venue-chip {
  display: inline-flex;
  align-items: center;
  background: linear-gradient(135deg, rgba(185, 121, 204, 0.2), rgba(185, 121, 204, 0.05));
  border: 1px solid rgba(185, 121, 204, 0.4);
  color: #f8fafc;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 500;
  backdrop-filter: blur(4px);
  animation: fadeInChip 0.3s ease-out forwards;
}

.chip-remove {
  background: none;
  border: none;
  color: #94a3b8;
  margin-left: 0.5rem;
  font-size: 1.1rem;
  line-height: 1;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.chip-remove:hover {
  color: #ef4444;
}

@keyframes fadeInChip {
  from { opacity: 0; transform: translateY(5px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.btn-add-custom-venue {
  width: 100%;
  padding: 0.75rem;
  background: transparent;
  border: none;
  color: #b979cc;
  font-weight: 500;
  cursor: pointer;
  text-align: left;
  transition: background-color 0.2s;
}
.btn-add-custom-venue:hover {
  background-color: rgba(185, 121, 204, 0.1);
}

.pax-input-group {
  display: flex;
  align-items: center;
  gap: 12px;
}
.creative-pax-input {
  width: 65px;
  padding: 8px;
  background: #1e293b;
  border: 1px solid #475569;
  border-radius: 6px;
  color: #fff;
  font-size: 14px;
  font-weight: 500;
  text-align: center;
  transition: all 0.2s ease;
}
.creative-pax-input:focus {
  border-color: #b979cc;
  background: #2a3b54;
  box-shadow: 0 0 0 2px rgba(185, 121, 204, 0.2);
  outline: none;
}
.pax-label { font-size: 14px; color: #e2e8f0; font-weight: 500; } .pax-calc-text { font-size: 12px; color: #94a3b8; margin-left: 77px; display: flex; align-items: center; gap: 6px; background: rgba(0, 0, 0, 0.2); padding: 4px 8px; border-radius: 4px; width: fit-content; } .pax-calc-formula { color: #cbd5e1; } .pax-calc-equals { color: #64748b; } .pax-calc-total { color: #10b981; font-weight: 600; font-size: 13px; } .budget-group-total { font-weight: 600; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 4px 10px; border-radius: 6px; font-size: 13px; margin-top: 4px; } .budget-row-item:has(.pax-breakdown-list) { flex-wrap: wrap !important; } .budget-group-header { flex-wrap: wrap !important; } .pax-calc-text { flex-wrap: wrap !important; max-width: 100% !important; margin-top: 8px !important; margin-left: 0 !important; } .pax-calc-formula, .pax-calc-equals, .pax-calc-total { white-space: nowrap !important; } </style>












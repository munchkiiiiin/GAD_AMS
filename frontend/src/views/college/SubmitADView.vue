<template>
  <div style="width: 100%; overflow-x: hidden;">
    <div style="min-height: 100vh; width: 100%;">
      <main class="twg-view-wrapper">
        <div class="main-content-container">
          <div class="form-header">
            <h1 class="form-main-title">Submit Activity Design</h1>
            <p class="form-description">Fill out the activity design form below. All fields marked with * are required.</p>
          </div>

          <div class="form-container-box">
            <form @submit.prevent="submitActivityDesign" class="form-main-layout">
              <div class="form-grid-main">
                <!-- Section 01: Classification & Activity Information -->
                <section class="form-section-card">

                  <div class="form-sub-grid">
                    <div class="input-group">
                      <label class="form-label">Form Type *</label>
                      <select 
                        v-model="form.form_type" 
                        required 
                        class="custom-input-field select-arrow-fix"
                      >
                        <option value="" disabled class="dark-option">Select form type...</option>
                        <option 
                          v-for="ft in formTypes" 
                          :key="ft.id" 
                          :value="ft.id" 
                          class="dark-option"
                        >
                          {{ ft.name }}
                        </option>
                      </select>
                    </div>

                    <div class="input-group">
                      <label class="form-label">Activity Classification *</label>
                      <select
                        v-model="form.activity_classification_id"
                        required
                        class="custom-input-field select-arrow-fix"
                      >
                        <option value="" disabled class="dark-option">Select Classification</option>
                        <option
                          v-for="classification in ActClassification"
                          :key="classification.id"
                          :value="classification.id"
                          class="dark-option"
                        >
                          {{ classification.classification_name }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="input-group" style="margin-top: 20px;">
                    <label class="form-label">Activity Title *</label>
                    <textarea 
                      v-model="form.activity_title" 
                      required 
                      rows="2" 
                      class="custom-input-field textarea-no-resize"
                      placeholder="Enter the complete title of the activity"
                    ></textarea>
                  </div>
                </section>

                <div class="form-divider"></div>

                <!-- Section 02: GAD Alignment & Gender Issues -->
                <section class="form-section-card" id="section-gad-mandates-issues">
                  <div class="form-sub-grid">
                    <!-- GAD Mandates Multiselect -->
                    <div class="input-group">
                      <label class="form-label">Gender Issue / GAD Mandate *</label>
                      <div class="gad-multiselect-container">
                        <div class="gad-search-header">
                          <div class="gad-search-input-wrap">
                            <span class="material-symbols-outlined gad-search-icon">search</span>
                            <input 
                              type="text" 
                              v-model="mandateSearchQuery" 
                              placeholder="Search mandates..." 
                              class="gad-search-input" 
                            />
                          </div>
                          <span class="gad-count-chip" :class="{ 'has-selection': selectedMandatesCount > 0 }">
                            {{ selectedMandatesCount }} selected
                          </span>
                        </div>

                        <div class="gad-multiselect-list" ref="mandatesListRef">
                          <label 
                            v-for="mandate in filteredMandatesList" 
                            :key="mandate.id" 
                            :id="'mandate-item-' + mandate.id"
                            class="gad-multiselect-item"
                            :class="{ 'is-selected': isMandateSelected(mandate.id) }"
                          >
                            <span class="gad-custom-checkbox">
                              <input 
                                type="checkbox" 
                                v-model="form.gad_mandate_id" 
                                :value="mandate.id" 
                                style="position: absolute; opacity: 0; pointer-events: none;" 
                              />
                              <span class="material-symbols-outlined gad-check-icon">check</span>
                            </span>
                            <div class="gad-item-text-wrap">
                              <span class="gad-mandate-code-badge">{{ mandate.code }}</span>
                              <span class="gad-item-title">{{ mandate.title }}</span>
                            </div>
                          </label>

                          <div v-if="filteredMandatesList.length === 0" class="gad-empty-hint">
                            <span class="material-symbols-outlined" style="font-size: 16px;">info</span>
                            No mandates match your search.
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Cause of Gender Issue Multiselect -->
                    <div class="input-group">
                      <label class="form-label">Cause of Gender Issue *</label>
                      <div class="gad-multiselect-container">
                        <div class="gad-search-header">
                          <div class="gad-search-input-wrap">
                            <span class="material-symbols-outlined gad-search-icon">search</span>
                            <input 
                              type="text" 
                              v-model="issueSearchQuery" 
                              placeholder="Search causes..." 
                              class="gad-search-input" 
                              :disabled="!form.gad_mandate_id || form.gad_mandate_id.length === 0"
                            />
                          </div>
                          <span class="gad-count-chip" :class="{ 'has-selection': selectedIssuesCount > 0 }">
                            {{ selectedIssuesCount }} selected
                          </span>
                        </div>

                        <div class="gad-multiselect-list" ref="issuesListRef">
                          <label 
                            v-for="issue in filteredGenderIssuesList" 
                            :key="issue.id" 
                            :id="'issue-item-' + issue.id"
                            class="gad-multiselect-item"
                            :class="{ 'is-selected': isIssueSelected(issue.id) }"
                          >
                            <span class="gad-custom-checkbox">
                              <input 
                                type="checkbox" 
                                v-model="form.gender_issue_id" 
                                :value="issue.id" 
                                style="position: absolute; opacity: 0; pointer-events: none;" 
                              />
                              <span class="material-symbols-outlined gad-check-icon">check</span>
                            </span>
                            <div class="gad-item-text-wrap">
                              <span class="gad-item-title">{{ issue.title }}</span>
                            </div>
                          </label>

                          <div v-if="!form.gad_mandate_id || form.gad_mandate_id.length === 0" class="gad-empty-hint">
                            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                            Select a mandate on the left first to see gender issues.
                          </div>
                          <div v-else-if="filteredGenderIssuesList.length === 0" class="gad-empty-hint">
                            <span class="material-symbols-outlined" style="font-size: 16px;">info</span>
                            No issues match your search.
                          </div>
                        </div>
                      </div>

                      <input v-if="form.gender_issue_id && (Array.isArray(form.gender_issue_id) ? form.gender_issue_id.includes('Other') : form.gender_issue_id === 'Other')" 
                            v-model="customGenderIssue" 
                            type="text" 
                            placeholder="Enter new gender issue..." 
                            class="custom-input-field" 
                            style="margin-top: 10px;" />
                    </div>
                  </div>
                </section>

                <div class="form-divider"></div>

                <!-- Section 03: Activity Schedules & Timeline -->
                <section class="form-section-card">

                  <div class="schedules-container" style="margin-bottom: 20px;">
                    <div class="flex justify-between items-center mb-4 flex-wrap gap-4">
                      <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                          <label class="form-label !mb-0 flex items-center gap-2" style="white-space: nowrap;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">schedule</span>
                            Activity Schedules *
                          </label>
                          <div class="schedule-type-toggle-container">
                            <button type="button" @click.prevent="handleScheduleTypeChange('staggered')" class="schedule-type-toggle-btn" :style="{ background: scheduleType === 'staggered' ? 'rgba(185, 121, 204, 0.2)' : 'transparent', color: scheduleType === 'staggered' ? '#b979cc' : 'inherit' }">Non Consecutive</button>
                            <button type="button" @click.prevent="handleScheduleTypeChange('continuous')" class="schedule-type-toggle-btn" :style="{ background: scheduleType === 'continuous' ? 'rgba(185, 121, 204, 0.2)' : 'transparent', color: scheduleType === 'continuous' ? '#b979cc' : 'inherit' }">Consecutive</button>
                          </div>
                      </div>
                      <button type="button" v-if="scheduleType === 'staggered'" @click.prevent="addSchedule" class="btn-add-other">
                        <span class="material-symbols-outlined" style="font-size: 14px;">add</span> Add Schedule
                      </button>
                    </div>
                    
                    <div v-if="schedules.length === 0" style="color: #94a3b8; font-size: 13px; font-style: italic; margin-bottom: 8px;">
                      Please add at least one schedule.
                    </div>
                    
                    <!-- Continuous Config UI -->
                    <div v-if="scheduleType === 'continuous'" class="schedule-row mb-3 p-4 rounded-lg relative">
                      <div class="schedule-inputs-wrapper" style="margin-bottom: 16px;">
                        <div class="flex-1">
                          <label class="text-[10px] uppercase font-bold text-slate-500 mb-1 block">Start Date</label>
                          <VueDatePicker :dark="isDarkMode" v-model="continuousConfig.start_date" :min-date="minStartDate" :disabled-dates="isDisabledDate" model-type="yyyy-MM-dd" :enable-time-picker="false" format="MM/dd/yyyy" auto-apply required input-class-name="custom-input-field dp-custom-transparent" :max-date="maxDateLimit" >
                            <template #dp-input="{ value }">
                              <input type="text" :value="value ? String(value).replace(',', '').trim().split(' ')[0] : ''" class="custom-input-field dp-custom-transparent !text-xs !p-2" readonly placeholder="Select Date" />
                            </template>
                          </VueDatePicker>
                        </div>
                        <div class="flex-1">
                          <label class="text-[10px] uppercase font-bold text-slate-500 mb-1 block">End Date</label>
                          <VueDatePicker :dark="isDarkMode" v-model="continuousConfig.end_date" :min-date="continuousConfig.start_date || minStartDate" :disabled-dates="isDisabledDate" model-type="yyyy-MM-dd" :enable-time-picker="false" format="MM/dd/yyyy" auto-apply required input-class-name="custom-input-field dp-custom-transparent" :max-date="maxDateLimit" >
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
                          <AppTimePicker 
                            v-model="continuousConfig.start_time" 
                            min="04:00" 
                            max="20:00" 
                            required 
                            @change="handleTimeChange(continuousConfig)" 
                          />
                        </div>
                        <div class="flex-1">
                          <div class="label-container" style="margin-bottom: 4px;">
                            <label class="text-[10px] uppercase font-bold text-slate-500 mb-0">Time To</label>
                            <div class="info-btn-wrapper">
                              <span class="material-symbols-outlined" @click.stop="toggleHelp('endTime')" style="font-size: 14px; cursor: pointer; color: #94a3b8; transition: color 0.2s;" onmouseover="this.style.color='#b979cc'" onmouseout="this.style.color='#94a3b8'">info</span>
                              <transition name="fade-pop"><div v-if="helpState.endTime" class="simple-popup" style="width:160px; font-size:10px; font-weight:normal;">Valid times: 04:00 AM - 08:00 PM</div></transition>
                            </div>
                          </div>
                          <AppTimePicker 
                            v-model="continuousConfig.end_time" 
                            min="04:00" 
                            max="20:00" 
                            required 
                            @change="handleTimeChange(continuousConfig)" 
                          />
                        </div>
                      </div>
                    </div>

                    <!-- Expanded Schedules UI -->
                    <div v-if="scheduleType === 'continuous' && schedules.length > 0" style="margin-top: 16px; margin-bottom: 8px; color: #b979cc; font-size: 11px; font-weight: bold; display: flex; align-items: center; gap: 4px;">
                      <span class="material-symbols-outlined" style="font-size: 14px;">info</span>
                      You can customize the Time for specific days (e.g., half-day on the last day) below:
                    </div>
                    <div v-for="(sch, index) in schedules" :key="index" class="schedule-inputs-wrapper schedule-card-item" style="position: relative;">
                      <div style="flex: 1; min-width: 130px;">
                        <label style="color: #94a3b8; font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 6px; display: block;">Date</label>
                        <VueDatePicker :dark="isDarkMode" v-model="sch.date" @update:model-value="handleScheduleDateChange($event, index)" :disabled="scheduleType === 'continuous'" :min-date="minStartDate" :disabled-dates="isDisabledDate" model-type="yyyy-MM-dd" :enable-time-picker="false" format="MM/dd/yyyy" auto-apply required input-class-name="custom-input-field dp-custom-transparent" :max-date="maxDateLimit" >
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
                        <AppTimePicker 
                          v-model="sch.start_time" 
                          min="04:00" 
                          max="20:00" 
                          required 
                          @change="validateScheduleTime(index)" 
                        />
                      </div>
                      <div style="flex: 1; min-width: 130px;">
                        <div class="label-container" style="margin-bottom: 6px;">
                          <label style="color: #94a3b8; font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 0;">End Time</label>
                          <div class="info-btn-wrapper">
                            <span class="material-symbols-outlined" @click.stop="toggleHelp('endTime')" style="font-size: 14px; cursor: pointer; color: #94a3b8; transition: color 0.2s;" onmouseover="this.style.color='#b979cc'" onmouseout="this.style.color='#94a3b8'">info</span>
                            <transition name="fade-pop"><div v-if="helpState.endTime" class="simple-popup" style="width:160px; font-size:10px; font-weight:normal;">Valid times: 04:00 AM - 08:00 PM</div></transition>
                          </div>
                        </div>
                        <AppTimePicker 
                          v-model="sch.end_time" 
                          min="04:00" 
                          max="20:00" 
                          required 
                          @change="validateScheduleTime(index)" 
                        />
                      </div>
                      <button type="button" v-if="scheduleType === 'staggered' && schedules.length > 1" @click.prevent="removeSchedule(index)" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" title="Remove Schedule">
                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                      </button>
                    </div>
                  </div>

                  <!-- Computed Global Dates Sub-Grid -->
                  <div class="form-sub-grid">
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
                      <div class="custom-input-field" style="display: flex; align-items: center; gap: 8px; opacity: 0.85; cursor: not-allowed;">
                        <span class="material-symbols-outlined" style="font-size: 16px; color: #b979cc;">calendar_month</span>
                        {{ computedStartDate || 'Awaiting schedule...' }}
                      </div>
                    </div>

                    <div class="input-group">
                      <label class="form-label">Calculated End Date</label>
                      <div class="custom-input-field" style="display: flex; align-items: center; gap: 8px; opacity: 0.85; cursor: not-allowed;">
                        <span class="material-symbols-outlined" style="font-size: 16px; color: #b979cc;">event</span>
                        {{ computedEndDate || 'Awaiting schedule...' }}
                      </div>
                    </div>
                  </div>
                </section>

                <div class="form-divider"></div>

                <!-- Section 04: Venue Location & Setup (MOVED BELOW SCHEDULE!) -->
                <section class="form-section-card">

                  <div class="input-group" style="margin-bottom: 20px;">
                    <label class="form-label">Venue Location *</label>
                    <div class="toggle-container" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; min-height: 42px; height: auto; padding: 4px 0;">
                      <label class="location-radio-label" style="font-size: 14px; cursor: pointer; display: flex; align-items: center; white-space: nowrap;">
                        <input type="radio" :value="true" v-model="form.is_inside_bsu" style="accent-color: #b979cc; transform: scale(1.1); margin-right: 5px;" /> Inside BSU
                      </label>
                      <label class="location-radio-label" style="font-size: 14px; cursor: pointer; display: flex; align-items: center; white-space: nowrap;">
                        <input type="radio" :value="false" v-model="form.is_inside_bsu" style="accent-color: #b979cc; transform: scale(1.1); margin-right: 5px;" /> Outside BSU
                      </label>
                      <label class="location-radio-label" style="font-size: 14px; cursor: pointer; display: flex; align-items: center; white-space: nowrap;">
                        <input type="radio" value="mixed" v-model="form.is_inside_bsu" style="accent-color: #b979cc; transform: scale(1.1); margin-right: 5px;" /> Mixed (Inside &amp; Outside)
                      </label>
                    </div>
                    <div v-if="form.is_inside_bsu === 'mixed'" class="mixed-mode-hint" style="margin-top: 8px; font-size: 12px; padding: 6px 10px; border-radius: 6px;">
                      💡 Mixed mode: all venues are shown. Each venue will use its own inside/outside baseline rate automatically.
                    </div>
                  </div>

                  <div class="input-group">
                    <label class="form-label">Venue *</label>
                    <div class="custom-multiselect-container">
                      <div v-if="venueDropdownOpen" class="multiselect-backdrop" @click="venueDropdownOpen = false"></div>
                      
                      <div 
                        class="custom-input-field multiselect-trigger" 
                        @click="venueDropdownOpen = !venueDropdownOpen"
                        :class="{ 'is-open': venueDropdownOpen }"
                      >
                        <span v-if="!form.venues || form.venues.length === 0" class="placeholder-text">Select venues...</span>
                        <span v-else class="selected-text">{{ form.venues.length }} venue(s) selected</span>
                        <span class="dropdown-arrow">▼</span>
                      </div>

                      <div v-if="venueDropdownOpen" class="multiselect-menu">
                        <label 
                          v-for="v in filteredVenues" 
                          :key="v.venue_id" 
                          class="multiselect-option"
                        >
                          <input type="checkbox" :value="v.venue_id" v-model="form.venues" class="multiselect-checkbox" />
                          <span>{{ v.venue_name }}</span>
                        </label>
                        <div class="multiselect-divider"></div>
                        <button type="button" class="btn-add-custom-venue" @click.prevent="addCustomVenue">
                          + Add Custom Venue
                        </button>
                      </div>

                      <div class="chips-container" v-if="form.venues && form.venues.length > 0">
                        <div v-for="vid in form.venues" :key="vid" class="venue-chip">
                          <span class="chip-text">{{ getVenueName(vid) }}</span>
                          <button type="button" class="chip-remove" @click.stop="removeVenue(vid)">×</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </section>

                <div class="form-divider"></div>

                <!-- Section 05: Itemized Budget Breakdown -->
                <section class="form-section-card">

                  <div class="budget-section">
                    <BudgetBuilder 
                      ref="budgetBuilder"
                      v-model:venueBudgets="form.venue_budgets" 
                      :venues="form.venues" 
                      :baselineSettings="baselineSettings"
                      :isOutsideBsu="isOutsideBsu" 
                      :computedDays="computedDays"
                      :filteredVenues="venues"
                      :customVenuesList="customVenuesList"
                      :venueInsideMap="venueInsideMap"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                      <!-- Overall Target Participants Banner -->
                      <div class="attendance-banner-card">
                        <div class="attendance-banner-label">Overall Expected Attendance (Auto-calculated)</div>
                        <div class="attendance-banner-value">
                          {{ form.target_participants || 0 }} Pax
                        </div>
                      </div>

                      <!-- Grand Total Banner Card -->
                      <div class="grand-total-banner-card">
                        <div class="grand-total-label-banner">Grand Total (PHP)</div>
                        <div class="grand-total-value-banner">
                          ₱{{ Number(form.proposed_budget || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </section>

                <div class="form-divider"></div>

                <!-- Section 06: Document Attachments -->
                <section class="form-section-card">

                  <div class="attachment-section-container">
                    <label class="form-label">Upload Activity Design (PDF) *</label>
                    <div class="attachment-display-grid">
                      <div class="attachment-upload-column">
                        <div class="upload-dropzone" @click="$refs.fileInput.click()">
                          <input ref="fileInput" type="file" @change="handleFileUpload" accept=".pdf" style="display: none;" />
                          <span class="upload-icon">📤</span>
                          <p class="upload-text">Upload Activity Design Document</p>
                          <p class="upload-hint">PDF format (Max 10MB)</p>
                        </div>
                      </div>
                      <div class="attachment-preview-column">
                        <div v-if="designFile" class="uploaded-file-display" style="flex-direction: column; align-items: flex-start;">
                          <div class="uploaded-file-tag" style="width: 100%;">
                            <span class="uploaded-file-name">📄 {{ designFile.name }}</span>
                            <button type="button" @click="removeFile" class="remove-file-btn">Remove</button>
                          </div>
                          <!-- Document Previews -->
                          <div class="document-previews" style="margin-top: 15px; width: 100%;" v-if="designFile.previewUrl">
                            <p style="color: #b979cc; font-size: 13px; font-weight: bold; margin-bottom: 8px;">Document Preview:</p>
                            <div style="display: flex; justify-content: flex-end; margin-bottom: 8px;">
                              <button @click.prevent="expandToNewTab(designFile.previewUrl)" style="background: rgba(185, 121, 204, 0.1); border: 1px solid rgba(185, 121, 204, 0.3); color: #e9d5ff; padding: 4px 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; font-size: 13px;">
                                <span class="material-symbols-outlined" style="font-size: 14px; margin-right: 4px;">open_in_new</span> Expand
                              </button>
                            </div>
                            <iframe ref="pdfIframeRef" :src="getPdfViewerUrl(designFile.previewUrl)" width="100%" height="400px" style="border: 1px solid #b979cc; border-radius: 8px;" @load="postPdfToIframe"></iframe>
                          </div>
                        </div>
                        <p v-else class="no-file-uploaded-text">No file uploaded yet.</p>
                      </div>
                    </div>
                  </div>
                </section>
              </div>

              <!-- Floating Sticky Action Dock -->
              <div class="form-sticky-dock">
                <div class="form-sticky-dock-inner">
                  <div class="dock-summary-group">
                    <div class="dock-stat-item">
                      <span class="dock-stat-label">Expected Pax</span>
                      <span class="dock-stat-value">{{ form.target_participants || 0 }} Pax</span>
                    </div>
                    <div class="dock-stat-item">
                      <span class="dock-stat-label">Proposed Grand Total</span>
                      <span class="dock-stat-value">₱{{ Number(form.proposed_budget || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                    </div>
                  </div>
                  <div class="dock-actions-group">
                    <button 
                      type="button"
                      @click="goBack" 
                      class="back-button"
                    >
                      &#8592; Back
                    </button>
                    <button 
                      type="submit" 
                      class="submit-action-btn"
                    >
                      Submit Design &#8594;
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
        </main>
    </div>
  </div>
</template>

<script setup>
import { useHolidays } from '../../utils/useHolidays';
const { isDisabledDate, fetchHolidays, getWorkingDaysDiff, addWorkingDays } = useHolidays();
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import BudgetBuilder from '../../components/BudgetBuilder.vue';
import AppTimePicker from '../../components/AppTimePicker.vue';
import api from '../../api';

const router = useRouter();
const route = useRoute();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));
const isDarkMode = ref(typeof document !== 'undefined' ? document.documentElement.classList.contains('dark') : false);
let themeObserver = null;

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

const venues = ref([]);
const customVenue = ref('');
const formTypes = ref([]);
const GADMandates = ref([]);
const genderIssues = ref([]);
const ActClassification = ref([]); 
const customMandate = ref('');
const customGenderIssue = ref('');

const mandatesListRef = ref(null);
const issuesListRef = ref(null);
const mandateSearchQuery = ref('');
const issueSearchQuery = ref('');

const filteredMandatesList = computed(() => {
  if (!mandateSearchQuery.value) return GADMandates.value || [];
  const q = mandateSearchQuery.value.toLowerCase().trim();
  return (GADMandates.value || []).filter(m => 
    (m.code && m.code.toLowerCase().includes(q)) || 
    (m.title && m.title.toLowerCase().includes(q))
  );
});

const filteredGenderIssuesList = computed(() => {
  if (!issueSearchQuery.value) return genderIssues.value || [];
  const q = issueSearchQuery.value.toLowerCase().trim();
  return (genderIssues.value || []).filter(i => 
    i.title && i.title.toLowerCase().includes(q)
  );
});

const isMandateSelected = (id) => {
  const list = form.value?.gad_mandate_id || form.gad_mandate_id;
  if (!list || !Array.isArray(list)) return false;
  return list.some(item => String(item) === String(id));
};

const isIssueSelected = (id) => {
  const list = form.value?.gender_issue_id || form.gender_issue_id;
  if (!list || !Array.isArray(list)) return false;
  return list.some(item => String(item) === String(id));
};

const selectedMandatesCount = computed(() => {
  const list = form.value?.gad_mandate_id || form.gad_mandate_id;
  return Array.isArray(list) ? list.length : 0;
});

const selectedIssuesCount = computed(() => {
  const list = form.value?.gender_issue_id || form.gender_issue_id;
  return Array.isArray(list) ? list.length : 0;
});

const scheduleType = ref('staggered');
const continuousConfig = ref({
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '' });

const schedules = ref([{ date: '', start_time: '', end_time: '' }]);


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
  schedules.value.push({ date: '', start_time: '', end_time: '' });
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
const getVenueName = (id) => {
  if (String(id).startsWith('temp_')) {
    const custom = customVenuesList.value.find(x => String(x.venue_id) === String(id));
    return custom ? custom.venue_name : 'Custom Venue';
  }
  const v = venues.value.find(x => String(x.venue_id) === String(id));
  return v ? v.venue_name : 'Unknown Venue';
};


const form = ref({
  form_type: '',
  nature: '',
  activity_classification_id: '',
  gad_mandate_id: [],
  gender_issue_id: [],
  activity_title: '',
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  venues: [],
  venue_budgets: {},
  venue: '',
  is_inside_bsu: true,
  target_participants: '',
  proposed_budget: 0,
  budget_items: [
    { name: 'Meals', total: '', meals_needed: { breakfast: 0, lunch: 0, dinner: 0 } },
    { name: 'Snacks', total: '', meals_needed: { am_snack: 0, pm_snack: 0 } },
    { name: 'Function Room/Venue', total: '' },
    { name: 'Accommodation', total: '' },
    { name: 'Equipment Rental', total: '' },
    { name: 'Professional Fee/Honoraria', total: '' },
    { name: 'Token/s', total: '' },
    { name: 'Materials and Supplies', total: '' },
    { name: 'Transportation', total: '' },
    { name: 'Others', total: '' }
  ]
});

/*
watch(() => form.value?.venues, (newVenues) => {
  if (!newVenues) return;
  newVenues.forEach(vid => {
    if (!form.value.venue_budgets[vid]) {
      form.value.venue_budgets[vid] = [
        { name: 'Meals', total: '', meals_needed: { breakfast: 0, lunch: 0, dinner: 0 } },
        { name: 'Snacks', total: '', meals_needed: { am_snack: 0, pm_snack: 0 } },
        { name: 'Function Room/Venue', total: '' },
        { name: 'Accommodation', total: '' },
        { name: 'Equipment Rental', total: '' },
        { name: 'Professional Fee/Honoraria', total: '', pax: '' },
        { name: 'Token/s', total: '', pax: '' },
        { name: 'Materials and Supplies', total: '' },
        { name: 'Transportation', total: '' },
        { name: 'Others', total: '' }
      ];
    }
  });
}, { deep: true });
*/

const originalData = ref(null);
const fileInput = ref(null);
const venueDropdownOpen = ref(false);
const removeVenue = (vid) => {
  form.value.venues = form.value.venues.filter(id => String(id) !== String(vid));
};

const customVenuesList = ref([]);
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
      if (!value) {
        return 'You need to write something!';
      }
    }
  });

  if (venueName) {
    const tempId = 'temp_' + Date.now();
    customVenuesList.value.push({
      venue_id: tempId,
      venue_name: venueName
    });
    if (!form.value.venues) form.value.venues = [];
    form.value.venues.push(tempId);
  }
};

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

const designFile = ref(null);

const pdfIframeRef = ref(null);
const postPdfToIframe = () => {
  if (pdfIframeRef.value?.contentWindow && designFile.value?.rawBytes) {
    pdfIframeRef.value.contentWindow.postMessage({
      type: 'LOAD_PDF_DATA',
      bytes: designFile.value.rawBytes
    }, '*');
  }
};

const handleFileUpload = (event) => {
  if (event.target.files.length > 0) {
    const file = event.target.files[0];
    
    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid File Type',
        text: 'Only PDF files are allowed.',
        confirmButtonColor: '#b979cc'
      });
      removeFile();
      return;
    }

    if (file.size > 10 * 1024 * 1024) {
      Swal.fire({
        icon: 'error',
        title: 'File Too Large',
        text: 'The file size must not exceed 10 MB.',
        confirmButtonColor: '#b979cc'
      });
      removeFile();
      return;
    }

    designFile.value = file;
    file.previewUrl = URL.createObjectURL(file);

    // Read raw ArrayBuffer for mobile devices to bypass cross-frame blob isolation
    const reader = new FileReader();
    reader.onload = () => {
      file.rawBytes = reader.result;
      postPdfToIframe();
    };
    reader.readAsArrayBuffer(file);
  }
};

const removeFile = () => {
  designFile.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const formatBudgetName = (name) => {
  if (!name) return '';
  return name.replace(/(\(.*\))/g, '<span class="budget-item-subtext">$1</span>');
};

const fetchVenues = async () => {
  try {
    const response = await api.get('venues');
    if (Array.isArray(response.data)) {
      venues.value = response.data;
    } else if (response.data && response.data.success) {
      venues.value = response.data.data || [];
    }
  } catch (error) {
    console.error('Error fetching venues:', error);
  }
};

const filteredVenues = computed(() => {
  if (form.value.is_inside_bsu === 'mixed') {
    return venues.value.filter(v => v.venue_name && v.venue_name.trim() !== '');
  }
  return venues.value.filter(v => 
    (v.is_inside_bsu == 1 || v.is_inside_bsu === true) === form.value.is_inside_bsu &&
    v.venue_name && v.venue_name.trim() !== ''
  );
});

const venueInsideMap = computed(() => {
  if (form.value.is_inside_bsu !== 'mixed') return null;
  const map = {};
  venues.value.forEach(v => { map[String(v.venue_id)] = v.is_inside_bsu == 1 || v.is_inside_bsu === true; });
  customVenuesList.value.forEach(v => { if (!map[String(v.venue_id)]) map[String(v.venue_id)] = false; });
  return map;
});

watch(() => form.value.is_inside_bsu, () => {
  if (form.value.venues && form.value.venues.length > 0) {
    form.value.venues = [];
  }
  
  if (form.value.venue && form.value.venue !== 'Other') {
    const isValid = filteredVenues.value.some(v => v.venue_id == form.value.venue);
    if (!isValid) {
      form.value.venue = '';
    }
  }
});



const fetchFormTypes = async () => {
  try {
    const res = await api.get('get-form-types');
    formTypes.value = res.data;
  } catch (error) {
    console.error('Error fetching form types:', error);
  }
};

const fetchActivityClassifications = async () => {
  try {
    const res = await api.get('get-activity-classifications');
    ActClassification.value = res.data;
  } catch (error) {
    console.error('Error fetching activity classifications:', error);
  }
};

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

const fetchGenderIssues = async (mandateIds) => {
  let ids = mandateIds || form.value?.gad_mandate_id || gad_mandate_id?.value;
  if (typeof ids === 'string' || typeof ids === 'number') ids = [ids];
  if (!ids || !Array.isArray(ids) || ids.length === 0 ) {
    genderIssues.value = [];
    return;
  }
  try {
    const allIssues = [];
    for (const mandateId of ids) {
       if (mandateId !== 'Other') {
           let url = `get-gender-issues?mandates=${mandateId}`;
             if (form.value && form.value.activity_classification_id) {
                 url += '&classification=' + form.value.activity_classification_id;
             }
             const res = await api.get(url);
           allIssues.push(...res.data);
       }
    }
    genderIssues.value = allIssues;
  } catch (error) {
    console.error('Error fetching gender issues:', error);
  }
};

watch(() => form.value.activity_classification_id, (newVal) => {
    form.value.gad_mandate_id = [];
    form.value.gender_issue_id = [];
    fetchGADMandates();
  });

  watch(() => form.value.gad_mandate_id, (newVal) => {
  form.value.gender_issue_id = [];
  fetchGenderIssues(newVal);
});

const validBudgetMultipliers = item => Array.isArray(item?.mult)
  ? item.mult.filter(multiplier => multiplier && typeof multiplier === 'object')
  : [];

// Meal-time mismatch warning helper (non-blocking)
const checkMealTimeMismatch = async () => {
  const allSchedules = schedules.value || [];
  const budgetItems = Object.values(form.value.venue_budgets || {}).flat();
  const getAmount = name => {
    const item = budgetItems.find(i => i.name === name);
    if (!item) return 0;
    const mults = Array.isArray(item.mult) ? item.mult.filter(m => m && typeof m === 'object') : [];
    return (Number(item.rate) || 0) * mults.reduce((p, m) => p * (Number(m.q) || 0), 1);
  };
  const hasBf = getAmount('Breakfast') > 0;
  const hasAm = getAmount('AM Snack') > 0;
  const hasLn = getAmount('Lunch') > 0;
  const hasPm = getAmount('PM Snack') > 0;
  const hasDn = getAmount('Dinner') > 0;
  const warnings = [];
  for (const sch of allSchedules) {
    if (!sch.start_time || !sch.end_time) continue;
    const [sh, sm] = sch.start_time.split(':').map(Number);
    const [eh, em] = sch.end_time.split(':').map(Number);
    const startMins = sh * 60 + sm;
    const endMins = eh * 60 + em;
    const dateLabel = sch.date ? ` (${sch.date})` : '';
    if (endMins <= 720 && (hasLn || hasPm || hasDn)) {
      warnings.push(`Schedule${dateLabel} ends at ${sch.end_time} (AM only) but budget includes: ${[hasLn && 'Lunch', hasPm && 'PM Snack', hasDn && 'Dinner'].filter(Boolean).join(', ')}.`);
    }
    if (startMins >= 780 && (hasBf || hasAm)) {
      warnings.push(`Schedule${dateLabel} starts at ${sch.start_time} (PM only) but budget includes: ${[hasBf && 'Breakfast', hasAm && 'AM Snack'].filter(Boolean).join(', ')}.`);
    }
  }
  if (warnings.length > 0) {
    const result = await Swal.fire({
      icon: 'warning',
      title: 'Possible Meal Mismatch',
      html: `<div style="text-align:left;font-size:13px;"><ul style="margin:0;padding-left:18px;">${warnings.map(w => `<li style="margin-bottom:6px;">${w}</li>`).join('')}</ul><br>You may still proceed if this is intentional.</div>`,
      showCancelButton: true,
      confirmButtonText: 'Proceed Anyway',
      cancelButtonText: 'Go Back & Fix',
      confirmButtonColor: '#b979cc',
      cancelButtonColor: '#64748b'
    });
    return result.isConfirmed;
  }
  return true;
};

const budgetValidationError = () => {
  for (const venueId of form.value.venues || []) {
    const items = form.value.venue_budgets?.[venueId] || [];
    let venueTotal = 0;
    for (const item of items) {
      const multipliers = validBudgetMultipliers(item);
      const amount = (Number(item.rate) || 0) * multipliers.reduce((total, multiplier) => total * (Number(multiplier.q) || 0), 1);
      if (amount < 0) {
        return 'Each budget line must have a non-negative amount.';
      }
      venueTotal += amount;
    }
    if (venueTotal <= 0) return `Please enter at least one positive budget amount for ${getVenueName(venueId)}.`;
  }
  return '';
};

watch(() => form.value.venue_budgets, (newBudgets) => {
  let grandTotal = 0;
  let maxOverallPax = 0;
  
  if (newBudgets) {
    Object.values(newBudgets).forEach(items => {
      grandTotal += items.reduce((sum, item) => {
        const mults = validBudgetMultipliers(item).reduce((p, m) => p * (Number(m.q) || 0), 1);
        return sum + ((Number(item.rate) || 0) * mults);
      }, 0);
      
      let venueMaxPax = 0;
      items.forEach(item => {
        const paxMult = validBudgetMultipliers(item).find(m => /^(pax|person|persons|head|heads)$/i.test(String(m.u || '').trim()));
        if (paxMult && Number(paxMult.q) > venueMaxPax) {
          venueMaxPax = Number(paxMult.q);
        }
      });
      maxOverallPax += venueMaxPax;
    });
  }
  
  form.value.proposed_budget = grandTotal;
  form.value.target_participants = maxOverallPax > 0 ? maxOverallPax : '';
}, { deep: true });

watch(() => computedStartDate.value, (newDate, oldDate) => {
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
      form.value.start_date = '';
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
    if (form.value.end_date) {
      const durationValidation = isValidActivityDuration(newDate, form.value.end_date);
      if (!durationValidation.valid) {
        document.activeElement?.blur();
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Duration',
          text: durationValidation.reason,
          confirmButtonColor: '#b979cc'
        });
        form.value.start_date = '';
      }
    }
  }
});

watch(() => form.value.end_date, (newDate) => {
  if (newDate) {
    const validation = isValidActivityDate(newDate, false);
    if (!validation.valid) {
      document.activeElement?.blur();
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Date',
        text: validation.reason,
        confirmButtonColor: '#b979cc'
      });
      form.value.end_date = '';
      return;
    }
    if (form.value.start_date) {
      const durationValidation = isValidActivityDuration(form.value.start_date, newDate);
      if (!durationValidation.valid) {
        document.activeElement?.blur();
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Duration',
          text: durationValidation.reason,
          confirmButtonColor: '#b979cc'
        });
        form.value.end_date = '';
      }
    }
  }
});

const isValidTime = (timeStr) => {
  if (!timeStr) return true;
  return timeStr >= "04:00" && timeStr <= "20:00";
};

watch(() => form.value.start_time, (newTime) => {
  if (newTime && !isValidTime(newTime)) {
    document.activeElement?.blur();
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Time',
      text: 'Must be set between 04:00 AM and 08:00 PM.',
      confirmButtonColor: '#b979cc'
    });
    form.value.start_time = '';
  }
});

watch(() => form.value.end_time, (newTime) => {
  if (newTime && !isValidTime(newTime)) {
    document.activeElement?.blur();
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Time',
      text: 'Must be set between 04:00 AM and 08:00 PM.',
      confirmButtonColor: '#b979cc'
    });
    form.value.end_time = '';
  }
});

watch([() => form.value.start_time, () => form.value.end_time], ([newStart, newEnd]) => {
  if (scheduleType.value === 'staggered') return;
  if (form.value.start_date && form.value.end_date && form.value.start_date !== form.value.end_date) return;
  if (newStart && newEnd) {
    if (newStart >= newEnd) {
      document.activeElement?.blur();
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Time Range',
        text: 'End time must be after start time.',
        confirmButtonColor: '#b979cc'
      });
      form.value.end_time = '';
    }
  }
});

// Computed Properties for Auto-calculation





const computedDays = computed(() => {
  let totalDays = 0;
  schedules.value.forEach(s => {
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

const isOutsideBsu = computed(() => {
  if (form.value.is_inside_bsu === 'mixed') return false;
  return !form.value.is_inside_bsu;
});

// Baseline Settings
const baselineSettings = ref({
  meals_inside: 220,
  meals_outside: 350,
  snacks_inside: 85,
  snacks_outside: 150,
  pf_honoraria: 2258.25,
  tokens: 1000,
  materials: 1000,
  transportation_limit: 20000
});

const fetchBaselineSettings = async () => {
  try {
    const res = await api.get('settings/baseline');
    if (res.data) {
      baselineSettings.value = res.data;
    }
  } catch (error) {
    console.error('Failed to fetch baseline settings:', error);
  }
};

const adSubmissionLimitEnabled = ref(true);

const fetchSystemSettings = async () => {
  try {
    const res = await api.get('settings/system');
    adSubmissionLimitEnabled.value = res.data.ad_submission_limit_enabled ?? true;
  } catch (err) {
    console.error('Failed to fetch system settings:', err);
  }
};

const submitActivityDesign = async () => {
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
  form.value.start_date = sortedSchedules[0]?.date || '';
  form.value.end_date = sortedSchedules[sortedSchedules.length - 1]?.date || '';
  form.value.start_time = sortedSchedules[0]?.start_time || '08:00';
  form.value.end_time = sortedSchedules[sortedSchedules.length - 1]?.end_time || '17:00';
  if (schedules.value.length === 0 || schedules.value.some(s => !s.date || !s.start_time || !s.end_time)) {
      Swal.fire({ icon: 'warning', title: 'Incomplete Schedules', text: 'Please complete all schedule dates and times.' });
      return;
  }
  
  

  if (adSubmissionLimitEnabled.value) {
    // Check if today is a weekday
    const currentDay = new Date().getDay();
    if (currentDay === 0 || currentDay === 6) {
      Swal.fire({
        icon: 'warning',
        title: 'Submission Not Allowed',
        text: 'Submissions are only allowed from Monday to Friday.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
  }

  // Validate Cause of Gender Issue
  if (!form.value.gender_issue_id || form.value.gender_issue_id.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Field',
      text: 'Please select at least one Cause of Gender Issue before submitting.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }



  // Validate target participants
  if (Number(form.value.target_participants) <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Participants',
      text: 'Target participants must be at least 1.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  // Validate start date
  // Validate activity duration
  const durationValidation = isValidActivityDuration(form.value.start_date, form.value.end_date);
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
  if (!isValidTime(form.value.start_time) || !isValidTime(form.value.end_time)) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Time',
      text: 'Must be set between 04:00 AM and 08:00 PM.',
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


  // Warn about meal-time mismatch (non-blocking)
  const mealTimeOk = await checkMealTimeMismatch();
  if (!mealTimeOk) return;

  // Validate venue budgets — at least one item must have a value per venue
  const emptyVenues = form.value.venues.filter(vid => {
    const items = form.value.venue_budgets[vid];
    if (!items) return true;
    const budgetTotal = items.reduce((sum, item) => sum + ((Number(item.rate) || 0) * validBudgetMultipliers(item).reduce((p, m) => p * (Number(m.q) || 0), 1)), 0);
    return budgetTotal === 0;
  });
  if (emptyVenues.length > 0) {
    const names = emptyVenues.map(vid => `"${getVenueName(vid)}"`).join(', ');
    Swal.fire({
      icon: 'warning',
      title: 'Empty Venue Budget',
      html: `The following venue(s) have no budget entries:<br><br><strong>${names}</strong><br><br>Please fill in at least one budget item per venue before submitting.`,
      confirmButtonColor: '#b979cc'
    });
    return;
  }
  const budgetError = budgetValidationError();
  if (budgetError) {
    Swal.fire({ icon: 'warning', title: 'Invalid Budget', text: budgetError, confirmButtonColor: '#b979cc' });
    return;
  }

  if (!designFile.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Document',
      text: 'Please upload the Activity Design PDF document.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  const submitConfirm = await Swal.fire({
    title: 'Confirm Submission',
    text: 'Are you sure you want to submit this Activity Design?',
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
    text: 'Please wait while we submit your design and dispatch email notifications.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  try {
    
    const sortedSchedules = [...schedules.value].sort((a, b) => new Date(a.date) - new Date(b.date));
    form.value.start_time = sortedSchedules[0]?.start_time || '08:00';
    form.value.end_time = sortedSchedules[sortedSchedules.length - 1]?.end_time || '17:00';
    
    const formData = new FormData();
    
    formData.append('form_type', form.value.form_type || form.value.nature);
    formData.append('activity_classification_id', form.value.activity_classification_id);
    const mandateVal = Array.isArray(form.value.gad_mandate_id)
      ? form.value.gad_mandate_id.join(',')
      : (form.value.gad_mandate_id || '');
    const issueVal = Array.isArray(form.value.gender_issue_id)
      ? form.value.gender_issue_id.join(',')
      : (form.value.gender_issue_id || '');

    formData.append('gad_mandate_id', mandateVal);
    formData.append('gender_issue_id', issueVal);
    
    if (mandateVal.includes('Other')) {
      formData.append('custom_gad_mandate', customMandate.value);
    }
    if (issueVal.includes('Other')) {
      formData.append('custom_gender_issue', customGenderIssue.value);
    }

    formData.append('activity_title', form.value.activity_title);
    formData.append('start_date', form.value.start_date);
    formData.append('end_date', form.value.end_date);
    formData.append('start_time', form.value.start_time);
    formData.append('end_time', form.value.end_time);
    formData.append('user_id', user.value.id || user.value.user_id);
    formData.append('is_inside_bsu', form.value.is_inside_bsu === 'mixed' ? 'mixed' : (form.value.is_inside_bsu ? '1' : '0'));
    formData.append('venues', JSON.stringify(form.value.venues));
    formData.append('custom_venues', JSON.stringify(customVenuesList.value));
    formData.append('target_participants', form.value.target_participants);
    formData.append('schedules', JSON.stringify(schedules.value));
    formData.append('schedule_type', scheduleType.value);
    formData.append('proposed_budget', form.value.proposed_budget);

    let exceedsTransportLimit = false;
    const normalizedBudgetItems = [];

    form.value.venues.forEach(vid => {
      (form.value.venue_budgets[vid] || []).forEach(item => {
        const multipliers = validBudgetMultipliers(item);
        const lineTotalAmount = (Number(item.rate) || 0) * multipliers.reduce((p, m) => p * (Number(m.q) || 0), 1);
        if (item.capKey && lineTotalAmount > Number(baselineSettings.value[item.capKey])) exceedsTransportLimit = true;
        if (item.custom && (!String(item.name).trim() || lineTotalAmount <= 0)) return;
        const isOther = item.custom && item.group === 'materials';
        
        const paxOfItem = Number((multipliers.find(m => /^(pax|person|persons|head|heads)$/i.test(String(m.u || '').trim())) || {}).q) || 0;
        const lineFormulaStr = [('₱' + (Number(item.rate) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })), ...multipliers.map(m => `${Number(m.q) || 0} ${String(m.u || '').trim()}`.trim())].join(' × ');
        
        normalizedBudgetItems.push({
          venue_id: vid === 'Other' ? 'Other' : vid,
          category_id: null,
          item_name: isOther ? 'Others' : item.name,
          sub_item: isOther ? item.name : lineFormulaStr,
          pax: paxOfItem || null,
          unit_cost: Number(item.rate) || 0,
          multipliers: multipliers.map(m => ({ label: String(m.u || '').trim(), value: Number(m.q) || 0 })),
          formula: lineFormulaStr,
          amount: lineTotalAmount
        });
      });
    });

    formData.append('budget_items', JSON.stringify(normalizedBudgetItems));

    if (designFile.value) {
      formData.append('design_file', designFile.value);
    }

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
    const errText = error.response?.data?.errors ? JSON.stringify(error.response.data.errors) : 'Failed to submit activity design. Please double check all details.';
    Swal.fire({
      icon: 'error',
      title: 'Submission Failed',
      text: 'Errors: ' + errText,
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
  if (!user.value.id || !['twg', 'non-twg'].includes(user.value.role)) {
    router.push('/login');
  }
  fetchBaselineSettings();
  fetchFormTypes();
  fetchActivityClassifications();
  fetchGADMandates();
  fetchVenues();
  fetchHolidays();
  fetchSystemSettings();
  document.addEventListener('click', closeAllHelp);
  themeObserver = new MutationObserver(() => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
  });
  themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onUnmounted(() => {
  document.removeEventListener('click', closeAllHelp);
  if (themeObserver) themeObserver.disconnect();
});

</script>

<style scoped src="../../assets/submit-ad-styles.css"></style>

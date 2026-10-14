<template>
  <div style="width: 100%; overflow-x: hidden;">
    <div style="min-height: 100vh; width: 100%;">
      <main class="twg-view-wrapper">
        <div class="main-content-container-ar">
          <div class="form-header-ar">
            <h1 class="form-main-title">Submit Accomplishment Report</h1>
            <p class="form-description-ar">Fill out the accomplishment report form below. All fields marked with * are required.</p>
          </div>

          <div class="form-container-box">
            <form @submit.prevent="submitReport" class="form-main-layout-ar">
              <div class="form-grid-main-ar">
                <!-- Section 01: Activity Reference & Classification -->
                <section class="form-section-card">

                  <div class="input-group-ar">
                    <label class="form-label-ar">Activity Design Control Number *</label>
                    <select 
                      v-model="form.control_number" 
                      required 
                      class="custom-input-field select-arrow-fix"
                    >
                      <option value="" class="dark-option">Select approved activity design...</option>
                      <option v-if="loadingControls" value="" disabled class="dark-option">Loading...</option>
                      <option v-for="control in approvedControls" :key="control.control_number" :value="control.control_number" class="dark-option">
                        {{ control.control_number }} - {{ control.activity_title }}
                      </option>
                      <option v-if="!loadingControls && approvedControls.length === 0" value="" disabled class="dark-option">No approved control numbers found</option>
                    </select>
                  </div>

                  <div class="input-group-ar" style="margin-top: 16px;">
                    <label class="form-label-ar">Activity Title *</label>
                    <textarea 
                      v-model="form.activity_title" 
                      required 
                      rows="2" 
                      class="custom-input-field textarea-no-resize"
                      placeholder="Enter the complete title of the activity"
                    ></textarea>
                  </div>

                  <div class="form-sub-grid-ar" style="margin-top: 16px;">
                    <div class="input-group-ar">
                      <label class="form-label-ar">Form Type *</label>
                      <select 
                        v-model="form.form_type" 
                        required 
                        class="custom-input-field select-arrow-fix"
                      >
                        <option value="" disabled class="dark-option">Select form type...</option>
                        <option 
                          v-for="ft in formTypes" 
                          :key="ft.id" 
                          :value="ft.name" 
                          class="dark-option"
                        >
                          {{ ft.name }}
                        </option>
                      </select>
                    </div>

                    <div class="input-group-ar">
                      <label class="form-label-ar">Activity Classification *</label>
                      <select
                        v-model="form.activity_classification"
                        @change="handleClassificationChange"
                        required
                        class="custom-input-field select-arrow-fix"
                      >
                        <option value="" disabled class="dark-option">Select Classification</option>
                        <option
                          v-for="classification in ActClassification"
                          :key="classification.id"
                          :value="classification.classification_name"
                          class="dark-option"
                        >
                          {{ classification.classification_name }}
                        </option>
                      </select>
                    </div>
                  </div>
                </section>

                <div class="form-divider"></div>

                <!-- Section 02: GAD Alignment & Gender Issues -->
                <section class="form-section-card" id="section-gad-mandates-issues">
                  <div class="form-sub-grid-ar">
                    <!-- GAD Mandates Multiselect -->
                    <div class="input-group-ar">
                      <label class="form-label-ar">Gender Issue / GAD Mandate *</label>
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
                                @change="handleMandateChange" 
                                v-model="form.gad_mandate_id" 
                                :value="mandate.id.toString()" 
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
                    <div class="input-group-ar">
                      <label class="form-label-ar">Cause of Gender Issue *</label>
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
                                :value="issue.id.toString()" 
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

                  <div class="schedules-container-ar" style="margin-bottom: 20px;">
                    <div class="flex justify-between items-center mb-4 flex-wrap gap-4">
                      <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                          <label class="form-label-ar !mb-0 flex items-center gap-2" style="white-space: nowrap;">
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
                    
                    <div v-if="form.schedules.length === 0" style="color: #94a3b8; font-size: 13px; font-style: italic; margin-bottom: 8px;">
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
                    <div v-if="scheduleType === 'continuous' && form.schedules.length > 0" style="margin-top: 16px; margin-bottom: 8px; color: #b979cc; font-size: 11px; font-weight: bold; display: flex; align-items: center; gap: 4px;">
                      <span class="material-symbols-outlined" style="font-size: 14px;">info</span>
                      You can customize the Time and Meals for specific days (e.g., half-day on the last day) below:
                    </div>
                    <div v-for="(sch, index) in form.schedules" :key="index" class="schedule-inputs-wrapper schedule-card-item relative">
                      <div style="flex: 1; min-width: 130px;">
                        <label class="text-[10px] uppercase font-bold text-slate-500 mb-1 block">Date</label>
                        <VueDatePicker :dark="isDarkMode" v-model="sch.date" @update:model-value="handleScheduleDateChange($event, index)" :disabled="scheduleType === 'continuous'" :min-date="minStartDate" :disabled-dates="isDisabledDate" model-type="yyyy-MM-dd" :enable-time-picker="false" format="MM/dd/yyyy" auto-apply required input-class-name="custom-input-field dp-custom-transparent" :max-date="maxDateLimit" >
                          <template #dp-input="{ value }">
                            <input type="text" :value="value ? String(value).replace(',', '').trim().split(' ')[0] : ''" class="custom-input-field dp-custom-transparent !text-xs !p-2" readonly placeholder="Select Date" />
                          </template>
                        </VueDatePicker>
                      </div>
                      <div style="flex: 1; min-width: 130px;">
                        <div class="label-container" style="margin-bottom: 4px;">
                          <label class="text-[10px] uppercase font-bold text-slate-500 mb-0">Start Time</label>
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
                        <div class="label-container" style="margin-bottom: 4px;">
                          <label class="text-[10px] uppercase font-bold text-slate-500 mb-0">End Time</label>
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
                      <button type="button" v-if="scheduleType === 'staggered' && form.schedules.length > 1" @click.prevent="removeSchedule(index)" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" title="Remove Schedule">
                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                      </button>
                    </div>
                  </div>

                  <!-- Computed Global Dates Sub-Grid -->
                  <div class="form-sub-grid-ar">
                    <div class="input-group-ar">
                      <div class="label-container">
                        <label class="form-label-ar">Calculated Start Date</label>
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
                    <div class="input-group-ar">
                      <label class="form-label-ar">Calculated End Date</label>
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

                  <div class="input-group-ar" style="margin-bottom: 20px;">
                    <label class="form-label-ar">Venue Location *</label>
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
                    <div v-if="form.is_inside_bsu === 'mixed'" style="margin-top: 8px; font-size: 12px; color: #94a3b8; padding: 6px 10px; background: rgba(185,121,204,0.07); border-radius: 6px; border: 1px solid rgba(185,121,204,0.2);">
                      💡 Mixed mode: all venues are shown. Each venue will use its own inside/outside baseline rate automatically.
                    </div>
                  </div>

                  <div class="input-group-ar">
                    <label class="form-label-ar">Venue *</label>
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
                          <button type="button" class="chip-remove" @click.stop="removeVenue(vid)">x</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </section>

                <div class="form-divider"></div>

                <!-- Section 05: Attendance & Participant Demographics -->
                <section class="form-section-card">

                  <div class="form-sub-grid-ar">
                    <div class="input-group-ar">
                      <label class="form-label-ar">Male Participants *</label>
                      <input 
                        type="number" 
                        v-model="form.male" 
                        required 
                        min="0"
                        class="custom-input-field"
                        placeholder="0"
                      >
                    </div>
                    <div class="input-group-ar">
                      <label class="form-label-ar">Female Participants *</label>
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

                  <div class="input-group-ar" style="margin-top: 16px;">
                    <label class="form-label-ar">Total Attendees (Auto-calculated) *</label>
                    <input 
                      type="number" 
                      v-model="form.attendees" 
                      required 
                      min="0"
                      class="custom-input-field input-disabled-ar"
                      placeholder="0"
                      readonly
                    >
                  </div>
                </section>

                <div class="form-divider"></div>

                <!-- Section 06: Actual Budgetary Expenditures -->
                <section class="form-section-card">

                  <div class="budget-section">
                    <BudgetBuilder 
                      ref="budgetBuilder"
                      :filteredVenues="venues"
                      :customVenuesList="customVenuesList"
                      v-model:venueBudgets="form.venue_budgets" 
                      :venues="form.venues" 
                      :baselineSettings="baselineSettings"
                      :isOutsideBsu="isOutsideBsu"
                      :computedDays="computedDays"
                      :venueInsideMap="venueInsideMap"
                      :label="''"
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
                        <div class="grand-total-label-banner">Actual Total Expenditures</div>
                        <div class="grand-total-value-banner">
                          ₱{{ Number(form.proposed_budget || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </section>

                <div class="form-divider"></div>

                <!-- Section 07: Evaluation Results -->
                <section class="form-section-card">

                  <div class="evaluation-section-ar">
                    <div class="evaluation-table-wrapper-ar">
                      <table class="evaluation-table-ar">
                        <thead class="evaluation-table-header-ar">
                          <tr>
                            <th class="table-header-cell">Area of Evaluation</th>
                            <th class="table-header-cell rating-col-ar">Average Rating</th>
                            <th class="table-header-cell interpretation-col-ar">Interpretation</th>
                          </tr>
                        </thead>
                        <tbody class="evaluation-table-body-ar">
                          <tr v-for="(item, index) in form.evaluation_items" :key="index">
                            <td class="evaluation-item-name-ar" style="width: 50%;">
                              <span v-if="!item.isCustom">{{ item.area }}</span>
                              <textarea v-else v-model="item.area" class="evaluation-input-field-ar" style="width: 100%; text-align: left; padding-left: 12px; padding-top: 8px; box-sizing: border-box; resize: vertical; min-height: 42px; line-height: 1.4;" placeholder="Enter evaluation area" required></textarea>
                            </td>
                            <td class="evaluation-item-input-cell-ar" style="position: relative;">
                              <input 
                                type="number" 
                                v-model="item.rating" 
                                @input="if(item.rating > 5) item.rating = 5; if(item.rating < 0) item.rating = 0;"
                                @blur="item.rating = Math.min(5, Math.max(0, Number(item.rating) || 0))"
                                min="0" 
                                max="5" 
                                step="0.01" 
                                required
                                class="evaluation-input-field-ar"
                                placeholder="0.00"
                              />
                            </td>
                            <td class="evaluation-interpretation-cell-ar" style="position: relative;">
                              <span :class="['interpretation-tag-ar', getInterpretationClass(item.rating)]">
                                {{ getInterpretation(item.rating) }}
                              </span>
                              <button type="button" @click="removeEvaluationItem(index)" class="btn-remove-other" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); width: 24px; height: 24px; font-size: 14px; padding: 0; display: flex; align-items: center; justify-content: center;" title="Remove">×</button>
                            </td>
                          </tr>
                          <tr>
                            <td colspan="3" style="padding: 12px 16px; border-bottom: none; background: transparent;">
                              <button type="button" @click="addEvaluationItem" class="btn-add-other" style="width: 100%; justify-content: center;">
                                <span>+</span> Add Evaluation Item
                              </button>
                            </td>
                          </tr>
                        </tbody>
                        <tfoot class="evaluation-table-footer-ar">
                          <tr>
                            <td class="total-avg-label-ar">Total Average Rating</td>
                            <td class="total-avg-value-ar">{{ form.rating ? Number(form.rating).toFixed(2) : '—' }}</td>
                            <td class="total-avg-interpretation-ar">
                              <span :class="['interpretation-tag-ar', getInterpretationClass(form.rating)]">
                                {{ getInterpretation(form.rating) }}
                              </span>
                            </td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  </div>
                </section>

                <div class="form-divider"></div>

                <!-- Section 08: Document Attachments -->
                <section class="form-section-card">

                  <div class="attachment-section-container-ar">
                    <div class="attachment-display-grid-ar">
                      <div class="attachment-upload-column-ar">
                        <div class="upload-zone-ar" 
                             @click="$refs.fileInput.click()"
                             @dragover.prevent
                             @dragenter.prevent
                             @drop.prevent="handleDrop">
                          <input 
                            ref="fileInput" 
                            type="file" 
                            @change="handleFileUpload" 
                            accept=".pdf" 
                            class="file-input-hidden" 
                            multiple 
                          />
                          <span class="upload-icon-ar">📤</span>
                          <h4 class="upload-zone-title">Drag &amp; drop your files here</h4>
                          <p class="upload-zone-desc">or click to browse from your computer</p>
                          <span class="upload-zone-badge">Max 10MB per file</span>
                        </div>
                      </div>
                      <div class="attachment-preview-column-ar">
                        <div v-if="uploadedFiles.length > 0" class="uploaded-files-container-ar">
                          <div v-for="(file, index) in uploadedFiles" :key="index" class="uploaded-file-card">
                            <div class="uploaded-file-tag">
                              <span class="uploaded-file-name" style="word-break: break-all;">📄 {{ file.name }}</span>
                              <div class="uploaded-file-actions-ar">
                                <span class="uploaded-file-size-ar">({{ (file.size / 1024).toFixed(2) }} KB)</span>
                                <button type="button" @click.stop="removeFile(index)" class="remove-file-btn">Remove</button>
                              </div>
                            </div>
                            
                            <div v-if="file.previewUrl" class="document-previews" style="width: 100%;">
                              <div style="display: flex; justify-content: flex-end; margin-bottom: 8px;">
                                <button @click.prevent="expandToNewTab(file.previewUrl)" class="btn-expand-preview">
                                  <span class="material-symbols-outlined" style="font-size: 14px; margin-right: 4px;">open_in_new</span> Expand
                                </button>
                              </div>
                              <iframe :src="getPdfViewerUrl(file.previewUrl)" width="100%" height="400px" style="border: 1px solid #b979cc; border-radius: 8px; background: white;"></iframe>
                            </div>
                          </div>
                        </div>
                        <p v-else style="color: #94a3b8; font-size: 14px; text-align: center; padding: 24px 0;">No files uploaded yet.</p>
                      </div>
                    </div>
                  </div>
                </section>
              </div>

              <!-- Budget Exceeded Warning Card -->
              <div v-if="isExceedingLimit" class="ar-limit-warning-card" style="margin-top: 24px;">
                <span class="warning-icon">⚠️</span>
                <div class="warning-content">
                  <h4 class="warning-title">Budget Limit Exceeded</h4>
                  <p class="warning-desc">
                    The actual spending grand total of <strong>₱{{ Number(form.proposed_budget || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</strong> exceeds the approved proposed budget of <strong>₱{{ Number(selectedProposedBudget || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</strong>. This will be flagged for the Director/Admin to review.
                  </p>
                  <p class="warning-instruction">
                    Please file an Activity Design Revision to increase the budget before submitting this report, or adjust the actual spending inputs.
                  </p>
                </div>
              </div>

              <!-- Floating Sticky Action Dock -->
              <div class="form-sticky-dock">
                <div class="form-sticky-dock-inner">
                  <div class="dock-summary-group">
                    <div class="dock-stat-item">
                      <span class="dock-stat-label">Actual Pax</span>
                      <span class="dock-stat-value">{{ form.attendees || form.target_participants || 0 }} Pax</span>
                    </div>
                    <div class="dock-stat-item">
                      <span class="dock-stat-label">Actual Total Expenditures</span>
                      <span class="dock-stat-value">₱{{ Number(form.proposed_budget || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                    </div>
                    <span v-if="isExceedingLimit" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                      ⚠️ Exceeds Approved Budget
                    </span>
                  </div>
                  <div class="dock-actions-group">
                    <button 
                      type="button"
                      @click="router.back()" 
                      class="back-button"
                    >
                      &#8592; Back
                    </button>
                    <button 
                      type="submit" 
                      class="submit-action-btn"
                    >
                      Submit Report &#8594;
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
const { isDisabledDate, fetchHolidays } = useHolidays();
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import api from '../../api';
import BudgetBuilder from '../../components/BudgetBuilder.vue';
import AppTimePicker from '../../components/AppTimePicker.vue';

const router = useRouter();
const route = useRoute();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));
const isDarkMode = ref(document.documentElement.classList.contains('dark'));
let themeObserver = null;

const menuItems = computed(() => {
  if (route.path.includes('/staff')) return staffMenu;
  return [];
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



// Validations are minimal since AD is already approved
const pfPax = ref('');
const tokensPax = ref('');
const mealsSelected = ref({ breakfast: false, lunch: false, dinner: false });
const snacksSelected = ref({ am: false, pm: false });

const form = ref({
  activity_title: '',
  control_number: '',
  form_type: '',
  nature: '',
  activity_classification_id: '',
  activity_classification: '',
  gad_mandate_id: [],
  gender_issue_id: [],
  target_participants: '',
  schedule_type: 'continuous',
  schedules: [],
  act_design_id: null,
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  venues: [],
  venue_budgets: {},
  venue: '',
  is_inside_bsu: true,
  attendees: 0,
  male: '',
  female: '', 
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
  ],
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

const GADMandates = ref([]);
const genderIssues = ref([]);

const scheduleType = ref('continuous');
const continuousConfig = ref({
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  meals_and_snacks: { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false }
});

const computedStartDate = computed(() => {
  if (scheduleType.value === 'continuous') return continuousConfig.value.start_date;
  return form.value.start_date;
});
const computedEndDate = computed(() => {
  if (scheduleType.value === 'continuous') return continuousConfig.value.end_date;
  return form.value.end_date;
});

// Meal-time mismatch warning helper (non-blocking)
const checkMealTimeMismatch = async () => {
  const allSchedules = form.value.schedules || [];
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

const computedDays = computed(() => {
  let totalDays = 0;
  form.value.schedules.forEach(s => {
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

const isOutsideBsu = computed(() => form.value.is_inside_bsu === false || form.value.is_inside_bsu === 'false');

const recalculateMeals = (vId) => {
  const budgetItems = form.value.venue_budgets[vId];
  if (!budgetItems) return;
  const mealsItem = budgetItems.find(i => i.name === 'Meals');
  if (mealsItem && mealsItem.meals_needed) {
    const { breakfast, lunch, dinner } = mealsItem.meals_needed;
    const bPax = Number(breakfast) || 0;
    const lPax = Number(lunch) || 0;
    const dPax = Number(dinner) || 0;
    const mealsRate = isOutsideBsu.value ? baselineSettings.value.meals_outside : baselineSettings.value.meals_inside;
    const days = computedDays.value || 1;
    const totalCost = ((bPax * mealsRate) + (lPax * mealsRate) + (dPax * mealsRate)) * days;
    mealsItem.total = totalCost > 0 ? totalCost.toFixed(2) : '';
  }
};

const recalculateSnacks = (vId) => {
  const budgetItems = form.value.venue_budgets[vId];
  if (!budgetItems) return;
  const snacksItem = budgetItems.find(i => i.name === 'Snacks');
  if (snacksItem && snacksItem.meals_needed) {
    const { am_snack, pm_snack } = snacksItem.meals_needed;
    const amPax = Number(am_snack) || 0;
    const pmPax = Number(pm_snack) || 0;
    const snacksRate = isOutsideBsu.value ? baselineSettings.value.snacks_outside : baselineSettings.value.snacks_inside;
    const days = computedDays.value || 1;
    const totalCost = ((amPax * snacksRate) + (pmPax * snacksRate)) * days;
    snacksItem.total = totalCost > 0 ? totalCost.toFixed(2) : '';
  }
};

watch([isOutsideBsu, computedDays], () => {
  if (!form.value.venues) return;
  form.value.venues.forEach(vId => {
    recalculateMeals(vId);
    recalculateSnacks(vId);
  });
});

const handleScheduleTypeChange = (newType) => {
  if (scheduleType.value === newType) return;
  scheduleType.value = newType;
  continuousConfig.value = { start_date: '', end_date: '', start_time: '', end_time: '', meals_and_snacks: { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false } };
  form.value.schedules = [];
  if (newType === 'staggered') {
    addSchedule();
  }
};

const handleScheduleDateChange = (date, index) => {
  if (scheduleType.value !== 'staggered' || !date) return;
  const duplicate = form.value.schedules.some((schedule, scheduleIndex) => scheduleIndex !== index && schedule.date === date);
  if (duplicate) {
    form.value.schedules[index].date = '';
    Swal.fire({ icon: 'warning', title: 'Duplicate Schedule Date', text: 'Each non-consecutive schedule must use a different date.', confirmButtonColor: '#b979cc' });
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
  if (form.value.schedules.length > 1) {
    form.value.schedules.splice(index, 1);
  }
};

const validateScheduleTime = (index) => {
  const sch = form.value.schedules[index];
  if (sch.start_time && sch.end_time) {
    if (sch.end_time <= sch.start_time) {
      Swal.fire({ icon: 'warning', title: 'Invalid Time', text: 'End time must be after start time', confirmButtonColor: '#b979cc' });
      sch.end_time = '';
    }
  }
};

const handleTimeChange = (config) => {
  if (config.start_time && config.end_time) {
    if (config.end_time <= config.start_time) {
      Swal.fire({ icon: 'warning', title: 'Invalid Time', text: 'End time must be after start time', confirmButtonColor: '#b979cc' });
      config.end_time = '';
    }
  }
};


const customMandate = ref('');
const customGenderIssue = ref('');

const venues = ref([]);
const customVenue = ref('');
const formTypes = ref([]);
const ActClassification = ref([]);

const venueDropdownOpen = ref(false);

const getVenueName = (vid) => {
  const v = venues.value.find(v => String(v.venue_id) === String(vid));
  return v ? v.venue_name : 'Unknown Venue';
};

const removeVenue = (vid) => {
  if (form.value.venues) {
    form.value.venues = form.value.venues.filter(id => String(id) !== String(vid));
  }
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
      if (!value) {
        return 'You need to write something!';
      }
    }
  });

  if (venueName) {
    const tempId = 'custom_' + Date.now();
    const newVenue = {
      venue_id: tempId,
      venue_name: venueName,
      is_inside_bsu: form.value.is_inside_bsu ? 1 : 0
    };
    
    venues.value.push(newVenue);
    
    if (!form.value.venues) form.value.venues = [];
    form.value.venues.push(tempId);
    
    if (!form.value.venue_budgets) {
      form.value.venue_budgets = {};
    }
    form.value.venue_budgets[tempId] = [
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
    
    if (!form.value.custom_others) {
      form.value.custom_others = {};
    }
    form.value.custom_others[tempId] = [];
    
    Swal.fire({
      title: 'Added!',
      text: `${venueName} has been added.`,
      icon: 'success',
      confirmButtonColor: '#b979cc',
      timer: 1500,
      showConfirmButton: false
    });
  }
};


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
  return map;
});

const isAutoFillingFromAD = ref(false);

watch(() => form.value.is_inside_bsu, () => {
  if (isAutoFillingFromAD.value) return; // Don't clear when auto-filling from an approved AD
  if (form.value.venues && form.value.venues.length > 0) {
    form.value.venues = [];
  }
  
  if (form.value.venue && form.value.venue !== 'Other') {
    const isValid = filteredVenues.value.some(v => String(v.venue_id) === String(form.value.venue));
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
    if (form.value && form.value.activity_classification) {
       const classificationObj = ActClassification.value.find(c => c.classification_name === form.value.activity_classification);
       if (classificationObj) {
           url += '?classification=' + classificationObj.id;
       }
    }
    const res = await api.get(url);
    GADMandates.value = res.data;
  } catch (error) {
    console.error('Error fetching GAD mandates:', error);
  }
};


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

const scrollToPreselectedMandateAndIssue = () => {
  // Auto-scroll within Mandates list to the first preselected mandate
  if (form.value.gad_mandate_id && form.value.gad_mandate_id.length > 0) {
    const firstMandateId = form.value.gad_mandate_id[0];
    const mandateEl = document.getElementById('mandate-item-' + firstMandateId);
    if (mandateEl && mandatesListRef.value) {
      mandatesListRef.value.scrollTo({
        top: mandateEl.offsetTop - mandatesListRef.value.offsetTop - 8,
        behavior: 'smooth'
      });
      mandateEl.classList.add('gad-item-highlight');
      setTimeout(() => mandateEl.classList.remove('gad-item-highlight'), 1500);
    }
  }

  // Auto-scroll within Issues list to the first preselected issue
  if (form.value.gender_issue_id && form.value.gender_issue_id.length > 0) {
    const firstIssueId = form.value.gender_issue_id[0];
    const issueEl = document.getElementById('issue-item-' + firstIssueId);
    if (issueEl && issuesListRef.value) {
      issuesListRef.value.scrollTo({
        top: issueEl.offsetTop - issuesListRef.value.offsetTop - 8,
        behavior: 'smooth'
      });
      issueEl.classList.add('gad-item-highlight');
      setTimeout(() => issueEl.classList.remove('gad-item-highlight'), 1500);
    }
  }

  // Smoothly scroll the page to the GAD Mandates & Gender Issues section
  const sectionEl = document.getElementById('section-gad-mandates-issues');
  if (sectionEl) {
    sectionEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
};

const handleClassificationChange = async () => {
  form.value.gad_mandate_id = [];
  form.value.gender_issue_id = [];
  await fetchGADMandates();
};

const handleMandateChange = async () => {
  form.value.gender_issue_id = [];
  await fetchGenderIssues(form.value.gad_mandate_id);
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
    const allIssues = [];
    for (const mandateId of ids) {
       if (mandateId !== 'Other') {
           let url = `get-gender-issues?mandates=${mandateId}`;
             if (form.value && form.value.activity_classification) {
                 const classificationObj = ActClassification.value.find(c => c.classification_name === form.value.activity_classification);
                 if (classificationObj) {
                     url += '&classification=' + classificationObj.id;
                 }
             }
             const res = await api.get(url);
           allIssues.push(...res.data);
       }
    }
    const uniqueIssues = [];
    const map = new Map();
    for (const item of allIssues) {
        if(!map.has(item.id)){
            map.set(item.id, true);
            uniqueIssues.push(item);
        }
    }
    genderIssues.value = uniqueIssues;
  } catch (error) {
    console.error('Error fetching gender issues:', error);
  }
};

const approvedControls = ref([]);
const loadingControls = ref(false);

const fetchApprovedControls = async () => {
  loadingControls.value = true;
  try {
    // Fetch all values from the control_number table
    const res = await api.get(`approved-controls/${user.value.id}`);
    if (res.data.success) {
      approvedControls.value = res.data.data;
    }
  } catch (error) {
    console.error('Error fetching approved controls:', error);
  } finally {
    loadingControls.value = false;
  }
};

// Reactive Others State
const addOtherItem = (vId) => {
  if (!form.value.custom_others) form.value.custom_others = {};
  if (!form.value.custom_others[vId]) form.value.custom_others[vId] = [];
  form.value.custom_others[vId].push({ name: '', amount: '' });
};
const removeOtherItem = (vId, index) => {
  if (form.value.custom_others && form.value.custom_others[vId]) {
    form.value.custom_others[vId].splice(index, 1);
  }
};

watch(
  () => form.value.custom_others,
  (newOthers) => {
    if (!newOthers) return;
    Object.entries(newOthers).forEach(([vId, list]) => {
      const sum = list.reduce((acc, curr) => acc + (Number(curr.amount) || 0), 0);
      if (form.value.venue_budgets[vId] && form.value.venue_budgets[vId][9]) {
        form.value.venue_budgets[vId][9].total = sum || '';
      }
    });
  },
  { deep: true }
);

const selectedProposedBudget = ref(0);

const isExceedingLimit = computed(() => {
  return selectedProposedBudget.value > 0 && form.value.proposed_budget > selectedProposedBudget.value;
});

watch(() => form.value.control_number, async (newVal) => {
  const selected = approvedControls.value.find(c => c.control_number === newVal);
  if (selected) {
    form.value.act_design_id = selected.act_design_id;
    form.value.activity_title = selected.activity_title;
    form.value.start_date = selected.start_date;
    form.value.end_date = selected.end_date;
    form.value.start_time = selected.start_time;
    form.value.end_time = selected.end_time;
    
    form.value.schedule_type = selected.schedule_type || 'continuous';
    scheduleType.value = selected.schedule_type || 'continuous';
    
    if (selected.schedules && selected.schedules.length > 0) {
      form.value.schedules = selected.schedules.map(sch => {
        let meals = { breakfast: false, am_snack: false, lunch: false, pm_snack: false, dinner: false };
        try {
          if (sch.meals_and_snacks) {
            meals = typeof sch.meals_and_snacks === 'string' ? JSON.parse(sch.meals_and_snacks) : sch.meals_and_snacks;
          }
        } catch (e) {
          console.error('Error parsing meals', e);
        }
        return {
          date: sch.schedule_date,
          start_time: sch.start_time ? sch.start_time.substring(0, 5) : '',
          end_time: sch.end_time ? sch.end_time.substring(0, 5) : '',
          meals_and_snacks: meals
        };
      });

      if (scheduleType.value === 'continuous') {
        continuousConfig.value.start_date = selected.start_date || '';
        continuousConfig.value.end_date = selected.end_date || '';
        continuousConfig.value.start_time = form.value.schedules[0]?.start_time || '';
        continuousConfig.value.end_time = form.value.schedules[0]?.end_time || '';
        continuousConfig.value.meals_and_snacks = { ...(form.value.schedules[0]?.meals_and_snacks || {}) };
      }
    } else {
      form.value.schedules = [];
    }
    form.value.is_inside_bsu = selected.is_inside_bsu == 1 || selected.is_inside_bsu === true;
    isAutoFillingFromAD.value = true;
    form.value.venue = selected.venue_name || selected.venue; 
    if (selected.venues_list && selected.venues_list.length > 0) {
      // venues_list now returns [{venue_id, venue_name, is_inside_bsu}] objects
      // Inject them into local venues.value so getVenueName() can resolve them
      selected.venues_list.forEach(v => {
        const exists = venues.value.find(lv => String(lv.venue_id) === String(v.venue_id));
        if (!exists) {
          venues.value.push({ venue_id: String(v.venue_id), venue_name: v.venue_name, is_inside_bsu: v.is_inside_bsu });
        }
      });
      form.value.venues = selected.venues_list.map(v => String(v.venue_id));
    } else {
      form.value.venues = [];
    }
    nextTick(() => { isAutoFillingFromAD.value = false; });
    form.value.activity_classification = selected.activity_classification || 'N/A';
    form.value.form_type = selected.form_type_name || selected.form_type || 'N/A';
    form.value.target_participants = selected.target_participants || '0';
    await fetchGADMandates();
    const savedMandates = selected.gad_mandate_ids ? String(selected.gad_mandate_ids).split(',').map(s=>s.trim()) : [];
    form.value.gad_mandate_id = GADMandates.value.filter(m => {
       const mIds = String(m.id).split(',');
       return mIds.every(id => savedMandates.includes(id));
    }).map(m => String(m.id));
    if (savedMandates.includes('Other') && !form.value.gad_mandate_id.includes('Other')) {
        form.value.gad_mandate_id.push('Other');
    }

    await fetchGenderIssues(form.value.gad_mandate_id);
    
    const savedIssues = selected.gender_issue_ids ? String(selected.gender_issue_ids).split(',').map(s=>s.trim()) : [];
    form.value.gender_issue_id = genderIssues.value.filter(m => {
       const mIds = String(m.id).split(',');
       return mIds.every(id => savedIssues.includes(id));
    }).map(m => String(m.id));
    if (savedIssues.includes('Other') && !form.value.gender_issue_id.includes('Other')) {
        form.value.gender_issue_id.push('Other');
    }
    selectedProposedBudget.value = Number(selected.proposed_budget) || 0;

    if (selected) {
      // Clear out the venue budgets and custom others before population
      form.value.venue_budgets = {};
      form.value.custom_others = {};

      // Populate from selected.budget_items if available
      if (selected.budget_items && Array.isArray(selected.budget_items)) {
        selected.budget_items.forEach(dbItem => {
          const vid = dbItem.venue_id === null ? 'Other' : String(dbItem.venue_id);
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
            rate: Number(dbItem.unit_cost) || 0,
            mult: parsedMult,
            custom: dbItem.item_name === 'Others' || isCatering,
            baseKey,
            base,
            capKey: dbItem.item_name === 'Transportation' ? 'transportation_limit' : null
          });
        });
      }
    }

    // Auto-scroll to the preselected GAD Mandate and Gender Issue
    await nextTick();
    setTimeout(() => {
      scrollToPreselectedMandateAndIssue();
    }, 150);
  } else {
    selectedProposedBudget.value = 0;
    form.value.form_type = '';
    form.value.target_participants = '';
    form.value.activity_classification = '';
    form.value.gad_mandate_id = [];
    form.value.gender_issue_id = [];
    othersList.value = [];
  }
});

const formatBudgetName = (name) => {
  if (!name) return '';
  return name.replace(/(\(.*\))/g, '<span class="budget-item-subtext">$1</span>');
};


watch(pfPax, (newPax) => {
  const item = form.value.budget_items.find(i => i.name === 'Professional Fee/Honoraria');
  if (item) item.total = (Number(newPax) * 2258.25) || '';
});
watch(tokensPax, (newPax) => {
  const item = form.value.budget_items.find(i => i.name === 'Token/s');
  if (item) item.total = (Number(newPax) * 1000) || '';
});


  

watch(() => form.value.venue_budgets, (newBudgets) => {
  if (isAutoFillingFromAD.value) return;
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

watch([() => form.value.male, () => form.value.female], ([newMale, newFemale]) => {
  const m = parseInt(newMale) || 0;
  const f = parseInt(newFemale) || 0;
  form.value.attendees = m + f;
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
const fileInput = ref(null);

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
const activePreviewIndex = ref(0);

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

const handleDrop = (event) => {
  if (event.dataTransfer.files.length > 0) {
    processFiles(event.dataTransfer.files);
  }
};

const handleFileUpload = (event) => {
  if (event.target.files.length > 0) {
    processFiles(event.target.files);
  }
};

const processFiles = (fileList) => {
  const newFiles = Array.from(fileList);
  const validFiles = [];
  newFiles.forEach(file => {
      if (file.size > 10 * 1024 * 1024) {
        Swal.fire({
          icon: 'error',
          title: 'File Too Large',
          text: `File "${file.name}" exceeds the 10MB limit.`,
          confirmButtonColor: '#b979cc'
        });
        return;
      }
      if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid File Type',
          text: `File "${file.name}" is not a PDF. Only PDF files are allowed.`,
          confirmButtonColor: '#b979cc'
        });
        return;
      }
    file.previewUrl = URL.createObjectURL(file);
    validFiles.push(file);
  });
  uploadedFiles.value = [...uploadedFiles.value, ...validFiles];
  if (uploadedFiles.value.length > 0 && activePreviewIndex.value >= uploadedFiles.value.length) {
    activePreviewIndex.value = 0;
  }
};

const addEvaluationItem = () => {
  form.value.evaluation_items.push({ area: '', rating: '', isCustom: true });
};

const removeEvaluationItem = (index) => {
  form.value.evaluation_items.splice(index, 1);
};

const removeFile = (index) => {
  uploadedFiles.value.splice(index, 1);
  if (uploadedFiles.value.length === 0 && fileInput.value) {
    fileInput.value.value = '';
    activePreviewIndex.value = 0;
  } else if (activePreviewIndex.value >= index && activePreviewIndex.value > 0) {
    activePreviewIndex.value--;
  }
};

const submitReport = async () => {
  if (scheduleType.value === 'continuous') {
    if (!continuousConfig.value.start_date || !continuousConfig.value.end_date || !continuousConfig.value.start_time || !continuousConfig.value.end_time) {
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
      if (!isDisabledDate(curr)) {
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

  const hasBlankEval = form.value.evaluation_items && form.value.evaluation_items.some(i => !i.area || i.rating === '' || i.rating === null);
  if (hasBlankEval) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Evaluation Details',
      text: 'Please ensure all evaluation areas and ratings are filled in before submitting.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  if (!form.value.control_number) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Field',
      text: 'Please select an Activity Design Control Number before proceeding.',
      confirmButtonColor: '#b979cc'
    });
    return;
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

  // Validate participants
  if (Number(form.value.target_participants) < 1) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Participants',
      text: 'Target participants must be at least 1.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  if (Number(form.value.male) < 1 || Number(form.value.female) < 1) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Participants',
      text: 'Male and Female participants must each be at least 1.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  if (form.value.start_date && form.value.end_date) {
    const startDate = new Date(form.value.start_date + 'T00:00:00');
    const endDate = new Date(form.value.end_date + 'T00:00:00');
    if (endDate < startDate) {
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Duration',
        text: 'End date cannot be before start date.',
        confirmButtonColor: '#b979cc'
      });
      return;
    }
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

  for (const venueId of form.value.venues || []) {
    const items = form.value.venue_budgets?.[venueId] || [];
    let venueTotal = 0;
    for (const item of items) {
      const multipliers = Array.isArray(item.mult) ? item.mult.filter(multiplier => multiplier && typeof multiplier === 'object') : [];
      const amount = (Number(item.rate) || 0) * multipliers.reduce((total, multiplier) => total * (Number(multiplier.q) || 0), 1);
      if (amount < 0) {
        Swal.fire({ icon: 'warning', title: 'Invalid Budget', text: 'Each budget line must have a non-negative amount.', confirmButtonColor: '#b979cc' });
        return;
      }
      venueTotal += amount;
    }
    if (venueTotal <= 0) {
      Swal.fire({ icon: 'warning', title: 'Empty Venue Budget', text: `Please enter at least one positive budget amount for ${getVenueName(venueId)}.`, confirmButtonColor: '#b979cc' });
      return;
    }
  }

  if (scheduleType.value === 'staggered') {
    const selectedDates = form.value.schedules.map(schedule => schedule.date).filter(Boolean);
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

  if (uploadedFiles.value.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Document',
      text: 'Please upload the Accomplishment Report and any attachments.',
      confirmButtonColor: '#b979cc'
    });
    return;
  }

  const submitConfirm = await Swal.fire({
    title: 'Confirm Submission',
    text: 'Are you sure you want to submit this Accomplishment Report?',
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
    text: 'Please wait while we submit your report and dispatch email notifications.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  try {
    const formData = new FormData();
    
    uploadedFiles.value.forEach(file => {
      formData.append('attachments[]', file);
    });
    
    const normalizedBudgetItems = [];
    form.value.venues.forEach(vid => {
      (form.value.venue_budgets[vid] || []).forEach(item => {
        const lineTotalAmount = (Number(item.rate) || 0) * (item.mult ? item.mult.reduce((p, m) => p * (Number(m.q) || 0), 1) : 1);
        if (item.custom && (!String(item.name).trim() || lineTotalAmount <= 0)) return;
        const isOther = item.custom && item.group === 'materials';
        
        const paxOfItem = Number(((item.mult || []).find(m => /^(pax|person|persons|head|heads)$/i.test(String(m.u || '').trim())) || {}).q) || 0;
        const lineFormulaStr = [('₱' + (Number(item.rate) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })), ...(item.mult ? item.mult.map(m => `${Number(m.q) || 0} ${String(m.u || '').trim()}`.trim()) : [])].join(' x ');
        
        normalizedBudgetItems.push({
          venue_id: vid === 'Other' ? 'Other' : vid,
          category_id: null,
          item_name: isOther ? 'Others' : item.name,
          sub_item: isOther ? item.name : lineFormulaStr,
          pax: paxOfItem || null,
          amount: lineTotalAmount
        });
      });
    });

    formData.append('venues', JSON.stringify(form.value.venues));
    const customVenuesToSave = venues.value.filter(v => String(v.venue_id).startsWith('custom_'));
    if (customVenuesToSave.length > 0) {
       formData.append('custom_venues', JSON.stringify(customVenuesToSave));
    }
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
        evalObj[dbKey] = item.rating || 0;
      }
    });
        formData.append('evaluation_results', JSON.stringify(evalObj));
    formData.append('schedules', JSON.stringify(form.value.schedules || []));

    Object.keys(form.value).forEach(key => {
      if (key !== 'budget_items' && key !== 'evaluation_items' && key !== 'venue' && key !== 'is_inside_bsu' && key !== 'schedules') {
        formData.append(key, form.value[key]);
      }
    });

    if (form.value.venue === 'Other') {
      formData.append('venue', customVenue.value);
    } else {
      formData.append('venue', form.value.venue);
    }
    formData.append('is_inside_bsu', form.value.is_inside_bsu === 'mixed' ? 'mixed' : (form.value.is_inside_bsu ? 1 : 0));

    const selectedClassification = ActClassification.value.find(c => c.classification_name === form.value.activity_classification);
    if (selectedClassification) {
        formData.append('activity_classification_id', selectedClassification.id);
    }
    formData.append('attendees', form.value.attendees);
    formData.append('male', form.value.male);
    formData.append('female', form.value.female);
    formData.append('rating', form.value.rating);
    formData.append('user_id', user.value.id);

    if (form.value.gad_mandate_id) {
      formData.append('gad_mandate_id', Array.isArray(form.value.gad_mandate_id) ? form.value.gad_mandate_id.join(',') : form.value.gad_mandate_id);
    }
    if (form.value.gad_mandate_id && form.value.gad_mandate_id.includes('Other')) {
      formData.append('custom_gad_mandate', customMandate.value);
    }
    
    if (form.value.gender_issue_id) {
      formData.append('gender_issue_id', Array.isArray(form.value.gender_issue_id) ? form.value.gender_issue_id.join(',') : form.value.gender_issue_id);
    }
    if (form.value.gender_issue_id && form.value.gender_issue_id.includes('Other')) {
      formData.append('custom_gender_issue', customGenderIssue.value);
    }
    
    uploadedFiles.value.forEach(file => {
      formData.append('attachment[]', file);
    });
    
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
        router.push('/staff/ar-list');
      });

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



const minDate = computed(() => {
  if (form.value.control_number) {
    const selected = approvedControls.value.find(c => c.control_number === form.value.control_number);
    if (selected && selected.start_date) {
      return selected.start_date.substring(0, 10);
    }
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
  if (typeof loadingData !== 'undefined' && loadingData.value) return;
  if (newDate) {
    const d1 = newDate.substring(0, 10);
    const d2 = minDate.value ? minDate.value.substring(0, 10) : '';
    if (d1 < d2) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Start date cannot be earlier than the approved Activity Design start date.', confirmButtonColor: '#b979cc' });
      form.value.start_date = '';
      return;
    }
    if (!isCurrentYear(newDate)) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Activity must be within the current year.', confirmButtonColor: '#b979cc' });
      form.value.start_date = '';
      return;
    }
    if (form.value.end_date && form.value.end_date < newDate) {
        document.activeElement?.blur();
        Swal.fire({ icon: 'warning', title: 'Invalid Duration', text: 'End date cannot be before start date.', confirmButtonColor: '#b979cc' });
        form.value.start_date = '';
    }
  }
});

watch(() => form.value.end_date, (newDate) => {
  if (typeof loadingData !== 'undefined' && loadingData.value) return;
  if (newDate) {
    const d1 = newDate.substring(0, 10);
    const d2 = minDate.value ? minDate.value.substring(0, 10) : '';
    if (d1 < d2) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'End date cannot be earlier than the approved Activity Design start date.', confirmButtonColor: '#b979cc' });
      form.value.end_date = '';
      return;
    }
    if (!isCurrentYear(newDate)) {
      document.activeElement?.blur();
      Swal.fire({ icon: 'warning', title: 'Invalid Date', text: 'Activity must be within the current year.', confirmButtonColor: '#b979cc' });
      form.value.end_date = '';
      return;
    }
    if (form.value.start_date && newDate < form.value.start_date) {
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






const baselineSettings = ref({});

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

const checkTransportationLimit = (vId) => {
  const limit = Number(baselineSettings.value?.transportation_limit || 20000);
  
  let totalTransport = 0;
  if (form.value.venue_budgets) {
    Object.values(form.value.venue_budgets).forEach(budgetItems => {
      const transItem = budgetItems.find(i => i.name === 'Transportation');
      if (transItem) {
        totalTransport += Number(transItem.total) || 0;
      }
    });
  }

  if (totalTransport > limit) {
    const excess = totalTransport - limit;
    const transItem = form.value.venue_budgets[vId]?.find(i => i.name === 'Transportation');
    if (transItem) {
      transItem.total = Math.max(0, (Number(transItem.total) || 0) - excess);
    }

    const role = user.value?.role || 'staff';
    Swal.fire({
      icon: 'warning',
      title: 'Limit Exceeded',
      html: `Overall Transportation budget cannot exceed the baseline limit of ₱${limit.toLocaleString('en-US')}.<br><br>
             If you need to request an exemption, please <a href="/${role}/messages" style="color: #b979cc; text-decoration: underline; font-weight: bold;">message the GAD Director/Staff</a>.`,
      confirmButtonColor: '#b979cc'
    });
  }
};

onMounted(() => {
  if (!user.value.id) {
    router.push('/login');
  } else {
    fetchBaselineSettings();
    fetchApprovedControls();
    fetchHolidays();
    fetchGADMandates();
    fetchFormTypes();
    fetchActivityClassifications();
    fetchVenues();
  }
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

<style scoped src="../../assets/submit-ar-styles.css"></style>


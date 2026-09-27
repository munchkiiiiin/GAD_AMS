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
                <div class="form-column-left">
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

                  <div class="input-group">
                    <label class="form-label">Activity Title *</label>
                    <textarea 
                      v-model="form.activity_title" 
                      required 
                      rows="2" 
                      class="custom-input-field textarea-no-resize"
                      placeholder="Enter the complete title of the activity"
                    ></textarea>
                  </div>

                  <div class="input-group">
                    <label class="form-label">Gender Issue / GAD Mandate *</label>
                    <div class="checkbox-group-container custom-input-field" style="min-height: 120px; max-height: 250px; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 10px;">
                      <label v-for="mandate in GADMandates" :key="mandate.id" class="checkbox-label" style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; color: #ffffff;">
                        <input type="radio" v-model="form.gad_mandate_id" :value="mandate.id" style="margin-top: 2px; accent-color: #b979cc; transform: scale(1.1);" />
                        <span style="font-size: 14px; line-height: 1.4;">{{ mandate.code }} - {{ mandate.title }}</span>
                      </label>
                      
                    </div>
                    
                  </div>

                  <div class="input-group">
                    <label class="form-label">Cause of Gender Issue *</label>
                    <div class="checkbox-group-container custom-input-field" style="min-height: 120px; max-height: 250px; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 10px;">
                      <label v-for="issue in genderIssues" :key="issue.id" class="checkbox-label" style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; color: #ffffff;">
                        <input type="radio" v-model="form.gender_issue_id" :value="issue.id" style="margin-top: 2px; accent-color: #b979cc; transform: scale(1.1);" />
                        <span style="font-size: 14px; line-height: 1.4;">{{ issue.title }}</span>
                      </label>
                      
                      <p v-if="!form.gad_mandate_id || form.gad_mandate_id.length === 0" style="color: #94a3b8; font-size: 13px; font-style: italic; margin: 0;">Select a mandate first to see gender issues.</p>
                    </div>
                    <input v-if="form.gender_issue_id && form.gender_issue_id === 'Other'" 
                          v-model="customGenderIssue" 
                          type="text" 
                          placeholder="Enter new gender issue..." 
                          class="custom-input-field" 
                          style="margin-top: 10px;" />
                  </div>

                  <div class="input-group">
                    <label class="form-label">Venue Location *</label>
                    <div class="toggle-container" style="display: flex; gap: 1rem; align-items: center; height: 42px;">
                      <label style="color: #cbd5e1; font-size: 14px; cursor: pointer;">
                        <input type="radio" :value="true" v-model="form.is_inside_bsu" style="accent-color: #b979cc; transform: scale(1.1); margin-right: 5px;" /> Inside BSU
                      </label>
                      <label style="color: #cbd5e1; font-size: 14px; cursor: pointer;">
                        <input type="radio" :value="false" v-model="form.is_inside_bsu" style="accent-color: #b979cc; transform: scale(1.1); margin-right: 5px;" /> Outside BSU
                      </label>
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
                  </div>
                  <div class="form-sub-grid mb-4 mt-4">
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
                      You can customize the Time for specific days (e.g., half-day on the last day) below:
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

                  <!-- Upload Activity Design (Moved inside left column) -->
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
                            <iframe :src="getPdfViewerUrl(designFile.previewUrl)" width="100%" height="400px" style="border: 1px solid #b979cc; border-radius: 8px;"></iframe>
                          </div>
                        </div>
                        <p v-else class="no-file-uploaded-text">No file uploaded yet.</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-column-right">
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
                    />
                    
                    <!-- Overall Target Participants Banner -->
                    <div class="grand-total-banner-card" style="background: rgba(30,41,59,0.7); margin-bottom: 12px; border-color: #334155; padding: 12px 20px;">
                      <div class="grand-total-label-banner" style="color: #94a3b8; font-size: 13px;">Overall Expected Attendance (Auto-calculated)</div>
                      <div class="grand-total-value-banner" style="color: #cbd5e1; font-size: 16px;">
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
              </div>

              <div class="form-actions">
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
import api from '../../api';

const router = useRouter();
const route = useRoute();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

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
  const v = filteredVenues.value.find(x => String(x.venue_id) === String(id));
  return v ? v.venue_name : 'Unknown Venue';
};


const form = ref({
  form_type: '',
  nature: '',
  activity_classification_id: '',
  gad_mandate_id: '',
  gender_issue_id: '',
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
  return venues.value.filter(v => 
    (v.is_inside_bsu == 1 || v.is_inside_bsu === true) === form.value.is_inside_bsu &&
    v.venue_name && v.venue_name.trim() !== ''
  );
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
    form.value.gad_mandate_id = '';
    form.value.gender_issue_id = '';
    fetchGADMandates();
  });

  watch(() => form.value.gad_mandate_id, (newVal) => {
  form.value.gender_issue_id = '';
  fetchGenderIssues(newVal);
});

watch(() => form.value.venue_budgets, (newBudgets) => {
  let grandTotal = 0;
  let maxOverallPax = 0;
  Object.values(newBudgets).forEach(items => {
    grandTotal += items.reduce((sum, i) => sum + lineTotal(i), 0);
    maxOverallPax += Math.max(0, ...items.filter(i => i.group === 'catering').map(paxOf));
  });
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

const validMultipliers = item => Array.isArray(item?.mult)
  ? item.mult.filter(multiplier => multiplier && typeof multiplier === 'object')
  : [];
const lineTotal = item => (Number(item?.rate) || 0) * validMultipliers(item)
  .reduce((total, multiplier) => total * (Number(multiplier.q) || 0), 1);
const paxOf = item => Number(
  (validMultipliers(item).find(multiplier =>
    /^(pax|person|persons|head|heads)$/i.test(String(multiplier.u || '').trim())
  ) || {}).q
) || 0;
const getHeadcountMultiplier = (item, multipliers) => {
  const isPF = /professional fee|honoraria/i.test(item?.name || '');
  const isToken = /token/i.test(item?.name || '');

  if (isPF) {
    return multipliers.find(m => /^(speaker|speakers|pax|person|persons|head|heads|expert|experts|facilitator|facilitators|resource\s*person|trainer|trainers|moderator|panelist|session|sessions|hr|hrs|hour|hours|day|days)$/i.test(String(m.u || '').trim())) || multipliers[0];
  }
  if (isToken) {
    return multipliers.find(m => /^(recipient|recipients|speaker|speakers|pax|person|persons|head|heads|pc|pcs|piece|pieces|item|items|set|sets)$/i.test(String(m.u || '').trim())) || multipliers[0];
  }
  return multipliers.find(m => /^(pax|person|persons|head|heads|people|participant|participants|attendee|attendees)$/i.test(String(m.u || '').trim()))
    || multipliers.find(m => !/^(day|days|night|nights|hr|hrs|hour|hours|trip|trips)$/i.test(String(m.u || '').trim()))
    || multipliers[0];
};

const budgetValidationError = () => {
  const paxBased = /(breakfast|lunch|dinner|snack|professional fee|honoraria|token)/i;
  for (const venueId of form.value.venues || []) {
    const items = form.value.venue_budgets?.[venueId] || [];
    let venueTotal = 0;
    for (const item of items) {
      const multipliers = validMultipliers(item);
      const amount = (Number(item.rate) || 0) * multipliers.reduce((total, multiplier) => total * (Number(multiplier.q) || 0), 1);
      const headMultiplier = getHeadcountMultiplier(item, multipliers);
      const count = headMultiplier ? Number(headMultiplier.q) : 0;
      if (amount < 0 || count < 0 || (amount === 0 && count > 0)) {
        return 'Each budget line must have a non-negative amount, and a zero-cost line cannot have a positive quantity count.';
      }
      if (amount > 0 && paxBased.test(String(item.name || '')) && count <= 0) {
        if (/professional fee|honoraria/i.test(item.name || '')) {
          return `${item.name} requires a speaker or quantity count greater than zero when it has an amount.`;
        }
        if (/token/i.test(item.name || '')) {
          return `${item.name} requires a recipient or item count greater than zero when it has an amount.`;
        }
        return `${item.name} requires a pax/participant count greater than zero when it has an amount.`;
      }
      venueTotal += amount;
    }
    if (venueTotal <= 0) return `Please enter at least one positive budget amount for ${getVenueName(venueId)}.`;
  }
  return '';
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


  // Validate venue budgets — at least one item must have a value per venue
  const emptyVenues = form.value.venues.filter(vid => {
    const items = form.value.venue_budgets[vid];
    if (!items) return true;
    return items.reduce((sum, item) => sum + lineTotal(item), 0) === 0;
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
    
    if (mandateVal === 'Other') {
      formData.append('custom_gad_mandate', customMandate.value);
    }
    if (issueVal === 'Other') {
      formData.append('custom_gender_issue', customGenderIssue.value);
    }

    formData.append('activity_title', form.value.activity_title);
    formData.append('start_date', form.value.start_date);
    formData.append('end_date', form.value.end_date);
    formData.append('start_time', form.value.start_time);
    formData.append('end_time', form.value.end_time);
    formData.append('user_id', user.value.id || user.value.user_id);
    formData.append('is_inside_bsu', form.value.is_inside_bsu ? '1' : '0');
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
        const multipliers = validMultipliers(item);
        const lineTotalAmount = (Number(item.rate) || 0) * multipliers.reduce((p, m) => p * (Number(m.q) || 0), 1);
        if (item.capKey && lineTotalAmount > Number(baselineSettings.value[item.capKey])) exceedsTransportLimit = true;
        if (item.custom && (!String(item.name).trim() || lineTotalAmount <= 0)) return;
        const isOther = item.custom && item.group === 'materials';
        
        const headMultiplier = getHeadcountMultiplier(item, multipliers);
        const paxOfItem = Number(headMultiplier?.q) || 0;
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

    if (exceedsTransportLimit) {
      Swal.fire({
        icon: 'warning',
        title: 'Limit Exceeded',
        text: `Transportation budget cannot exceed the maximum limit of ₱${Number(baselineSettings.value.transportation_limit).toLocaleString('en-US')}.`,
        confirmButtonColor: '#b979cc'
      });
      return;
    }

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
        router.push('/staff/ad-list');
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
  router.push('/staff/submit');
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
  if (!user.value.id) {
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
});

onUnmounted(() => {
  document.removeEventListener('click', closeAllHelp);
});

</script>

<style scoped>
.twg-view-wrapper {
  flex: 1;
  overflow-y: auto;
  display: flex;
  background: transparent;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.text-sm { font-size: 14px; }
.text-3xl { font-size: 26px; }

.form-main-layout {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.main-content-container {
  max-width: 1280px;
  margin-left: auto;
  margin-right: auto;
  width: 100%;
}

.form-header {
  margin-bottom: 32px;
}

.form-main-title {
  font-size: 26px;
  font-weight: 800;
  letter-spacing: -0.025em;
  color: #16213e;
  letter-spacing: -0.02em;
}

.form-description {
  font-size: 14px;
  color: #64748b;
  margin-top: 6px;
}

.form-grid-main {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 30px;
}
@media (min-width: 1024px) {
  .form-grid-main {
    grid-template-columns: 1fr 1fr;
  }
}

.form-column-left, .form-column-right {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-column-left {
  border-right: 1px solid rgba(185, 121, 204, 0.2);
  padding-right: 20px;
}
  
.form-section-spacing {
  display: flex;
  flex-direction: column;
  gap: 20px;
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

.form-label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #b979cc;
}

.form-container-box {
  background: linear-gradient(145deg, #1a1a2e 0%, #16213e 100%);
  border: 1px solid rgba(185, 121, 204, 0.2);
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 20px 40px rgba(10, 10, 20, 0.4);
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

.dark-option {
  background-color: #16213e;
  color: #ffffff;
}

.code-icon-calendar::-webkit-calendar-picker-indicator,
.code-icon-clock::-webkit-calendar-picker-indicator {
  filter: invert(1);
  cursor: pointer;
  opacity: 0.7;
}

.code-icon-calendar::-webkit-calendar-picker-indicator:hover,
.code-icon-clock::-webkit-calendar-picker-indicator:hover {
  opacity: 1;
}

.budget-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.budget-table-wrapper {
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
  font-weight: 600;
}

.budget-col-total {
  width: 128px;
}

.budget-table-body {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.budget-item-name {
  padding: 12px 16px;
  color: #cbd5e1;
  line-height: 1.25;
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
  color: #ffffff;
  text-align: right;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.grand-total-value {
  padding: 12px 16px;
  font-size: 14px;
  font-weight: 700;
  color: #b979cc;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.upload-dropzone {
  border: 2px dashed rgba(185, 121, 204, 0.3);
  background: rgba(185, 121, 204, 0.02);
  border-radius: 14px;
  padding: 30px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.upload-dropzone:hover {
  border-color: #b979cc;
  background: rgba(185, 121, 204, 0.06);
}

.upload-icon {
  font-size: 26px;
  margin-bottom: 8px;
  transition: transform 0.2s ease;
}
.upload-dropzone:hover .upload-icon {
  transform: scale(1.1);
}

.upload-text {
  font-size: 14px;
  font-weight: 600;
  color: #ffffff;
  text-align: center;
  transition: color 0.2s ease;
}
.upload-dropzone:hover .upload-text {
  color: #b979cc;
}

.upload-hint {
  font-size: 12px;
  color: #64748b;
  margin-top: 4px;
}

.uploaded-file-display {
  margin-top: 16px;
  width: 100%;
}

.attachment-section-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 10px;
}

.attachment-display-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.attachment-preview-column {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 60px;
  border: 1px dashed rgba(185, 121, 204, 0.15);
  border-radius: 12px;
  padding: 12px;
  background: rgba(185, 121, 204, 0.02);
}

.no-file-uploaded-text {
  color: #94a3b8;
  font-size: 14px;
  text-align: center;
}

.uploaded-file-tag {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 8px 14px;
  border-radius: 8px;
  color: #cbd5e1;
  font-size: 12px;
  width: 100%;
}

.uploaded-file-name {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.remove-file-btn {
  color: #f472b6;
  font-weight: 700;
  font-size: 14px;
  margin-left: 8px;
  flex-shrink: 0;
}
.remove-file-btn:hover {
  color: #f43f5e;
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 24px;
}

.back-button {
  padding: 12px 24px;
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #b979cc;
  border-radius: 12px;
  transition: all 0.2s ease;
}
.back-button:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

.submit-action-btn {
  background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%);
  color: #ffffff;
  padding: 14px 40px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 16px;
  cursor: pointer;
  border: none;
  box-shadow: 0 4px 14px rgba(153, 13, 209, 0.3);
  transition: all 0.3s ease;
}

.submit-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(153, 13, 209, 0.45);
  background: linear-gradient(135deg, #b979cc 0%, #990dd1 100%);
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
  .grand-total-banner-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
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
}

.multiselect-option {
  display: flex;
  align-items: center;
  padding: 0.6rem 1rem;
  cursor: pointer;
  color: #e2e8f0;
  transition: background-color 0.2s;
  margin: 0;
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

.pax-breakdown-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 10px;
  background: rgba(15, 23, 42, 0.4);
  padding: 12px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}
.pax-breakdown-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
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
.pax-label {
  font-size: 14px;
  color: #e2e8f0;
  font-weight: 500;
}
.pax-calc-text {
  font-size: 12px;
  color: #94a3b8;
  margin-left: 77px;
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(0, 0, 0, 0.2);
  padding: 4px 8px;
  border-radius: 4px;
  width: fit-content;
}
.pax-calc-formula {
  color: #cbd5e1;
}
.pax-calc-equals {
  color: #64748b;
}
.pax-calc-total {
  color: #10b981;
  font-weight: 600;
  font-size: 13px;
}
.budget-group-total {
  font-weight: 600;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 13px;
}

/* Universal budget lines */
.bl-ctl { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 8px; }
.bl-rate { width: 110px; background: rgba(15, 23, 42, 0.3); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 7px 10px; color: #fff; font-weight: 600; text-align: right; }
.bl-x { color: #64748b; }
.bl-mult { display: inline-flex; align-items: center; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(185, 121, 204, 0.3); border-radius: 8px; padding: 2px 2px 2px 4px; }
.bl-mult input { background: transparent; border: 1px solid transparent; border-radius: 6px; padding: 5px 6px; color: #fff; }
.bl-q { width: 58px; text-align: center; font-weight: 600; }
.bl-u { width: 84px; color: #e9d5ff; }
.bl-rm { background: none; border: 0; color: #64748b; cursor: pointer; font-size: 11px; padding: 4px 6px; }
.bl-rm:hover { color: #f43f5e; }
.bl-acts { display: flex; gap: 10px; align-items: center; margin-top: 6px; }
.bl-clear { background: none; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 6px; color: #94a3b8; font-size: 11px; padding: 2px 8px; cursor: pointer; }
.bl-clear:hover { color: #fff; border-color: #b979cc; }
.bl-note { font-size: 11px; color: #64748b; margin-top: 6px; }
.bl-link { background: none; border: 0; color: #b979cc; cursor: pointer; font-size: 11px; padding: 0; text-decoration: underline; }
@media (max-width: 640px) { .budget-row-item { flex-direction: column; align-items: stretch !important; } }
</style>

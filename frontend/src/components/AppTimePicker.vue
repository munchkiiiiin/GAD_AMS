<template>
  <div class="app-time-picker-wrapper" ref="wrapperRef">
    <!-- Trigger Field -->
    <div 
      class="time-picker-trigger custom-input-field"
      :class="{ 'is-disabled': disabled, 'is-open': isOpen }"
      @click="togglePicker"
      tabindex="0"
      @keydown.enter.prevent="togglePicker"
      @keydown.space.prevent="togglePicker"
    >
      <span class="material-symbols-outlined trigger-icon">schedule</span>
      <span class="trigger-text" :class="{ 'placeholder-text': !formattedDisplay }">
        {{ formattedDisplay || placeholder || 'Select Time' }}
      </span>
      <span class="material-symbols-outlined chevron-icon" :class="{ 'rotated': isOpen }">
        expand_more
      </span>
    </div>

    <!-- Hidden input for form validation support -->
    <input 
      type="hidden" 
      :value="modelValue" 
      :required="required"
    />

    <!-- Teleported Modal / Popover -->
    <Teleport to="body">
      <Transition name="fade-scale">
        <div v-if="isOpen" class="time-picker-backdrop" :class="{ 'is-dark': isDarkMode }" @click="close">
          <div class="time-picker-card" @click.stop>
            <!-- Card Header -->
            <div class="picker-header">
              <div class="header-title-row">
                <span class="material-symbols-outlined header-icon">access_time</span>
                <span class="header-title">Select Time</span>
                <button type="button" class="header-close-btn" @click="close" title="Close">
                  <span class="material-symbols-outlined">close</span>
                </button>
              </div>

              <!-- Big Digital Readout -->
              <div class="digital-display-card">
                <div class="digital-time-boxes">
                  <button 
                    type="button" 
                    class="time-unit-box" 
                    :class="{ active: activeTab === 'hour' }"
                    @click="activeTab = 'hour'"
                  >
                    {{ padZero(selectedHour) }}
                  </button>
                  <span class="time-colon">:</span>
                  <button 
                    type="button" 
                    class="time-unit-box" 
                    :class="{ active: activeTab === 'minute' }"
                    @click="activeTab = 'minute'"
                  >
                    {{ padZero(selectedMinute) }}
                  </button>
                </div>

                <!-- AM / PM Segmented Switch -->
                <div class="ampm-switch">
                  <button 
                    type="button" 
                    class="ampm-btn" 
                    :class="{ active: selectedPeriod === 'AM' }"
                    @click="setPeriod('AM')"
                  >
                    AM
                  </button>
                  <button 
                    type="button" 
                    class="ampm-btn" 
                    :class="{ active: selectedPeriod === 'PM' }"
                    @click="setPeriod('PM')"
                  >
                    PM
                  </button>
                </div>
              </div>
            </div>

            <!-- Body: Tab Navigation / Selector Grid -->
            <div class="picker-body">
              <div class="picker-tabs">
                <button 
                  type="button" 
                  class="tab-btn" 
                  :class="{ active: activeTab === 'hour' }"
                  @click="activeTab = 'hour'"
                >
                  Hour
                </button>
                <button 
                  type="button" 
                  class="tab-btn" 
                  :class="{ active: activeTab === 'minute' }"
                  @click="activeTab = 'minute'"
                >
                  Minute
                </button>
              </div>

              <!-- Hour Selection (1 - 12) -->
              <div v-if="activeTab === 'hour'" class="grid-container hour-grid">
                <button 
                  v-for="h in 12" 
                  :key="h" 
                  type="button" 
                  class="grid-item-btn" 
                  :class="{ 
                    selected: selectedHour === h,
                    disabled: isHourDisabled(h) 
                  }"
                  :disabled="isHourDisabled(h)"
                  @click="selectHour(h)"
                >
                  {{ padZero(h) }}
                </button>
              </div>

              <!-- Minute Selection -->
              <div v-else class="minute-view-wrapper">
                <div class="grid-container minute-grid">
                  <button 
                    v-for="m in minutePresets" 
                    :key="m" 
                    type="button" 
                    class="grid-item-btn" 
                    :class="{ 
                      selected: selectedMinute === m,
                      disabled: isMinuteDisabled(m)
                    }"
                    :disabled="isMinuteDisabled(m)"
                    @click="selectMinute(m)"
                  >
                    {{ padZero(m) }}
                  </button>
                </div>

                <!-- Precise Minute Stepper -->
                <div class="minute-stepper-row">
                  <span class="stepper-label">Fine Adjust:</span>
                  <div class="stepper-controls">
                    <button 
                      type="button" 
                      class="stepper-btn" 
                      @click="adjustMinute(-1)"
                      title="Minus 1 minute"
                    >
                      <span class="material-symbols-outlined">remove</span>
                    </button>
                    <span class="stepper-val">{{ padZero(selectedMinute) }} m</span>
                    <button 
                      type="button" 
                      class="stepper-btn" 
                      @click="adjustMinute(1)"
                      title="Plus 1 minute"
                    >
                      <span class="material-symbols-outlined">add</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Range Constraint Notice -->
              <div v-if="min || max" class="constraint-hint">
                Valid schedule hours: 04:00 AM – 08:00 PM
              </div>
            </div>

            <!-- Footer Buttons -->
            <div class="picker-footer">
              <button type="button" class="btn-clear" @click="handleClear">
                Clear
              </button>
              <div class="footer-right">
                <button type="button" class="btn-cancel" @click="close">
                  Cancel
                </button>
                <button type="button" class="btn-confirm" @click="handleConfirm">
                  Set Time
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  min: {
    type: String,
    default: '04:00'
  },
  max: {
    type: String,
    default: '20:00'
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  placeholder: {
    type: String,
    default: 'Select Time'
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const wrapperRef = ref(null);
const isOpen = ref(false);
const activeTab = ref('hour');
const isDarkMode = ref(false);

const updateDarkMode = () => {
  if (typeof document !== 'undefined') {
    isDarkMode.value = document.documentElement.classList.contains('dark');
  }
};

// Internal 12-hour values
const selectedHour = ref(8);
const selectedMinute = ref(0);
const selectedPeriod = ref('AM');

const minutePresets = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55];

const padZero = (n) => String(n).padStart(2, '0');

// Parse modelValue ("HH:mm") into 12-hour format
const syncFromModel = (val) => {
  if (!val || typeof val !== 'string' || !val.includes(':')) {
    selectedHour.value = 8;
    selectedMinute.value = 0;
    selectedPeriod.value = 'AM';
    return;
  }
  const [hStr, mStr] = val.split(':');
  let h = parseInt(hStr, 10);
  const m = parseInt(mStr, 10) || 0;
  
  if (isNaN(h)) h = 8;
  
  if (h >= 12) {
    selectedPeriod.value = 'PM';
    selectedHour.value = h === 12 ? 12 : h - 12;
  } else {
    selectedPeriod.value = 'AM';
    selectedHour.value = h === 0 ? 12 : h;
  }
  selectedMinute.value = m;
};

watch(() => props.modelValue, (newVal) => {
  syncFromModel(newVal);
}, { immediate: true });

// Convert current 12-hour internal state back to 24-hour "HH:mm"
const to24HourString = (hour, minute, period) => {
  let h = hour;
  if (period === 'PM' && h < 12) {
    h += 12;
  } else if (period === 'AM' && h === 12) {
    h = 0;
  }
  return `${padZero(h)}:${padZero(minute)}`;
};

// Formatted display in trigger box (e.g., "08:00 AM")
const formattedDisplay = computed(() => {
  if (!props.modelValue || !props.modelValue.includes(':')) return '';
  const [hStr, mStr] = props.modelValue.split(':');
  const h = parseInt(hStr, 10);
  const m = parseInt(mStr, 10) || 0;
  if (isNaN(h)) return '';

  const period = h >= 12 ? 'PM' : 'AM';
  let hour12 = h % 12;
  if (hour12 === 0) hour12 = 12;

  return `${padZero(hour12)}:${padZero(m)} ${period}`;
});

// Precompute min and max minutes for ultra-fast integer comparisons
const minMinutes = computed(() => {
  if (!props.min || !props.min.includes(':')) return null;
  const [h, m] = props.min.split(':').map(Number);
  return h * 60 + (m || 0);
});

const maxMinutes = computed(() => {
  if (!props.max || !props.max.includes(':')) return null;
  const [h, m] = props.max.split(':').map(Number);
  return h * 60 + (m || 0);
});

// Fast integer Min / Max bound validation checks
const isHourDisabled = (h) => {
  if (minMinutes.value === null && maxMinutes.value === null) return false;
  let h24 = h;
  if (selectedPeriod.value === 'PM' && h24 < 12) h24 += 12;
  else if (selectedPeriod.value === 'AM' && h24 === 12) h24 = 0;

  const hourStartMins = h24 * 60;
  const hourEndMins = h24 * 60 + 59;

  if (minMinutes.value !== null && hourEndMins < minMinutes.value) return true;
  if (maxMinutes.value !== null && hourStartMins > maxMinutes.value) return true;
  return false;
};

const isMinuteDisabled = (m) => {
  if (minMinutes.value === null && maxMinutes.value === null) return false;
  let h24 = selectedHour.value;
  if (selectedPeriod.value === 'PM' && h24 < 12) h24 += 12;
  else if (selectedPeriod.value === 'AM' && h24 === 12) h24 = 0;

  const currentMins = h24 * 60 + m;
  if (minMinutes.value !== null && currentMins < minMinutes.value) return true;
  if (maxMinutes.value !== null && currentMins > maxMinutes.value) return true;
  return false;
};

const togglePicker = () => {
  if (props.disabled) return;
  if (!isOpen.value) {
    updateDarkMode();
    syncFromModel(props.modelValue);
    activeTab.value = 'hour';
    isOpen.value = true;
  } else {
    isOpen.value = false;
  }
};

const close = () => {
  isOpen.value = false;
};

const selectHour = (h) => {
  selectedHour.value = h;
  // Automatically switch tab to minute for fast flow
  activeTab.value = 'minute';
};

const selectMinute = (m) => {
  selectedMinute.value = m;
};

const adjustMinute = (delta) => {
  let newM = selectedMinute.value + delta;
  if (newM < 0) newM = 59;
  if (newM > 59) newM = 0;
  selectedMinute.value = newM;
};

const setPeriod = (period) => {
  selectedPeriod.value = period;
};

const handleConfirm = () => {
  const result24 = to24HourString(selectedHour.value, selectedMinute.value, selectedPeriod.value);
  emit('update:modelValue', result24);
  emit('change', result24);
  close();
};

const handleClear = () => {
  emit('update:modelValue', '');
  emit('change', '');
  close();
};

// Handle ESC key
const handleKeyDown = (e) => {
  if (e.key === 'Escape' && isOpen.value) {
    close();
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<style scoped>
.app-time-picker-wrapper {
  position: relative;
  width: 100%;
}

.time-picker-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.625rem 0.875rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease;
  background-color: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(185, 121, 204, 0.3);
  color: inherit;
}

.time-picker-trigger:hover:not(.is-disabled) {
  border-color: #b979cc;
  box-shadow: 0 0 0 2px rgba(185, 121, 204, 0.15);
}

.time-picker-trigger.is-open {
  border-color: #b979cc;
  box-shadow: 0 0 0 3px rgba(185, 121, 204, 0.25);
}

.time-picker-trigger.is-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.trigger-icon {
  font-size: 1.15rem;
  color: #b979cc;
  margin-right: 0.5rem;
  flex-shrink: 0;
}

.trigger-text {
  flex: 1;
  text-align: left;
  letter-spacing: 0.02em;
}

.placeholder-text {
  color: #94a3b8;
  font-weight: normal;
}

.chevron-icon {
  font-size: 1.25rem;
  color: #94a3b8;
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.chevron-icon.rotated {
  transform: rotate(180deg);
}

/* Modal Backdrop */
.time-picker-backdrop {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(15, 23, 42, 0.72);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

/* Card */
.time-picker-card {
  width: 100%;
  max-width: 320px;
  background: #ffffff;
  border-radius: 1.25rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(185, 121, 204, 0.3);
  overflow: hidden;
  animation: popIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  display: flex;
  flex-direction: column;
}

.is-dark .time-picker-card {
  background: #1e2026;
  border-color: rgba(255, 255, 255, 0.1);
  color: #f1f5f9;
}

@keyframes popIn {
  from {
    opacity: 0;
    transform: scale(0.92) translateY(8px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Header */
.picker-header {
  padding: 1rem 1.25rem;
  background: linear-gradient(135deg, rgba(185, 121, 204, 0.12) 0%, rgba(139, 92, 246, 0.08) 100%);
  border-bottom: 1px solid rgba(185, 121, 204, 0.2);
}

.header-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.875rem;
}

.header-icon {
  font-size: 1.2rem;
  color: #b979cc;
  margin-right: 0.375rem;
}

.header-title {
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #7e22ce;
  flex: 1;
}

.is-dark .header-title {
  color: #f8fafc;
}

.header-close-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
  border-radius: 0.375rem;
  transition: all 0.15s ease;
}

.header-close-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #0f172a;
}

.is-dark .header-close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
}

/* Digital Display Card */
.digital-display-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.7);
  padding: 0.5rem 0.75rem;
  border-radius: 0.875rem;
  border: 1px solid rgba(185, 121, 204, 0.25);
}

.is-dark .digital-display-card {
  background: #16181d;
  border-color: #334155;
}

.digital-time-boxes {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.time-unit-box {
  background: #f1f5f9;
  border: 2px solid transparent;
  border-radius: 0.625rem;
  font-size: 1.5rem;
  font-weight: 800;
  padding: 0.25rem 0.6rem;
  color: #334155;
  cursor: pointer;
  transition: all 0.15s ease;
  font-family: inherit;
}

.is-dark .time-unit-box {
  background: #2d313a;
  color: #f8fafc;
}

.time-unit-box.active {
  background: rgba(185, 121, 204, 0.2);
  border-color: #b979cc;
  color: #7e22ce;
}

.is-dark .time-unit-box.active {
  color: #ffffff;
}

.time-colon {
  font-size: 1.5rem;
  font-weight: 800;
  color: #b979cc;
}

/* AM/PM Switch */
.ampm-switch {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  background: #e2e8f0;
  padding: 0.2rem;
  border-radius: 0.625rem;
}

.is-dark .ampm-switch {
  background: #2d313a;
}

.ampm-btn {
  background: transparent;
  border: none;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.25rem 0.6rem;
  border-radius: 0.375rem;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s ease;
}

.is-dark .ampm-btn {
  color: #94a3b8;
}

.ampm-btn.active {
  background: #b979cc;
  color: #ffffff;
  box-shadow: 0 2px 4px rgba(185, 121, 204, 0.4);
}

/* Body */
.picker-body {
  padding: 1rem 1.25rem;
}

.picker-tabs {
  display: flex;
  background: #f1f5f9;
  border-radius: 0.5rem;
  padding: 0.2rem;
  margin-bottom: 0.875rem;
}

.is-dark .picker-tabs {
  background: #16181d;
}

.tab-btn {
  flex: 1;
  background: transparent;
  border: none;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.35rem;
  border-radius: 0.375rem;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s ease;
}

.is-dark .tab-btn {
  color: #94a3b8;
}

.tab-btn.active {
  background: #ffffff;
  color: #7e22ce;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.is-dark .tab-btn.active {
  background: #2d313a;
  color: #ffffff;
}

/* Grids */
.grid-container {
  display: grid;
  gap: 0.375rem;
}

.hour-grid {
  grid-template-columns: repeat(4, 1fr);
}

.minute-grid {
  grid-template-columns: repeat(4, 1fr);
  margin-bottom: 0.875rem;
}

.grid-item-btn {
  height: 2.25rem;
  border-radius: 0.5rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  font-size: 0.875rem;
  font-weight: 700;
  color: #334155;
  cursor: pointer;
  transition: all 0.15s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.is-dark .grid-item-btn {
  background: #16181d;
  border-color: #334155;
  color: #e2e8f0;
}

.grid-item-btn:hover:not(:disabled) {
  border-color: #b979cc;
  background: rgba(185, 121, 204, 0.1);
  color: #7e22ce;
}

.is-dark .grid-item-btn:hover:not(:disabled) {
  color: #ffffff;
}

.grid-item-btn.selected {
  background: #b979cc !important;
  color: #ffffff !important;
  border-color: #b979cc !important;
  box-shadow: 0 2px 6px rgba(185, 121, 204, 0.4);
}

.grid-item-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
  border-color: transparent;
}

/* Minute Stepper */
.minute-stepper-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f8fafc;
  padding: 0.375rem 0.625rem;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
}

.is-dark .minute-stepper-row {
  background: #16181d;
  border-color: #334155;
}

.stepper-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
}

.is-dark .stepper-label {
  color: #94a3b8;
}

.stepper-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stepper-btn {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 0.375rem;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #334155;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
}

:global(html.dark) .stepper-btn,
:global(.dark) .stepper-btn {
  background: #2d264c;
  border-color: #423869;
  color: #e2e8f0;
}

.stepper-btn:hover {
  background: #b979cc;
  color: #ffffff;
  border-color: #b979cc;
}

.stepper-btn .material-symbols-outlined {
  font-size: 1rem;
}

.stepper-val {
  font-size: 0.8rem;
  font-weight: 700;
  min-width: 2rem;
  text-align: center;
}

.constraint-hint {
  font-size: 0.7rem;
  color: #b979cc;
  font-weight: 600;
  margin-top: 0.75rem;
  text-align: center;
}

/* Footer */
.picker-footer {
  padding: 0.875rem 1.25rem;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.is-dark .picker-footer {
  background: #16181d;
  border-color: #334155;
}

.footer-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-clear {
  background: transparent;
  border: none;
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  cursor: pointer;
  padding: 0.375rem 0.5rem;
  border-radius: 0.375rem;
  transition: all 0.15s ease;
}

.btn-clear:hover {
  color: #ef4444;
}

.btn-cancel {
  background: transparent;
  border: 1px solid #cbd5e1;
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  padding: 0.375rem 0.75rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.is-dark .btn-cancel {
  border-color: #475569;
  color: #94a3b8;
}

.btn-cancel:hover {
  background: rgba(0, 0, 0, 0.05);
}

.is-dark .btn-cancel:hover {
  background: rgba(255, 255, 255, 0.05);
}

.btn-confirm {
  background: linear-gradient(135deg, #b979cc 0%, #9333ea 100%);
  border: none;
  font-size: 0.75rem;
  font-weight: 700;
  color: #ffffff;
  padding: 0.4rem 0.875rem;
  border-radius: 0.5rem;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(185, 121, 204, 0.4);
  transition: all 0.15s ease;
}

.btn-confirm:hover {
  filter: brightness(1.1);
  transform: translateY(-1px);
}

/* Transition */
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
}
</style>

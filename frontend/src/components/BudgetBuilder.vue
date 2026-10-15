<template>
  <div class="budget-section">
    <label class="form-label" v-if="label">{{ label }}</label>
    <datalist id="bl-units">
      <option v-for="u in unitSuggestions" :key="u" :value="u"></option>
    </datalist>

    <!-- Multi-Venue Tabs (Only shown when > 1 venue selected) -->
    <div v-if="activeVenues.length > 1" class="venue-tabs-bar">
      <button 
        type="button" 
        class="venue-tab-btn"
        :class="{ active: selectedVenueTab === 'all' }"
        @click="selectedVenueTab = 'all'"
      >
        <span class="material-symbols-outlined text-[16px]">view_agenda</span>
        <span>All Venues</span>
      </button>
      <button 
        v-for="vId in activeVenues" 
        :key="vId" 
        type="button" 
        class="venue-tab-btn"
        :class="{ active: selectedVenueTab === vId }"
        @click="selectedVenueTab = vId"
      >
        <span class="material-symbols-outlined text-[16px]">location_on</span>
        <span class="truncate">{{ getVenueName(vId) }}</span>
        <span class="venue-tab-badge">
          {{ peso(venueTotal(vId)) }}
        </span>
      </button>
    </div>
    
    <div 
      v-for="vId in displayedVenues" 
      :key="vId" 
      class="venue-budget-wrapper"
    >
      <!-- Venue Card Header -->
      <div class="venue-card-header">
        <div class="venue-title-group">
          <div class="venue-avatar-icon">
            <span class="material-symbols-outlined">domain</span>
          </div>
          <div>
            <h4 class="venue-budget-title">Budget for Venue: {{ getVenueName(vId) }}</h4>
            <div v-if="venueInsideMap !== null" class="venue-rate-status-container">
              <span :class="venueIsOutside(vId) ? 'venue-rate-outside' : 'venue-rate-inside'">
                <span class="material-symbols-outlined text-[13px] mr-1 align-middle">
                  {{ venueIsOutside(vId) ? 'apartment' : 'school' }}
                </span>
                {{ venueIsOutside(vId) ? 'Outside BSU — using outside rates' : 'Inside BSU — using inside rates' }}
              </span>
            </div>
          </div>
        </div>

        <div class="venue-header-right">
          <div class="venue-subtotal-badge">
            <span class="subtotal-label">Venue Subtotal</span>
            <span class="subtotal-amount">{{ peso(venueTotal(vId)) }}</span>
          </div>
        </div>
      </div>

      <!-- Main Budget Groups Container (Card-based layout, NO Collapsible Accordion) -->
      <div class="budget-groups-container">

        <!-- 1. CATERING & HOSPITALITY (Meals & Snacks Card Grid) -->
        <div class="budget-section-card meals-section-card" :class="{ 'is-collapsed': !isSectionOpen(vId, 'catering') }">
          <!-- Meals Section Header -->
          <div 
            class="meals-header-bar accordion-header-bar"
            :class="{ 'is-open': isSectionOpen(vId, 'catering') }"
            @click="toggleSection(vId, 'catering')"
            role="button"
            tabindex="0"
            :title="isSectionOpen(vId, 'catering') ? 'Click to collapse Meals & Snacks section' : 'Click to expand Meals & Snacks section'"
            @keydown.enter.space.prevent="toggleSection(vId, 'catering')"
          >
            <div class="meals-header-left">
              <div class="meals-icon-box">
                <span class="material-symbols-outlined">restaurant</span>
              </div>
              <div>
                <h5 class="meals-section-title">Meals & Snacks Budget (Auto-Computed from Schedule)</h5>
                <span class="meals-legal-subtext">(refer to E.O No. 77 and BSU Office Memorandum 117, s. 2024)</span>
              </div>
            </div>

            <div class="meals-header-right">
              <span class="meal-stat-pill" title="Maximum participant count across scheduled meals">
                Max Pax: <strong class="ml-1 text-slate-100">{{ venueMaxPax(vId) }}</strong>
              </span>
              <span class="meal-subtotal-pill">
                Meals Subtotal: <strong class="ml-1 text-purple-200">{{ peso(groupTotal(vId, 'catering')) }}</strong>
              </span>
              <div class="accordion-chevron-box">
                <span class="material-symbols-outlined accordion-arrow" :class="{ 'is-open': isSectionOpen(vId, 'catering') }">
                  expand_more
                </span>
              </div>
            </div>
          </div>

          <!-- Collapsible Catering Body -->
          <div v-show="isSectionOpen(vId, 'catering')" class="meals-collapsible-body">
            <!-- Meal Selector Chips Row -->
          <div class="meal-selector-bar">
            <div class="meal-selector-label">
              <span class="material-symbols-outlined text-[15px]">restaurant_menu</span>
              <span>Included Meals:</span>
            </div>
            <div class="meal-chips-group">
              <button
                v-for="m in standardMealKeys"
                :key="m.name"
                type="button"
                class="meal-chip-btn"
                :class="{ 'is-selected': isMealSelected(vId, m.name) }"
                @click="toggleMealSelection(vId, m.name)"
                :title="isMealSelected(vId, m.name) ? 'Click to deselect ' + m.label : 'Click to select ' + m.label"
              >
                <span class="material-symbols-outlined text-[13px]">
                  {{ isMealSelected(vId, m.name) ? 'check' : 'add' }}
                </span>
                <span>{{ m.label }}</span>
              </button>
              
              <div class="meal-quick-toggles">
                <button type="button" class="meal-text-toggle" @click="selectAllMeals(vId)">Select All</button>
                <span class="meal-toggle-dot">·</span>
                <button type="button" class="meal-text-toggle" @click="clearAllMeals(vId)">Clear All</button>
                <span class="meal-toggle-dot">·</span>
                <button type="button" class="meal-add-custom-link" @click="addLine(vId, 'catering')">
                  <span class="material-symbols-outlined text-[13px]">add_circle</span>
                  <span>Custom Meal</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Quick Constant Pax Row -->
          <div class="constant-pax-card">
            <div class="constant-pax-left">
              <div class="constant-pax-icon">
                <span class="material-symbols-outlined">person_add</span>
              </div>
              <div>
                <div class="constant-pax-title">Constant Pax for this Venue</div>
                <div class="constant-pax-desc">Quickly apply a fixed participant count to all scheduled meals below</div>
              </div>
            </div>
            <div class="constant-pax-right">
              <input 
                type="number" 
                min="0" 
                v-model.number="constantPaxMap[vId]" 
                class="constant-pax-input" 
                placeholder="e.g. 50" 
                @keydown.enter.prevent="applyConstantPax(vId)"
              />
              <button 
                type="button" 
                class="btn-apply-constant-pax" 
                @click="applyConstantPax(vId)"
                title="Apply this pax count to all visible meals"
              >
                Apply to All
              </button>
            </div>
          </div>

          <!-- Meals Grid of Cards -->
          <div v-if="visibleCateringItems(vId).length > 0" class="meals-cards-grid">
            <div 
              v-for="item in visibleCateringItems(vId)" 
              :key="item.id" 
              class="meal-item-card"
              :class="{ 'has-value': lineTotal(item) > 0 }"
            >
              <!-- Card Header -->
              <div class="meal-card-top">
                <div class="meal-card-title-wrap">
                  <input 
                    v-if="item.custom && !isStandardMeal(item.name)" 
                    type="text" 
                    v-model="item.name" 
                    class="meal-custom-name-input" 
                    placeholder="Meal / Snack name" 
                    @input="emit('update:venueBudgets', props.venueBudgets)"
                  />
                  <div v-else class="meal-card-name">{{ item.name }}</div>
                </div>

                <div class="meal-card-top-right">
                  <button 
                    type="button" 
                    class="meal-card-close-btn" 
                    title="Remove this meal" 
                    @click="deselectMeal(vId, item)"
                  >
                    <span class="material-symbols-outlined text-[16px]">close</span>
                  </button>
                </div>
              </div>

              <!-- Card Body -->
              <div class="meal-card-body">
                <!-- Pax Row -->
                <div class="meal-input-row">
                  <span class="meal-row-label">PAX:</span>
                  <div class="meal-input-box">
                    <input 
                      type="number" 
                      min="0" 
                      v-model.number="getPaxMultiplier(item).q" 
                      placeholder="Pax" 
                      class="meal-val-input" 
                    />
                  </div>
                </div>

                <!-- Rate Row -->
                <div class="meal-input-row">
                  <span class="meal-row-label">RATE:</span>
                  <div class="meal-input-box rate-box">
                    <span class="meal-currency-symbol">₱</span>
                    <input 
                      type="number" 
                      min="0" 
                      step="0.01" 
                      v-model.number="item.rate" 
                      placeholder="Rate" 
                      class="meal-val-input" 
                    />
                  </div>
                </div>

                <!-- Baseline Note & Reset -->
                <div v-if="item.baseKey && Number(item.rate) !== Number(item.base)" class="meal-baseline-hint">
                  <span>Base {{ peso(item.base) }}</span>
                  <button type="button" class="meal-reset-link" @click="item.rate = item.base">
                    <span class="material-symbols-outlined text-[11px] mr-0.5">refresh</span>
                    reset
                  </button>
                </div>

                <!-- Multipliers List Below (Editable, just like old design) -->
                <div class="meal-multipliers-list">
                  <template v-for="(m, mi) in nonPaxMultipliers(item)" :key="mi">
                    <div class="meal-mult-pill-row">
                      <span class="bl-symbol">×</span>
                      <div class="pill-unit-group mult-container">
                        <input 
                          type="number" 
                          min="0" 
                          step="any" 
                          v-model.number="m.q" 
                          class="pill-input pill-qty-input" 
                          placeholder="Qty"
                          title="Multiplier Quantity"
                        />
                        <input 
                          type="text" 
                          list="bl-units" 
                          v-model="m.u" 
                          class="pill-unit-tag" 
                          placeholder="unit"
                          title="Multiplier Unit (days, etc.)"
                          @focus="openUnitPicker"
                          @click="openUnitPicker"
                        />
                        <button 
                          type="button" 
                          class="pill-rm-btn" 
                          title="Remove multiplier" 
                          @click="removeMultiplier(item, m)"
                        >
                          <span class="material-symbols-outlined text-[13px]">close</span>
                        </button>
                      </div>
                    </div>
                  </template>

                  <!-- Add Multiplier Button -->
                  <div class="meal-add-mult-btn-wrap">
                    <button 
                      type="button" 
                      class="btn-add-multiplier" 
                      @click="item.mult.push({ q: 1, u: '' })"
                      title="Add another multiplier"
                    >
                      <span class="material-symbols-outlined text-[13px]">add</span>
                      <span>multiplier</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Card Footer -->
              <div class="meal-card-footer">
                <div class="meal-card-total">
                  = {{ peso(lineTotal(item)) }}
                </div>
                <button 
                  v-if="lineTotal(item) > 0" 
                  type="button" 
                  class="meal-card-clear" 
                  @click="clearLine(item)"
                  title="Clear pax and rate"
                >
                  Clear
                </button>
              </div>
            </div>
          </div>

            <!-- Empty State when no meals selected -->
            <div v-else class="meals-empty-state">
              <span class="material-symbols-outlined text-[24px] text-purple-400 mb-1">no_food</span>
              <div class="text-[13px] font-medium text-slate-400">No meals selected for this venue.</div>
              <div class="text-[11px] text-slate-500 mt-0.5">Click any meal chip above (e.g. Breakfast, Lunch) to configure its pax and rate.</div>
            </div>
          </div>
        </div>

        <!-- 2, 3, 4. NON-CATERING GROUPS (Dedicated Card Sections) -->
        <div 
          v-for="g in nonCateringGroups" 
          :key="g.key" 
          class="budget-section-card non-catering-card"
          :class="{ 'is-collapsed': !isSectionOpen(vId, g.key) }"
        >
          <!-- Section Header -->
          <div 
            class="section-card-header accordion-header-bar"
            :class="{ 'is-open': isSectionOpen(vId, g.key) }"
            @click="toggleSection(vId, g.key)"
            role="button"
            tabindex="0"
            :title="isSectionOpen(vId, g.key) ? 'Click to collapse ' + g.title : 'Click to expand ' + g.title"
            @keydown.enter.space.prevent="toggleSection(vId, g.key)"
          >
            <div class="section-title-group">
              <div class="section-icon-box">
                <span class="material-symbols-outlined">{{ g.materialIcon }}</span>
              </div>
              <div class="section-titles">
                <h5 class="section-card-title">{{ g.title }}</h5>
                <span class="section-active-count">{{ activeCountText(vId, g.key) }}</span>
              </div>
            </div>

            <div class="section-header-right">
              <div class="section-subtotal-badge">
                <span class="subtotal-label">Subtotal</span>
                <span class="subtotal-amount">{{ peso(groupTotal(vId, g.key)) }}</span>
              </div>
              <div class="accordion-chevron-box">
                <span class="material-symbols-outlined accordion-arrow" :class="{ 'is-open': isSectionOpen(vId, g.key) }">
                  expand_more
                </span>
              </div>
            </div>
          </div>

          <!-- Section Line Items -->
          <div v-show="isSectionOpen(vId, g.key)" class="section-card-body">
            <div 
              v-for="item in groupItems(vId, g.key)" 
              :key="item.id" 
              class="budget-row-item"
              :class="{ 'has-value': lineTotal(item) > 0 }"
            >
              <div class="budget-item-main">
                <!-- Item Title Row -->
                <div class="item-title-row">
                  <input 
                    v-if="item.custom" 
                    type="text" 
                    v-model="item.name" 
                    class="others-input-name" 
                    placeholder="Item name (e.g. Specialty Equipment)" 
                  />
                  <div v-else class="budget-item-title">{{ item.name }}</div>
                  <span v-if="item.hint" class="budget-item-subtext">({{ item.hint }})</span>
                </div>

                <!-- Equation Pill Row -->
                <div class="bl-equation-pill">
                  <!-- Rate Container -->
                  <div class="pill-unit-group rate-container" title="Rate per unit">
                    <span class="pill-prefix">₱</span>
                    <input 
                      type="number" 
                      min="0" 
                      step="0.01" 
                      v-model.number="item.rate" 
                      class="pill-input pill-rate-input" 
                      :placeholder="item.mult.length ? 'Rate' : 'Amount'" 
                    />
                  </div>

                  <!-- Multipliers Loop -->
                  <template v-for="(m, mi) in item.mult" :key="mi">
                    <span class="bl-symbol">×</span>
                    <div class="pill-unit-group mult-container">
                      <input 
                        type="number" 
                        min="0" 
                        step="any" 
                        v-model.number="m.q" 
                        class="pill-input pill-qty-input" 
                        placeholder="Qty"
                        title="Multiplier Quantity"
                      />
                      <input 
                        type="text" 
                        list="bl-units" 
                        v-model="m.u" 
                        class="pill-unit-tag" 
                        placeholder="unit"
                        title="Multiplier Unit (pax, days, speakers, etc.)"
                        @focus="openUnitPicker"
                        @click="openUnitPicker"
                      />
                      <button 
                        type="button" 
                        class="pill-rm-btn" 
                        title="Remove multiplier" 
                        @click="item.mult.splice(mi, 1)"
                      >
                        <span class="material-symbols-outlined text-[13px]">close</span>
                      </button>
                    </div>
                  </template>

                  <!-- Add Multiplier Button -->
                  <button 
                    type="button" 
                    class="btn-add-multiplier" 
                    @click="item.mult.push({ q: 1, u: '' })"
                    title="Add another multiplier"
                  >
                    <span class="material-symbols-outlined text-[13px]">add</span>
                    <span>multiplier</span>
                  </button>
                </div>

                <!-- Notes & Limit Warnings -->
                <div v-if="item.baseKey" class="bl-note">
                  <span>Baseline rate {{ peso(item.base) }}</span>
                  <template v-if="Number(item.rate) !== Number(item.base)">
                    <span class="note-separator">·</span>
                    <button type="button" class="bl-reset-link" @click="item.rate = item.base">
                      <span class="material-symbols-outlined text-[11px] mr-0.5">refresh</span>
                      reset
                    </button>
                  </template>
                </div>
                <div v-if="item.capKey && lineTotal(item) > Number(baselineSettings[item.capKey])" class="budget-error-inline">
                  <span class="material-symbols-outlined text-[13px] mr-1">warning</span>
                  Exceeds the {{ peso(baselineSettings[item.capKey]) }} limit for this item.
                </div>
              </div>

              <!-- Item Value & Clear Actions -->
              <div class="budget-item-side">
                <span class="line-total-badge" :class="{ 'is-active': lineTotal(item) > 0 }">
                  {{ peso(lineTotal(item)) }}
                </span>
                <div class="bl-acts">
                  <button 
                    v-if="lineTotal(item) > 0"
                    type="button" 
                    class="bl-action-btn bl-clear-btn" 
                    title="Clear rate and multipliers" 
                    @click="clearLine(item)"
                  >
                    Clear
                  </button>
                  <button 
                    v-if="item.custom" 
                    type="button" 
                    class="bl-action-btn bl-remove-btn" 
                    title="Remove custom budget line" 
                    @click="removeLine(vId, item)"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>

            <!-- Add Extra Item Button -->
            <button 
              v-if="g.addLabel" 
              type="button" 
              class="btn-add-group-row" 
              @click="addLine(vId, g.key)"
            >
              <span class="material-symbols-outlined text-[15px]">add_circle</span>
              <span>Add {{ g.addLabel }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, ref, onMounted } from 'vue';

const props = defineProps({
  label: { type: String, default: 'Proposed Budgetary Requirements *' },
  venues: { type: Array, required: true },
  venueBudgets: { type: Object, required: true }, // The main state model passed from parent
  baselineSettings: { type: Object, required: true },
  isOutsideBsu: { type: Boolean, required: true },
  computedDays: { type: Number, required: true },
  filteredVenues: { type: Array, default: () => [] },
  customVenuesList: { type: Array, default: () => [] },
  venueInsideMap: { type: Object, default: null }
});

const emit = defineEmits(['update:venueBudgets']);

const unitSuggestions = [
  'days',
  'day',
  'pax',
  'hours',
  'hr',
  'nights',
  'night',
  'pcs',
  'pc',
  'sets',
  'set',
  'trips',
  'trip',
  'speakers',
  'speaker',
  'recipients',
  'recipient',
  'meals',
  'meal',
  'snacks',
  'snack',
  'batch',
  'lot'
];

const openUnitPicker = (event) => {
  if (event && event.target) {
    event.target.select();
    if (typeof event.target.showPicker === 'function') {
      try {
        event.target.showPicker();
      } catch (e) {
        // Fallback gracefully if browser policy restricts
      }
    }
  }
};

const standardMealKeys = [
  { name: 'Breakfast', label: 'Breakfast' },
  { name: 'AM Snack', label: 'AM Snack' },
  { name: 'Lunch', label: 'Lunch' },
  { name: 'PM Snack', label: 'PM Snack' },
  { name: 'Dinner', label: 'Dinner' }
];

const isStandardMeal = name => standardMealKeys.some(m => m.name === name);

const nonCateringGroups = [
  { key: 'logistics', materialIcon: 'apartment', title: 'Venue & Logistics' },
  { key: 'program', materialIcon: 'school', title: 'Program & Speakers' },
  { key: 'materials', materialIcon: 'inventory_2', title: 'Materials & Miscellaneous', addLabel: 'custom item' }
];

const budgetGroups = [
  { key: 'catering', materialIcon: 'restaurant', title: 'Catering & Hospitality (Meals / Snacks)', addLabel: 'meal / snack' },
  ...nonCateringGroups
];

let lineSeq = 1;
const peso = n => '₱' + (Number(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const lineTotal = it => (Number(it.rate) || 0) * (it.mult || []).reduce((p, m) => p * (Number(m.q) || 0), 1);
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

const paxOf = it => {
  if (!it || !Array.isArray(it.mult)) return 0;
  const hm = getHeadcountMultiplier(it, it.mult);
  return Number(hm?.q) || 0;
};
const lineFormula = it => [peso(it.rate), ...(it.mult || []).map(m => `${Number(m.q) || 0} ${String(m.u || '').trim()}`.trim())].join(' × ');

// Collapsible Accordion State for Budget Sections (Default: all uncollapsed / expanded)
const collapsedSections = ref({});

const isSectionOpen = (vId, groupKey) => {
  return !collapsedSections.value[`${vId}_${groupKey}`];
};

const toggleSection = (vId, groupKey) => {
  const key = `${vId}_${groupKey}`;
  collapsedSections.value[key] = !collapsedSections.value[key];
};

// Multi-venue tab handling
const selectedVenueTab = ref('all');

const activeVenues = computed(() => {
  return Array.isArray(props.venues) ? props.venues : [];
});

watch(activeVenues, (newVenues) => {
  if (newVenues.length > 0) {
    if (!selectedVenueTab.value || (!newVenues.includes(selectedVenueTab.value) && selectedVenueTab.value !== 'all')) {
      selectedVenueTab.value = 'all';
    }
  } else {
    selectedVenueTab.value = 'all';
  }
}, { immediate: true });

const displayedVenues = computed(() => {
  if (activeVenues.value.length <= 1 || selectedVenueTab.value === 'all') {
    return activeVenues.value;
  }
  return activeVenues.value.filter(v => v === selectedVenueTab.value);
});

// Meals Selection & Active Card Management
const activeMealsMap = ref({});
const constantPaxMap = ref({});

const syncActiveMealsForVenue = (vId, forceReset = false) => {
  const items = (props.venueBudgets[vId] || []).filter(i => i.group === 'catering');
  if (!items.length) {
    if (!activeMealsMap.value[vId] || forceReset) {
      activeMealsMap.value[vId] = new Set();
    }
    return;
  }
  
  // If already initialized and not force-resetting, keep user selections and ensure any configured items are also visible
  if (activeMealsMap.value[vId] && !forceReset) {
    items.forEach(i => {
      if ((lineTotal(i) > 0 || paxOf(i) > 0) && isStandardMeal(i.name)) {
        activeMealsMap.value[vId].add(i.name);
      }
    });
    return;
  }
  
  // Initial sync: check if there are configured items (e.g. In Submit AR loaded from AD or draft)
  const configuredItems = items.filter(i => lineTotal(i) > 0 || paxOf(i) > 0);
  
  if (configuredItems.length > 0) {
    activeMealsMap.value[vId] = new Set(
      configuredItems.filter(i => isStandardMeal(i.name)).map(i => i.name)
    );
  } else {
    // If no items have positive values yet (fresh AD)
    if (items.length >= 5) {
      activeMealsMap.value[vId] = new Set(standardMealKeys.map(m => m.name));
    } else {
      activeMealsMap.value[vId] = new Set(items.filter(i => isStandardMeal(i.name)).map(i => i.name));
    }
  }
};

// Keep active meals synchronized when venueBudgets is updated
watch(() => props.venueBudgets, (newBudgets) => {
  if (!newBudgets || Object.keys(newBudgets).length === 0) {
    activeMealsMap.value = {};
    return;
  }
  Object.keys(newBudgets).forEach(vId => {
    if (!activeMealsMap.value[vId]) {
      syncActiveMealsForVenue(vId);
    }
  });
  Object.keys(activeMealsMap.value).forEach(vId => {
    if (!newBudgets[vId]) {
      delete activeMealsMap.value[vId];
    }
  });
}, { immediate: true });

const isMealSelected = (vId, mealName) => {
  if (!activeMealsMap.value[vId]) {
    syncActiveMealsForVenue(vId);
  }
  return activeMealsMap.value[vId]?.has(mealName) ?? false;
};

const toggleMealSelection = (vId, mealName) => {
  if (!activeMealsMap.value[vId]) {
    syncActiveMealsForVenue(vId);
  }
  const set = activeMealsMap.value[vId];
  if (set.has(mealName)) {
    // Deselect meal
    set.delete(mealName);
    const item = (props.venueBudgets[vId] || []).find(i => i.name === mealName);
    if (item) {
      getPaxMultiplier(item).q = 0;
    }
  } else {
    // Select meal: ensure item exists in venueBudgets
    set.add(mealName);
    let item = (props.venueBudgets[vId] || []).find(i => i.name === mealName);
    if (!item) {
      // If it wasn't in venueBudgets (e.g. In Submit AR when AD only had Lunch and PM Snack), create it now!
      const baseKey = mealName.includes('Snack') ? 'snacks' : 'meals';
      item = bl('catering', mealName, cateringMult(), { baseKey, custom: true }, vId);
      if (!props.venueBudgets[vId]) {
        props.venueBudgets[vId] = [];
      }
      props.venueBudgets[vId].push(item);
    }
    
    const cp = Number(constantPaxMap.value[vId]) || 0;
    if (cp > 0 && getPaxMultiplier(item).q === 0) {
      getPaxMultiplier(item).q = cp;
    }
    if (!item.rate && item.base) {
      item.rate = item.base;
    }
  }
  emit('update:venueBudgets', props.venueBudgets);
};

const selectAllMeals = (vId) => {
  if (!activeMealsMap.value[vId]) {
    syncActiveMealsForVenue(vId);
  }
  const set = activeMealsMap.value[vId];
  standardMealKeys.forEach(m => {
    set.add(m.name);
    let item = (props.venueBudgets[vId] || []).find(i => i.name === m.name);
    if (!item) {
      const baseKey = m.name.includes('Snack') ? 'snacks' : 'meals';
      item = bl('catering', m.name, cateringMult(), { baseKey, custom: true }, vId);
      if (!props.venueBudgets[vId]) props.venueBudgets[vId] = [];
      props.venueBudgets[vId].push(item);
    }
    const cp = Number(constantPaxMap.value[vId]) || 0;
    if (cp > 0 && getPaxMultiplier(item).q === 0) {
      getPaxMultiplier(item).q = cp;
    }
    if (!item.rate && item.base) {
      item.rate = item.base;
    }
  });
  emit('update:venueBudgets', props.venueBudgets);
};

const clearAllMeals = (vId) => {
  if (!activeMealsMap.value[vId]) {
    syncActiveMealsForVenue(vId);
  }
  activeMealsMap.value[vId].clear();
  if (Array.isArray(props.venueBudgets[vId])) {
    const toRemove = [];
    props.venueBudgets[vId].forEach(it => {
      if (it.group === 'catering') {
        if (it.custom && !isStandardMeal(it.name)) {
          toRemove.push(it);
        } else {
          getPaxMultiplier(it).q = 0;
        }
      }
    });
    toRemove.forEach(it => {
      const idx = props.venueBudgets[vId].indexOf(it);
      if (idx !== -1) props.venueBudgets[vId].splice(idx, 1);
    });
  }
  emit('update:venueBudgets', props.venueBudgets);
};

const deselectMeal = (vId, item) => {
  if (item.custom && !isStandardMeal(item.name)) {
    removeLine(vId, item);
    return;
  }
  getPaxMultiplier(item).q = 0;
  if (!activeMealsMap.value[vId]) {
    syncActiveMealsForVenue(vId);
  }
  activeMealsMap.value[vId].delete(item.name);
  emit('update:venueBudgets', props.venueBudgets);
};

const applyConstantPax = (vId) => {
  const paxVal = Number(constantPaxMap.value[vId]) || 0;
  if (paxVal <= 0) return;
  const items = groupItems(vId, 'catering');
  items.forEach(item => {
    if (isMealSelected(vId, item.name)) {
      getPaxMultiplier(item).q = paxVal;
    }
  });
};

// Catering Multiplier Helpers
const getPaxMultiplier = (item) => {
  if (!Array.isArray(item.mult)) item.mult = [];
  let p = item.mult.find(m => /^(pax|person|persons|head|heads|people|participant|participants|attendee|attendees)$/i.test(String(m.u || '').trim()));
  if (!p) {
    p = { q: 0, u: 'pax' };
    item.mult.unshift(p);
  }
  return p;
};

// All multipliers except the primary pax multiplier (e.g. Days, and any custom multipliers)
const nonPaxMultipliers = (item) => {
  if (!Array.isArray(item.mult)) return [];
  const paxM = getPaxMultiplier(item);
  return item.mult.filter(m => m !== paxM);
};

const removeMultiplier = (item, m) => {
  if (!Array.isArray(item.mult)) return;
  const idx = item.mult.indexOf(m);
  if (idx !== -1) {
    item.mult.splice(idx, 1);
  }
};

// Ordering of Catering Items (Breakfast -> AM Snack -> Lunch -> PM Snack -> Dinner -> Custom)
const mealOrder = ['Breakfast', 'AM Snack', 'Lunch', 'PM Snack', 'Dinner'];
const sortedCateringItems = (vId) => {
  const items = groupItems(vId, 'catering');
  return [...items].sort((a, b) => {
    const idxA = mealOrder.indexOf(a.name);
    const idxB = mealOrder.indexOf(b.name);
    if (idxA !== -1 && idxB !== -1) return idxA - idxB;
    if (idxA !== -1) return -1;
    if (idxB !== -1) return 1;
    return (a.id || 0) - (b.id || 0);
  });
};

const visibleCateringItems = (vId) => {
  return sortedCateringItems(vId).filter(it => {
    if (!isStandardMeal(it.name)) return true;
    return isMealSelected(vId, it.name);
  });
};

const venueMaxPax = (vId) => {
  const items = groupItems(vId, 'catering');
  return Math.max(0, ...items.map(paxOf));
};

const activeCountText = (vId, groupKey) => {
  const items = groupItems(vId, groupKey);
  const activeItems = items.filter(it => lineTotal(it) > 0);
  if (activeItems.length === 0) return '0 configured';
  return `${activeItems.length} active`;
};

// Determine if a specific venue is outside BSU
const venueIsOutside = (vId) => {
  if (props.venueInsideMap !== null && props.venueInsideMap !== undefined) {
    return !(props.venueInsideMap[String(vId)] ?? true);
  }
  return props.isOutsideBsu;
};

const venueTotal = (vId) => {
  const lines = props.venueBudgets[vId] || [];
  return lines.reduce((s, it) => s + lineTotal(it), 0);
};

const baseFor = (l, vId) => {
  const b = props.baselineSettings;
  const out = vId !== undefined ? venueIsOutside(vId) : props.isOutsideBsu;
  if (!b) return 0;
  if (l.baseKey === 'meals') return out ? b.meals_outside : b.meals_inside;
  if (l.baseKey === 'snacks') return out ? b.snacks_outside : b.snacks_inside;
  return b[l.baseKey] || 0;
};

const bl = (group, name, mult = [], extra = {}, vId = undefined) => {
  const l = { id: lineSeq++, group, name, rate: '', mult, ...extra };
  if (l.baseKey) { 
    l.base = baseFor(l, vId); 
    l.rate = l.base; 
  }
  return l;
};

const cateringMult = () => [{ q: 0, u: 'pax' }, { q: props.computedDays, u: 'days' }];

const defaultBudgetLines = (vId = undefined) => [
  bl('catering', 'Breakfast', cateringMult(), { baseKey: 'meals', custom: true }, vId),
  bl('catering', 'Lunch', cateringMult(), { baseKey: 'meals', custom: true }, vId),
  bl('catering', 'Dinner', cateringMult(), { baseKey: 'meals', custom: true }, vId),
  bl('catering', 'AM Snack', cateringMult(), { baseKey: 'snacks', custom: true }, vId),
  bl('catering', 'PM Snack', cateringMult(), { baseKey: 'snacks', custom: true }, vId),
  bl('logistics', 'Function Room/Venue', [], { hint: 'Leave blank/zero for Attribution' }),
  bl('logistics', 'Accommodation'),
  bl('logistics', 'Equipment Rental'),
  bl('logistics', 'Transportation', [], { capKey: 'transportation_limit' }),
  bl('program', 'Professional Fee/Honoraria', [{ q: 0, u: 'speakers' }], { baseKey: 'pf_honoraria' }),
  bl('program', 'Token/s', [{ q: 0, u: 'recipients' }], { baseKey: 'tokens' }),
  bl('materials', 'Materials and Supplies')
];

const groupItems = (vId, g) => (props.venueBudgets[vId] || []).filter(i => i.group === g);
const groupTotal = (vId, g) => groupItems(vId, g).reduce((s, i) => s + lineTotal(i), 0);

const addLine = (vId, g) => {
  if (!props.venueBudgets[vId]) {
    props.venueBudgets[vId] = [];
  }
  const newLine = bl(g, '', g === 'catering' ? cateringMult() : [], { custom: true }, vId);
  if (g === 'catering') {
    const cp = Number(constantPaxMap.value[vId]) || 0;
    if (cp > 0) {
      getPaxMultiplier(newLine).q = cp;
    }
  }
  props.venueBudgets[vId].push(newLine);
  emit('update:venueBudgets', props.venueBudgets);
};

const removeLine = (vId, item) => {
  if (!props.venueBudgets[vId]) return;
  const arr = props.venueBudgets[vId];
  const idx = arr.indexOf(item);
  if (idx !== -1) {
    arr.splice(idx, 1);
    emit('update:venueBudgets', props.venueBudgets);
  }
};

const clearLine = item => {
  if (item.group === 'catering') {
    getPaxMultiplier(item).q = 0;
    if (item.baseKey) {
      item.rate = item.base || 0;
    } else {
      item.rate = '';
    }
    item.mult = item.mult.filter(m => 
      /^(pax|person|persons|head|heads)$/i.test(String(m.u || '').trim()) ||
      /^(day|days)$/i.test(String(m.u || '').trim())
    );
  } else {
    item.mult = [];
    item.rate = '';
  }
};

const allLines = () => Object.values(props.venueBudgets).flat();
const allLinesWithVenue = () => {
  const result = [];
  Object.entries(props.venueBudgets).forEach(([vId, lines]) => {
    (lines || []).forEach(line => result.push({ line, vId }));
  });
  return result;
};

watch(() => props.venues, (newVenues) => {
  if (!newVenues) return;
  let changed = false;
  newVenues.forEach(vid => {
    if (!Array.isArray(props.venueBudgets[vid]) || props.venueBudgets[vid].length === 0) {
      props.venueBudgets[vid] = defaultBudgetLines(vid);
      changed = true;
    }
  });
  if (changed) {
    emit('update:venueBudgets', props.venueBudgets);
  }
}, { deep: true, immediate: true });

// Untouched rates follow the baseline (and the Inside/Outside BSU switch); edited rates are left alone
watch([() => props.isOutsideBsu, () => props.baselineSettings, () => props.venueInsideMap], () => {
  allLinesWithVenue().forEach(({ line: l, vId }) => {
    if (!l.baseKey) return;
    const nb = baseFor(l, vId);
    if (Number(l.rate) === Number(l.base)) l.rate = nb;
    l.base = nb;
  });
}, { deep: true });

// The schedule count can change after the venue budget is initialized.
// Keep the automatically supplied catering day multiplier in sync
watch(() => props.computedDays, (days) => {
  if (days === undefined || days === null) return;
  allLines().forEach(line => {
    if (line.group !== 'catering' || !Array.isArray(line.mult)) return;
    line.mult.forEach(multiplier => {
      if (String(multiplier.u || '').trim().toLowerCase() === 'days') {
        multiplier.q = days;
      }
    });
  });
}, { immediate: true });

// Expose these helpers if parents need to compute totals
const getGrandTotal = () => {
  let grandTotal = 0;
  Object.values(props.venueBudgets).forEach(items => {
    grandTotal += items.reduce((sum, i) => sum + lineTotal(i), 0);
  });
  return grandTotal;
};

const getMaxOverallPax = () => {
  let maxOverallPax = 0;
  Object.values(props.venueBudgets).forEach(items => {
    maxOverallPax += Math.max(0, ...items.filter(i => i.group === 'catering').map(paxOf));
  });
  return maxOverallPax;
};

defineExpose({
  getGrandTotal,
  getMaxOverallPax,
  lineTotal,
  paxOf,
  lineFormula,
  defaultBudgetLines,
  bl
});

const getVenueName = (id) => {
  if (String(id).startsWith('temp_')) {
    const custom = props.customVenuesList.find(x => String(x.venue_id) === String(id));
    return custom ? custom.venue_name : 'Custom Venue';
  }
  const v = props.filteredVenues.find(x => String(x.venue_id) === String(id));
  return v ? v.venue_name : 'Unknown Venue';
};
</script>

<style scoped>
.form-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #7e22ce;
  margin-bottom: 12px;
}

/* Multi-Venue Tabs Bar */
.venue-tabs-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.venue-tab-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 9999px;
  font-size: 13px;
  font-weight: 600;
  background: rgba(148, 163, 184, 0.1);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.venue-tab-btn:hover {
  background: rgba(185, 121, 204, 0.15);
  border-color: rgba(185, 121, 204, 0.3);
  color: #7e22ce;
}

.venue-tab-btn.active {
  background: rgba(185, 121, 204, 0.2);
  border-color: #b979cc;
  color: #7e22ce;
  box-shadow: 0 2px 6px rgba(185, 121, 204, 0.15);
}

.venue-tab-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 9999px;
  background: rgba(185, 121, 204, 0.2);
  color: #7e22ce;
}

.venue-tab-btn.active .venue-tab-badge {
  background: #7e22ce;
  color: #ffffff;
}

/* Venue Card Wrapper */
.venue-budget-wrapper {
  margin-bottom: 2rem;
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

/* Venue Header */
.venue-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
  gap: 12px;
}

.venue-title-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.venue-avatar-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(185, 121, 204, 0.15);
  color: #7e22ce;
  display: flex;
  align-items: center;
  justify-content: center;
}

.venue-budget-title {
  color: #1e293b;
  font-weight: 700;
  font-size: 16px;
  margin: 0;
}

.venue-rate-status-container {
  margin-top: 4px;
}

.venue-rate-outside {
  color: #be185d;
  background: #fdf2f8;
  border: 1px solid #fbcfe8;
  padding: 2px 8px;
  border-radius: 9999px;
  font-size: 11px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
}

.venue-rate-inside {
  color: #15803d;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  padding: 2px 8px;
  border-radius: 9999px;
  font-size: 11px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
}

.venue-subtotal-badge {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  padding: 6px 14px;
  border-radius: 10px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.subtotal-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.05em;
}

.subtotal-amount {
  font-size: 16px;
  font-weight: 800;
  color: #7e22ce;
}

/* Card-based Budget Groups Container */
.budget-groups-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.budget-section-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
  transition: all 0.2s ease;
}

/* ==========================================================================
   1. Meals & Snacks Card Section Styles
   ========================================================================== */
.meals-section-card {
  padding: 0;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.meals-header-bar {
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.meals-header-bar.is-open {
  border-bottom: 1px solid #f1f5f9;
}

.meals-collapsible-body {
  padding: 16px 20px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.meals-header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.meals-icon-box {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(185, 121, 204, 0.15);
  color: #7e22ce;
  display: flex;
  align-items: center;
  justify-content: center;
}

.meals-section-title {
  color: #0f172a;
  font-weight: 700;
  font-size: 15px;
  margin: 0;
}

.meals-legal-subtext {
  font-size: 11px;
  font-style: italic;
  color: #16a34a;
  font-weight: 500;
  display: block;
  margin-top: 2px;
}

.meals-header-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.meal-stat-pill {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  padding: 5px 12px;
  border-radius: 8px;
}

.meal-subtotal-pill {
  font-size: 13px;
  font-weight: 700;
  color: #7e22ce;
  background: #f3e8ff;
  border: 1px solid #e9d5ff;
  padding: 5px 14px;
  border-radius: 8px;
}

/* Meal Selector Chips Bar */
.meal-selector-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  padding: 10px 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
}

.meal-selector-label {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
}

.meal-chips-group {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  flex: 1;
}

.meal-chip-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border-radius: 9999px;
  font-size: 12px;
  font-weight: 600;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #475569;
  cursor: pointer;
  transition: all 0.15s ease;
}

.meal-chip-btn:hover {
  background: #fdf4ff;
  border-color: #b979cc;
  color: #7e22ce;
}

.meal-chip-btn.is-selected {
  background: rgba(185, 121, 204, 0.15);
  border-color: #b979cc;
  color: #7e22ce;
  font-weight: 700;
}

.meal-quick-toggles {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-left: auto;
}

.meal-text-toggle {
  background: none;
  border: none;
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  padding: 2px 4px;
  transition: color 0.15s;
}

.meal-text-toggle:hover {
  color: #7e22ce;
  text-decoration: underline;
}

.meal-toggle-dot {
  color: #cbd5e1;
  font-size: 12px;
}

.meal-add-custom-link {
  background: none;
  border: none;
  font-size: 11px;
  font-weight: 700;
  color: #7e22ce;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 6px;
  border-radius: 4px;
  transition: background 0.15s;
}

.meal-add-custom-link:hover {
  background: #f3e8ff;
}

/* Constant Pax Card */
.constant-pax-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  padding: 12px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}

.constant-pax-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.constant-pax-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(185, 121, 204, 0.15);
  color: #7e22ce;
  display: flex;
  align-items: center;
  justify-content: center;
}

.constant-pax-title {
  font-size: 13px;
  font-weight: 700;
  color: #0f172a;
}

.constant-pax-desc {
  font-size: 11px;
  color: #64748b;
  margin-top: 1px;
}

.constant-pax-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.constant-pax-input {
  width: 90px;
  padding: 6px 10px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #0f172a;
  font-size: 13px;
  font-weight: 600;
  text-align: center;
  outline: none;
  transition: border-color 0.15s;
}

.constant-pax-input:focus {
  border-color: #7e22ce;
  box-shadow: 0 0 0 2px rgba(126, 34, 206, 0.15);
}

.btn-apply-constant-pax {
  padding: 7px 16px;
  border-radius: 8px;
  background: #b979cc;
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-apply-constant-pax:hover {
  background: #a855f7;
}

/* Meals Card Grid */
.meals-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 14px;
}

@media (min-width: 1200px) {
  .meals-cards-grid {
    grid-template-columns: repeat(5, minmax(0, 1fr));
  }
}

.meal-item-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.meal-item-card:hover {
  border-color: #cbd5e1;
}

.meal-item-card.has-value {
  border-color: rgba(185, 121, 204, 0.5);
  background: rgba(250, 245, 255, 0.5);
  box-shadow: 0 2px 8px rgba(185, 121, 204, 0.08);
}

.meal-card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.meal-card-title-wrap {
  flex: 1;
  min-width: 0;
}

.meal-card-name {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.meal-custom-name-input {
  width: 100%;
  font-size: 12px;
  font-weight: 600;
  padding: 3px 6px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  outline: none;
}

.meal-custom-name-input:focus {
  border-color: #7e22ce;
}

.meal-card-top-right {
  display: flex;
  align-items: center;
  gap: 6px;
}

.meal-days-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 9999px;
  background: rgba(185, 121, 204, 0.15);
  color: #7e22ce;
  border: 1px solid rgba(185, 121, 204, 0.25);
  white-space: nowrap;
}

.meal-card-close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 1px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: color 0.15s;
}

.meal-card-close-btn:hover {
  color: #ef4444;
}

/* Meal Card Body Inputs */
.meal-card-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.meal-input-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.meal-row-label {
  width: 44px;
  font-size: 11px;
  font-weight: 800;
  color: #64748b;
  letter-spacing: 0.04em;
}

.meal-input-box {
  flex: 1;
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 4px 8px;
  transition: all 0.15s;
}

.meal-input-box:focus-within {
  border-color: #7e22ce;
  background: #ffffff;
  box-shadow: 0 0 0 2px rgba(126, 34, 206, 0.12);
}

.meal-currency-symbol {
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  margin-right: 3px;
}

.meal-val-input {
  width: 100%;
  border: none;
  background: transparent;
  outline: none;
  font-size: 13px;
  font-weight: 700;
  color: #0f172a;
}

.meal-baseline-hint {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  color: #64748b;
  padding-left: 52px;
}

.meal-reset-link {
  background: none;
  border: none;
  color: #7e22ce;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  display: inline-flex;
  align-items: center;
}

.meal-reset-link:hover {
  text-decoration: underline;
}

/* Multipliers in Meal Card (Editable, matching non-catering and old design) */
.meal-multipliers-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 4px;
}

.meal-mult-pill-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.meal-mult-pill-row .mult-container {
  display: inline-flex;
  align-items: center;
}

.meal-add-mult-btn-wrap {
  display: flex;
  margin-top: 2px;
}

/* Meal Card Footer */
.meal-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 12px;
  padding-top: 8px;
  border-top: 1px solid #f1f5f9;
}

.meal-card-total {
  font-size: 13px;
  font-weight: 800;
  color: #0f172a;
  margin-left: auto;
}

.meal-card-clear {
  background: none;
  border: none;
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  cursor: pointer;
  padding: 2px 4px;
  border-radius: 4px;
  transition: color 0.15s;
}

.meal-card-clear:hover {
  color: #0f172a;
}

/* Empty State */
.meals-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  text-align: center;
}

/* ==========================================================================
   2. Non-Catering Section Card Styles (Venue, Program, Materials)
   ========================================================================== */
.non-catering-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
}

.section-card-header {
  background: #ffffff;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
  gap: 10px;
}

.section-card-header:not(.is-open) {
  border-bottom: none;
}

/* Accordion Header & Chevron Styles */
.accordion-header-bar {
  cursor: pointer;
  user-select: none;
  transition: background-color 0.15s ease;
}

.meals-header-bar.accordion-header-bar:hover {
  background: rgba(185, 121, 204, 0.04);
}

.section-card-header.accordion-header-bar:hover {
  background: #f1f5f9;
}

.accordion-chevron-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: rgba(148, 163, 184, 0.1);
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.accordion-arrow {
  font-size: 20px;
  color: #64748b;
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s ease;
}

.accordion-arrow.is-open {
  transform: rotate(180deg);
  color: #7e22ce;
}

.section-title-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.section-icon-box {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(185, 121, 204, 0.12);
  color: #7e22ce;
  display: flex;
  align-items: center;
  justify-content: center;
}

.section-icon-box .material-symbols-outlined {
  font-size: 18px;
}

.section-titles {
  display: flex;
  flex-direction: column;
}

.section-card-title {
  color: #0f172a;
  font-weight: 700;
  font-size: 13px;
  margin: 0;
}

.section-active-count {
  font-size: 11px;
  font-weight: 500;
  color: #64748b;
}

.section-header-right {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-left: auto;
}

.section-subtotal-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #f3e8ff;
  border: 1px solid #e9d5ff;
  padding: 4px 10px;
  border-radius: 6px;
}

.section-subtotal-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  color: #7e22ce;
}

.section-subtotal-amount {
  font-size: 13px;
  font-weight: 700;
  color: #7e22ce;
}

.section-card-body {
  padding: 14px 18px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

/* Row Items */
.budget-row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  background: #ffffff;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
  gap: 16px;
}

.budget-row-item:hover {
  border-color: #cbd5e1;
}

.budget-row-item.has-value {
  border-color: rgba(185, 121, 204, 0.35);
  background: rgba(250, 245, 255, 0.6);
}

.budget-item-main {
  flex: 1;
  min-width: 0;
}

.item-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.budget-item-title {
  color: #1e293b;
  font-size: 13px;
  font-weight: 600;
}

.others-input-name {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #0f172a;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  max-width: 220px;
}

.others-input-name:focus {
  outline: none;
  border-color: #7e22ce;
  box-shadow: 0 0 0 2px rgba(126, 34, 206, 0.15);
}

.budget-item-subtext {
  color: #94a3b8;
  font-size: 11px;
}

/* Equation Pill Row */
.bl-equation-pill {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
}

.pill-unit-group {
  display: inline-flex;
  align-items: center;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 2px 4px;
  transition: all 0.15s ease;
}

.pill-unit-group:focus-within {
  border-color: #7e22ce;
  box-shadow: 0 0 0 2px rgba(126, 34, 206, 0.15);
}

.rate-container {
  padding-left: 8px;
}

.pill-prefix {
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
  margin-right: 2px;
}

.pill-input {
  background: transparent;
  border: none;
  font-size: 13px;
  font-weight: 700;
  color: #0f172a;
  outline: none;
}

.pill-rate-input {
  width: 85px;
}

.pill-qty-input {
  width: 55px;
  padding-left: 4px;
}

.bl-symbol {
  color: #94a3b8;
  font-size: 13px;
  font-weight: 700;
}

.pill-unit-tag {
  background: #f1f5f9;
  border: none;
  color: #7e22ce;
  width: 68px;
  min-width: 60px;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 6px;
  border-radius: 5px;
  text-align: center;
  outline: none;
  cursor: pointer;
  transition: all 0.15s ease;
}

.pill-unit-tag:focus {
  background: #ede9fe;
  color: #6b21a8;
}

.pill-rm-btn {
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 2px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s ease;
}

.pill-rm-btn:hover {
  color: #ef4444;
}

.btn-add-multiplier {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  background: #f8fafc;
  color: #7e22ce;
  border: 1px dashed #d8b4fe;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-add-multiplier:hover {
  background: #f3e8ff;
  border-color: #7e22ce;
}

.bl-note {
  font-size: 11px;
  color: #64748b;
  margin-top: 6px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.note-separator {
  color: #cbd5e1;
}

.bl-reset-link {
  background: none;
  border: none;
  color: #7e22ce;
  cursor: pointer;
  padding: 0;
  font-size: 11px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
}

.bl-reset-link:hover {
  text-decoration: underline;
}

.budget-error-inline {
  color: #ef4444;
  font-size: 11px;
  font-weight: 600;
  margin-top: 6px;
  display: flex;
  align-items: center;
}

/* Side Item Value */
.budget-item-side {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  flex-shrink: 0;
  min-width: 110px;
}

.line-total-badge {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #64748b;
  font-weight: 700;
  font-size: 14px;
  padding: 4px 10px;
  border-radius: 8px;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.line-total-badge.is-active {
  background: rgba(185, 121, 204, 0.15);
  border-color: rgba(185, 121, 204, 0.35);
  color: #7e22ce;
  font-weight: 800;
}

.bl-acts {
  display: flex;
  align-items: center;
  gap: 8px;
}

.bl-action-btn {
  background: none;
  border: none;
  font-size: 11px;
  cursor: pointer;
  padding: 2px 4px;
  border-radius: 4px;
  transition: all 0.15s ease;
}

.bl-clear-btn {
  color: #94a3b8;
}

.bl-clear-btn:hover {
  color: #0f172a;
}

.bl-remove-btn {
  color: #ef4444;
}

.bl-remove-btn:hover {
  color: #dc2626;
}

.btn-add-group-row {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px;
  background: #ffffff;
  border: 1px dashed #cbd5e1;
  color: #7e22ce;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-add-group-row:hover {
  background: #fdf4ff;
  border-color: #7e22ce;
}

/* ==========================================================================
   Dark Mode Overrides
   ========================================================================== */
html.dark .form-label, .dark .form-label {
  color: #b979cc;
}

html.dark .venue-tab-btn, .dark .venue-tab-btn {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
  color: #94a3b8;
}

html.dark .venue-tab-btn:hover, .dark .venue-tab-btn:hover {
  background: rgba(185, 121, 204, 0.15);
  border-color: rgba(185, 121, 204, 0.4);
  color: #e9d5ff;
}

html.dark .venue-tab-btn.active, .dark .venue-tab-btn.active {
  background: rgba(185, 121, 204, 0.2);
  border-color: #b979cc;
  color: #ffffff;
}

html.dark .venue-tab-badge, .dark .venue-tab-badge {
  background: rgba(185, 121, 204, 0.2);
  color: #d8b4e2;
}

html.dark .venue-tab-btn.active .venue-tab-badge, .dark .venue-tab-btn.active .venue-tab-badge {
  background: #b979cc;
  color: #ffffff;
}

html.dark .venue-budget-wrapper, .dark .venue-budget-wrapper {
  background: rgba(20, 22, 34, 0.95);
  border-color: rgba(185, 121, 204, 0.2);
}

html.dark .venue-card-header, .dark .venue-card-header {
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

html.dark .venue-avatar-icon, .dark .venue-avatar-icon {
  background: rgba(185, 121, 204, 0.2);
  color: #d8b4e2;
}

html.dark .venue-budget-title, .dark .venue-budget-title {
  color: #f1f5f9;
}

html.dark .venue-rate-outside, .dark .venue-rate-outside {
  color: #f9a8d4;
  background: rgba(244, 63, 94, 0.12);
  border-color: rgba(244, 63, 94, 0.3);
}

html.dark .venue-rate-inside, .dark .venue-rate-inside {
  color: #86efac;
  background: rgba(16, 185, 129, 0.12);
  border-color: rgba(16, 185, 129, 0.3);
}

html.dark .venue-subtotal-badge, .dark .venue-subtotal-badge {
  background: rgba(15, 23, 42, 0.6);
  border-color: rgba(255, 255, 255, 0.1);
}

html.dark .subtotal-label, .dark .subtotal-label {
  color: #94a3b8;
}

html.dark .subtotal-amount, .dark .subtotal-amount {
  color: #d8b4e2;
}

/* Dark Mode - Meals Card Section */
html.dark .meals-section-card, .dark .meals-section-card {
  background: rgba(24, 25, 38, 0.85);
  border-color: rgba(185, 121, 204, 0.25);
}

html.dark .meals-icon-box, .dark .meals-icon-box {
  background: rgba(185, 121, 204, 0.2);
  color: #d8b4e2;
}

html.dark .meals-section-title, .dark .meals-section-title {
  color: #ffffff;
}

html.dark .meals-legal-subtext, .dark .meals-legal-subtext {
  color: #34d399;
}

html.dark .meal-stat-pill, .dark .meal-stat-pill {
  background: rgba(15, 23, 42, 0.7);
  border-color: rgba(185, 121, 204, 0.25);
  color: #94a3b8;
}

html.dark .meal-subtotal-pill, .dark .meal-subtotal-pill {
  background: rgba(185, 121, 204, 0.15);
  border-color: rgba(185, 121, 204, 0.35);
  color: #d8b4e2;
}

html.dark .meal-selector-bar, .dark .meal-selector-bar {
  background: rgba(18, 20, 30, 0.7);
  border-color: rgba(255, 255, 255, 0.08);
}

html.dark .meal-selector-label, .dark .meal-selector-label {
  color: #94a3b8;
}

html.dark .meal-chip-btn, .dark .meal-chip-btn {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.12);
  color: #94a3b8;
}

html.dark .meal-chip-btn:hover, .dark .meal-chip-btn:hover {
  background: rgba(185, 121, 204, 0.15);
  border-color: rgba(185, 121, 204, 0.4);
  color: #e9d5ff;
}

html.dark .meal-chip-btn.is-selected, .dark .meal-chip-btn.is-selected {
  background: rgba(185, 121, 204, 0.22);
  border-color: #b979cc;
  color: #f3e8ff;
  box-shadow: 0 2px 8px rgba(185, 121, 204, 0.2);
}

html.dark .meal-text-toggle, .dark .meal-text-toggle {
  color: #94a3b8;
}

html.dark .meal-text-toggle:hover, .dark .meal-text-toggle:hover {
  color: #d8b4e2;
}

html.dark .meal-toggle-dot, .dark .meal-toggle-dot {
  color: #475569;
}

html.dark .meal-add-custom-link, .dark .meal-add-custom-link {
  color: #d8b4e2;
}

html.dark .meal-add-custom-link:hover, .dark .meal-add-custom-link:hover {
  background: rgba(185, 121, 204, 0.2);
}

html.dark .constant-pax-card, .dark .constant-pax-card {
  background: rgba(18, 20, 30, 0.75);
  border-color: rgba(185, 121, 204, 0.2);
}

html.dark .constant-pax-icon, .dark .constant-pax-icon {
  background: rgba(185, 121, 204, 0.2);
  color: #d8b4e2;
}

html.dark .constant-pax-title, .dark .constant-pax-title {
  color: #ffffff;
}

html.dark .constant-pax-desc, .dark .constant-pax-desc {
  color: #94a3b8;
}

html.dark .constant-pax-input, .dark .constant-pax-input {
  background: rgba(15, 23, 42, 0.8);
  border-color: rgba(185, 121, 204, 0.3);
  color: #ffffff;
}

html.dark .constant-pax-input:focus, .dark .constant-pax-input:focus {
  border-color: #b979cc;
}

html.dark .btn-apply-constant-pax, .dark .btn-apply-constant-pax {
  background: #b979cc;
  color: #ffffff;
}

html.dark .btn-apply-constant-pax:hover, .dark .btn-apply-constant-pax:hover {
  background: #c084fc;
}

/* Dark Mode - Meal Item Card */
html.dark .meal-item-card, .dark .meal-item-card {
  background: rgba(18, 19, 29, 0.9);
  border-color: rgba(185, 121, 204, 0.25);
  box-shadow: none;
}

html.dark .meal-item-card:hover, .dark .meal-item-card:hover {
  border-color: rgba(185, 121, 204, 0.4);
}

html.dark .meal-item-card.has-value, .dark .meal-item-card.has-value {
  background: rgba(30, 24, 44, 0.75);
  border-color: rgba(185, 121, 204, 0.5);
  box-shadow: 0 4px 14px rgba(185, 121, 204, 0.12);
}

html.dark .meal-card-name, .dark .meal-card-name {
  color: #ffffff;
}

html.dark .meal-custom-name-input, .dark .meal-custom-name-input {
  background: rgba(15, 23, 42, 0.8);
  border-color: rgba(255, 255, 255, 0.2);
  color: #ffffff;
}

html.dark .meal-days-badge, .dark .meal-days-badge {
  background: rgba(185, 121, 204, 0.2);
  color: #d8b4e2;
  border-color: rgba(185, 121, 204, 0.35);
}

html.dark .meal-card-close-btn, .dark .meal-card-close-btn {
  color: #64748b;
}

html.dark .meal-card-close-btn:hover, .dark .meal-card-close-btn:hover {
  color: #f87171;
}

html.dark .meal-row-label, .dark .meal-row-label {
  color: #94a3b8;
}

html.dark .meal-input-box, .dark .meal-input-box {
  background: rgba(15, 23, 42, 0.8);
  border-color: rgba(255, 255, 255, 0.15);
}

html.dark .meal-input-box:focus-within, .dark .meal-input-box:focus-within {
  border-color: #b979cc;
  background: rgba(15, 23, 42, 0.95);
  box-shadow: 0 0 0 2px rgba(185, 121, 204, 0.2);
}

html.dark .meal-currency-symbol, .dark .meal-currency-symbol {
  color: #94a3b8;
}

html.dark .meal-val-input, .dark .meal-val-input {
  color: #ffffff;
}

html.dark .meal-baseline-hint, .dark .meal-baseline-hint {
  color: #94a3b8;
}

html.dark .meal-reset-link, .dark .meal-reset-link {
  color: #d8b4e2;
}



html.dark .meal-card-footer, .dark .meal-card-footer {
  border-top-color: rgba(255, 255, 255, 0.08);
}

html.dark .meal-card-total, .dark .meal-card-total {
  color: #ffffff;
}

html.dark .meal-card-clear, .dark .meal-card-clear {
  color: #64748b;
}

html.dark .meal-card-clear:hover, .dark .meal-card-clear:hover {
  color: #e2e8f0;
}

html.dark .meals-empty-state, .dark .meals-empty-state {
  background: rgba(18, 20, 30, 0.6);
  border-color: rgba(255, 255, 255, 0.1);
}

/* Dark Mode - Non-Catering Cards */
html.dark .non-catering-card, .dark .non-catering-card {
  background: rgba(18, 20, 30, 0.7);
  border-color: rgba(255, 255, 255, 0.1);
}

html.dark .section-card-header, .dark .section-card-header {
  background: rgba(24, 25, 38, 0.85);
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

html.dark .meals-header-bar.is-open, .dark .meals-header-bar.is-open {
  border-bottom-color: rgba(255, 255, 255, 0.08);
}

html.dark .meals-header-bar.accordion-header-bar:hover, .dark .meals-header-bar.accordion-header-bar:hover {
  background: rgba(185, 121, 204, 0.1);
}

html.dark .section-card-header.accordion-header-bar:hover, .dark .section-card-header.accordion-header-bar:hover {
  background: rgba(255, 255, 255, 0.04);
}

html.dark .accordion-chevron-box, .dark .accordion-chevron-box {
  background: rgba(255, 255, 255, 0.06);
}

html.dark .accordion-arrow, .dark .accordion-arrow {
  color: #94a3b8;
}

html.dark .accordion-arrow.is-open, .dark .accordion-arrow.is-open {
  color: #d8b4e2;
}

html.dark .section-icon-box, .dark .section-icon-box {
  background: rgba(185, 121, 204, 0.2);
  color: #d8b4e2;
}

html.dark .section-card-title, .dark .section-card-title {
  color: #f1f5f9;
}

html.dark .section-active-count, .dark .section-active-count {
  color: #94a3b8;
}

html.dark .section-subtotal-badge, .dark .section-subtotal-badge {
  background: rgba(185, 121, 204, 0.15);
  border-color: rgba(185, 121, 204, 0.3);
}

html.dark .section-subtotal-label, .dark .section-subtotal-label {
  color: #d8b4e2;
}

html.dark .section-subtotal-amount, .dark .section-subtotal-amount {
  color: #d8b4e2;
}

html.dark .budget-row-item, .dark .budget-row-item {
  background: rgba(22, 24, 29, 0.6);
  border-color: rgba(255, 255, 255, 0.08);
}

html.dark .budget-row-item:hover, .dark .budget-row-item:hover {
  border-color: rgba(255, 255, 255, 0.18);
}

html.dark .budget-row-item.has-value, .dark .budget-row-item.has-value {
  background: rgba(185, 121, 204, 0.08);
  border-color: rgba(185, 121, 204, 0.3);
}

html.dark .budget-item-title, .dark .budget-item-title {
  color: #e2e8f0;
}

html.dark .others-input-name, .dark .others-input-name {
  background: rgba(15, 23, 42, 0.8);
  border-color: rgba(255, 255, 255, 0.2);
  color: #ffffff;
}

html.dark .pill-unit-group, .dark .pill-unit-group {
  background: rgba(15, 23, 42, 0.8);
  border-color: rgba(255, 255, 255, 0.2);
}

html.dark .pill-unit-group:focus-within, .dark .pill-unit-group:focus-within {
  border-color: #b979cc;
  box-shadow: 0 0 0 2px rgba(185, 121, 204, 0.25);
}

html.dark .pill-prefix, .dark .pill-prefix {
  color: #94a3b8;
}

html.dark .pill-input, .dark .pill-input {
  color: #f1f5f9;
}

html.dark .bl-symbol, .dark .bl-symbol {
  color: #64748b;
}

html.dark .pill-unit-tag, .dark .pill-unit-tag {
  background: rgba(185, 121, 204, 0.2);
  color: #d8b4e2;
}

html.dark .pill-rm-btn:hover, .dark .pill-rm-btn:hover {
  color: #f87171;
}

html.dark .btn-add-multiplier, .dark .btn-add-multiplier {
  background: rgba(185, 121, 204, 0.1);
  color: #d8b4e2;
  border-color: rgba(185, 121, 204, 0.3);
}

html.dark .btn-add-multiplier:hover, .dark .btn-add-multiplier:hover {
  background: rgba(185, 121, 204, 0.2);
  border-color: #b979cc;
}

html.dark .bl-note, .dark .bl-note {
  color: #94a3b8;
}

html.dark .note-separator, .dark .note-separator {
  color: #475569;
}

html.dark .bl-reset-link, .dark .bl-reset-link {
  color: #d8b4e2;
}

html.dark .line-total-badge, .dark .line-total-badge {
  background: rgba(15, 23, 42, 0.7);
  border-color: rgba(255, 255, 255, 0.1);
  color: #94a3b8;
}

html.dark .line-total-badge.is-active, .dark .line-total-badge.is-active {
  background: rgba(185, 121, 204, 0.18);
  border-color: rgba(185, 121, 204, 0.4);
  color: #e9d5ff;
}

html.dark .bl-clear-btn, .dark .bl-clear-btn {
  color: #64748b;
}

html.dark .bl-clear-btn:hover, .dark .bl-clear-btn:hover {
  color: #e2e8f0;
}

html.dark .btn-add-group-row, .dark .btn-add-group-row {
  background: rgba(15, 23, 42, 0.6);
  border-color: rgba(255, 255, 255, 0.12);
  color: #d8b4e2;
}

html.dark .btn-add-group-row:hover, .dark .btn-add-group-row:hover {
  background: rgba(185, 121, 204, 0.15);
  border-color: #b979cc;
}
</style>

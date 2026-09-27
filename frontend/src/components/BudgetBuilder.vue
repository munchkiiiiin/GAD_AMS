<template>
  <div class="budget-section">
    <label class="form-label" v-if="label">{{ label }}</label>
    <datalist id="bl-units">
      <option v-for="u in unitSuggestions" :key="u" :value="u"></option>
    </datalist>
    
    <div v-for="vId in (venues && venues.length ? venues : [])" :key="vId" class="venue-budget-wrapper" style="margin-bottom: 2rem; border-radius: 8px; padding: 1rem; border: 1px solid rgba(185, 121, 204, 0.3);">
      <h4 style="color: #e9d5ff; margin-bottom: 15px; border-left: 4px solid #b979cc; padding-left: 10px;">Budget for Venue: {{ getVenueName(vId) }}</h4>
      <div class="budget-groups-container" style="display: flex; flex-direction: column; gap: 16px;">
        <div v-for="g in budgetGroups" :key="g.key" class="budget-group-card">
          <div class="budget-group-header" style="justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <span class="budget-group-icon">{{ g.icon }}</span>
              <span class="budget-group-title">{{ g.title }}</span>
            </div>
            <div class="budget-group-total">{{ peso(groupTotal(vId, g.key)) }}</div>
          </div>
          <div class="budget-group-content">
            <div v-for="item in groupItems(vId, g.key)" :key="item.id" class="budget-row-item" style="align-items: flex-start;">
              <div class="budget-item-info">
                <input v-if="item.custom" type="text" v-model="item.name" class="others-input-name" placeholder="Item name (e.g. Coffee)" />
                <div v-else class="budget-item-title">{{ item.name }}</div>
                <span v-if="item.hint" class="budget-item-subtext">({{ item.hint }})</span>
                <div class="bl-ctl">
                  <span class="budget-currency-symbol">₱</span>
                  <input type="number" min="0" step="0.01" v-model.number="item.rate" class="bl-rate" :placeholder="item.mult.length ? 'Rate' : 'Amount'" />
                  <template v-for="(m, mi) in item.mult" :key="mi">
                    <span class="bl-x">×</span>
                    <span class="bl-mult">
                      <input type="number" min="0" step="any" v-model.number="m.q" class="bl-q" />
                      <input type="text" list="bl-units" v-model="m.u" class="bl-u" placeholder="unit" />
                      <button type="button" class="bl-rm" title="Remove multiplier" @click="item.mult.splice(mi, 1)">✕</button>
                    </span>
                  </template>
                  <button type="button" class="btn-add-other" @click="item.mult.push({ q: 1, u: '' })">+ multiplier</button>
                </div>
                <div v-if="item.baseKey" class="bl-note">Baseline rate {{ peso(item.base) }}<template v-if="Number(item.rate) !== Number(item.base)"> · <button type="button" class="bl-link" @click="item.rate = item.base">reset</button></template></div>
                <div v-if="item.capKey && lineTotal(item) > Number(baselineSettings[item.capKey])" class="budget-error-inline">Exceeds the {{ peso(baselineSettings[item.capKey]) }} limit for this item.</div>
              </div>
              <div class="budget-item-value" style="width: 150px; flex-direction: column; align-items: flex-end;">
                <span class="others-total-badge">{{ peso(lineTotal(item)) }}</span>
                <div class="bl-acts">
                  <button type="button" class="bl-clear" title="Remove multipliers and set this line to ₱0.00" @click="clearLine(item)">Clear</button>
                  <button v-if="item.custom" type="button" class="btn-remove-other" style="font-size: 11px;" @click="removeLine(vId, item)">Remove</button>
                </div>
              </div>
            </div>
            <button v-if="g.addLabel" type="button" class="btn-add-other" style="width: 100%; justify-content: center;" @click="addLine(vId, g.key)"><span>+</span> Add {{ g.addLabel }}</button>
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
  customVenuesList: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:venueBudgets']);

const unitSuggestions = ['pax', 'day', 'hr', 'night', 'pc', 'set', 'trip', 'speaker', 'snack', 'meal'];
const budgetGroups = [
  { key: 'catering', icon: '🍽️', title: 'Catering & Hospitality (Meals/Snacks)', addLabel: 'meal/snack' },
  { key: 'logistics', icon: '🏨', title: 'Venue & Logistics' },
  { key: 'program', icon: '🎓', title: 'Program & Speakers' },
  { key: 'materials', icon: '📦', title: 'Materials & Miscellaneous', addLabel: 'item' }
];

let lineSeq = 1;
const peso = n => '₱' + (Number(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const lineTotal = it => (Number(it.rate) || 0) * it.mult.reduce((p, m) => p * (Number(m.q) || 0), 1);
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
const lineFormula = it => [peso(it.rate), ...it.mult.map(m => `${Number(m.q) || 0} ${String(m.u || '').trim()}`.trim())].join(' × ');

const baseFor = l => {
  const b = props.baselineSettings;
  const out = props.isOutsideBsu;
  if (!b) return 0;
  if (l.baseKey === 'meals') return out ? b.meals_outside : b.meals_inside;
  if (l.baseKey === 'snacks') return out ? b.snacks_outside : b.snacks_inside;
  return b[l.baseKey] || 0;
};

const bl = (group, name, mult = [], extra = {}) => {
  const l = { id: lineSeq++, group, name, rate: '', mult, ...extra };
  if (l.baseKey) { 
    l.base = baseFor(l); 
    l.rate = l.base; 
  }
  return l;
};

const cateringMult = () => [{ q: 0, u: 'pax' }, { q: props.computedDays, u: 'days' }];

const defaultBudgetLines = () => [
  bl('catering', 'Breakfast', cateringMult(), { baseKey: 'meals', custom: true }),
  bl('catering', 'Lunch', cateringMult(), { baseKey: 'meals', custom: true }),
  bl('catering', 'Dinner', cateringMult(), { baseKey: 'meals', custom: true }),
  bl('catering', 'AM Snack', cateringMult(), { baseKey: 'snacks', custom: true }),
  bl('catering', 'PM Snack', cateringMult(), { baseKey: 'snacks', custom: true }),
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
  if (!props.venueBudgets[vId]) return;
  props.venueBudgets[vId].push(bl(g, '', g === 'catering' ? cateringMult() : [], { custom: true }));
};
const removeLine = (vId, item) => {
  if (!props.venueBudgets[vId]) return;
  const arr = props.venueBudgets[vId];
  arr.splice(arr.indexOf(item), 1);
};
const clearLine = item => { item.mult = []; item.rate = ''; };

const allLines = () => Object.values(props.venueBudgets).flat();

watch(() => props.venues, (newVenues) => {
  if (!newVenues) return;
  let changed = false;
  newVenues.forEach(vid => {
    if (!Array.isArray(props.venueBudgets[vid]) || props.venueBudgets[vid].length === 0) {
      props.venueBudgets[vid] = defaultBudgetLines();
      changed = true;
    }
  });
  if (changed) {
    emit('update:venueBudgets', props.venueBudgets);
  }
}, { deep: true, immediate: true });

// Untouched rates follow the baseline (and the Inside/Outside BSU switch); edited rates are left alone
watch([() => props.isOutsideBsu, () => props.baselineSettings], () => {
  allLines().forEach(l => {
    if (!l.baseKey) return;
    const nb = baseFor(l);
    if (Number(l.rate) === Number(l.base)) l.rate = nb;
    l.base = nb;
  });
}, { deep: true });

// The schedule count can change after the venue budget is initialized.
// Keep the automatically supplied catering day multiplier in sync so
// non-consecutive schedules are included in the proposed total.
watch(() => props.computedDays, (days) => {
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
/* Reusing the CSS from the main form */
.form-label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #b979cc;
}

.budget-group-card {
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 8px;
  overflow: hidden;
}
.budget-group-header {
  background: rgba(15, 23, 42, 0.6);
  padding: 12px 16px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}
.budget-group-icon {
  font-size: 20px;
}
.budget-group-title {
  color: #f8fafc;
  font-weight: 600;
  font-size: 14px;
}
.budget-group-total {
  color: #b979cc;
  font-weight: 700;
  font-size: 15px;
}
.budget-group-content {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.budget-row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: rgba(15, 23, 42, 0.4);
  border-radius: 6px;
  border: 1px solid rgba(148, 163, 184, 0.1);
}
.budget-item-title {
  color: #e2e8f0;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 6px;
}
.others-input-name {
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.3);
  color: #ffffff;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 14px;
  width: 100%;
  max-width: 200px;
  margin-bottom: 6px;
}
.others-input-name:focus {
  outline: none;
  border-color: #b979cc;
}
.budget-item-subtext {
  color: #94a3b8;
  font-size: 12px;
  margin-left: 6px;
}
.bl-ctl {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 4px;
}
.budget-currency-symbol {
  color: #cbd5e1;
  font-weight: 500;
}
.bl-rate, .bl-q {
  appearance: auto;
  -webkit-appearance: auto;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.3);
  color: #ffffff;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 14px;
}
.bl-rate { width: 90px; }
.bl-q { width: 60px; }
.bl-rate:focus, .bl-q:focus, .bl-u:focus {
  outline: none;
  border-color: #b979cc;
}
.bl-x {
  color: #94a3b8;
  font-size: 14px;
  font-weight: 500;
}
.bl-mult {
  display: flex;
  align-items: center;
  background: rgba(30, 41, 59, 0.5);
  border-radius: 4px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  padding: 2px;
  gap: 4px;
}
.bl-u {
  background: transparent;
  border: none;
  color: #e2e8f0;
  width: 60px;
  font-size: 13px;
  padding: 2px 4px;
}
.bl-rm {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  padding: 0 4px;
  font-size: 12px;
}
.bl-rm:hover {
  color: #f87171;
}
.btn-add-other {
  background: rgba(185, 121, 204, 0.1);
  color: #d8b4e2;
  border: 1px dashed rgba(185, 121, 204, 0.4);
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s;
}
.btn-add-other:hover {
  background: rgba(185, 121, 204, 0.2);
  border-color: rgba(185, 121, 204, 0.6);
}
.bl-note {
  font-size: 11px;
  color: #64748b;
  margin-top: 4px;
}
.bl-link {
  background: none;
  border: none;
  color: #b979cc;
  cursor: pointer;
  padding: 0;
  text-decoration: underline;
  font-size: 11px;
}
.budget-error-inline {
  color: #ef4444;
  font-size: 11px;
  margin-top: 4px;
}
.others-total-badge {
  background: rgba(15, 23, 42, 0.6);
  padding: 4px 12px;
  border-radius: 4px;
  color: #e2e8f0;
  font-weight: 600;
  font-size: 14px;
  border: 1px solid rgba(148, 163, 184, 0.2);
}
.bl-acts {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}
.bl-clear, .btn-remove-other {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  font-size: 11px;
  text-decoration: underline;
}
.bl-clear:hover, .btn-remove-other:hover {
  color: #cbd5e1;
}
.btn-remove-other {
  color: #ef4444;
}
.btn-remove-other:hover {
  color: #f87171;
}
</style>

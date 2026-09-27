<template>
  <div class="budget-dist-page">
    <!-- TOP HEADER -->
    <header class="dist-header">
      <div class="dist-header-content">
        <div class="header-titles">
          <div class="breadcrumb-row">
            <span class="badge-role">{{ userRoleName }}</span>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-text">Budget Management</span>
          </div>
          <h1 class="page-title">GAD Budget Distribution by Mandate</h1>
          <p class="page-subtitle">
            Monitor budget allocations, actual expenditures from verified Accomplishment Reports, pending commitments from approved Activity Designs, and remaining balances per institutional mandate.
          </p>
        </div>

        <div class="header-actions">
          <button class="btn-action btn-refresh" @click="fetchMandateStats" :disabled="loadingStats" title="Reload data">
            <span class="material-symbols-outlined" :class="{ 'spin-icon': loadingStats }">refresh</span>
            <span>{{ loadingStats ? 'Refreshing…' : 'Refresh' }}</span>
          </button>

          <router-link :to="planAndBudgetPath" class="btn-action btn-primary-plan">
            <span class="material-symbols-outlined">edit_document</span>
            <span>Edit GAD Plan &amp; Budget</span>
          </router-link>
        </div>
      </div>
    </header>

    <!-- KPI SUMMARY METRICS CARDS -->
    <div class="kpi-grid">
        <div class="kpi-card kpi-mandates">
          <div class="kpi-icon-wrap indigo">
            <span class="material-symbols-outlined">account_tree</span>
          </div>
          <div class="kpi-info">
            <span class="kpi-label">Active Mandates</span>
            <div class="kpi-value">{{ filteredMandateStats.length }} <span class="kpi-subtotal">/ {{ mandateStats.length }} total</span></div>
          </div>
        </div>

        <div class="kpi-card kpi-budget">
          <div class="kpi-icon-wrap purple">
            <span class="material-symbols-outlined">account_balance_wallet</span>
          </div>
          <div class="kpi-info">
            <span class="kpi-label">Total Mandate Budget</span>
            <div class="kpi-value mono">₱{{ formatNum(totals.budget) }}</div>
          </div>
        </div>

        <div class="kpi-card kpi-utilized">
          <div class="kpi-icon-wrap green">
            <span class="material-symbols-outlined">check_circle</span>
          </div>
          <div class="kpi-info">
            <span class="kpi-label">Utilized (Actual Cost)</span>
            <div class="kpi-value mono text-emerald">₱{{ formatNum(totals.utilized) }}</div>
          </div>
        </div>

        <div class="kpi-card kpi-pending">
          <div class="kpi-icon-wrap amber">
            <span class="material-symbols-outlined">pending_actions</span>
          </div>
          <div class="kpi-info">
            <span class="kpi-label">Pending (Approved ADs)</span>
            <div class="kpi-value mono text-amber">₱{{ formatNum(totals.pending) }}</div>
          </div>
        </div>

        <div class="kpi-card kpi-remaining">
          <div class="kpi-icon-wrap" :class="totals.remaining < 0 ? 'red' : 'blue'">
            <span class="material-symbols-outlined">{{ totals.remaining < 0 ? 'error' : 'savings' }}</span>
          </div>
          <div class="kpi-info">
            <span class="kpi-label">Remaining Balance</span>
            <div class="kpi-value mono" :class="totals.remaining < 0 ? 'text-rose' : 'text-blue'">
              ₱{{ formatNum(totals.remaining) }}
            </div>
          </div>
        </div>
      </div>

    <!-- TOOLBAR & FILTERS -->
    <div class="toolbar-section">
      <div class="search-wrap">
        <span class="material-symbols-outlined search-icon">search</span>
        <input
          v-model="searchQuery"
          type="text"
          class="search-input"
          placeholder="Search by mandate, cause, or GAD activity keyword…"
        />
        <button v-if="searchQuery" @click="searchQuery = ''" class="clear-search-btn" title="Clear search">✕</button>
      </div>

      <div class="filters-wrap">
        <label class="filter-label">Filter by Classification:</label>
        <div class="select-wrapper">
          <select v-model="mandateStatsFilter" class="filter-select">
            <option value="all">All Classifications</option>
            <option value="client">Client-Focused</option>
            <option value="org">Organization-Focused</option>
            <option value="attributed">Attributed Program</option>
          </select>
        </div>
      </div>
    </div>

    <!-- MAIN CARDS CONTENT -->
    <main class="content-body">
      <!-- Loading State -->
      <div v-if="loadingStats" class="state-container">
        <div class="spinner"></div>
        <p class="state-text">Loading mandate distribution data…</p>
      </div>

      <!-- No Mandate Data in DB -->
      <div v-else-if="mandateStats.length === 0" class="state-container empty-box">
        <span class="empty-icon">📭</span>
        <h3 class="empty-title">No Mandate Data Available</h3>
        <p class="empty-desc">
          Statistics are generated automatically from your saved GAD Plan.<br />
          Please go to the GAD Plan &amp; Budget Editor and ensure your plan is saved.
        </p>
        <router-link :to="planAndBudgetPath" class="btn-action btn-primary-plan mt-4">
          <span class="material-symbols-outlined">arrow_forward</span>
          <span>Go to GAD Plan &amp; Budget</span>
        </router-link>
      </div>

      <!-- Filtered out / No search match -->
      <div v-else-if="filteredMandateStats.length === 0" class="state-container empty-box">
        <span class="empty-icon">🔍</span>
        <h3 class="empty-title">No Matching Mandates</h3>
        <p class="empty-desc">
          No records match your selected classification or search query.
        </p>
        <button class="btn-action btn-outline mt-3" @click="resetFilters">
          Reset Filters
        </button>
      </div>

      <!-- Cards Grid (Matching user's attached design) -->
      <div v-else class="mandates-grid">
        <article
          v-for="(stat, idx) in filteredMandateStats"
          :key="stat.mandate_id || idx"
          class="mandate-card"
        >
          <!-- Content Section -->
          <div class="card-content-stack">
            <!-- Gender Issue / Mandate -->
            <div class="callout-block callout-mandate">
              <div class="callout-tag">Gender Issue / Mandate</div>
              <div class="callout-text font-medium">{{ stat.mandate || 'N/A' }}</div>
            </div>

            <!-- Cause of Gender Issue -->
            <div class="callout-block callout-cause">
              <div class="callout-tag">Cause of Gender Issue</div>
              <div class="callout-text text-secondary">{{ stat.cause || 'N/A' }}</div>
            </div>

            <!-- GAD Activity -->
            <div class="callout-block callout-activity">
              <div class="callout-tag">GAD Activity</div>
              <div class="callout-text text-secondary">{{ stat.activity || 'N/A' }}</div>
            </div>
          </div>

          <!-- Statistics & Financials -->
          <div class="card-stats-box">
            <!-- Approved ADs / ARs Counters -->
            <div class="counters-row">
              <div class="counter-box">
                <span class="counter-label">Approved ADs</span>
                <span class="counter-value">{{ stat.approved_ad_count || 0 }}</span>
              </div>
              <div class="counter-box">
                <span class="counter-label">Approved ARs</span>
                <span class="counter-value">{{ stat.approved_ar_count || 0 }}</span>
              </div>
            </div>

            <!-- Financial Figures -->
            <div class="finance-list">
              <div class="finance-row">
                <span class="finance-label">Budget:</span>
                <span class="finance-val mono">₱{{ formatNum(stat.budget) }}</span>
              </div>
              <div class="finance-row">
                <span class="finance-label">Utilized:</span>
                <span class="finance-val mono text-emerald">₱{{ formatNum(stat.utilized_budget) }}</span>
              </div>
              <div class="finance-row">
                <span class="finance-label">Pending (ADs):</span>
                <span class="finance-val mono text-amber">₱{{ formatNum(stat.pending_budget) }}</span>
              </div>
              <div class="finance-row finance-remaining">
                <span class="remaining-label">Remaining:</span>
                <span
                  class="finance-val mono"
                  :class="stat.remaining_budget < 0 ? 'text-rose font-bold' : 'text-blue font-bold'"
                >
                  ₱{{ formatNum(stat.remaining_budget) }}
                </span>
              </div>
            </div>

            <!-- Budget Lines Breakdown -->
            <div v-if="stat.budget_lines && stat.budget_lines.length > 0" class="lines-breakdown">
              <div class="lines-header" @click="stat._showLines = !stat._showLines">
                <span class="lines-title">Budget Lines Breakdown ({{ stat.budget_lines.length }})</span>
                <span class="lines-toggle">{{ stat._showLines ? 'Hide ▲' : 'Show ▼' }}</span>
              </div>

              <div v-show="stat._showLines !== false" class="lines-stack">
                <div v-for="bl in stat.budget_lines" :key="bl.id" class="line-card">
                  <div class="line-label">{{ bl.label || 'Unnamed Line' }}</div>
                  <div class="line-details">
                    <div class="line-detail-row">
                      <span>Original:</span>
                      <span class="mono">₱{{ formatNum(bl.amount) }}</span>
                    </div>
                    <div class="line-detail-row text-emerald">
                      <span>Utilized:</span>
                      <span class="mono">₱{{ formatNum(bl.utilized_budget) }}</span>
                    </div>
                    <div class="line-detail-row text-amber">
                      <span>Pending (AD):</span>
                      <span class="mono">₱{{ formatNum(bl.pending_budget) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Manage Allocations Action -->
          <button
            v-if="!isReadOnly"
            @click="openAllocationModal(stat)"
            class="btn-manage-allocations"
          >
            <span class="material-symbols-outlined icon-inline">tune</span>
            <span>Manage Allocations</span>
          </button>
        </article>
      </div>
    </main>

    <!-- ALLOCATION MODAL -->
    <div v-if="showAllocationModal" class="modal-backdrop" @click.self="closeAllocationModal">
      <div class="modal-card">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">Budget Allocations</h2>
            <p class="modal-subtitle">
              Assign specific Activity Design and Accomplishment Report budgets to this mandate.
            </p>
          </div>
          <button class="btn-close-modal" @click="closeAllocationModal">✕</button>
        </div>

        <div v-if="loadingAllocations" class="modal-loading">
          <div class="spinner"></div>
          <p>Loading document allocations…</p>
        </div>

        <div v-else class="modal-body">
          <!-- Planned Budget Lines Summary -->
          <div v-if="currentAllocationStat?.budget_lines?.length" class="modal-section">
            <h3 class="section-title">Planned Budget Lines</h3>
            <div class="table-scroll">
              <table class="modal-table">
                <thead>
                  <tr>
                    <th>Budget Line</th>
                    <th>Original Amount</th>
                    <th>Pending (AD)</th>
                    <th>Utilized (AR)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="bl in currentAllocationStat.budget_lines" :key="bl.id">
                    <td class="font-medium">{{ bl.label || 'Unnamed Line' }}</td>
                    <td class="mono">₱{{ formatNum(bl.amount) }}</td>
                    <td class="mono text-amber">₱{{ formatNum(bl.pending_budget) }}</td>
                    <td class="mono text-emerald">₱{{ formatNum(bl.utilized_budget) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Actual Expenditures Breakdown (Verified ARs) -->
          <div v-if="arVerifiedTotals && arVerifiedTotals.length > 0" class="modal-section">
            <h3 class="section-title">Actual Expenditures Breakdown (Verified ARs)</h3>
            <div class="table-scroll">
              <table class="modal-table">
                <thead>
                  <tr>
                    <th>Expenditure Item</th>
                    <th>Total Cost</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(tv, idx) in arVerifiedTotals" :key="idx">
                    <td>{{ tv.name }}</td>
                    <td class="mono text-emerald font-semibold">₱{{ formatNum(tv.amount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Document Items Assignment -->
          <div class="modal-section">
            <h3 class="section-title">Document Item Assignments</h3>
            <div v-if="allocationsData.length === 0" class="empty-allocations">
              No approved Activity Designs or Accomplishment Reports found for this mandate.
            </div>

            <div v-else class="docs-stack">
              <div v-for="doc in allocationsData" :key="doc.type + doc.id" class="doc-card">
                <div class="doc-header" @click="doc._expanded = !doc._expanded">
                  <div class="doc-title-row">
                    <span class="badge-type" :class="doc.type === 'AR' ? 'badge-ar' : 'badge-ad'">
                      [{{ doc.type }}]
                    </span>
                    <span class="doc-title-text">{{ doc.title || doc.control_number }}</span>
                    <button
                      v-if="doc.attachment"
                      @click.stop="openDocumentPreview(doc.attachment, doc.type)"
                      class="btn-preview-link"
                      title="Preview Document"
                    >
                      <span class="material-symbols-outlined text-sm">visibility</span>
                      <span>Preview Document</span>
                    </button>
                  </div>
                  <span class="expand-arrow">{{ doc._expanded ? '▼' : '▶' }}</span>
                </div>

                <div v-if="doc._expanded" class="doc-body">
                  <table class="modal-table">
                    <thead>
                      <tr>
                        <th>Item Name</th>
                        <th>Total Cost</th>
                        <th>Allocated To (Budget Line)</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in doc.items" :key="item.id">
                        <td>
                          <div class="font-medium">{{ item.item_name }}</div>
                          <div v-if="item.sub_item" class="item-subtext">{{ item.sub_item }}</div>
                        </td>
                        <td class="mono font-semibold">₱{{ formatNum(item.amount) }}</td>
                        <td>
                          <select
                            v-if="item.amount > 0"
                            v-model="item.gpb_budget_line_id"
                            class="line-select"
                            @change="markAllocationsDirty"
                          >
                            <option :value="null">-- Not Allocated --</option>
                            <option
                              v-for="bl in (currentAllocationStat?.budget_lines || [])"
                              :key="bl.id"
                              :value="bl.id"
                            >
                              {{ bl.label }} (₱{{ formatNum(bl.amount) }})
                            </option>
                          </select>
                          <span v-else class="text-dim text-xs">N/A</span>
                        </td>
                        <td>
                          <span v-if="item.amount <= 0" class="status-pill status-gray">No Cost</span>
                          <span v-else-if="item.gpb_budget_line_id" class="status-pill status-green">Assigned</span>
                          <span
                            v-else-if="getAllocatedElsewhere(item) >= item.amount"
                            class="status-pill status-red"
                            title="This budget item has been fully assigned to other mandates."
                          >
                            🔒 Locked
                          </span>
                          <span v-else class="status-pill status-gray">Unassigned</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="closeAllocationModal" class="btn-action btn-outline">Cancel</button>
          <button
            @click="saveAllocations"
            :disabled="savingAllocations || !allocationsDirty"
            class="btn-action btn-save-alloc"
          >
            <span class="material-symbols-outlined icon-inline" v-if="!savingAllocations">save</span>
            <span>{{ savingAllocations ? 'Saving…' : 'Save Allocations' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- PDF PREVIEW MODAL -->
    <PdfPreviewModal :isOpen="isPdfModalOpen" :fileUrl="pdfFileUrl" @close="closePdfModal" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import api from '../api';
import PdfPreviewModal from '../components/PdfPreviewModal.vue';

const route = useRoute();

// User & Role Context
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));
const userRole = computed(() => {
  return (user.value.role || user.value.user_role || '').toLowerCase();
});

const isReadOnly = computed(() => {
  return userRole.value === 'college' || userRole.value === 'twg';
});

const userRoleName = computed(() => {
  if (userRole.value === 'admin') return 'Administrator';
  if (userRole.value === 'staff') return 'GAD Staff';
  if (userRole.value === 'college' || userRole.value === 'twg') return 'College / TWG';
  return 'User';
});

const planAndBudgetPath = computed(() => {
  if (route.path.startsWith('/staff')) return '/staff/plan-and-budget';
  if (route.path.startsWith('/college')) return '/college/plan-and-budget';
  return '/admin/plan-and-budget';
});

// State for Mandates
const mandateStats = ref([]);
const mandateStatsFilter = ref('all');
const searchQuery = ref('');
const loadingStats = ref(true);

const formatNum = (val) => {
  if (val === undefined || val === null || isNaN(val)) return '0.00';
  return Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// Filtered Mandate Cards
const filteredMandateStats = computed(() => {
  let list = mandateStats.value;
  if (mandateStatsFilter.value !== 'all') {
    list = list.filter((s) => s.classification === mandateStatsFilter.value);
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter((s) => {
      const mandateText = (s.mandate || '').toLowerCase();
      const causeText = (s.cause || '').toLowerCase();
      const activityText = (s.activity || '').toLowerCase();
      return mandateText.includes(q) || causeText.includes(q) || activityText.includes(q);
    });
  }
  return list;
});

// High level totals
const totals = computed(() => {
  return mandateStats.value.reduce(
    (acc, cur) => {
      acc.budget += parseFloat(cur.budget) || 0;
      acc.utilized += parseFloat(cur.utilized_budget) || 0;
      acc.pending += parseFloat(cur.pending_budget) || 0;
      acc.remaining += parseFloat(cur.remaining_budget) || 0;
      return acc;
    },
    { budget: 0, utilized: 0, pending: 0, remaining: 0 }
  );
});

const resetFilters = () => {
  mandateStatsFilter.value = 'all';
  searchQuery.value = '';
};

// Fetch data from backend
const fetchMandateStats = async () => {
  loadingStats.value = true;
  try {
    const res = await api.get('/plan/mandate-statistics');
    if (res.data && res.data.success) {
      mandateStats.value = (res.data.data || []).map((m) => ({
        ...m,
        _showLines: true
      }));
    } else {
      mandateStats.value = [];
    }
  } catch (err) {
    console.error('Failed to fetch mandate stats:', err);
    Swal.fire({
      icon: 'error',
      title: 'Error Loading Statistics',
      text: 'Unable to retrieve GAD mandate statistics. Please ensure the backend is running.',
      toast: true,
      position: 'top-end',
      timer: 4000,
      showConfirmButton: false
    });
  } finally {
    loadingStats.value = false;
  }
};

// Allocation Modal State
const showAllocationModal = ref(false);
const loadingAllocations = ref(false);
const savingAllocations = ref(false);
const allocationsData = ref([]);
const currentAllocationStat = ref(null);
const allocationsDirty = ref(false);

const arVerifiedTotals = computed(() => {
  const map = {};
  for (const doc of allocationsData.value) {
    if (doc.type === 'AR') {
      for (const item of doc.items || []) {
        const name = item.item_name || 'Unspecified Item';
        map[name] = (map[name] || 0) + (parseFloat(item.amount) || 0);
      }
    }
  }
  return Object.entries(map).map(([name, amount]) => ({ name, amount }));
});

const openAllocationModal = async (stat) => {
  currentAllocationStat.value = stat;
  showAllocationModal.value = true;
  loadingAllocations.value = true;
  allocationsDirty.value = false;
  allocationsData.value = [];

  try {
    const gpbIds = (stat.gpb_ids || []).join(',');
    const res = await api.get(`/plan/mandate-allocations?gpb_ids=${gpbIds}`);
    if (res.data && res.data.success) {
      allocationsData.value = (res.data.data || []).map((d) => ({
        ...d,
        _expanded: true
      }));
    } else {
      Swal.fire('Notice', res.data?.message || 'No allocations data found for this mandate.', 'info');
    }
  } catch (err) {
    console.error('Error fetching allocations:', err);
    Swal.fire('Error', 'Network error while loading allocations.', 'error');
  } finally {
    loadingAllocations.value = false;
  }
};

const closeAllocationModal = () => {
  showAllocationModal.value = false;
  currentAllocationStat.value = null;
  allocationsDirty.value = false;
};

const markAllocationsDirty = () => {
  allocationsDirty.value = true;
};

const getAllocatedElsewhere = (item) => {
  if (!item.allocations || !currentAllocationStat.value) return 0;
  return item.allocations.reduce((sum, al) => {
    if (!currentAllocationStat.value.gpb_ids.includes(parseInt(al.mandate_id))) {
      return sum + (parseFloat(al.allocated_amount) || 0);
    }
    return sum;
  }, 0);
};

const saveAllocations = async () => {
  if (!currentAllocationStat.value) return;
  savingAllocations.value = true;

  const flatAllocs = [];
  for (const doc of allocationsData.value) {
    for (const item of doc.items || []) {
      const gpbLineId = item.gpb_budget_line_id;
      const val = gpbLineId ? parseFloat(item.amount) || 0 : 0;
      flatAllocs.push({
        budget_item_id: item.id,
        item_type: doc.type,
        allocated_amount: val,
        gpb_budget_line_id: gpbLineId
      });
    }
  }

  try {
    const res = await api.post('/plan/mandate-allocations', {
      gpb_ids: currentAllocationStat.value.gpb_ids,
      allocations: flatAllocs
    });
    if (res.data && res.data.success) {
      allocationsDirty.value = false;
      closeAllocationModal();
      await fetchMandateStats();
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Allocations saved successfully',
        showConfirmButton: false,
        timer: 3000
      });
    } else {
      Swal.fire('Error', res.data?.message || 'Failed to save allocations.', 'error');
    }
  } catch (err) {
    console.error('Save allocations error:', err);
    Swal.fire('Error', 'Network error while saving allocations.', 'error');
  } finally {
    savingAllocations.value = false;
  }
};

// PDF Preview Modal
const isPdfModalOpen = ref(false);
const pdfFileUrl = ref('');

const openDocumentPreview = (attachment) => {
  if (!attachment) return;
  let fileName = attachment;
  if (typeof attachment === 'string' && attachment.startsWith('[')) {
    try {
      const parsed = JSON.parse(attachment);
      if (parsed.length > 0) fileName = parsed[0];
    } catch (e) {}
  }
  const apiBase = import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '');
  pdfFileUrl.value = `${apiBase}/api/files/archived/${fileName}`;
  isPdfModalOpen.value = true;
};

const closePdfModal = () => {
  isPdfModalOpen.value = false;
  pdfFileUrl.value = '';
};

onMounted(() => {
  fetchMandateStats();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600;700&display=swap');

.budget-dist-page {
  --primary-bright: #c084fc;
  --text-primary: #ffffff;
  --text-muted: #94a3b8;
  --text-dim: #64748b;
  --emerald: #10b981;
  --amber: #f59e0b;
  --rose: #ef4444;
  --blue: #3b82f6;

  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.mono {
  font-family: 'IBM Plex Mono', monospace;
}

/* ── Top Header ────────────────────────────────────────── */
.dist-header {
  padding: 0 0.25rem;
}

.dist-header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 1.25rem;
}

.header-titles {
  max-width: 820px;
}

.breadcrumb-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  font-size: 0.8rem;
  color: #64748b;
}

.badge-role {
  background: rgba(147, 51, 234, 0.1);
  border: 1px solid rgba(147, 51, 234, 0.3);
  color: #7e22ce;
  padding: 4px 10px;
  border-radius: 999px;
  font-weight: 700;
  font-size: 11.5px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.breadcrumb-sep {
  opacity: 0.6;
}

.breadcrumb-text {
  font-weight: 500;
  color: #64748b;
}

.page-title {
  font-size: 1.85rem;
  font-weight: 900;
  color: #0f172a;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.025em;
}

.page-subtitle {
  font-size: 1rem;
  color: #475569;
  line-height: 1.5;
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
  text-decoration: none;
}

.btn-refresh {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #1e293b;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
.btn-refresh:hover {
  background: #f8fafc;
  border-color: #9333ea;
  color: #7e22ce;
  box-shadow: 0 2px 6px rgba(147, 51, 234, 0.15);
}

.btn-primary-plan {
  background: linear-gradient(135deg, #7b2cbf, #6100a4);
  color: #ffffff;
  box-shadow: 0 2px 6px rgba(123, 44, 191, 0.25);
}
.btn-primary-plan:hover {
  filter: brightness(1.1);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(123, 44, 191, 0.35);
}

.spin-icon {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ── KPI Grid ────────────────────────────────────────── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 1rem;
}

.kpi-card {
  display: flex;
  align-items: center;
  gap: 14px;
  background: linear-gradient(135deg, #13111f 0%, #1e1b2e 100%);
  border: 1px solid rgba(192, 132, 252, 0.15);
  border-radius: 1rem;
  padding: 1.25rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.25);
  transition: all 0.3s ease;
}
.kpi-card:hover {
  transform: translateY(-2px);
  border-color: rgba(192, 132, 252, 0.35);
  box-shadow: 0 14px 20px -3px rgba(0, 0, 0, 0.35);
}

.kpi-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.kpi-icon-wrap.indigo { background: rgba(99, 102, 241, 0.15); color: #818cf8; }
.kpi-icon-wrap.purple { background: rgba(168, 85, 247, 0.15); color: #c084fc; }
.kpi-icon-wrap.green  { background: rgba(16, 185, 129, 0.15); color: #34d399; }
.kpi-icon-wrap.amber  { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.kpi-icon-wrap.blue   { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.kpi-icon-wrap.red    { background: rgba(239, 68, 68, 0.15);  color: #f87171; }

.kpi-info {
  display: flex;
  flex-direction: column;
}
.kpi-label {
  font-size: 0.78rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 700;
}
.kpi-value {
  font-size: 1.35rem;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.2;
}
.kpi-subtotal {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 400;
}

.text-emerald { color: var(--emerald); }
.text-amber   { color: var(--amber); }
.text-blue    { color: var(--blue); }
.text-rose    { color: var(--rose); }

/* ── Toolbar & Filters ───────────────────────────────── */
.toolbar-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  background: #141120;
  border: 1px solid rgba(192, 132, 252, 0.15);
  border-radius: 12px;
  padding: 12px 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.search-wrap {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1;
  min-width: 280px;
  max-width: 520px;
}
.search-icon {
  position: absolute;
  left: 12px;
  color: #94a3b8;
  font-size: 1.2rem;
  pointer-events: none;
}
.search-input {
  width: 100%;
  background: #1e1b2e;
  border: 1px solid rgba(192, 132, 252, 0.2);
  border-radius: 8px;
  padding: 9px 36px 9px 38px;
  color: #ffffff;
  font-size: 0.9rem;
  outline: none;
  transition: all 0.2s;
}
.search-input:focus {
  border-color: var(--primary-bright);
  box-shadow: 0 0 0 2px rgba(192, 132, 252, 0.2);
}
.clear-search-btn {
  position: absolute;
  right: 10px;
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
}

.filters-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}
.filter-label {
  font-size: 0.85rem;
  color: #cbd5e1;
}
.filter-select {
  background: #1e1b2e;
  border: 1px solid rgba(192, 132, 252, 0.2);
  color: #ffffff;
  padding: 8px 14px;
  border-radius: 8px;
  outline: none;
  font-size: 0.88rem;
  cursor: pointer;
}
.filter-select option {
  background: #1e293b;
  color: #ffffff;
}

/* ── Content States ──────────────────────────────────── */
.state-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: var(--text-muted);
  text-align: center;
}
.state-container.empty-box {
  background: #141120;
  border: 1px dashed rgba(192, 132, 252, 0.3);
  border-radius: 16px;
}
.empty-icon {
  font-size: 2.5rem;
  margin-bottom: 12px;
}
.empty-title {
  color: #ffffff;
  margin: 0 0 8px 0;
  font-size: 1.2rem;
}
.empty-desc {
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0;
  max-width: 480px;
}
.spinner {
  width: 36px;
  height: 36px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top-color: var(--primary-bright);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 14px;
}

/* ── Mandates Cards Grid ─────────────────────────────── */
.mandates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 1.25rem;
}

.mandate-card {
  background: linear-gradient(135deg, #13111f 0%, #1e1b2e 100%);
  border: 1px solid rgba(192, 132, 252, 0.15);
  border-radius: 14px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.25);
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.mandate-card:hover {
  transform: translateY(-3px);
  border-color: rgba(192, 132, 252, 0.35);
  box-shadow: 0 14px 20px -3px rgba(0, 0, 0, 0.35);
}

.card-content-stack {
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}

.callout-block {
  background: rgba(255, 255, 255, 0.03);
  padding: 12px 14px;
  border-radius: 8px;
  border-left: 3px solid #6366f1;
}
.callout-mandate  { border-left-color: #6366f1; }
.callout-cause    { border-left-color: #8b5cf6; }
.callout-activity { border-left-color: #ec4899; }

.callout-tag {
  font-size: 0.68rem;
  color: var(--text-muted);
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.callout-text {
  font-size: 0.9rem;
  line-height: 1.45;
  color: #ffffff;
}
.callout-text.text-secondary {
  color: #cbd5e1;
  font-size: 0.85rem;
}

/* ── Card Stats Box ──────────────────────────────────── */
.card-stats-box {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 10px;
  padding: 16px;
  border: 1px solid rgba(255, 255, 255, 0.04);
}

.counters-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.counter-box {
  text-align: center;
  padding: 8px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
}
.counter-label {
  display: block;
  font-size: 0.68rem;
  color: var(--text-muted);
  text-transform: uppercase;
  margin-bottom: 2px;
}
.counter-value {
  font-size: 1.15rem;
  color: #ffffff;
  font-weight: 700;
}

.finance-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 0.86rem;
}
.finance-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.finance-label {
  color: #cbd5e1;
  font-weight: 500;
}
.finance-val {
  font-size: 0.95rem;
}
.finance-remaining {
  padding-top: 8px;
  border-top: 1px dashed rgba(255, 255, 255, 0.1);
  margin-top: 2px;
}
.remaining-label {
  text-transform: uppercase;
  font-size: 0.75rem;
  font-weight: 700;
  color: #ffffff;
}

/* ── Lines Breakdown ─────────────────────────────────── */
.lines-breakdown {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.lines-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  user-select: none;
  margin-bottom: 10px;
}
.lines-title {
  font-size: 0.72rem;
  color: var(--text-muted);
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.5px;
}
.lines-toggle {
  font-size: 0.75rem;
  color: var(--primary-bright);
}

.lines-stack {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.line-card {
  background: rgba(0, 0, 0, 0.25);
  border-radius: 6px;
  padding: 10px;
  border: 1px solid rgba(255, 255, 255, 0.04);
  font-size: 0.8rem;
}
.line-label {
  color: #ffffff;
  font-weight: 600;
  margin-bottom: 6px;
}
.line-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.line-detail-row {
  display: flex;
  justify-content: space-between;
  color: #cbd5e1;
}

/* ── Manage Allocations Button ───────────────────────── */
.btn-manage-allocations {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 14px;
  background: rgba(59, 130, 246, 0.15);
  border: 1px solid rgba(59, 130, 246, 0.4);
  color: #93c5fd;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-manage-allocations:hover {
  background: rgba(59, 130, 246, 0.25);
  color: #ffffff;
  border-color: rgba(59, 130, 246, 0.6);
}
.icon-inline {
  font-size: 1.1rem;
}

/* ── Modal Styling ───────────────────────────────────── */
.modal-backdrop {
  z-index: 1000;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-card {
  width: 100%;
  max-width: 860px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  background: #1e293b;
  border-radius: 14px;
  border: 1px solid var(--border-subtle);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-subtle);
}
.modal-title {
  margin: 0 0 4px 0;
  font-size: 1.35rem;
  color: #ffffff;
}
.modal-subtitle {
  margin: 0;
  font-size: 0.88rem;
  color: var(--text-muted);
}
.btn-close-modal {
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 1.25rem;
  cursor: pointer;
}
.btn-close-modal:hover {
  color: #ffffff;
}

.modal-body {
  padding: 20px 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.modal-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.section-title {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #ffffff;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 8px;
}

.table-scroll {
  overflow-x: auto;
  border: 1px solid var(--border-subtle);
  border-radius: 8px;
}
.modal-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
}
.modal-table thead tr {
  background: rgba(0, 0, 0, 0.25);
  text-align: left;
  color: var(--text-muted);
}
.modal-table th,
.modal-table td {
  padding: 10px 12px;
}
.modal-table tbody tr {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.empty-allocations {
  padding: 24px;
  text-align: center;
  color: var(--text-muted);
  background: rgba(0, 0, 0, 0.15);
  border-radius: 8px;
}

.docs-stack {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.doc-card {
  border: 1px solid var(--border-subtle);
  border-radius: 8px;
  overflow: hidden;
  background: rgba(0, 0, 0, 0.15);
}
.doc-header {
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  background: rgba(255, 255, 255, 0.02);
}
.doc-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.badge-type {
  font-weight: 700;
  font-size: 0.8rem;
}
.badge-ar { color: var(--emerald); }
.badge-ad { color: var(--amber); }

.doc-title-text {
  font-weight: 600;
  color: #ffffff;
}

.btn-preview-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: transparent;
  border: none;
  color: #60a5fa;
  font-size: 0.8rem;
  cursor: pointer;
  text-decoration: underline;
}
.expand-arrow {
  color: var(--text-muted);
}
.doc-body {
  padding: 12px 16px;
  background: rgba(0, 0, 0, 0.25);
}

.item-subtext {
  font-size: 0.75rem;
  color: var(--text-dim);
}

.line-select {
  padding: 6px 10px;
  background: #0f172a;
  border: 1px solid var(--border-subtle);
  color: #ffffff;
  border-radius: 6px;
  font-size: 0.85rem;
  outline: none;
  max-width: 220px;
}

.status-pill {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}
.status-green { background: rgba(16, 185, 129, 0.15); color: #34d399; }
.status-red   { background: rgba(239, 68, 68, 0.15);  color: #f87171; }
.status-gray  { background: rgba(255, 255, 255, 0.05); color: var(--text-muted); }

.modal-footer {
  padding: 16px 24px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  border-top: 1px solid var(--border-subtle);
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--border-subtle);
  color: #ffffff;
}
.btn-outline:hover {
  background: rgba(255, 255, 255, 0.05);
}

.btn-save-alloc {
  background: #3b82f6;
  color: #ffffff;
}
.btn-save-alloc:hover:not(:disabled) {
  background: #2563eb;
}
.btn-save-alloc:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.modal-loading {
  padding: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  color: var(--text-muted);
}
</style>

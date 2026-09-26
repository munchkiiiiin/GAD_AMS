<template>
  <main class="main-content">
    <div class="content-wrapper">
      
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-main-flex">
          <div>
            <div class="header-badge">
              <span class="material-symbols-outlined text-[15px]">payments</span>
              <span>Financial Monitoring & Audit Trail</span>
            </div>
            <h1 class="page-title">Budget Utilization Monitoring</h1>
            <p class="page-subtitle">Track mandate allocations, pending approved commitments (ADs), actual disbursed expenditures (ARs), and comprehensive document audit trails.</p>
          </div>
          <div class="header-actions">
            <button class="btn-refresh" @click="fetchBudgetData" :disabled="loading" title="Refresh live data">
              <span class="material-symbols-outlined" :class="{ 'spin': loading }">sync</span>
              <span>Refresh</span>
            </button>
            <button class="btn-toggle-all" @click="toggleExpandAll" title="Expand or collapse all audit trails">
              <span class="material-symbols-outlined">{{ allExpanded ? 'unfold_less' : 'unfold_more' }}</span>
              <span>{{ allExpanded ? 'Collapse All' : 'Expand All' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- KPI Summary Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon-wrapper blue">
            <span class="material-symbols-outlined">account_balance</span>
          </div>
          <div class="stat-content">
            <h3 class="stat-value mono">₱{{ formatNum(totalGadBudget) }}</h3>
            <p class="stat-label">Total GAD Budget</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrapper green">
            <span class="material-symbols-outlined">trending_up</span>
          </div>
          <div class="stat-content">
            <h3 class="stat-value mono">₱{{ formatNum(actualCost) }}</h3>
            <p class="stat-label">Actual Cost (Disbursed)</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrapper amber">
            <span class="material-symbols-outlined">hourglass_empty</span>
          </div>
          <div class="stat-content">
            <h3 class="stat-value mono">₱{{ formatNum(proposedBudget) }}</h3>
            <p class="stat-label">Proposed Budget (Committed ADs)</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrapper purple">
            <span class="material-symbols-outlined">pie_chart</span>
          </div>
          <div class="stat-content">
            <h3 class="stat-value mono">{{ overallUtilizationRate }}%</h3>
            <p class="stat-label">% Utilization Rate</p>
          </div>
        </div>
      </div>

      <!-- Search & Filters Toolbar -->
      <div class="filter-toolbar">
        <div class="search-box">
          <span class="material-symbols-outlined search-icon">search</span>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search mandate, activity, or GPB code..." 
            class="search-input"
          />
          <button v-if="searchQuery" class="clear-search" @click="searchQuery = ''">
            <span class="material-symbols-outlined text-[16px]">close</span>
          </button>
        </div>

        <div class="filter-group">
          <!-- Classification Filter -->
          <div class="select-wrapper">
            <select v-model="selectedClassification" class="filter-select">
              <option value="all">All Classifications</option>
              <option value="client">Client-Focused (CF)</option>
              <option value="org">Organization-Focused (OF)</option>
              <option value="attributed">Attributed Program (AP)</option>
            </select>
          </div>

          <!-- Office Filter -->
          <div class="select-wrapper" v-if="uniqueOffices.length > 0">
            <select v-model="selectedOffice" class="filter-select">
              <option value="all">All Responsible Units</option>
              <option v-for="office in uniqueOffices" :key="office" :value="office">{{ office }}</option>
            </select>
          </div>

          <!-- Health / Status Filter -->
          <div class="select-wrapper">
            <select v-model="selectedHealth" class="filter-select">
              <option value="all">All Statuses</option>
              <option value="healthy">Healthy (> ₱50k remaining)</option>
              <option value="warning">Low Balance (< ₱20k remaining)</option>
              <option value="critical">Exhausted (₱0.00 remaining)</option>
              <option value="has_ads">Has Pending ADs</option>
              <option value="has_ars">Has Verified ARs</option>
            </select>
          </div>

          <!-- Reset Filter Button -->
          <button 
            v-if="searchQuery || selectedClassification !== 'all' || selectedOffice !== 'all' || selectedHealth !== 'all'"
            @click="resetFilters" 
            class="btn-reset-filters"
            title="Reset all filters"
          >
            <span class="material-symbols-outlined text-[16px]">filter_alt_off</span>
            <span>Reset</span>
          </button>
        </div>

        <div class="results-count">
          Showing <b class="text-purple-300">{{ filteredRows.length }}</b> of {{ budgetRows.length }} Mandates
        </div>
      </div>

      <!-- Main Data Table Container -->
      <div class="table-container">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr class="table-header-row">
                <th class="table-header-cell col-expand"></th>
                <th class="table-header-cell col-number">#</th>
                <th class="table-header-cell col-unit text-left">Gender Issue / Mandate</th>
                <th class="table-header-cell col-activity text-left">GAD Activity</th>
                <th class="table-header-cell col-allocated">Budget</th>
                <th class="table-header-cell col-pending">Pending (ADs)</th>
                <th class="table-header-cell col-remaining">Remaining</th>
                <th class="table-header-cell col-actual-cost">Actual Cost (ARs)</th>
                <th class="table-header-cell col-actions text-center">Audit Trail</th>
              </tr>
            </thead>
            <tbody class="table-body">
              <tr v-if="loading && budgetRows.length === 0">
                <td colspan="9" class="empty-state">
                  <div class="flex flex-col items-center justify-center gap-2 py-8">
                    <span class="material-symbols-outlined text-4xl spin text-purple-400">sync</span>
                    <span class="text-slate-400 font-medium">Loading budget utilization & audit trail...</span>
                  </div>
                </td>
              </tr>

              <tr v-else-if="filteredRows.length === 0">
                <td colspan="9" class="empty-state">
                  <div class="flex flex-col items-center justify-center gap-2 py-8">
                    <span class="material-symbols-outlined text-4xl text-slate-500">search_off</span>
                    <span class="text-slate-300 font-semibold text-lg">No budget records found</span>
                    <span class="text-slate-400 text-sm">Try adjusting your search keywords or filter dropdowns.</span>
                    <button @click="resetFilters" class="btn-clear-empty mt-2">Clear Filters</button>
                  </div>
                </td>
              </tr>

              <!-- Data Rows -->
              <template v-else v-for="(row, index) in filteredRows" :key="row.id">
                <tr 
                  class="table-row cursor-pointer" 
                  :class="{ 'expanded-row-parent': isExpanded(row.id) }"
                  @click="toggleRow(row.id)"
                >
                  <!-- Expand Icon -->
                  <td class="table-cell cell-expand text-center">
                    <span class="expand-icon material-symbols-outlined" :class="{ 'rotated': isExpanded(row.id) }">
                      keyboard_arrow_right
                    </span>
                  </td>

                  <!-- Number -->
                  <td class="table-cell cell-number">
                    {{ index + 1 }}
                  </td>
                  
                  <!-- Mandate & Metadata -->
                  <td class="table-cell cell-unit">
                    <div class="unit-badges mb-1 flex items-center gap-1.5 flex-wrap">
                      <span class="classification-pill" :class="getClassificationClass(row.section)">
                        {{ getClassificationLabel(row.section) }}
                      </span>
                      <span class="unit-code">{{ row.unit_code }}</span>
                      <span v-if="row.responsible" class="office-pill" :title="'Responsible Unit: ' + row.responsible">
                        {{ row.responsible }}
                      </span>
                    </div>
                    <div class="unit-name">{{ row.mandate }}</div>
                  </td>

                  <!-- Activity -->
                  <td class="table-cell cell-activity">
                    <div class="activity-text">{{ row.activity || 'N/A' }}</div>
                  </td>

                  <!-- Allocated Budget -->
                  <td class="table-cell cell-allocated text-right">
                    <div class="cell-value mono font-semibold">₱{{ formatNum(row.allocated) }}</div>
                  </td>

                  <!-- Pending (ADs) -->
                  <td class="table-cell cell-pending text-right">
                    <div class="cell-value mono" :class="{ 'text-amber-400 font-semibold': row.pending_approved > 0 }">
                      ₱{{ formatNum(row.pending_approved) }}
                    </div>
                    <div v-if="row.pending_ads && row.pending_ads.length > 0" class="sub-badge text-amber-300/80">
                      {{ row.pending_ads.length }} approved {{ row.pending_ads.length === 1 ? 'AD' : 'ADs' }}
                    </div>
                  </td>

                  <!-- Remaining -->
                  <td class="table-cell cell-remaining text-right">
                    <div class="cell-value mono font-bold" :class="getRemainingClass(row.remaining)">
                      ₱{{ formatNum(row.remaining) }}
                    </div>
                    <div class="sub-badge" :class="getRemainingClass(row.remaining)">
                      {{ getHealthLabel(row.remaining) }}
                    </div>
                  </td>

                  <!-- Actual Cost / Disbursed -->
                  <td class="table-cell cell-actual-cost text-right">
                    <div class="cell-value mono font-semibold" :class="{ 'text-emerald-400': row.actual_cost > 0 }">
                      ₱{{ formatNum(row.actual_cost) }}
                    </div>
                    <div v-if="row.completed_ars && row.completed_ars.length > 0" class="sub-badge text-emerald-300/80">
                      {{ row.completed_ars.length }} verified {{ row.completed_ars.length === 1 ? 'AR' : 'ARs' }}
                    </div>
                  </td>

                  <!-- Actions / Audit Trail Toggle -->
                  <td class="table-cell cell-actions text-center" @click.stop="toggleRow(row.id)">
                    <button class="btn-audit-toggle" :class="{ 'active': isExpanded(row.id) }">
                      <span class="material-symbols-outlined text-[16px]">
                        {{ isExpanded(row.id) ? 'folder_open' : 'folder' }}
                      </span>
                      <span>{{ isExpanded(row.id) ? 'Hide' : 'Audit Trail' }}</span>
                      <span v-if="(row.total_docs_count || 0) > 0" class="audit-counter">
                        {{ row.total_docs_count }}
                      </span>
                    </button>
                  </td>
                </tr>

                <!-- Collapsible Expenditure Audit Trail Drawer -->
                <tr v-if="isExpanded(row.id)" class="audit-drawer-row">
                  <td colspan="9" class="audit-drawer-cell">
                    <div class="audit-drawer-container">
                      
                      <!-- Drawer Header Strip -->
                      <div class="drawer-header-strip">
                        <div class="drawer-info">
                          <span class="drawer-chip-code">{{ row.unit_code }}</span>
                          <span class="drawer-mandate-title">{{ row.activity || row.mandate }}</span>
                        </div>
                        <div class="drawer-financial-summary mono">
                          <span class="summary-item">
                            <span class="text-slate-400">Allocated:</span>
                            <span class="text-purple-300 font-bold">₱{{ formatNum(row.allocated) }}</span>
                          </span>
                          <span class="summary-sep">|</span>
                          <span class="summary-item">
                            <span class="text-slate-400">Disbursed (AR):</span>
                            <span class="text-emerald-400 font-bold">₱{{ formatNum(row.actual_cost) }}</span>
                          </span>
                          <span class="summary-sep">|</span>
                          <span class="summary-item">
                            <span class="text-slate-400">Committed (AD):</span>
                            <span class="text-amber-400 font-bold">₱{{ formatNum(row.pending_approved) }}</span>
                          </span>
                          <span class="summary-sep">|</span>
                          <span class="summary-item">
                            <span class="text-slate-400">Remaining Balance:</span>
                            <span class="font-bold" :class="getRemainingClass(row.remaining)">₱{{ formatNum(row.remaining) }}</span>
                          </span>
                        </div>
                      </div>

                      <!-- Sub-Panels Grid (2 Columns: ADs vs ARs) -->
                      <div class="audit-panels-grid">
                        
                        <!-- Panel 1: Committed Pending Activity Designs -->
                        <div class="audit-panel ad-panel">
                          <div class="panel-header">
                            <div class="flex items-center gap-2">
                              <span class="material-symbols-outlined text-amber-400 text-[18px]">pending_actions</span>
                              <h4 class="panel-title">Approved Activity Designs (Committed Pending)</h4>
                            </div>
                            <span class="panel-badge-count amber">
                              {{ (row.pending_ads || []).length }} Pending
                            </span>
                          </div>

                          <div v-if="!row.pending_ads || row.pending_ads.length === 0" class="panel-empty">
                            <span class="material-symbols-outlined text-slate-500 text-3xl">task_alt</span>
                            <p>No pending Activity Designs awaiting accomplishment for this mandate.</p>
                          </div>

                          <div v-else class="doc-list">
                            <div v-for="ad in row.pending_ads" :key="'ad-' + ad.id" class="doc-card">
                              <div class="doc-card-top">
                                <span class="doc-control-badge text-amber-300 bg-amber-900/30 border border-amber-500/30">
                                  {{ ad.control_number || ('AD-' + ad.id) }}
                                </span>
                                <span class="doc-amount mono font-bold text-amber-400">
                                  ₱{{ formatNum(ad.amount) }}
                                </span>
                              </div>
                              <div class="doc-title">{{ ad.title }}</div>
                              <div class="doc-footer">
                                <div class="doc-meta">
                                  <span v-if="ad.college" class="meta-item">
                                    <span class="material-symbols-outlined text-[13px]">domain</span>
                                    <span>{{ ad.college }}</span>
                                  </span>
                                  <span v-if="ad.created_at" class="meta-item">
                                    <span class="material-symbols-outlined text-[13px]">calendar_today</span>
                                    <span>{{ formatDate(ad.created_at) }}</span>
                                  </span>
                                </div>
                                <button 
                                  v-if="ad.attachment" 
                                  @click.stop="openDocumentPreview(ad.attachment)"
                                  class="btn-preview-doc"
                                  title="Preview Document"
                                >
                                  <span class="material-symbols-outlined text-[14px]">visibility</span>
                                  <span>View Proposal</span>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Panel 2: Disbursed Actual Accomplishment Reports -->
                        <div class="audit-panel ar-panel">
                          <div class="panel-header">
                            <div class="flex items-center gap-2">
                              <span class="material-symbols-outlined text-emerald-400 text-[18px]">verified</span>
                              <h4 class="panel-title">Completed Accomplishment Reports (Disbursed Actuals)</h4>
                            </div>
                            <span class="panel-badge-count green">
                              {{ (row.completed_ars || []).length }} Disbursed
                            </span>
                          </div>

                          <div v-if="!row.completed_ars || row.completed_ars.length === 0" class="panel-empty">
                            <span class="material-symbols-outlined text-slate-500 text-3xl">hourglass_empty</span>
                            <p>No verified Accomplishment Reports recorded yet for this mandate.</p>
                          </div>

                          <div v-else class="doc-list">
                            <div v-for="ar in row.completed_ars" :key="'ar-' + ar.id" class="doc-card">
                              <div class="doc-card-top">
                                <span class="doc-control-badge text-emerald-300 bg-emerald-900/30 border border-emerald-500/30">
                                  {{ ar.control_number || ('AR-' + ar.id) }}
                                </span>
                                <span class="doc-amount mono font-bold text-emerald-400">
                                  ₱{{ formatNum(ar.amount) }}
                                </span>
                              </div>
                              <div class="doc-title">{{ ar.title }}</div>
                              <div class="doc-footer">
                                <div class="doc-meta">
                                  <span v-if="ar.college" class="meta-item">
                                    <span class="material-symbols-outlined text-[13px]">domain</span>
                                    <span>{{ ar.college }}</span>
                                  </span>
                                  <span v-if="ar.created_at" class="meta-item">
                                    <span class="material-symbols-outlined text-[13px]">calendar_today</span>
                                    <span>{{ formatDate(ar.created_at) }}</span>
                                  </span>
                                </div>
                                <button 
                                  v-if="ar.attachment" 
                                  @click.stop="openDocumentPreview(ar.attachment)"
                                  class="btn-preview-doc"
                                  title="Preview Document"
                                >
                                  <span class="material-symbols-outlined text-[14px]">visibility</span>
                                  <span>View Report</span>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- PDF Document Preview Modal -->
    <PdfPreviewModal 
      :isOpen="isPdfModalOpen" 
      :pdfUrl="pdfFileUrl" 
      @close="closePdfModal" 
    />
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api';
import PdfPreviewModal from '../../components/PdfPreviewModal.vue';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

const budgetRows = ref([]);
const totalGadBudget = ref(0);
const actualCost = ref(0);
const proposedBudget = ref(0);
const overallUtilizationRate = ref('0.0');
const loading = ref(false);

// Filter & Search states
const searchQuery = ref('');
const selectedClassification = ref('all');
const selectedOffice = ref('all');
const selectedHealth = ref('all');

// Expandable drawer state
const expandedRows = ref([]);

// Document Preview Modal states
const isPdfModalOpen = ref(false);
const pdfFileUrl = ref('');

const formatNum = (val) => {
  if (val === undefined || val === null) return '0.00';
  return Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  } catch {
    return dateStr;
  }
};

const updateRowCalculations = (row) => {
  row.remaining = Math.max(0, (row.allocated || 0) - (row.utilized || 0) - (row.pending_approved || 0));
};

const getRemainingClass = (remaining) => {
  if (remaining <= 0) return 'remaining-critical';
  if (remaining < 20000) return 'remaining-warning';
  return 'remaining-healthy';
};

const getHealthLabel = (remaining) => {
  if (remaining <= 0) return 'Exhausted';
  if (remaining < 20000) return 'Low Balance';
  return 'Healthy';
};

const getClassificationLabel = (section) => {
  const s = (section || '').toLowerCase();
  if (s === 'client') return 'Client-Focused';
  if (s === 'org') return 'Org-Focused';
  if (s === 'attributed') return 'Attributed';
  return 'General';
};

const getClassificationClass = (section) => {
  const s = (section || '').toLowerCase();
  if (s === 'client') return 'pill-client';
  if (s === 'org') return 'pill-org';
  if (s === 'attributed') return 'pill-attributed';
  return 'pill-general';
};

// Unique Responsible Offices for filter dropdown
const uniqueOffices = computed(() => {
  const set = new Set();
  budgetRows.value.forEach(r => {
    if (r.responsible && typeof r.responsible === 'string') {
      const parts = r.responsible.split(',').map(p => p.trim());
      parts.forEach(p => { if (p) set.add(p); });
    }
  });
  return Array.from(set).sort();
});

// Filtered data rows
const filteredRows = computed(() => {
  return budgetRows.value.filter(row => {
    // Keyword search
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase().trim();
      const matchMandate = (row.mandate || '').toLowerCase().includes(q);
      const matchActivity = (row.activity || '').toLowerCase().includes(q);
      const matchCode = (row.unit_code || '').toLowerCase().includes(q);
      const matchResp = (row.responsible || '').toLowerCase().includes(q);
      if (!matchMandate && !matchActivity && !matchCode && !matchResp) {
        return false;
      }
    }

    // Classification filter
    if (selectedClassification.value !== 'all') {
      if ((row.section || '').toLowerCase() !== selectedClassification.value.toLowerCase()) {
        return false;
      }
    }

    // Office filter
    if (selectedOffice.value !== 'all') {
      const resp = (row.responsible || '').toLowerCase();
      if (!resp.includes(selectedOffice.value.toLowerCase())) {
        return false;
      }
    }

    // Health filter
    if (selectedHealth.value !== 'all') {
      const rem = Number(row.remaining) || 0;
      if (selectedHealth.value === 'healthy' && rem < 50000) return false;
      if (selectedHealth.value === 'warning' && (rem >= 20000 || rem <= 0)) return false;
      if (selectedHealth.value === 'critical' && rem > 0) return false;
      if (selectedHealth.value === 'has_ads' && (!row.pending_ads || row.pending_ads.length === 0)) return false;
      if (selectedHealth.value === 'has_ars' && (!row.completed_ars || row.completed_ars.length === 0)) return false;
    }

    return true;
  });
});

const resetFilters = () => {
  searchQuery.value = '';
  selectedClassification.value = 'all';
  selectedOffice.value = 'all';
  selectedHealth.value = 'all';
};

// Expand / Collapse Drawer logic
const isExpanded = (id) => expandedRows.value.includes(id);

const toggleRow = (id) => {
  const index = expandedRows.value.indexOf(id);
  if (index > -1) {
    expandedRows.value.splice(index, 1);
  } else {
    expandedRows.value.push(id);
  }
};

const allExpanded = computed(() => {
  return filteredRows.value.length > 0 && expandedRows.value.length >= filteredRows.value.length;
});

const toggleExpandAll = () => {
  if (allExpanded.value) {
    expandedRows.value = [];
  } else {
    expandedRows.value = filteredRows.value.map(r => r.id);
  }
};

// PDF preview modal logic
const openDocumentPreview = (attachment) => {
  if (!attachment) return;
  let fileName = attachment;
  if (typeof attachment === 'string' && attachment.startsWith('[')) {
    try {
      const parsed = JSON.parse(attachment);
      if (parsed.length > 0) fileName = parsed[0];
    } catch(e) {}
  }
  const baseUrl = (import.meta.env.VITE_API_BASE_URL || '').replace(/\/api\/?$/, '');
  pdfFileUrl.value = `${baseUrl}/api/files/archived/${fileName}`;
  isPdfModalOpen.value = true;
};

const closePdfModal = () => {
  isPdfModalOpen.value = false;
  pdfFileUrl.value = '';
};

// Data Fetching
const fetchBudgetData = async () => {
  loading.value = true;
  try {
    const [monitoringRes, summaryRes] = await Promise.all([
      api.get('staff/budget-monitoring'),
      api.get('budget/summary')
    ]);

    if (monitoringRes.data) {
      budgetRows.value = monitoringRes.data;
      budgetRows.value.forEach(row => {
        updateRowCalculations(row);
      });
    }

    if (summaryRes.data && summaryRes.data.success) {
      const b = summaryRes.data.data;
      totalGadBudget.value = b.total_budget || 0;
      actualCost.value = b.total_utilized || 0;
      proposedBudget.value = b.total_pending_approved || 0;
      overallUtilizationRate.value = Number(b.utilization_rate || 0).toFixed(1);
    }
  } catch (err) {
    console.error('Error fetching budget data:', err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  const role = (user.value.role || user.value.user_role || '').toLowerCase();
  if (!user.value.id || !['gad_staff', 'staff', 'admin', 'director'].some(r => role.includes(r))) { 
    router.push('/login'); 
  } else { 
    fetchBudgetData(); 
  }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600;700&display=swap');

.mono {
  font-family: 'IBM Plex Mono', monospace;
}

.main-content {
  padding: 0;
  flex-grow: 1;
  color: #cbd5e1;
}

.content-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Page Header */
.page-header {
  padding: 0 0.25rem;
}

.header-main-flex {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.header-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(147, 51, 234, 0.1);
  border: 1px solid rgba(147, 51, 234, 0.3);
  color: #7e22ce;
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
}

.page-title {
  font-size: 1.85rem;
  font-weight: 900;
  letter-spacing: -0.025em;
  color: #0f172a; /* Deep crisp slate for light page background */
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  font-size: 1rem;
  color: #475569; /* Slate 600 for high legibility */
  margin: 0;
  line-height: 1.5;
  max-width: 820px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-refresh, .btn-toggle-all {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #1e293b;
  padding: 8px 15px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  transition: all 0.2s ease;
}

.btn-refresh:hover, .btn-toggle-all:hover {
  background: #f8fafc;
  border-color: #9333ea;
  color: #7e22ce;
  box-shadow: 0 2px 6px rgba(147, 51, 234, 0.15);
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}

/* Stats KPI Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  padding: 1.25rem;
  border-radius: 1rem;
  border: 1px solid rgba(192, 132, 252, 0.15);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.25);
  background: linear-gradient(135deg, #13111f 0%, #1e1b2e 100%);
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-2px);
  border-color: rgba(192, 132, 252, 0.35);
  box-shadow: 0 14px 20px -3px rgba(0, 0, 0, 0.35);
}

.stat-icon-wrapper {
  width: 42px;
  height: 42px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-bottom: 0.75rem;
}

.stat-icon-wrapper.blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.stat-icon-wrapper.green { background: rgba(16, 185, 129, 0.15); color: #34d399; }
.stat-icon-wrapper.amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.stat-icon-wrapper.purple { background: rgba(168, 85, 247, 0.15); color: #c084fc; }

.stat-value {
  font-size: 1.35rem;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.2;
  margin: 0;
}

.stat-label {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8;
  margin-top: 0.35rem;
}

/* Filter Toolbar */
.filter-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  background: #141120;
  border: 1px solid rgba(192, 132, 252, 0.15);
  border-radius: 12px;
  padding: 12px 16px;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1;
  min-width: 260px;
}

.search-icon {
  position: absolute;
  left: 12px;
  color: #94a3b8;
  font-size: 20px;
  pointer-events: none;
}

.search-input {
  width: 100%;
  background: #1e1b2e;
  border: 1px solid rgba(192, 132, 252, 0.2);
  border-radius: 8px;
  color: #ffffff;
  font-size: 13px;
  padding: 8px 36px 8px 38px;
  outline: none;
  transition: all 0.2s;
}

.search-input:focus {
  border-color: #c084fc;
  box-shadow: 0 0 0 2px rgba(192, 132, 252, 0.2);
}

.clear-search {
  position: absolute;
  right: 10px;
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.clear-search:hover { color: #ffffff; }

.filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.filter-select {
  background: #1e1b2e;
  border: 1px solid rgba(192, 132, 252, 0.2);
  border-radius: 8px;
  color: #e2e8f0;
  font-size: 13px;
  padding: 8px 12px;
  outline: none;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-select:focus {
  border-color: #c084fc;
}

.btn-reset-filters {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
  padding: 7px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.btn-reset-filters:hover {
  background: rgba(239, 68, 68, 0.25);
  color: #ffffff;
}

.results-count {
  font-size: 12px;
  color: #94a3b8;
  white-space: nowrap;
}

/* Data Table */
.table-container {
  border-radius: 14px;
  border: 1px solid rgba(192, 132, 252, 0.15);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  background: #141120;
}

.table-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1100px;
}

.col-expand { width: 44px; text-align: center; }
.col-number { width: 50px; text-align: center; }
.col-unit { width: 280px; }
.col-activity { width: 260px; }
.col-allocated { width: 140px; text-align: right; }
.col-pending { width: 140px; text-align: right; }
.col-remaining { width: 140px; text-align: right; }
.col-actual-cost { width: 150px; text-align: right; }
.col-actions { width: 110px; text-align: center; }

.table-header-row {
  border-bottom: 1px solid rgba(192, 132, 252, 0.15);
  background: #1a1628;
}

.table-header-cell {
  padding: 12px 14px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #c084fc;
}

.table-row {
  border-bottom: 1px solid rgba(192, 132, 252, 0.08);
  transition: background 0.15s ease;
}

.table-row:hover {
  background: rgba(192, 132, 252, 0.05);
}

.expanded-row-parent {
  background: rgba(192, 132, 252, 0.08) !important;
  border-bottom: 1px solid rgba(192, 132, 252, 0.25);
}

.table-cell {
  padding: 12px 14px;
  vertical-align: middle;
  font-size: 13px;
}

.expand-icon {
  font-size: 20px;
  color: #94a3b8;
  transition: transform 0.2s ease, color 0.2s ease;
}

.expand-icon.rotated {
  transform: rotate(90deg);
  color: #c084fc;
}

.cell-number {
  color: #94a3b8;
  font-weight: 600;
}

.classification-pill {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  padding: 2px 6px;
  border-radius: 4px;
  letter-spacing: 0.03em;
}

.pill-client {
  background: rgba(59, 130, 246, 0.2);
  color: #93c5fd;
  border: 1px solid rgba(59, 130, 246, 0.35);
}

.pill-org {
  background: rgba(168, 85, 247, 0.2);
  color: #d8b4fe;
  border: 1px solid rgba(168, 85, 247, 0.35);
}

.pill-attributed {
  background: rgba(245, 158, 11, 0.2);
  color: #fde68a;
  border: 1px solid rgba(245, 158, 11, 0.35);
}

.pill-general {
  background: rgba(148, 163, 184, 0.15);
  color: #cbd5e1;
}

.unit-code {
  font-size: 10px;
  font-family: 'IBM Plex Mono', monospace;
  background: rgba(0, 0, 0, 0.4);
  color: #cbd5e1;
  padding: 2px 6px;
  border-radius: 4px;
}

.office-pill {
  font-size: 10px;
  font-weight: 600;
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #6ee7b7;
  padding: 2px 6px;
  border-radius: 4px;
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.unit-name {
  font-size: 12.5px;
  color: #e2e8f0;
  font-weight: 500;
  line-height: 1.4;
}

.activity-text {
  font-size: 12.5px;
  color: #cbd5e1;
  line-height: 1.4;
  font-weight: 500;
}

.cell-value {
  font-size: 13.5px;
}

.sub-badge {
  font-size: 11px;
  margin-top: 2px;
}

.remaining-healthy {
  color: #34d399;
}

.remaining-warning {
  color: #fbbf24;
}

.remaining-critical {
  color: #f87171;
}

.btn-audit-toggle {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: rgba(192, 132, 252, 0.1);
  border: 1px solid rgba(192, 132, 252, 0.25);
  color: #d8b4fe;
  padding: 5px 9px;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-audit-toggle:hover, .btn-audit-toggle.active {
  background: #c084fc;
  color: #0f172a;
  border-color: #c084fc;
}

.audit-counter {
  background: rgba(0, 0, 0, 0.35);
  color: #ffffff;
  padding: 1px 5px;
  border-radius: 10px;
  font-size: 10px;
  font-weight: 700;
}

.btn-audit-toggle.active .audit-counter {
  background: #0f172a;
  color: #c084fc;
}

/* Collapsible Audit Drawer */
.audit-drawer-row {
  background: #0d0a17;
  border-bottom: 2px solid rgba(192, 132, 252, 0.2);
}

.audit-drawer-cell {
  padding: 0 !important;
}

.audit-drawer-container {
  padding: 16px 20px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.drawer-header-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #1a1628;
  border: 1px solid rgba(192, 132, 252, 0.2);
  border-radius: 10px;
  padding: 10px 14px;
  flex-wrap: wrap;
  gap: 10px;
}

.drawer-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.drawer-chip-code {
  font-size: 11px;
  font-family: 'IBM Plex Mono', monospace;
  font-weight: 700;
  background: #9333ea;
  color: #ffffff;
  padding: 3px 8px;
  border-radius: 4px;
}

.drawer-mandate-title {
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
}

.drawer-financial-summary {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12px;
  flex-wrap: wrap;
}

.summary-sep {
  color: rgba(255, 255, 255, 0.15);
}

.summary-item {
  display: inline-flex;
  gap: 5px;
}

/* 2-Column Audit Panels */
.audit-panels-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}

@media (max-width: 900px) {
  .audit-panels-grid {
    grid-template-columns: 1fr;
  }
}

.audit-panel {
  background: #141120;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.audit-panel.ad-panel {
  border-color: rgba(245, 158, 11, 0.25);
}

.audit-panel.ar-panel {
  border-color: rgba(16, 185, 129, 0.25);
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 8px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.panel-title {
  font-size: 12.5px;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}

.panel-badge-count {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
}

.panel-badge-count.amber {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.panel-badge-count.green {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.panel-empty {
  padding: 24px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #64748b;
  gap: 8px;
  font-size: 12px;
}

.doc-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 340px;
  overflow-y: auto;
  padding-right: 4px;
}

.doc-card {
  background: #1b172a;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 8px;
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  transition: all 0.2s;
}

.doc-card:hover {
  border-color: rgba(192, 132, 252, 0.3);
  background: #201b33;
}

.doc-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.doc-control-badge {
  font-size: 11px;
  font-family: 'IBM Plex Mono', monospace;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
}

.doc-amount {
  font-size: 13px;
}

.doc-title {
  font-size: 12.5px;
  color: #e2e8f0;
  font-weight: 500;
  line-height: 1.35;
}

.doc-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 4px;
  padding-top: 6px;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
}

.doc-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 11px;
  color: #94a3b8;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.btn-preview-doc {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: rgba(192, 132, 252, 0.15);
  border: 1px solid rgba(192, 132, 252, 0.3);
  color: #d8b4fe;
  padding: 3px 8px;
  border-radius: 5px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-preview-doc:hover {
  background: #c084fc;
  color: #0f172a;
}

.btn-clear-empty {
  background: #1e1b2e;
  border: 1px solid rgba(192, 132, 252, 0.3);
  color: #d8b4fe;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.btn-clear-empty:hover {
  background: rgba(192, 132, 252, 0.2);
}
</style>

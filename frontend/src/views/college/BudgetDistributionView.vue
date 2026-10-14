<template>
  <div class="app-wrapper app" style="background: #ffffff; width: 100%; overflow-x: auto;">
    <div class="banner-wrapper">
      <header class="topbar">
        <div class="topbar-brand">
          <span class="topbar-eyebrow">GAD Budget Distribution</span>
          <h1 class="topbar-title">Budget Distribution by Mandate</h1>
        </div>
        <div class="topbar-actions">
          <router-link to="/college/plan-and-budget">
            <button class="topbar-btn outline">← Back to Plan & Budget</button>
          </router-link>
        </div>
      </header>
    </div>

    <div id="mandate-statistics-section" class="card main-card">
      <div class="card-controls">
        <div class="card-title-row">
          <h2 class="card-title">
            GAD Budget Distribution by Mandate
          </h2>
          <div class="filter-group">
             <label class="filter-label">Filter by Classification:</label>
             <select v-model="mandateStatsFilter" class="filter-select">
               <option value="all">All Classifications</option>
               <option value="client">Client-Focused</option>
               <option value="org">Organization-Focused</option>
               <option value="attributed">Attributed Program</option>
             </select>
          </div>
        </div>

        <div class="stats-search-row">
          <div class="table-actions-left">
            <button 
              @click="toggleAllRows" 
              class="btn-toggle-all"
              type="button"
              :title="allExpanded ? 'Collapse all budget line breakdowns' : 'Expand all budget line breakdowns'"
            >
              <span class="material-symbols-outlined text-[16px]">{{ allExpanded ? 'unfold_less' : 'unfold_more' }}</span>
              <span>{{ allExpanded ? 'Collapse All Lines' : 'Expand All Lines' }}</span>
            </button>
            <span class="results-counter">
              Showing <b>{{ filteredMandateStats.length }}</b> of {{ mandateStats.length }} mandates
            </span>
          </div>

          <div class="search-wrap" :class="{ 'search-active': searchQuery }">
            <span class="material-symbols-outlined search-icon">search</span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search mandate, cause, activity..."
              class="search-input"
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="search-clear-btn" type="button">Clear</button>
          </div>
        </div>
      </div>

      <div v-if="loadingStats" class="state-message-box">
        Loading statistics...
      </div>
      <div v-else-if="mandateStats.length === 0" class="empty-state-card">
        <span class="empty-icon">📭</span>
        <h3 class="empty-title">No Mandate Data Available</h3>
        <p class="empty-desc">The statistics are generated from your saved GAD Plan.<br>Please go to <router-link to="/college/plan-and-budget" class="plan-link">Plan & Budget</router-link> and click <b>"Save Plan"</b> first to generate statistics.</p>
      </div>
      <div v-else>
         <div v-if="filteredMandateStats.length === 0" class="state-message-box">
           <span style="font-size: 1.5rem; display: block; margin-bottom: 8px;">🔍</span>
           No mandates match your search or filter criteria.
         </div>
         <div v-else class="mandate-table-container">
           <table class="mandate-table">
             <thead>
               <tr class="mandate-th-row">
                 <th class="th-expand text-center" style="width: 44px;"></th>
                 <th class="th-idx text-center" style="width: 48px;">#</th>
                 <th class="th-mandate text-left" style="min-width: 280px;">Gender Issue / Mandate</th>
                 <th class="th-activity text-left" style="min-width: 220px;">GAD Activity</th>
                 <th class="th-docs text-center" style="width: 130px;">Approved Docs</th>
                 <th class="th-amount text-right" style="width: 150px;">GAD Budget</th>
                 <th class="th-amount text-right" style="width: 150px;">Utilized (AR)</th>
                 <th class="th-amount text-right" style="width: 140px;">Pending (AD)</th>
                 <th class="th-amount text-right" style="width: 160px;">Remaining</th>
                 <th class="th-actions text-center" style="width: 120px;">Details</th>
               </tr>
             </thead>
             <tbody>
               <template v-for="(stat, idx) in filteredMandateStats" :key="stat.key || idx">
                <tr 
                  class="mandate-row"
                  :class="{ 'row-expanded': isExpanded(stat.key || idx) }"
                  @click="toggleRow(stat.key || idx)"
                >
                  <!-- Expand toggle -->
                  <td class="td-cell text-center" @click.stop="toggleRow(stat.key || idx)">
                    <button 
                      type="button"
                      class="expand-toggle-btn" 
                      :class="{ 'is-rotated': isExpanded(stat.key || idx) }"
                      :title="isExpanded(stat.key || idx) ? 'Collapse Details' : 'Expand Details'"
                    >
                      <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                  </td>

                  <!-- # Index -->
                  <td class="td-cell text-center font-mono text-muted">
                    {{ idx + 1 }}
                  </td>

                  <!-- Mandate & Classification & Cause -->
                  <td class="td-cell text-left">
                    <div class="mandate-cell">
                      <div class="pill-row">
                        <span class="classification-pill" :class="getClassificationClass(stat.classification)">
                          {{ getClassificationLabel(stat.classification) }}
                        </span>
                      </div>
                      <div class="mandate-name-text">{{ stat.mandate || 'N/A' }}</div>
                      <div v-if="stat.cause" class="mandate-cause-box">
                        <span class="cause-prefix">Cause:</span> {{ stat.cause }}
                      </div>
                    </div>
                  </td>

                  <!-- GAD Activity -->
                  <td class="td-cell text-left">
                    <div class="activity-text">{{ stat.activity || 'N/A' }}</div>
                  </td>

                  <!-- Approved Docs -->
                  <td class="td-cell text-center">
                    <div class="docs-badge-stack">
                      <span class="badge-doc badge-ad" :title="stat.approved_ad_count + ' Approved Activity Designs'">
                        {{ stat.approved_ad_count }} AD{{ stat.approved_ad_count === 1 ? '' : 's' }}
                      </span>
                      <span class="badge-doc badge-ar" :title="stat.approved_ar_count + ' Approved Accomplishment Reports'">
                        {{ stat.approved_ar_count }} AR{{ stat.approved_ar_count === 1 ? '' : 's' }}
                      </span>
                    </div>
                  </td>

                  <!-- GAD Budget -->
                  <td class="td-cell text-right">
                    <div class="amount-val mono font-bold">₱{{ formatCurrency(stat.budget) }}</div>
                    <div v-if="stat.budget_lines && stat.budget_lines.length > 0" class="lines-count-hint">
                      {{ stat.budget_lines.length }} line item{{ stat.budget_lines.length === 1 ? '' : 's' }}
                    </div>
                  </td>

                  <!-- Utilized (AR) -->
                  <td class="td-cell text-right">
                    <div class="amount-val mono text-green font-semibold">₱{{ formatCurrency(stat.utilized_budget) }}</div>
                    <div v-if="stat.budget > 0" class="util-percent-hint text-green">
                      {{ ((stat.utilized_budget / stat.budget) * 100).toFixed(1) }}%
                    </div>
                  </td>

                  <!-- Pending (AD) -->
                  <td class="td-cell text-right">
                    <div class="amount-val mono text-yellow">₱{{ formatCurrency(stat.pending_budget) }}</div>
                  </td>

                  <!-- Remaining -->
                  <td class="td-cell text-right">
                    <div 
                      class="amount-val mono font-bold"
                      :class="stat.remaining_budget < 0 ? 'text-red font-extrabold' : 'text-blue'"
                    >
                      {{ stat.remaining_budget < 0 ? '-' : '' }}₱{{ formatCurrency(Math.abs(stat.remaining_budget)) }}
                    </div>
                    <span v-if="stat.remaining_budget < 0" class="deficit-chip">DEFICIT</span>
                  </td>

                  <!-- Details -->
                  <td class="td-cell text-center" @click.stop>
                    <div class="actions-flex justify-center">
                      <button 
                        type="button"
                        @click="toggleRow(stat.key || idx)" 
                        class="tbl-action-btn btn-lines-toggle"
                        :class="{ 'btn-lines-active': isExpanded(stat.key || idx) }"
                        :title="isExpanded(stat.key || idx) ? 'Hide Lines' : 'View Lines'"
                      >
                        <span class="material-symbols-outlined text-[15px]">{{ isExpanded(stat.key || idx) ? 'unfold_less' : 'unfold_more' }}</span>
                        <span>{{ (stat.budget_lines?.length || 0) }} Lines</span>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Expanded Breakdown Drawer Row -->
                <tr v-if="isExpanded(stat.key || idx)" class="drawer-tr">
                  <td colspan="10" class="drawer-td">
                    <div class="drawer-wrapper">
                      <div class="drawer-top-bar">
                        <div class="drawer-heading-group">
                          <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">table_rows</span>
                          <span class="drawer-title">Budget Lines Breakdown</span>
                          <span class="drawer-subtitle">— {{ stat.mandate }}</span>
                        </div>
                        <div class="drawer-mini-metrics mono">
                          <span class="metric-pill metric-budget">
                            <span class="m-lbl">Budget:</span>
                            <span class="m-val">₱{{ formatCurrency(stat.budget) }}</span>
                          </span>
                          <span class="metric-sep">/</span>
                          <span class="metric-pill metric-utilized">
                            <span class="m-lbl">Utilized:</span>
                            <span class="m-val">₱{{ formatCurrency(stat.utilized_budget) }}</span>
                          </span>
                          <span class="metric-sep">/</span>
                          <span class="metric-pill metric-pending">
                            <span class="m-lbl">Pending:</span>
                            <span class="m-val">₱{{ formatCurrency(stat.pending_budget) }}</span>
                          </span>
                          <span class="metric-sep">/</span>
                          <span class="metric-pill" :class="stat.remaining_budget < 0 ? 'metric-deficit' : 'metric-remaining'">
                            <span class="m-lbl">Remaining:</span>
                            <span class="m-val">{{ stat.remaining_budget < 0 ? '-' : '' }}₱{{ formatCurrency(Math.abs(stat.remaining_budget)) }}</span>
                          </span>
                        </div>
                      </div>

                      <!-- Subtable -->
                      <div v-if="stat.budget_lines && stat.budget_lines.length > 0" class="subtable-container">
                        <table class="nested-table">
                          <thead>
                            <tr>
                              <th class="text-center" style="width: 44px;">#</th>
                              <th class="text-left">Budget Line Item</th>
                              <th class="text-right" style="width: 170px;">Original Budget</th>
                              <th class="text-right" style="width: 170px;">Utilized (AR)</th>
                              <th class="text-right" style="width: 170px;">Pending (AD)</th>
                              <th class="text-right" style="width: 170px;">Remaining Balance</th>
                              <th class="text-center" style="width: 140px;">Utilization Rate</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(bl, bidx) in stat.budget_lines" :key="bl.id || bidx">
                              <td class="text-center font-mono text-muted">{{ bidx + 1 }}</td>
                              <td class="text-left font-semibold text-main">{{ bl.label || 'Unnamed Line' }}</td>
                              <td class="text-right font-mono">₱{{ formatCurrency(bl.amount) }}</td>
                              <td class="text-right font-mono text-green font-semibold">₱{{ formatCurrency(bl.utilized_budget) }}</td>
                              <td class="text-right font-mono text-yellow">₱{{ formatCurrency(bl.pending_budget) }}</td>
                              <td 
                                class="text-right font-mono font-bold"
                                :class="((bl.amount || 0) - (bl.utilized_budget || 0) - (bl.pending_budget || 0)) < 0 ? 'text-red' : 'text-blue'"
                              >
                                ₱{{ formatCurrency((bl.amount || 0) - (bl.utilized_budget || 0) - (bl.pending_budget || 0)) }}
                              </td>
                              <td class="text-center">
                                <div class="rate-progress-cell">
                                  <div class="rate-track">
                                    <div 
                                      class="rate-fill"
                                      :style="{ width: Math.min(100, Math.max(0, (bl.amount > 0 ? (bl.utilized_budget / bl.amount) * 100 : 0))) + '%' }"
                                    ></div>
                                  </div>
                                  <span class="rate-text font-mono">
                                    {{ bl.amount > 0 ? ((bl.utilized_budget / bl.amount) * 100).toFixed(0) : 0 }}%
                                  </span>
                                </div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <div v-else class="drawer-empty-state">
                        <span class="material-symbols-outlined empty-drawer-icon">info</span>
                        <span>No itemized budget lines recorded in the GAD Plan for this mandate.</span>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
            <tfoot class="mandate-tfoot">
              <tr>
                <td colspan="4" class="tfoot-cell text-left font-bold">
                  Total Summary ({{ filteredMandateStats.length }} Mandate{{ filteredMandateStats.length === 1 ? '' : 's' }})
                </td>
                <td class="tfoot-cell text-center">
                  <div class="docs-badge-stack justify-center">
                    <span class="badge-doc badge-ad">{{ totalApprovedAds }} ADs</span>
                    <span class="badge-doc badge-ar">{{ totalApprovedArs }} ARs</span>
                  </div>
                </td>
                <td class="tfoot-cell text-right font-mono font-bold">
                  ₱{{ formatCurrency(totalBudget) }}
                </td>
                <td class="tfoot-cell text-right font-mono font-bold text-green">
                  ₱{{ formatCurrency(totalUtilized) }}
                </td>
                <td class="tfoot-cell text-right font-mono font-bold text-yellow">
                  ₱{{ formatCurrency(totalPending) }}
                </td>
                <td 
                  class="tfoot-cell text-right font-mono font-bold"
                  :class="totalRemaining < 0 ? 'text-red' : 'text-blue'"
                >
                  {{ totalRemaining < 0 ? '-' : '' }}₱{{ formatCurrency(Math.abs(totalRemaining)) }}
                </td>
                <td class="tfoot-cell"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <PdfPreviewModal :isOpen="isPdfModalOpen" :fileUrl="pdfFileUrl" @close="closePdfModal" />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import api from '../../api';
import PdfPreviewModal from '../../components/PdfPreviewModal.vue';

export default {
  name: 'BudgetDistributionView',
  components: { PdfPreviewModal },
  setup() {
    const mandateStats = ref([]);
    const mandateStatsFilter = ref('all');
    const searchQuery = ref('');
    const filteredMandateStats = computed(() => {
      let list = mandateStats.value;
      if (mandateStatsFilter.value !== 'all') {
        list = list.filter(s => s.classification === mandateStatsFilter.value);
      }
      const q = searchQuery.value.trim().toLowerCase();
      if (q) {
        list = list.filter(s =>
          (s.mandate || '').toLowerCase().includes(q) ||
          (s.cause || '').toLowerCase().includes(q) ||
          (s.activity || '').toLowerCase().includes(q)
        );
      }
      return list;
    });

    const expandedRows = ref(new Set());
    const toggleRow = (key) => {
      if (expandedRows.value.has(key)) {
        expandedRows.value.delete(key);
      } else {
        expandedRows.value.add(key);
      }
      expandedRows.value = new Set(expandedRows.value);
    };
    const isExpanded = (key) => expandedRows.value.has(key);
    const allExpanded = computed(() => {
      return filteredMandateStats.value.length > 0 && expandedRows.value.size === filteredMandateStats.value.length;
    });
    const toggleAllRows = () => {
      if (allExpanded.value) {
        expandedRows.value = new Set();
      } else {
        expandedRows.value = new Set(filteredMandateStats.value.map((s, idx) => s.key || idx));
      }
    };

    const formatCurrency = (val) => {
      return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const getClassificationLabel = (val) => {
      if (!val) return 'Unclassified';
      const v = String(val).toLowerCase();
      if (v === 'client') return 'Client-Focused';
      if (v === 'org') return 'Org-Focused';
      if (v === 'attributed') return 'Attributed Program';
      return val;
    };

    const getClassificationClass = (val) => {
      const v = String(val).toLowerCase();
      if (v === 'client') return 'pill-client';
      if (v === 'org') return 'pill-org';
      if (v === 'attributed') return 'pill-attributed';
      return 'pill-default';
    };

    const totalBudget = computed(() => {
      return filteredMandateStats.value.reduce((sum, s) => sum + (Number(s.budget) || 0), 0);
    });
    const totalUtilized = computed(() => {
      return filteredMandateStats.value.reduce((sum, s) => sum + (Number(s.utilized_budget) || 0), 0);
    });
    const totalPending = computed(() => {
      return filteredMandateStats.value.reduce((sum, s) => sum + (Number(s.pending_budget) || 0), 0);
    });
    const totalRemaining = computed(() => {
      return filteredMandateStats.value.reduce((sum, s) => sum + (Number(s.remaining_budget) || 0), 0);
    });
    const totalApprovedAds = computed(() => {
      return filteredMandateStats.value.reduce((sum, s) => sum + (Number(s.approved_ad_count) || 0), 0);
    });
    const totalApprovedArs = computed(() => {
      return filteredMandateStats.value.reduce((sum, s) => sum + (Number(s.approved_ar_count) || 0), 0);
    });

    const loadingStats = ref(true);
    
    const fetchMandateStats = async () => {
      loadingStats.value = true;
      try {
        const response = await api.get('/plan/mandate-statistics');
        if (response.data.success) {
          mandateStats.value = response.data.data;
        }
      } catch (err) {
        console.error('Failed to fetch mandate stats', err);
      } finally {
        loadingStats.value = false;
      }
    };

    const isPdfModalOpen = ref(false);
    const pdfFileUrl = ref('');

    const openDocumentPreview = (attachment, type) => {
      if (attachment) {
        let fileName = attachment;
        if (typeof attachment === 'string' && attachment.startsWith('[')) {
           try {
               const parsed = JSON.parse(attachment);
               if (parsed.length > 0) fileName = parsed[0];
           } catch(e) {}
        }
        pdfFileUrl.value = `${import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '')}/api/files/archived/${fileName}`;
        isPdfModalOpen.value = true;
      }
    };
    const closePdfModal = () => {
      isPdfModalOpen.value = false;
      pdfFileUrl.value = '';
    };

    onMounted(async () => {
      fetchMandateStats();
    });

    return {
      mandateStats, mandateStatsFilter, searchQuery, filteredMandateStats, loadingStats,
      expandedRows, toggleRow, isExpanded, allExpanded, toggleAllRows, formatCurrency,
      getClassificationLabel, getClassificationClass, totalBudget, totalUtilized, totalPending,
      totalRemaining, totalApprovedAds, totalApprovedArs,
      fetchMandateStats,
      isPdfModalOpen, pdfFileUrl, openDocumentPreview, closePdfModal
    };
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&display=swap');

*, *::before, *::after { box-sizing: border-box; }

.app-wrapper {
  margin: 0;
  padding: 0;
  background: #ffffff;
  color: #0f172a;
  font-size: 15.5px;
  line-height: 1.6;
  min-height: calc(100vh - 80px);
}
.font-mono { font-family: 'IBM Plex Mono', monospace; }
.font-bold { font-weight: 700; }
.text-xs { font-size: 0.75rem; }
.text-muted { color: #64748b; }

/* Top Banner */
.banner-wrapper {
  min-width: 1200px;
  margin: 32px 32px 0 32px;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
  background: #ffffff;
  display: flex;
  flex-direction: column;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 24px;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
}

.topbar-brand {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-width: 180px;
  margin-right: 6px;
  border-right: 1px solid #e2e8f0;
  padding-right: 18px;
}
.topbar-eyebrow {
  font-size: 0.75rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #7e22ce;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 4px;
}
.topbar-title {
  font-size: 1.125rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.2;
  white-space: nowrap;
}

.topbar-actions {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-shrink: 0;
}
.topbar-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 0.8125rem;
  font-weight: 700;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #475569;
  transition: all 0.2s;
  cursor: pointer;
}
.topbar-btn:hover {
  background: #f8fafc;
  border-color: #94a3b8;
  color: #0f172a;
}

/* Main Card */
.main-card {
  min-width: 1200px;
  margin: 24px 32px 32px 32px;
  padding: 24px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
}

.card-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 20px;
}
.card-title {
  color: #0f172a;
  font-size: 1.25rem;
  font-weight: 800;
  margin: 0;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.filter-label {
  color: #64748b;
  font-size: 0.875rem;
  font-weight: 600;
}
.filter-select {
  background: #f8fafc url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%237e22ce' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E") no-repeat right 0.75rem center/1.25rem 1.25rem;
  appearance: none;
  border: 1px solid #cbd5e1;
  color: #0f172a;
  padding: 6px 32px 6px 12px;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  outline: none;
  cursor: pointer;
  transition: all 0.2s;
}
.filter-select:focus {
  border-color: #9333ea;
  background-color: #ffffff;
  box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.15);
}
.filter-select option {
  background: #ffffff;
  color: #0f172a;
}

/* Stats Summary Strip */
.stats-summary-strip {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}
.stat-pill {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.2s ease;
}
.stat-pill:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.stat-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(126, 34, 206, 0.1);
  color: #7e22ce;
}
.stat-icon-wrap.stat-purple {
  background: rgba(147, 51, 234, 0.1);
  color: #9333ea;
}
.stat-icon-wrap.stat-emerald {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}
.stat-icon-wrap.stat-blue {
  background: rgba(37, 99, 235, 0.1);
  color: #2563eb;
}
.stat-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.stat-title {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #64748b;
}
.stat-count {
  font-size: 1.15rem;
  font-weight: 800;
  color: #0f172a;
}

.stats-search-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}

/* Table Toolbar Actions */
.table-actions-left {
  display: flex;
  align-items: center;
  gap: 14px;
}
.btn-toggle-all {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #1e293b;
  border-radius: 8px;
  font-size: 0.8125rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
.btn-toggle-all:hover {
  background: #f8fafc;
  border-color: #9333ea;
  color: #7e22ce;
}
.results-counter {
  font-size: 0.85rem;
  color: #64748b;
}
.results-counter b {
  color: #7e22ce;
}

.search-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 16px;
  background: #f8fafc;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  transition: all 0.2s;
  min-width: 280px;
}
.search-wrap.search-active, .search-wrap:focus-within {
  border-color: #9333ea;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.15);
}
.search-icon {
  color: #64748b;
  font-size: 20px;
  flex-shrink: 0;
}
.search-input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 12px 0;
  color: #0f172a;
  font-size: 0.875rem;
  outline: none;
}
.search-clear-btn {
  background: #e2e8f0;
  border: 1px solid #cbd5e1;
  color: #475569;
  border-radius: 6px;
  padding: 4px 10px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
}
.search-clear-btn:hover {
  background: #cbd5e1;
  color: #0f172a;
}

/* State Boxes */
.state-message-box {
  text-align: center;
  color: #64748b;
  padding: 40px;
}
.empty-state-card {
  text-align: center;
  color: #64748b;
  padding: 40px;
}
.empty-icon {
  font-size: 2.25rem;
  display: block;
  margin-bottom: 12px;
}
.empty-title {
  color: #0f172a;
  font-weight: 800;
  margin-bottom: 8px;
}
.empty-desc {
  font-size: 0.9rem;
}
.plan-link {
  color: #7e22ce;
  font-weight: 600;
  text-decoration: underline;
}

/* Mandate Table Container */
.mandate-table-container {
  width: 100%;
  overflow-x: auto;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  background: #ffffff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  margin-top: 8px;
}
.mandate-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.875rem;
  min-width: 1100px;
}
.mandate-th-row {
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
}
.mandate-table th {
  padding: 14px 16px;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #475569;
  white-space: nowrap;
}
.mandate-row {
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: background-color 0.15s ease;
}
.mandate-row:hover {
  background-color: #f8fafc;
}
.mandate-row.row-expanded {
  background-color: #faf5ff;
}
.td-cell {
  padding: 14px 16px;
  vertical-align: middle;
  color: #1e293b;
}

/* Expand toggle chevron */
.expand-toggle-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  padding: 4px;
  border-radius: 6px;
  transition: transform 0.2s ease, color 0.2s ease;
}
.expand-toggle-btn:hover {
  color: #7e22ce;
  background: rgba(126, 34, 206, 0.08);
}
.expand-toggle-btn.is-rotated {
  transform: rotate(90deg);
  color: #7e22ce;
}

/* Mandate Cell */
.mandate-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.pill-row {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 2px;
}
.classification-pill {
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 2px 8px;
  border-radius: 4px;
  display: inline-block;
}
.pill-client {
  background: rgba(59, 130, 246, 0.1);
  color: #1d4ed8;
  border: 1px solid rgba(59, 130, 246, 0.25);
}
html.dark .pill-client,
.dark .pill-client {
  background: rgba(59, 130, 246, 0.15) !important;
  color: #93c5fd !important;
  border: 1px solid rgba(59, 130, 246, 0.35) !important;
}

.pill-org {
  background: rgba(168, 85, 247, 0.1);
  color: #7e22ce;
  border: 1px solid rgba(168, 85, 247, 0.25);
}
html.dark .pill-org,
.dark .pill-org {
  background: rgba(168, 85, 247, 0.15) !important;
  color: #d8b4fe !important;
  border: 1px solid rgba(168, 85, 247, 0.35) !important;
}

.pill-attributed {
  background: rgba(245, 158, 11, 0.1);
  color: #b45309;
  border: 1px solid rgba(245, 158, 11, 0.25);
}
html.dark .pill-attributed,
.dark .pill-attributed {
  background: rgba(245, 158, 11, 0.15) !important;
  color: #fde68a !important;
  border: 1px solid rgba(245, 158, 11, 0.35) !important;
}

.pill-default {
  background: rgba(148, 163, 184, 0.1);
  color: #475569;
  border: 1px solid rgba(148, 163, 184, 0.25);
}
html.dark .pill-default,
.dark .pill-default {
  background: rgba(148, 163, 184, 0.15) !important;
  color: #cbd5e1 !important;
  border: 1px solid rgba(148, 163, 184, 0.25) !important;
}

.mandate-name-text {
  font-weight: 700;
  color: #0f172a;
  font-size: 0.925rem;
  line-height: 1.4;
}
.mandate-cause-box {
  font-size: 0.775rem;
  color: #64748b;
  line-height: 1.35;
  margin-top: 2px;
}
.cause-prefix {
  font-weight: 700;
  color: #8b5cf6;
}

/* Activity text */
.activity-text {
  font-size: 0.85rem;
  color: #334155;
  line-height: 1.4;
}

/* Docs badge stack */
.docs-badge-stack {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.badge-doc {
  font-size: 0.725rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  white-space: nowrap;
}
.badge-doc.badge-ad {
  background: #fef3c7;
  color: #b45309;
  border: 1px solid #fde68a;
}
.badge-doc.badge-ar {
  background: #d1fae5;
  color: #047857;
  border: 1px solid #a7f3d0;
}

/* Amounts */
.amount-val {
  font-size: 0.95rem;
}
.lines-count-hint {
  font-size: 0.7rem;
  color: #94a3b8;
  margin-top: 2px;
}
.util-percent-hint {
  font-size: 0.725rem;
  font-weight: 700;
  margin-top: 2px;
}
.deficit-chip {
  display: inline-block;
  font-size: 0.65rem;
  font-weight: 800;
  background: #fee2e2;
  color: #b91c1c;
  padding: 1px 6px;
  border-radius: 4px;
  margin-top: 2px;
}

/* Actions in Table */
.actions-flex {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.tbl-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 0.775rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}
.btn-lines-toggle {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  color: #475569;
}
.btn-lines-toggle:hover,
.btn-lines-toggle.btn-lines-active {
  background: #7e22ce;
  border-color: #7e22ce;
  color: #ffffff;
}

/* Drawer Row */
.drawer-tr {
  background: #fbfbfe;
  border-bottom: 2px solid #e2e8f0;
}
.drawer-td {
  padding: 16px 20px !important;
}
.drawer-wrapper {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
}
.drawer-top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f5f9;
  margin-bottom: 14px;
}
.drawer-heading-group {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.drawer-title {
  font-weight: 800;
  color: #0f172a;
  font-size: 0.925rem;
}
.drawer-subtitle {
  color: #64748b;
  font-size: 0.85rem;
}
.drawer-mini-metrics {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.825rem;
  flex-wrap: wrap;
}
.metric-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 3px 8px;
  border-radius: 6px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}
.metric-pill .m-lbl {
  color: #64748b;
  font-weight: 600;
  font-size: 0.775rem;
}
.metric-pill .m-val {
  font-weight: 700;
  color: #0f172a;
}
.metric-budget .m-val {
  color: #2563eb;
}
.metric-utilized .m-val {
  color: #059669;
}
.metric-pending .m-val {
  color: #d97706;
}
.metric-remaining .m-val {
  color: #0284c7;
}
.metric-deficit {
  background: #fef2f2 !important;
  border-color: #fecaca !important;
}
.metric-deficit .m-lbl {
  color: #b91c1c !important;
}
.metric-deficit .m-val {
  color: #dc2626 !important;
}
.metric-sep {
  color: #94a3b8;
  font-weight: 700;
  opacity: 0.5;
}

/* Sub-table */
.subtable-container {
  overflow-x: auto;
}
.nested-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8125rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}
.nested-table thead tr {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}
.nested-table th {
  padding: 10px 14px;
  font-size: 0.725rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #475569;
}
.nested-table tbody tr {
  border-bottom: 1px solid #f1f5f9;
}
.nested-table tbody tr:last-child {
  border-bottom: none;
}
.nested-table td {
  padding: 10px 14px;
  vertical-align: middle;
}
.rate-progress-cell {
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: center;
}
.rate-track {
  width: 70px;
  height: 6px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}
.rate-fill {
  height: 100%;
  background: #10b981;
  border-radius: 4px;
}
.rate-text {
  font-size: 0.75rem;
  font-weight: 700;
  color: #059669;
  min-width: 34px;
  text-align: right;
}
.drawer-empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 18px;
  color: #64748b;
  font-size: 0.85rem;
  background: #f8fafc;
  border-radius: 6px;
}

/* Table Footer */
.mandate-tfoot {
  background: #f8fafc;
  border-top: 2px solid #cbd5e1;
}
.tfoot-cell {
  padding: 14px 16px;
  font-size: 0.875rem;
  color: #0f172a;
  white-space: nowrap;
}
.text-green { color: #10b981 !important; }
.text-yellow { color: #d97706 !important; }
.text-red { color: #ef4444 !important; }
.text-blue { color: #2563eb !important; }
</style>

<style>
/* ==========================================================================
   Dark Mode Overrides for Budget Distribution by Mandate
   Outer background remains white; ONLY the cards darken!
   ========================================================================== */
html.dark .banner-wrapper,
.dark .banner-wrapper {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
  border-color: rgba(185, 121, 204, 0.25) !important;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25) !important;
}

html.dark .topbar,
.dark .topbar {
  background: transparent !important;
  border-bottom-color: rgba(185, 121, 204, 0.2) !important;
}

html.dark .topbar-brand,
.dark .topbar-brand {
  border-right-color: rgba(185, 121, 204, 0.2) !important;
}

html.dark .topbar-eyebrow,
.dark .topbar-eyebrow {
  color: #c084fc !important;
}

html.dark .topbar-title,
.dark .topbar-title {
  color: #ffffff !important;
}

html.dark .topbar-btn,
.dark .topbar-btn {
  background: rgba(0, 0, 0, 0.3) !important;
  border-color: rgba(185, 121, 204, 0.3) !important;
  color: #cbd5e1 !important;
}
html.dark .topbar-btn:hover,
.dark .topbar-btn:hover {
  background: rgba(255, 255, 255, 0.08) !important;
  color: #ffffff !important;
}

html.dark .main-card,
.dark .main-card {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
  border-color: rgba(185, 121, 204, 0.25) !important;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25) !important;
}

html.dark .card-title,
.dark .card-title {
  color: #ffffff !important;
}

html.dark .filter-label,
.dark .filter-label {
  color: #cbd5e1 !important;
}

html.dark .filter-select,
.dark .filter-select {
  background: rgba(0, 0, 0, 0.4) url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23b979cc' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E") no-repeat right 0.75rem center/1.25rem 1.25rem !important;
  border-color: rgba(185, 121, 204, 0.4) !important;
  color: #ffffff !important;
}
html.dark .filter-select option,
.dark .filter-select option {
  background: #1e293b !important;
  color: #ffffff !important;
}

/* Table Dark Mode Overrides */
html.dark .stats-summary-strip .stat-pill,
.dark .stats-summary-strip .stat-pill {
  background: rgba(0, 0, 0, 0.25) !important;
  border-color: rgba(185, 121, 204, 0.2) !important;
}
html.dark .stat-title,
.dark .stat-title {
  color: #94a3b8 !important;
}
html.dark .stat-count,
.dark .stat-count {
  color: #ffffff !important;
}
html.dark .btn-toggle-all,
.dark .btn-toggle-all {
  background: #1e1b2e !important;
  border: 1px solid rgba(192, 132, 252, 0.25) !important;
  color: #ffffff !important;
}
html.dark .btn-toggle-all:hover,
.dark .btn-toggle-all:hover {
  background: #252038 !important;
  border-color: #c084fc !important;
  color: #e9d5ff !important;
}

html.dark .mandate-table-container,
.dark .mandate-table-container {
  background: rgba(0, 0, 0, 0.2) !important;
  border-color: rgba(185, 121, 204, 0.2) !important;
}
html.dark .mandate-th-row,
.dark .mandate-th-row {
  background: rgba(0, 0, 0, 0.35) !important;
  border-bottom-color: rgba(185, 121, 204, 0.2) !important;
}
html.dark .mandate-table th,
.dark .mandate-table th {
  color: #cbd5e1 !important;
}
html.dark .mandate-row,
.dark .mandate-row {
  border-bottom-color: rgba(255, 255, 255, 0.05) !important;
}
html.dark .mandate-row:hover,
.dark .mandate-row:hover {
  background-color: rgba(255, 255, 255, 0.04) !important;
}
html.dark .mandate-row.row-expanded,
.dark .mandate-row.row-expanded {
  background-color: rgba(126, 34, 206, 0.12) !important;
}
html.dark .td-cell,
.dark .td-cell {
  color: #e2e8f0 !important;
}
html.dark .mandate-name-text,
.dark .mandate-name-text {
  color: #ffffff !important;
}
html.dark .mandate-cause-box,
.dark .mandate-cause-box {
  color: #94a3b8 !important;
}
html.dark .activity-text,
.dark .activity-text {
  color: #cbd5e1 !important;
}

html.dark .pill-client,
.dark .pill-client {
  background: rgba(59, 130, 246, 0.2) !important;
  color: #93c5fd !important;
  border-color: rgba(59, 130, 246, 0.35) !important;
}
html.dark .pill-org,
.dark .pill-org {
  background: rgba(168, 85, 247, 0.2) !important;
  color: #d8b4fe !important;
  border-color: rgba(168, 85, 247, 0.35) !important;
}
html.dark .pill-attributed,
.dark .pill-attributed {
  background: rgba(236, 72, 153, 0.2) !important;
  color: #f472b6 !important;
  border-color: rgba(236, 72, 153, 0.35) !important;
}
html.dark .pill-default,
.dark .pill-default {
  background: rgba(255, 255, 255, 0.05) !important;
  color: #cbd5e1 !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
}

html.dark .badge-doc.badge-ad,
.dark .badge-doc.badge-ad {
  background: rgba(217, 119, 6, 0.2) !important;
  color: #fcd34d !important;
  border-color: rgba(217, 119, 6, 0.35) !important;
}
html.dark .badge-doc.badge-ar,
.dark .badge-doc.badge-ar {
  background: rgba(16, 185, 129, 0.2) !important;
  color: #6ee7b7 !important;
  border-color: rgba(16, 185, 129, 0.35) !important;
}

html.dark .btn-lines-toggle,
.dark .btn-lines-toggle {
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(185, 121, 204, 0.3) !important;
  color: #cbd5e1 !important;
}

html.dark .drawer-tr,
.dark .drawer-tr {
  background: rgba(0, 0, 0, 0.15) !important;
  border-bottom-color: rgba(185, 121, 204, 0.2) !important;
}
html.dark .drawer-wrapper,
.dark .drawer-wrapper {
  background: #1e293b !important;
  border-color: rgba(185, 121, 204, 0.25) !important;
}
html.dark .drawer-top-bar,
.dark .drawer-top-bar {
  border-bottom-color: rgba(255, 255, 255, 0.08) !important;
}
html.dark .drawer-title,
.dark .drawer-title {
  color: #ffffff !important;
}
html.dark .drawer-subtitle,
.dark .drawer-subtitle {
  color: #cbd5e1 !important;
}
html.dark .metric-pill,
.dark .metric-pill {
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
}
html.dark .metric-pill .m-lbl,
.dark .metric-pill .m-lbl {
  color: #94a3b8 !important;
}
html.dark .metric-budget .m-val,
.dark .metric-budget .m-val {
  color: #93c5fd !important;
}
html.dark .metric-utilized .m-val,
.dark .metric-utilized .m-val {
  color: #6ee7b7 !important;
}
html.dark .metric-pending .m-val,
.dark .metric-pending .m-val {
  color: #fcd34d !important;
}
html.dark .metric-remaining .m-val,
.dark .metric-remaining .m-val {
  color: #38bdf8 !important;
}
html.dark .metric-deficit,
.dark .metric-deficit {
  background: rgba(239, 68, 68, 0.15) !important;
  border-color: rgba(239, 68, 68, 0.35) !important;
}
html.dark .metric-deficit .m-lbl,
.dark .metric-deficit .m-lbl {
  color: #fca5a5 !important;
}
html.dark .metric-deficit .m-val,
.dark .metric-deficit .m-val {
  color: #f87171 !important;
}
html.dark .metric-sep,
.dark .metric-sep {
  color: #64748b !important;
}
html.dark .nested-table,
.dark .nested-table {
  border-color: rgba(185, 121, 204, 0.2) !important;
}
html.dark .nested-table thead tr,
.dark .nested-table thead tr {
  background: rgba(0, 0, 0, 0.25) !important;
  border-bottom-color: rgba(185, 121, 204, 0.2) !important;
}
html.dark .nested-table th,
.dark .nested-table th {
  color: #cbd5e1 !important;
}
html.dark .nested-table tbody tr,
.dark .nested-table tbody tr {
  border-bottom-color: rgba(255, 255, 255, 0.05) !important;
}
html.dark .nested-table td,
.dark .nested-table td {
  color: #e2e8f0 !important;
}
html.dark .rate-track,
.dark .rate-track {
  background: rgba(255, 255, 255, 0.1) !important;
}
html.dark .drawer-empty-state,
.dark .drawer-empty-state {
  background: rgba(0, 0, 0, 0.2) !important;
  color: #94a3b8 !important;
}
html.dark .mandate-tfoot,
.dark .mandate-tfoot,
html.dark .mandate-tfoot tr,
.dark .mandate-tfoot tr,
html.dark .tfoot-cell,
.dark .tfoot-cell {
  background: #151226 !important;
  border-top: 2px solid rgba(192, 132, 252, 0.3) !important;
  color: #f1f5f9 !important;
}

html.dark .tfoot-cell.text-green,
.dark .tfoot-cell.text-green {
  color: #34d399 !important;
}

html.dark .tfoot-cell.text-yellow,
.dark .tfoot-cell.text-yellow {
  color: #fbbf24 !important;
}

html.dark .tfoot-cell.text-blue,
.dark .tfoot-cell.text-blue {
  color: #60a5fa !important;
}

html.dark .tfoot-cell.text-red,
.dark .tfoot-cell.text-red {
  color: #f87171 !important;
}
</style>

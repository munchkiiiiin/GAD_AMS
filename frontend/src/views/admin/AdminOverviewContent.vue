<template>
  <div class="flex flex-col flex-grow">
    <DashboardHeader
      title="Director Overview"
      context="Main Administration"
      :username="user?.username"
    />
    <main class="p-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div v-for="stat in stats" :key="stat.label" class="bg-white p-6 rounded-2xl shadow-sm border border-outline-variant/15 stat-card">
          <div class="flex items-start justify-between mb-4">
            <div :class="`p-3 rounded-xl ${stat.bg}`">
              <span class="material-symbols-outlined" :class="stat.color">{{ stat.icon }}</span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Live Data</span>
          </div>
          <h3 class="text-3xl font-headline font-extrabold text-on-surface mb-1">{{ stat.value }}</h3>
          <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">{{ stat.label }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white p-6 rounded-2xl shadow-sm border border-outline-variant/15">
            <div class="flex justify-between items-center mb-6">
              <h3 class="font-headline font-bold text-lg text-primary">Budget Utilization Trends</h3>
              <select class="bg-surface-container-low border-none rounded-lg text-xs font-bold px-3 py-2 outline-none">
                <option>Current Year (2024)</option>
                <option>Last Year (2023)</option>
              </select>
            </div>
            <div class="h-64 flex items-center justify-center bg-surface-container-low rounded-xl">
              <p class="text-on-surface-variant text-sm font-bold italic">Chart visualization placeholder</p>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl shadow-sm border border-outline-variant/15">
            <div class="flex justify-between items-center mb-6">
              <h3 class="font-headline font-bold text-lg text-primary">Recent Submissions</h3>
              <router-link to="/admin/submitted-list" class="text-xs font-bold text-primary hover:underline">View All</router-link>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead>
                  <tr class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant border-b border-outline-variant/15">
                    <th class="pb-4">Unit/College</th>
                    <th class="pb-4">Document Type</th>
                    <th class="pb-4">Status</th>
                    <th class="pb-4">Date</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                  <tr v-for="sub in recentSubmissions" :key="sub.id" class="text-sm clickable-row">
                    <td class="py-4 font-bold text-on-surface">{{ sub.unit }}</td>
                    <td class="py-4 text-on-surface-variant">{{ sub.type }}</td>
                    <td class="py-4">
                      <span :class="`px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${sub.statusClass}`">
                        {{ sub.status }}
                      </span>
                    </td>
                    <td class="py-4 text-on-surface-variant">{{ sub.date }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="bg-white p-6 rounded-2xl shadow-sm border border-outline-variant/15">
            <h3 class="font-headline font-bold text-lg text-primary mb-6">Upcoming Deadlines</h3>
            <div class="space-y-4">
              <div v-for="deadline in deadlines" :key="deadline.id" class="flex items-start gap-3 p-3 rounded-xl bg-surface-container-low">
                <div class="p-2 rounded-lg bg-white shadow-sm">
                  <span class="material-symbols-outlined text-primary text-lg">event</span>
                </div>
                <div>
                  <h4 class="text-xs font-bold text-on-surface">{{ deadline.title }}</h4>
                  <p class="text-[10px] text-on-surface-variant mt-1">{{ deadline.date }}</p>
                  <span :class="`mt-2 inline-block px-2 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-widest ${deadline.badgeClass}`">
                    {{ deadline.priority }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl shadow-sm border border-outline-variant/15">
            <h3 class="font-headline font-bold text-lg text-primary mb-6">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
              <router-link to="/admin/gad-plan-budget" class="p-4 rounded-xl bg-primary/5 border border-primary/10 hover:bg-primary/10 transition-colors flex flex-col items-center gap-2">
                <span class="material-symbols-outlined text-primary">add_circle</span>
                <span class="text-[10px] font-bold text-primary uppercase">New Plan</span>
              </router-link>
              <router-link to="/admin/annual-report" class="p-4 rounded-xl bg-secondary/5 border border-secondary/10 hover:bg-secondary/10 transition-colors flex flex-col items-center gap-2">
                <span class="material-symbols-outlined text-secondary">summarize</span>
                <span class="text-[10px] font-bold text-secondary uppercase">Report</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import DashboardHeader from '../../components/DashboardHeader.vue';

const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

const stats = [
  { label: 'Active Submissions', value: '24', icon: 'pending_actions', color: 'text-blue-500', bg: 'bg-blue-50' },
  { label: 'Approved Plans', value: '18', icon: 'check_circle', color: 'text-green-500', bg: 'bg-green-50' },
  { label: 'Budget Utilization', value: '82%', icon: 'pie_chart', color: 'text-purple-500', bg: 'bg-purple-50' },
  { label: 'System Alerts', value: '3', icon: 'warning', color: 'text-red-500', bg: 'bg-red-50' }
];

const recentSubmissions = [
  { id: 1, unit: 'College of Nursing', type: 'Annual Report', status: 'Pending Review', statusClass: 'bg-yellow-100 text-yellow-700', date: 'Oct 24, 2024' },
  { id: 2, unit: 'College of Arts & Sciences', type: 'GPB Revision', status: 'Approved', statusClass: 'bg-green-100 text-green-700', date: 'Oct 22, 2024' },
  { id: 3, unit: 'College of Agriculture', type: 'Accomplishment Report', status: 'Draft', statusClass: 'bg-slate-100 text-slate-600', date: 'Oct 21, 2024' }
];

const deadlines = [
  { id: 1, title: 'Annual GAD Report Submission', date: 'Oct 31, 2024', priority: 'Urgent', badgeClass: 'bg-red-100 text-red-700' },
  { id: 2, title: 'GPB 2025 Initial Review', date: 'Nov 15, 2024', priority: 'Warning', badgeClass: 'bg-yellow-100 text-yellow-700' }
];
</script>

<style scoped>
.stat-card { transition: all 0.3s ease; }
.stat-card:hover { transform: translateY(-4px); }
.clickable-row { cursor: pointer; transition: background-color 0.2s ease; }
.clickable-row:hover { background-color: rgba(97, 0, 164, 0.05); }
</style>

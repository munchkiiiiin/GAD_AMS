<template>
  <div class="flex flex-col flex-grow">
    <DashboardHeader
      title="College Overview"
      context="Academic Unit"
      :username="user?.username"
    />
    <main class="p-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
          <header class="mb-8">
            <h1 class="text-3xl font-headline font-extrabold text-primary">OSS Dashboard</h1>
            <p class="text-on-surface-variant text-sm mt-1">Manage your unit's GAD accomplishments and submissions.</p>
          </header>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="stat in stats" :key="stat.label" class="bg-white p-6 rounded-2xl shadow-sm border border-outline-variant/15">
              <div class="flex items-center gap-4">
                <div :class="`p-3 rounded-xl ${stat.bg}`">
                  <span class="material-symbols-outlined" :class="stat.color">{{ stat.icon }}</span>
                </div>
                <div>
                  <h3 class="text-2xl font-headline font-extrabold text-on-surface">{{ stat.value }}</h3>
                  <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider">{{ stat.label }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl shadow-sm border border-outline-variant/15">
            <div class="flex justify-between items-center mb-6">
              <h3 class="font-headline font-bold text-lg text-primary">Your Submissions</h3>
              <router-link to="/college/submit" class="text-xs font-bold text-primary hover:underline">New Submission</router-link>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead>
                  <tr class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant border-b border-outline-variant/15">
                    <th class="pb-4">Control No.</th>
                    <th class="pb-4">Title</th>
                    <th class="pb-4">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                  <tr v-for="sub in submissions" :key="sub.id" class="text-sm clickable-row">
                    <td class="py-4 font-mono text-xs text-on-surface-variant">{{ sub.control }}</td>
                    <td class="py-4 font-bold text-on-surface">{{ sub.title }}</td>
                    <td class="py-4">
                      <span :class="`px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${sub.statusClass}`">
                        {{ sub.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="space-y-8">
          <div class="bg-white p-6 rounded-2xl shadow-sm border border-outline-variant/15">
            <h3 class="font-headline font-bold text-lg text-primary mb-6">Calendar</h3>
            <div class="calendar-grid">
              <div class="grid grid-cols-7 gap-1 text-center mb-2">
                <span v-for="day in ['S', 'M', 'T', 'W', 'T', 'F', 'S']" :key="day" class="text-[10px] font-bold text-on-surface-variant">{{ day }}</span>
              </div>
              <div class="grid grid-cols-7 gap-1 text-center">
                <div v-for="n in 31" :key="n" class="p-2 text-xs rounded-lg hover:bg-primary/10 cursor-pointer transition-colors relative" :class="n === 18 ? 'bg-primary text-white font-bold' : ''">
                  {{ n }}
                  <span v-if="[20, 22, 25].includes(n)" class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-red-500 rounded-full"></span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl shadow-sm border border-outline-variant/15">
            <h3 class="font-headline font-bold text-lg text-primary mb-6">Upcoming Deadlines</h3>
            <div class="space-y-4">
              <div v-for="deadline in deadlines" :key="deadline.id" class="p-4 rounded-xl border border-outline-variant/10 hover:border-primary/30 transition-all bg-surface-container-low group">
                <div class="flex justify-between items-start mb-2">
                  <span :class="`px-2 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-widest ${deadline.badgeClass}`">
                    {{ deadline.type }}
                  </span>
                  <span class="text-[10px] font-bold text-on-surface-variant">{{ deadline.date }}</span>
                </div>
                <h4 class="text-xs font-bold text-on-surface group-hover:text-primary transition-colors">{{ deadline.title }}</h4>
                <p class="text-[10px] text-on-surface-variant mt-2">Control: {{ deadline.control }}</p>
              </div>
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
  { label: 'Approved Plans', value: '12', icon: 'verified', color: 'text-green-500', bg: 'bg-green-50' },
  { label: 'Pending Review', value: '4', icon: 'schedule', color: 'text-yellow-500', bg: 'bg-yellow-50' },
  { label: 'Total Reports', value: '28', icon: 'analytics', color: 'text-blue-500', bg: 'bg-blue-50' }
];

const submissions = [
  { id: 1, control: 'ACC-OSS-2025-001', title: 'GAD Awareness Workshop', status: 'Approved', statusClass: 'bg-green-100 text-green-700' },
  { id: 2, control: 'ACC-OSS-2025-002', title: 'Quarterly Report Q3', status: 'In Review', statusClass: 'bg-blue-100 text-blue-700' },
  { id: 3, control: 'ACC-OSS-2025-003', title: 'Student Wellness Fair', status: 'Revision Required', statusClass: 'bg-red-100 text-red-700' }
];

const deadlines = [
  { id: 1, title: 'Annual Report 2024', control: 'ACC-OSS-2025-003', date: '2025-04-20', type: 'Revision', badgeClass: 'bg-red-100 text-red-700' },
  { id: 2, title: 'Budget Plan Q1 2025', control: 'ACC-OSS-2025-004', date: '2025-04-22', type: 'Submission', badgeClass: 'bg-yellow-100 text-yellow-700' }
];
</script>

<style scoped>
.clickable-row:hover { background-color: rgba(97, 0, 164, 0.05); }
</style>

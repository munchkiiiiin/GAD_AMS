<template>
  <div class="staff-dashboard bg-background min-h-screen flex">
    <DashboardSidebar
      roleLabel="GAD Staff"
      :menuItems="staffMenu"
      @logout="handleLogout"
    />
    <div class="flex-grow ml-64 flex flex-col min-h-screen">
      <router-view />
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import DashboardSidebar from '../components/DashboardSidebar.vue';

const router = useRouter();

const staffMenu = [
  { label: 'New Submission', icon: 'add', href: '/staff/submit' },
  { label: 'Dashboard', icon: 'dashboard', href: '/staff/dashboard' },
  { label: 'Submitted List', icon: 'rate_review', href: '/staff/submitted-list' },
  { label: 'Activity Design List', icon: 'design_services', href: '/staff/ad-list' },
  { label: 'Accomplishment Report List', icon: 'fact_check', href: '/staff/ar-list' },
  { label: 'Archives', icon: 'archive', href: '/staff/archive' },
  { label: 'Mandates', icon: 'gavel', href: '/staff/mandates' },
  { label: 'Report Monitoring', icon: 'description', href: '/staff/reports' },
  { label: 'Budget Monitoring', icon: 'payments', href: '/staff/budget' },
  { label: 'Budget Allocation', icon: 'account_balance', href: '/staff/budget-allocation' },
  { label: 'Plan & Budget', icon: 'account_balance_wallet', href: '/staff/gad-plan-budget' },
  { label: 'User Manual', icon: 'menu_book', href: '/staff/user-manual' },
  { label: 'Data Privacy Policy', icon: 'privacy_tip', href: '/staff/data-privacy-policy' }
];

const handleLogout = async () => {
  try {
    await api.get('logout');
  } catch (err) {
    console.error('Logout failed:', err);
  } finally {
    localStorage.removeItem('user');
    localStorage.removeItem('authToken');
    router.push('/login');
  }
};

onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!user.id || user.role !== 'gad_staff') {
    router.push('/login');
  }
});
</script>

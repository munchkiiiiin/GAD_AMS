<template>
  <div class="admin-dashboard bg-background min-h-screen flex">
    <DashboardSidebar
      roleLabel="Administrator"
      :menuItems="adminMenu"
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

const adminMenu = [
  { label: 'Dashboard', icon: 'dashboard', href: '/admin/dashboard' },
  { label: 'Annual Reports', icon: 'description', href: '/admin/annual-report' },
  { label: 'Submitted List', icon: 'folder', href: '/admin/submitted-list' },
  { label: 'Activity Design List', icon: 'design_services', href: '/admin/ad-list' },
  { label: 'Accomplishment Report List', icon: 'fact_check', href: '/admin/ar-list' },
  { label: 'Plan & Budget', icon: 'account_balance_wallet', href: '/admin/gad-plan-budget' },
  { label: 'Mandates', icon: 'gavel', href: '/admin/mandates' },
  { label: 'Archives', icon: 'archive', href: '/admin/archive' },
  { label: 'Report Monitoring', icon: 'bar_chart', href: '/admin/reports' },
  { label: 'Budget Monitoring', icon: 'payments', href: '/admin/budget' },
  { label: 'User Manual', icon: 'menu_book', href: '/admin/user-manual' },
  { label: 'Data Privacy Policy', icon: 'privacy_tip', href: '/admin/data-privacy-policy' }
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
  if (!user.id || user.role !== 'admin') {
    router.push('/login');
  }
});
</script>

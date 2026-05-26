<template>
  <div class="college-dashboard bg-background min-h-screen flex">
    <DashboardSidebar
      roleLabel="College/Unit"
      :menuItems="collegeMenu"
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

const collegeMenu = [
  { label: 'New Submission', icon: 'add', href: '/college/submit' },
  { label: 'Dashboard', icon: 'dashboard', href: '/college/dashboard' },
  { label: 'Submit Design', icon: 'add_task', href: '/college/submit-design' },
  { label: 'Submit Accomplishment', icon: 'fact_check', href: '/college/submit-report' },
  { label: 'Submitted List', icon: 'list', href: '/college/submitted-list' },
  { label: 'Technical Assist', icon: 'support_agent', href: '/college/tech-assist' },
  { label: 'Mandates', icon: 'gavel', href: '/college/mandates' },
  { label: 'Plan & Budget', icon: 'account_balance_wallet', href: '/college/gad-plan-budget' },
  { label: 'Archives', icon: 'archive', href: '/college/archive' },
  { label: 'User Manual', icon: 'menu_book', href: '/college/user-manual' },
  { label: 'Data Privacy Policy', icon: 'privacy_tip', href: '/college/data-privacy-policy' }
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
  if (!user.id || user.role !== 'college') {
    router.push('/login');
  }
});
</script>

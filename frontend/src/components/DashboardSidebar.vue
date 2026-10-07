<template>
  <aside 
    :class="[
      'w-64 bg-[#1a1a2e] text-white fixed h-full flex flex-col p-6 shadow-xl z-50 transition-transform duration-300 top-0 left-0',
      isOpen ? 'translate-x-0' : '-translate-x-full'
    ]"
  >
    <div class="flex items-center justify-between mb-6 flex-shrink-0 border-b border-white/10 pb-4">
      <div class="flex items-center gap-3">
        <div class="flex items-center">
          <img src="/images/bsulogo.webp" alt="BSU Logo" class="h-9 w-auto object-contain" />
          <img src="/images/gad_logo_enhanced.png" alt="GAD Logo" class="h-10 w-auto object-contain -ml-2 z-10" />
        </div>
        <div class="flex flex-col justify-center leading-none">
          <span class="text-xl font-black text-white tracking-tight">GAD-AMS</span>
        </div>
      </div>
      <button @click="$emit('close')" class="text-white hover:text-slate-300 p-1 transition-colors">
        <span class="material-symbols-outlined font-bold text-2xl">close</span>
      </button>
    </div>

    <!-- User Profile Card (Mobile Only) -->
    <div v-if="user && user.id" class="bg-[#24133d] rounded-2xl p-4 flex items-center gap-3 mb-2 border border-[#371f5c] flex-shrink-0">
      <div :class="['w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg', avatarStyle]">
        <span class="text-lg font-bold text-white">{{ userInitial }}</span>
      </div>
      <div class="flex flex-col overflow-hidden">
        <div class="text-sm font-bold text-white truncate leading-tight">{{ user.full_name || user.name || user.username || 'User Name' }}</div>
        <div class="text-[10px] font-black tracking-widest text-[#c084fc] uppercase mt-1">{{ displayRole }}</div>
      </div>
    </div>

    <nav class="flex-grow space-y-1 overflow-y-auto custom-scrollbar mt-2">
      <template v-for="item in menuItems" :key="item.label">
        <!-- Render normal link if no children -->
        <router-link 
          v-if="!item.children"
          :to="item.href"
          @click="$emit('close')"
          class="flex items-center justify-between p-3 rounded-xl transition-all duration-200"
          :class="$route.path === item.href ? 'bg-primary/20 text-white font-bold' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
        >
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-xl">{{ item.icon }}</span>
            <span class="text-sm">{{ item.label }}</span>
          </div>
          <span v-if="item.badge && item.badge > 0" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ item.badge }}</span>
        </router-link>

        <!-- Render dropdown if has children -->
        <div v-else class="flex flex-col">
          <button 
            @click="toggleExpand(item.label)"
            class="flex items-center justify-between p-3 rounded-xl transition-all duration-200 w-full text-left"
            :class="isChildActive(item) ? 'bg-primary/10 text-white font-bold' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
          >
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-xl">{{ item.icon }}</span>
              <span class="text-sm">{{ item.label }}</span>
              <span v-if="getChildBadgeTotal(item) > 0" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full ml-1">{{ getChildBadgeTotal(item) }}</span>
            </div>
            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': expandedState[item.label] }">
              expand_more
            </span>
          </button>
          
          <!-- Dropdown items -->
          <div v-show="expandedState[item.label]" class="flex flex-col gap-1">
            <router-link 
              v-for="child in item.children" 
              :key="child.label"
              :to="child.href"
              @click="$emit('close')"
              class="flex items-center justify-between pl-10 pr-3 py-3 rounded-xl transition-all duration-200"
              :class="$route.path === child.href ? 'bg-primary/20 text-white font-bold' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
            >
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-xl">{{ child.icon }}</span>
                <span class="text-sm">{{ child.label }}</span>
              </div>
              <span v-if="child.badge && child.badge > 0" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ child.badge }}</span>
            </router-link>
          </div>
        </div>
      </template>
    </nav>

    <div class="mt-auto pt-6 border-t border-white/10 flex flex-col gap-1">
      <router-link :to="settingsPath" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition-colors w-full text-left rounded-xl hover:bg-white/5" :class="$route.path.includes('/settings') ? 'bg-primary/20 text-white font-bold' : ''">
        <span class="material-symbols-outlined text-xl">settings</span>
        <span class="text-sm font-bold">Settings</span>
      </router-link>
      <button @click="$emit('logout')" class="flex items-center gap-3 p-3 text-slate-400 hover:text-red-400 transition-colors w-full text-left rounded-xl hover:bg-white/5">
        <span class="material-symbols-outlined text-xl">logout</span>
        <span class="text-sm font-bold">Sign Out</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed, reactive } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
  roleLabel: { type: String, default: 'User' },
  menuItems: { type: Array, required: true },
  isOpen: { type: Boolean, default: false },
  user: { type: Object, default: () => ({}) }
});

const userInitial = computed(() => {
  const name = props.user?.full_name || props.user?.name || props.user?.username || 'U';
  return name.charAt(0).toUpperCase();
});

const displayRole = computed(() => {
  const r = props.user?.user_role || props.user?.role || 'Role';
  if (r.toLowerCase() === 'non-twg') return 'Proponent';
  return r;
});

const avatarStyle = computed(() => {
  const role = (props.user?.user_role || props.user?.role || '').toLowerCase();
  if (role.includes('admin') || role.includes('director')) return 'bg-gradient-to-br from-purple-500 to-fuchsia-600 shadow-purple-500/20';
  if (role.includes('staff')) return 'bg-gradient-to-br from-emerald-400 to-teal-600 shadow-emerald-500/20';
  if (role === 'twg') return 'bg-gradient-to-br from-blue-400 to-indigo-600 shadow-blue-500/20';
  if (role.includes('non-twg') || role.includes('proponent')) return 'bg-gradient-to-br from-purple-400 to-indigo-500 shadow-purple-500/20';
  return 'bg-gradient-to-br from-slate-400 to-slate-600 shadow-slate-500/20';
});

defineEmits(['logout', 'close']);

const route = useRoute();
const settingsPath = computed(() => {
  const base = route.path.split('/')[1] || 'dashboard';
  return `/${base}/settings`;
});

const expandedState = reactive({});

const toggleExpand = (label) => {
  if (expandedState[label]) {
    expandedState[label] = false;
  } else {
    for (const key in expandedState) {
      expandedState[key] = false;
    }
    expandedState[label] = true;
  }
};

const isChildActive = (item) => {
  if (!item.children) return false;
  return item.children.some(child => route.path === child.href);
};

const getChildBadgeTotal = (item) => {
  if (!item.children) return 0;
  return item.children.reduce((sum, child) => sum + (child.badge || 0), 0);
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255,255,255,0.05);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #b979cc;
  border-radius: 10px;
}
</style>

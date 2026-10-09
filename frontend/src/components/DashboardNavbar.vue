<template>
  <nav class="dashboard-navbar">
    <div class="navbar-inner">
      <!-- Left: Brand -->
      <div class="navbar-brand flex items-center">
        <!-- Mobile Menu Toggle (Left side for mobile) -->
        <button class="xl:hidden mr-3 p-1 flex items-center !text-white active:scale-95 transition-transform" @click.stop.prevent="$emit('toggle-mobile-menu')" style="touch-action: manipulation;">
          <span class="material-symbols-outlined text-3xl font-bold !text-white pointer-events-none">menu</span>
        </button>

        <router-link to="/" class="brand-container flex-shrink-0">
          <div class="brand-logos">
            <img src="/images/bsulogo.webp" alt="BSU Logo" class="brand-logo" />
            <img src="/images/gad_logo_enhanced.png" alt="GAD Logo" class="brand-logo" />
          </div>
          <div class="brand-text flex flex-col justify-center leading-tight">
            <span class="brand-subtitle hidden xl:block">BENGUET STATE UNIVERSITY</span>
            <span class="brand-title text-white">GAD-AMS</span>
          </div>
        </router-link>
      </div>

      <!-- Center: Navigation -->
      <div class="navbar-center hidden xl:flex items-center gap-4">
        <div 
          v-for="(item, index) in menuItems" 
          :key="index"
          class="relative nav-item-wrapper"
          @mouseenter="openDropdown(index)"
          @mouseleave="closeDropdown(index)"
        >
          <!-- Direct Link -->
          <router-link 
            v-if="!item.children"
            :to="item.href"
            class="nav-item"
            :class="{ 'active': isRouteActive(item.href) }"
          >
            <span class="material-symbols-outlined nav-icon">{{ item.icon }}</span>
            <span class="nav-label">{{ item.label }}</span>
          </router-link>

          <!-- Dropdown Trigger -->
          <button 
            v-else
            class="nav-item"
            :class="{ 'active': isChildRouteActive(item.children) }"
          >
            <span class="material-symbols-outlined nav-icon">{{ item.icon }}</span>
            <span class="nav-label">{{ item.label }}</span>
          </button>

          <!-- Dropdown Menu -->
          <transition name="dropdown">
            <div 
              v-if="item.children && activeDropdown === index" 
              class="dropdown-menu"
            >
              <div class="dropdown-header">{{ item.label }}</div>
              <router-link 
                v-for="(child, childIdx) in item.children"
                :key="childIdx"
                :to="child.href"
                class="dropdown-item"
                :class="{ 'active': isRouteActive(child.href) }"
                @click="activeDropdown = null"
              >
                <span class="material-symbols-outlined">{{ child.icon }}</span>
                <span>{{ child.label }}</span>
              </router-link>
            </div>
          </transition>
        </div>
      </div>

      <!-- Right: Actions -->
      <div class="navbar-right">
        <!-- Messages -->
        <router-link :to="messagesLink" class="action-btn" title="Messages">
          <span class="material-symbols-outlined">chat</span>
          <span v-if="unreadMessages > 0" class="absolute -top-1 -right-1.5 bg-[#ef4444] text-white text-[10px] font-bold px-1.5 py-[2px] rounded-full border-2 border-[#13111f] min-w-[18px] text-center leading-none shadow-sm">{{ unreadMessages > 99 ? '99+' : unreadMessages }}</span>
        </router-link>

        <!-- Notifications -->
        <NotificationDropdown class="action-btn-wrapper" />

        <!-- User Profile Dropdown -->
        <div class="relative profile-wrapper" ref="profileDropdownRef">
          <button class="profile-btn" @click="isProfileOpen = !isProfileOpen" :title="currentUser?.full_name || currentUser?.name || 'User'">
            <img 
              v-if="currentUser?.profile_picture" 
              :src="getAvatarUrl(currentUser.profile_picture)" 
              alt="Avatar" 
              class="w-full h-full object-cover rounded-full" 
            />
            <span v-else class="user-initial">{{ userInitial }}</span>
          </button>
          
          <transition name="dropdown">
            <div v-if="isProfileOpen" class="dropdown-menu profile-menu !p-2 !bg-[#13101c] !border-[#2c2041] !rounded-2xl">
              <div class="bg-[#24133d] rounded-[14px] p-4 flex items-center gap-4 mb-2 shadow-inner border border-[#371f5c]">
                <div :class="['w-[52px] h-[52px] rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg overflow-hidden', avatarStyle]">
                  <img 
                    v-if="currentUser?.profile_picture" 
                    :src="getAvatarUrl(currentUser.profile_picture)" 
                    alt="Avatar" 
                    class="w-full h-full object-cover" 
                  />
                  <span v-else class="text-xl font-bold text-white">{{ userInitial }}</span>
                </div>
                <div class="flex flex-col overflow-hidden">
                  <div class="text-[15px] font-bold text-white truncate leading-tight">{{ currentUser?.full_name || currentUser?.name || currentUser?.username || 'User Name' }}</div>
                  <div class="text-[13px] text-purple-200/60 truncate mb-2 mt-0.5">{{ currentUser?.email || 'user@bsu.edu.ph' }}</div>
                  <div :class="['inline-flex items-center gap-1.5 border rounded-full px-3 py-1 w-fit shadow-sm', roleStyle.bgClass, roleStyle.borderClass]">
                    <span :class="['material-symbols-outlined text-[14px]', roleStyle.textClass]">{{ roleStyle.icon }}</span>
                    <span class="text-[10px] font-black tracking-[0.05em] text-white uppercase leading-none mt-[1px]">{{ displayRole }}</span>
                  </div>
                </div>
              </div>
              
              <div class="flex flex-col px-1 pb-1">
                <router-link :to="settingsLink" class="flex items-center gap-4 px-3 py-3 rounded-xl hover:bg-white/5 transition-colors text-white text-[15px] font-semibold no-underline" @click="isProfileOpen = false">
                  <span class="material-symbols-outlined text-[24px] text-[#e9d5ff]">settings</span>
                  <span>Account Settings</span>
                </router-link>
                
                <router-link :to="manualLink" class="flex items-center gap-4 px-3 py-3 rounded-xl hover:bg-white/5 transition-colors text-white text-[15px] font-semibold no-underline" @click="isProfileOpen = false">
                  <span class="material-symbols-outlined text-[24px] text-[#e9d5ff]">help</span>
                  <span>User Manual</span>
                </router-link>
                
                <router-link :to="privacyLink" class="flex items-center gap-4 px-3 py-3 rounded-xl hover:bg-white/5 transition-colors text-white text-[15px] font-semibold no-underline" @click="isProfileOpen = false">
                  <span class="material-symbols-outlined text-[24px] text-[#e9d5ff]">policy</span>
                  <span>Privacy Policy</span>
                </router-link>
                
                <div class="h-px bg-white/5 mx-2 my-1"></div>
                
                <button @click="handleLogout" class="flex items-center gap-4 px-3 py-3 rounded-xl hover:bg-white/5 transition-colors !text-white text-[15px] font-semibold bg-transparent border-none cursor-pointer w-full text-left">
                  <span class="material-symbols-outlined text-[24px] !text-white">logout</span>
                  <span class="!text-white">Sign Out</span>
                </button>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';
import NotificationDropdown from './NotificationDropdown.vue';

const props = defineProps({
  menuItems: { type: Array, required: true },
  user: { type: Object, default: () => ({}) }
});

const emit = defineEmits(['toggle-mobile-menu']);

const route = useRoute();
const router = useRouter();

const activeDropdown = ref(null);
let dropdownTimeout = null;

const isProfileOpen = ref(false);
const profileDropdownRef = ref(null);
const unreadMessages = ref(0);

// Computed base route for dynamic links
const baseRoute = computed(() => '/' + (route.path.split('/')[1] || 'dashboard'));
const messagesLink = computed(() => `${baseRoute.value}/messages`);
const settingsLink = computed(() => `${baseRoute.value}/settings`);
const manualLink = computed(() => `${baseRoute.value}/user-manual`);
const privacyLink = computed(() => `${baseRoute.value}/data-privacy-policy`);

// Reactive local user synced with localStorage events
const localUser = ref(JSON.parse(localStorage.getItem('user') || '{}'));

const handleUserUpdated = () => {
  try {
    localUser.value = JSON.parse(localStorage.getItem('user') || '{}');
  } catch (e) {}
};

const currentUser = computed(() => {
  const merged = { ...props.user, ...localUser.value };
  merged.profile_picture = localUser.value?.profile_picture || props.user?.profile_picture || '';
  return merged;
});

const getAvatarUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  const rawBase = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080/api/';
  const baseUrl = rawBase.replace(/\/api\/?$/, '');
  return `${baseUrl.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
};

const fetchProfileData = async () => {
  try {
    const res = await api.get('/users/profile');
    if (res.data?.success && res.data?.user) {
      const u = res.data.user;
      const stored = JSON.parse(localStorage.getItem('user') || '{}');
      const updated = { ...stored, ...u };
      localStorage.setItem('user', JSON.stringify(updated));
      localUser.value = updated;
    }
  } catch (e) {}
};

const userInitial = computed(() => {
  const name = currentUser.value?.full_name || currentUser.value?.name || currentUser.value?.username || 'U';
  return name.charAt(0).toUpperCase();
});

const displayRole = computed(() => {
  const r = currentUser.value?.user_role || currentUser.value?.role || 'Role';
  if (r.toLowerCase() === 'non-twg') return 'Proponent';
  return r;
});

const roleStyle = computed(() => {
  const role = (currentUser.value?.user_role || currentUser.value?.role || '').toLowerCase();
  
  if (role.includes('admin') || role.includes('director')) {
    return {
      bgClass: 'bg-[#401f71]',
      borderClass: 'border-[#6b32b8]',
      textClass: 'text-purple-100',
      icon: 'local_police'
    };
  } else if (role.includes('staff')) {
    return {
      bgClass: 'bg-emerald-900/80',
      borderClass: 'border-emerald-500/50',
      textClass: 'text-emerald-300',
      icon: 'support_agent'
    };
  } else if (role === 'twg') {
    return {
      bgClass: 'bg-blue-900/80',
      borderClass: 'border-blue-500/50',
      textClass: 'text-blue-300',
      icon: 'school'
    };
  } else if (role.includes('non-twg') || role.includes('proponent')) {
    return {
      bgClass: 'bg-indigo-900/80',
      borderClass: 'border-indigo-500/50',
      textClass: 'text-indigo-300',
      icon: 'person'
    };
  }
  
  return {
    bgClass: 'bg-slate-800',
    borderClass: 'border-slate-600',
    textClass: 'text-slate-300',
    icon: 'badge'
  };
});

const avatarStyle = computed(() => {
  const role = (currentUser.value?.user_role || currentUser.value?.role || '').toLowerCase();
  
  if (role.includes('admin') || role.includes('director')) {
    return 'bg-gradient-to-br from-[#d946ef] to-[#9333ea] shadow-purple-500/20';
  } else if (role.includes('staff')) {
    return 'bg-gradient-to-br from-emerald-400 to-teal-600 shadow-emerald-500/20';
  } else if (role === 'twg') {
    return 'bg-gradient-to-br from-blue-400 to-indigo-600 shadow-blue-500/20';
  } else if (role.includes('non-twg') || role.includes('proponent')) {
    return 'bg-gradient-to-br from-purple-400 to-indigo-500 shadow-purple-500/20';
  }
  
  return 'bg-gradient-to-br from-slate-400 to-slate-600 shadow-slate-500/20';
});

const openDropdown = (index) => {
  clearTimeout(dropdownTimeout);
  activeDropdown.value = index;
};

const closeDropdown = () => {
  dropdownTimeout = setTimeout(() => {
    activeDropdown.value = null;
  }, 100);
};

const isRouteActive = (href) => route.path === href;
const isChildRouteActive = (children) => children?.some(c => route.path === c.href);

const closeProfileOnClickOutside = (e) => {
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
    isProfileOpen.value = false;
  }
};

const fetchUnreadMessages = async () => {
  if (props.user?.id) {
    try {
      const res = await api.get(`/messages/unread-count/${props.user.id}`);
      if (res.data.success) {
        unreadMessages.value = res.data.count;
      }
    } catch (err) {
      // silent fail
    }
  }
};

const handleLogout = async () => {
  try {
    await api.get('logout');
  } catch (err) {
    // proceed anyway
  } finally {
    localStorage.removeItem('user');
    localStorage.removeItem('authToken');
    router.push('/login');
  }
};

let msgInterval;

onMounted(() => {
  document.addEventListener('click', closeProfileOnClickOutside);
  window.addEventListener('user-updated', handleUserUpdated);
  window.addEventListener('storage', handleUserUpdated);
  fetchProfileData();
  fetchUnreadMessages();
  msgInterval = setInterval(fetchUnreadMessages, 10000);
});

onUnmounted(() => {
  document.removeEventListener('click', closeProfileOnClickOutside);
  window.removeEventListener('user-updated', handleUserUpdated);
  window.removeEventListener('storage', handleUserUpdated);
  if (msgInterval) clearInterval(msgInterval);
});
</script>

<style scoped>
.dashboard-navbar {
  width: 100%;
  position: sticky;
  top: 0;
  z-index: 50;
  background: #1a1625;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  border-bottom: 1px solid rgba(185, 121, 204, 0.1);
  font-family: system-ui, -apple-system, sans-serif;
}

.navbar-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  height: 72px;
  max-width: 100%;
  gap: 24px;
}

/* Brand Styles */
.brand-container {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
}
.brand-logos {
  display: flex;
  align-items: center;
}
.brand-logo {
  height: 36px;
  width: auto;
  object-fit: contain;
}
.brand-logo:nth-child(2) {
  margin-left: -6px;
  z-index: 1;
  height: 38px;
}
.brand-text {
  line-height: 1.1;
}
.brand-subtitle {
  font-size: 9px;
  font-weight: 700;
  color: #c084fc;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}
.brand-title {
  font-size: 18px;
  font-weight: 900;
  color: #ffffff;
  letter-spacing: -0.02em;
}

/* Center Navigation Styles */
.navbar-center {
  flex: 1;
  justify-content: center;
}

.nav-item-wrapper {
  height: 100%;
  display: flex;
  align-items: center;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  border: none;
  color: #cbd5e1;
  font-size: 14px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s ease;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
}

.nav-item.active {
  background: rgba(168, 85, 247, 0.15);
  color: #ffffff;
  position: relative;
}

.nav-item.active::after {
  content: '';
  position: absolute;
  bottom: -4px; /* Moved higher to sit right under the nav item */
  left: 10%;
  width: 80%;
  height: 3px;
  background: linear-gradient(90deg, #c026d3, #ec4899);
  border-radius: 3px 3px 0 0;
  box-shadow: 0 -2px 10px rgba(236, 72, 153, 0.4);
}

.nav-icon {
  font-size: 20px;
}

/* Dropdown Menu Styles */
.dropdown-menu {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  min-width: 240px;
  background: #1e1b2e;
  border: 1px solid rgba(185, 121, 204, 0.15);
  border-radius: 12px;
  padding: 8px 0;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
  z-index: 100;
}

/* For Profile dropdown specifically */
.profile-menu {
  right: 0;
  left: auto;
  min-width: 280px;
}

.dropdown-header {
  padding: 8px 16px;
  font-size: 10px;
  font-weight: 800;
  color: #c084fc;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  color: #cbd5e1;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
  background: transparent;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.dropdown-item:hover, .dropdown-item.active {
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
}

.dropdown-item.logout {
  color: #f87171;
}

.dropdown-item.logout:hover {
  background: rgba(248, 113, 113, 0.1);
}

.dropdown-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.05);
  margin: 8px 0;
}

/* Profile dropdown is styled entirely with Tailwind utilities in the template */

/* Right Actions Styles */
.navbar-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.action-btn {
  position: relative;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 50%;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: bold;
  padding: 2px 4px;
  border-radius: 10px;
  min-width: 16px;
  text-align: center;
  border: 2px solid #1a1625;
}

.action-btn-wrapper :deep(.action-btn) {
  /* Ensure NotificationDropdown action button inherits same styles */
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 50%;
  color: #cbd5e1;
  cursor: pointer;
}
.action-btn-wrapper :deep(.action-btn:hover) {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}
.action-btn-wrapper :deep(.notification-badge) {
  position: absolute;
  top: -2px;
  right: -2px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: bold;
  padding: 2px 4px;
  border-radius: 10px;
  min-width: 16px;
  text-align: center;
  border: 2px solid #1a1625;
}

.profile-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  border: 2px solid transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  padding: 0;
  overflow: hidden;
}

.profile-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 0 15px rgba(236, 72, 153, 0.4);
}

.user-initial {
  color: white;
  font-weight: 700;
  font-size: 16px;
}

/* Transitions */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

/* Responsive Mobile Scaling */
@media (max-width: 640px) {
  .navbar-inner {
    padding: 0 12px;
  }
  .navbar-right {
    gap: 8px;
  }
  .action-btn,
  .action-btn-wrapper :deep(.action-btn),
  .profile-btn {
    width: 32px !important;
    height: 32px !important;
  }
  .action-btn .material-symbols-outlined,
  .action-btn-wrapper :deep(.material-symbols-outlined) {
    font-size: 20px;
  }
  .user-initial {
    font-size: 14px;
  }
  .brand-logo {
    height: 28px;
  }
  .brand-logo:nth-child(2) {
    height: 30px;
  }
  .brand-title {
    font-size: 14px;
  }
  .badge, .action-btn-wrapper :deep(.notification-badge) {
    transform: scale(0.85);
    top: -4px;
    right: -4px;
  }
}

</style>

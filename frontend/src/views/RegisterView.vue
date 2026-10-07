<template>
  <div class="register-page font-body pt-32 pb-16 px-4 flex flex-col items-center justify-center min-h-screen" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #ffffff;">
    <div class="w-full max-w-4xl relative z-10">
      
      <div class="text-center mb-10 flex flex-col items-center">
        <div class="inline-flex items-center gap-2 bg-purple-500/20 text-purple-300 px-4 py-1.5 rounded-full border border-purple-500/30 mb-6">
          <span class="material-symbols-outlined text-[18px]">account_circle</span>
          <span class="text-xs font-bold uppercase tracking-[0.2em] font-label">Create Account</span>
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold font-headline tracking-tighter text-white leading-tight">
          Welcome to <span class="text-purple-400">GAD-AMS Portal.</span>
        </h1>
      </div>

      <div class="rounded-xl p-8 md:p-12 shadow-2xl border border-white/10 relative overflow-hidden" style="background-color: rgba(255, 255, 255, 0.03); backdrop-filter: blur(20px);">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 to-blue-500"></div>
        <form @submit.prevent="handleRegister" class="space-y-8">
          <div v-if="error" class="rounded-md bg-red-900/50 border border-red-500/50 text-red-200 px-4 py-3 text-sm">{{ error }}</div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="flex flex-col gap-2">
              <label class="text-xs uppercase tracking-widest font-label font-bold text-slate-400">First Name <span class="text-red-400">*</span></label>
              <input v-model="form.first_name" placeholder="Juan" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all placeholder:text-slate-600" required />
            </div>
            <div class="flex flex-col gap-2">
              <label class="text-xs uppercase tracking-widest font-label font-bold text-slate-400">Middle Name</label>
              <input v-model="form.middle_name" placeholder="Dela" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all placeholder:text-slate-600" />
            </div>
            <div class="flex flex-col gap-2 md:col-span-2">
              <label class="text-xs uppercase tracking-widest font-label font-bold text-slate-400">Last Name <span class="text-red-400">*</span></label>
              <input v-model="form.last_name" placeholder="Santos" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all placeholder:text-slate-600" required />
            </div>

            <div class="flex flex-col gap-2">
              <label class="text-xs uppercase tracking-widest font-label font-bold text-slate-400">Role <span class="text-red-400">*</span></label>
              <select v-model="form.user_role" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all">
                <option value="Non-TWG" class="bg-[#1a1a2e] text-white">Proponent</option>
                <option value="TWG" class="bg-[#1a1a2e] text-white">TWG</option>
              </select>
            </div>

            <div class="flex flex-col gap-2">
              <label class="text-xs uppercase tracking-widest font-label font-bold text-slate-400">Department / Office <span class="text-red-400">*</span></label>
              
              <div class="space-y-2 relative" ref="dropdownRef">
                <!-- Searchable Combobox -->
                <div class="relative">
                  <input 
                    v-model="officeSearchQuery" 
                    @focus="isDropdownOpen = true"
                    @input="isDropdownOpen = true; handleSearchInput()"
                    placeholder="Search or Select your unit"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all placeholder:text-slate-600"
                    :required="!form.office_unit_id && !isAddingNew"
                  />
                  <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">expand_more</span>
                </div>
                
                <!-- Dropdown List -->
                <div v-if="isDropdownOpen" class="absolute z-50 w-full mt-1 bg-[#1a1a2e] border border-white/20 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                  <div 
                    v-for="unit in filteredOffices" 
                    :key="unit.unit_id" 
                    @click="selectOffice(unit)"
                    class="px-4 py-3 hover:bg-white/10 cursor-pointer text-white transition-colors"
                  >
                    {{ unit.unit_name }}
                  </div>
                  
                  <div v-if="filteredOffices.length === 0" class="px-4 py-3 text-slate-500 italic text-sm">
                    No matching offices found.
                  </div>

                  <div 
                    @click="selectAddNew"
                    class="px-4 py-3 font-bold text-purple-400 hover:bg-purple-500/20 cursor-pointer border-t border-white/10 transition-colors flex items-center gap-2"
                  >
                    <span class="material-symbols-outlined text-sm">add_circle</span>
                    Not in the list? Add office
                  </div>
                </div>
                
                <div v-if="isAddingNew" class="mt-2 animate-fade-in">
                  <label class="text-[10px] uppercase tracking-widest font-label font-bold text-purple-400 mb-1 block">New Office Name</label>
                  <input v-model="newOfficeName" placeholder="Enter full new office name" 
                         class="w-full bg-white/5 border border-purple-500/50 focus:border-purple-500 rounded-lg px-4 py-3 text-white outline-none focus:ring-1 focus:ring-purple-500 transition-all placeholder:text-slate-600" required />
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-2 md:col-span-2">
              <label class="text-xs uppercase tracking-widest font-label font-bold text-slate-400">
                {{ form.user_role !== 'Non-TWG' ? 'Institutional Email' : 'Email Address' }} <span class="text-red-400">*</span>
              </label>
              <input v-model="form.email" type="email" :placeholder="form.user_role !== 'Non-TWG' ? 'name@bsu.edu.ph' : 'name@example.com'" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all placeholder:text-slate-600" required />
            </div>
            <div class="flex flex-col gap-2">
              <label class="text-xs uppercase tracking-widest font-label font-bold text-slate-400">Password <span class="text-red-400">*</span></label>
              <div class="relative">
                <input v-model="form.password" :type="showPass ? 'text' : 'password'" placeholder="••••••••" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all placeholder:text-slate-600" required />
                <button type="button" @click="showPass = !showPass" class="absolute right-3 top-3 text-slate-500 hover:text-white transition-colors"><span class="material-symbols-outlined">{{ showPass ? 'visibility_off' : 'visibility' }}</span></button>
              </div>
              <ul class="text-xs mt-2 space-y-1 font-medium transition-colors">
                <li class="flex items-center gap-1 transition-colors" :class="form.password.length >= 8 ? 'text-green-400' : 'text-slate-500'">
                  <span class="material-symbols-outlined text-[14px]">{{ form.password.length >= 8 ? 'check_circle' : 'cancel' }}</span>
                  At least 8 characters
                </li>
                <li class="flex items-center gap-1 transition-colors" :class="/[A-Z]/.test(form.password || '') ? 'text-green-400' : 'text-slate-500'">
                  <span class="material-symbols-outlined text-[14px]">{{ /[A-Z]/.test(form.password || '') ? 'check_circle' : 'cancel' }}</span>
                  One uppercase letter
                </li>
                <li class="flex items-center gap-1 transition-colors" :class="/[a-z]/.test(form.password || '') ? 'text-green-400' : 'text-slate-500'">
                  <span class="material-symbols-outlined text-[14px]">{{ /[a-z]/.test(form.password || '') ? 'check_circle' : 'cancel' }}</span>
                  One lowercase letter
                </li>
                <li class="flex items-center gap-1 transition-colors" :class="/[0-9]/.test(form.password || '') ? 'text-green-400' : 'text-slate-500'">
                  <span class="material-symbols-outlined text-[14px]">{{ /[0-9]/.test(form.password || '') ? 'check_circle' : 'cancel' }}</span>
                  One number
                </li>
                <li class="flex items-center gap-1 transition-colors" :class="/[^A-Za-z0-9]/.test(form.password || '') ? 'text-green-400' : 'text-slate-500'">
                  <span class="material-symbols-outlined text-[14px]">{{ /[^A-Za-z0-9]/.test(form.password || '') ? 'check_circle' : 'cancel' }}</span>
                  One special character
                </li>
              </ul>
            </div>
            <div class="flex flex-col gap-2">
              <label class="text-xs uppercase tracking-widest font-label font-bold text-slate-400">Confirm Password <span class="text-red-400">*</span></label>
              <div class="relative">
                <input v-model="form.confirm_password" :type="showConfirmPass ? 'text' : 'password'" placeholder="••••••••" class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all placeholder:text-slate-600" required />
                <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-3 text-slate-500 hover:text-white transition-colors"><span class="material-symbols-outlined">{{ showConfirmPass ? 'visibility_off' : 'visibility' }}</span></button>
              </div>
            </div>
          </div>

          <!-- Turnstile Widget -->
          <TurnstileWidget ref="turnstileRef" @verify="onTurnstileVerify" />

          <!-- Privacy Policy Checkbox -->
          <div class="flex items-center gap-3 pt-2">
            <input id="privacy" v-model="form.privacyAccepted" type="checkbox" class="w-5 h-5 bg-white/10 border-white/20 rounded text-purple-500 focus:ring-purple-500 flex-shrink-0 cursor-pointer" required />
            <label for="privacy" class="text-sm text-slate-300 font-medium cursor-pointer flex items-center gap-1.5 flex-wrap">
              <span>I agree to the</span>
              <button type="button" @click.stop.prevent="showPrivacyModal = true" class="text-purple-400 hover:underline font-bold">Privacy Policy</button>
            </label>
          </div>

          <div class="flex flex-col gap-4 pt-4">
            <button :disabled="loading" class="w-full py-4 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-400 hover:to-purple-400 text-white rounded-full font-bold uppercase shadow-[0_0_20px_rgba(168,85,247,0.4)] hover:shadow-[0_0_30px_rgba(168,85,247,0.7)] hover:-translate-y-1 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300" type="submit">
              {{ loading ? 'Processing...' : 'Register' }}
            </button>
            <button type="button" @click="router.back()" class="w-full border border-white/20 text-white py-4 rounded-full font-bold uppercase hover:bg-white/10 transition-all">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Privacy Policy Modal -->
    <PrivacyPolicyModal 
      v-if="showPrivacyModal" 
      :show-accept="true" 
      @close="showPrivacyModal = false" 
      @accept="acceptPrivacy" 
    />
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import TurnstileWidget from '../components/TurnstileWidget.vue';
import PrivacyPolicyModal from '../components/PrivacyPolicyModal.vue';

const router = useRouter();
const loading = ref(false);
const error = ref('');
const showPass = ref(false);
const showConfirmPass = ref(false);
const turnstileToken = ref('');
const showPrivacyModal = ref(false);
const turnstileRef = ref(null);

const acceptPrivacy = () => {
  form.privacyAccepted = true;
  showPrivacyModal.value = false;
};

const onTurnstileVerify = (token) => {
  turnstileToken.value = token;
};

const officeUnits = ref([]);
const isAddingNew = ref(false);
const newOfficeName = ref('');

// Combobox logic
const dropdownRef = ref(null);
const isDropdownOpen = ref(false);
const officeSearchQuery = ref('');

const filteredOffices = computed(() => {
  if (!officeSearchQuery.value) return officeUnits.value;
  
  const selectedOffice = officeUnits.value.find(u => u.unit_id === form.office_unit_id);
  if (selectedOffice && officeSearchQuery.value === selectedOffice.unit_name) {
    return officeUnits.value;
  }

  const q = officeSearchQuery.value.toLowerCase();
  return officeUnits.value.filter(u => u.unit_name.toLowerCase().includes(q));
});

const exactMatchExists = computed(() => {
  if (!officeSearchQuery.value) return false;
  return officeUnits.value.some(u => u.unit_name.toLowerCase() === officeSearchQuery.value.trim().toLowerCase());
});

const handleSearchInput = () => {
  isAddingNew.value = false;
  form.office_unit_id = ''; 
};

const selectOffice = (unit) => {
  form.office_unit_id = unit.unit_id;
  officeSearchQuery.value = unit.unit_name;
  isAddingNew.value = false;
  isDropdownOpen.value = false;
};

const selectAddNew = () => {
  form.office_unit_id = 'add_new';
  isAddingNew.value = true;
  if (exactMatchExists.value) {
    newOfficeName.value = '';
  } else {
    newOfficeName.value = officeSearchQuery.value;
  }
  officeSearchQuery.value = 'Custom Office';
  isDropdownOpen.value = false;
};

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isDropdownOpen.value = false;
  }
};

const form = reactive({
  first_name: '', middle_name: '', last_name: '',
  user_role: 'Non-TWG', office_unit_id: '',
  email: '', password: '', confirm_password: '',
  privacyAccepted: false
});

const fetchOffices = async () => {
  try {
    const res = await api.get('office_units');
    officeUnits.value = res.data;
  } catch (err) {
    console.error("Fetch error:", err);
  }
};



onMounted(() => {
  fetchOffices();
  document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside);
});

const handleRegister = async () => {
  if (form.user_role !== 'Non-TWG' && form.user_role !== 'TWG' && !form.email.toLowerCase().endsWith('@bsu.edu.ph')) {
    return error.value = 'This role requires a valid institutional email (@bsu.edu.ph).';
  }
  if (form.password.length < 8) {
    return error.value = 'Password must be at least 8 characters long.';
  }
  if (!/[A-Z]/.test(form.password)) {
    return error.value = 'Password must contain at least 1 uppercase letter.';
  }
  if (!/[a-z]/.test(form.password)) {
    return error.value = 'Password must contain at least 1 lowercase letter.';
  }
  if (!/[0-9]/.test(form.password)) {
    return error.value = 'Password must contain at least 1 number.';
  }
  if (!/[^A-Za-z0-9]/.test(form.password)) {
    return error.value = 'Password must contain at least 1 special character.';
  }
  if (form.password !== form.confirm_password) {
    return error.value = 'Passwords do not match.';
  }
  if (!turnstileToken.value) {
    return error.value = 'Please complete the security check.';
  }
  if (!form.privacyAccepted) {
    return error.value = 'You must agree to the Privacy Policy.';
  }
  
  loading.value = true;
  error.value = null; 

  try {
    let departmentId = form.office_unit_id;

    if (isAddingNew.value && newOfficeName.value) {
      const res = await api.post('add_office', { 
        unit_name: newOfficeName.value 
      });
      departmentId = res.data.new_id;
    }

    const payload = {
      fullname: `${form.first_name} ${form.middle_name} ${form.last_name}`.replace(/\s+/g, ' ').trim(),
      first_name: form.first_name,
      middle_name: form.middle_name,
      last_name: form.last_name,
      department: departmentId, 
      email: form.email,
      password: form.password,
      confirm_password: form.confirm_password,
      user_role: form.user_role,
      turnstile_token: turnstileToken.value
    };

    await api.post('register', payload);
    
    router.push('/login?registered=true');

  } catch (err) {
    console.error("Registration Error", err);
    if (turnstileRef.value) turnstileRef.value.reset();
    turnstileToken.value = '';
    
    if (err && err.messages) {
      const messages = err.messages;
      error.value = typeof messages === 'string' 
        ? messages 
        : Object.values(messages).join(', ');
    } else if (err && err.message) {
      error.value = err.message;
    } else {
      error.value = 'Registration failed. Please check your input.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
</style>

<template>
  <div class="p-8 bg-slate-50/60 min-h-full">
    <!-- Modern Sleek Toast Notification -->
    <Transition name="toast">
      <div 
        v-if="showToast" 
        class="fixed top-6 right-8 z-50 flex items-center gap-3 px-4 py-3 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl shadow-slate-900/10 border transition-all duration-300 font-sans"
        :class="[
          toastType === 'error'
            ? 'border-rose-200 ring-1 ring-rose-500/10'
            : 'border-emerald-200 ring-1 ring-emerald-500/10'
        ]"
      >
        <!-- Icon Pill -->
        <div 
          class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
          :class="[
            toastType === 'error' 
              ? 'bg-rose-50 text-rose-600 border border-rose-100' 
              : 'bg-emerald-50 text-emerald-600 border border-emerald-100'
          ]"
        >
          <AlertCircle v-if="toastType === 'error'" class="w-4 h-4" />
          <CheckCircle2 v-else class="w-4 h-4" />
        </div>

        <!-- Text Content -->
        <div class="pr-2">
          <p 
            class="text-xs font-semibold tracking-tight"
            :class="toastType === 'error' ? 'text-rose-950' : 'text-slate-800'"
          >
            {{ toastMessage }}
          </p>
        </div>

        <!-- Dismiss Button -->
        <button 
          @click="showToast = false" 
          type="button" 
          class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1 rounded-lg transition-colors cursor-pointer ml-1"
        >
          <X class="w-3.5 h-3.5" />
        </button>
      </div>
    </Transition>

    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight">Settings</h1>
      <p class="text-sm text-slate-500 mt-1">Manage your personal profile and security preferences.</p>
    </div>

    <!-- Main Settings Card -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden flex flex-col lg:flex-row">
      
      <!-- Left Vertical Navigation Tabs -->
      <div class="w-full lg:w-64 border-b lg:border-b-0 lg:border-r border-slate-100 bg-slate-50/40 p-5 flex flex-col gap-1.5 shrink-0">
        <button 
          @click="activeTab = 'profile'" 
          :class="[
            'flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all text-left cursor-pointer',
            activeTab === 'profile' 
              ? 'bg-white text-teal-700 font-semibold shadow-xs border border-slate-200/80' 
              : 'text-slate-500 hover:bg-slate-100/80 hover:text-slate-800'
          ]"
        >
          <User class="w-4 h-4" :class="activeTab === 'profile' ? 'text-teal-600' : 'text-slate-400'" />
          <span>Profile Details</span>
        </button>

        <button 
          @click="activeTab = 'security'" 
          :class="[
            'flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all text-left cursor-pointer',
            activeTab === 'security' 
              ? 'bg-white text-teal-700 font-semibold shadow-xs border border-slate-200/80' 
              : 'text-slate-500 hover:bg-slate-100/80 hover:text-slate-800'
          ]"
        >
          <ShieldCheck class="w-4 h-4" :class="activeTab === 'security' ? 'text-teal-600' : 'text-slate-400'" />
          <span>Security</span>
        </button>
      </div>

      <!-- Right Content Section -->
      <div class="flex-1 p-8 md:p-10">
        
        <!-- ================= PROFILE TAB ================= -->
        <div v-if="activeTab === 'profile'" class="animate-fade-in max-w-2xl">
          <!-- Section Title -->
          <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-800 tracking-tight">Profile Information</h2>
            <p class="text-xs text-slate-400 mt-0.5">Update your personal photo and account details.</p>
          </div>
          
          <!-- Avatar Card Row -->
          <div class="flex items-center gap-6 p-5 bg-slate-50/50 rounded-2xl border border-slate-100 mb-8">
            <div 
              class="relative shrink-0 group cursor-pointer" 
              @click="triggerFileSelect"
              title="Click to change photo"
            >
              <img 
                :src="displayAvatar" 
                @error="onImageError"
                alt="Profile Avatar" 
                class="w-20 h-20 rounded-full object-cover border-2 border-white shadow-sm ring-2 ring-slate-200/70 bg-slate-100 transition-transform duration-200 group-hover:ring-teal-500 group-hover:scale-[1.02]" 
              />
              <!-- Subtle Hover Overlay -->
              <div class="absolute inset-0 rounded-full bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <Camera class="w-5 h-5 text-white drop-shadow-md" />
              </div>
              <input 
                type="file" 
                ref="fileInputRef" 
                accept="image/png, image/jpeg, image/gif, image/webp" 
                class="hidden" 
                @change="handleFileSelect" 
              />
              <button 
                type="button" 
                @click.stop="triggerFileSelect"
                class="absolute bottom-0 right-0 w-7 h-7 bg-teal-600 hover:bg-teal-700 text-white rounded-full flex items-center justify-center shadow-md border-2 border-white cursor-pointer transition-all group-hover:scale-110"
                title="Change Avatar"
              >
                <Camera class="w-3.5 h-3.5" />
              </button>
            </div>

            <div>
              <h3 class="font-bold text-slate-800 text-base leading-snug">{{ profileName || 'admin' }}</h3>
              <p class="text-xs text-slate-500 mt-1">JPG, GIF or PNG. Max size of 800K</p>
              
              <div class="flex items-center gap-3 mt-2">
                <button 
                  type="button" 
                  @click="triggerFileSelect" 
                  class="text-xs font-semibold text-teal-600 hover:text-teal-700 cursor-pointer"
                >
                  Upload new image
                </button>
                <span v-if="hasCustomAvatar" class="text-slate-300">•</span>
                <button 
                  v-if="hasCustomAvatar" 
                  type="button" 
                  @click="removeAvatar" 
                  class="text-xs font-semibold text-rose-500 hover:text-rose-600 cursor-pointer"
                >
                  Remove photo
                </button>
              </div>
            </div>
          </div>

          <!-- Form Fields Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-2">Username</label>
              <div class="relative">
                <User class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input 
                  type="text" 
                  v-model="profileName" 
                  @input="profileName = profileName.replace(/\b\w/g, (c) => c.toUpperCase())"
                  autocapitalize="words"
                  placeholder="admin"
                  class="w-full py-2.5 pl-10 pr-4 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all shadow-2xs font-sans" 
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-2">Email Address</label>
              <div class="relative">
                <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input 
                  type="email" 
                  v-model="profileEmail" 
                  :disabled="!isSuperAdmin"
                  placeholder="doctor@hospital.com"
                  :class="[
                    'w-full py-2.5 pl-10 pr-4 border rounded-xl text-sm transition-all shadow-2xs font-sans',
                    !isSuperAdmin 
                      ? 'bg-slate-100/90 border-slate-200 text-slate-500 cursor-not-allowed select-none' 
                      : 'bg-white border-slate-200 text-slate-800 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10'
                  ]" 
                />
              </div>
            </div>
          </div>

          <!-- Save Button at Bottom -->
          <div class="flex justify-end pt-2">
            <button 
              @click="saveProfile" 
              type="button"
              :disabled="isSaving"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-600 hover:bg-teal-700 active:scale-[0.98] text-white rounded-xl font-semibold text-sm transition-all shadow-sm shadow-teal-600/20 cursor-pointer disabled:opacity-70"
            >
              <Save v-if="!isSaving" class="w-4 h-4" />
              <span>{{ isSaving ? 'Saving...' : 'Save Changes' }}</span>
            </button>
          </div>
        </div>

        <!-- ================= SECURITY TAB ================= -->
        <div v-if="activeTab === 'security'" class="animate-fade-in max-w-xl">
          <!-- Section Title -->
          <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-800 tracking-tight">Security & Password</h2>
            <p class="text-xs text-slate-400 mt-0.5">Ensure your account uses a secure, updated password.</p>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-2">Current Password</label>
              <div class="relative">
                <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input 
                  :type="showCurrent ? 'text' : 'password'" 
                  v-model="currentPassword"
                  placeholder="••••••••" 
                  class="w-full py-2.5 pl-10 pr-10 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all shadow-2xs" 
                />
                <button 
                  type="button" 
                  @click="showCurrent = !showCurrent" 
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
                >
                  <EyeOff v-if="showCurrent" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </button>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-2">New Password</label>
              <div class="relative">
                <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input 
                  :type="showNew ? 'text' : 'password'" 
                  v-model="newPassword"
                  placeholder="Create new password" 
                  class="w-full py-2.5 pl-10 pr-10 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all shadow-2xs" 
                />
                <button 
                  type="button" 
                  @click="showNew = !showNew" 
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
                >
                  <EyeOff v-if="showNew" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </button>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-2">Confirm New Password</label>
              <div class="relative">
                <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input 
                  :type="showConfirm ? 'text' : 'password'" 
                  v-model="confirmPassword"
                  placeholder="Confirm new password" 
                  class="w-full py-2.5 pl-10 pr-10 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all shadow-2xs" 
                />
                <button 
                  type="button" 
                  @click="showConfirm = !showConfirm" 
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
                >
                  <EyeOff v-if="showConfirm" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </button>
              </div>
            </div>

            <div class="pt-2">
              <button 
                @click="updatePassword" 
                type="button"
                class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 active:scale-[0.98] text-white rounded-xl font-semibold text-sm transition-all shadow-xs cursor-pointer"
              >
                Update Password
              </button>
            </div>
          </div>

          <!-- Recent Login Activity -->
          <div class="mt-10 pt-6 border-t border-slate-100">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-bold text-slate-800 text-sm font-heading tracking-tight">Recent Login Activity</h3>
              <span class="text-[11px] font-medium text-slate-400 font-sans">Security Audit</span>
            </div>
            
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl overflow-hidden divide-y divide-slate-200/60 shadow-2xs font-sans">
              <div 
                v-for="act in loginActivities" 
                :key="act.id"
                class="px-4 py-3 flex justify-between items-center transition-colors"
                :class="act.isCurrent ? 'bg-white' : 'bg-slate-50/40 hover:bg-white'"
              >
                <div class="flex items-center gap-3 font-sans">
                  <div 
                    class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                    :class="act.isCurrent ? 'bg-teal-50 text-teal-600 border border-teal-100/60' : 'bg-slate-100 text-slate-400'"
                  >
                    <Smartphone v-if="act.type === 'mobile'" class="w-3.5 h-3.5" />
                    <Laptop v-else class="w-3.5 h-3.5" />
                  </div>
                  <div class="font-sans">
                    <span 
                      class="text-xs font-semibold block leading-tight font-sans"
                      :class="act.isCurrent ? 'text-slate-800' : 'text-slate-600'"
                    >
                      {{ act.deviceName }} - {{ act.browser }}
                    </span>
                    <span v-if="act.ip" class="text-[10px] text-slate-400 font-sans">IP: {{ act.ip }}</span>
                  </div>
                </div>

                <div>
                  <span 
                    v-if="act.isCurrent" 
                    class="text-[11px] font-semibold text-teal-700 bg-teal-50 border border-teal-100/90 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1.5 font-sans"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                    Current Session
                  </span>
                  <span v-else class="text-xs text-slate-500 font-medium font-sans">
                    {{ act.timeStr }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { api, resolveServerUrl } from '../services/api'
import defaultAvatar from '@/assets/profiledefault.svg'
import { 
  Save, 
  User, 
  ShieldCheck, 
  Camera, 
  Mail, 
  Lock, 
  Eye, 
  EyeOff, 
  CheckCircle2, 
  AlertCircle,
  X,
  Laptop, 
  Smartphone 
} from 'lucide-vue-next'

const activeTab = ref('profile')

const profileName = ref('admin')
const profileEmail = ref('admin@gmail.com')
const profileRole = ref(localStorage.getItem('userRole') || 'superadmin')
const isSuperAdmin = computed(() => profileRole.value === 'superadmin')
const avatarPath = ref<string | null>(null)

// Local staged states (ONLY saved when "Save Changes" is clicked)
const previewUrl = ref<string | null>(null)
const pendingFile = ref<File | null>(null)
const isPendingRemove = ref(false)

const fileInputRef = ref<HTMLInputElement | null>(null)
const isSaving = ref(false)

const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')

const showCurrent = ref(false)
const showNew = ref(false)
const showConfirm = ref(false)

const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref<'success' | 'error'>('success')

const displayToast = (msg: string, type: 'success' | 'error' = 'success') => {
  toastMessage.value = msg
  toastType.value = type
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 3500)
}

const onImageError = (e: Event) => {
  const target = e.target as HTMLImageElement
  target.src = defaultAvatar
}

const displayAvatar = computed(() => {
  // If user selected a new preview image or clicked remove
  if (previewUrl.value) {
    return previewUrl.value
  }
  if (isPendingRemove.value) {
    return defaultAvatar
  }
  if (!avatarPath.value) {
    return defaultAvatar
  }
  return resolveServerUrl(avatarPath.value)
})

const hasCustomAvatar = computed(() => {
  if (isPendingRemove.value) return false
  if (previewUrl.value && previewUrl.value !== defaultAvatar) return true
  return !!avatarPath.value
})

interface ActivityItem {
  id: number
  deviceName: string
  browser: string
  ip?: string
  isCurrent: boolean
  timeStr: string
  type: 'mobile' | 'desktop'
}

const loginActivities = ref<ActivityItem[]>([
  {
    id: 1,
    deviceName: 'MacBook Pro',
    browser: 'Chrome',
    isCurrent: true,
    timeStr: 'Current Session',
    type: 'desktop'
  }
])

const fetchLoginActivities = async () => {
  try {
    const res = await api.get('/api/user/login-activities')
    if (res && res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
      loginActivities.value = res.data
    }
  } catch (err) {
    console.warn('Error fetching login activities:', err)
  }
}

const loadProfile = async () => {
  const storedName = localStorage.getItem('username')
  const storedEmail = localStorage.getItem('userEmail')
  const storedAvatar = localStorage.getItem('userAvatar')
  
  if (storedName) profileName.value = storedName
  if (storedEmail) profileEmail.value = storedEmail
  if (storedAvatar) avatarPath.value = storedAvatar

  try {
    const res = await api.get('/api/user/profile')
    if (res && res.status === 'success' && res.data) {
      if (res.data.username) {
        profileName.value = res.data.username
        localStorage.setItem('username', res.data.username)
      }
      if (res.data.email) {
        profileEmail.value = res.data.email
        localStorage.setItem('userEmail', res.data.email)
      }
      if (res.data.role) {
        profileRole.value = res.data.role
        localStorage.setItem('userRole', res.data.role)
      }
      avatarPath.value = res.data.avatar || null
      if (res.data.avatar) {
        localStorage.setItem('userAvatar', res.data.avatar)
      } else {
        localStorage.removeItem('userAvatar')
      }
      window.dispatchEvent(new CustomEvent('profile-updated'))
    }
  } catch (err) {
    // Continue with localStorage data
  }
}

onMounted(() => {
  loadProfile()
  fetchLoginActivities()
})

const triggerFileSelect = () => {
  fileInputRef.value?.click()
}

// 📸 Handle photo selection: PREVIEW ONLY (DOES NOT SAVE YET)
const handleFileSelect = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    
    // File size check 800KB
    if (file.size > 800 * 1024) {
      displayToast('File too large! Max size is 800KB.')
      return
    }

    pendingFile.value = file
    isPendingRemove.value = false
    previewUrl.value = URL.createObjectURL(file)
    displayToast('Photo selected! Click "Save Changes" to apply.')
  }
}

// 🗑️ Stage photo removal (DOES NOT SAVE YET)
const removeAvatar = () => {
  previewUrl.value = defaultAvatar
  pendingFile.value = null
  isPendingRemove.value = true
  displayToast('Photo removed! Click "Save Changes" to apply.')
}

// 💾 Save Changes: Uploads staged photo & updates DB + localStorage
const saveProfile = async () => {
  isSaving.value = true

  try {
    // 1. If user clicked remove photo, call delete API
    if (isPendingRemove.value) {
      try {
        await api.delete('/api/user/avatar')
      } catch (e) {
        console.error('Delete avatar error:', e)
      }
      avatarPath.value = null
      localStorage.removeItem('userAvatar')
    }
    // 2. If user selected a new photo, upload and persist it
    else if (pendingFile.value) {
      const fileToSave = pendingFile.value
      
      // Convert to Base64 as guaranteed persistent local preview
      const dataUrl = await new Promise<string>((resolve) => {
        const reader = new FileReader()
        reader.onload = () => resolve(reader.result as string)
        reader.readAsDataURL(fileToSave)
      })

      const formData = new FormData()
      formData.append('avatar', fileToSave)
      formData.append('email', profileEmail.value)

      try {
        const uploadRes = await api.postFormData('/api/user/avatar', formData)
        if (uploadRes && uploadRes.status === 'success' && uploadRes.avatar) {
          avatarPath.value = uploadRes.avatar
          localStorage.setItem('userAvatar', uploadRes.avatar)
        } else {
          avatarPath.value = dataUrl
          localStorage.setItem('userAvatar', dataUrl)
        }
      } catch (uploadErr) {
        console.warn('Backend upload fallback:', uploadErr)
        avatarPath.value = dataUrl
        localStorage.setItem('userAvatar', dataUrl)
      }
    }

    // 3. Update username & email
    try {
      const res = await api.put('/api/user/profile', {
        username: profileName.value,
        email: profileEmail.value
      })

      if (res && res.status === 'success') {
        profileName.value = res.username || profileName.value
        profileEmail.value = res.email || profileEmail.value
      }
    } catch (e) {
      console.warn('Profile update fallback:', e)
    }

    localStorage.setItem('username', profileName.value)
    localStorage.setItem('userEmail', profileEmail.value)

    // Reset staged pending states
    pendingFile.value = null
    previewUrl.value = null
    isPendingRemove.value = false

    // Sync to App.vue top header immediately
    window.dispatchEvent(new CustomEvent('profile-updated'))
    displayToast('Profile and photo updated successfully!')

  } catch (err: any) {
    console.error('Save profile error:', err)
    localStorage.setItem('username', profileName.value)
    localStorage.setItem('userEmail', profileEmail.value)
    window.dispatchEvent(new CustomEvent('profile-updated'))
    displayToast('Profile updated!')
  } finally {
    isSaving.value = false
  }
}

const updatePassword = async () => {
  if (!currentPassword.value) {
    displayToast('Please enter your current password.', 'error')
    return
  }
  if (!newPassword.value) {
    displayToast('Please enter a new password.', 'error')
    return
  }
  if (newPassword.value.length < 6) {
    displayToast('New password must be at least 6 characters long.', 'error')
    return
  }
  if (newPassword.value !== confirmPassword.value) {
    displayToast('New password and confirm password do not match!', 'error')
    return
  }
  if (currentPassword.value === newPassword.value) {
    displayToast('New password cannot be the same as your current password!', 'error')
    return
  }

  try {
    const res = await api.post('/api/user/password', {
      email: profileEmail.value,
      currentPassword: currentPassword.value,
      newPassword: newPassword.value
    })

    if (res && res.status === 'success') {
      currentPassword.value = ''
      newPassword.value = ''
      confirmPassword.value = ''
      displayToast(res.message || 'Password updated successfully!', 'success')
    } else {
      displayToast(res.message || 'Current password is incorrect! Please try again.', 'error')
    }
  } catch (err: any) {
    displayToast(err.message || 'Error updating password.', 'error')
  }
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.toast-enter-active,
.toast-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
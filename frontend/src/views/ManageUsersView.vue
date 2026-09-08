<template>
  <div class="p-8 bg-slate-50/60 min-h-full font-sans" @click="closeAllDropdowns">
    <!-- Clean Top-Right Toast Notification -->
    <Transition name="toast">
      <div 
        v-if="showToast" 
        class="fixed top-24 right-8 z-50 flex items-center gap-3 px-4 py-2.5 bg-slate-900/95 backdrop-blur-md text-white rounded-2xl shadow-xl shadow-slate-900/10 border border-slate-800 text-xs font-medium font-sans"
      >
        <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/30">
          <Check class="w-3 h-3 stroke-[3]" />
        </div>
        <span class="text-slate-100 tracking-tight font-medium pr-1">{{ toastMessage }}</span>
      </div>
    </Transition>

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight flex items-center gap-2.5">
          <Users class="w-7 h-7 text-teal-600" />
          <span>User Management</span>
        </h1>
        <p class="text-sm text-slate-500 mt-1 font-sans">Manage system staff, roles, and administrative access permissions.</p>
      </div>

      <button 
        @click="openAddModal" 
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 active:scale-[0.98] text-white rounded-xl font-semibold text-sm transition-all shadow-sm shadow-teal-600/20 cursor-pointer shrink-0 font-sans"
      >
        <UserPlus class="w-4 h-4" />
        <span>Add New User</span>
      </button>
    </div>

    <!-- Quick Stat Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider font-sans">Total Accounts</p>
        <p class="text-2xl font-extrabold text-slate-900 mt-1 font-heading">{{ users.length }}</p>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-xs font-semibold text-teal-600 uppercase tracking-wider font-sans">Doctors</p>
        <p class="text-2xl font-extrabold text-teal-700 mt-1 font-heading">{{ countRole('doctor') }}</p>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider font-sans">Nurse</p>
        <p class="text-2xl font-extrabold text-amber-700 mt-1 font-heading">{{ countRole('nurse') }}</p>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider font-sans">Superadmins</p>
        <p class="text-2xl font-extrabold text-purple-700 mt-1 font-heading">{{ countRole('superadmin') }}</p>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 font-sans">
      <div class="relative w-full sm:w-80">
        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search by name or email..." 
          class="w-full py-2 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 transition-all font-sans"
        />
      </div>

      <!-- Role Filter Pills -->
      <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto">
        <button 
          v-for="r in ['all', 'superadmin', 'doctor', 'nurse', 'staff']" 
          :key="r"
          @click="selectedRoleFilter = r"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all capitalize cursor-pointer shrink-0 font-sans',
            selectedRoleFilter === r 
              ? 'bg-teal-600 text-white shadow-xs' 
              : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80'
          ]"
        >
          {{ r === 'all' ? 'All Roles' : r }}
        </button>
      </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden font-sans">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead class="bg-slate-800 text-white">
            <tr class="text-[13px] font-semibold tracking-wide">
              <th class="px-6 py-4">User</th>
              <th class="px-6 py-4">Email Address</th>
              <th class="px-6 py-4">Password</th>
              <th class="px-6 py-4">System Role</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-sm">
            <tr v-if="filteredUsers.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                <Users class="w-10 h-10 mx-auto mb-2 opacity-30" />
                <span class="font-sans">No user accounts found matching your search.</span>
              </td>
            </tr>
            <tr 
              v-for="(u, index) in filteredUsers" 
              :key="u.id" 
              class="hover:bg-slate-50/60 transition-colors"
            >
              <!-- Avatar & Name -->
              <td class="px-6 py-4 flex items-center gap-3">
                <img 
                  :src="getAvatarUrl(u.avatar)" 
                  @error="onImgErr"
                  alt="Avatar" 
                  class="w-10 h-10 rounded-full object-cover border border-slate-200 bg-slate-100 shrink-0" 
                />
                <div>
                  <span class="font-bold text-slate-900 block leading-tight font-sans">{{ u.username }}</span>
                  <span class="text-xs text-slate-400 font-sans">ID #{{ index + 1 }}</span>
                </div>
              </td>

              <!-- Email -->
              <td class="px-6 py-4 text-slate-600 font-medium text-xs font-sans">
                {{ u.email }}
              </td>

              <!-- Password -->
              <td class="px-6 py-4 font-sans">
                <div class="flex items-center gap-2">
                  <span class="text-slate-400 font-bold tracking-widest text-xs font-sans">••••••••</span>
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200/60 uppercase font-sans">Encrypted</span>
                </div>
              </td>

              <!-- Role Badge -->
              <td class="px-6 py-4">
                <span 
                  :class="[
                    'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 border font-sans',
                    getRoleBadge(u.role)
                  ]"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="getRoleDot(u.role)"></span>
                  {{ u.role }}
                </span>
              </td>

              <!-- Status -->
              <td class="px-6 py-4">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center gap-1.5 w-fit font-sans">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                  Active
                </span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    @click="openDetailsModal(u)" 
                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-100 cursor-pointer"
                    title="View Account Details & Login Devices"
                  >
                    <Eye class="w-4 h-4" />
                  </button>
                  <button 
                    @click="openEditModal(u)" 
                    class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors border border-transparent hover:border-teal-100 cursor-pointer"
                    title="Edit Role / User"
                  >
                    <Edit class="w-4 h-4" />
                  </button>
                  <button 
                    v-if="u.email !== 'admin@gmail.com' && u.id !== 1 && u.email !== currentUserEmail" 
                    @click="deleteUserAccount(u)" 
                    class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors border border-transparent hover:border-rose-100 cursor-pointer"
                    title="Delete User"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ================= ADD USER MODAL ================= -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 font-sans">
      <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" @click="isAddModalOpen = false"></div>
      
      <div class="bg-white rounded-3xl max-w-md w-full p-7 shadow-2xl border border-slate-100 z-10 animate-scale-up relative" @click.stop>
        <!-- Modal Header -->
        <div class="flex items-start justify-between pb-5 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold border border-teal-100 shadow-2xs">
              <UserPlus class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-bold text-slate-900 tracking-tight leading-snug font-heading">Add New Staff Account</h2>
              <p class="text-xs text-slate-400 mt-0.5 font-sans">Create login credentials and assign system permissions.</p>
            </div>
          </div>
          <button 
            @click="isAddModalOpen = false" 
            class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1.5 rounded-xl transition-colors cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="submitAddUser" class="mt-6 space-y-4 font-sans">
          <!-- Full Name -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 font-sans">Full Name / Username</label>
            <div class="relative">
              <User class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
              <input 
                type="text" 
                v-model="newUser.username" 
                @input="onAddUsernameInput"
                autocapitalize="words"
                required
                placeholder="Username" 
                class="w-full py-2.5 pl-10 pr-4 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all font-sans"
              />
            </div>
          </div>

          <!-- Email Address -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 font-sans">Email Address (Login Account)</label>
            <div class="relative">
              <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
              <input 
                type="email" 
                v-model="newUser.email" 
                required
                placeholder="username@gmail.com" 
                class="w-full py-2.5 pl-10 pr-4 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all font-sans"
              />
            </div>
          </div>

          <!-- Password Field with Toggle -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 font-sans">Password</label>
            <div class="relative">
              <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
              <input 
                :type="showAddPassword ? 'text' : 'password'" 
                v-model="newUser.password" 
                required
                placeholder="Min 6 characters" 
                class="w-full py-2.5 pl-10 pr-10 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all font-sans"
              />
              <button 
                type="button" 
                @click="showAddPassword = !showAddPassword" 
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
              >
                <EyeOff v-if="showAddPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Custom Modern System Role Dropdown -->
          <div class="relative">
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 font-sans">System Role</label>
            <div class="relative">
              <ShieldCheck class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none z-10" />
              <button 
                type="button" 
                @click.stop="isRoleDropdownOpen = !isRoleDropdownOpen"
                class="w-full py-2.5 pl-10 pr-4 bg-slate-50/70 hover:bg-slate-100/70 border border-slate-200 rounded-xl text-sm text-slate-800 flex items-center justify-between transition-all text-left cursor-pointer focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 font-sans"
              >
                <span class="font-semibold text-slate-900 capitalize">{{ newUser.role }}</span>
                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isRoleDropdownOpen }" />
              </button>
            </div>

            <!-- Click Outside Overlay -->
            <div 
              v-if="isRoleDropdownOpen" 
              class="fixed inset-0 z-20 cursor-default" 
              @click.stop="isRoleDropdownOpen = false"
            ></div>

            <!-- Custom Floating Role Dropdown Menu -->
            <div 
              v-if="isRoleDropdownOpen" 
              class="absolute left-0 right-0 top-full mt-1.5 bg-white rounded-2xl shadow-xl border border-slate-100 p-1.5 z-30 animate-scale-up space-y-0.5 font-sans"
            >
              <div 
                v-for="r in roleOptions" 
                :key="r.value"
                @click.stop="newUser.role = r.value; isRoleDropdownOpen = false"
                :class="[
                  'flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs cursor-pointer transition-all',
                  newUser.role === r.value 
                    ? 'bg-teal-50 text-teal-800 font-semibold' 
                    : 'text-slate-700 hover:bg-slate-50'
                ]"
              >
                <div>
                  <span class="block font-bold text-slate-800 text-xs leading-snug">{{ r.label }}</span>
                  <span class="text-[11px] text-slate-400 font-normal">{{ r.desc }}</span>
                </div>
                <Check v-if="newUser.role === r.value" class="w-4 h-4 text-teal-600 shrink-0 stroke-[2.5]" />
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 font-sans">
            <button 
              type="button" 
              @click="isAddModalOpen = false" 
              class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer font-sans"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="isSubmitting"
              class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 active:scale-[0.98] text-white text-sm font-semibold rounded-xl shadow-sm shadow-teal-600/20 transition-all disabled:opacity-50 cursor-pointer font-sans"
            >
              {{ isSubmitting ? 'Creating...' : 'Create Account' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ================= EDIT USER MODAL ================= -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 font-sans">
      <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" @click="isEditModalOpen = false"></div>
      
      <div class="bg-white rounded-3xl max-w-md w-full p-7 shadow-2xl border border-slate-100 z-10 animate-scale-up relative" @click.stop>
        <div class="flex items-start justify-between pb-5 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold border border-teal-100 shadow-2xs">
              <Edit3 class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-bold text-slate-900 tracking-tight leading-snug font-heading">Edit User Account</h2>
              <p class="text-xs text-slate-400 mt-0.5 font-sans">Update staff account information and role.</p>
            </div>
          </div>
          <button 
            @click="isEditModalOpen = false" 
            class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1.5 rounded-xl transition-colors cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitEditUser" class="mt-6 space-y-4 font-sans">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 font-sans">Username</label>
            <div class="relative">
              <User class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
              <input 
                type="text" 
                v-model="editUserForm.username" 
                @input="onEditUsernameInput"
                autocapitalize="words"
                required
                class="w-full py-2.5 pl-10 pr-4 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all font-sans"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 font-sans">Email Address</label>
            <div class="relative">
              <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
              <input 
                type="email" 
                v-model="editUserForm.email" 
                required
                class="w-full py-2.5 pl-10 pr-4 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all font-sans"
              />
            </div>
          </div>

          <!-- Reset Password (Optional) -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 font-sans">Reset Password (Optional)</label>
            <div class="relative">
              <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
              <input 
                :type="showEditPassword ? 'text' : 'password'" 
                v-model="editUserForm.password" 
                placeholder="Leave blank to keep current password" 
                class="w-full py-2.5 pl-10 pr-10 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all font-sans"
              />
              <button 
                type="button" 
                @click="showEditPassword = !showEditPassword" 
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
              >
                <EyeOff v-if="showEditPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Custom Modern Edit Role Dropdown -->
          <div class="relative">
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 font-sans">Role</label>
            <div class="relative">
              <ShieldCheck class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none z-10" />
              <button 
                type="button" 
                @click.stop="isEditRoleDropdownOpen = !isEditRoleDropdownOpen"
                class="w-full py-2.5 pl-10 pr-4 bg-slate-50/70 hover:bg-slate-100/70 border border-slate-200 rounded-xl text-sm text-slate-800 flex items-center justify-between transition-all text-left cursor-pointer focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 font-sans"
              >
                <span class="font-semibold text-slate-900 capitalize">{{ editUserForm.role }}</span>
                <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isEditRoleDropdownOpen }" />
              </button>
            </div>

            <!-- Click Outside Overlay -->
            <div 
              v-if="isEditRoleDropdownOpen" 
              class="fixed inset-0 z-20 cursor-default" 
              @click.stop="isEditRoleDropdownOpen = false"
            ></div>

            <!-- Custom Floating Role Dropdown Menu -->
            <div 
              v-if="isEditRoleDropdownOpen" 
              class="absolute left-0 right-0 top-full mt-1.5 bg-white rounded-2xl shadow-xl border border-slate-100 p-1.5 z-30 animate-scale-up space-y-0.5 font-sans"
            >
              <div 
                v-for="r in roleOptions" 
                :key="r.value"
                @click.stop="editUserForm.role = r.value; isEditRoleDropdownOpen = false"
                :class="[
                  'flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs cursor-pointer transition-all',
                  editUserForm.role === r.value 
                    ? 'bg-teal-50 text-teal-800 font-semibold' 
                    : 'text-slate-700 hover:bg-slate-50'
                ]"
              >
                <div>
                  <span class="block font-bold text-slate-800 text-xs leading-snug">{{ r.label }}</span>
                  <span class="text-[11px] text-slate-400 font-normal">{{ r.desc }}</span>
                </div>
                <Check v-if="editUserForm.role === r.value" class="w-4 h-4 text-teal-600 shrink-0 stroke-[2.5]" />
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 font-sans">
            <button 
              type="button" 
              @click="isEditModalOpen = false" 
              class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer font-sans"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="isSubmitting"
              class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 active:scale-[0.98] text-white text-sm font-semibold rounded-xl shadow-sm shadow-teal-600/20 transition-all disabled:opacity-50 cursor-pointer font-sans"
            >
              {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ================= USER DETAILS & LOGIN DEVICES MODAL ================= -->
    <div v-if="isDetailsModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 font-sans">
      <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" @click="isDetailsModalOpen = false"></div>
      
      <div class="bg-white rounded-3xl max-w-lg w-full p-7 shadow-2xl border border-slate-100 z-10 animate-scale-up relative" @click.stop>
        <!-- Modal Header -->
        <div class="flex items-start justify-between pb-5 border-b border-slate-100">
          <div class="flex items-center gap-3.5">
            <img 
              :src="selectedUserDetails?.avatar ? resolveServerUrl(selectedUserDetails.avatar) : defaultAvatar" 
              alt="Avatar" 
              class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-xs ring-2 ring-slate-100"
              @error="(e: Event) => (e.target as HTMLImageElement).src = defaultAvatar"
            />
            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-base font-bold text-slate-900 font-heading">{{ selectedUserDetails?.username }}</h2>
                <span 
                  :class="[
                    'text-[10px] font-bold px-2 py-0.5 rounded-md border tracking-wider uppercase font-sans',
                    getRoleBadge(selectedUserDetails?.role || '')
                  ]"
                >
                  {{ selectedUserDetails?.role }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-0.5 font-sans">{{ selectedUserDetails?.email }}</p>
            </div>
          </div>
          <button 
            @click="isDetailsModalOpen = false" 
            class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1.5 rounded-xl transition-colors cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Content Body -->
        <div class="mt-5 space-y-5">
          
          <!-- Recent Login & Device Activity -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider font-heading flex items-center gap-1.5">
                <ShieldCheck class="w-4 h-4 text-teal-600" />
                <span>Login & Device History</span>
              </h3>
              <span class="text-[10px] font-medium text-slate-400 font-sans">Security Logs</span>
            </div>

            <!-- Loading Spinner -->
            <div v-if="isLoadingActivities" class="py-8 text-center text-xs text-slate-400 font-sans flex items-center justify-center gap-2">
              <div class="w-4 h-4 border-2 border-teal-500 border-t-transparent rounded-full animate-spin"></div>
              <span>Loading login records...</span>
            </div>

            <!-- Empty State -->
            <div v-else-if="userActivities.length === 0" class="p-6 bg-slate-50/70 border border-slate-200/70 rounded-2xl text-center">
              <Laptop class="w-8 h-8 text-slate-300 mx-auto mb-2" />
              <p class="text-xs font-semibold text-slate-600 font-sans">No Login History Recorded Yet</p>
              <p class="text-[11px] text-slate-400 mt-0.5 font-sans">This account has not logged in since tracking was enabled.</p>
            </div>

            <!-- Activity List -->
            <div v-else class="bg-slate-50/60 border border-slate-200/80 rounded-2xl overflow-hidden divide-y divide-slate-200/60 shadow-2xs font-sans max-h-60 overflow-y-auto">
              <div 
                v-for="(act, idx) in userActivities" 
                :key="act.id"
                class="px-4 py-3 flex justify-between items-center bg-white hover:bg-slate-50/50 transition-colors"
              >
                <div class="flex items-center gap-3">
                  <div 
                    class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                    :class="idx === 0 ? 'bg-teal-50 text-teal-600 border border-teal-100/60' : 'bg-slate-100 text-slate-400'"
                  >
                    <Smartphone v-if="act.type === 'mobile'" class="w-3.5 h-3.5" />
                    <Laptop v-else class="w-3.5 h-3.5" />
                  </div>
                  <div>
                    <span class="text-xs font-semibold text-slate-800 block leading-tight font-sans">
                      {{ act.deviceName }} - {{ act.browser }}
                    </span>
                    <span v-if="act.ip" class="text-[10px] text-slate-400 font-sans">IP: {{ act.ip }}</span>
                  </div>
                </div>

                <div class="text-right">
                  <span 
                    v-if="idx === 0" 
                    class="text-[10px] font-semibold text-teal-700 bg-teal-50 border border-teal-100 px-2 py-0.5 rounded-full inline-flex items-center gap-1 font-sans"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                    Latest
                  </span>
                  <span v-else class="text-xs text-slate-400 font-medium font-sans">
                    {{ act.timeStr }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer Close Button -->
          <div class="pt-2 flex justify-end">
            <button 
              type="button" 
              @click="isDetailsModalOpen = false" 
              class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors cursor-pointer font-sans"
            >
              Close
            </button>
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
  Users, 
  UserPlus, 
  Search, 
  Edit, 
  Trash2, 
  Check, 
  X, 
  User, 
  Mail, 
  Lock, 
  ShieldCheck, 
  ChevronDown, 
  Eye, 
  EyeOff,
  Laptop,
  Smartphone 
} from 'lucide-vue-next'

interface UserItem {
  id: number
  username: string
  email: string
  role: string
  avatar?: string | null
}

const users = ref<UserItem[]>([])
const currentUserEmail = ref(localStorage.getItem('userEmail') || 'admin@gmail.com')
const searchQuery = ref('')
const selectedRoleFilter = ref('all')

const isAddModalOpen = ref(false)
const isEditModalOpen = ref(false)
const isDetailsModalOpen = ref(false)
const selectedUserDetails = ref<UserItem | null>(null)
const isLoadingActivities = ref(false)
const userActivities = ref<any[]>([])
const isSubmitting = ref(false)
const showAddPassword = ref(false)
const showEditPassword = ref(false)

const isRoleDropdownOpen = ref(false)
const isEditRoleDropdownOpen = ref(false)

const closeAllDropdowns = () => {
  isRoleDropdownOpen.value = false
  isEditRoleDropdownOpen.value = false
}

const roleOptions = [
  { value: 'superadmin', label: 'Superadmin', desc: 'Full System Management & Control' },
  { value: 'doctor', label: 'Doctor', desc: 'Medical & Clinical Records' },
  { value: 'nurse', label: 'Nurse', desc: 'Patient Care & Vitals' },
  { value: 'staff', label: 'Staff', desc: 'Receptionist & Front Desk' }
]

const formatCapitalizeWords = (str: string) => {
  if (!str) return ''
  return str.replace(/\b\w/g, (char) => char.toUpperCase())
}

const onAddUsernameInput = (e: Event) => {
  const target = e.target as HTMLInputElement
  newUser.value.username = formatCapitalizeWords(target.value)
}

const onEditUsernameInput = (e: Event) => {
  const target = e.target as HTMLInputElement
  editUserForm.value.username = formatCapitalizeWords(target.value)
}

const newUser = ref({
  username: '',
  email: '',
  password: '',
  role: 'doctor'
})

const editUserForm = ref({
  id: 0,
  username: '',
  email: '',
  password: '',
  role: 'doctor'
})

const showToast = ref(false)
const toastMessage = ref('')

const displayToast = (msg: string) => {
  toastMessage.value = msg
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 3000)
}

const getAvatarUrl = (path?: string | null) => {
  if (!path) return defaultAvatar
  return resolveServerUrl(path)
}

const onImgErr = (e: Event) => {
  const img = e.target as HTMLImageElement
  if (img && img.src.includes('/DMR_project/backend/public/uploads/')) {
    img.src = img.src.replace('/DMR_project/backend/public/uploads/', '/uploads/')
  } else {
    img.src = defaultAvatar
  }
}

const countRole = (role: string) => {
  return users.value.filter(u => u.role === role).length
}

const getRoleBadge = (role: string) => {
  switch (role) {
    case 'superadmin':
      return 'bg-purple-50 text-purple-700 border-purple-200'
    case 'doctor':
      return 'bg-teal-50 text-teal-700 border-teal-200'
    case 'nurse':
      return 'bg-sky-50 text-sky-700 border-sky-200'
    case 'staff':
      return 'bg-amber-50 text-amber-700 border-amber-200'
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200'
  }
}

const getRoleDot = (role: string) => {
  switch (role) {
    case 'superadmin': return 'bg-purple-500'
    case 'doctor': return 'bg-teal-500'
    case 'nurse': return 'bg-sky-500'
    case 'staff': return 'bg-amber-500'
    default: return 'bg-slate-500'
  }
}

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const matchSearch = u.username.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        u.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchRole = selectedRoleFilter.value === 'all' || u.role === selectedRoleFilter.value
    return matchSearch && matchRole
  })
})

const fetchUsers = async () => {
  try {
    const res = await api.get('/api/admin/users')
    if (res && res.status === 'success' && Array.isArray(res.data)) {
      users.value = res.data
    }
  } catch (err) {
    console.error('Error fetching users:', err)
  }
}

onMounted(() => {
  fetchUsers()
})

const openAddModal = () => {
  newUser.value = {
    username: '',
    email: '',
    password: '',
    role: 'doctor'
  }
  showAddPassword.value = false
  isRoleDropdownOpen.value = false
  isAddModalOpen.value = true
}

const submitAddUser = async () => {
  isSubmitting.value = true
  try {
    const res = await api.post('/api/admin/users', newUser.value)
    if (res && res.status === 'success') {
      displayToast('User account created successfully!')
      isAddModalOpen.value = false
      fetchUsers()
    } else {
      displayToast(res.message || 'Failed to create user')
    }
  } catch (err: any) {
    displayToast(err.message || 'Error creating user')
  } finally {
    isSubmitting.value = false
  }
}

const openDetailsModal = async (u: UserItem) => {
  selectedUserDetails.value = u
  userActivities.value = []
  isLoadingActivities.value = true
  isDetailsModalOpen.value = true
  try {
    const res = await api.get(`/api/admin/users/${u.id}/activities`)
    if (res && res.status === 'success' && Array.isArray(res.data)) {
      userActivities.value = res.data
    }
  } catch (err) {
    console.warn('Error fetching user activities:', err)
  } finally {
    isLoadingActivities.value = false
  }
}

const openEditModal = (u: UserItem) => {
  editUserForm.value = {
    id: u.id,
    username: u.username,
    email: u.email,
    password: '',
    role: u.role
  }
  showEditPassword.value = false
  isEditRoleDropdownOpen.value = false
  isEditModalOpen.value = true
}

const submitEditUser = async () => {
  isSubmitting.value = true
  try {
    const payload: any = {
      username: editUserForm.value.username,
      email: editUserForm.value.email,
      role: editUserForm.value.role
    }
    if (editUserForm.value.password) {
      payload.password = editUserForm.value.password
    }
    const res = await api.put(`/api/admin/users/${editUserForm.value.id}`, payload)
    if (res && res.status === 'success') {
      displayToast('User updated successfully!')
      isEditModalOpen.value = false
      fetchUsers()
    } else {
      displayToast(res.message || 'Failed to update user')
    }
  } catch (err: any) {
    displayToast(err.message || 'Error updating user')
  } finally {
    isSubmitting.value = false
  }
}

const deleteUserAccount = async (u: UserItem) => {
  if (confirm(`Are you sure you want to delete user "${u.username}" (${u.email})?`)) {
    try {
      const res = await api.delete(`/api/admin/users/${u.id}`)
      if (res && res.status === 'success') {
        displayToast('User deleted successfully!')
        fetchUsers()
      } else {
        displayToast(res.message || 'Failed to delete user')
      }
    } catch (err: any) {
      displayToast(err.message || 'Error deleting user')
    }
  }
}
</script>

<style scoped>
.animate-scale-up {
  animation: scaleUp 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes scaleUp {
  from {
    opacity: 0;
    transform: scale(0.96);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>

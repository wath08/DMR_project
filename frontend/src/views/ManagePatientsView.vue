<template>
  <div class="px-8 py-8 bg-slate-50/60 min-h-full">
    <div class="mb-6 flex justify-between items-end">
      <div>
        <h1 class="text-[24px] font-heading font-bold text-slate-900 tracking-tight">Patients</h1>
        <p class="text-[14px] text-slate-500 mt-1 font-medium">Manage patient profiles, demographics, and contact information.</p>
      </div>
      <button @click="openAddModal" class="flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-teal-600 text-white rounded-lg font-semibold text-[13px] sm:text-sm hover:bg-teal-700 transition-colors shadow-sm cursor-pointer shrink-0 whitespace-nowrap">
        <Plus class="w-4 h-4 shrink-0" />
        <span>Add Patient</span>
      </button>
    </div>
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
      <!-- Table Header Options -->
      <div class="p-6 border-b border-slate-100 bg-white">
            <div class="flex flex-col xl:flex-row gap-4 items-start xl:items-end justify-between">
              <!-- Search Bar -->
              <div class="relative w-full xl:w-[400px] shrink-0">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input type="text" v-model="searchQuery" placeholder="Search patients by name, ID, or phone..." class="w-full py-2.5 pl-11 pr-4 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-600 focus:outline-none focus:border-teal-500 focus:bg-white shadow-sm transition-colors" />
              </div>
              
              <div class="grid grid-cols-2 xl:flex xl:flex-row gap-4 w-full xl:w-auto">
                <!-- Custom Gender Dropdown -->
                <div class="flex flex-col gap-1 relative dropdown-container">
                  <label class="text-[12px] font-semibold text-slate-700">Gender</label>
                  <button @click.stop="isGenderOpen = !isGenderOpen; isSortOpen = false" class="relative w-full xl:w-[120px] py-2.5 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-600 focus:outline-none cursor-pointer flex items-center hover:bg-white transition-colors text-left shadow-sm">
                    <span class="truncate block w-full">{{ filterGender }}</span>
                    <ChevronDown class="absolute right-3 w-4 h-4 text-slate-400 pointer-events-none transition-transform duration-200" :class="{'rotate-180': isGenderOpen}" />
                  </button>
                  <div v-if="isGenderOpen" class="absolute top-[68px] left-0 w-full bg-white border border-slate-100 rounded-lg shadow-xl z-50 overflow-hidden py-1 animate-fade-in">
                    <div @click="filterGender = '(All)'; isGenderOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterGender === '(All)'}">(All)</div>
                    <div @click="filterGender = 'Male'; isGenderOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterGender === 'Male'}">Male</div>
                    <div @click="filterGender = 'Female'; isGenderOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterGender === 'Female'}">Female</div>
                  </div>
                </div>

                <!-- Custom Sort Dropdown -->
                <div class="flex flex-col gap-1 relative dropdown-container">
                  <label class="text-[12px] font-semibold text-slate-700">Sort By</label>
                  <button @click.stop="isSortOpen = !isSortOpen; isGenderOpen = false" class="relative w-full xl:w-[140px] py-2.5 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-600 focus:outline-none cursor-pointer flex items-center hover:bg-white transition-colors text-left shadow-sm">
                    <span class="truncate block w-full">{{ currentSort }}</span>
                    <ChevronDown class="absolute right-3 w-4 h-4 text-slate-400 pointer-events-none transition-transform duration-200" :class="{'rotate-180': isSortOpen}" />
                  </button>
                  <div v-if="isSortOpen" class="absolute top-[68px] right-0 w-[160px] bg-white border border-slate-100 rounded-lg shadow-xl z-50 overflow-hidden py-1 animate-fade-in">
                    <div @click="currentSort = 'Newest'; isSortOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': currentSort === 'Newest'}">Newest</div>
                    <div @click="currentSort = 'Oldest'; isSortOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': currentSort === 'Oldest'}">Oldest</div>
                    <div @click="currentSort = 'Name (A - Z)'; isSortOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors border-t border-slate-50 mt-1 pt-1" :class="{'bg-teal-50 text-teal-700 font-medium': currentSort === 'Name (A - Z)'}">Name (A - Z)</div>
                    <div @click="currentSort = 'Name (Z - A)'; isSortOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': currentSort === 'Name (Z - A)'}">Name (Z - A)</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto min-h-[440px] flex flex-col">
            <table class="w-full text-left border-collapse whitespace-nowrap">
              <thead class="bg-slate-800 text-white">
                <tr class="h-12">
                  <th class="px-6 font-semibold text-[13px] tracking-wide">Patient ID</th>
                  <th class="px-6 font-semibold text-[13px] tracking-wide">Name</th>
                  <th class="px-6 font-semibold text-[13px] tracking-wide">Date of Birth</th>
                  <th class="px-6 font-semibold text-[13px] tracking-wide">Gender</th>
                  <th class="px-6 font-semibold text-[13px] tracking-wide">Phone</th>
                  <th class="px-6 font-semibold text-[13px] tracking-wide text-center">Actions</th>
                </tr>
              </thead>

              <!-- using v-for to show an data from database -->
              <tbody class="divide-y divide-slate-100">
                <!-- Empty State -->
                <tr v-if="filteredPatients.length === 0">
                  <td colspan="6" class="px-6 py-24 text-center bg-slate-50/30">
                    <div class="flex flex-col items-center justify-center text-slate-400">
                      <Users class="w-12 h-12 mb-3 text-slate-300" />
                      <p class="text-[15px] font-medium text-slate-600">No patients found</p>
                      <p class="text-[13px] mt-1 text-slate-400">Try adjusting your filters or search query.</p>
                    </div>
                  </td>
                </tr>

                <!-- Patient Rows -->
                <tr v-for="patient in paginatedPatients" :key="patient.id" class="h-[68px] hover:bg-slate-50/50 transition-colors animate-fade-in">
                  <td class="px-6 py-4 text-sm font-medium text-slate-500">{{ patient.id }}</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs">{{ patient.initials }}</div>
                      <div class="font-bold text-slate-800 text-sm">{{ patient.name }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-600">{{ formatDate(patient.dob) }}</td>
                  <td class="px-6 py-4">
                    <span :class="{
                      'px-3 py-1 bg-pink-50 text-pink-700 rounded-full text-xs font-bold border border-pink-100': patient.gender === 'Female',
                      'px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-100': patient.gender === 'Male',
                      'px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-bold border border-slate-200': patient.gender !== 'Male' && patient.gender !== 'Female'
                    }">
                      {{ patient.gender }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-600">
                    <span v-if="patient.phone">{{ patient.phone }}</span>
                    <span v-else class="text-slate-400 font-medium">—</span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <button @click="openViewModal(patient)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-100 cursor-pointer" title="View Details">
                        <Eye class="w-4 h-4" />
                      </button>
                      <button @click="openEditModal(patient)" class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors border border-transparent hover:border-teal-100 cursor-pointer" title="Edit Patient">
                        <Edit class="w-4 h-4" />
                      </button>
                      <button @click="confirmDeletePatient(patient.id)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors border border-transparent hover:border-rose-100 cursor-pointer" title="Delete Patient">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Table Pagination Bar -->
          <div class="p-6 border-t border-slate-100 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Showing count & Items per page -->
            <div class="flex items-center gap-4 text-[13px] text-slate-500 font-medium flex-wrap">
              <span>
                Showing {{ filteredPatients.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredPatients.length) }} of {{ filteredPatients.length }} entries
              </span>
              
              <!-- Clean Custom Rows Dropdown -->
              <div class="flex items-center gap-2 border-l border-slate-200 pl-4 relative dropdown-container">
                <span class="text-xs text-slate-400 font-medium">Rows:</span>
                <button 
                  @click.stop="isPageSizeOpen = !isPageSizeOpen; isGenderOpen = false; isSortOpen = false" 
                  type="button"
                  class="h-8 px-2.5 bg-slate-50 border border-slate-200 hover:bg-white rounded-lg text-xs font-semibold text-slate-700 flex items-center gap-1.5 focus:outline-none transition-colors shadow-2xs cursor-pointer"
                >
                  <span>{{ itemsPerPage }}</span>
                  <ChevronDown class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{'rotate-180': isPageSizeOpen}" />
                </button>

                <!-- Rows Menu Popover (Opens Upward cleanly) -->
                <div 
                  v-if="isPageSizeOpen" 
                  class="absolute bottom-10 left-12 w-20 bg-white border border-slate-100 rounded-xl shadow-xl z-50 overflow-hidden py-1 animate-fade-in divide-y divide-slate-50"
                >
                  <div 
                    v-for="size in pageSizeOptions" 
                    :key="size" 
                    @click="itemsPerPage = size; isPageSizeOpen = false" 
                    :class="['px-3 py-1.5 text-xs font-semibold cursor-pointer transition-colors text-center', itemsPerPage === size ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-teal-600']"
                  >
                    {{ size }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination Controls -->
            <div class="flex items-center gap-1.5">
              <button 
                @click="currentPage > 1 && currentPage--" 
                :disabled="currentPage === 1"
                class="px-3.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
              >
                Prev
              </button>
              <template v-for="(p, pIdx) in visiblePages" :key="pIdx">
                <span v-if="p === '...'" class="px-2 text-xs font-bold text-slate-400 select-none">...</span>
                <button 
                  v-else
                  @click="currentPage = Number(p)"
                  :class="['min-w-[32px] h-8 px-2 flex items-center justify-center text-xs font-bold rounded-lg transition-colors cursor-pointer', currentPage === p ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 border border-slate-200']"
                >
                  {{ p }}
                </button>
              </template>
              <button 
                @click="currentPage < totalPages && currentPage++" 
                :disabled="currentPage === totalPages || totalPages === 0"
                class="px-3.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer"
              >
                Next
              </button>
            </div>
          </div>
        </div>

    <!-- View Patient Modal -->
    <div v-if="isViewModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-slate-900/40 transition-opacity" @click="isViewModalOpen = false"></div>
      
      <!-- Modal Content -->
      <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden animate-fade-in mx-4">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
          <div>
            <h2 class="text-lg font-bold text-slate-800">Patient Details</h2>
            <p class="text-[13px] text-slate-500 mt-0.5">ID: {{ viewingPatient?.id }}</p>
          </div>
          <button @click="isViewModalOpen = false" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors focus:outline-none">
            <X class="w-5 h-5" />
          </button>
        </div>
        
        <!-- Body -->
        <div class="p-6">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xl shadow-sm">
              {{ viewingPatient?.initials }}
            </div>
            <div>
              <h3 class="text-xl font-bold text-slate-800">{{ viewingPatient?.name }}</h3>
              <p class="text-[13px] text-slate-500 font-medium mt-0.5">{{ viewingPatient?.gender }} &bull; Born {{ formatDate(viewingPatient?.dob || '') }}</p>
            </div>
          </div>

          <div class="space-y-4">
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
              <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Contact Information</h4>
              <div class="space-y-3">
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center border border-slate-200 shrink-0 text-slate-500">
                    <Phone class="w-4 h-4" />
                  </div>
                  <div class="pt-1.5">
                    <p class="text-[13px] font-medium" :class="viewingPatient?.phone ? 'text-slate-800' : 'text-slate-400 italic'">
                      {{ viewingPatient?.phone || 'Null' }}
                    </p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center border border-slate-200 shrink-0 text-slate-500">
                    <MapPin class="w-4 h-4" />
                  </div>
                  <div class="pt-1.5">
                    <p class="text-[13px] font-medium text-slate-800 leading-relaxed">{{ viewingPatient?.address || 'No address provided' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end">
          <button @click="isViewModalOpen = false" class="px-5 py-2 text-[13px] font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors shadow-sm focus:outline-none">
            Done
          </button>
        </div>
      </div>
    </div>

    <!-- Add Patient Modal -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-slate-900/40 transition-opacity" @click="isAddModalOpen = false; isModalGenderOpen = false"></div>
      
      <!-- Modal Content -->
      <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden animate-fade-in mx-4">
        <form @submit.prevent="savePatient" class="flex flex-col max-h-[90vh]">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50 shrink-0">
            <div>
              <h2 class="text-lg font-bold text-slate-800">{{ editingPatientId ? 'Edit Patient' : 'Add New Patient' }}</h2>
              <p class="text-[13px] text-slate-500 mt-0.5">Enter the patient's personal and contact information.</p>
            </div>
            <button type="button" @click="isAddModalOpen = false" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors focus:outline-none cursor-pointer">
              <X class="w-5 h-5" />
            </button>
          </div>
          
          <!-- Form Body -->
          <div class="p-6 overflow-y-auto custom-scrollbar space-y-6 flex-1">
            <!-- Error Message -->
            <div v-if="formError" class="bg-red-50 text-red-600 px-4 py-3 rounded-lg border border-red-100 flex items-start gap-3 animate-fade-in mb-4">
              <AlertCircle class="w-5 h-5 shrink-0 mt-0.5" />
              <p class="text-[13px] font-medium leading-relaxed">{{ formError }}</p>
            </div>

            <!-- Personal Info Section -->
            <div>
              <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <User class="w-4 h-4 text-teal-600" />
                Personal Information
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                  <label class="text-[13px] font-medium text-slate-700">First Name <span class="text-red-500">*</span></label>
                  <input type="text" v-model="newPatient.firstName" @input="newPatient.firstName = capitalizeName(newPatient.firstName)" @keydown.enter.prevent="lastNameInputRef?.focus()" required placeholder="e.g. John" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" />
                </div>
                <div class="space-y-1.5">
                  <label class="text-[13px] font-medium text-slate-700">Last Name <span class="text-red-500">*</span></label>
                  <input type="text" ref="lastNameInputRef" v-model="newPatient.lastName" @input="newPatient.lastName = capitalizeName(newPatient.lastName)" required placeholder="e.g. Doe" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" />
                </div>
                <div class="space-y-1.5 relative">
                  <label class="text-[13px] font-medium text-slate-700">Date of Birth <span class="text-red-500">*</span></label>
                  
                  <div v-if="isDobOpen" @click="isDobOpen = false" class="fixed inset-0 z-40"></div>
                  
                  <button type="button" @click="isDobOpen = !isDobOpen" class="relative w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 focus:outline-none focus:border-teal-500 transition-colors text-left flex items-center justify-between cursor-pointer">
                    <span :class="formattedDob ? 'text-slate-800' : 'text-slate-400'" class="flex items-center">
                      <CalendarCheck class="w-4 h-4 mr-2 text-slate-400"/>
                      {{ formattedDob || 'Select date of birth' }}
                    </span>
                    <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': isDobOpen}" />
                  </button>
                  
                  <!-- Custom DOB Popover -->
                  <div v-if="isDobOpen" class="absolute top-[62px] left-0 w-full sm:w-[320px] bg-white border border-slate-100 rounded-xl shadow-xl z-50 p-4 animate-fade-in origin-top-left">
                    <div class="flex gap-3 h-[200px]">
                      <!-- Month -->
                      <div class="flex-1 flex flex-col">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">Month</span>
                        <div class="flex-1 overflow-y-auto space-y-1 pr-1 pb-4" style="scrollbar-width: none;">
                          <button type="button" v-for="m in months" :key="m.value" @click="dobMonth = m.value" :class="['w-full py-2 text-[13px] rounded-lg transition-colors cursor-pointer', dobMonth === m.value ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 hover:bg-slate-50']">
                            {{ m.label }}
                          </button>
                        </div>
                      </div>
                      <!-- Day -->
                      <div class="flex-1 flex flex-col border-l border-slate-100 pl-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">Day</span>
                        <div class="flex-1 overflow-y-auto space-y-1 pr-1 pb-4" style="scrollbar-width: none;">
                          <button type="button" v-for="d in 31" :key="d" @click="dobDay = d" :class="['w-full py-2 text-[13px] rounded-lg transition-colors cursor-pointer', dobDay == d ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 hover:bg-slate-50']">
                            {{ d }}
                          </button>
                        </div>
                      </div>
                      <!-- Year -->
                      <div class="flex-1 flex flex-col border-l border-slate-100 pl-3">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">Year</span>
                        <div class="flex-1 overflow-y-auto space-y-1 pr-1 pb-4" style="scrollbar-width: none;">
                          <button type="button" v-for="y in 100" :key="y" @click="dobYear = currentYear - y + 1" :class="['w-full py-2 text-[13px] rounded-lg transition-colors cursor-pointer', dobYear == (currentYear - y + 1) ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 hover:bg-slate-50']">
                            {{ currentYear - y + 1 }}
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="mt-5 pt-4 border-t border-slate-100">
                      <button type="button" @click="isDobOpen = false" class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-[13px] font-bold rounded-lg transition-colors cursor-pointer">
                        Confirm Date
                      </button>
                    </div>
                  </div>
                </div>
                <div class="space-y-1.5 relative">
                  <label class="text-[13px] font-medium text-slate-700">Gender <span class="text-red-500">*</span></label>
                  
                  <!-- Click outside overlay for modal dropdown -->
                  <div v-if="isModalGenderOpen" @click="isModalGenderOpen = false" class="fixed inset-0 z-40"></div>
                  
                  <button type="button" @click="isModalGenderOpen = !isModalGenderOpen" class="relative w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors text-left flex items-center justify-between cursor-pointer">
                    <span :class="newPatient.gender ? 'text-slate-800' : 'text-slate-400'">{{ newPatient.gender || 'Select gender' }}</span>
                    <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': isModalGenderOpen}" />
                  </button>
                  
                  <!-- Custom Dropdown Menu -->
                  <div v-if="isModalGenderOpen" class="absolute top-[62px] left-0 w-full bg-white border border-slate-100 rounded-lg shadow-xl z-50 overflow-hidden py-1 animate-fade-in">
                    <div @click="newPatient.gender = 'Male'; isModalGenderOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': newPatient.gender === 'Male'}">Male</div>
                    <div @click="newPatient.gender = 'Female'; isModalGenderOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': newPatient.gender === 'Female'}">Female</div>
                    <div @click="newPatient.gender = 'Other'; isModalGenderOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': newPatient.gender === 'Other'}">Other</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact Info Section -->
            <div class="pt-6 border-t border-slate-100">
              <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <FileText class="w-4 h-4 text-teal-600" />
                Contact Details
              </h3>
              <div class="grid grid-cols-1 gap-5">
                <div class="space-y-1.5">
                  <label class="text-[13px] font-medium text-slate-700">Phone Number</label>
                  <input type="tel" v-model="newPatient.phone" placeholder="e.g. 0123456789" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" />
                </div>
                <div class="space-y-1.5">
                  <label class="text-[13px] font-medium text-slate-700">Full Address</label>
                  <textarea v-model="newPatient.address" rows="3" placeholder="Enter patient's residential address..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors resize-none"></textarea>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Footer -->
          <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 shrink-0">
            <button @click="isAddModalOpen = false" :disabled="isSaving" type="button" class="px-4 py-2 text-[13px] font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors focus:outline-none disabled:opacity-40 cursor-pointer">
              Cancel
            </button>
            <button :disabled="isSaving" type="submit" class="px-5 py-2 text-[13px] font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors shadow-sm focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2 cursor-pointer">
              <span v-if="isSaving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <span>{{ isSaving ? 'Saving...' : (editingPatientId ? 'Save Changes' : 'Save Patient') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── Modern Clean Toast Notifications ────────────────────────────── -->
    <div class="fixed bottom-6 right-6 z-[200] flex flex-col gap-2.5 pointer-events-none">
      <transition-group name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-[13px] font-semibold pointer-events-auto border backdrop-blur-md transition-all duration-300 min-w-[280px] max-w-[400px]',
            toast.type === 'success' ? 'bg-slate-900/95 text-white border-slate-800 shadow-slate-900/20' :
            toast.type === 'error'   ? 'bg-rose-900/95 text-white border-rose-800 shadow-rose-900/20' :
                                       'bg-slate-900/95 text-white border-slate-800'
          ]"
        >
          <div 
            :class="[
              'w-6 h-6 rounded-full flex items-center justify-center shrink-0',
              toast.type === 'success' ? 'bg-teal-500/20 text-teal-400' :
              toast.type === 'error'   ? 'bg-rose-500/20 text-rose-400' :
                                         'bg-slate-500/20 text-slate-300'
            ]"
          >
            <CheckCircle2 v-if="toast.type === 'success'" class="w-4 h-4" />
            <AlertCircle v-else-if="toast.type === 'error'" class="w-4 h-4" />
            <Info v-else class="w-4 h-4" />
          </div>
          <span class="flex-1 leading-snug tracking-normal">{{ toast.message }}</span>
        </div>
      </transition-group>
    </div>

    <!-- ── Delete Confirmation Modal ───────────────────────────────────── -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 z-[150] flex items-center justify-center">
      <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="cancelDelete"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl p-6 mx-4 max-w-sm w-full animate-scale-up">
        <div class="flex flex-col items-center text-center gap-3">
          <div class="w-14 h-14 rounded-full bg-rose-50 flex items-center justify-center">
            <Trash2 class="w-7 h-7 text-rose-500" />
          </div>
          <h3 class="text-[17px] font-bold text-slate-800">Delete Patient Profile?</h3>
          <p class="text-[13px] text-slate-500 leading-relaxed">This action cannot be undone. All patient demographics will be permanently removed.</p>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="cancelDelete" class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">
            Cancel
          </button>
          <button @click="pendingDeleteId && deletePatient(pendingDeleteId)" :disabled="isDeleting" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors disabled:opacity-60 cursor-pointer flex items-center justify-center gap-2">
            <span v-if="isDeleting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span>{{ isDeleting ? 'Deleting...' : 'Yes, Delete' }}</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { api } from '../services/api'
import { Search, Plus, Edit, Trash2, ChevronDown, X, Phone, MapPin, Eye, User, CalendarCheck, AlertCircle, CheckCircle2, Info } from 'lucide-vue-next'

interface Patient {
  id: string;
  db_id?: number; 
  initials: string;
  name: string;
  dob: string;
  gender: string;
  phone: string;
  email?: string;
  address?: string;
}

const isSortOpen = ref(false)
const currentSort = ref('Newest')
const isGenderOpen = ref(false)
const filterGender = ref('(All)')
const isPageSizeOpen = ref(false)
const searchQuery = ref('')

// ── Toast Notification System ──────────────────────────────────────────────
interface Toast { id: number; type: 'success' | 'error' | 'info'; message: string }
const toasts = ref<Toast[]>([])
let toastId = 0
const showToast = (message: string, type: 'success' | 'error' | 'info' = 'success') => {
  const id = ++toastId
  toasts.value.push({ id, type, message })
  setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id) }, 3500)
}

// ── Loading / Double-Submit Guard ──────────────────────────────────────────
const isSaving = ref(false)
const isDeleting = ref(false)

// ── Delete Confirmation Modal ──────────────────────────────────────────────
const showDeleteConfirm = ref(false)
const pendingDeleteId = ref<string | null>(null)
const confirmDeletePatient = (id: string) => {
  pendingDeleteId.value = id
  showDeleteConfirm.value = true
}
const cancelDelete = () => {
  showDeleteConfirm.value = false
  pendingDeleteId.value = null
}

const filteredPatients = computed(() => {
  let result = [...patients.value]

  // 1. Search Filter
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(p => 
      p.name.toLowerCase().includes(q) || 
      p.id.toLowerCase().includes(q) || 
      p.phone.includes(q)
    )
  }

  // 2. Gender Filter
  if (filterGender.value !== '(All)') {
    result = result.filter(p => p.gender === filterGender.value)
  }

  // 3. Sort By
  result.sort((a, b) => {
    const idA = parseInt(a.id.split('-')[1] || '0')
    const idB = parseInt(b.id.split('-')[1] || '0')

    if (currentSort.value === 'Name (A - Z)') {
      return a.name.localeCompare(b.name)
    } else if (currentSort.value === 'Name (Z - A)') {
      return b.name.localeCompare(a.name)
    } else if (currentSort.value === 'Oldest') {
      return idA - idB
    } else {
      // Newest (default)
      return idB - idA
    }
  })

  return result
})

// ── Pagination State ─────────────────────────────────────────────────────────
const currentPage = ref(1)
const itemsPerPage = ref(6)
const pageSizeOptions = [6, 10, 20, 50]

const totalPages = computed(() => {
  return Math.ceil(filteredPatients.value.length / itemsPerPage.value) || 1
})

const paginatedPatients = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredPatients.value.slice(start, start + itemsPerPage.value)
})

// Smart visible page numbers with ellipsis (e.g. 1, 2, ..., 10)
const visiblePages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  const pages: (number | string)[] = []

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    pages.push(1)
    if (current > 3) pages.push('...')
    
    const start = Math.max(2, current - 1)
    const end = Math.min(total - 1, current + 1)
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }

    if (current < total - 2) pages.push('...')
    pages.push(total)
  }
  return pages
})

// Reset to page 1 when filters or page size change
watch([searchQuery, filterGender, currentSort, itemsPerPage], () => {
  currentPage.value = 1
})

// Modal State
const isAddModalOpen = ref(false)
const isViewModalOpen = ref(false)
const viewingPatient = ref<Patient | null>(null)

const isModalGenderOpen = ref(false)
const isDobOpen = ref(false)
const editingPatientId = ref<string | null>(null)
const lastNameInputRef = ref<HTMLInputElement | null>(null)

const openViewModal = (patient: Patient) => {
  viewingPatient.value = patient
  isViewModalOpen.value = true
}

const dobDay = ref<string | number>('')
const dobMonth = ref('')
const dobYear = ref<string | number>('')
const currentYear = new Date().getFullYear()

const months = [
  { value: '01', label: 'Jan' }, { value: '02', label: 'Feb' }, { value: '03', label: 'Mar' },
  { value: '04', label: 'Apr' }, { value: '05', label: 'May' }, { value: '06', label: 'Jun' },
  { value: '07', label: 'Jul' }, { value: '08', label: 'Aug' }, { value: '09', label: 'Sep' },
  { value: '10', label: 'Oct' }, { value: '11', label: 'Nov' }, { value: '12', label: 'Dec' }
]

const formattedDob = computed(() => {
  if (dobDay.value && dobMonth.value && dobYear.value) {
    const m = months.find(m => m.value === dobMonth.value)
    return `${m?.label} ${dobDay.value}, ${dobYear.value}`
  }
  return ''
})

watch([dobDay, dobMonth, dobYear], ([d, m, y]) => {
  if (d && m && y) {
    const dStr = d.toString().padStart(2, '0')
    newPatient.value.dob = `${y}-${m}-${dStr}`
  } else {
    newPatient.value.dob = ''
  }
})

const newPatient = ref({
  firstName: '',
  lastName: '',
  dob: '',
  gender: '',
  phone: '',
  email: '',
  address: ''
})



const formError = ref('')

const openAddModal = () => {
  editingPatientId.value = null
  formError.value = ''
  dobDay.value = ''
  dobMonth.value = ''
  dobYear.value = ''
  isDobOpen.value = false
  isModalGenderOpen.value = false
  newPatient.value = {
    firstName: '',
    lastName: '',
    dob: '',
    gender: '',
    phone: '',
    email: '',
    address: ''
  }
  isAddModalOpen.value = true
}

const openEditModal = (patient: any) => {
  editingPatientId.value = patient.id
  formError.value = ''
  const parts = patient.name.split(' ')
  const firstName = parts[0]
  const lastName = parts.slice(1).join(' ')
  
  if (patient.dob) {
    const dParts = patient.dob.split('-')
    if (dParts.length === 3) {
      dobYear.value = dParts[0]
      dobMonth.value = dParts[1]
      dobDay.value = parseInt(dParts[2])
    }
  } else {
    dobDay.value = ''
    dobMonth.value = ''
    dobYear.value = ''
  }
  
  newPatient.value = {
    firstName,
    lastName,
    dob: patient.dob,
    gender: patient.gender,
    phone: patient.phone,
    email: patient.email || '',
    address: patient.address || ''
  }
  isAddModalOpen.value = true
}


// State
const patients = ref<Patient[]>([])

// Format date from YYYY-MM-DD to DD-MM-YYYY
const formatDate = (dateString: string) => {
  if (!dateString) return ''
  const parts = dateString.split('-')
  if (parts.length === 3) {
    return `${parts[2]}-${parts[1]}-${parts[0]}`
  }
  return dateString
}

const capitalizeName = (val: string) => {
  if (!val) return ''
  return val.replace(/\b[a-z]/g, char => char.toUpperCase())
}

const fetchPatients = async () => {
  try {
    const response = await api.get('/api/patients')
    if (response && response.status === 'success') {
      patients.value = response.data.map((p: any) => {
        const fn = p.first_name || ''
        const ln = p.last_name || ''
        const firstInitial = fn.charAt(0) || ''
        const lastInitial = ln.charAt(0) || ''
        const initials = (firstInitial + lastInitial).toUpperCase() || 'P'
        return {
          id: p.patient_id || '',
          db_id: p.id,
          initials,
          name: `${fn} ${ln}`.trim(),
          dob: p.dob || '',
          gender: p.gender || 'Other',
          phone: p.phone || '',
          address: p.address || ''
        }
      })
    }
  } catch (error) {
    showToast('Failed to load patients. Please check your connection.', 'error')
    console.error("Error fetching patients:", error)
  }
}

const deletePatient = async (id: string) => {
  if (isDeleting.value) return
  const patientToDelete = patients.value.find(p => p.id === id)
  if (!patientToDelete || !patientToDelete.db_id) return

  isDeleting.value = true
  showDeleteConfirm.value = false
  pendingDeleteId.value = null

  try {
    await api.delete(`/api/patients/${patientToDelete.db_id}`)
    await fetchPatients()
    showToast('Patient deleted successfully.', 'success')
  } catch (error) {
    showToast('Failed to delete patient. Please try again.', 'error')
    console.error("Delete failed", error)
  } finally {
    isDeleting.value = false
  }
}

const savePatient = async () => {
  formError.value = ''
  
  if (!newPatient.value.firstName || 
      !newPatient.value.lastName || 
      !newPatient.value.dob || 
      !newPatient.value.gender) {
    formError.value = 'Please fill in all required fields (First Name, Last Name, Date of Birth, Gender)!'
    return
  }

  if (isSaving.value) return
  isSaving.value = true
  const isEdit = !!editingPatientId.value

  // Duplicate Check: Name + DOB + Gender
  const fullName = `${newPatient.value.firstName} ${newPatient.value.lastName}`.trim().toLowerCase()
  const inputDob = newPatient.value.dob
  const inputGender = newPatient.value.gender

  const isDuplicate = patients.value.some(p => {
    if (editingPatientId.value && p.id === editingPatientId.value) return false
    return p.name.toLowerCase() === fullName && p.dob === inputDob && p.gender === inputGender
  })

  if (isDuplicate) {
    formError.value = 'This patient already exists (same Name, Date of Birth, and Gender). Duplicates are not allowed!'
    isSaving.value = false
    return
  }

  const payload = {
    first_name: newPatient.value.firstName,
    last_name: newPatient.value.lastName,
    dob: newPatient.value.dob,
    gender: newPatient.value.gender,
    phone: newPatient.value.phone,
    address: newPatient.value.address
  }

  try {
    if (editingPatientId.value) {
      const patientToEdit = patients.value.find(p => p.id === editingPatientId.value)
      if (patientToEdit && patientToEdit.db_id) {
        await api.put(`/api/patients/${patientToEdit.db_id}`, payload)
      }
    } else {
      const response = await api.post('/api/patients', payload)
      
      // Save to recent patients suggestion queue (for Health Records 24-hour recommendation)
      try {
        if (response && response.status === 'success' && response.patient) {
          const recentStr = localStorage.getItem('recentPatients') || '[]'
          const recent = JSON.parse(recentStr)
          const twentyFourHours = 24 * 60 * 60 * 1000
          const now = Date.now()
          const filtered = recent.filter((r: any) => r.id !== response.patient.id && (now - (r.timestamp || 0)) < twentyFourHours)
          filtered.unshift({ 
            id: response.patient.id, 
            name: response.patient.name, 
            timestamp: now 
          })
          localStorage.setItem('recentPatients', JSON.stringify(filtered))
        }
      } catch (e) {
        console.error('Failed to save recent patient', e)
      }
    }
    
    isAddModalOpen.value = false
    isModalGenderOpen.value = false
    if (!isEdit) {
      currentSort.value = 'Newest'
      currentPage.value = 1 // Instantly switch to page 1 so new patient is in view
    }
    await fetchPatients()
    showToast(isEdit ? 'Patient profile updated successfully.' : 'New patient registered successfully.', 'success')
  } catch (error) {
    showToast('Failed to save patient. Please try again.', 'error')
    console.error("Save failed", error)
  } finally {
    isSaving.value = false
  }
}

// Close all table dropdowns
const closeAllDropdowns = () => {
  isGenderOpen.value = false
  isSortOpen.value = false
  isPageSizeOpen.value = false
}

// Global click outside listener
const handleGlobalClick = (e: MouseEvent) => {
  const target = e.target as HTMLElement | null
  if (target && !target.closest('.dropdown-container')) {
    closeAllDropdowns()
  }
}

onMounted(() => {
  fetchPatients()
  window.addEventListener('click', handleGlobalClick)
})

onUnmounted(() => {
  window.removeEventListener('click', handleGlobalClick)
})
</script>

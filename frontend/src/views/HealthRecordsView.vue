<template>
  <div class="px-8 py-8 bg-slate-50/60 min-h-full">
    
        <div v-if="selectedPatientId" class="space-y-6 animate-fade-in">
          <!-- Back Navigation & Action Bar -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
            <button 
              @click="selectedPatientId = null" 
              class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 rounded-xl border border-slate-200 transition-colors shadow-xs w-fit"
            >
              <ArrowLeft class="w-4 h-4 text-teal-600" />
              <span>Back to Health Records</span>
            </button>

            <div class="flex items-center gap-3">
              <button 
                @click="openNewVisitForCurrentPatient" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-semibold text-sm transition-colors shadow-xs"
              >
                <Plus class="w-4 h-4" />
                <span>Add New Record</span>
              </button>
            </div>
          </div>

          <!-- Professional Patient Summary Banner (EHR Standard) -->
          <div v-if="currentPatientInfo" class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
              
              <!-- Patient Identification -->
              <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-2xl font-heading shadow-xs border border-teal-200 shrink-0">
                  {{ currentPatientInfo.patientName.charAt(0) }}
                </div>
                <div>
                  <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-2xl font-extrabold text-slate-900 font-heading tracking-tight">{{ currentPatientInfo.patientName }}</h2>
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200/80 font-mono">
                      MRN: {{ currentPatientInfo.patientId }}
                    </span>
                    <span :class="['px-2.5 py-0.5 rounded-full text-xs font-bold border flex items-center gap-1.5', currentPatientInfo.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200']">
                      <span class="w-1.5 h-1.5 rounded-full" :class="currentPatientInfo.status === 'Active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                      {{ currentPatientInfo.status }} Patient
                    </span>
                  </div>
                  <div class="flex items-center gap-2 text-xs text-slate-500 font-medium mt-1.5 flex-wrap">
                    <span>Sex: <strong class="text-slate-700 font-semibold">{{ currentPatientInfo.gender }}</strong></span>
                    <span class="text-slate-300">&bull;</span>
                    <span v-if="currentPatientInfo.dob">DOB: <strong class="text-slate-700 font-semibold">{{ currentPatientInfo.dob }}</strong></span>
                    <span v-if="currentPatientInfo.dob" class="text-slate-300">&bull;</span>
                    <span>Primary Attending: <strong class="text-slate-700 font-semibold">{{ currentPatientInfo.attendingDoctor || 'Dr. Sarah Jenkins' }}</strong></span>
                  </div>
                </div>
              </div>

              <!-- Quick Clinical Metrics Strip -->
              <div class="grid grid-cols-3 gap-3 w-full lg:w-auto">
                <div class="bg-slate-50/80 rounded-xl px-4 py-3 border border-slate-100 min-w-[110px]">
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Encounters</p>
                  <p class="text-base font-extrabold text-teal-700 mt-0.5">{{ currentPatientRecords.length }} Visits</p>
                </div>
                <div class="bg-slate-50/80 rounded-xl px-4 py-3 border border-slate-100 min-w-[110px]">
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Last Evaluation</p>
                  <p class="text-xs font-bold text-slate-800 mt-1">{{ currentPatientRecords[0]?.date || 'N/A' }}</p>
                </div>
                <div class="bg-slate-50/80 rounded-xl px-4 py-3 border border-slate-100 min-w-[110px]">
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Latest Vitals</p>
                  <p class="text-xs font-bold text-slate-800 mt-1">{{ currentPatientRecords[0]?.bloodPressure || 'N/A' }} <span class="text-[10px] text-slate-400 font-normal">mmHg</span></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Medical Encounters Section -->
          <div class="space-y-4">
            
            <!-- Section Header & Filter Pills -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
              <div class="flex items-center gap-2">
                <Clock class="w-5 h-5 text-teal-600" />
                <h3 class="text-base font-extrabold text-slate-800 tracking-tight">
                  Clinical Encounters History
                </h3>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                  {{ currentPatientRecords.length }}
                </span>
              </div>

              <!-- Filter Pills -->
              <div v-if="uniqueRecordTypes.length > 1" class="flex items-center gap-2 overflow-x-auto pb-1">
                <button 
                  @click="dossierTypeFilter = 'ALL'"
                  :class="['px-3 py-1 rounded-lg text-xs font-bold transition-colors shadow-xs', dossierTypeFilter === 'ALL' ? 'bg-teal-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200']"
                >
                  All ({{ currentPatientRecords.length }})
                </button>
                <button 
                  v-for="t in uniqueRecordTypes" 
                  :key="t.name"
                  @click="dossierTypeFilter = t.name"
                  :class="['px-3 py-1 rounded-lg text-xs font-bold transition-colors shadow-xs', dossierTypeFilter === t.name ? 'bg-teal-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200']"
                >
                  {{ t.name }} ({{ t.count }})
                </button>
              </div>
            </div>

            <!-- Empty State for this patient -->
            <div v-if="filteredDossierRecords.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200/80">
              <FileText class="w-12 h-12 text-slate-300 mx-auto mb-3" />
              <p class="text-slate-600 font-medium">No health records recorded for this patient.</p>
              <button @click="openNewVisitForCurrentPatient" class="mt-4 px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition-colors shadow-xs">
                Add First Record
              </button>
            </div>

            <!-- Connected History Timeline Cards (Newest to Oldest) -->
            <div v-else class="space-y-4">
              <div 
                v-for="(record, index) in filteredDossierRecords" 
                :key="record.id" 
                class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden transition-all hover:border-slate-300"
              >
                <!-- Encounter Card Header -->
                <div class="px-5 py-4 bg-slate-50/70 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <div class="flex items-center gap-3 flex-wrap">
                    <span class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 border border-teal-200/80 font-extrabold text-xs tracking-wide">
                      Encounter #{{ currentPatientRecords.length - getRecordOriginalIndex(record.id) }}
                    </span>
                    <span class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                      <CalendarDays class="w-4 h-4 text-teal-600" />
                      {{ record.date }}
                    </span>
                    <span :class="['px-3 py-0.5 rounded-full text-xs font-bold border', getBadgeClass(record.recordType)]">
                      {{ record.recordType }}
                    </span>
                    <!-- 💵 Consultation Fee Badge -->
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                      <span>${{ Math.round(Number(record.fee || 35)) }}</span>
                      <span class="text-[10px] font-semibold text-emerald-600">({{ record.payment_status || 'Paid' }})</span>
                    </span>
                    <span v-if="getRecordOriginalIndex(record.id) === 0" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-50 text-teal-700 border border-teal-200 uppercase tracking-wider">
                      Latest Visit
                    </span>
                  </div>

                  <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 mr-2 flex items-center gap-1">
                      <User class="w-3.5 h-3.5 text-slate-400" />
                      {{ record.attendingDoctor }}
                    </span>
                    <button @click="openPrintModal(record)" class="p-1.5 text-slate-600 hover:text-teal-700 hover:bg-teal-50 rounded-lg transition-colors border border-slate-200 hover:border-teal-300 cursor-pointer" title="Print Health Record">
                      <Printer class="w-3.5 h-3.5" />
                    </button>
                    <button @click="openEditModal(record)" class="p-1.5 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors border border-slate-200 hover:border-teal-300" title="Edit Record">
                      <Edit class="w-3.5 h-3.5" />
                    </button>
                    <button @click="deleteRecord(record.id)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors border border-slate-200 hover:border-rose-300" title="Delete Record">
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>

                <!-- Clinical Vitals Parameters Grid -->
                <div class="p-5 grid grid-cols-2 sm:grid-cols-4 gap-4 border-b border-slate-100 bg-white">
                  
                  <!-- BP -->
                  <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-100">
                    <div class="flex items-center justify-between">
                      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Blood Pressure</p>
                      <HeartPulse class="w-3.5 h-3.5 text-rose-500" />
                    </div>
                    <p class="text-base font-extrabold text-slate-800 mt-1">
                      {{ record.bloodPressure || 'N/A' }} <span class="text-xs font-normal text-slate-500">mmHg</span>
                    </p>
                    <p v-if="getBpComparison(record.id)" class="text-[11px] font-semibold text-slate-500 mt-1">
                      {{ getBpComparison(record.id) }}
                    </p>
                  </div>

                  <!-- Pulse -->
                  <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-100">
                    <div class="flex items-center justify-between">
                      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pulse Rate</p>
                      <Activity class="w-3.5 h-3.5 text-teal-600" />
                    </div>
                    <p class="text-base font-extrabold text-slate-800 mt-1">
                      {{ record.pulse || 'N/A' }} <span class="text-xs font-normal text-slate-500">bpm</span>
                    </p>
                  </div>

                  <!-- Weight / Height -->
                  <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-100">
                    <div class="flex items-center justify-between">
                      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Weight / Height</p>
                      <Scale class="w-3.5 h-3.5 text-indigo-500" />
                    </div>
                    <p class="text-base font-extrabold text-slate-800 mt-1">
                      {{ record.weightHeight || 'N/A' }}
                    </p>
                    <p v-if="getWeightComparison(record.id)" class="text-[11px] font-semibold text-slate-500 mt-1">
                      {{ getWeightComparison(record.id) }}
                    </p>
                  </div>

                  <!-- BMI -->
                  <div class="p-3.5 bg-slate-50/60 rounded-xl border border-slate-100">
                    <div class="flex items-center justify-between">
                      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">BMI Assessment</p>
                      <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" :class="getBadgeClass(getBmiLabel(record.bmi))">
                        {{ getBmiLabel(record.bmi) }}
                      </span>
                    </div>
                    <p class="text-base font-extrabold text-slate-800 mt-1">
                      {{ record.bmi || 'N/A' }}
                    </p>
                  </div>
                </div>

                <!-- Physician's Clinical Assessment Notes -->
                <div v-if="record.note" class="p-5 border-b border-slate-100 bg-slate-50/40">
                  <div class="border-l-4 border-teal-500 bg-white p-4 rounded-r-xl border border-l-0 border-slate-100 shadow-2xs">
                    <p class="text-[11px] font-bold text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                      <FileText class="w-3.5 h-3.5 text-teal-600" />
                      Physician's Clinical Assessment & Notes
                    </p>
                    <p class="text-[13px] text-slate-700 leading-relaxed whitespace-pre-wrap font-sans">{{ record.note }}</p>
                  </div>
                </div>

                <!-- Attached Diagnostic Documents & Results -->
                <div v-if="record.attachment_url && getAttachments(record.attachment_url).length > 0" class="p-5 bg-white">
                  <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <FileCheck class="w-3.5 h-3.5 text-teal-600" />
                    Attached Lab Results & Diagnostic Reports
                  </p>
                  <div class="flex flex-wrap gap-3 items-center">
                    <template v-for="(fileUrl, fIdx) in getAttachments(record.attachment_url)" :key="fIdx">
                      <div v-if="isImage(fileUrl)" class="flex flex-col gap-1 max-w-[100px]">
                        <div class="relative group rounded-xl overflow-hidden border border-slate-200 shadow-xs hover:ring-2 hover:ring-teal-500 transition-all">
                          <img :src="getFileUrl(fileUrl)" alt="Attachment" class="h-20 w-20 object-cover cursor-pointer" @click="openImagePreview(getFileUrl(fileUrl))" />
                          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 pointer-events-none">
                            <Eye class="w-4 h-4 text-white" />
                          </div>
                        </div>
                        <span class="text-[10px] font-medium text-slate-600 truncate text-center" :title="getOriginalFileName(fileUrl)">{{ getOriginalFileName(fileUrl) }}</span>
                      </div>
                      <a v-else :href="getFileUrl(fileUrl)" target="_blank" class="px-3.5 py-2 bg-slate-50 hover:bg-teal-50 text-slate-700 hover:text-teal-700 rounded-xl font-semibold text-xs flex items-center gap-2 border border-slate-200 hover:border-teal-300 transition-colors shadow-2xs max-w-[260px]">
                        <FileText class="w-4 h-4 text-teal-600 shrink-0" />
                        <span class="truncate" :title="getOriginalFileName(fileUrl)">{{ getOriginalFileName(fileUrl) }}</span>
                      </a>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ========================================================================= -->
        <!-- VIEW 2: MAIN ALL HEALTH RECORDS TABLE (Default Overview) -->
        <!-- ========================================================================= -->
        <div v-else class="space-y-6">
          <div class="flex justify-between items-end">
            <div>
              <h1 class="text-[24px] font-heading font-bold text-slate-900 tracking-tight">Health Records</h1>
              <p class="text-[14px] text-slate-500 mt-1 font-medium">Manage clinical notes, lab results, and patient documents.</p>
            </div>
            <button @click="openAddModal" class="flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-teal-600 text-white rounded-lg font-semibold text-[13px] sm:text-sm hover:bg-teal-700 transition-colors shadow-sm cursor-pointer shrink-0 whitespace-nowrap">
              <Plus class="w-4 h-4 shrink-0" />
              <span class="hidden sm:inline">Add Health Record</span>
              <span class="sm:hidden">Add Record</span>
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
                    <button @click.stop="isGenderOpen = !isGenderOpen; isStatusOpen = false; isRangeOpen = false; isSortOpen = false" class="relative w-full xl:w-[120px] py-2.5 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-600 focus:outline-none cursor-pointer flex items-center hover:bg-white transition-colors text-left shadow-sm">
                      <span class="truncate block w-full">{{ filterGender }}</span>
                      <ChevronDown class="absolute right-3 w-4 h-4 text-slate-400 pointer-events-none transition-transform duration-200" :class="{'rotate-180': isGenderOpen}" />
                    </button>
                    <div v-if="isGenderOpen" class="absolute top-[68px] left-0 w-full bg-white border border-slate-100 rounded-lg shadow-xl z-50 overflow-hidden py-1 animate-fade-in">
                      <div @click="filterGender = '(All)'; isGenderOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterGender === '(All)'}">(All)</div>
                      <div @click="filterGender = 'Male'; isGenderOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterGender === 'Male'}">Male</div>
                      <div @click="filterGender = 'Female'; isGenderOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterGender === 'Female'}">Female</div>
                    </div>
                  </div>

                  <!-- Custom Status Dropdown -->
                  <div class="flex flex-col gap-1 relative dropdown-container">
                    <label class="text-[12px] font-semibold text-slate-700">Status</label>
                    <button @click.stop="isStatusOpen = !isStatusOpen; isGenderOpen = false; isRangeOpen = false; isSortOpen = false" class="relative w-full xl:w-[120px] py-2.5 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-600 focus:outline-none cursor-pointer flex items-center hover:bg-white transition-colors text-left shadow-sm">
                      <span class="truncate block w-full">{{ filterStatus }}</span>
                      <ChevronDown class="absolute right-3 w-4 h-4 text-slate-400 pointer-events-none transition-transform duration-200" :class="{'rotate-180': isStatusOpen}" />
                    </button>
                    <div v-if="isStatusOpen" class="absolute top-[68px] left-0 w-full bg-white border border-slate-100 rounded-lg shadow-xl z-50 overflow-hidden py-1 animate-fade-in">
                      <div @click="filterStatus = 'Active'; isStatusOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterStatus === 'Active'}">Active</div>
                      <div @click="filterStatus = 'Inactive'; isStatusOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterStatus === 'Inactive'}">Inactive</div>
                    </div>
                  </div>

                  <!-- Custom Range Dropdown -->
                  <div class="flex flex-col gap-1 relative dropdown-container">
                    <label class="text-[12px] font-semibold text-slate-700">Last Visited</label>
                    <button @click.stop="isRangeOpen = !isRangeOpen; isGenderOpen = false; isStatusOpen = false; isSortOpen = false" class="relative w-full xl:w-[130px] py-2.5 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-600 focus:outline-none cursor-pointer flex items-center hover:bg-white transition-colors text-left shadow-sm">
                      <span class="truncate block w-full">{{ filterRange }}</span>
                      <ChevronDown class="absolute right-3 w-4 h-4 text-slate-400 pointer-events-none transition-transform duration-200" :class="{'rotate-180': isRangeOpen}" />
                    </button>
                    <div v-if="isRangeOpen" class="absolute top-[68px] right-0 w-full bg-white border border-slate-100 rounded-lg shadow-xl z-50 overflow-hidden py-1 animate-fade-in">
                      <div @click="filterRange = '(Last Month)'; isRangeOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterRange === '(Last Month)'}">(Last Month)</div>
                      <div @click="filterRange = '(Last 3 Months)'; isRangeOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterRange === '(Last 3 Months)'}">(Last 3 Months)</div>
                      <div @click="filterRange = '(Last Year)'; isRangeOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': filterRange === '(Last Year)'}">(Last Year)</div>
                    </div>
                  </div>

                  <!-- Custom Sort Dropdown -->
                  <div class="flex flex-col gap-1 relative dropdown-container">
                    <label class="text-[12px] font-semibold text-slate-700">Sort By</label>
                    <button @click.stop="isSortOpen = !isSortOpen; isGenderOpen = false; isStatusOpen = false; isRangeOpen = false" class="relative w-full xl:w-[140px] py-2.5 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-600 focus:outline-none cursor-pointer flex items-center hover:bg-white transition-colors text-left shadow-sm">
                      <span class="truncate block w-full">{{ currentSort }}</span>
                      <ChevronDown class="absolute right-3 w-4 h-4 text-slate-400 pointer-events-none transition-transform duration-200" :class="{'rotate-180': isSortOpen}" />
                    </button>
                    <div v-if="isSortOpen" class="absolute top-[68px] right-0 w-[160px] bg-white border border-slate-100 rounded-lg shadow-xl z-50 overflow-hidden py-1 animate-fade-in">
                      <div @click="currentSort = 'Newest First'; isSortOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': currentSort === 'Newest First'}">Newest First</div>
                      <div @click="currentSort = 'Oldest First'; isSortOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': currentSort === 'Oldest First'}">Oldest First</div>
                      <div @click="currentSort = 'Name (A to Z)'; isSortOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': currentSort === 'Name (A to Z)'}">Name (A to Z)</div>
                      <div @click="currentSort = 'Name (Z to A)'; isSortOpen = false" class="px-3 py-2 text-[13px] text-slate-600 hover:bg-slate-50 hover:text-teal-600 cursor-pointer transition-colors" :class="{'bg-teal-50 text-teal-700 font-medium': currentSort === 'Name (Z to A)'}">Name (Z to A)</div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <!-- Table Body with Fixed Consistent Height -->
            <div class="overflow-x-auto min-h-[440px] flex flex-col">
              <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="bg-slate-800 text-white">
                  <tr class="h-12">
                    <th class="px-6 font-semibold text-[13px] tracking-wide">Date</th>
                    <th class="px-6 font-semibold text-[13px] tracking-wide">Patient</th>
                    <th class="px-6 font-semibold text-[13px] tracking-wide">Record Type</th>
                    <th class="px-6 font-semibold text-[13px] tracking-wide">Status</th>
                    <th class="px-6 font-semibold text-[13px] tracking-wide text-center">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="record in paginatedRecords" :key="record.id" class="h-[68px] hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4 text-sm text-slate-600 font-medium">{{ formatDisplayDate(record.date) }}</td>
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs shrink-0">
                          {{ record.patientName.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                          <div class="font-bold text-slate-800 text-sm hover:text-teal-600 cursor-pointer transition-colors" @click="viewPatientDossier(record.patientId)">
                            {{ record.patientName }}
                          </div>
                          <div class="text-xs text-slate-400 mt-0.5">({{ record.patientId }})</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span :class="['px-3 py-1 rounded-full text-xs font-bold border inline-flex items-center', getBadgeClass(record.recordType)]">
                        {{ record.recordType }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <span :class="['px-2.5 py-0.5 rounded-full text-xs font-bold border inline-flex items-center gap-1.5', record.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200']">
                        <span class="w-1.5 h-1.5 rounded-full" :class="record.status === 'Active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                        {{ record.status || 'Active' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                      <div class="flex items-center justify-center gap-2">
                        <button @click="viewPatientDossier(record.patientId)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-100 cursor-pointer" title="View Patient Medical Dossier">
                          <Eye class="w-4 h-4" />
                        </button>
                        <button @click="openEditModal(record)" class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors border border-transparent hover:border-teal-100 cursor-pointer" title="Edit Record">
                          <Edit class="w-4 h-4" />
                        </button>
                        <button @click="confirmDeleteRecord(record.id)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors border border-transparent hover:border-rose-100 cursor-pointer" title="Delete Record">
                          <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                  
                  <!-- Empty State (Keeps same balanced height) -->
                  <tr v-if="filteredRecords.length === 0">
                    <td colspan="5" class="px-6 py-24 text-center bg-slate-50/30">
                      <div class="flex flex-col items-center justify-center text-slate-400">
                        <FileText class="w-12 h-12 mb-3 text-slate-300" />
                        <p class="text-[15px] font-medium text-slate-600">No health records found</p>
                        <p class="text-[13px] mt-1 text-slate-400">Try adjusting your filters or search query.</p>
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
                  Showing {{ filteredRecords.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredRecords.length) }} of {{ filteredRecords.length }} entries
                </span>
                
                <!-- Clean Custom Rows Dropdown -->
                <div class="flex items-center gap-2 border-l border-slate-200 pl-4 relative dropdown-container">
                  <span class="text-xs text-slate-400 font-medium">Rows:</span>
                  <button 
                    @click.stop="isPageSizeOpen = !isPageSizeOpen; isGenderOpen = false; isStatusOpen = false; isRangeOpen = false; isSortOpen = false" 
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
        </div>

        <!-- Add/Edit Health Record Modal -->
        <div v-if="isAddModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center">
          <div class="absolute inset-0 bg-slate-900/40" @click="isAddModalOpen = false"></div>
          
          <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-scale-up">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
              <h3 class="text-lg font-bold text-slate-800">{{ editingRecordId ? 'Edit Health Record' : 'Add New Health Record' }}</h3>
              <button @click="isAddModalOpen = false" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                <X class="w-5 h-5" />
              </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto">
              <form ref="recordForm" @submit.prevent="saveRecord" class="space-y-6">
                <!-- Patient Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                  <div class="space-y-1.5">
                    <label class="text-[13px] font-medium text-slate-700">Patient Name <span class="text-rose-500">*</span></label>
                    <input list="recent-patients" type="text" v-model="newRecord.patientName" @keydown.enter.prevent="recordForm?.requestSubmit()" required placeholder="e.g. John Doe" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" />
                    <datalist id="recent-patients">
                      <option v-for="p in suggestedPatients" :key="p.id" :value="p.name">{{ p.id }}</option>
                    </datalist>
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-[13px] font-medium text-slate-700">Patient ID</label>
                    <input type="text" v-model="newRecord.patientId" placeholder="Auto-generated if blank" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" />
                  </div>
                </div>

                <!-- Record Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                  <div class="space-y-1.5 relative">
                    <label class="text-[13px] font-medium text-slate-700">Record Type <span class="text-rose-500">*</span></label>
                    <div v-if="isTypeOpen" @click="isTypeOpen = false" class="fixed inset-0 z-40"></div>
                    <div class="relative w-full flex items-center">
                      <input 
                        type="text" 
                        v-model="newRecord.recordType"  
                        @focus="isTypeOpen = true"
                        placeholder="Select or type a record type..."
                        class="w-full pl-3 pr-10 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors"
                      />
                      <ChevronDown class="absolute right-3 w-4 h-4 text-slate-400 pointer-events-none transition-transform duration-200" :class="{'rotate-180': isTypeOpen}" />
                    </div>
                    <!-- Custom Dropdown Menu -->
                    <div v-if="isTypeOpen" class="absolute top-[62px] left-0 w-full bg-white border border-slate-100 rounded-lg shadow-xl z-50 py-1 animate-fade-in">
                      <button type="button" @click="newRecord.recordType = 'General Checkup'; isTypeOpen = false" class="w-full text-left px-3 py-2 text-[13px] hover:bg-slate-50 hover:text-teal-600 transition-colors">General Checkup</button>
                      <button type="button" @click="newRecord.recordType = 'Lab Results'; isTypeOpen = false" class="w-full text-left px-3 py-2 text-[13px] hover:bg-slate-50 hover:text-teal-600 transition-colors">Lab Results</button>
                      <button type="button" @click="newRecord.recordType = 'Routine Exam'; isTypeOpen = false" class="w-full text-left px-3 py-2 text-[13px] hover:bg-slate-50 hover:text-teal-600 transition-colors">Routine Exam</button>
                      <button type="button" @click="newRecord.recordType = 'Cardiology Evaluation'; isTypeOpen = false" class="w-full text-left px-3 py-2 text-[13px] hover:bg-slate-50 hover:text-teal-600 transition-colors">Cardiology Evaluation</button>
                      <button type="button" @click="newRecord.recordType = 'Endocrinology Follow-up'; isTypeOpen = false" class="w-full text-left px-3 py-2 text-[13px] hover:bg-slate-50 hover:text-teal-600 transition-colors">Endocrinology Follow-up</button>
                    </div>
                  </div>
                  <div class="space-y-1.5 relative">
                    <label class="text-[13px] font-medium text-slate-700">Date <span class="text-rose-500">*</span></label>
                    <div v-if="isDateOpen" @click="isDateOpen = false" class="fixed inset-0 z-40"></div>
                    <button type="button" @click="isDateOpen = !isDateOpen" class="relative w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 focus:outline-none focus:border-teal-500 transition-colors text-left flex items-center justify-between">
                      <span :class="formattedDate ? 'text-slate-800' : 'text-slate-400'" class="flex items-center">
                        <CalendarCheck class="w-4 h-4 mr-2 text-slate-400"/>
                        {{ formattedDate || 'Select date' }}
                      </span>
                      <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': isDateOpen}" />
                    </button>
                    <!-- Custom Date Popover -->
                    <div v-if="isDateOpen" class="absolute top-[62px] right-0 w-full sm:w-[320px] bg-white border border-slate-100 rounded-xl shadow-xl z-50 p-4 animate-fade-in origin-top-right">
                      <div class="flex gap-3 h-[200px]">
                        <!-- Month -->
                        <div class="flex-1 flex flex-col">
                          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">Month</span>
                          <div class="flex-1 overflow-y-auto space-y-1 pr-1 pb-4" style="scrollbar-width: none;">
                            <button type="button" v-for="m in months" :key="m.value" @click="recMonth = m.value" :class="['w-full py-2 text-[13px] rounded-lg transition-colors', recMonth === m.value ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 hover:bg-slate-50']">
                              {{ m.label }}
                            </button>
                          </div>
                        </div>
                        <!-- Day -->
                        <div class="flex-1 flex flex-col border-l border-slate-100 pl-3">
                          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">Day</span>
                          <div class="flex-1 overflow-y-auto space-y-1 pr-1 pb-4" style="scrollbar-width: none;">
                            <button type="button" v-for="d in 31" :key="d" @click="recDay = d" :class="['w-full py-2 text-[13px] rounded-lg transition-colors', recDay == d ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 hover:bg-slate-50']">
                              {{ d }}
                            </button>
                          </div>
                        </div>
                        <!-- Year -->
                        <div class="flex-1 flex flex-col border-l border-slate-100 pl-3">
                          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">Year</span>
                          <div class="flex-1 overflow-y-auto space-y-1 pr-1 pb-4" style="scrollbar-width: none;">
                            <button type="button" v-for="y in 20" :key="y" @click="recYear = currentYear - y + 6" :class="['w-full py-2 text-[13px] rounded-lg transition-colors', recYear == (currentYear - y + 6) ? 'bg-teal-50 text-teal-700 font-bold' : 'text-slate-600 hover:bg-slate-50']">
                              {{ currentYear - y + 6 }}
                            </button>
                          </div>
                        </div>
                      </div>
                      <div class="mt-5 pt-4 border-t border-slate-100">
                        <button type="button" @click="isDateOpen = false" class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-[13px] font-bold rounded-lg transition-colors">
                          Confirm Date
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Vitals -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                  <div class="space-y-1.5">
                    <label class="text-[13px] font-medium text-slate-700">Blood Pressure</label>
                    <input list="bp-options" ref="bpInput" v-model="newRecord.bloodPressure" @keydown.enter.prevent="recordForm?.requestSubmit()" placeholder="120/80" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" />
                    <datalist id="bp-options">
                      <option value="90/60"></option>
                      <option value="100/60"></option>
                      <option value="100/70"></option>
                      <option value="110/70"></option>
                      <option value="115/75"></option>
                      <option value="120/80"></option>
                      <option value="125/80"></option>
                      <option value="130/80"></option>
                      <option value="130/85"></option>
                      <option value="135/85"></option>
                      <option value="140/90"></option>
                      <option value="145/90"></option>
                      <option value="150/95"></option>
                      <option value="160/100"></option>
                    </datalist>
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-[13px] font-medium text-slate-700">Pulse (bpm)</label>
                    <input list="pulse-options" ref="pulseInput" type="number" v-model="newRecord.pulse" @keydown.enter.prevent="recordForm?.requestSubmit()" placeholder="72" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" />
                    <datalist id="pulse-options">
                      <option value="60"></option>
                      <option value="65"></option>
                      <option value="70"></option>
                      <option value="72"></option>
                      <option value="75"></option>
                      <option value="80"></option>
                      <option value="85"></option>
                      <option value="90"></option>
                      <option value="95"></option>
                      <option value="100"></option>
                      <option value="105"></option>
                      <option value="110"></option>
                      <option value="120"></option>
                    </datalist>
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-[13px] font-medium text-slate-700">BMI</label>
                    <input type="number" step="0.1" v-model="newRecord.bmi" placeholder="Auto-calculated" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" readonly />
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                  <div class="space-y-1.5">
                    <label class="text-[13px] font-medium text-slate-700">Weight (kg)</label>
                    <input list="weight-options" type="number" ref="weightInput" step="0.1" v-model="newRecord.weight" @keydown.enter.prevent="recordForm?.requestSubmit()" placeholder="60" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" />
                    <datalist id="weight-options">
                      <option value="40"></option><option value="45"></option><option value="50"></option>
                      <option value="55"></option><option value="60"></option><option value="65"></option>
                      <option value="70"></option><option value="75"></option><option value="80"></option>
                      <option value="85"></option><option value="90"></option><option value="95"></option>
                      <option value="100"></option><option value="110"></option><option value="120"></option>
                    </datalist>
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-[13px] font-medium text-slate-700">Height (m)</label>
                    <input list="height-options" type="number" ref="heightInput" step="0.01" v-model="newRecord.height" @keydown.enter.prevent="recordForm?.requestSubmit()" placeholder="1.65" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" />
                    <datalist id="height-options">
                      <option value="1.40"></option><option value="1.45"></option><option value="1.50"></option>
                      <option value="1.55"></option><option value="1.60"></option><option value="1.65"></option>
                      <option value="1.70"></option><option value="1.75"></option><option value="1.80"></option>
                      <option value="1.85"></option><option value="1.90"></option><option value="1.95"></option>
                    </datalist>
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-[13px] font-medium text-slate-700">Attending Doctor</label>
                    <input list="doctor-options" ref="doctorInput" type="text" v-model="newRecord.attendingDoctor"  @keydown.enter.prevent="recordForm?.requestSubmit()" placeholder="Dr. Name" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors" />
                    <datalist id="doctor-options">
                      <option :value="getLoggedInDoctorDefault()"></option>
                      <option value="Dr. Sarah Jenkins"></option>
                      <option value="Dr. James Wilson"></option>
                      <option value="Dr. Amara Okonkwo"></option>
                      <option value="Dr. Michael Chen"></option>
                      <option value="Dr. Elena Rodriguez"></option>
                      <option value="Dr. David Smith"></option>
                      <option value="Dr. Sophia Patel"></option>
                    </datalist>
                  </div>
                </div>

                <!-- 💵 Consultation Fee & Billing Section -->
                <div class="p-4 bg-teal-50/50 rounded-xl border border-teal-100 space-y-3 mt-4">
                  <div class="flex items-center justify-between">
                    <label class="text-[13px] font-bold text-slate-800 flex items-center gap-1.5">
                      <span>Consultation Fee & Billing ($)</span>
                    </label>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1 relative" ref="feeDropdownRef">
                      <label class="text-[12px] font-semibold text-slate-600">Amount (USD)</label>
                      <div class="relative flex items-center">
                        <span class="absolute left-3 text-slate-400 font-bold text-sm pointer-events-none">$</span>
                        <input 
                          type="number" 
                          step="1" 
                          min="0"
                          v-model="newRecord.fee" 
                          placeholder="35" 
                          @focus="isFeeMenuOpen = true; ($event.target as HTMLInputElement).select()"
                          @click="isFeeMenuOpen = true"
                          class="w-full pl-7 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-[13px] font-bold text-slate-800 focus:outline-none focus:border-teal-500 transition-colors" 
                        />
                        <button 
                          type="button" 
                          @click.stop="isFeeMenuOpen = !isFeeMenuOpen"
                          class="absolute right-2 text-slate-400 hover:text-slate-600 p-1 cursor-pointer"
                        >
                          <ChevronDown class="w-3.5 h-3.5 transition-transform duration-200" :class="{'rotate-180': isFeeMenuOpen}" />
                        </button>
                      </div>

                      <!-- 🌟 Custom Suggestions Floating Popover (Opens Immediately on Click!) -->
                      <Transition name="fade">
                        <div 
                          v-if="isFeeMenuOpen" 
                          class="absolute top-full left-0 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden py-1 max-h-48 overflow-y-auto"
                        >
                          <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                            Suggested Fees
                          </div>
                          <button
                            v-for="amt in [10, 15, 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 100]"
                            :key="amt"
                            type="button"
                            @click="newRecord.fee = amt; isFeeMenuOpen = false"
                            class="w-full px-3 py-2 text-left text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:text-teal-700 flex items-center justify-between transition-colors cursor-pointer"
                            :class="{'bg-teal-50/80 text-teal-700 font-bold': Number(newRecord.fee) === amt}"
                          >
                            <span>${{ amt }} USD</span>
                            <span v-if="Number(newRecord.fee) === amt" class="text-[10px] bg-teal-600 text-white px-1.5 py-0.5 rounded font-bold">Selected</span>
                          </button>
                        </div>
                      </Transition>
                    </div>

                    <div class="space-y-1">
                      <label class="text-[12px] font-semibold text-slate-600">Payment Status</label>
                      <div class="grid grid-cols-3 gap-1.5 h-[38px]">
                        <button
                          type="button"
                          @click="newRecord.payment_status = 'Paid'"
                          :class="[
                            'h-full rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 border',
                            newRecord.payment_status === 'Paid' 
                              ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs' 
                              : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                          ]"
                        >
                          <span class="w-1.5 h-1.5 rounded-full" :class="newRecord.payment_status === 'Paid' ? 'bg-white' : 'bg-emerald-500'"></span>
                          <span>Paid</span>
                        </button>

                        <button
                          type="button"
                          @click="newRecord.payment_status = 'Pending'"
                          :class="[
                            'h-full rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 border',
                            newRecord.payment_status === 'Pending' 
                              ? 'bg-amber-500 text-white border-amber-500 shadow-2xs' 
                              : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                          ]"
                        >
                          <span class="w-1.5 h-1.5 rounded-full" :class="newRecord.payment_status === 'Pending' ? 'bg-white' : 'bg-amber-500'"></span>
                          <span>Pending</span>
                        </button>

                        <button
                          type="button"
                          @click="newRecord.payment_status = 'Waived'"
                          :class="[
                            'h-full rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 border',
                            newRecord.payment_status === 'Waived' 
                              ? 'bg-slate-700 text-white border-slate-700 shadow-2xs' 
                              : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                          ]"
                        >
                          <span class="w-1.5 h-1.5 rounded-full" :class="newRecord.payment_status === 'Waived' ? 'bg-white' : 'bg-slate-400'"></span>
                          <span>Waived</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-1.5 mt-4">
                  <label class="text-[13px] font-medium text-slate-700">Note / Additional Information</label>
                  <textarea ref="noteInput" v-model="newRecord.note" @keydown.enter.exact.prevent="recordForm?.requestSubmit()" rows="3" placeholder="Add any clinical notes, symptoms, or observations here..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-colors resize-y"></textarea>
                  <p class="text-[11px] text-slate-400">Press <kbd class="bg-slate-100 border border-slate-200 px-1 py-0.5 rounded text-[10px]">Enter</kbd> to save. Use <kbd class="bg-slate-100 border border-slate-200 px-1 py-0.5 rounded text-[10px]">Shift + Enter</kbd> for a new line.</p>
                </div>

                <div class="space-y-1.5 mt-4">
                  <label class="text-[13px] font-medium text-slate-700">Attachment (Picture/PDF)</label>
                  <div class="relative">
                    <input type="file" ref="fileInput" id="file-upload" @change="handleFileUpload" accept="image/*,.pdf" multiple class="hidden" :disabled="existingAttachments.length >= 5" />
                    <label for="file-upload" tabindex="0" @keydown.enter.prevent="fileInput?.click()" @keydown.space.prevent="fileInput?.click()" :class="['flex items-center justify-center px-4 py-3 text-[13px] font-medium rounded-lg transition-colors border border-dashed w-full group focus:outline-none focus:ring-2 focus:ring-teal-500', existingAttachments.length >= 5 ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' : 'cursor-pointer bg-slate-50 hover:bg-teal-50 text-slate-600 hover:text-teal-700 border-slate-200 hover:border-teal-300']">
                      <FileText class="w-4 h-4 mr-2" :class="existingAttachments.length >= 5 ? 'text-slate-400' : 'text-slate-400 group-hover:text-teal-500'" /> 
                      <span class="truncate max-w-[200px] sm:max-w-xs">
                        {{ existingAttachments.length >= 5 ? 'Maximum 5 files reached' : (selectedFiles.length > 0 ? selectedFiles.length + ' file(s) selected' : 'Click to Upload up to ' + (5 - existingAttachments.length) + ' Files') }}
                      </span>
                    </label>
                  </div>
                  <!-- Show newly selected files before upload -->
                  <div v-if="selectedFiles.length > 0" class="mt-3">
                    <p class="text-[12px] font-medium text-slate-500 mb-2">Selected for Upload:</p>
                    <div class="space-y-2">
                      <div v-for="(file, index) in selectedFiles" :key="'new'+index" class="flex items-center justify-between p-2 bg-teal-50 border border-teal-100 rounded-lg">
                        <span class="text-[12px] text-teal-700 truncate max-w-[250px]"><FileText class="w-3 h-3 inline mr-1" />{{ file.name }}</span>
                        <button type="button" @click.prevent="removeSelectedFile(index)" class="text-teal-600 hover:text-rose-500 p-1 bg-white rounded-full shadow-sm">
                          <X class="w-3 h-3" />
                        </button>
                      </div>
                    </div>
                  </div>
                  <!-- Show existing attachments when editing -->
                  <div v-if="editingRecordId && existingAttachments.length > 0" class="mt-3">
                    <p class="text-[12px] font-medium text-slate-500 mb-2">Current Attachments:</p>
                    <div class="flex flex-wrap gap-2">
                      <div v-for="(fileUrl, index) in existingAttachments" :key="'old'+index" class="relative group">
                        <a v-if="isImage(fileUrl)" href="#" @click.prevent="openImagePreview(getFileUrl(fileUrl))" class="block rounded-md overflow-hidden border border-slate-200 shadow-sm transition-all cursor-pointer">
                          <img :src="getFileUrl(fileUrl)" alt="Attachment" class="h-12 w-12 object-cover" />
                        </a>
                        <a v-else :href="getFileUrl(fileUrl)" target="_blank" class="text-[11px] px-2.5 py-1.5 bg-slate-100 text-blue-600 hover:bg-slate-200 rounded flex items-center h-12 gap-1.5 transition-colors border border-slate-200 max-w-[180px]">
                          <FileText class="w-3.5 h-3.5 shrink-0" />
                          <span class="truncate" :title="getOriginalFileName(fileUrl)">{{ getOriginalFileName(fileUrl) }}</span>
                        </a>
                        <button type="button" @click.prevent="removeExistingAttachment(index)" class="absolute -top-1.5 -right-1.5 bg-white text-slate-400 hover:text-rose-500 rounded-full border border-slate-200 p-0.5 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity">
                          <X class="w-3 h-3" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3 mt-8">
                  <button type="button" @click="isAddModalOpen = false" :disabled="isSaving" class="px-5 py-2.5 text-[14px] font-semibold text-slate-600 hover:bg-slate-50 rounded-lg transition-colors disabled:opacity-40">
                    Cancel
                  </button>
                  <button type="submit" :disabled="isSaving" class="px-5 py-2.5 text-[14px] font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition-colors shadow-sm disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2">
                    <span v-if="isSaving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ isSaving ? 'Saving...' : (editingRecordId ? 'Update Record' : 'Save Record') }}</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

      <!-- Image Preview Lightbox -->
      <div v-if="isImagePreviewOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" @click="isImagePreviewOpen = false">
        <div class="relative max-w-5xl w-full max-h-[90vh] flex flex-col items-center justify-center" @click.stop>
          <div class="absolute -top-12 right-0 flex items-center gap-3">
            <button type="button" @click.prevent="forceDownload(previewImageUrl)" class="text-white hover:text-teal-400 transition-colors bg-white/10 hover:bg-white/20 rounded-full p-2" title="Download Image">
              <Download class="w-6 h-6" />
            </button>
            <button type="button" @click="isImagePreviewOpen = false" class="text-white hover:text-rose-400 transition-colors bg-white/10 hover:bg-white/20 rounded-full p-2" title="Close">
              <X class="w-6 h-6" />
            </button>
          </div>
          <img :src="previewImageUrl" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain ring-1 ring-white/20" alt="Preview" />
        </div>
      </div>

    <!-- ========================================================================= -->
    <!-- CLINICAL RECORD & PRESCRIPTION PRINT PREVIEW MODAL -->
    <div v-if="isPrintModalOpen" class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs no-print transition-opacity" @click="isPrintModalOpen = false"></div>
      
      <!-- Modal Wrapper -->
      <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-200/90 z-10 my-auto flex flex-col overflow-hidden max-h-[95vh] animate-scale-up" @click.stop>
        
        <!-- Controls & Options Header (Hidden in Print) -->
        <div class="px-6 py-4 bg-slate-900 text-white flex flex-col md:flex-row md:items-center justify-between gap-4 shrink-0 no-print">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center border border-teal-500/30">
              <Printer class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-bold font-heading tracking-tight text-white leading-snug">Hospital Medical Report & Prescription</h3>
              <p class="text-xs text-slate-400">Standard A4 Clinical Lab & Encounter Sheet</p>
            </div>
          </div>

          <!-- Section Toggles & Actions -->
          <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700 text-xs font-semibold text-slate-300">
              <label class="flex items-center gap-1.5 cursor-pointer hover:text-white">
                <input type="checkbox" v-model="printOptions.vitals" class="rounded accent-teal-500 w-3.5 h-3.5 cursor-pointer" />
                <span>Vitals / Lab</span>
              </label>
              <span class="text-slate-600">|</span>
              <label class="flex items-center gap-1.5 cursor-pointer hover:text-white">
                <input type="checkbox" v-model="printOptions.notes" class="rounded accent-teal-500 w-3.5 h-3.5 cursor-pointer" />
                <span>Clinical Notes</span>
              </label>
              <span class="text-slate-600">|</span>
              <label class="flex items-center gap-1.5 cursor-pointer hover:text-white">
                <input type="checkbox" v-model="printOptions.prescription" class="rounded accent-teal-500 w-3.5 h-3.5 cursor-pointer" />
                <span>Rx (Meds)</span>
              </label>
              <span class="text-slate-600">|</span>
              <label class="flex items-center gap-1.5 cursor-pointer hover:text-white">
                <input type="checkbox" v-model="printOptions.signature" class="rounded accent-teal-500 w-3.5 h-3.5 cursor-pointer" />
                <span>Signature</span>
              </label>
            </div>

            <button 
              type="button" 
              @click="triggerPrint"
              class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded-xl font-bold text-xs shadow-md shadow-teal-600/30 transition-all cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Print Document (A4)</span>
            </button>

            <button 
              type="button" 
              @click="isPrintModalOpen = false"
              class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-colors cursor-pointer"
              title="Close Preview"
            >
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Scrollable Document Preview Body (Dark Desk Canvas) -->
        <div ref="printScrollContainer" class="p-4 sm:p-8 overflow-y-auto bg-slate-900/95 flex justify-center items-start max-h-[82vh] print:p-0 print:bg-white print:max-h-none print:overflow-visible">
          
          <!-- Distinct Floating White A4 Paper Sheet -->
          <div 
            id="printable-medical-record" 
            class="w-full max-w-[780px] bg-white shadow-2xl rounded-sm border border-slate-200 p-8 text-slate-900 font-sans text-xs my-2 ring-8 ring-black/20 print:shadow-none print:border-none print:rounded-none print:p-0 print:m-0 print:w-full print:max-w-none print:ring-0"
          >
            <!-- Official Hospital Letterhead Top Header -->
            <div class="flex items-center justify-between pb-3 border-b-2 border-slate-900 gap-4 mb-3.5">
              <div class="flex items-center gap-3">
                <img :src="hospitalLogo" alt="DMR Hospital" class="w-12 h-12 object-contain shrink-0" style="width: 48px; height: 48px; max-width: 48px; max-height: 48px;" />
                <div>
                  <h1 class="text-lg font-black font-heading text-slate-900 tracking-tight leading-none uppercase">DMR HOSPITAL</h1>
                  <p class="text-[9.5px] font-bold text-teal-700 tracking-widest uppercase mt-0.5">We'll Treat You Well &bull; Digital Medical Records</p>
                </div>
              </div>
            </div>

            <!-- Formal Patient Demographics Box (Clean Fixed-Grid Alignment) -->
            <div class="border border-slate-400 mb-4.5 text-[11px] leading-relaxed bg-slate-50/40">
              <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-slate-400">
                
                <!-- Left Details (Aligned Grid) -->
                <div class="p-3.5 space-y-2">
                  <div class="grid grid-cols-[115px_12px_1fr] items-center">
                    <span class="font-bold text-slate-700 whitespace-nowrap">Name</span>
                    <span class="text-slate-500 font-bold">:</span>
                    <span class="font-black text-slate-900">{{ printingRecord?.patientName }}</span>
                  </div>
                  <div class="grid grid-cols-[115px_12px_1fr] items-center">
                    <span class="font-bold text-slate-700 whitespace-nowrap">Age / Gender</span>
                    <span class="text-slate-500 font-bold">:</span>
                    <span class="text-slate-800 font-medium">{{ getPatientAge(printingRecord) }} Years / {{ printingRecord?.gender }}</span>
                  </div>
                  <div class="grid grid-cols-[115px_12px_1fr] items-center">
                    <span class="font-bold text-slate-700 whitespace-nowrap">MRN / PID</span>
                    <span class="text-slate-500 font-bold">:</span>
                    <span class="font-sans font-bold text-slate-900">{{ printingRecord?.patientId }}</span>
                  </div>
                  <div class="grid grid-cols-[115px_12px_1fr] items-center">
                    <span class="font-bold text-slate-700 whitespace-nowrap">Referred By</span>
                    <span class="text-slate-500 font-bold">:</span>
                    <span class="text-slate-800">{{ currentDoctorName }}</span>
                  </div>
                  <div class="grid grid-cols-[115px_12px_1fr] items-center">
                    <span class="font-bold text-slate-700 whitespace-nowrap">Referring Org</span>
                    <span class="text-slate-500 font-bold">:</span>
                    <span class="text-slate-700">DMR MEDICAL CENTRE</span>
                  </div>
                </div>

                <!-- Right Details (Aligned Grid) -->
                <div class="p-3.5 space-y-2">
                  <div class="grid grid-cols-[115px_12px_1fr] items-center">
                    <span class="font-bold text-slate-700 whitespace-nowrap">Encounter Date</span>
                    <span class="text-slate-500 font-bold">:</span>
                    <span class="text-slate-800 font-medium">{{ formatDisplayDate(printingRecord?.date || '') }}</span>
                  </div>
                  <div class="grid grid-cols-[115px_12px_1fr] items-center">
                    <span class="font-bold text-slate-700 whitespace-nowrap">Examined On</span>
                    <span class="text-slate-500 font-bold">:</span>
                    <span class="text-slate-800">{{ formatDisplayDate(printingRecord?.date || '') }} 09:30</span>
                  </div>
                  <div class="grid grid-cols-[115px_12px_1fr] items-center">
                    <span class="font-bold text-slate-700 whitespace-nowrap">Reported On</span>
                    <span class="text-slate-500 font-bold">:</span>
                    <span class="text-slate-800">{{ currentFormattedDateOnly }} 10:15</span>
                  </div>
                  <div class="grid grid-cols-[115px_12px_1fr] items-center">
                    <span class="font-bold text-slate-700 whitespace-nowrap">Encounter Status</span>
                    <span class="text-slate-500 font-bold">:</span>
                    <span class="font-bold text-emerald-800 uppercase">FINAL / COMPLETED</span>
                  </div>
                  <div class="grid grid-cols-[115px_12px_1fr] items-center">
                    <span class="font-bold text-slate-700 whitespace-nowrap">Consultation Fee</span>
                    <span class="text-slate-500 font-bold">:</span>
                    <span class="font-bold text-slate-900 font-mono">${{ Math.round(Number(printingRecord?.fee || 35)) }} USD ({{ printingRecord?.payment_status || 'Paid' }})</span>
                  </div>
                </div>

              </div>
            </div>

            <!-- Clinical / Laboratory Results Formal Table -->
            <div v-if="printOptions.vitals" class="mb-4">
              <div class="text-center font-bold text-[10.5px] uppercase tracking-wider bg-slate-200/90 border-t border-x border-slate-400 py-1 text-slate-900">
                CLINICAL EXAMINATION & LABORATORY FINDINGS
              </div>
              <table class="w-full text-left border-collapse border border-slate-400 text-[11px]">
                <thead class="bg-slate-100 font-extrabold text-slate-800 border-b border-slate-400">
                  <tr>
                    <th class="py-2 px-3 border-r border-slate-400">Test / Parameter Name</th>
                    <th class="py-2 px-3 border-r border-slate-400 text-center w-28">Results</th>
                    <th class="py-2 px-3 border-r border-slate-400 text-center w-36">Biological Ref. Interval</th>
                    <th class="py-2 px-3 border-r border-slate-400 text-center w-20">Units</th>
                    <th class="py-2 px-3 text-center w-24">Clinical Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-300">
                  <!-- Row 1: Blood Pressure -->
                  <tr>
                    <td class="py-2 px-3 font-bold text-slate-800 border-r border-slate-400">
                      Blood Pressure (Systolic / Diastolic)
                    </td>
                    <td class="py-2 px-3 font-mono font-bold text-slate-900 border-r border-slate-400 text-center">
                      {{ printingRecord?.bloodPressure || '115/75' }}
                    </td>
                    <td class="py-2 px-3 text-slate-600 border-r border-slate-400 text-center">
                      90/60 - 120/80
                    </td>
                    <td class="py-2 px-3 text-slate-600 border-r border-slate-400 text-center font-mono">
                      mmHg
                    </td>
                    <td class="py-2 px-3 font-bold text-emerald-700 text-center">
                      Optimal
                    </td>
                  </tr>

                  <!-- Row 2: Pulse Rate -->
                  <tr>
                    <td class="py-2 px-3 font-bold text-slate-800 border-r border-slate-400">
                      Resting Pulse / Heart Rate
                    </td>
                    <td class="py-2 px-3 font-mono font-bold text-slate-900 border-r border-slate-400 text-center">
                      {{ printingRecord?.pulse || '75' }}
                    </td>
                    <td class="py-2 px-3 text-slate-600 border-r border-slate-400 text-center">
                      60 - 100
                    </td>
                    <td class="py-2 px-3 text-slate-600 border-r border-slate-400 text-center font-mono">
                      bpm
                    </td>
                    <td class="py-2 px-3 font-bold text-emerald-700 text-center">
                      Normal
                    </td>
                  </tr>

                  <!-- Row 3: BMI Assessment -->
                  <tr>
                    <td class="py-2 px-3 font-bold text-slate-800 border-r border-slate-400">
                      Body Mass Index (BMI Score)
                    </td>
                    <td class="py-2 px-3 font-mono font-bold text-slate-900 border-r border-slate-400 text-center">
                      {{ printingRecord?.bmi || '25.4' }}
                    </td>
                    <td class="py-2 px-3 text-slate-600 border-r border-slate-400 text-center">
                      18.5 - 24.9
                    </td>
                    <td class="py-2 px-3 text-slate-600 border-r border-slate-400 text-center font-mono">
                      kg/m²
                    </td>
                    <td class="py-2 px-3 font-bold text-center" :class="printingRecord?.bmi && parseFloat(printingRecord.bmi) >= 25 ? 'text-amber-700' : 'text-emerald-700'">
                      {{ getBmiLabel(printingRecord?.bmi || '25.4') }}
                    </td>
                  </tr>

                  <!-- Row 4: Weight & Height -->
                  <tr>
                    <td class="py-2 px-3 font-bold text-slate-800 border-r border-slate-400">
                      Anthropometry (Weight / Height)
                    </td>
                    <td class="py-2 px-3 font-mono font-bold text-slate-900 border-r border-slate-400 text-center whitespace-nowrap">
                      {{ printingRecord?.weightHeight || '70 kg / 1.55 m' }}
                    </td>
                    <td class="py-2 px-3 text-slate-600 border-r border-slate-400 text-center">
                      Age/Sex Standard
                    </td>
                    <td class="py-2 px-3 text-slate-600 border-r border-slate-400 text-center font-mono">
                      kg / m
                    </td>
                    <td class="py-2 px-3 font-bold text-slate-700 text-center">
                      Recorded
                    </td>
                  </tr>

                  <!-- Row 5: Encounter Type -->
                  <tr>
                    <td class="py-2 px-3 font-bold text-slate-800 border-r border-slate-400">
                      Encounter Category & Evaluation
                    </td>
                    <td class="py-2 px-3 font-bold text-slate-900 border-r border-slate-400 text-center">
                      {{ printingRecord?.recordType }}
                    </td>
                    <td class="py-2 px-3 text-slate-600 border-r border-slate-400 text-center">
                      Clinical Protocol
                    </td>
                    <td class="py-2 px-3 text-slate-600 border-r border-slate-400 text-center font-mono">
                      Encounter
                    </td>
                    <td class="py-2 px-3 font-bold text-emerald-700 text-center">
                      Verified
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- 📝 Section 2: Clinical Assessment & Physician Notes -->
            <div v-if="printOptions.notes" class="mb-4">
              <div class="border border-slate-400 p-3.5 bg-slate-50/50 text-[11px] leading-relaxed">
                <span class="font-bold uppercase tracking-wider text-slate-900 block mb-1">
                  Physician's Clinical Interpretation & Recommendations:
                </span>
                <p class="text-slate-800 whitespace-pre-wrap font-sans">
                  {{ printingRecord?.note || 'Patient evaluated for routine clinical care. Vital signs and laboratory findings are documented above. Routine clinical follow-up is advised.' }}
                </p>
              </div>
            </div>

            <!-- Section 3: Prescribed Medications (Rx) -->
            <div v-if="printOptions.prescription" class="mb-4">
              <div class="flex items-center justify-between bg-slate-200/90 border-t border-x border-slate-400 px-3 py-1.5 text-[10.5px] font-bold text-slate-900 uppercase">
                <span>MEDICAL PRESCRIPTION ORDERS (Rx)</span>
                <button 
                  type="button" 
                  @click="addPrescriptionRow" 
                  class="no-print text-[10px] font-bold text-teal-700 hover:text-teal-900 cursor-pointer flex items-center gap-1"
                >
                  <Plus class="w-3 h-3" />
                  <span>Add Medicine</span>
                </button>
              </div>

              <table class="w-full text-left border-collapse border border-slate-400 text-[11px]">
                <thead class="bg-slate-100 font-extrabold text-slate-800 border-b border-slate-400">
                  <tr>
                    <th class="py-2 px-2 w-8 text-center border-r border-slate-400">#</th>
                    <th class="py-2 px-3 border-r border-slate-400">Medication Name & Strength</th>
                    <th class="py-2 px-3 w-28 border-r border-slate-400">Dosage</th>
                    <th class="py-2 px-3 w-40 border-r border-slate-400">Frequency / Instructions</th>
                    <th class="py-2 px-3 w-24 border-r border-slate-400">Duration</th>
                    <th class="py-2 px-2 w-8 text-center no-print"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-300">
                  <tr v-for="(med, mIdx) in prescriptionsList" :key="mIdx">
                    <td class="py-2 px-2 text-center font-bold text-slate-500 border-r border-slate-400">{{ mIdx + 1 }}</td>
                    <td class="py-2 px-3 border-r border-slate-400">
                      <input 
                        type="text" 
                        v-model="med.medicine" 
                        placeholder="Medication Name" 
                        class="w-full bg-transparent border-0 border-b border-transparent focus:border-teal-500 focus:outline-none py-0 text-[11px] font-bold text-slate-900"
                      />
                    </td>
                    <td class="py-2 px-3 border-r border-slate-400">
                      <input 
                        type="text" 
                        v-model="med.dosage" 
                        placeholder="Dosage" 
                        class="w-full bg-transparent border-0 border-b border-transparent focus:border-teal-500 focus:outline-none py-0 text-[11px] text-slate-800"
                      />
                    </td>
                    <td class="py-2 px-3 border-r border-slate-400">
                      <input 
                        type="text" 
                        v-model="med.frequency" 
                        placeholder="Frequency" 
                        class="w-full bg-transparent border-0 border-b border-transparent focus:border-teal-500 focus:outline-none py-0 text-[11px] text-slate-800"
                      />
                    </td>
                    <td class="py-2 px-3 border-r border-slate-400">
                      <input 
                        type="text" 
                        v-model="med.duration" 
                        placeholder="Duration" 
                        class="w-full bg-transparent border-0 border-b border-transparent focus:border-teal-500 focus:outline-none py-0 text-[11px] text-slate-800 font-mono"
                      />
                    </td>
                    <td class="py-2 px-2 text-center no-print">
                      <button 
                        v-if="prescriptionsList.length > 1" 
                        type="button" 
                        @click="removePrescriptionRow(mIdx)" 
                        class="text-slate-400 hover:text-rose-500 p-0.5 cursor-pointer"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Bottom Note / Aster-style disclaimer -->
            <p class="text-[9.5px] text-slate-500 italic mt-3 text-center border-t border-slate-300 pt-2">
              Values out of reference range should be confirmed clinically. All examination results and medical notes recorded above pertain to the specified encounter date.
            </p>
            <p class="text-[9.5px] font-bold text-slate-700 uppercase tracking-widest text-center mt-0.5">
               END OF REPORT 
            </p>

            <!-- ✍️ Section 4: Formal 3-Column Hospital Sign-off & Stamp Block -->
            <div v-if="printOptions.signature" class="mt-5 pt-3 border-t border-slate-400">
              <div class="grid grid-cols-3 gap-6 text-center text-[10px]">
                
                <!-- Column 1: Entered By -->
                <div class="flex flex-col">
                  <p class="font-bold text-slate-700 uppercase tracking-wider text-[9.5px]">Entered By:</p>
                  <div class="h-4"></div>
                  <p class="font-bold text-slate-900 pb-0.5">Clinical Care Staff</p>
                  <div class="border-t border-slate-400 pt-2 pb-6">
                    <p class="text-[8.5px] text-slate-500">DMR Hospital Clinic</p>
                  </div>
                </div>

                <!-- Column 2: Reviewed By -->
                <div class="flex flex-col">
                  <p class="font-bold text-slate-700 uppercase tracking-wider text-[9.5px]">Reviewed By:</p>
                  <div class="h-4"></div>
                  <p class="font-bold text-slate-900 pb-0.5">Medical Resident / Tech</p>
                  <div class="border-t border-slate-400 pt-2 pb-6">
                    <p class="text-[8.5px] text-slate-500">Quality Assurance</p>
                  </div>
                </div>

                <!-- Column 3: Authorized / Attending Physician -->
                <div class="flex flex-col">
                  <p class="font-bold text-slate-700 uppercase tracking-wider text-[9.5px]">Authorized By:</p>
                  <div class="h-4"></div>
                  <p class="font-bold text-slate-900 text-[10.5px] pb-0.5">{{ currentDoctorName }}</p>
                  <div class="border-t border-slate-400 pt-2 pb-6">
                  </div>
                </div>

              </div>
            </div>

            <!-- Hospital Bottom Contact Footer -->
            <div class="mt-4 pt-2.5 border-t border-slate-300 flex items-center justify-between text-[9px] text-slate-500 font-sans">
              <span>DMR Hospital Clinic &bull; Phnom Penh, Cambodia &bull; Tel: +855 23 888 999</span>
              <span>Email: care@dmr.hospital &bull; www.dmr.hospital</span>
            </div>

          </div>

        </div>

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
          <h3 class="text-[17px] font-bold text-slate-800">Delete Health Record?</h3>
          <p class="text-[13px] text-slate-500 leading-relaxed">This action cannot be undone. The record will be permanently removed from the system.</p>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="cancelDelete" class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">
            Cancel
          </button>
          <button @click="pendingDeleteId && deleteRecord(pendingDeleteId)" :disabled="isDeleting" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors disabled:opacity-60 cursor-pointer flex items-center justify-center gap-2">
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
import { api, resolveServerUrl } from '../services/api'
import { 
  Activity, Users, FileText, Plus, Trash2, 
  Search, HeartPulse, LineChart, ChevronDown, X, User, Edit, Eye, Download, ArrowLeft, Clock, CalendarDays,
  Scale, FileCheck, CheckCircle2, AlertCircle, Info, Printer
} from 'lucide-vue-next'
import hospitalLogo from '@/assets/hospital-logo.png'

// TypeScript Interfaces for strict typing
interface HealthRecord {
  id: string;
  db_id?: number;
  date: string;
  patientName: string;
  patientId: string;
  gender: string;
  status: string;
  recordType: string;
  bloodPressure: string;
  pulse: string;
  weightHeight: string;
  weight?: string | number;
  height?: string | number;
  bmi: string;
  attendingDoctor: string;
  note?: string;
  dob?: string;
  fee?: number | string;
  payment_status?: string;
  attachment_url?: string;
}

interface HealthRecordForm extends Omit<HealthRecord, 'id'> {
  weight?: string | number;
  height?: string | number;
}

// Dropdown state
const isGenderOpen = ref(false)
const filterGender = ref('(All)')
const isStatusOpen = ref(false)
const filterStatus = ref('Active')
const isRangeOpen = ref(false)
const filterRange = ref('(Last Month)')
const isSortOpen = ref(false)
const currentSort = ref('Newest First')
const isPageSizeOpen = ref(false)
const searchQuery = ref('')
const isFeeMenuOpen = ref(false)
const feeDropdownRef = ref<HTMLElement | null>(null)

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
const confirmDeleteRecord = (id: string) => {
  pendingDeleteId.value = id
  showDeleteConfirm.value = true
}
const cancelDelete = () => {
  showDeleteConfirm.value = false
  pendingDeleteId.value = null
}

// State for Dedicated Patient Dossier View
const selectedPatientId = ref<string | null>(null)
const dossierTypeFilter = ref<string>('ALL')

// Modal states
const isAddModalOpen = ref(false)
const isImagePreviewOpen = ref(false)
const previewImageUrl = ref('')
const editingRecordId = ref<string | null>(null)

// Doctor name helper extracted from logged-in username or email
const getLoggedInDoctorDefault = () => {
  const storedName = localStorage.getItem('username')
  if (storedName && storedName.trim()) {
    const clean = storedName.trim()
    const formatted = clean.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
    return formatted.startsWith('Dr.') ? formatted : `Dr. ${formatted}`
  }

  const storedEmail = localStorage.getItem('userEmail')
  if (storedEmail && storedEmail.includes('@')) {
    const namePart = storedEmail.split('@')[0] || ''
    const cleanName = namePart.replace(/[._-]/g, ' ')
    const capitalized = cleanName.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
    return capitalized.startsWith('Dr.') ? capitalized : `Dr. ${capitalized}`
  }

  return 'Dr. Sarah Jenkins'
}

// Navigate into Dedicated Patient Dossier View
const viewPatientDossier = (patientId: string) => {
  selectedPatientId.value = patientId
  dossierTypeFilter.value = 'ALL'
}

// Filter all records belonging to the selected patient (newest to oldest)
const currentPatientRecords = computed(() => {
  if (!selectedPatientId.value) return []
  return records.value
    .filter(r => r.patientId === selectedPatientId.value || r.patientName.toLowerCase() === selectedPatientId.value?.toLowerCase())
    .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
})

// Filtered dossier records based on pill filter
const filteredDossierRecords = computed(() => {
  if (dossierTypeFilter.value === 'ALL') {
    return currentPatientRecords.value
  }
  return currentPatientRecords.value.filter(r => r.recordType === dossierTypeFilter.value)
})

// Extract unique record types with counts for the patient
const uniqueRecordTypes = computed(() => {
  const map = new Map<string, number>()
  currentPatientRecords.value.forEach(r => {
    map.set(r.recordType, (map.get(r.recordType) || 0) + 1)
  })
  return Array.from(map.entries()).map(([name, count]) => ({ name, count }))
})

// Find index of record in the full patient records list
const getRecordOriginalIndex = (id: string) => {
  return currentPatientRecords.value.findIndex(r => r.id === id)
}

// Compare systolic BP with the previous visit in chronological order
const getBpComparison = (id: string) => {
  const idx = getRecordOriginalIndex(id)
  if (idx < 0 || idx >= currentPatientRecords.value.length - 1) return null
  const currentRecord = currentPatientRecords.value[idx]
  const prevRecord = currentPatientRecords.value[idx + 1]
  
  if (!currentRecord?.bloodPressure || !prevRecord?.bloodPressure) return null
  const curSys = parseInt(currentRecord.bloodPressure.split('/')[0] || '0')
  const prevSys = parseInt(prevRecord.bloodPressure.split('/')[0] || '0')
  
  if (!curSys || !prevSys) return null
  const diff = curSys - prevSys
  if (diff < 0) return `${Math.abs(diff)} mmHg lower vs prev`
  if (diff > 0) return `+${diff} mmHg vs prev`
  return `Same as prev visit`
}

// Compare weight with previous visit
const getWeightComparison = (id: string) => {
  const idx = getRecordOriginalIndex(id)
  if (idx < 0 || idx >= currentPatientRecords.value.length - 1) return null
  const currentRecord = currentPatientRecords.value[idx]
  const prevRecord = currentPatientRecords.value[idx + 1]
  
  if (!currentRecord?.weight || !prevRecord?.weight) return null
  const curW = parseFloat(currentRecord.weight.toString())
  const prevW = parseFloat(prevRecord.weight.toString())
  
  if (isNaN(curW) || isNaN(prevW)) return null
  const diff = (curW - prevW).toFixed(1)
  const numDiff = parseFloat(diff)
  if (numDiff > 0) return `+${numDiff} kg vs prev`
  if (numDiff < 0) return `${numDiff} kg vs prev`
  return `Weight unchanged`
}

// BMI label
const getBmiLabel = (bmiStr: string) => {
  const bmi = parseFloat(bmiStr)
  if (isNaN(bmi)) return 'Unknown'
  if (bmi < 18.5) return 'Underweight'
  if (bmi >= 18.5 && bmi < 25) return 'Normal'
  if (bmi >= 25 && bmi < 30) return 'Overweight'
  return 'Obese'
}

// Current patient profile metadata
const currentPatientInfo = computed(() => {
  if (!selectedPatientId.value || currentPatientRecords.value.length === 0) {
    const firstMatch = records.value.find(r => r.patientId === selectedPatientId.value)
    return firstMatch || null
  }
  return currentPatientRecords.value[0]
})

// Add new visit record for the currently selected patient
const openNewVisitForCurrentPatient = () => {
  const patient = currentPatientInfo.value
  if (!patient) return
  
  editingRecordId.value = null
  selectedFiles.value = []
  existingAttachments.value = []
  
  const now = new Date()
  recYear.value = now.getFullYear().toString()
  recMonth.value = (now.getMonth() + 1).toString().padStart(2, '0')
  recDay.value = now.getDate().toString()

  newRecord.value = {
    date: now.toISOString().split('T')[0] || '',
    patientName: patient.patientName,
    patientId: patient.patientId,
    gender: patient.gender,
    status: patient.status,
    recordType: 'General Checkup',
    bloodPressure: '',
    pulse: '',
    weightHeight: '',
    bmi: '',
    attendingDoctor: getLoggedInDoctorDefault(),
    note: '',
    weight: '',
    height: '',
    fee: 35,
    payment_status: 'Paid'
  }
  isAddModalOpen.value = true
}

const openImagePreview = (url: string) => {
  previewImageUrl.value = url
  isImagePreviewOpen.value = true
}

const forceDownload = async (url: string) => {
  try {
    const response = await fetch(url)
    if (!response.ok) throw new Error('Network response was not ok')
    const blob = await response.blob()
    const blobUrl = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = blobUrl
    const filename = url.split('/').pop() || 'download'
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(blobUrl)
  } catch (error) {
    console.error("Download failed, opening in new tab", error)
    window.open(url, '_blank')
  }
}

const handleGlobalKeydown = (e: KeyboardEvent) => {
  if (isImagePreviewOpen.value && e.key === 'Enter') {
    e.preventDefault()
    forceDownload(previewImageUrl.value)
  } else if (isImagePreviewOpen.value && e.key === 'Escape') {
    isImagePreviewOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown)
})




const capitalizeWords = (str: string) => {
  if (!str) return ""
  return str.split(" ").map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(" ")
}

const isTypeOpen = ref(false)
const isDateOpen = ref(false)

const bpInput = ref<HTMLInputElement | null>(null)
const pulseInput = ref<HTMLInputElement | null>(null)
const weightInput = ref<HTMLInputElement | null>(null)
const heightInput = ref<HTMLInputElement | null>(null)
const doctorInput = ref<HTMLInputElement | null>(null)
const noteInput = ref<HTMLTextAreaElement | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const recordForm = ref<HTMLFormElement | null>(null)

const recDay = ref<string | number>('')
const recMonth = ref('')
const recYear = ref<string | number>('')
const currentYear = new Date().getFullYear()

const months = [
  { value: '01', label: 'Jan' }, { value: '02', label: 'Feb' }, { value: '03', label: 'Mar' },
  { value: '04', label: 'Apr' }, { value: '05', label: 'May' }, { value: '06', label: 'Jun' },
  { value: '07', label: 'Jul' }, { value: '08', label: 'Aug' }, { value: '09', label: 'Sep' },
  { value: '10', label: 'Oct' }, { value: '11', label: 'Nov' }, { value: '12', label: 'Dec' }
]

const formattedDate = computed(() => {
  if (recDay.value && recMonth.value && recYear.value) {
    const m = months.find(m => m.value === recMonth.value)
    return `${m?.label} ${recDay.value}, ${recYear.value}`
  }
  return ''
})


interface RecentPatient {
  id: string;
  name: string;
  timestamp: number;
}

interface SystemPatient {
  id: string;
  name: string;
  gender?: string;
  dob?: string;
}

const allPatients = ref<SystemPatient[]>([])
const recentPatients = ref<RecentPatient[]>([])

const fetchAllPatientsList = async () => {
  try {
    const res = await api.get('/api/patients')
    if (res && res.status === 'success' && Array.isArray(res.data)) {
      allPatients.value = res.data.map((p: any) => ({
        id: p.patient_id,
        name: `${p.first_name || ''} ${p.last_name || ''}`.trim(),
        gender: p.gender || 'Male',
        dob: p.dob || ''
      }))
    }
  } catch (e) {
    console.error('Failed to load patient directory', e)
  }
}

const loadRecentPatients = () => {
  try {
    const stored = localStorage.getItem('recentPatients')
    if (stored) {
      const parsed = JSON.parse(stored) as RecentPatient[]
      const twentyFourHours = 24 * 60 * 60 * 1000
      const now = Date.now()
      // Keep suggestions added or used within the last 24 hours
      recentPatients.value = parsed.filter(p => now - p.timestamp < twentyFourHours)
    }
  } catch (e) {
    console.error('Failed to load recent patients', e)
  }
}

// Strictly suggest ONLY patients active/created within the last 24 hours.
// Once 24 hours have elapsed, they will NO LONGER be suggested in the dropdown.
const suggestedPatients = computed(() => {
  const twentyFourHours = 24 * 60 * 60 * 1000
  const now = Date.now()
  const activeRecentPatients = recentPatients.value.filter(rp => (now - rp.timestamp) < twentyFourHours)

  return activeRecentPatients.map(rp => {
    const sys = allPatients.value.find(p => p.id === rp.id)
    return sys || { id: rp.id, name: rp.name }
  })
})

onMounted(() => {
  loadRecentPatients()
  fetchAllPatientsList()
  fetchRecords()
})

// Form state
const newRecord = ref<HealthRecordForm>({
  date: '',
  patientName: '',
  patientId: '',
  gender: 'Male',
  status: 'Active',
  recordType: '',
  bloodPressure: '',
  pulse: '',
  weightHeight: '',
  bmi: '',
  attendingDoctor: '',
  note: '',
  weight: '',
  height: ''
})

watch(() => newRecord.value.patientName, (newName) => {
  if (!newName) return
  const q = newName.trim().toLowerCase()
  // 1. Search in full registered patients directory
  const sysPatient = allPatients.value.find(p => p.name.toLowerCase() === q || p.id.toLowerCase() === q)
  if (sysPatient) {
    newRecord.value.patientName = sysPatient.name
    newRecord.value.patientId = sysPatient.id
    if (sysPatient.gender) newRecord.value.gender = sysPatient.gender
    return
  }
  // 2. Fallback to recent patients list
  const recentP = recentPatients.value.find(rp => rp.name.toLowerCase() === q || rp.id.toLowerCase() === q)
  if (recentP) {
    newRecord.value.patientId = recentP.id
    if (newRecord.value.patientName !== recentP.name) {
      newRecord.value.patientName = recentP.name
    }
  }
})

watch([recDay, recMonth, recYear], ([d, m, y]) => {
  if (d && m && y) {
    const dStr = d.toString().padStart(2, '0')
    newRecord.value.date = `${y}-${m}-${dStr}`
  } else {
    newRecord.value.date = ''
  }
})

watch([() => newRecord.value.weight, () => newRecord.value.height], ([w, h]) => {
  if (w && h) {
    const weight = Number(w)
    const height = Number(h)
    if (weight > 0 && height > 0) {
      newRecord.value.bmi = (weight / (height * height)).toFixed(1)
    } else {
      newRecord.value.bmi = ''
    }
  } else {
    newRecord.value.bmi = ''
  }
})

// State
const records = ref<HealthRecord[]>([])

const filteredRecords = computed(() => {
  let result = [...records.value]

  // 1. Search Filter
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(r => 
      r.patientName.toLowerCase().includes(q) || 
      r.patientId.toLowerCase().includes(q) ||
      r.attendingDoctor.toLowerCase().includes(q)
    )
  }

  // 2. Gender Filter
  if (filterGender.value !== '(All)') {
    result = result.filter(r => r.gender === filterGender.value)
  }

  // 3. Status Filter (UI toggles Active/Inactive)
  // If the user selects a specific status, filter. If it has an (All) option in the future, we skip.
  if (filterStatus.value) {
    result = result.filter(r => r.status === filterStatus.value)
  }

  // 4. Sort By
  result.sort((a, b) => {
    if (currentSort.value === 'Name (A to Z)') {
      return a.patientName.localeCompare(b.patientName)
    } else if (currentSort.value === 'Name (Z to A)') {
      return b.patientName.localeCompare(a.patientName)
    } else if (currentSort.value === 'Oldest First') {
      return new Date(a.date).getTime() - new Date(b.date).getTime()
    } else {
      // Newest First (default)
      return new Date(b.date).getTime() - new Date(a.date).getTime()
    }
  })

  return result
})

// Pagination State
const currentPage = ref(1)
const itemsPerPage = ref(6)
const pageSizeOptions = [6, 10, 20, 50]

const totalPages = computed(() => {
  return Math.ceil(filteredRecords.value.length / itemsPerPage.value) || 1
})

const paginatedRecords = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredRecords.value.slice(start, start + itemsPerPage.value)
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
watch([searchQuery, filterGender, filterStatus, currentSort, itemsPerPage], () => {
  currentPage.value = 1
})

const getBadgeClass = (type: string) => {
  const map: Record<string, string> = {
    'Lab Results': 'bg-teal-50 text-teal-700 border-teal-100',
    'Endocrinology Follow-up': 'bg-emerald-50 text-emerald-700 border-emerald-100',
    'Routine Exam': 'bg-teal-50 text-teal-700 border-teal-100',
    'Cardiology Evaluation': 'bg-emerald-50 text-emerald-700 border-emerald-100',
    'General Checkup': 'bg-teal-50 text-teal-700 border-teal-100'
  }
  return map[type] || 'bg-slate-50 text-slate-700 border-slate-200'
}

const getBmiClass = (bmiStr: string) => {
  const bmi = parseFloat(bmiStr)
  if (isNaN(bmi)) return 'bg-slate-100 text-slate-700'
  if (bmi < 18.5) return 'bg-blue-100 text-blue-700'
  if (bmi >= 18.5 && bmi < 25) return 'bg-green-100 text-green-700'
  if (bmi >= 25 && bmi < 30) return 'bg-yellow-100 text-yellow-700'
  return 'bg-red-100 text-red-700'
}

const getFileUrl = (url: string) => {
  return resolveServerUrl(url)
}

const getAttachments = (attachmentUrl?: string) => {
  if (!attachmentUrl) return [];
  try {
    const parsed = JSON.parse(attachmentUrl);
    return Array.isArray(parsed) ? parsed : [attachmentUrl];
  } catch (e) {
    return [attachmentUrl]; // old format fallback
  }
}

const isImage = (url: string) => {
  return url.match(/\.(jpeg|jpg|gif|png|webp)$/i) != null;
}

const getOriginalFileName = (url: string) => {
  if (!url) return 'File'
  const base = url.split('/').pop() || ''
  const underscoreIndex = base.indexOf('_')
  if (underscoreIndex !== -1 && underscoreIndex < base.length - 1) {
    return decodeURIComponent(base.substring(underscoreIndex + 1))
  }
  return decodeURIComponent(base)
}

// Modal Actions
const openAddModal = () => {
  editingRecordId.value = null
  selectedFiles.value = []
  existingAttachments.value = []
  loadRecentPatients()
  fetchAllPatientsList() // Freshly fetch directory from DB on modal open
  
  isTypeOpen.value = false
  isDateOpen.value = false
  
  const today = new Date()
  const now = new Date()
  recYear.value = now.getFullYear().toString()
  recMonth.value = (now.getMonth() + 1).toString().padStart(2, '0')
  recDay.value = now.getDate().toString()

  newRecord.value = {
    date: now.toISOString().split('T')[0] || '',
    patientName: '',
    patientId: '',
    gender: 'Male',
    status: 'Active',
    recordType: '',
    bloodPressure: '',
    pulse: '',
    weightHeight: '',
    bmi: '',
    attendingDoctor: getLoggedInDoctorDefault(),
    note: '',
    weight: '',
    height: '',
    fee: 35,
    payment_status: 'Paid'
  }
  loadRecentPatients()
  isAddModalOpen.value = true
}

const openEditModal = (record: HealthRecord) => {
  editingRecordId.value = record.id
  selectedFiles.value = []
  existingAttachments.value = getAttachments(record.attachment_url)
  
  if (record.date) {
    const dParts = record.date.split('-')
    if (dParts.length === 3) {
      recYear.value = dParts[0]!
      recMonth.value = dParts[1]!
      recDay.value = parseInt(dParts[2]!)
    }
  } else {
    recDay.value = ''
    recMonth.value = ''
    recYear.value = ''
  }

  let w = '', h = ''
  if (record.weightHeight) {
    const parts = record.weightHeight.split(' / ')
    if (parts.length === 2) {
      w = parts[0]!.replace(' kg', '')
      h = parts[1]!.replace(' m', '')
    }
  }
  
  newRecord.value = { 
    ...record, 
    weight: record.weight !== undefined ? record.weight : w, 
    height: record.height !== undefined ? record.height : h,
    fee: record.fee !== undefined ? record.fee : 35,
    payment_status: record.payment_status || 'Paid'
  }
  isAddModalOpen.value = true
}

const fetchRecords = async () => {
  try {
    const response = await api.get('/api/health-records')
    if (response && response.status === 'success') {
      records.value = response.data.map((r: any) => ({
        id: r.record_id,
        db_id: r.id,
        date: r.date,
        patientName: r.patient_name,
        patientId: r.patient_id || '',
        gender: r.gender || 'Other',
        status: r.status || 'Active',
        recordType: r.record_type,
        bloodPressure: r.blood_pressure || '',
        pulse: r.pulse || '',
        weightHeight: (r.weight && r.height) ? `${r.weight} kg / ${r.height} m` : '',
        weight: r.weight,
        height: r.height,
        bmi: r.bmi || '',
        attendingDoctor: r.attending_doctor || '',
        dob: r.dob || '',
        note: r.note || '',
        fee: r.fee !== undefined && !isNaN(Number(r.fee)) ? Math.round(Number(r.fee)) : 35,
        payment_status: r.payment_status || 'Paid',
        attachment_url: r.attachment_url || ''
      }))
    }
  } catch (error) {
    showToast('Failed to load health records. Please check your connection.', 'error')
    console.error("Failed to fetch records:", error)
  }
}

// Format date YYYY-MM-DD → DD MMM YYYY (Bug #8 fix)
const formatDisplayDate = (dateStr: string) => {
  if (!dateStr) return '—'
  const d = new Date(dateStr + 'T00:00:00')
  if (isNaN(d.getTime())) return dateStr
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

const deleteRecord = async (id: string) => {
  if (isDeleting.value) return
  const recordToDelete = records.value.find(r => r.id === id)
  if (!recordToDelete || !recordToDelete.db_id) return
  isDeleting.value = true
  showDeleteConfirm.value = false
  pendingDeleteId.value = null
  try {
    await api.delete(`/api/health-records/${recordToDelete.db_id}`)
    await fetchRecords()
    showToast('Health record deleted successfully.', 'success')
  } catch (error) {
    showToast('Failed to delete record. Please try again.', 'error')
    console.error("Delete failed", error)
  } finally {
    isDeleting.value = false
  }
}

const selectedFiles = ref<File[]>([])
const existingAttachments = ref<string[]>([])

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files) {
    const maxAllowed = 5 - existingAttachments.value.length
    
    if (target.files.length > maxAllowed) {
      alert(`Maximum 5 files allowed per record! You can only add ${maxAllowed} more file(s).`)
      target.value = '' // Reset input
      return
    }
    
    selectedFiles.value = Array.from(target.files)
  } else {
    selectedFiles.value = []
  }
}

const removeSelectedFile = (index: number) => {
  selectedFiles.value.splice(index, 1)
}

const removeExistingAttachment = (index: number) => {
  existingAttachments.value.splice(index, 1)
}

const saveRecord = async () => {
  if (!newRecord.value.patientName) return
  if (isSaving.value) return  // prevent double-submit

  isSaving.value = true
  const isEdit = !!editingRecordId.value

  const payload = new FormData()
  payload.append('patient_name', newRecord.value.patientName)
  payload.append('patient_id', newRecord.value.patientId)
  payload.append('gender', newRecord.value.gender)
  payload.append('status', newRecord.value.status)
  payload.append('record_type', newRecord.value.recordType)
  payload.append('date', newRecord.value.date)
  payload.append('blood_pressure', newRecord.value.bloodPressure)
  payload.append('pulse', newRecord.value.pulse)
  payload.append('weight', newRecord.value.weight?.toString() || '')
  payload.append('height', newRecord.value.height?.toString() || '')
  payload.append('bmi', newRecord.value.bmi)
  payload.append('attending_doctor', newRecord.value.attendingDoctor)
  payload.append('note', newRecord.value.note || '')
  payload.append('fee', Math.round(Number(newRecord.value.fee || 35)).toString())
  payload.append('payment_status', newRecord.value.payment_status || 'Paid')
  
  if (editingRecordId.value) {
    payload.append('existing_attachments', JSON.stringify(existingAttachments.value))
  }
  
  selectedFiles.value.forEach((file) => {
    payload.append('attachments[]', file)
  })

  try {
    if (editingRecordId.value) {
      const recordToEdit = records.value.find(r => r.id === editingRecordId.value)
      if (recordToEdit && recordToEdit.db_id) {
        await api.postFormData(`/api/health-records/${recordToEdit.db_id}`, payload)
      }
    } else {
      await api.postFormData('/api/health-records', payload)
      // Keep/refresh patient in recent patients suggestions (for 24 hours)
      try {
        const stored = localStorage.getItem('recentPatients') || '[]'
        const parsed = JSON.parse(stored) as RecentPatient[]
        const twentyFourHours = 24 * 60 * 60 * 1000
        const now = Date.now()
        const updated = parsed.filter(p => p.id !== newRecord.value.patientId && (now - p.timestamp) < twentyFourHours)
        
        if (newRecord.value.patientId && newRecord.value.patientName) {
          updated.unshift({
            id: newRecord.value.patientId,
            name: newRecord.value.patientName,
            timestamp: now
          })
        }
        localStorage.setItem('recentPatients', JSON.stringify(updated))
        loadRecentPatients()
      } catch (e) {
        console.error('Failed to update recent patients', e)
      }
    }
    
    isAddModalOpen.value = false
    if (!isEdit) {
      currentSort.value = 'Newest First'
      currentPage.value = 1 // Immediately display Page 1 to see the new record on top
    }
    await fetchRecords()  // await so data is fresh before UI shows
    showToast(isEdit ? 'Health record updated successfully.' : 'Health record added successfully.', 'success')
  } catch (error) {
    showToast('Failed to save record. Please try again.', 'error')
    console.error("Save failed", error)
  } finally {
    isSaving.value = false
  }
}

// Close all table dropdowns
const closeAllDropdowns = () => {
  isGenderOpen.value = false
  isStatusOpen.value = false
  isRangeOpen.value = false
  isSortOpen.value = false
  isPageSizeOpen.value = false
  isFeeMenuOpen.value = false
}

// Global click outside listener
const handleGlobalClick = (e: MouseEvent) => {
  const target = e.target as HTMLElement | null
  if (target && !target.closest('.dropdown-container')) {
    closeAllDropdowns()
  }
  if (target && feeDropdownRef.value && !feeDropdownRef.value.contains(target)) {
    isFeeMenuOpen.value = false
  }
}

// =========================================================================
// CLINICAL PRINT SYSTEM (REUSABLE FOR ALL PATIENTS & ENCOUNTERS)
// =========================================================================
const isPrintModalOpen = ref(false)
const printingRecord = ref<HealthRecord | null>(null)

const printOptions = ref({
  vitals: true,
  notes: false,
  prescription: false,
  signature: true
})

interface PrescriptionItem {
  medicine: string;
  dosage: string;
  frequency: string;
  duration: string;
}

const prescriptionsList = ref<PrescriptionItem[]>([])

const addPrescriptionRow = () => {
  prescriptionsList.value.push({ medicine: '', dosage: '', frequency: '', duration: '' })
}

const removePrescriptionRow = (index: number) => {
  if (prescriptionsList.value.length > 1) {
    prescriptionsList.value.splice(index, 1)
  }
}

const printScrollContainer = ref<HTMLElement | null>(null)

const openPrintModal = (record: HealthRecord) => {
  printingRecord.value = record
  printOptions.value = {
    vitals: true,
    notes: false,
    prescription: false,
    signature: true
  }
  
  // Provide smart initial prescription rows based on encounter type
  if (record.recordType === 'Cardiology Evaluation') {
    prescriptionsList.value = [
      { medicine: 'Amlodipine Besylate', dosage: '5mg', frequency: 'Once daily in morning', duration: '30 days' },
      { medicine: 'Aspirin (Enteric Coated)', dosage: '81mg', frequency: 'Once daily after breakfast', duration: '30 days' }
    ]
  } else if (record.recordType === 'Lab Results') {
    prescriptionsList.value = [
      { medicine: 'Multivitamins + Iron Supplement', dosage: '1 capsule', frequency: 'Once daily with meals', duration: '14 days' }
    ]
  } else {
    prescriptionsList.value = [
      { medicine: 'Paracetamol', dosage: '500mg', frequency: '1 tab every 6h as needed', duration: '3 days' },
      { medicine: 'Vitamin C (Ascorbic Acid)', dosage: '500mg', frequency: 'Once daily after food', duration: '10 days' }
    ]
  }
  
  isPrintModalOpen.value = true
  setTimeout(() => {
    if (printScrollContainer.value) {
      printScrollContainer.value.scrollTop = 0
    }
  }, 50)
}

const getResolvedPatientDob = (rec: HealthRecord | null) => {
  if (!rec) return 'N/A'
  if (rec.dob) return rec.dob
  // Find in registered patients directory
  const matched = allPatients.value.find(p => p.id === rec.patientId || p.name.toLowerCase() === rec.patientName.toLowerCase())
  if (matched && matched.dob) return matched.dob
  return '2000-01-01'
}

const getPatientAge = (rec: HealthRecord | null) => {
  const dob = getResolvedPatientDob(rec)
  if (!dob || dob === 'N/A') return '25'
  const birthDate = new Date(dob)
  if (isNaN(birthDate.getTime())) return '25'
  const diff = Date.now() - birthDate.getTime()
  const ageDate = new Date(diff)
  return Math.abs(ageDate.getUTCFullYear() - 1970).toString()
}

const currentFormattedTimestamp = computed(() => {
  const d = new Date()
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
})

const currentFormattedDateOnly = computed(() => {
  const d = new Date()
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
})

const currentDoctorName = computed(() => {
  if (printingRecord.value?.attendingDoctor && printingRecord.value.attendingDoctor.trim()) {
    const doc = printingRecord.value.attendingDoctor.trim()
    return doc.startsWith('Dr.') ? doc : `Dr. ${doc}`
  }
  return getLoggedInDoctorDefault()
})

const triggerPrint = () => {
  const printElement = document.getElementById('printable-medical-record')
  if (!printElement) return

  // Clone node and capture live input values
  const cloned = printElement.cloneNode(true) as HTMLElement
  const originalInputs = printElement.querySelectorAll('input')
  const clonedInputs = cloned.querySelectorAll('input')
  
  originalInputs.forEach((inp, idx) => {
    if (clonedInputs[idx]) {
      clonedInputs[idx].setAttribute('value', (inp as HTMLInputElement).value)
    }
  })

  // Create isolated invisible iframe to avoid any parent CSS overflow / hidden issues
  const printFrame = document.createElement('iframe')
  printFrame.style.position = 'fixed'
  printFrame.style.right = '0'
  printFrame.style.bottom = '0'
  printFrame.style.width = '0'
  printFrame.style.height = '0'
  printFrame.style.border = '0'
  document.body.appendChild(printFrame)

  const doc = printFrame.contentWindow?.document
  if (!doc) return

  doc.open()
  doc.write('<!DOCTYPE html><html><head><title>DMR Hospital - Medical Report - ' + (printingRecord.value?.patientName || 'Patient') + '</title>')
  doc.write('<link rel="preconnect" href="https://fonts.googleapis.com">')
  doc.write('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>')
  doc.write('<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">')
  doc.write('<script src="https://cdn.tailwindcss.com"><' + '/script>')
  doc.write('<style>' +
    '@page { size: A4 portrait; margin: 8mm 12mm; }' +
    '* { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; box-sizing: border-box; font-family: "Inter", system-ui, sans-serif; }' +
    'html, body { background: #ffffff !important; color: #0f172a !important; margin: 0 !important; padding: 0 !important; height: auto; font-size: 11px; }' +
    'img { width: 48px !important; height: 48px !important; max-width: 48px !important; max-height: 48px !important; object-fit: contain !important; }' +
    '.no-print { display: none !important; }' +
    '#printable-medical-record { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }' +
    'input { border: none !important; outline: none !important; background: transparent !important; }' +
  '<' + '/style>')
  doc.write('</head><body>')
  doc.write(cloned.outerHTML)
  doc.write('</body></html>')
  doc.close()

  printFrame.contentWindow?.focus()
  setTimeout(() => {
    printFrame.contentWindow?.print()
    setTimeout(() => {
      if (document.body.contains(printFrame)) {
        document.body.removeChild(printFrame)
      }
    }, 2000)
  }, 500)
}

onMounted(() => {
  fetchRecords()
  window.addEventListener('click', handleGlobalClick)
})

onUnmounted(() => {
  window.removeEventListener('click', handleGlobalClick)
})
</script>

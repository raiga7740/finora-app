<div
    x-data="receiptScannerModal()"
    @open-receipt-scanner.window="openModal()"
    class="relative z-50"
    role="dialog"
    aria-modal="true"
    style="display: none;"
    x-show="isOpen"
>
    {{-- Backdrop --}}
    <div
        x-show="isOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 transition-opacity"
        style="background-color: rgba(0, 0, 0, 0.65); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"
        @click="closeModal()"
    ></div>

    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 lg:p-8 flex items-center justify-center">
        <div
            x-show="isOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden text-left"
            @click.stop
        >
            {{-- Header --}}
            <div class="px-6 py-5 bg-[#00a86b] text-white flex items-center justify-between border-b border-emerald-600/40">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white border border-white/20 shadow-sm shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-extrabold tracking-tight text-white">{{ __('Finora AI Receipt Scanner') }}</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black tracking-wider uppercase bg-white/20 text-white border border-white/20">OCR & AI</span>
                        </div>
                        <p class="text-xs text-emerald-100/90 mt-0.5">{{ __('Upload or snap a photo of any receipt to auto-fill transaction details') }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="closeModal()"
                    class="p-2 text-white/70 hover:text-white rounded-xl hover:bg-white/10 transition-colors cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6">
                {{-- Global Error Banner --}}
                <template x-if="errorMessage">
                    <div class="mb-4 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start justify-between gap-3 animate-fade-in shadow-xs">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                            <span class="font-medium leading-relaxed" x-text="errorMessage"></span>
                        </div>
                        <button type="button" @click="errorMessage = ''" class="text-rose-400 hover:text-rose-600 transition-colors p-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                {{-- STEP 1: UPLOAD / CAMERA SELECTION --}}
                <div x-show="step === 'upload'" class="space-y-4">
                    {{-- Tabs: File / Camera vs Text Paste --}}
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <button
                            type="button"
                            @click="mode = 'file'"
                            :class="mode === 'file' ? 'bg-[#00c078] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            <span>{{ __('Photo / File Upload') }}</span>
                        </button>
                        <button
                            type="button"
                            @click="mode = 'text'"
                            :class="mode === 'text' ? 'bg-[#00c078] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>{{ __('Paste Receipt Text') }}</span>
                        </button>
                    </div>

                    {{-- Mode: File Upload / Mobile Camera --}}
                    <div x-show="mode === 'file'">
                        <div
                            class="relative border-2 border-dashed border-gray-200 hover:border-emerald-400 rounded-3xl p-8 text-center bg-gray-50/50 hover:bg-emerald-50/20 transition-all cursor-pointer"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop($event)"
                            @click="$refs.receiptFileInput.click()"
                        >
                            <input
                                type="file"
                                x-ref="receiptFileInput"
                                accept="image/*"
                                capture="environment"
                                class="hidden"
                                @change="handleFileSelect($event)"
                            >

                            <template x-if="!imagePreview">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-3xl bg-emerald-100 text-[#00c078] flex items-center justify-center mb-3 shadow-inner">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-bold text-gray-900">{{ __('Take a photo or drag receipt image here') }}</h4>
                                    <p class="text-xs text-gray-500 mt-1 max-w-sm">{{ __('Supports JPG, PNG, WebP receipts from restaurants, supermarkets, fuel stations & online invoices') }}</p>

                                    <div class="mt-4 flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-gray-200 text-xs font-semibold text-gray-700 shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-[#00c078]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                            {{ __('Browse Receipt') }}
                                        </span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="imagePreview">
                                <div class="flex flex-col items-center">
                                    <div class="relative w-48 h-48 rounded-2xl overflow-hidden border border-gray-200 shadow-md mb-3 bg-gray-900">
                                        <img :src="imagePreview" alt="Receipt preview" class="w-full h-full object-cover">
                                    </div>
                                    <p class="text-xs font-semibold text-gray-800" x-text="fileName"></p>
                                    <span class="text-[11px] text-[#00c078] hover:underline mt-1 font-bold cursor-pointer" @click.stop="$refs.receiptFileInput.click()">{{ __('Change photo') }}</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Mode: Pure Text Paste --}}
                    <div x-show="mode === 'text'" style="display: none;">
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Paste Raw Receipt Text / Struk') }}</label>
                        <textarea
                            x-model="rawText"
                            rows="7"
                            placeholder="e.g. STARBUCKS COFFEE INDONESIA&#10;1 Caffe Latte 48.000&#10;Subtotal 48.000&#10;PB1 10% 4.800&#10;TOTAL Rp 52.800&#10;Debit BCA"
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-gray-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                        ></textarea>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="closeModal()"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-100 transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="button"
                            @click="startScanning()"
                            :disabled="(!imageFile && !rawText.trim()) || isScanning"
                            class="px-5 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold shadow-md shadow-[#00c078]/25 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
                            <span>{{ __('Scan with Finora AI') }}</span>
                        </button>
                    </div>
                </div>

                {{-- STEP 2: SCANNING IN PROGRESS --}}
                <div x-show="step === 'scanning'" style="display: none;" class="py-12 flex flex-col items-center text-center">
                    <div class="relative w-28 h-36 rounded-2xl overflow-hidden border-2 border-emerald-400 bg-gray-900 shadow-xl mb-5 flex items-center justify-center">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="Scanning" class="w-full h-full object-cover opacity-60">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="text-white/40 font-mono text-[9px] p-2 leading-tight">STARBUCKS TOTAL 52,800 BCA DEBIT</div>
                        </template>

                        {{-- Laser Scan Line --}}
                        <div class="absolute inset-x-0 h-1 bg-gradient-to-r from-transparent via-[#00c078] to-transparent shadow-[0_0_12px_#00c078] animate-bounce"></div>
                    </div>

                    <h4 class="text-sm font-bold text-gray-900" x-text="scanStatusText"></h4>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm">{{ __('Finora AI is reading merchant name, items, tax, payment method and total bill...') }}</p>

                    <div class="w-48 bg-gray-100 rounded-full h-2 mt-4 overflow-hidden">
                        <div class="bg-[#00c078] h-2 rounded-full transition-all duration-300" :style="'width: ' + scanProgress + '%'"></div>
                    </div>
                </div>

                {{-- STEP 3: REVIEW & CONFIRM TRANSACTION --}}
                <div x-show="step === 'review'" style="display: none;" class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">{{ __('Receipt Parsed Successfully') }}</span>
                        </div>
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold border border-emerald-200" x-text="(scannedData.confidence || 75) + '% Confidence'"></span>
                    </div>

                    {{-- Manual review alert if amount was not clearly extracted --}}
                    <template x-if="!scannedData.amount || Number(scannedData.amount) <= 0">
                        <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2.5 shadow-2xs">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            <span>{{ __('Total amount could not be detected automatically. Please verify and fill in the amount manually.') }}</span>
                        </div>
                    </template>

                    <form @submit.prevent="saveTransaction()" class="space-y-3.5">
                        {{-- Row 1: Title & Amount --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Merchant / Description') }} *</label>
                                <input
                                    type="text"
                                    x-model="scannedData.merchant_name"
                                    required
                                    class="w-full px-3.5 py-2 rounded-xl border border-gray-200 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Total Amount') }} *</label>
                                <input
                                    type="number"
                                    step="any"
                                    x-model="scannedData.amount"
                                    required
                                    class="w-full px-3.5 py-2 rounded-xl border border-emerald-300 bg-emerald-50/40 text-sm font-black text-emerald-900 focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                        </div>

                        {{-- Row 2: Category, Wallet & Date --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Category') }}</label>
                                <select
                                    x-model="scannedData.category_id"
                                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <template x-for="c in categories" :key="c.id">
                                        <option :value="c.id" x-text="c.name" :selected="c.id == scannedData.category_id"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Paid From (Wallet)') }}</label>
                                <select
                                    x-model="scannedData.wallet_id"
                                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <template x-for="w in wallets" :key="w.id">
                                        <option :value="w.id" x-text="w.name + ' (' + w.currency + ')'" :selected="w.id == scannedData.wallet_id"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Transaction Date') }} *</label>
                                <input
                                    type="date"
                                    x-model="scannedData.transaction_date"
                                    required
                                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                        </div>

                        {{-- Row 3: Items Breakdown (if parsed) --}}
                        <template x-if="scannedData.items && scannedData.items.length > 0">
                            <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1.5 block">{{ __('Itemized Breakdown Detected') }}</span>
                                <div class="space-y-1 max-h-24 overflow-y-auto pr-1 text-xs text-gray-700">
                                    <template x-for="it in scannedData.items" :key="it.name">
                                        <div class="flex items-center justify-between text-[11px] py-0.5 border-b border-gray-100 last:border-0">
                                            <span x-text="(it.qty ? it.qty + 'x ' : '') + it.name"></span>
                                            <span class="font-mono font-semibold" x-text="it.price ? formatNumber(it.price) : ''"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Row 4: Notes --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">{{ __('Notes & Receipt Details') }}</label>
                            <input
                                type="text"
                                x-model="scannedData.notes"
                                class="w-full px-3.5 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                            >
                        </div>

                        {{-- Footer Actions --}}
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <button
                                type="button"
                                @click="step = 'upload'"
                                class="text-xs font-semibold text-gray-500 hover:text-gray-800 transition cursor-pointer"
                            >
                                {{ __('← Scan Another') }}
                            </button>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="closeModal()"
                                    class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-100 transition cursor-pointer"
                                >
                                    {{ __('Cancel') }}
                                </button>
                                <button
                                    type="submit"
                                    :disabled="isSaving"
                                    class="px-5 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    <span x-text="isSaving ? '{{ __('Saving...') }}' : '{{ __('Save to Ledger') }}'"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function receiptScannerModal() {
        return {
            isOpen: false,
            step: 'upload', // 'upload' | 'scanning' | 'review'
            mode: 'file',   // 'file' | 'text'
            imageFile: null,
            imagePreview: null,
            fileName: '',
            rawText: '',
            dragOver: false,
            isScanning: false,
            isSaving: false,
            errorMessage: '',
            scanProgress: 10,
            scanStatusText: 'Initializing Finora AI OCR Engine...',
            receiptPath: null,
            receiptUrl: null,
            categories: [],
            wallets: [],
            scannedData: {
                merchant_name: '',
                amount: '',
                currency: 'IDR',
                category_id: '',
                wallet_id: '',
                transaction_date: '',
                notes: '',
                items: [],
                confidence: 85
            },
            openModal() {
                this.isOpen = true;
                this.step = 'upload';
                this.errorMessage = '';
                this.scanProgress = 10;
            },
            closeModal() {
                this.isOpen = false;
                this.errorMessage = '';
            },
            handleDrop(e) {
                this.dragOver = false;
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    this.processSelectedFile(e.dataTransfer.files[0]);
                }
            },
            handleFileSelect(e) {
                if (e.target.files && e.target.files.length > 0) {
                    this.processSelectedFile(e.target.files[0]);
                }
            },
            processSelectedFile(file) {
                this.errorMessage = '';
                if (!file || !file.type.startsWith('image/')) {
                    this.errorMessage = 'Please select a valid image file (JPG, PNG, WebP, HEIC).';
                    return;
                }
                this.imageFile = file;
                this.fileName = file.name;
                this.imagePreview = URL.createObjectURL(file);
            },
            compressImage(file, maxWidth = 1600, quality = 0.85) {
                return new Promise((resolve) => {
                    if (!file || !file.type.startsWith('image/')) {
                        resolve(file);
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            let w = img.width;
                            let h = img.height;
                            if (w > maxWidth || h > maxWidth) {
                                if (w > h) {
                                    h = Math.round((h * maxWidth) / w);
                                    w = maxWidth;
                                } else {
                                    w = Math.round((w * maxWidth) / h);
                                    h = maxWidth;
                                }
                            }
                            const canvas = document.createElement('canvas');
                            canvas.width = w;
                            canvas.height = h;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, w, h);
                            canvas.toBlob((blob) => {
                                if (!blob) {
                                    resolve(file);
                                    return;
                                }
                                const cleanName = (file.name || 'receipt').replace(/\.[^/.]+$/, "") + ".jpg";
                                const compressedFile = new File([blob], cleanName, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                resolve(compressedFile);
                            }, 'image/jpeg', quality);
                        };
                        img.onerror = () => resolve(file);
                        img.src = e.target.result;
                    };
                    reader.onerror = () => resolve(file);
                    reader.readAsDataURL(file);
                });
            },
            recognizeWithTimeout(file, timeoutMs = 8000) {
                if (!window.Tesseract || !file) {
                    return Promise.resolve('');
                }
                return new Promise((resolve) => {
                    let isResolved = false;
                    const timer = setTimeout(() => {
                        if (!isResolved) {
                            isResolved = true;
                            console.warn('Tesseract OCR timeout reached. Proceeding with server parsing fallback.');
                            resolve('');
                        }
                    }, timeoutMs);

                    Tesseract.recognize(file, 'eng', {
                        logger: m => {
                            if (m.status === 'recognizing text') {
                                this.scanProgress = 35 + Math.round((m.progress || 0) * 40);
                                this.scanStatusText = `Extracting receipt characters: ${Math.round((m.progress || 0) * 100)}%`;
                            }
                        }
                    })
                    .then(res => {
                        if (!isResolved) {
                            isResolved = true;
                            clearTimeout(timer);
                            resolve(res?.data?.text || '');
                        }
                    })
                    .catch(err => {
                        if (!isResolved) {
                            isResolved = true;
                            clearTimeout(timer);
                            console.warn('Client OCR error, proceeding to backend:', err);
                            resolve('');
                        }
                    });
                });
            },
            async startScanning() {
                this.errorMessage = '';
                this.step = 'scanning';
                this.isScanning = true;
                this.scanProgress = 20;
                this.scanStatusText = 'Optimizing and preparing receipt image...';

                let processedFile = this.imageFile;
                if (this.imageFile) {
                    try {
                        processedFile = await this.compressImage(this.imageFile, 1600, 0.85);
                    } catch (e) {
                        console.warn('Image compression bypassed:', e);
                    }
                }

                let clientExtractedText = this.rawText || '';

                if (processedFile && window.Tesseract) {
                    this.scanProgress = 35;
                    this.scanStatusText = 'Scanning receipt text with OCR engine...';
                    try {
                        const ocrResult = await this.recognizeWithTimeout(processedFile, 8000);
                        if (ocrResult) {
                            clientExtractedText = ocrResult;
                        }
                    } catch (e) {
                        console.warn('OCR execution fallback:', e);
                    }
                }

                this.scanProgress = 80;
                this.scanStatusText = 'Finora AI is structuring items, total bill & categories...';

                try {
                    const formData = new FormData();
                    if (processedFile) {
                        formData.append('image', processedFile);
                    }
                    if (clientExtractedText) {
                        formData.append('ocr_text', clientExtractedText);
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const resp = await fetch('{{ route("receipts.scan") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    if (!resp.ok) {
                        let errMsg = 'Could not scan receipt. Please check the image and try again.';
                        try {
                            const errJson = await resp.json();
                            if (resp.status === 419) {
                                errMsg = 'Session expired. Please refresh the page and try again.';
                            } else if (errJson.message) {
                                errMsg = errJson.message;
                            }
                        } catch (_) {}

                        this.errorMessage = errMsg;
                        this.step = 'upload';
                        this.isScanning = false;
                        return;
                    }

                    const json = await resp.json();

                    if (json.success && json.data) {
                        this.scannedData = json.data;
                        this.receiptPath = json.receipt_path;
                        this.receiptUrl = json.receipt_url;
                        this.categories = json.categories || [];
                        this.wallets = json.wallets || [];
                        this.scanProgress = 100;

                        setTimeout(() => {
                            this.step = 'review';
                            this.isScanning = false;
                        }, 350);
                    } else {
                        this.errorMessage = json.message || 'Could not parse receipt content. Please try another image.';
                        this.step = 'upload';
                        this.isScanning = false;
                    }
                } catch (err) {
                    console.error('Receipt scan request failed:', err);
                    this.errorMessage = 'Network connection error. Please check your internet or try again.';
                    this.step = 'upload';
                    this.isScanning = false;
                }
            },
            async saveTransaction() {
                this.isSaving = true;
                this.errorMessage = '';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                try {
                    const payload = {
                        title: this.scannedData.merchant_name || 'Receipt Purchase',
                        amount: this.scannedData.amount,
                        currency: this.scannedData.currency || 'IDR',
                        category_id: this.scannedData.category_id,
                        wallet_id: this.scannedData.wallet_id,
                        transaction_date: this.scannedData.transaction_date,
                        receipt_path: this.receiptPath,
                        notes: this.scannedData.notes
                    };

                    const resp = await fetch('{{ route("receipts.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload)
                    });

                    const res = await resp.json();
                    if (res.success) {
                        this.isOpen = false;
                        window.location.reload();
                    } else {
                        this.errorMessage = res.message || 'Failed to save transaction. Please check all required fields.';
                    }
                } catch (e) {
                    this.errorMessage = 'Network error while saving transaction. Please try again.';
                } finally {
                    this.isSaving = false;
                }
            },
            formatNumber(num) {
                return new Intl.NumberFormat().format(num || 0);
            }
        };
    }
</script>


<template>

    <Head title="POS" />
    <Banner />
    <div class="flex flex-col items-center justify-start min-h-screen py-6 space-y-3 bg-gray-100 md:px-20 px-4">
        <Header />

        <div class="w-full md:w-5/6 py-4 space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link href="/"><img src="/images/back-arrow.png" class="w-14 h-14" /></Link>
                    <p class="pt-3 text-4xl font-bold tracking-wide text-black uppercase">PoS</p>
                </div>
                <button @click="loadActiveSessions" :disabled="loadingActiveSessions"
                    class="px-6 py-3 border-2 border-black text-black text-xl font-semibold rounded-xl hover:bg-black hover:text-white transition-all disabled:opacity-50">
                    {{ loadingActiveSessions ? 'Refreshing...' : 'Refresh Open Services' }}
                </button>
            </div>

            <!-- Two-column layout -->
            <div class="flex md:flex-row flex-col w-full gap-4 items-start">

                <!-- ====== LEFT: Customers Panel ====== -->
                <div class="flex flex-col md:w-1/2 w-full border-4 border-black rounded-3xl p-6 bg-white">
                    <h2 class="text-4xl font-bold text-black">Customers</h2>
                    <p class="text-gray-500 text-lg mb-4">Click a square to open billing</p>

                    <div class="flex gap-3 mb-4">
                        <button @click="openAddCustomerModal"
                            class="flex-1 py-4 text-xl font-bold border-2 border-gray-400 text-gray-800 rounded-xl hover:bg-gray-100 transition-all">
                            Add Customer
                        </button>
                        <button @click="switchToLiveBill"
                            class="flex-1 py-4 text-xl font-bold rounded-xl transition-all bg-black text-white hover:bg-gray-800">
                            Live Bill
                        </button>
                    </div>

                    <!-- Cards -->
                    <div v-if="loadingActiveSessions" class="flex items-center justify-center py-12">
                        <p class="text-gray-400 text-xl">Loading...</p>
                    </div>
                    <div v-else-if="activeSessions.length === 0" class="flex items-center justify-center py-12">
                        <p class="text-gray-400 text-lg text-center">No active sessions.<br>Click "Add Customer" to begin.</p>
                    </div>
                    <div v-else class="grid grid-cols-2 gap-3 overflow-y-auto" style="max-height:55vh;">
                        <div v-for="sess in activeSessions" :key="sess.session.id"
                            @click="selectSession(sess)"
                            class="p-4 border-2 rounded-2xl cursor-pointer transition-all select-none"
                            :class="selectedSessionId === sess.session.id ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-white hover:border-blue-300'">
                            <p class="text-lg font-bold text-black truncate">{{ sess.session.customer_name || 'Walk-in' }}</p>
                            <p class="text-gray-500 text-sm truncate">{{ sess.session.customer_contact || '-' }}</p>

                            <!-- Package name -->
                            <p v-if="sess.session.package" class="text-xs text-blue-700 font-semibold mt-1 truncate">
                                {{ sess.session.package.name }}
                            </p>

                            <!-- Status badges & timer -->
                            <div class="mt-2">
                                <template v-if="sess.session.status === 'active' && sess.session.package_id">
                                    <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded-full">ACTIVE</span>
                                    <p class="text-sm font-bold text-blue-700 mt-1">
                                        &#9201; {{ sessionTimers[sess.session.id]?.elapsed ?? 0 }}
                                        / {{ sess.session.base_time_minutes }} mins
                                    </p>
                                    <p v-if="(sessionTimers[sess.session.id]?.extra_minutes ?? 0) > 0"
                                        class="text-xs text-red-600 font-bold">
                                        +{{ sessionTimers[sess.session.id].extra_minutes }} mins overtime
                                    </p>
                                </template>
                                <template v-else-if="sess.session.status === 'active'">
                                    <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-700 rounded-full">ACTIVE</span>
                                </template>
                                <template v-else>
                                    <span class="px-2 py-0.5 text-xs font-bold bg-orange-100 text-orange-600 rounded-full">Pending Open Bill</span>
                                    <p class="text-xs text-gray-400 mt-1">No barcode yet</p>
                                </template>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Items: {{ sess.session.items?.length ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- ====== RIGHT: Billing Details Panel ====== -->
                <div class="flex md:w-1/2 w-full p-6 border-4 border-black rounded-3xl bg-white">
                    <div class="flex flex-col w-full space-y-4">

                        <!-- Header row -->
                        <div class="flex items-center justify-between">
                            <h2 class="text-4xl font-bold text-black">Billing Details</h2>
                            <div class="flex items-center gap-4">
                                <span v-if="isLiveBillMode"
                                    class="px-3 py-1 bg-orange-500 text-white text-sm font-bold rounded-full">LIVE BILL</span>
                                <span class="flex cursor-pointer" @click="isSelectModalOpen = true">
                                    <p class="text-xl text-blue-600 font-bold">User Manual</p>
                                    <img src="/images/selectpsoduct.svg" class="w-6 h-6 ml-2" />
                                </span>
                            </div>
                        </div>

                        <!-- Barcode scanner (always visible) -->
                        <div class="flex items-stretch w-full border-2 border-black rounded-2xl overflow-hidden">
                            <input v-model="form.barcode" id="barcodeInput" type="text"
                                placeholder="Scan Product Barcode / Order Barcode"
                                @keydown.enter.prevent="submitBarcode"
                                class="flex-1 h-16 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <button @click="submitBarcode"
                                class="px-10 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 hover:bg-blue-700 transition-all">
                                ENTER
                            </button>
                        </div>

                        <!-- ── STATE: fetched order checkout ── -->
                        <div v-if="fetchedOrder" class="space-y-4">
                            <div class="flex items-center justify-between bg-gray-50 rounded-2xl p-4">
                                <div>
                                    <p class="text-sm text-gray-500">Order: <span class="font-bold text-black">{{ fetchedOrder.barcode }}</span></p>
                                    <p class="text-sm text-gray-500">Customer: <span class="font-bold text-black">{{ fetchedOrder.customer_name || 'Walk-in' }}</span></p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-lg font-semibold"
                                        :class="fetchedOrder.status === 'closed' ? 'text-red-600' : 'text-green-600'">
                                        {{ fetchedOrder.status === 'closed' ? 'Closed' : 'Active' }}
                                    </span>
                                    <button @click="fetchedOrder = null" class="text-3xl text-gray-400 hover:text-black leading-none">&times;</button>
                                </div>
                            </div>

                            <div v-if="fetchedOrder.items?.length" class="space-y-1">
                                <p class="font-bold text-black">Products</p>
                                <div v-for="item in fetchedOrder.items" :key="item.id"
                                    class="flex justify-between text-gray-700 border-b pb-1">
                                    <span>{{ item.product?.name || '-' }} &times; {{ item.quantity }}</span>
                                    <span>{{ Number(item.total_price).toFixed(2) }} LKR</span>
                                </div>
                            </div>

                            <div v-if="fetchedOrder.package_id" class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <p class="text-sm text-blue-700 font-semibold">{{ fetchedOrder.package?.name }}</p>
                                <p class="text-4xl font-bold text-blue-900">{{ elapsedDisplay }} <span class="text-lg">mins</span></p>
                                <div v-if="orderTotals.extra_minutes > 0" class="text-sm text-red-600 mt-1">
                                    Overtime: {{ orderTotals.extra_minutes }} mins &mdash; {{ Number(orderTotals.extra_amount).toFixed(2) }} LKR
                                </div>
                            </div>

                            <div class="space-y-1 border-t pt-3">
                                <div v-if="fetchedOrder.package_id" class="flex justify-between">
                                    <span>Package Total</span><span>{{ Number(orderTotals.package_total).toFixed(2) }} LKR</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Products Total</span><span>{{ Number(orderTotals.products_total).toFixed(2) }} LKR</span>
                                </div>
                                <div v-if="fetchedOrder.package_id && orderTotals.extra_amount > 0" class="flex justify-between text-red-600">
                                    <span>Overtime Charge</span><span>{{ Number(orderTotals.extra_amount).toFixed(2) }} LKR</span>
                                </div>
                                <div class="flex justify-between text-2xl font-bold border-t pt-2">
                                    <span>Final Total</span><span>{{ Number(orderTotals.final_total).toFixed(2) }} LKR</span>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-3 gap-3 items-center">
                                <div class="flex items-center gap-2">
                                    <div @click="checkoutPaymentMethod = 'cash'"
                                        :class="['cursor-pointer px-4 py-2 border rounded-xl font-bold text-lg', checkoutPaymentMethod === 'cash' ? 'bg-yellow-400 border-yellow-500' : 'border-gray-300 text-gray-600']">Cash</div>
                                    <div @click="checkoutPaymentMethod = 'card'"
                                        :class="['cursor-pointer px-4 py-2 border rounded-xl font-bold text-lg', checkoutPaymentMethod === 'card' ? 'bg-yellow-400 border-yellow-500' : 'border-gray-300 text-gray-600']">Card</div>
                                </div>
                                <input v-model.number="checkoutCash" type="number" min="0" placeholder="Cash Amount"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-black focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <button @click="confirmFetchedOrder" :disabled="loadingClose || fetchedOrder.status === 'closed'"
                                    class="w-full py-3 text-lg font-bold text-white uppercase bg-black rounded-xl disabled:opacity-50">
                                    {{ loadingClose ? 'Processing...' : 'Confirm & Print Bill' }}
                                </button>
                            </div>
                        </div>

                        <!-- ── STATE: session selected ── -->
                        <template v-else-if="selectedSession && !isLiveBillMode">

                            <!-- Package card -->
                            <div v-if="selectedSession.session.package_id"
                                class="rounded-2xl p-5 space-y-3"
                                :class="selectedSession.session.status === 'active' ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50 border border-gray-200'">

                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-xl font-bold text-blue-900">{{ selectedSession.session.package?.name }}</p>
                                        <p class="text-gray-600 text-sm">Base: {{ selectedSession.session.base_time_minutes }} mins &bull; {{ Number(selectedSession.session.package_total).toFixed(2) }} LKR</p>
                                    </div>
                                    <span v-if="selectedSession.session.status === 'pending'"
                                        class="px-3 py-1 bg-orange-100 text-orange-600 text-sm font-bold rounded-full">
                                        Timer starts on Open Bill
                                    </span>
                                </div>

                                <!-- Live timer (only active) -->
                                <div v-if="selectedSession.session.status === 'active'">
                                    <div class="flex items-baseline gap-2">
                                        <p class="text-5xl font-bold text-blue-900">{{ currentTimer?.elapsed ?? 0 }}</p>
                                        <p class="text-xl text-blue-600">/ {{ selectedSession.session.base_time_minutes }} mins</p>
                                    </div>
                                    <!-- Progress bar -->
                                    <div class="w-full bg-blue-200 rounded-full h-3 mt-2">
                                        <div class="bg-blue-600 h-3 rounded-full transition-all"
                                            :style="{ width: Math.min(100, ((currentTimer?.elapsed ?? 0) / selectedSession.session.base_time_minutes) * 100) + '%' }">
                                        </div>
                                    </div>
                                    <div v-if="(currentTimer?.extra_minutes ?? 0) > 0"
                                        class="mt-3 bg-red-50 border border-red-200 rounded-xl p-3">
                                        <p class="text-red-600 font-bold text-lg">Overtime: {{ currentTimer.extra_minutes }} mins</p>
                                        <p class="text-red-600">Extra Charge: {{ Number(currentTimer.extra_amount ?? 0).toFixed(2) }} LKR</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Products list -->
                            <div v-if="sessionItems.length === 0" class="text-center py-4">
                                <p class="text-red-500 text-xl">No products in this customer cart.</p>
                            </div>

                            <div class="space-y-2" style="max-height:28vh; overflow-y:auto;">
                                <div v-for="item in sessionItems" :key="item.id"
                                    class="flex items-center gap-3 py-2 border-b border-gray-200">
                                    <img :src="item.product?.image ? `/${item.product.image}` : '/images/placeholder.jpg'"
                                        class="w-12 h-12 object-cover border border-gray-300 rounded flex-shrink-0" />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-semibold text-black truncate">{{ item.product?.name || '-' }}</p>
                                        <p class="text-sm text-gray-500">{{ Number(item.unit_price).toFixed(2) }} LKR each</p>
                                    </div>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <button @click="decrementSessionItem(item)"
                                            class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded font-bold text-lg leading-none flex items-center justify-center transition-all">-</button>
                                        <span class="w-8 text-center font-bold">{{ item.quantity }}</span>
                                        <button @click="incrementSessionItem(item)"
                                            class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded font-bold text-lg leading-none flex items-center justify-center transition-all">+</button>
                                    </div>
                                    <p class="w-24 text-right font-bold text-sm flex-shrink-0">{{ Number(item.total_price).toFixed(2) }} LKR</p>
                                    <button @click="removeSessionItem(item)"
                                        class="text-red-400 hover:text-red-600 text-2xl leading-none flex-shrink-0">&times;</button>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="space-y-1 border-t pt-3">
                                <div v-if="selectedSession.session.package_id" class="flex justify-between">
                                    <span class="text-lg">Package Total</span>
                                    <span class="text-lg">{{ Number(selectedSession.session.package_total ?? 0).toFixed(2) }} LKR</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-lg">Products Total</span>
                                    <span class="text-lg">{{ Number(selectedSession.session.products_total ?? 0).toFixed(2) }} LKR</span>
                                </div>
                                <div v-if="(currentTimer?.extra_minutes ?? 0) > 0" class="flex justify-between text-red-600">
                                    <span class="text-lg">Extra Time ({{ currentTimer.extra_minutes }} mins)</span>
                                    <span class="text-lg">{{ Number(currentTimer.extra_amount ?? 0).toFixed(2) }} LKR</span>
                                </div>
                                <div class="flex justify-between text-2xl font-bold border-t pt-2">
                                    <span>Total</span>
                                    <span>{{ sessionTotalDisplay }} LKR</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div v-if="selectedSession.session.status === 'pending'" class="space-y-3">
                                <div class="px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-xl">
                                    <p class="text-yellow-700">Click "Open Bill &amp; Print" to generate barcode and start service timer.</p>
                                </div>
                                <button @click="openBillAndPrint" :disabled="loadingOpenBill"
                                    class="w-full py-4 text-xl font-bold bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50">
                                    <i class="ri-barcode-line mr-2"></i>
                                    {{ loadingOpenBill ? 'Opening...' : 'Open Bill & Print' }}
                                </button>
                            </div>

                            <div v-else class="space-y-3">
                                <button @click="reprintBarcode"
                                    class="w-full py-3 border-2 border-black text-black text-xl font-bold rounded-xl hover:bg-gray-100 transition-all">
                                    <i class="ri-barcode-line mr-2"></i>Reprint Barcode
                                </button>

                                <!-- Confirm panel toggle -->
                                <div v-if="!showConfirmPanel">
                                    <button @click="showConfirmPanel = true"
                                        class="w-full py-4 bg-black text-white text-xl font-bold rounded-xl hover:bg-gray-800 transition-all">
                                        <i class="ri-check-double-line mr-2"></i>Confirm &amp; Print Final Bill
                                    </button>
                                </div>
                                <div v-else class="border-2 border-black rounded-2xl p-5 space-y-4">
                                    <p class="text-xl font-bold text-black">Final Total: {{ sessionTotalDisplay }} LKR</p>
                                    <div class="flex gap-3 items-center">
                                        <p class="font-semibold">Payment:</p>
                                        <div @click="checkoutPaymentMethod = 'cash'"
                                            :class="['cursor-pointer px-4 py-2 border rounded-xl font-bold transition-all', checkoutPaymentMethod === 'cash' ? 'bg-yellow-400 border-yellow-500' : 'border-gray-300 text-gray-600']">Cash</div>
                                        <div @click="checkoutPaymentMethod = 'card'"
                                            :class="['cursor-pointer px-4 py-2 border rounded-xl font-bold transition-all', checkoutPaymentMethod === 'card' ? 'bg-yellow-400 border-yellow-500' : 'border-gray-300 text-gray-600']">Card</div>
                                    </div>
                                    <input v-model.number="checkoutCash" type="number" min="0" placeholder="Cash Amount"
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-black focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    <div class="flex gap-3">
                                        <button @click="showConfirmPanel = false"
                                            class="flex-1 py-3 border border-gray-400 text-gray-700 font-bold rounded-xl">Cancel</button>
                                        <button @click="confirmSessionFinalBill" :disabled="loadingClose"
                                            class="flex-1 py-3 bg-black text-white font-bold text-lg rounded-xl disabled:opacity-50">
                                            {{ loadingClose ? 'Processing...' : 'Confirm' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- ── STATE: Live Bill ── -->
                        <template v-else-if="isLiveBillMode">
                            <div class="space-y-2" style="max-height:30vh;overflow-y:auto;">
                                <div v-if="liveBillProducts.length === 0" class="text-center py-4">
                                    <p class="text-red-500 text-xl">No products added yet. Scan or use User Manual.</p>
                                </div>
                                <div v-for="item in liveBillProducts" :key="item.id"
                                    class="flex items-center gap-3 py-2 border-b border-gray-200">
                                    <img :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'"
                                        class="w-12 h-12 object-cover border border-gray-300 rounded flex-shrink-0" />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-semibold truncate">{{ item.name }}</p>
                                        <p class="text-sm text-gray-500">{{ Number(item.selling_price).toFixed(2) }} LKR each</p>
                                    </div>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <button @click="item.quantity > 1 ? item.quantity-- : removeLiveBillItem(item)"
                                            class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded font-bold text-lg leading-none flex items-center justify-center">-</button>
                                        <span class="w-8 text-center font-bold">{{ item.quantity }}</span>
                                        <button @click="item.quantity++"
                                            class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded font-bold text-lg leading-none flex items-center justify-center">+</button>
                                    </div>
                                    <p class="w-24 text-right font-bold text-sm flex-shrink-0">{{ Number(item.selling_price * item.quantity).toFixed(2) }} LKR</p>
                                    <button @click="removeLiveBillItem(item)" class="text-red-400 hover:text-red-600 text-2xl leading-none">&times;</button>
                                </div>
                            </div>

                            <div class="space-y-1 border-t pt-3">
                                <div class="flex justify-between text-2xl font-bold">
                                    <span>Total</span><span>{{ liveBillTotal }} LKR</span>
                                </div>
                            </div>

                            <div v-if="!showLiveBillConfirm">
                                <button @click="showLiveBillConfirm = true" :disabled="liveBillProducts.length === 0"
                                    class="w-full py-4 bg-black text-white text-xl font-bold rounded-xl hover:bg-gray-800 transition-all disabled:opacity-50">
                                    <i class="ri-check-double-line mr-2"></i>Confirm &amp; Print Final Bill
                                </button>
                            </div>
                            <div v-else class="border-2 border-black rounded-2xl p-5 space-y-4">
                                <p class="text-xl font-bold">Total: {{ liveBillTotal }} LKR</p>
                                <div class="flex gap-3 items-center">
                                    <p class="font-semibold">Payment:</p>
                                    <div @click="liveBillPaymentMethod = 'cash'"
                                        :class="['cursor-pointer px-4 py-2 border rounded-xl font-bold', liveBillPaymentMethod === 'cash' ? 'bg-yellow-400 border-yellow-500' : 'border-gray-300 text-gray-600']">Cash</div>
                                    <div @click="liveBillPaymentMethod = 'card'"
                                        :class="['cursor-pointer px-4 py-2 border rounded-xl font-bold', liveBillPaymentMethod === 'card' ? 'bg-yellow-400 border-yellow-500' : 'border-gray-300 text-gray-600']">Card</div>
                                </div>
                                <input v-model.number="liveBillCash" type="number" min="0" placeholder="Cash Amount"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <div class="flex gap-3">
                                    <button @click="showLiveBillConfirm = false"
                                        class="flex-1 py-3 border border-gray-400 font-bold rounded-xl">Cancel</button>
                                    <button @click="confirmLiveBill" :disabled="loadingLiveBill"
                                        class="flex-1 py-3 bg-black text-white font-bold text-lg rounded-xl disabled:opacity-50">
                                        {{ loadingLiveBill ? 'Processing...' : 'Confirm' }}
                                    </button>
                                </div>
                            </div>

                            <button @click="isLiveBillMode = false; liveBillProducts = []"
                                class="w-full py-2 text-gray-500 hover:text-black text-sm">&#8592; Cancel Live Bill</button>
                        </template>

                        <!-- ── STATE: nothing selected ── -->
                        <template v-else>
                            <div class="text-center py-4">
                                <p class="text-2xl text-red-500">No products in this customer cart.</p>
                            </div>
                            <div class="px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-xl">
                                <p class="text-yellow-700">Select a customer from the left panel or scan an order barcode to begin.</p>
                            </div>
                        </template>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Add Customer Modal ══ -->
    <div v-if="showAddCustomerModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
        @click.self="closeAddCustomerModal">
        <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-8 py-5 border-b">
                <h3 class="text-3xl font-bold">Add Customer</h3>
                <button @click="closeAddCustomerModal" class="text-4xl text-gray-400 hover:text-black">&times;</button>
            </div>
            <div class="p-8 space-y-4">
                <div class="relative">
                    <input v-model="customerSearchQuery" type="text" placeholder="Search existing customer (name, phone, email)"
                        @input="handleCustomerSearchInput"
                        class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <div v-if="showCustomerSearchDropdown"
                        class="absolute z-20 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                        <div v-if="searchingCustomers" class="px-4 py-3 text-sm text-gray-500">Searching...</div>
                        <button v-for="customer in customerSearchResults" :key="customer.id" type="button"
                            @click="selectCustomer(customer)"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 border-b last:border-b-0 border-gray-100">
                            <p class="font-semibold text-gray-900">{{ customer.name || 'Unnamed Customer' }}</p>
                            <p class="text-xs text-gray-500">{{ customer.phone || '-' }} &bull; {{ customer.email || '-' }}</p>
                        </button>
                        <div v-if="!searchingCustomers && customerSearchResults.length === 0" class="px-4 py-3 text-sm text-gray-500">
                            No customers found.
                        </div>
                    </div>
                </div>
                <input v-model="newSession.customerName" type="text" placeholder="Enter Customer Name"
                    class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <input v-model="newSession.customerContact" type="text" placeholder="Enter Customer Contact Number"
                    class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <input v-model="newSession.customerEmail" type="email" placeholder="Enter Customer Email"
                    class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <select v-model="newSession.employeeId"
                    class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled>Select an Employee</option>
                    <option v-for="emp in allemployee" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                </select>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Package Count (Tickets)</label>
                        <input v-model.number="newSession.packageQuantity" type="number" min="1"
                            class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div v-if="newSessionPackagePreview" class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3">
                        <p class="text-xs text-blue-700 font-semibold">Package Total</p>
                        <p class="text-lg font-bold text-blue-900">
                            {{ Number(newSession.packageQuantity || 1) }} × {{ packageUnitTotal.toFixed(2) }} = {{ packageGrandTotal.toFixed(2) }} LKR
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 items-center">
                    <button type="button" @click="showPackagePicker = true"
                        class="flex-1 px-4 py-4 border-2 border-blue-500 text-blue-600 font-bold rounded-xl hover:bg-blue-50 transition-all">
                        {{ newSessionPackagePreview ? newSessionPackagePreview.name : 'Select Package (Optional)' }}
                    </button>
                    <button v-if="newSession.packageId" @click="newSession.packageId = ''"
                        class="px-4 py-4 border-2 border-red-400 text-red-500 rounded-xl">&times;</button>
                </div>
                <p v-if="newSessionPackagePreview" class="text-gray-500 text-sm px-2">
                    {{ newSessionPackagePreview.base_time_minutes }} mins &bull;
                    {{ getPackageTotal(newSessionPackagePreview).toFixed(2) }} LKR
                </p>
                <button @click="createNewSession" :disabled="loadingCreateSession"
                    class="w-full py-4 text-xl font-bold bg-black text-white rounded-xl hover:bg-gray-800 transition-all disabled:opacity-50">
                    {{ loadingCreateSession ? 'Creating...' : 'Add Customer' }}
                </button>
            </div>
        </div>
    </div>

    <!-- ══ Package Picker Modal ══ -->
    <div v-if="showPackagePicker" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4"
        @click.self="showPackagePicker = false">
        <div class="w-full max-w-6xl bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-8 py-5 border-b">
                <h3 class="text-4xl font-bold">Select Package</h3>
                <button @click="showPackagePicker = false" class="text-4xl text-gray-400 hover:text-black">&times;</button>
            </div>
            <div class="p-6 border-b">
                <div class="grid md:grid-cols-4 gap-3">
                    <input v-model="packageSearch" type="text" placeholder="Search package..."
                        class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <select v-model="packageDurationFilter"
                        class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">All Durations</option>
                        <option value="short">Up to 60 mins</option>
                        <option value="medium">61-120 mins</option>
                        <option value="long">More than 120 mins</option>
                    </select>
                    <select v-model="packagePriceFilter"
                        class="px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">All Prices</option>
                        <option value="budget">Up to 1000 LKR</option>
                        <option value="standard">1001-3000 LKR</option>
                        <option value="premium">More than 3000 LKR</option>
                    </select>
                    <button @click="packageSearch=''; packageDurationFilter='all'; packagePriceFilter='all'"
                        class="px-4 py-3 bg-blue-600 text-white rounded-xl font-semibold">Reset</button>
                </div>
            </div>
            <div class="p-6 max-h-[62vh] overflow-y-auto">
                <div class="grid md:grid-cols-3 gap-4">
                    <button type="button" @click="newSession.packageId = ''; showPackagePicker = false"
                        class="text-left p-5 rounded-2xl border-2 transition-all"
                        :class="newSession.packageId === '' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'">
                        <p class="text-2xl font-bold">No Service Package</p>
                        <p class="text-gray-600 mt-2">Products only billing</p>
                    </button>
                    <button type="button" v-for="pkg in filteredPackages" :key="pkg.id"
                        @click="newSession.packageId = pkg.id; showPackagePicker = false"
                        class="text-left p-5 rounded-2xl border-2 transition-all"
                        :class="Number(newSession.packageId) === Number(pkg.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-xl font-bold leading-tight">{{ pkg.name }}</p>
                            <span class="text-sm font-semibold bg-green-100 text-green-700 px-3 py-1 rounded-full whitespace-nowrap">
                                {{ getPackageTotal(pkg).toFixed(2) }} LKR
                            </span>
                        </div>
                        <p class="text-gray-600 mt-2">Duration: <span class="font-semibold">{{ pkg.base_time_minutes }} mins</span></p>
                        <p class="text-gray-600">Overtime: +{{ Number(pkg.extra_charge || 0).toFixed(2) }} LKR / {{ pkg.extra_charge_per_minutes }} mins</p>
                    </button>
                </div>
                <div v-if="filteredPackages.length === 0" class="text-center py-12 text-gray-400 text-xl">
                    No packages found.
                </div>
            </div>
        </div>
    </div>

    <AlertModel v-model:open="isAlertModalOpen" :message="message" />
    <SelectProductModel v-model:open="isSelectModalOpen" :allcategories="allcategories" :colors="colors" :sizes="sizes"
        @selected-products="handleSelectedProducts" />
    <Footer />
</template>
<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import AlertModel from "@/Components/custom/AlertModel.vue";
import { useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount, computed, reactive } from "vue";
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";

// ── Props ──
const props = defineProps({
    loggedInUser: Object,
    allcategories: Array,
    allemployee: Array,
    packages: Array,
    colors: Array,
    sizes: Array,
});

// ── Alert ──
const isAlertModalOpen = ref(false);
const message = ref("");
const isSelectModalOpen = ref(false);

// ── Active Sessions ──
const activeSessions = ref([]);
const selectedSessionId = ref(null);
const loadingActiveSessions = ref(false);

const selectedSession = computed(
    () => activeSessions.value.find(s => s.session.id === selectedSessionId.value) || null
);
const sessionItems = computed(() => selectedSession.value?.session?.items ?? []);

// ── Timer system ──
// sessionTimers[id] = { elapsed, extra_minutes, extra_amount }
const sessionTimers = reactive({});
let globalTimer = null;

const updateAllTimers = () => {
    activeSessions.value.forEach(s => {
        if (s.session.status === 'active' && s.session.start_time && s.session.package_id) {
            const elapsed = Math.max(0, Math.floor((Date.now() - new Date(s.session.start_time).getTime()) / 60000));
            const base = Number(s.session.base_time_minutes || 0);
            const extra = Math.max(0, elapsed - base);
            const perUnit = Math.max(1, Number(s.session.extra_charge_per_minutes || 1));
            const units = extra > 0 ? Math.ceil(extra / perUnit) : 0;
            const extraAmt = units * Number(s.session.extra_charge || 0);
            sessionTimers[s.session.id] = {
                elapsed,
                extra_minutes: extra,
                extra_amount: extraAmt,
                final_total: Number(s.session.package_total || 0) + Number(s.session.products_total || 0) + extraAmt,
            };
        }
    });
};

const currentTimer = computed(() => {
    if (!selectedSession.value) return null;
    return sessionTimers[selectedSession.value.session.id] ?? null;
});

const sessionTotalDisplay = computed(() => {
    if (!selectedSession.value) return "0.00";
    const pkg = Number(selectedSession.value.session.package_total ?? 0);
    const prod = Number(selectedSession.value.session.products_total ?? 0);
    const extra = Number(currentTimer.value?.extra_amount ?? 0);
    return (pkg + prod + extra).toFixed(2);
});

// ── Load sessions ──
const loadActiveSessions = async () => {
    loadingActiveSessions.value = true;
    try {
        const res = await axios.get(route("play-area.session.active"));
        activeSessions.value = res.data.sessions || [];
        // If selected session was removed (closed), deselect
        if (selectedSessionId.value) {
            const still = activeSessions.value.find(s => s.session.id === selectedSessionId.value);
            if (!still) selectedSessionId.value = null;
        }
        updateAllTimers();
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Failed to load sessions.";
    } finally {
        loadingActiveSessions.value = false;
    }
};

const selectSession = (sess) => {
    selectedSessionId.value = sess.session.id;
    isLiveBillMode.value = false;
    fetchedOrder.value = null;
    showConfirmPanel.value = false;
};

// ── Open Bill & Print ──
const loadingOpenBill = ref(false);

const openBillAndPrint = async () => {
    if (!selectedSession.value) return;
    loadingOpenBill.value = true;
    try {
        const res = await axios.post(route("play-area.session.open", selectedSession.value.session.id));
        // Update session in list
        const idx = activeSessions.value.findIndex(s => s.session.id === selectedSessionId.value);
        if (idx !== -1) {
            activeSessions.value[idx].session = res.data.session;
        }
        // Open barcode print in new tab
        window.open(route("play-area.barcode.print", res.data.session.id), "_blank");
        updateAllTimers();
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Failed to open session.";
    } finally {
        loadingOpenBill.value = false;
    }
};

const reprintBarcode = () => {
    if (!selectedSession.value) return;
    window.open(route("play-area.barcode.print", selectedSession.value.session.id), "_blank");
};

// ── Confirm Final Bill (session) ──
const showConfirmPanel = ref(false);
const checkoutPaymentMethod = ref("cash");
const checkoutCash = ref(0);
const loadingClose = ref(false);

const confirmSessionFinalBill = async () => {
    if (!selectedSession.value) return;
    loadingClose.value = true;
    try {
        const res = await axios.post(route("play-area.session.close"), {
            barcode: selectedSession.value.session.barcode,
            payment_method: checkoutPaymentMethod.value,
            cash: checkoutCash.value,
            employee_id: selectedSession.value.session.employee_id || null,
            user_id: props.loggedInUser.id,
        });
        clearInterval(globalTimer);
        printFinalReceipt(res.data.session, res.data.totals, res.data.balance);
        await loadActiveSessions();
        selectedSessionId.value = null;
        showConfirmPanel.value = false;
        globalTimer = setInterval(updateAllTimers, 1000);
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Failed to confirm order.";
    } finally {
        loadingClose.value = false;
    }
};

// ── Sync products to a session ──
const syncingProducts = ref(false);

const syncSessionProducts = async (products) => {
    if (!selectedSession.value || syncingProducts.value) return;
    syncingProducts.value = true;
    const sessId = selectedSession.value.session.id;
    const barcode = selectedSession.value.session.barcode;
    try {
        const payload = barcode
            ? { barcode, products }
            : { session_id: sessId, products };
        const res = await axios.post(route("play-area.session.items.sync"), payload);
        const idx = activeSessions.value.findIndex(s => s.session.id === sessId);
        if (idx !== -1 && res.data.session) {
            activeSessions.value[idx].session = res.data.session;
            if (res.data.totals) activeSessions.value[idx].totals = res.data.totals;
        }
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Failed to update items.";
    } finally {
        syncingProducts.value = false;
    }
};

const removeSessionItem = (item) => {
    const remaining = sessionItems.value
        .filter(i => i.id !== item.id)
        .map(i => ({ id: i.product_id, quantity: i.quantity }));
    syncSessionProducts(remaining);
};

const incrementSessionItem = (item) => {
    const updated = sessionItems.value.map(i => ({
        id: i.product_id,
        quantity: i.id === item.id ? i.quantity + 1 : i.quantity,
    }));
    syncSessionProducts(updated);
};

const decrementSessionItem = (item) => {
    if (item.quantity <= 1) {
        removeSessionItem(item);
        return;
    }
    const updated = sessionItems.value.map(i => ({
        id: i.product_id,
        quantity: i.id === item.id ? i.quantity - 1 : i.quantity,
    }));
    syncSessionProducts(updated);
};

// ── Add Customer Modal ──
const showAddCustomerModal = ref(false);
const loadingCreateSession = ref(false);
const customerSearchQuery = ref("");
const customerSearchResults = ref([]);
const searchingCustomers = ref(false);
const selectedCustomerId = ref(null);
let customerSearchDebounce = null;

const newSession = reactive({
    customerName: "",
    customerContact: "",
    customerEmail: "",
    employeeId: "",
    packageId: "",
    packageQuantity: 1,
});

const showCustomerSearchDropdown = computed(() =>
    customerSearchQuery.value.trim().length > 0
);

const resetCustomerLookup = () => {
    customerSearchQuery.value = "";
    customerSearchResults.value = [];
    searchingCustomers.value = false;
    selectedCustomerId.value = null;
    if (customerSearchDebounce) {
        clearTimeout(customerSearchDebounce);
        customerSearchDebounce = null;
    }
};

const openAddCustomerModal = () => {
    isLiveBillMode.value = false;
    showAddCustomerModal.value = true;
};

const closeAddCustomerModal = () => {
    showAddCustomerModal.value = false;
    resetCustomerLookup();
};

const fetchCustomers = async () => {
    const query = customerSearchQuery.value.trim();

    if (!query) {
        customerSearchResults.value = [];
        searchingCustomers.value = false;
        return;
    }

    searchingCustomers.value = true;
    try {
        const res = await axios.get(route("pos.customers.search"), { params: { q: query } });
        customerSearchResults.value = res.data.customers || [];
    } catch (err) {
        customerSearchResults.value = [];
    } finally {
        searchingCustomers.value = false;
    }
};

const handleCustomerSearchInput = () => {
    selectedCustomerId.value = null;

    if (customerSearchDebounce) {
        clearTimeout(customerSearchDebounce);
    }

    customerSearchDebounce = setTimeout(fetchCustomers, 250);
};

const selectCustomer = (customer) => {
    selectedCustomerId.value = customer.id;
    newSession.customerName = customer.name || "";
    newSession.customerContact = customer.phone || "";
    newSession.customerEmail = customer.email || "";
    customerSearchQuery.value = customer.name || "";
    customerSearchResults.value = [];
};

const newSessionPackagePreview = computed(
    () => props.packages?.find(p => Number(p.id) === Number(newSession.packageId)) || null
);

const packageUnitTotal = computed(() => getPackageTotal(newSessionPackagePreview.value));

const packageGrandTotal = computed(() =>
    packageUnitTotal.value * Math.max(1, Number(newSession.packageQuantity || 1))
);

const getPackageTotal = (pkg) => {
    if (!pkg) return 0;

    return Number(pkg.base_price || 0) + Number(pkg.additional_payment || 0);
};

const createNewSession = async () => {
    loadingCreateSession.value = true;
    try {
        const res = await axios.post(route("play-area.session.create"), {
            open: false, // create as pending
            package_id: newSession.packageId || null,
            package_quantity: Math.max(1, Number(newSession.packageQuantity || 1)),
            employee_id: newSession.employeeId || null,
            user_id: props.loggedInUser.id,
            customer: {
                name: newSession.customerName,
                contactNumber: newSession.customerContact,
                email: newSession.customerEmail,
                age: null,
            },
            products: [],
        });

        Object.assign(newSession, { customerName: "", customerContact: "", customerEmail: "", employeeId: "", packageId: "", packageQuantity: 1 });
        resetCustomerLookup();
        closeAddCustomerModal();

        await loadActiveSessions();
        if (res.data.session) selectedSessionId.value = res.data.session.id;

        isAlertModalOpen.value = true;
        message.value = "Customer added. Click 'Open Bill & Print' to start the session.";
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Failed to create session.";
    } finally {
        loadingCreateSession.value = false;
    }
};

// ── Package Picker ──
const showPackagePicker = ref(false);
const packageSearch = ref("");
const packageDurationFilter = ref("all");
const packagePriceFilter = ref("all");

const filteredPackages = computed(() => {
    const search = packageSearch.value.trim().toLowerCase();
    return (props.packages || []).filter(pkg => {
        const duration = Number(pkg.base_time_minutes || 0);
        const price = getPackageTotal(pkg);
        const matchSearch = !search || pkg.name?.toLowerCase().includes(search);
        const matchDur = packageDurationFilter.value === "all"
            || (packageDurationFilter.value === "short" && duration <= 60)
            || (packageDurationFilter.value === "medium" && duration > 60 && duration <= 120)
            || (packageDurationFilter.value === "long" && duration > 120);
        const matchPrice = packagePriceFilter.value === "all"
            || (packagePriceFilter.value === "budget" && price <= 1000)
            || (packagePriceFilter.value === "standard" && price > 1000 && price <= 3000)
            || (packagePriceFilter.value === "premium" && price > 3000);
        return matchSearch && matchDur && matchPrice;
    });
});

// ── Live Bill ──
const isLiveBillMode = ref(false);
const liveBillProducts = ref([]);
const showLiveBillConfirm = ref(false);
const liveBillPaymentMethod = ref("cash");
const liveBillCash = ref(0);
const loadingLiveBill = ref(false);

const liveBillTotal = computed(() =>
    liveBillProducts.value.reduce((t, i) => t + i.selling_price * i.quantity, 0).toFixed(2)
);

const switchToLiveBill = () => {
    // Live bill is a walk-in flow; clear customer/session specific UI state.
    showAddCustomerModal.value = false;
    showPackagePicker.value = false;
    Object.assign(newSession, { customerName: "", customerContact: "", customerEmail: "", employeeId: "", packageId: "", packageQuantity: 1 });
    resetCustomerLookup();

    isLiveBillMode.value = true;
    selectedSessionId.value = null;
    fetchedOrder.value = null;
    showConfirmPanel.value = false;
};

const removeLiveBillItem = (item) => {
    liveBillProducts.value = liveBillProducts.value.filter(i => i.id !== item.id);
};

const confirmLiveBill = async () => {
    if (liveBillProducts.value.length === 0) return;
    loadingLiveBill.value = true;
    try {
        // Create session with open:true (no package), add products, then close
        const createRes = await axios.post(route("play-area.session.create"), {
            open: true,
            package_id: null,
            employee_id: null,
            user_id: props.loggedInUser.id,
            customer: { name: "", contactNumber: "", email: "", age: null },
            products: liveBillProducts.value.map(i => ({ id: i.id, quantity: i.quantity })),
        });
        const session = createRes.data.session;

        const closeRes = await axios.post(route("play-area.session.close"), {
            barcode: session.barcode,
            payment_method: liveBillPaymentMethod.value,
            cash: liveBillCash.value,
            user_id: props.loggedInUser.id,
        });
        printFinalReceipt(closeRes.data.session, closeRes.data.totals, closeRes.data.balance);
        liveBillProducts.value = [];
        showLiveBillConfirm.value = false;
        isLiveBillMode.value = false;
        await loadActiveSessions();
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Failed to process live bill.";
    } finally {
        loadingLiveBill.value = false;
    }
};

// ── Fetched order checkout (standalone barcode scan) ──
const fetchedOrder = ref(null);
const orderTotals = reactive({ package_total: 0, extra_amount: 0, products_total: 0, final_total: 0, extra_minutes: 0, elapsed_minutes: 0 });
const elapsedDisplay = ref(0);
let fetchedOrderTimer = null;

const startFetchedOrderTimer = () => {
    clearInterval(fetchedOrderTimer);
    fetchedOrderTimer = setInterval(() => {
        if (!fetchedOrder.value || fetchedOrder.value.status === "closed" || !fetchedOrder.value.package_id) {
            clearInterval(fetchedOrderTimer);
            return;
        }
        const elapsed = Math.max(0, Math.floor((Date.now() - new Date(fetchedOrder.value.start_time).getTime()) / 60000));
        elapsedDisplay.value = elapsed;
        const extra = Math.max(0, elapsed - Number(fetchedOrder.value.base_time_minutes || 0));
        const perUnit = Math.max(1, Number(fetchedOrder.value.extra_charge_per_minutes || 1));
        const units = extra > 0 ? Math.ceil(extra / perUnit) : 0;
        orderTotals.extra_minutes = extra;
        orderTotals.extra_amount = units * Number(fetchedOrder.value.extra_charge || 0);
        orderTotals.final_total = Number(fetchedOrder.value.package_total || 0) + Number(orderTotals.products_total || 0) + Number(orderTotals.extra_amount || 0);
    }, 1000);
};

const fetchOrderByBarcode = async (barcode) => {
    try {
        const res = await axios.post(route("play-area.session.fetch"), { barcode });
        fetchedOrder.value = res.data.session;
        Object.assign(orderTotals, res.data.totals || {});
        elapsedDisplay.value = res.data.totals?.elapsed_minutes || 0;
        checkoutCash.value = Number(res.data.totals?.final_total || 0);
        if (fetchedOrder.value.package_id) startFetchedOrderTimer();
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Order not found.";
    }
};

const confirmFetchedOrder = async () => {
    if (!fetchedOrder.value) return;
    loadingClose.value = true;
    try {
        const res = await axios.post(route("play-area.session.close"), {
            barcode: fetchedOrder.value.barcode,
            payment_method: checkoutPaymentMethod.value,
            cash: checkoutCash.value,
            user_id: props.loggedInUser.id,
        });
        clearInterval(fetchedOrderTimer);
        printFinalReceipt(res.data.session, res.data.totals, res.data.balance);
        fetchedOrder.value = null;
        await loadActiveSessions();
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Failed to confirm order.";
    } finally {
        loadingClose.value = false;
    }
};

// ── Barcode scanner ──
const form = useForm({ barcode: "" });

const submitBarcode = async () => {
    const scanned = (form.barcode || "").trim();
    if (!scanned) return;

    // PA... barcode → look for order
    if (/^PA[A-Z0-9]+$/i.test(scanned)) {
        // Check if it belongs to an active session in the list
        const found = activeSessions.value.find(s => s.session.barcode === scanned);
        if (found) {
            selectSession(found);
        } else {
            await fetchOrderByBarcode(scanned);
        }
        form.barcode = "";
        return;
    }

    // Product barcode
    const targetLiveBill = isLiveBillMode.value;
    const targetSession = selectedSession.value;

    if (!targetLiveBill && !targetSession) {
        isAlertModalOpen.value = true;
        message.value = "Please select a customer or enable Live Bill mode first.";
        return;
    }

    try {
        const res = await axios.post(route("pos.getProduct"), { barcode: scanned });
        const { product: fetchedProduct, error: fetchedError } = res.data;

        if (!fetchedProduct) {
            isAlertModalOpen.value = true;
            message.value = fetchedError || "Product not found.";
            return;
        }
        if (fetchedProduct.stock_quantity < 1) {
            isAlertModalOpen.value = true;
            message.value = "Product is out of stock.";
            return;
        }

        if (targetLiveBill) {
            const existing = liveBillProducts.value.find(i => i.id === fetchedProduct.id);
            if (existing) { existing.quantity++; } else {
                liveBillProducts.value.push({ ...fetchedProduct, quantity: 1 });
            }
        } else {
            // Add to session
            const currentItems = sessionItems.value.map(i => ({ id: i.product_id, quantity: i.quantity }));
            const ex = currentItems.find(i => i.id === fetchedProduct.id);
            if (ex) { ex.quantity++; } else { currentItems.push({ id: fetchedProduct.id, quantity: 1 }); }
            await syncSessionProducts(currentItems);
        }
        form.barcode = "";
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Error scanning product.";
    }
};

const handleSelectedProducts = (selectedProducts) => {
    if (isLiveBillMode.value) {
        selectedProducts.forEach(p => {
            const ex = liveBillProducts.value.find(i => i.id === p.id);
            if (ex) { ex.quantity++; } else { liveBillProducts.value.push({ ...p, quantity: 1 }); }
        });
        return;
    }
    if (!selectedSession.value) {
        if (isLiveBillMode.value) {
            return;
        }
        isAlertModalOpen.value = true;
        message.value = "Please select a customer or use Live Bill.";
        return;
    }
    const currentItems = sessionItems.value.map(i => ({ id: i.product_id, quantity: i.quantity }));
    selectedProducts.forEach(p => {
        const ex = currentItems.find(i => i.id === p.id);
        if (ex) { ex.quantity++; } else { currentItems.push({ id: p.id, quantity: 1 }); }
    });
    syncSessionProducts(currentItems);
};

// ── Receipt printer ──
const printFinalReceipt = (sessionData, totalsData, balance) => {
    const payMethod = String(checkoutPaymentMethod.value || liveBillPaymentMethod.value || "cash");
    const cashPaid = Number(totalsData.cash ?? checkoutCash.value ?? liveBillCash.value ?? 0);
    const productRows = (sessionData.items || []).map(item =>
        `<tr>
            <td>${item.product?.name || "Item"}</td>
            <td style="text-align:center;">${item.quantity}</td>
            <td style="text-align:right;">LKR ${Number(item.total_price || 0).toFixed(2)}</td>
        </tr>`
    ).join("");

    const html = `<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Final Bill ${sessionData.barcode || ''}</title>
<script src="/js/JsBarcode.all.min.js"><\/script>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
@media print { html,body { width:100mm; } .receipt { width:100mm; } }
body { background:#fff; font-family:Arial,sans-serif; font-size:12px; color:#000; }
.receipt { width:100mm; margin:0 auto; padding:6mm 5mm 10mm 5mm; }
.header { text-align:center; border-bottom:1px dashed #000; padding-bottom:8px; margin-bottom:10px; }
.header img { width:130px; height:auto; display:block; margin:0 auto 5px auto; }
.header .shop { font-size:15px; font-weight:bold; letter-spacing:2px; margin-bottom:2px; }
.header .tel { font-size:11px; }
.title-sec { text-align:center; border-bottom:1px dashed #000; padding-bottom:8px; margin-bottom:10px; }
.title-sec .lbl { font-size:15px; font-weight:bold; letter-spacing:1px; margin-bottom:4px; }
.title-sec .bill-no { font-size:13px; font-weight:bold; }
.title-sec .bill-dt { font-size:10px; color:#555; margin-top:3px; }
.info-sec { border-bottom:1px dashed #000; padding-bottom:8px; margin-bottom:10px; }
.irow { display:flex; justify-content:space-between; margin-bottom:4px; font-size:11px; }
.irow .k { font-weight:bold; }
.items-sec { margin-bottom:10px; }
table { width:100%; border-collapse:collapse; font-size:11px; }
th,td { padding:3px 4px; }
th { text-align:left; border-bottom:1px solid #000; }
td { vertical-align:top; }
td:nth-child(2) { text-align:center; }
td:nth-child(3) { text-align:right; white-space:nowrap; }
.tot-sec { border-top:1px dashed #000; padding-top:7px; margin-bottom:10px; }
.tot-row { display:flex; justify-content:space-between; margin-bottom:4px; font-size:12px; }
.grand-row { display:flex; justify-content:space-between; font-size:14px; font-weight:bold; border-top:1px solid #000; padding-top:5px; margin-top:5px; margin-bottom:7px; }
.pay-box { border:1px solid #ccc; border-radius:3px; padding:5px 7px; font-size:11px; margin-bottom:10px; }
.pay-box span { font-weight:bold; }
.barcode-sec { text-align:center; border-top:1px dashed #000; border-bottom:1px dashed #000; padding:8px 0 6px 0; margin-bottom:10px; }
.barcode-sec svg { width:100%; max-width:90mm; }
.barcode-sec .bc-text { font-size:12px; font-weight:bold; letter-spacing:2px; margin-top:4px; }
.footer { text-align:center; font-size:10px; color:#444; }
.footer p { margin-bottom:3px; }
</style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <img src="/images/logo.png" alt="Logo" onerror="this.style.display='none'" />
        <div class="shop">PLAY AREA</div>
        <div class="tel">Tel : 077 306 3000</div>
    </div>

    <div class="title-sec">
        <div class="lbl">FINAL BILL</div>
        <div class="bill-no">${sessionData.barcode || ''}</div>
        <div class="bill-dt">${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</div>
    </div>

    <div class="info-sec">
        <div class="irow"><span class="k">Customer</span><span>${sessionData.customer_name || 'Walk-in'}</span></div>
        ${sessionData.package ? `<div class="irow"><span class="k">Service</span><span>${sessionData.package.name}</span></div>` : ''}
        ${sessionData.package && totalsData.elapsed_minutes > 0 ? `<div class="irow"><span class="k">Time Used</span><span>${totalsData.elapsed_minutes} mins</span></div>` : ''}
        ${totalsData.extra_minutes > 0 ? `<div class="irow"><span class="k">Overtime</span><span>${totalsData.extra_minutes} mins</span></div>` : ''}
    </div>

    ${productRows ? `
    <div class="items-sec">
        <table>
            <thead><tr><th>Item</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Price</th></tr></thead>
            <tbody>${productRows}</tbody>
        </table>
    </div>` : ''}

    <div class="tot-sec">
        ${sessionData.package_id ? `<div class="tot-row"><span>Package</span><span>LKR ${(Number(totalsData.package_total)||0).toFixed(2)}</span></div>` : ''}
        ${Number(totalsData.products_total) > 0 ? `<div class="tot-row"><span>Products</span><span>LKR ${(Number(totalsData.products_total)||0).toFixed(2)}</span></div>` : ''}
        ${totalsData.extra_amount > 0 ? `<div class="tot-row" style="color:#c00;"><span>Extra Time</span><span>LKR ${(Number(totalsData.extra_amount)||0).toFixed(2)}</span></div>` : ''}
        <div class="grand-row"><span>GRAND TOTAL</span><span>LKR ${(Number(totalsData.final_total)||0).toFixed(2)}</span></div>
        <div class="tot-row"><span>Cash</span><span>LKR ${cashPaid.toFixed(2)}</span></div>
        <div class="tot-row" style="font-weight:bold;"><span>Balance</span><span>LKR ${(Number(balance)||0).toFixed(2)}</span></div>
    </div>

    <div class="pay-box">Payment Method: &nbsp;<span>${payMethod.charAt(0).toUpperCase() + payMethod.slice(1)}</span></div>

    <div class="barcode-sec">
        <svg id="finalBarcode"></svg>
        <div class="bc-text">${sessionData.barcode || ''}</div>
    </div>

    <div class="footer">
        <p>We hope you enjoyed the play area!</p>
        <p>Come again for more fun times.</p>
        <p style="font-weight:bold; margin-top:3px;">Powered by JAAN Network Ltd.</p>
    </div>
</div>
<script>
if (typeof JsBarcode !== 'undefined' && '${sessionData.barcode || ''}') {
    JsBarcode('#finalBarcode', '${sessionData.barcode || ''}', {
        format:'CODE128', width:3, height:80, displayValue:false, margin:5
    });
}
window.onload = function() { window.print(); };
<\/script>
</body>
</html>`;
    const w = window.open("", "_blank", "width=700,height=900");
    w.document.write(html);
    w.document.close();
};

// ── Keyboard scanner ──
let kbBarcode = "";
let kbTimeout;

const handleScannerInput = (event) => {
    const tag = document.activeElement?.tagName?.toLowerCase();
    if (tag === "input" || tag === "textarea" || tag === "select") return;
    clearTimeout(kbTimeout);
    if (event.key === "Enter") {
        form.barcode = kbBarcode;
        submitBarcode();
        kbBarcode = "";
    } else {
        kbBarcode += event.key;
    }
    kbTimeout = setTimeout(() => { kbBarcode = ""; }, 100);
};

onMounted(() => {
    document.addEventListener("keypress", handleScannerInput);
    loadActiveSessions();
    globalTimer = setInterval(updateAllTimers, 1000);
});

onBeforeUnmount(() => {
    document.removeEventListener("keypress", handleScannerInput);
    clearInterval(globalTimer);
    clearInterval(fetchedOrderTimer);
});
</script>

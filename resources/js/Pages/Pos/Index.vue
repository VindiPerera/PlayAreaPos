<template>

    <Head title="POS" />
    <Banner />
    <div class="flex flex-col items-center justify-start min-h-screen py-6 space-y-3 bg-gray-100 md:px-20 px-4">
        <!-- Include the Header -->
        <Header />

        <div class="w-full md:w-5/6 w-full py-4 space-y-8">
            <div class="flex items-center justify-between space-x-4">
                <div class="flex w-full space-x-4">
                    <Link href="/">
                    <img src="/images/back-arrow.png" class="w-14 h-14" />
                    </Link>
                    <p class="pt-3 text-4xl font-bold tracking-wide text-black uppercase">
                        PoS
                    </p>
                </div>
                <div class="flex items-center justify-between w-full space-x-4">
                    <p class="text-3xl font-bold tracking-wide text-black">
                        Order ID : #{{ orderid  }}
                    </p>
                    <div class="flex items-center space-x-3">
                        <p class="text-3xl text-black cursor-pointer">
                        <i @click="refreshData" class="ri-restart-line"></i>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex md:flex-row flex-col w-full gap-4">
                <div class="flex flex-col md:w-1/2 w-full">
                    <div class="flex flex-col w-full">
                        <div class="p-8 space-y-5 bg-black shadow-lg rounded-3xl">
                            <p class="mb-2 text-4xl font-bold text-white">Customer Details</p>
                            <div class="mb-3">
                                <input v-model="customer.name" type="text" placeholder="Enter Customer Name"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="flex gap-2 mb-3 text-black">
                                <!-- <select
                  v-model="customer.countryCode"
                  class="w-[60px] px-2 py-2 bg-white placeholder-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="+94">+94</option>
                  <option value="+1">+1</option>
                  <option value="+44">+44</option>
                </select> -->
                                <input v-model="customer.contactNumber" type="text"
                                    placeholder="Enter Customer Contact Number"
                                    class="flex-grow px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="text-black">
                                <input v-model="customer.email" type="email" placeholder="Enter Customer Email"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>

                            <div class="text-black">
                                <select required v-model="employee_id" id="employee_id"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Select an Employee</option>
                                    <option v-for="employee in allemployee" :key="employee.id" :value="employee.id">
                                        {{ employee.name }}
                                    </option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="flex md:w-1/2 w-full p-6 border-4 border-black rounded-3xl">
                    <div class="flex flex-col items-start justify-start w-full md:px-6 px-2">
                        <div class="flex items-center justify-between w-full">
                            <h2 class="md:text-5xl text-4xl font-bold text-black">Billing Details</h2>
                            <div class="flex items-center gap-5">
                                <span class="flex cursor-pointer" @click="showPackagePicker = true">
                                    <p class="text-xl text-blue-600 font-bold">Package</p>
                                    <i class="ri-price-tag-3-line text-2xl text-blue-600 ml-2"></i>
                                </span>
                                <span class="flex cursor-pointer" @click="isSelectModalOpen = true">
                                    <p class="text-xl text-blue-600 font-bold">User Manual</p>
                                    <img src="/images/selectpsoduct.svg" class="w-6 h-6 ml-2" />
                                </span>
                            </div>
                        </div>


                        <div class="flex items-end justify-between w-full my-3 border-2 border-black rounded-2xl">
                            <div class="flex items-center justify-center w-3/4">
                                <label for="search" class="text-xl font-medium text-gray-800"></label>
                                <input v-model="form.barcode" id="search" type="text" placeholder="Scan Product Barcode / Order Barcode"
                                    class="w-full h-16 px-4 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div class="flex items-end justify-end w-1/4">
                                <button @click="submitBarcode"
                                    class="px-12 py-4 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-r-xl">
                                    Enter
                                </button>
                            </div>
                        </div>

                        <!-- <div class="max-w-xs relative space-y-3">
              <label for="search" class="text-gray-900">
                Type the product name to search
              </label>

              <input
                v-model="form.barcode"
                id="search"
                type="text"
                placeholder="Enter BarCode Here!"
                class="w-full h-16 px-4 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <ul
                v-if="searchResults.length"
                class="w-full rounded bg-white border border-gray-300 px-4 py-2 space-y-1 absolute z-10"
              >
                <li class="px-1 pt-1 pb-2 font-bold border-b border-gray-200">
                  Showing {{ searchResults.length }} results
                </li>
                <li
                  v-for="product in searchResults"
                  :key="product.id"
                  @click="selectProduct(product.name)"
                  class="cursor-pointer hover:bg-gray-100 p-1"
                >
                  {{ product.name }}
                </li>
              </ul>

              <p v-if="form.barcode" class="text-lg pt-2 absolute">
                You have selected:
                <span class="font-semibold">{{ form.barcode }}</span>
              </p>
            </div> -->

                        <div class="w-full text-center">
                            <p v-if="products.length === 0 && !selectedPackagePreview" class="text-2xl text-red-500">
                                No Products to show
                            </p>
                        </div>

                        <div v-if="selectedPackagePreview" class="flex items-center w-full py-4 border-b border-black">
                            <div class="flex w-1/6 items-center justify-center">
                                <i class="ri-price-tag-3-line text-4xl text-blue-700"></i>
                            </div>
                            <div class="flex flex-col justify-between w-5/6">
                                <p class="text-xl font-semibold text-black">{{ selectedPackagePreview.name }}</p>
                                <p class="text-sm text-gray-700">Service Package • {{ selectedPackagePreview.base_time_minutes }} mins</p>
                                <p class="text-2xl font-bold text-black text-right">{{ previewPackageTotal.toFixed(2) }} LKR</p>
                            </div>
                            <div class="flex justify-end w-1/6">
                                <p @click="clearSelectedPackage"
                                    class="text-3xl text-black border-2 border-black rounded-full cursor-pointer">
                                    <i class="ri-close-line"></i>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center w-full py-4 border-b border-black" v-for="item in products"
                            :key="item.id">
                            <div class="flex w-1/6">
                                <img :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'
                                    " alt="Supplier Image" class="object-cover w-16 h-16 border border-gray-500" />
                            </div>
                            <div class="flex flex-col justify-between w-5/6">
                                <p class="text-xl text-black">
                                    {{ item.name }}


                                </p>

                                <div
  v-if="Number(item.is_promotion) === 1"
  class="mt-2 rounded-lg border border-gray-200 p-3 bg-gray-50"
>
  <p class="text-md font-bold text-black mb-2">
    Pack items
  </p>

  <!-- Scrollable list -->
  <ul
    class="mt-1 list-disc pl-5 space-y-1 max-h-40 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
  >
    <li
      v-for="pi in item.promotion_items ?? []"
      :key="pi.id"
      class="text-sm text-gray-700 bg-white rounded-md px-2 py-1 shadow-sm hover:bg-gray-50"
    >

      <span v-if="pi.product">  {{ pi.product.name }}</span>
      <span class="ml-2 text-lg text-dark">× {{ pi.quantity }}</span>
    </li>
  </ul>
</div>
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex space-x-4">
                                        <p @click="incrementQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer">
                                            <i class="ri-add-line"></i>
                                        </p>
                                        <!-- <p
                      class="bg-[#D9D9D9] border-2 border-black h-8 w-8 text-black flex justify-center items-center rounded"
                    >
                      {{ item.quantity }}
                    </p> -->
                                        <input type="number" v-model="item.quantity" min="0"
                                            class="bg-[#D9D9D9] border-2 border-black h-8 w-24 text-black flex justify-center items-center rounded text-center" />
                                        <p @click="decrementQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer">
                                            <i class="ri-subtract-line"></i>
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <div>
                                            <p @click="applyDiscount(item.id)" v-if="
                                                item.discount &&
                                                item.discount > 0 &&
                                                item.apply_discount == false &&
                                                !appliedCoupon
                                            "
                                                class="cursor-pointer py-1 text-center px-4 bg-green-600 rounded-xl font-bold text-white tracking-wider">
                                                Apply {{ item.discount }}% off
                                            </p>

                                            <p v-if="
                                                item.discount &&
                                                item.discount > 0 &&
                                                item.apply_discount == true &&
                                                !appliedCoupon
                                            " @click="removeDiscount(item.id)"
                                                class="cursor-pointer py-1 text-center px-4 bg-red-600 rounded-xl font-bold text-white tracking-wider">
                                                Remove {{ item.discount }}% Off
                                            </p>
                                            <p class="text-2xl font-bold text-black text-right">
                                                {{ item.selling_price }}
                                                LKR
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end w-1/6">
                                <p @click="removeProduct(item.id)"
                                    class="text-3xl text-black border-2 border-black rounded-full cursor-pointer">
                                    <i class="ri-close-line"></i>
                                </p>
                            </div>
                        </div>
                        <div class="w-full pt-6 space-y-2">
                            <div class="flex items-center justify-between w-full px-8">
                                <p class="text-xl">Products Total</p>
                                <p class="text-xl">{{ subtotal }} LKR</p>
                            </div>
                            <div v-if="selectedPackagePreview" class="flex items-center justify-between w-full px-8">
                                <p class="text-xl">Service Package</p>
                                <p class="text-xl">{{ previewPackageTotal.toFixed(2) }} LKR</p>
                            </div>
                            <div class="flex items-center justify-between w-full px-8 py-2 pb-4 border-b border-black">
                                <p class="text-xl">Discount</p>
                                <p class="text-xl">( {{ totalDiscount }} LKR )</p>
                            </div>
                            <!-- <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                <p class="text-xl text-black">Custom Discount</p>
                <span>
                  <CurrencyInput
                    v-model="custom_discount"
                  />
                  <span class="ml-2">LKR</span>
                </span>
              </div> -->




                            <div class="flex items-center justify-between w-full px-8 pt-4">
                                <p class="text-3xl font-bold text-black">Estimated Total</p>
                                <p class="text-3xl font-bold text-black">{{ estimatedTotal }} LKR</p>
                            </div>
                        </div>

                        <!-- Confirm / Print Barcode actions -->
                        <div class="flex items-center justify-center w-full mt-6">
                            <div class="w-full grid md:grid-cols-2 gap-3">
                                <button @click="confirmOrderDirect" type="button"
                                    :disabled="loadingCreateOrder || loadingClose || (products.length === 0 && !selectedPackageId)"
                                    class="w-full bg-black py-4 text-2xl font-bold tracking-wider text-center text-white uppercase rounded-xl disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="pr-4 ri-check-double-line"></i>
                                    {{ (loadingCreateOrder || loadingClose) ? 'Processing...' : 'Confirm Order' }}
                                </button>

                                <button @click="printBarcodeForOrder" type="button"
                                    :disabled="loadingCreateOrder || loadingClose || (products.length === 0 && !selectedPackageId)"
                                    class="w-full bg-blue-600 py-4 text-2xl font-bold tracking-wider text-center text-white uppercase rounded-xl disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="pr-4 ri-barcode-line"></i>
                                    {{ loadingCreateOrder ? 'Please wait...' : 'Print Barcode' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── Session Checkout ─── -->
            <div class="w-full p-8 bg-white rounded-3xl shadow-lg space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-4xl font-bold text-black">Order Checkout</h3>
                    <p class="text-gray-500 text-lg">Use top barcode field to fetch order</p>
                </div>

                <div v-if="fetchedOrder" class="rounded-2xl border border-gray-200 shadow-sm p-5 space-y-5">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Order Barcode</p>
                            <p class="text-xl font-bold text-black">{{ fetchedOrder.barcode }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Customer</p>
                            <p class="text-xl font-bold text-black">{{ fetchedOrder.customer_name || 'Walk-in' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="text-lg font-semibold" :class="fetchedOrder.status === 'closed' ? 'text-red-600' : 'text-green-600'">
                                {{ fetchedOrder.status === 'closed' ? 'Closed' : 'Active' }}
                            </p>
                        </div>
                    </div>

                    <!-- Product items list -->
                    <div v-if="fetchedOrder.items && fetchedOrder.items.length" class="space-y-2">
                        <p class="font-bold text-black text-lg">Products</p>
                        <div v-for="item in fetchedOrder.items" :key="item.id"
                            class="flex justify-between text-gray-700 border-b pb-1 text-lg">
                            <span>{{ item.product?.name || '—' }} × {{ item.quantity }}</span>
                            <span>{{ Number(item.total_price).toFixed(2) }} LKR</span>
                        </div>
                    </div>
                    <div v-else class="text-gray-400 text-lg">No products in this order.</div>

                    <!-- Live service timer card (only for orders with service package) -->
                    <div v-if="fetchedOrder.package_id" class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <p class="text-sm text-blue-700 font-semibold">Service: {{ fetchedOrder.package?.name }}</p>
                        <p class="text-5xl font-bold text-blue-900 mt-1">{{ elapsedDisplay }} mins</p>
                        <div class="mt-2 text-sm text-blue-900 grid grid-cols-2 gap-2">
                            <p>Extra Time: <span class="font-semibold">{{ orderTotals.extra_minutes }} mins</span></p>
                            <p class="text-right">Extra Charge: <span class="font-semibold">{{ Number(orderTotals.extra_amount || 0).toFixed(2) }} LKR</span></p>
                        </div>
                    </div>

                    <!-- Totals summary -->
                    <div class="border-t pt-3 space-y-2 text-lg">
                        <div v-if="fetchedOrder.package_id" class="flex justify-between">
                            <span>Package Total</span><span>{{ Number(orderTotals.package_total || 0).toFixed(2) }} LKR</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Products Total</span><span>{{ Number(orderTotals.products_total || 0).toFixed(2) }} LKR</span>
                        </div>
                        <div v-if="fetchedOrder.package_id" class="flex justify-between">
                            <span>Overtime Charge</span><span>{{ Number(orderTotals.extra_amount || 0).toFixed(2) }} LKR</span>
                        </div>
                        <div class="flex justify-between text-3xl font-bold border-t pt-2">
                            <span>Final Total</span><span>{{ Number(orderTotals.final_total || 0).toFixed(2) }} LKR</span>
                        </div>
                    </div>

                    <!-- Payment + Confirm -->
                    <div class="grid md:grid-cols-3 gap-4 items-center pt-2">
                        <div class="flex items-center space-x-3">
                            <p class="text-lg font-semibold text-black">Payment:</p>
                            <div @click="checkoutPaymentMethod = 'cash'"
                                :class="['cursor-pointer px-4 py-2 border rounded-xl font-bold transition-all', checkoutPaymentMethod === 'cash' ? 'bg-yellow-400 border-yellow-500 text-black' : 'border-gray-300 text-gray-700']">
                                Cash
                            </div>
                            <div @click="checkoutPaymentMethod = 'card'"
                                :class="['cursor-pointer px-4 py-2 border rounded-xl font-bold transition-all', checkoutPaymentMethod === 'card' ? 'bg-yellow-400 border-yellow-500 text-black' : 'border-gray-300 text-gray-700']">
                                Card
                            </div>
                        </div>
                        <input v-model.number="checkoutCash" type="number" min="0" placeholder="Cash Amount"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-black text-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <button @click="confirmBill" :disabled="loadingClose || fetchedOrder.status === 'closed'"
                            class="w-full py-3 text-xl font-bold text-white uppercase bg-black rounded-xl disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ loadingClose ? 'Processing...' : 'Confirm Order & Print Bill' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-if="showPackagePicker" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4" @click.self="showPackagePicker = false">
        <div class="w-full max-w-6xl bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-8 py-5 border-b border-gray-200">
                <h3 class="text-4xl font-bold text-black">Select Package</h3>
                <button class="text-4xl text-black" @click="showPackagePicker = false">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="p-6 border-b border-gray-200">
                <div class="grid md:grid-cols-4 gap-3">
                    <input
                        v-model="packageSearch"
                        type="text"
                        placeholder="Search package..."
                        class="px-4 py-3 border border-gray-300 rounded-xl text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <select
                        v-model="packageDurationFilter"
                        class="px-4 py-3 border border-gray-300 rounded-xl text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="all">All Durations</option>
                        <option value="short">Up to 60 mins</option>
                        <option value="medium">61-120 mins</option>
                        <option value="long">More than 120 mins</option>
                    </select>
                    <select
                        v-model="packagePriceFilter"
                        class="px-4 py-3 border border-gray-300 rounded-xl text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="all">All Prices</option>
                        <option value="budget">Up to 1000 LKR</option>
                        <option value="standard">1001-3000 LKR</option>
                        <option value="premium">More than 3000 LKR</option>
                    </select>
                    <button
                        type="button"
                        @click="resetPackageFilters"
                        class="px-4 py-3 bg-blue-600 text-white rounded-xl text-lg font-semibold"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <div class="p-6 max-h-[62vh] overflow-y-auto">
                <div class="grid md:grid-cols-3 gap-4">
                    <button
                        type="button"
                        @click="selectPackage('')"
                        class="text-left p-5 rounded-2xl border-2 transition-all"
                        :class="selectedPackageId === '' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
                    >
                        <p class="text-2xl font-bold text-black">No Service Package</p>
                        <p class="text-gray-600 mt-2">Products only billing</p>
                    </button>

                    <button
                        type="button"
                        v-for="pkg in filteredPackages"
                        :key="pkg.id"
                        @click="selectPackage(pkg.id)"
                        class="text-left p-5 rounded-2xl border-2 transition-all"
                        :class="Number(selectedPackageId) === Number(pkg.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-2xl font-bold text-black leading-tight">{{ pkg.name }}</p>
                            <span class="text-sm font-semibold bg-green-100 text-green-700 px-3 py-1 rounded-full">
                                {{ Number(pkg.base_price).toFixed(2) }} LKR
                            </span>
                        </div>
                        <p class="text-gray-700 mt-3">Duration: <span class="font-semibold">{{ pkg.base_time_minutes }} mins</span></p>
                        <p class="text-gray-700">Overtime: +{{ Number(pkg.extra_charge || 0).toFixed(2) }} per {{ Number(pkg.extra_charge_per_minutes || 1) }} mins</p>
                        <p v-if="Number(pkg.additional_payment || 0) > 0" class="text-gray-700">
                            Age Extra: +{{ Number(pkg.additional_payment).toFixed(2) }} (age &gt; {{ pkg.age_threshold }})
                        </p>
                    </button>
                </div>

                <div v-if="filteredPackages.length === 0" class="text-center py-12 text-gray-500 text-xl">
                    No packages found for the selected filters.
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
import CurrencyInput from "@/Components/custom/CurrencyInput.vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";
import ProductAutoComplete from "@/Components/custom/ProductAutoComplete.vue";
import { generateOrderId } from "@/Utils/Other.js";

const product = ref(null);
const error = ref(null);
const products = ref([]);
const isAlertModalOpen = ref(false);
const message = ref("");
const appliedCoupon = ref(null);
const cash = ref(0);
const custom_discount = ref(0);
const isSelectModalOpen = ref(false);
const custom_discount_type = ref('percent');
const orderid = computed(() => generateOrderId());

// ── Unified Billing State ──
const selectedPackageId = ref("");
const showPackagePicker = ref(false);
const packageSearch = ref("");
const packageDurationFilter = ref("all");
const packagePriceFilter = ref("all");
const createdSession = ref(null);
const fetchedOrder = ref(null);
const orderTotals = reactive({
    package_total: 0,
    extra_amount: 0,
    products_total: 0,
    final_total: 0,
    extra_minutes: 0,
    elapsed_minutes: 0,
});
const elapsedDisplay = ref(0);
const checkoutPaymentMethod = ref("cash");
const checkoutCash = ref(0);
const loadingCreateOrder = ref(false);
const loadingFetch = ref(false);
const loadingClose = ref(false);
let orderTimer = null;

const props = defineProps({
    loggedInUser: Object, // Using backend product name to avoid messing with selected products
    allcategories: Array,
    allemployee: Array,
    packages: Array,
    colors: Array,
    sizes: Array,
});

const discount = ref(0);

const customer = ref({
    name: "",
    countryCode: "",
    contactNumber: "",
    email: "",
});

const employee_id = ref("");

const selectedPaymentMethod = ref("cash");

const refreshData = () => {
    router.visit(route("pos.index"), {
        preserveScroll: false, // Reset scroll
        preserveState: false, // Reset component state
    });
};

const removeProduct = (id) => {
    products.value = products.value.filter((item) => item.id !== id);
};

const removeCoupon = () => {
    appliedCoupon.value = null; // Clear the applied coupon
    couponForm.code = ""; // Clear the coupon field
};

const incrementQuantity = (id) => {
    const product = products.value.find((item) => item.id === id);
    if (product) {
        product.quantity += 1;
    }
};

const decrementQuantity = (id) => {
    const product = products.value.find((item) => item.id === id);
    if (product && product.quantity > 1) {
        product.quantity -= 1;
    }
};

const selectPackage = (packageId) => {
    selectedPackageId.value = packageId;
    showPackagePicker.value = false;

    if (!packageId) {
        isAlertModalOpen.value = true;
        message.value = "Package cleared. Products only mode selected.";
        return;
    }

    const selected = props.packages?.find((p) => Number(p.id) === Number(packageId));
    if (selected) {
        isAlertModalOpen.value = true;
        message.value = `Package added: ${selected.name}`;
    }
};

const clearSelectedPackage = () => {
    selectedPackageId.value = "";
    isAlertModalOpen.value = true;
    message.value = "Package removed.";
};

const resetPackageFilters = () => {
    packageSearch.value = "";
    packageDurationFilter.value = "all";
    packagePriceFilter.value = "all";
};

// const orderId = computed(() => {
//   const timestamp = Date.now().toString(36).toUpperCase(); // Convert timestamp to a base-36 string
//   const randomString = Math.random().toString(36).substr(2, 5).toUpperCase(); // Generate a shorter random string
//   return `ORD-${timestamp}-${randomString}`; // Combine to create unique order ID
// });
const orderId = computed(() => {
    const characters =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    return Array.from({ length: 6 }, () =>
        characters.charAt(Math.floor(Math.random() * characters.length))
    ).join("");
});

const submitOrder = async () => {
    // if (window.confirm("Are you sure you want to confirm the order?")) {
    console.log(products.value);
    if (balance.value < 0) {
        isAlertModalOpen.value = true;
        message.value = "Cash is not enough";
        return;
    }
    try {
        const response = await axios.post("/pos/submit", {
            customer: customer.value,
            products: products.value,
            employee_id: employee_id.value,
            paymentMethod: selectedPaymentMethod.value,
            userId: props.loggedInUser.id,
            orderid: orderid.value,
            cash: cash.value,
            custom_discount: custom_discount.value,
        });
        isSuccessModalOpen.value = true;
        console.log(response.data); // Handle success
    } catch (error) {
        if (error.response.status === 423) {
            isAlertModalOpen.value = true;
            message.value = error.response.data.message;
        }
        console.error(
            "Error submitting customer details:",
            error.response?.data || error.message
        );
        // alert("Failed to submit customer details. Please try again.");
    }
};

// ── Unified Billing Functions ──
const populateCheckoutFromSession = (session) => {
    fetchedOrder.value = session;

    orderTotals.package_total = Number(session.package_total || 0);
    orderTotals.products_total = Number(session.products_total || 0);
    orderTotals.extra_amount = Number(session.extra_amount || 0);
    orderTotals.extra_minutes = Number(session.extra_minutes || 0);
    orderTotals.elapsed_minutes = Number(session.elapsed_minutes || 0);
    orderTotals.final_total = Number(session.final_total || 0);

    elapsedDisplay.value = Number(session.elapsed_minutes || 0);
    checkoutCash.value = Number(session.final_total || 0);

    if (session.package_id && session.status !== "closed") {
        startLiveTimer();
    }
};

const createOrder = async () => {
    if (products.value.length === 0 && !selectedPackageId.value) {
        isAlertModalOpen.value = true;
        message.value = "Please add at least one product or select a service package.";
        return;
    }

    loadingCreateOrder.value = true;
    try {
        const response = await axios.post(route("play-area.session.create"), {
            package_id: selectedPackageId.value || null,
            employee_id: employee_id.value || null,
            user_id: props.loggedInUser.id,
            customer: {
                name: customer.value.name,
                contactNumber: customer.value.contactNumber,
                email: customer.value.email,
                age: null,
            },
            products: products.value.map((item) => ({
                id: item.id,
                quantity: item.quantity,
            })),
        });

        createdSession.value = response.data.session;
        form.barcode = createdSession.value.barcode;
        populateCheckoutFromSession(createdSession.value);
        return createdSession.value;
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Failed to create order.";
        return null;
    } finally {
        loadingCreateOrder.value = false;
    }
};

const confirmOrderDirect = async () => {
    const session = await createOrder();
    if (!session) {
        return;
    }

    // Confirm immediately and print final bill without barcode print step
    checkoutCash.value = Number(session.final_total || orderTotals.final_total || 0);

    loadingClose.value = true;
    try {
        const response = await axios.post(route("play-area.session.close"), {
            barcode: session.barcode,
            payment_method: checkoutPaymentMethod.value,
            cash: checkoutCash.value,
            employee_id: employee_id.value || null,
            user_id: props.loggedInUser.id,
        });

        clearInterval(orderTimer);
        fetchedOrder.value = response.data.session;
        Object.assign(orderTotals, response.data.totals || {});
        printOrderReceipt(response.data.session, response.data.totals, response.data.balance);
        setTimeout(() => { refreshData(); }, 1500);
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Failed to confirm order.";
    } finally {
        loadingClose.value = false;
    }
};

const printBarcodeForOrder = async () => {
    // Reuse active created session if available, otherwise create one.
    let session = createdSession.value;

    if (!session || session.status === "closed") {
        session = await createOrder();
        if (!session) {
            return;
        }
    }

    const printUrl = route("play-area.barcode.print", session.id);
    window.open(printUrl, "_blank");
};

const startLiveTimer = () => {
    clearInterval(orderTimer);
    orderTimer = setInterval(() => {
        if (!fetchedOrder.value || fetchedOrder.value.status === "closed" || !fetchedOrder.value.package_id) {
            clearInterval(orderTimer);
            return;
        }
        const start = new Date(fetchedOrder.value.start_time).getTime();
        const now = Date.now();
        const elapsed = Math.max(0, Math.floor((now - start) / 60000));
        elapsedDisplay.value = elapsed;

        const extraMinutes = Math.max(0, elapsed - Number(fetchedOrder.value.base_time_minutes || 0));
        const units = extraMinutes > 0
            ? Math.ceil(extraMinutes / Math.max(1, Number(fetchedOrder.value.extra_charge_per_minutes || 1)))
            : 0;
        orderTotals.extra_minutes = extraMinutes;
        orderTotals.extra_amount = units * Number(fetchedOrder.value.extra_charge || 0);
        orderTotals.final_total = Number(fetchedOrder.value.package_total || 0)
            + Number(orderTotals.products_total || 0)
            + Number(orderTotals.extra_amount || 0);
    }, 1000);
};

const fetchOrder = async (barcodeValue = null) => {
    const barcodeToFetch = (barcodeValue || form.barcode || "").trim();

    if (!barcodeToFetch) {
        isAlertModalOpen.value = true;
        message.value = "Please enter or scan a barcode.";
        return;
    }

    loadingFetch.value = true;
    clearInterval(orderTimer);

    try {
        const response = await axios.post(route("play-area.session.fetch"), {
            barcode: barcodeToFetch,
        });

        fetchedOrder.value = response.data.session;
        Object.assign(orderTotals, response.data.totals || {});
        elapsedDisplay.value = response.data.totals?.elapsed_minutes || 0;
        checkoutCash.value = Number(response.data.totals?.final_total || 0);

        // Keep scanned barcode in the field for visibility
        form.barcode = barcodeToFetch;

        if (fetchedOrder.value.package_id) {
            startLiveTimer();
        }
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Order not found. Check the barcode.";
    } finally {
        loadingFetch.value = false;
    }
};

const printOrderReceipt = (sessionData, totalsData, balance) => {
    const productRows = (sessionData.items || [])
        .map((item) => `
            <tr>
                <td>${item.product?.name || "Item"}</td>
                <td style="text-align:center;">${Number(item.quantity || 0)}</td>
                <td style="text-align:right;">${Number(item.total_price || 0).toFixed(2)} LKR</td>
            </tr>
        `)
        .join("");

    const html = `
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Receipt</title>
      <style>
          @media print {
              body {
                  margin: 0;
                  padding: 0 5mm 0 0;
                  -webkit-print-color-adjust: exact;
              }
          }
          body {
              background-color: #ffffff;
              font-size: 12px;
              font-family: 'Arial', sans-serif;
              margin: 0;
              padding: 10px 5mm 10mm 7mm;
              color: #000;
          }
          .header {
              text-align: center;
              margin-bottom: 16px;
          }
          .header h1 {
              font-size: 20px;
              font-weight: bold;
              margin: 0;
          }
          .header p {
              font-size: 12px;
              margin: 4px 0;
          }
          .section {
              margin-bottom: 16px;
              padding-top: 8px;
              border-top: 1px solid #000;
          }
          .info-row {
              display: flex;
              justify-content: space-between;
              font-size: 14px;
              margin-top: 8px;
          }
          .info-row p {
              margin: 0;
              font-weight: bold;
          }
          .info-row small {
              font-weight: normal;
          }
          table {
              width: 100%;
              font-size: 12px;
              border-collapse: collapse;
              margin-top: 8px;
          }
          table th, table td {
              padding: 6px 8px;
          }
          table th {
              text-align: left;
          }
          table td {
              text-align: right;
          }
          table td:first-child {
              text-align: left;
          }
          .totals {
              border-top: 1px solid #000;
              padding-top: 8px;
              font-size: 12px;
          }
          .totals div {
              display: flex;
              justify-content: space-between;
              margin-bottom: 8px;
          }
          .totals .grand-total {
              font-size: 14px;
              font-weight: bold;
          }
          .footer {
              text-align: center;
              font-size: 10px;
              margin-top: 16px;
          }
          .footer p {
              margin: 6px 0;
          }
      </style>
  </head>
  <body>
      <div class="receipt-container">
          <div class="header">
              <img src="/images/billlogo.png" style="width: 230px; height: 100px;" />
              <h1>Order Receipt</h1>
              <p>JAAN Network POS</p>
          </div>

          <div class="section">
              <div class="info-row">
                  <div>
                      <p>Date:</p>
                      <small>${new Date().toLocaleDateString()}</small>
                  </div>
                  <div>
                      <p>Order No:</p>
                      <small>${sessionData.barcode}</small>
                  </div>
              </div>
              <div class="info-row">
                  <div>
                      <p>Customer:</p>
                      <small>${sessionData.customer_name || "Walk-in"}</small>
                  </div>
                  <div>
                      <p>Cashier:</p>
                      <small>${props.loggedInUser?.name || "-"}</small>
                  </div>
              </div>
              ${sessionData.package ? `
              <div class="info-row">
                  <div>
                      <p>Service:</p>
                      <small>${sessionData.package.name}</small>
                  </div>
                  <div>
                      <p>Payment:</p>
                      <small>${String(checkoutPaymentMethod.value || "cash").toUpperCase()}</small>
                  </div>
              </div>` : ""}
          </div>

          <div class="section">
              <table>
                  <colgroup>
                      <col style="width:60%;">
                      <col style="width:15%;">
                      <col style="width:25%;">
                  </colgroup>
                  <thead>
                      <tr>
                          <th>Items</th>
                          <th style="text-align:center;">Qty</th>
                          <th style="text-align:right;">Price</th>
                      </tr>
                  </thead>
                  <tbody style="font-size:11px;">
                      ${productRows || '<tr><td colspan="3" style="text-align:center;">No products</td></tr>'}
                  </tbody>
              </table>
          </div>

          <div class="totals">
              ${sessionData.package ? `
              <div>
                  <span>Package Total</span>
                  <span>${(Number(totalsData.package_total) || 0).toFixed(2)} LKR</span>
              </div>` : ""}
              <div>
                  <span>Products Total</span>
                  <span>${(Number(totalsData.products_total) || 0).toFixed(2)} LKR</span>
              </div>
              ${sessionData.package ? `
              <div>
                  <span>Overtime Charge</span>
                  <span>${(Number(totalsData.extra_amount) || 0).toFixed(2)} LKR</span>
              </div>` : ""}
              <div class="grand-total">
                  <span>Total</span>
                  <span>${(Number(totalsData.final_total) || 0).toFixed(2)} LKR</span>
              </div>
              <div>
                  <span>Cash</span>
                  <span>${(Number(checkoutCash.value) || 0).toFixed(2)} LKR</span>
              </div>
              <div style="font-weight: bold;">
                  <span>Balance</span>
                  <span>${(Number(balance) || 0).toFixed(2)} LKR</span>
              </div>
          </div>

          <div class="footer">
              <p>THANK YOU COME AGAIN</p>
              <p style="font-weight: bold;">Powered by JAAN Network Ltd.</p>
              <p>${new Date().toLocaleTimeString()}</p>
          </div>
      </div>
      <script>
          window.onload = function () {
              window.print();
          };
      <\/script>
  </body>
  </html>
  `;

    const w = window.open("", "_blank", "width=800,height=900");
    w.document.write(html);
    w.document.close();
};

const confirmBill = async () => {
    if (!fetchedOrder.value) {
        isAlertModalOpen.value = true;
        message.value = "Please fetch an order first.";
        return;
    }
    loadingClose.value = true;
    try {
        const response = await axios.post(route("play-area.session.close"), {
            barcode: fetchedOrder.value.barcode,
            payment_method: checkoutPaymentMethod.value,
            cash: checkoutCash.value,
            employee_id: employee_id.value || null,
            user_id: props.loggedInUser.id,
        });
        clearInterval(orderTimer);
        fetchedOrder.value = response.data.session;
        Object.assign(orderTotals, response.data.totals || {});
        printOrderReceipt(response.data.session, response.data.totals, response.data.balance);
        setTimeout(() => { refreshData(); }, 2000);
    } catch (err) {
        isAlertModalOpen.value = true;
        message.value = err.response?.data?.message || "Failed to confirm order.";
    } finally {
        loadingClose.value = false;
    }
};

const subtotal = computed(() => {
    return products.value
        .reduce(
            (total, item) => total + parseFloat(item.selling_price) * item.quantity,
            0
        )
        .toFixed(2); // Ensures two decimal places
});

const totalDiscount = computed(() => {
    const productDiscount = products.value.reduce((total, item) => {
        // Check if item has a discount
        if (item.discount && item.discount > 0 && item.apply_discount == true) {
            const discountAmount =
                (parseFloat(item.selling_price) - parseFloat(item.discounted_price)) *
                item.quantity;
            return total + discountAmount;
        }
        return total; // If no discount, return total as-is
    }, 0); // Ensures two decimal places

    const couponDiscount = appliedCoupon.value
        ? Number(appliedCoupon.value.discount)
        : 0;

    return (productDiscount + couponDiscount).toFixed(2);
});

const validateCustomDiscount = () => {
    if (!custom_discount.value || isNaN(custom_discount.value)) {
        custom_discount.value = 0; // Set default to 0 if the field is empty or invalid
    }
};

const total = computed(() => {
    const subtotalValue = parseFloat(subtotal.value) || 0;
    const discountValue = parseFloat(totalDiscount.value) || 0;
    const customDiscount = parseFloat(custom_discount.value) || 0;

    let customValue = 0;

    if (custom_discount_type.value === 'percent') {
        customValue = (subtotalValue * customDiscount) / 100;
    } else if (custom_discount_type.value === 'fixed') {
        customValue = customDiscount;
    }

    return (subtotalValue - discountValue - customValue).toFixed(2);
});

const balance = computed(() => {
    if (cash.value == null || cash.value === 0) {
        return 0; // If cash.value is null or 0, return 0
    }
    return (parseFloat(cash.value) - parseFloat(total.value)).toFixed(2);
});

// ── Package & Unified Billing Computed ──
const selectedPackagePreview = computed(() =>
    props.packages?.find((p) => Number(p.id) === Number(selectedPackageId.value)) || null
);

const filteredPackages = computed(() => {
    const search = packageSearch.value.trim().toLowerCase();

    return (props.packages || []).filter((pkg) => {
        const duration = Number(pkg.base_time_minutes || 0);
        const price = Number(pkg.base_price || 0);

        const matchesSearch = !search
            || String(pkg.name || "").toLowerCase().includes(search)
            || String(pkg.base_time_minutes || "").includes(search)
            || String(pkg.base_price || "").includes(search);

        const matchesDuration = packageDurationFilter.value === "all"
            || (packageDurationFilter.value === "short" && duration <= 60)
            || (packageDurationFilter.value === "medium" && duration > 60 && duration <= 120)
            || (packageDurationFilter.value === "long" && duration > 120);

        const matchesPrice = packagePriceFilter.value === "all"
            || (packagePriceFilter.value === "budget" && price <= 1000)
            || (packagePriceFilter.value === "standard" && price > 1000 && price <= 3000)
            || (packagePriceFilter.value === "premium" && price > 3000);

        return matchesSearch && matchesDuration && matchesPrice;
    });
});

const previewPackageTotal = computed(() => {
    if (!selectedPackagePreview.value) return 0;
    const base = Number(selectedPackagePreview.value.base_price || 0);
    return base;
});

const estimatedTotal = computed(() => {
    return (parseFloat(subtotal.value) + previewPackageTotal.value - parseFloat(totalDiscount.value)).toFixed(2);
});

// Check for product or handle errors
const form = useForm({
    employee_id: "",
    barcode: "", // Form field for barcode
});

const couponForm = useForm({
    code: "",
});

// Temporary barcode storage during scanning
let barcode = "";
let timeout; // Timeout to detect the end of the scan

const submitCoupon = async () => {
    try {
        const response = await axios.post(route("pos.getCoupon"), {
            code: couponForm.code, // Send the coupon field
        });

        const { coupon: fetchedCoupon, error: fetchedError } = response.data;

        if (fetchedCoupon) {
            appliedCoupon.value = fetchedCoupon;
            products.value.forEach((product) => {
                product.apply_discount = false;
            });
        } else {
            isAlertModalOpen.value = true;
            message.value = fetchedError;
            error.value = fetchedError;
        }
    } catch (err) {
        // console.error(error);
        if (err.response.status === 422) {
            isAlertModalOpen.value = true;
            message.value = err.response.data.message;
        }
    }
};

// Automatically submit the barcode to the backend
const submitBarcode = async () => {
    const scanned = (form.barcode || "").trim();

    // Order barcode scan should fetch full order for checkout
    if (/^PA[A-Z0-9]+$/i.test(scanned)) {
        await fetchOrder(scanned);
        return;
    }

    try {
        // Send POST request to the backend
        const response = await axios.post(route("pos.getProduct"), {
            barcode: scanned, // Send the barcode field
        });

        // Extract the response data
        const { product: fetchedProduct, error: fetchedError } = response.data;

        if (fetchedProduct) {
            if (fetchedProduct.stock_quantity < 1) {
                isAlertModalOpen.value = true;
                message.value = "Product is out of stock";
                return;
            }
            // Check if the product already exists in the products array
            const existingProduct = products.value.find(
                (item) => item.id === fetchedProduct.id
            );

            if (existingProduct) {
                // If it exists, increment the quantity
                existingProduct.quantity += 1;
            } else {
                // If it doesn't exist, add it to the products array with quantity 1
                products.value.push({
                    ...fetchedProduct,
                    quantity: 1,
                    apply_discount: false, // Add the new attribute
                });
            }

            product.value = fetchedProduct; // Update product state for individual display
            error.value = null; // Clear any previous errors
            console.log(
                "Product fetched successfully and added to cart:",
                fetchedProduct
            );

            form.barcode = "";
        } else {
            isAlertModalOpen.value = true;
            message.value = fetchedError;
            error.value = fetchedError; // Set the error message
            console.error("Error:", fetchedError);
        }
    } catch (err) {
        if (err.response.status === 422) {
            isAlertModalOpen.value = true;
            message.value = err.response.data.message;
        }

        console.error("An error occurred:", err.response?.data || err.message);
        error.value = "An unexpected error occurred. Please try again.";
    }
};

// Handle input from the barcode scanner
const handleScannerInput = (event) => {
    // Only capture scanner input when not focused on an input field (except the product barcode field)
    const tag = document.activeElement?.tagName?.toLowerCase();
    if (tag === "input" || tag === "textarea" || tag === "select") return;

    clearTimeout(timeout);
    if (event.key === "Enter") {
        // Barcode scanning completed
        form.barcode = barcode; // Set the scanned barcode into the form
        submitBarcode(); // Automatically submit the barcode
        barcode = ""; // Reset the barcode for the next scan
    } else {
        // Append the pressed key to the barcode
        barcode += event.key;
    }

    // Timeout to reset the barcode if scanning is interrupted
    timeout = setTimeout(() => {
        barcode = "";
    }, 100); // Adjust timeout based on scanner speed
};

// Attach the keypress event listener when the component is mounted
onMounted(() => {
    document.addEventListener("keypress", handleScannerInput);
    console.log(props.products);

onBeforeUnmount(() => {
    document.removeEventListener("keypress", handleScannerInput);
    clearInterval(orderTimer);
});
});

const applyDiscount = (id) => {
    products.value.forEach((product) => {
        if (product.id === id) {
            product.apply_discount = true;
        }
    });
};

const removeDiscount = (id) => {
    products.value.forEach((product) => {
        if (product.id === id) {
            product.apply_discount = false;
        }
    });
};

const handleSelectedProducts = (selectedProducts) => {
    selectedProducts.forEach((fetchedProduct) => {
        const existingProduct = products.value.find(
            (item) => item.id === fetchedProduct.id
        );

        if (existingProduct) {
            // If the product exists, increment its quantity
            existingProduct.quantity += 1;
        } else {
            // If the product doesn't exist, add it with a default quantity
            products.value.push({
                ...fetchedProduct,
                quantity: 1,
                apply_discount: false, // Default additional attribute
            });
        }
    });
};

// const searchTerm = ref(form.barcode);

// // Computed property for filtered product results
// const searchResults = computed(() => {
//   if (searchTerm.value === "") {
//     return [];
//   }

//   let matches = 0;

//   return props.products.filter((product) => {
//     if (
//       product.name.toLowerCase().includes(searchTerm.value.toLowerCase()) &&
//       matches < 10
//     ) {
//       matches++;
//       return product;
//     }
//   });
// });

// // Watch for changes in the form barcode field and update the search term
// watch(
//   () => form.barcode,
//   (newValue) => {
//     searchTerm.value = newValue;
//   }
// );

// // Method to select a product (or barcode)
// const selectProduct = (productName) => {
//   form.barcode = productName; // Set the selected product name to the barcode field
//   searchTerm.value = ""; // Clear the search term after selection
// };
</script>

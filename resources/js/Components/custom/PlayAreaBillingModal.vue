<template>
  <div v-if="isVisible" :class="wrapperClass" @click.self="handleBackdropClick">
    <div :class="panelClass">
      <div v-if="!inline" :class="headerClass">
        <h2 class="text-2xl font-bold">Play Area Billing</h2>
        <button class="text-2xl" @click="close">
          <i class="ri-close-line"></i>
        </button>
      </div>

      <div class="p-6 space-y-6">
        <div class="grid md:grid-cols-2 gap-4">
          <div class="p-10 space-y-6 bg-white shadow-lg rounded-3xl">
            <p class="text-5xl font-bold text-black">Customer Details</p>
            <input v-model="createForm.customer.name" type="text" class="input-white" placeholder="Enter Customer Name" />
            <input v-model="createForm.customer.contactNumber" type="text" class="input-white" placeholder="Enter Customer Contact Number" />
            <input v-model="createForm.customer.email" type="email" class="input-white" placeholder="Enter Customer Email" />
            <input v-model.number="createForm.customer.age" type="number" min="0" class="input-white" placeholder="Enter Customer Age" />

            <select v-model="createForm.employee_id" class="input-white">
              <option value="">Select an Employee</option>
              <option v-for="employee in allemployee" :key="employee.id" :value="employee.id">
                {{ employee.name }}
              </option>
            </select>

            <select v-model="createForm.package_id" class="input-white">
              <option value="">Select Service Package</option>
              <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                {{ pkg.name }} - {{ pkg.base_time_minutes }} mins - {{ Number(pkg.base_price).toFixed(2) }} LKR
              </option>
            </select>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
              <button class="btn-primary md:col-span-2 w-full" :disabled="loadingCreate" @click="createSession">
                {{ loadingCreate ? "Creating..." : "Create Session + Generate Barcode" }}
              </button>
              <button class="btn-blue w-full" :disabled="loadingCreate" @click="printCreatedBarcode">
                {{ loadingCreate ? "Please Wait..." : "Print Barcode" }}
              </button>
            </div>

            <div v-if="createdSession" class="rounded-xl bg-green-100 border border-green-300 p-3 text-black space-y-2">
              <p class="font-bold text-green-700">Session Created Successfully</p>
              <p><span class="font-semibold">Barcode:</span> {{ createdSession.barcode }}</p>
              <p><span class="font-semibold">Customer:</span> {{ createdSession.customer_name || '-' }}</p>
              <p><span class="font-semibold">Package:</span> {{ createdSession.package?.name || '-' }}</p>
              <div class="flex gap-2">
                <button class="btn-blue" @click="printCreatedBarcode">Print Barcode</button>
                <button class="btn-gray" @click="copyBarcode(createdSession.barcode)">Copy</button>
                <button class="btn-black" @click="useCreatedBarcode">Use In Checkout</button>
              </div>
            </div>
          </div>

          <div class="p-8 rounded-3xl bg-white shadow-lg">
            <div class="flex items-center justify-between w-full mb-4">
              <h2 class="md:text-5xl text-4xl font-bold text-black">Billing Details</h2>
              <span class="flex items-center cursor-pointer" @click="isSelectModalOpen = true">
                <p class="text-xl text-blue-600 font-bold">User Manual</p>
                <img src="/images/selectpsoduct.svg" class="w-6 h-6 ml-2" />
              </span>
            </div>

            <div class="flex items-end justify-between w-full my-5 border-2 border-black rounded-2xl overflow-hidden">
              <div class="flex items-center justify-center w-3/4">
                <input v-model="productBarcode" type="text" placeholder="Enter product barcode" class="w-full h-16 px-4 focus:outline-none" />
              </div>
              <div class="flex items-end justify-end w-1/4">
                <button @click="addProductByBarcode" class="w-full h-16 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600">
                  Enter
                </button>
              </div>
            </div>

            <div class="w-full text-center mb-3">
              <p v-if="createProducts.length === 0" class="text-2xl text-red-500">No Products to show</p>
            </div>

            <div v-if="createProducts.length" class="space-y-2 max-h-56 overflow-y-auto pr-1">
              <div v-for="item in createProducts" :key="item.id" class="flex items-center justify-between border border-gray-300 rounded-lg p-2">
                <div>
                  <p class="font-semibold text-black">{{ item.name }}</p>
                  <p class="text-sm text-gray-600">{{ Number(item.selling_price).toFixed(2) }} LKR</p>
                </div>
                <div class="flex items-center gap-2">
                  <button class="qty-btn" @click="decreaseQty(item.id)">-</button>
                  <input v-model.number="item.quantity" type="number" min="1" class="w-16 text-center border rounded px-1 py-1" />
                  <button class="qty-btn" @click="increaseQty(item.id)">+</button>
                  <button class="text-red-600 text-xl" @click="removeCreateProduct(item.id)"><i class="ri-close-line"></i></button>
                </div>
              </div>
            </div>

            <div class="w-full pt-5 space-y-2 border-t border-black mt-5">
              <div class="flex items-center justify-between w-full"><p class="text-xl">Service Total</p><p class="text-xl">{{ previewPackageTotal.toFixed(2) }} LKR</p></div>
              <div class="flex items-center justify-between w-full"><p class="text-xl">Products Total</p><p class="text-xl">{{ previewProductsTotal.toFixed(2) }} LKR</p></div>
              <div class="flex items-center justify-between w-full pt-2 border-t border-black"><p class="text-3xl">Start Bill Total</p><p class="text-3xl">{{ (previewPackageTotal + previewProductsTotal).toFixed(2) }} LKR</p></div>
            </div>
          </div>
        </div>

        <div class="p-8 rounded-3xl bg-white shadow-lg space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-4xl font-bold text-black">Session Checkout</h3>
            <p class="text-lg text-gray-700">Scan barcode to fetch full order</p>
          </div>

          <div class="flex items-end justify-between w-full my-2 border-2 border-black rounded-2xl overflow-hidden">
            <div class="flex items-center justify-center w-3/4">
              <input v-model="fetchBarcode" type="text" placeholder="Scan customer barcode" class="w-full h-14 px-4 focus:outline-none" />
            </div>
            <div class="flex items-end justify-end w-1/4">
              <button @click="fetchSession" class="w-full h-14 text-xl font-bold tracking-wider text-white uppercase bg-blue-600">
                {{ loadingFetch ? "..." : "Fetch" }}
              </button>
            </div>
          </div>

          <div v-if="fetchedSession" class="rounded-2xl bg-white border border-gray-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500">Session</p>
                <p class="text-lg font-bold text-black">{{ fetchedSession.barcode }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-500">Service</p>
                <p class="text-lg font-bold text-black">{{ fetchedSession.package?.name || '-' }}</p>
              </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
              <p class="text-sm text-blue-700 font-semibold">Time Count</p>
              <p class="text-4xl font-bold text-blue-900">{{ elapsedDisplay }} mins</p>
              <div class="mt-2 text-sm text-blue-900 grid grid-cols-2 gap-2">
                <p>Extra Time: <span class="font-semibold">{{ totals.extra_minutes }} mins</span></p>
                <p class="text-right">Extra Charge: <span class="font-semibold">{{ Number(totals.extra_amount || 0).toFixed(2) }} LKR</span></p>
              </div>
            </div>

            <div class="border-t pt-3 text-lg space-y-2">
              <div class="flex justify-between"><span>Package Total</span><span>{{ Number(totals.package_total || 0).toFixed(2) }} LKR</span></div>
              <div class="flex justify-between"><span>Products Total</span><span>{{ Number(totals.products_total || 0).toFixed(2) }} LKR</span></div>
              <div class="flex justify-between"><span>Overtime Charge</span><span>{{ Number(totals.extra_amount || 0).toFixed(2) }} LKR</span></div>
              <div class="flex justify-between text-3xl font-bold border-t pt-2"><span>Final Total</span><span>{{ Number(totals.final_total || 0).toFixed(2) }} LKR</span></div>
            </div>

            <div class="grid md:grid-cols-4 gap-2 items-center pt-2">
              <select v-model="checkoutForm.payment_method" class="input">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
              </select>
              <input v-model.number="checkoutForm.cash" type="number" min="0" class="input" placeholder="Cash Amount" />
              <button class="btn-blue" @click="printFetchedBarcode" :disabled="!fetchedSession">
                Print Barcode
              </button>
              <button class="btn-black" :disabled="loadingClose" @click="closeSession">
                {{ loadingClose ? "Closing..." : "Confirm Bill + Print" }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <SelectProductModel
        v-model:open="isSelectModalOpen"
        :allcategories="allcategories"
        :colors="colors"
        :sizes="sizes"
        @selected-products="handleSelectedProducts"
      />

      <div v-if="message" class="mx-6 mb-6 p-3 rounded" :class="messageType === 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">
        {{ message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from "axios";
import { computed, onBeforeUnmount, reactive, ref } from "vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";

const props = defineProps({
  open: { type: Boolean, default: false },
  inline: { type: Boolean, default: false },
  packages: { type: Array, default: () => [] },
  allemployee: { type: Array, default: () => [] },
  allcategories: { type: Array, default: () => [] },
  colors: { type: Array, default: () => [] },
  sizes: { type: Array, default: () => [] },
  loggedInUser: { type: Object, required: true },
});

const emit = defineEmits(["update:open"]);

const isVisible = computed(() => props.inline || props.open);
const wrapperClass = computed(() =>
  props.inline
    ? "w-full"
    : "fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
);
const panelClass = computed(() =>
  props.inline
    ? "w-full bg-transparent rounded-2xl"
    : "w-full max-w-6xl max-h-[95vh] overflow-y-auto bg-white rounded-2xl shadow-2xl"
);
const headerClass = computed(() =>
  props.inline
    ? "bg-white text-black px-6 py-4 flex items-center justify-between rounded-t-2xl"
    : "sticky top-0 z-10 bg-white text-black px-6 py-4 flex items-center justify-between border-b"
);

const createForm = reactive({
  package_id: "",
  employee_id: "",
  customer: {
    name: "",
    contactNumber: "",
    email: "",
    age: null,
  },
  user_id: props.loggedInUser?.id,
});

const checkoutForm = reactive({
  payment_method: "cash",
  cash: 0,
});

const createProducts = ref([]);
const productBarcode = ref("");
const fetchBarcode = ref("");
const createdSession = ref(null);
const fetchedSession = ref(null);
const totals = reactive({
  package_total: 0,
  extra_amount: 0,
  products_total: 0,
  final_total: 0,
  extra_minutes: 0,
  elapsed_minutes: 0,
  start_time: "",
  current_time: "",
  expected_end_time: "",
});

const loadingCreate = ref(false);
const loadingFetch = ref(false);
const loadingClose = ref(false);
const message = ref("");
const messageType = ref("success");
const isSelectModalOpen = ref(false);
const nowDisplay = ref("-");
const elapsedDisplay = ref(0);
let timer = null;

const selectedPackage = computed(() => props.packages.find((p) => Number(p.id) === Number(createForm.package_id)));

const previewPackageTotal = computed(() => {
  if (!selectedPackage.value) {
    return 0;
  }

  const base = Number(selectedPackage.value.base_price || 0);
  const age = Number(createForm.customer.age || 0);
  const threshold = Number(selectedPackage.value.age_threshold || 0);
  const addPayment = age > threshold ? Number(selectedPackage.value.additional_payment || 0) : 0;

  return base + addPayment;
});

const previewProductsTotal = computed(() => {
  return createProducts.value.reduce((sum, item) => sum + Number(item.selling_price) * Number(item.quantity || 0), 0);
});

const close = () => {
  emit("update:open", false);
};

const handleBackdropClick = () => {
  if (!props.inline) {
    close();
  }
};

const showMessage = (text, type = "success") => {
  message.value = text;
  messageType.value = type;
  setTimeout(() => {
    message.value = "";
  }, 4000);
};

const addProductByBarcode = async () => {
  if (!productBarcode.value) {
    return;
  }

  try {
    const response = await axios.post(route("pos.getProduct"), {
      barcode: productBarcode.value,
    });

    const product = response.data.product;

    if (!product) {
      showMessage("Product not found for this barcode.", "error");
      return;
    }

    const existing = createProducts.value.find((item) => item.id === product.id);
    if (existing) {
      existing.quantity += 1;
    } else {
      createProducts.value.push({ ...product, quantity: 1 });
    }

    productBarcode.value = "";
  } catch (error) {
    showMessage(error.response?.data?.message || "Failed to add product.", "error");
  }
};

const increaseQty = (id) => {
  const item = createProducts.value.find((x) => x.id === id);
  if (item) {
    item.quantity += 1;
  }
};

const decreaseQty = (id) => {
  const item = createProducts.value.find((x) => x.id === id);
  if (item && item.quantity > 1) {
    item.quantity -= 1;
  }
};

const removeCreateProduct = (id) => {
  createProducts.value = createProducts.value.filter((x) => x.id !== id);
};

const handleSelectedProducts = (selectedProducts) => {
  selectedProducts.forEach((product) => {
    const existing = createProducts.value.find((item) => item.id === product.id);
    if (existing) {
      existing.quantity += 1;
      return;
    }

    createProducts.value.push({
      ...product,
      quantity: 1,
    });
  });
};

const createSession = async (shouldPrint = false) => {
  if (!createForm.package_id) {
    showMessage("Please select a package.", "error");
    return null;
  }

  loadingCreate.value = true;

  try {
    const response = await axios.post(route("play-area.session.create"), {
      package_id: createForm.package_id,
      employee_id: createForm.employee_id || null,
      user_id: props.loggedInUser.id,
      customer: createForm.customer,
      products: createProducts.value.map((item) => ({ id: item.id, quantity: item.quantity })),
    });

    createdSession.value = response.data.session;
    showMessage("Session created successfully.");
    fetchBarcode.value = createdSession.value.barcode;

    if (shouldPrint) {
      const printUrl = response.data.barcode_print_url || route("play-area.barcode.print", createdSession.value.id);
      window.open(printUrl, "_blank");
    }

    return createdSession.value;
  } catch (error) {
    showMessage(error.response?.data?.message || "Failed to create session.", "error");
    return null;
  } finally {
    loadingCreate.value = false;
  }
};

const printCreatedBarcode = async () => {
  if (!createdSession.value) {
    const session = await createSession(true);
    if (!session) {
      return;
    }
    return;
  }

  window.open(route("play-area.barcode.print", createdSession.value.id), "_blank");
};

const printFetchedBarcode = () => {
  if (!fetchedSession.value) {
    return;
  }

  window.open(route("play-area.barcode.print", fetchedSession.value.id), "_blank");
};

const useCreatedBarcode = async () => {
  if (!createdSession.value) {
    return;
  }

  fetchBarcode.value = createdSession.value.barcode;
  await fetchSession();
};

const copyBarcode = async (barcode) => {
  try {
    await navigator.clipboard.writeText(barcode);
    showMessage("Barcode copied.");
  } catch (e) {
    showMessage("Unable to copy barcode.", "error");
  }
};

const syncTimerFromTotals = () => {
  nowDisplay.value = totals.current_time || "-";
  elapsedDisplay.value = totals.elapsed_minutes || 0;
};

const startLocalTimer = () => {
  clearInterval(timer);
  timer = setInterval(() => {
    if (!fetchedSession.value || fetchedSession.value.status === "closed") {
      clearInterval(timer);
      return;
    }

    const start = new Date(fetchedSession.value.start_time).getTime();
    const now = Date.now();
    const elapsed = Math.max(0, Math.floor((now - start) / 60000));

    elapsedDisplay.value = elapsed;
    nowDisplay.value = new Date().toLocaleString();

    const extraMinutes = Math.max(0, elapsed - Number(fetchedSession.value.base_time_minutes || 0));
    const perMinutes = Number(fetchedSession.value.extra_charge_per_minutes || 0);
    const charge = Number(fetchedSession.value.extra_charge || 0);
    const units = (extraMinutes > 0 && perMinutes > 0 && charge > 0)
      ? Math.ceil(extraMinutes / perMinutes)
      : 0;

    totals.extra_minutes = extraMinutes;
    totals.extra_amount = units * charge;
    totals.final_total = Number(fetchedSession.value.package_total || 0) + Number(totals.products_total || 0) + Number(totals.extra_amount || 0);
  }, 1000);
};

const fetchSession = async () => {
  if (!fetchBarcode.value) {
    showMessage("Please scan barcode.", "error");
    return;
  }

  loadingFetch.value = true;
  clearInterval(timer);

  try {
    const response = await axios.post(route("play-area.session.fetch"), {
      barcode: fetchBarcode.value,
    });

    fetchedSession.value = response.data.session;
    Object.assign(totals, response.data.totals || {});
    checkoutForm.cash = Number(response.data.totals?.final_total || 0);
    syncTimerFromTotals();
    startLocalTimer();
  } catch (error) {
    showMessage(error.response?.data?.message || "Failed to fetch session.", "error");
  } finally {
    loadingFetch.value = false;
  }
};

const printFinalReceipt = (sessionData, totalsData, balance) => {
  const rows = (sessionData.items || [])
    .map((item) => `<tr><td>${item.product?.name || "-"}</td><td>${item.quantity}</td><td>${Number(item.total_price).toFixed(2)} LKR</td></tr>`)
    .join("");

  const html = `
    <html>
      <head>
        <title>Play Area Receipt</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 16px; }
          table { width: 100%; border-collapse: collapse; margin-top: 10px; }
          th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
          h2, h3 { margin: 4px 0; }
        </style>
      </head>
      <body>
        <h2>Play Area Receipt</h2>
        <p><strong>Barcode:</strong> ${sessionData.barcode}</p>
        <p><strong>Package:</strong> ${sessionData.package?.name || "-"}</p>
        <p><strong>Customer:</strong> ${sessionData.customer_name || "-"}</p>
        <p><strong>Start Time:</strong> ${totalsData.start_time || "-"}</p>
        <p><strong>End Time:</strong> ${new Date().toLocaleString()}</p>

        <h3>Products</h3>
        <table>
          <thead><tr><th>Item</th><th>Qty</th><th>Amount</th></tr></thead>
          <tbody>${rows || '<tr><td colspan="3">No products</td></tr>'}</tbody>
        </table>

        <h3>Summary</h3>
        <p>Package Total: ${Number(totalsData.package_total || 0).toFixed(2)} LKR</p>
        <p>Extra Charge: ${Number(totalsData.extra_amount || 0).toFixed(2)} LKR</p>
        <p>Products Total: ${Number(totalsData.products_total || 0).toFixed(2)} LKR</p>
        <p><strong>Final Total: ${Number(totalsData.final_total || 0).toFixed(2)} LKR</strong></p>
        <p>Cash: ${Number(checkoutForm.cash || 0).toFixed(2)} LKR</p>
        <p>Balance: ${Number(balance || 0).toFixed(2)} LKR</p>
      </body>
    </html>
  `;

  const printWindow = window.open("", "_blank", "width=800,height=900");
  printWindow.document.write(html);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
};

const closeSession = async () => {
  if (!fetchedSession.value) {
    showMessage("Fetch a session first.", "error");
    return;
  }

  loadingClose.value = true;

  try {
    const response = await axios.post(route("play-area.session.close"), {
      barcode: fetchedSession.value.barcode,
      payment_method: checkoutForm.payment_method,
      cash: checkoutForm.cash,
      employee_id: createForm.employee_id || null,
      user_id: props.loggedInUser.id,
    });

    fetchedSession.value = response.data.session;
    Object.assign(totals, response.data.totals || {});

    showMessage("Session closed successfully. Final receipt is ready.");
    printFinalReceipt(response.data.session, response.data.totals, response.data.balance);
  } catch (error) {
    showMessage(error.response?.data?.message || "Failed to close session.", "error");
  } finally {
    loadingClose.value = false;
  }
};

onBeforeUnmount(() => {
  clearInterval(timer);
});
</script>

<style scoped>
.input {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
}

.input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.checkout-label {
  display: block;
  margin-bottom: 0.35rem;
  font-size: 0.9rem;
  font-weight: 700;
  color: #374151;
}

.input-white {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
  background: #fff;
  color: #000;
}

.input-white:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

.btn-blue {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  background: #2563eb;
  color: #fff;
  font-weight: 600;
}

.btn-blue:hover {
  background: #1d4ed8;
}

.btn-black {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  background: #111827;
  color: #fff;
  font-weight: 600;
}

.btn-black:hover {
  background: #1f2937;
}

.btn-black:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-gray {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  background: #e5e7eb;
  color: #111827;
  font-weight: 600;
}

.btn-gray:hover {
  background: #d1d5db;
}

.qty-btn {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 0.25rem;
  background: #111827;
  color: #fff;
}
</style>

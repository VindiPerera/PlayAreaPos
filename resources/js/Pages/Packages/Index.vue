<template>
  <Head title="Packages" />
  <Banner />
  <div
    class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16"
  >
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-8">
      <!-- Header Section -->
      <div class="flex items-center justify-between">
        <div>
          <p class="text-3xl italic font-bold text-black">
            <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{
              packages.length
            }}</span>
            <span class="text-xl">/ Total Packages</span>
          </p>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex md:flex-row flex-col w-full gap-4 items-center justify-between">
        <div class="flex items-center w-full h-16 space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Packages
          </p>
        </div>
        <button
          v-if="HasRole(['Admin', 'Manager'])"
          @click="openCreateModal"
          class="px-8 py-4 text-lg font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors"
        >
          <i class="pr-2 ri-add-circle-fill"></i> Create Package
        </button>
      </div>

      <!-- Success Message -->
      <transition name="fade">
        <div
          v-if="successMessage"
          class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg animate-pulse"
        >
          ✓ {{ successMessage }}
        </div>
      </transition>

      <!-- Empty State -->
      <div
        v-if="packages.length === 0"
        class="bg-white rounded-lg shadow-lg p-12 text-center"
      >
        <p class="text-gray-500 text-lg mb-4">No packages created yet</p>
        <p class="text-gray-400 mb-6">Start by creating your first package</p>
        <button
          v-if="HasRole(['Admin', 'Manager'])"
          @click="openCreateModal"
          class="inline-block bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition-colors"
        >
          Create First Package
        </button>
      </div>

      <!-- Packages Grid -->
      <transition-group name="list" v-else tag="div" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="pkg in packages"
          :key="pkg.id"
          class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow"
        >
          <!-- Card Header -->
          <div
            :class="{
              'bg-gradient-to-r from-pink-500 to-rose-500': pkg.type === 'kid',
              'bg-gradient-to-r from-blue-500 to-indigo-600': pkg.type === 'elder',
            }"
            class="p-4"
          >
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-xl font-bold text-white">{{ pkg.name }}</h3>
                <span
                  :class="{
                    'text-pink-600': pkg.type === 'kid',
                    'text-blue-600': pkg.type === 'elder',
                  }"
                  class="inline-block bg-white text-xs font-semibold px-2 py-1 rounded mt-2"
                >
                  {{ pkg.type === 'kid' ? 'Kids' : 'Elders' }}
                </span>
              </div>
              <span
                v-if="!pkg.is_active"
                class="bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded"
              >
                Inactive
              </span>
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-6 space-y-4">
            <!-- Total Package Price (Main Display) -->
            <div
              :class="{
                'border-pink-500': pkg.type === 'kid',
                'border-blue-500': pkg.type === 'elder',
              }"
              class="border-l-4 pl-4 bg-gradient-to-r from-gray-50 to-white p-4 rounded-lg"
            >
              <p class="text-gray-600 text-sm mb-1">Package Price</p>
              <p class="text-4xl font-bold text-gray-800">
                {{ calculateTotalPrice(pkg) }} LKR
              </p>
              <p class="text-gray-500 text-xs mt-2">
                for {{ pkg.base_time_minutes }} minutes
              </p>
            </div>

            <!-- Base Price -->
            <div class="bg-gray-50 rounded p-4">
              <div class="flex justify-between items-center">
                <span class="text-gray-600 text-sm">Base Price:</span>
                <span class="font-semibold">{{ parseFloat(pkg.base_price).toFixed(2) }} LKR</span>
              </div>
            </div>

            <!-- Extra Charge Info -->
            <div class="bg-gray-50 rounded p-4">
              <p class="text-gray-700 font-semibold text-sm mb-3">Extra Charge</p>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">Amount:</span>
                  <span class="font-semibold">{{ parseFloat(pkg.extra_charge).toFixed(2) }} LKR</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Per:</span>
                  <span class="font-semibold">{{ pkg.extra_charge_per_minutes }} minutes</span>
                </div>
              </div>
            </div>

            <!-- Age Threshold -->
           

            <!-- Description -->
            <div v-if="pkg.description" class="text-sm">
              <p class="text-gray-600">{{ pkg.description }}</p>
            </div>
          </div>

          <!-- Card Footer -->
          <div class="bg-gray-50 px-6 py-4 flex gap-2">
            <button
              @click="editPackage(pkg.id)"
              class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded transition-colors text-sm"
            >
              Edit
            </button>
            <button
              @click="deletePackage(pkg.id)"
              class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded transition-colors text-sm"
            >
              Delete
            </button>
          </div>
        </div>
      </transition-group>
    </div>
  </div>

  <!-- Create Package Modal -->
  <div
    v-if="isCreateModalOpen"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click.self="closeCreateModal"
  >
    <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold">Create New Package</h2>
        <button
          @click="closeCreateModal"
          class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition"
        >
          <i class="ri-close-line text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <form @submit.prevent="submitCreateForm" class="p-6 space-y-6">
        <!-- Package Name -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Package Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="createForm.name"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :class="{ 'border-red-500': createErrors.name }"
            placeholder="e.g., Basic Hour"
            required
          />
          <p v-if="createErrors.name" class="text-red-500 text-sm mt-1">
            {{ createErrors.name[0] }}
          </p>
        </div>

        <!-- Package Type -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Package Type <span class="text-red-500">*</span>
          </label>
          <select
            v-model="createForm.type"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :class="{ 'border-red-500': createErrors.type }"
            required
          >
            <option value="">Select Type</option>
            <option value="elder">Elder</option>
            <option value="kid">Kid</option>
          </select>
          <p v-if="createErrors.type" class="text-red-500 text-sm mt-1">
            {{ createErrors.type[0] }}
          </p>
        </div>

        <!-- Base Price & Time -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Base Price <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center">
              <span class="text-gray-600 mr-2">LKR</span>
              <input
                v-model="createForm.base_price"
                type="number"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': createErrors.base_price }"
                placeholder="0.00"
                step="0.01"
                min="0"
                required
              />
            </div>
            <p v-if="createErrors.base_price" class="text-red-500 text-sm mt-1">
              {{ createErrors.base_price[0] }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Base Time (Minutes) <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center">
              <input
                v-model="createForm.base_time_minutes"
                type="number"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': createErrors.base_time_minutes }"
                placeholder="60"
                min="1"
                required
              />
              <span class="text-gray-600 ml-2">mins</span>
            </div>
            <p v-if="createErrors.base_time_minutes" class="text-red-500 text-sm mt-1">
              {{ createErrors.base_time_minutes[0] }}
            </p>
          </div>
        </div>

        <!-- Extra Charge Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Extra Charge Amount <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center">
              <span class="text-gray-600 mr-2">LKR</span>
              <input
                v-model="createForm.extra_charge"
                type="number"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': createErrors.extra_charge }"
                placeholder="5.00"
                step="0.01"
                min="0"
                required
              />
            </div>
            <p v-if="createErrors.extra_charge" class="text-red-500 text-sm mt-1">
              {{ createErrors.extra_charge[0] }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Extra Charge Per (Minutes) <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center">
              <input
                v-model="createForm.extra_charge_per_minutes"
                type="number"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': createErrors.extra_charge_per_minutes }"
                placeholder="15"
                min="1"
                required
              />
              <span class="text-gray-600 ml-2">mins</span>
            </div>
            <p v-if="createErrors.extra_charge_per_minutes" class="text-red-500 text-sm mt-1">
              {{ createErrors.extra_charge_per_minutes[0] }}
            </p>
          </div>
        </div>

        <!-- Age Threshold & Additional Payment -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Age Threshold <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center">
              <input
                v-model="createForm.age_threshold"
                type="number"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="10"
                min="0"
                required
              />
              <span class="text-gray-600 ml-2">years</span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Additional Payment (Optional)
            </label>
            <div class="flex items-center">
              <span class="text-gray-600 mr-2">LKR</span>
              <input
                v-model="createForm.additional_payment"
                type="number"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="0.00"
                step="0.01"
                min="0"
              />
            </div>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Description (Optional)
          </label>
          <textarea
            v-model="createForm.description"
            rows="3"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Enter package details..."
          ></textarea>
        </div>

        <!-- Summary -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <p class="text-gray-700 font-semibold text-sm mb-2">Quick Summary</p>
          <div class="text-sm space-y-1">
            <p><span class="text-gray-600">Base:</span> <span class="font-semibold">{{ parseFloat(createForm.base_price || 0).toFixed(2) }} LKR for {{ createForm.base_time_minutes || 0 }} min</span></p>
            <p><span class="text-gray-600">Extra:</span> <span class="font-semibold">{{ parseFloat(createForm.extra_charge || 0).toFixed(2) }} LKR per {{ createForm.extra_charge_per_minutes || 15 }} min</span></p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4 border-t">
          <button
            type="submit"
            :disabled="isSubmitting"
            class="flex-1 bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isSubmitting ? "Creating..." : "Create Package" }}
          </button>
          <button
            type="button"
            @click="closeCreateModal"
            class="flex-1 bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";
import { router } from "@inertiajs/vue3";
import { ref, reactive } from "vue";

const props = defineProps({
  packages: {
    type: Array,
    default: () => [],
  },
  success: String,
});

const allPackages = ref([...props.packages]);
const successMessage = ref("");
const isCreateModalOpen = ref(false);
const isSubmitting = ref(false);
const createErrors = reactive({});

const createForm = reactive({
  name: "",
  type: "",
  base_price: "",
  base_time_minutes: "",
  extra_charge: "",
  extra_charge_per_minutes: "",
  age_threshold: "10",
  additional_payment: "",
  description: "",
});

const openCreateModal = () => {
  isCreateModalOpen.value = true;
  Object.keys(createForm).forEach((key) => {
    if (key !== "age_threshold") {
      createForm[key] = "";
    }
  });
  Object.keys(createErrors).forEach((key) => delete createErrors[key]);
};

const closeCreateModal = () => {
  isCreateModalOpen.value = false;
  Object.keys(createErrors).forEach((key) => delete createErrors[key]);
};

const submitCreateForm = () => {
  isSubmitting.value = true;
  router.post(route("packages.store"), createForm, {
    onError: (errors) => {
      Object.assign(createErrors, errors);
      isSubmitting.value = false;
    },
    onSuccess: () => {
      // Reset form and close modal
      Object.keys(createForm).forEach((key) => {
        if (key !== "age_threshold") {
          createForm[key] = "";
        } else {
          createForm[key] = "10";
        }
      });
      Object.keys(createErrors).forEach((key) => delete createErrors[key]);
      isCreateModalOpen.value = false;
      isSubmitting.value = false;

      // Show success message
      successMessage.value = "Package created successfully!";
      setTimeout(() => {
        successMessage.value = "";
      }, 4000);

      // Reload packages
      router.reload({
        only: ["packages"],
        onFinish: () => {
          // Update local packages list
          allPackages.value = props.packages;
        },
      });
    },
  });
};

const editPackage = (id) => {
  router.visit(route("packages.edit", id));
};

const deletePackage = (id) => {
  if (confirm("Are you sure you want to delete this package?")) {
    router.delete(route("packages.destroy", id), {
      onSuccess: () => {
        successMessage.value = "Package deleted successfully!";
        setTimeout(() => {
          successMessage.value = "";
        }, 4000);
      },
    });
  }
};

const calculatePrice = (pkg, durationMinutes, age) => {
  let price = parseFloat(pkg.base_price);

  if (durationMinutes > pkg.base_time_minutes) {
    const extraMinutes = durationMinutes - pkg.base_time_minutes;
    const extraChargeUnits = Math.ceil(extraMinutes / pkg.extra_charge_per_minutes);
    price += extraChargeUnits * parseFloat(pkg.extra_charge);
  }

  if (age !== null && age > pkg.age_threshold && pkg.additional_payment) {
    price += parseFloat(pkg.additional_payment);
  }

  return price;
};

const calculateTotalPrice = (pkg) => {
  let totalPrice = parseFloat(pkg.base_price);
  
  if (pkg.additional_payment) {
    totalPrice += parseFloat(pkg.additional_payment);
  }
  
  return totalPrice.toFixed(2);
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.list-move {
  transition: transform 0.3s ease;
}
</style>


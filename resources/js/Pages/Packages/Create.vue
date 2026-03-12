<template>
  <Head title="Create Package" />
  <Banner />
  <div
    class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16"
  >
    <Header />
    <div class="w-full md:w-5/6 py-12">
      <!-- Header -->
      <div class="flex items-center h-16 space-x-4 rounded-2xl mb-8">
        <button @click="goBack" class="flex items-center">
          <img src="/images/back-arrow.png" class="w-14 h-14" />
        </button>
        <p class="text-4xl font-bold tracking-wide text-black uppercase">
          Create Package
        </p>
      </div>

      <!-- Form Container -->
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl mx-auto">
        <p class="text-gray-600 mb-6">Fill in the details to create a new play package</p>

        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Package Name -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Package Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.name }"
              placeholder="e.g., Basic Hour"
              required
            />
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">
              {{ errors.name[0] }}
            </p>
          </div>

          <!-- Package Type -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Package Type <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.type"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.type }"
              required
            >
              <option value="">Select Type</option>
              <option value="elder">Elder</option>
              <option value="kid">Kid</option>
            </select>
            <p v-if="errors.type" class="text-red-500 text-sm mt-1">
              {{ errors.type[0] }}
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
                  v-model="form.base_price"
                  type="number"
                  class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.base_price }"
                  placeholder="0.00"
                  step="0.01"
                  min="0"
                  required
                />
              </div>
              <p v-if="errors.base_price" class="text-red-500 text-sm mt-1">
                {{ errors.base_price[0] }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Base Time (Minutes) <span class="text-red-500">*</span>
              </label>
              <div class="flex items-center">
                <input
                  v-model="form.base_time_minutes"
                  type="number"
                  class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.base_time_minutes }"
                  placeholder="60"
                  min="1"
                  required
                />
                <span class="text-gray-600 ml-2">mins</span>
              </div>
              <p v-if="errors.base_time_minutes" class="text-red-500 text-sm mt-1">
                {{ errors.base_time_minutes[0] }}
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
                  v-model="form.extra_charge"
                  type="number"
                  class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.extra_charge }"
                  placeholder="5.00"
                  step="0.01"
                  min="0"
                  required
                />
              </div>
              <p v-if="errors.extra_charge" class="text-red-500 text-sm mt-1">
                {{ errors.extra_charge[0] }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Extra Charge Per (Minutes) <span class="text-red-500">*</span>
              </label>
              <div class="flex items-center">
                <input
                  v-model="form.extra_charge_per_minutes"
                  type="number"
                  class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.extra_charge_per_minutes }"
                  placeholder="15"
                  min="1"
                  required
                />
                <span class="text-gray-600 ml-2">mins</span>
              </div>
              <p v-if="errors.extra_charge_per_minutes" class="text-red-500 text-sm mt-1">
                {{ errors.extra_charge_per_minutes[0] }}
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
                  v-model="form.age_threshold"
                  type="number"
                  class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="10"
                  min="0"
                  required
                />
                <span class="text-gray-600 ml-2">years</span>
              </div>
              <p class="text-gray-500 text-xs mt-1">
                Additional charge applies if age exceeds this
              </p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Additional Payment (Optional)
              </label>
              <div class="flex items-center">
                <span class="text-gray-600 mr-2">LKR</span>
                <input
                  v-model="form.additional_payment"
                  type="number"
                  class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="0.00"
                  step="0.01"
                  min="0"
                />
              </div>
              <p class="text-gray-500 text-xs mt-1">
                Applied if customer age exceeds threshold
              </p>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Description (Optional)
            </label>
            <textarea
              v-model="form.description"
              rows="4"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter package details..."
            ></textarea>
          </div>

          <!-- Summary Card -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">Price Summary</h3>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-700">Base Price:</span>
                <span class="font-semibold">{{ parseFloat(form.base_price || 0).toFixed(2) }} LKR</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-700">Base Time:</span>
                <span class="font-semibold">{{ form.base_time_minutes || 0 }} minutes</span>
              </div>
              <div class="flex justify-between text-orange-600">
                <span>Extra Charge:</span>
                <span class="font-semibold"
                  >{{ parseFloat(form.extra_charge || 0).toFixed(2) }} LKR per
                  {{ form.extra_charge_per_minutes || 15 }} mins</span
                >
              </div>
              <div class="border-t border-blue-200 pt-2 mt-2 flex justify-between">
                <span class="text-gray-700"
                  >Additional Payment (if age > {{ form.age_threshold || 10 }}):</span
                >
                <span class="font-semibold"
                  >{{ parseFloat(form.additional_payment || 0).toFixed(2) }} LKR</span
                >
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-4">
            <button
              type="submit"
              :disabled="processing"
              class="flex-1 bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ processing ? "Creating..." : "Create Package" }}
            </button>
            <button
              type="button"
              @click="goBack"
              class="flex-1 bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <Footer />
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { router } from "@inertiajs/vue3";
import { reactive, ref } from "vue";

const processing = ref(false);
const errors = reactive({});

const form = reactive({
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

const goBack = () => {
  router.visit(route("packages.index"));
};

const submitForm = () => {
  processing.value = true;
  router.post(route("packages.store"), form, {
    onError: (err) => {
      Object.assign(errors, err);
      processing.value = false;
    },
    onSuccess: () => {
      processing.value = false;
    },
  });
};
</script>

<style scoped>
/* Add any additional styles here */
</style>

<template>
  <Head title="Coins" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-6 bg-gray-100 md:px-36 px-6">
    <Header />

    <div class="w-full py-8 space-y-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">Coins</p>
        </div>

        <button
          @click="showGameModal = true"
          class="px-6 py-3 text-lg font-bold tracking-wide text-white bg-blue-600 rounded-xl hover:bg-blue-700"
        >
          <i class="ri-add-line pr-2"></i>
          Add Game
        </button>
      </div>

      <div class="w-full bg-white border-4 border-black rounded-3xl p-6 space-y-4">
        <div class="flex md:flex-row flex-col md:items-center items-start md:justify-between gap-4">
          <div>
            <p class="text-2xl font-bold text-black">Daily Game Coins</p>
            <p class="text-gray-600">Assign coins count for each game and calculate daily totals.</p>
          </div>

          <div class="flex items-center gap-3">
            <input
              v-model="selectedDate"
              type="date"
              class="px-4 py-2 border border-gray-300 rounded-lg"
            />
            <button
              @click="reloadByDate"
              class="px-4 py-2 font-semibold text-white bg-slate-700 rounded-lg hover:bg-slate-800"
            >
              View
            </button>
            <button
              @click="downloadDailyReportPdf"
              class="px-4 py-2 font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700"
            >
              Download PDF
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full border border-gray-300 text-left">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-3 border">Game</th>
                <th class="px-4 py-3 border">Coin Product</th>
                <th class="px-4 py-3 border text-right">Coin Price</th>
                <th class="px-4 py-3 border text-right">Coin Count</th>
                <th class="px-4 py-3 border text-right">Daily Total</th>
                <th class="px-4 py-3 border text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="games.length === 0">
                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No games created yet.</td>
              </tr>

              <tr v-for="game in games" :key="game.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 border font-semibold">{{ game.name }}</td>
                <td class="px-4 py-3 border">{{ game.coin_product?.name || "Not Assigned" }}</td>
                <td class="px-4 py-3 border text-right">{{ money(game.coin_product?.selling_price || 0) }} LKR</td>
                <td class="px-4 py-3 border text-right">
                  <input
                    v-model.number="entryDraft[game.id]"
                    type="number"
                    min="0"
                    class="w-28 px-2 py-1 border border-gray-300 rounded text-right"
                  />
                </td>
                <td class="px-4 py-3 border text-right font-bold">
                  {{ money((entryDraft[game.id] || 0) * Number(game.coin_product?.selling_price || 0)) }} LKR
                </td>
                <td class="px-4 py-3 border text-center">
                  <button
                    @click="saveDailyCount(game)"
                    class="px-3 py-1 text-white bg-blue-600 rounded hover:bg-blue-700"
                  >
                    Save
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="grid md:grid-cols-2 grid-cols-1 gap-4 pt-4">
          <div class="p-4 bg-green-100 border border-green-300 rounded-xl">
            <p class="text-sm font-semibold text-green-700">Daily Coins Count</p>
            <p class="text-3xl font-bold text-green-900">{{ totalCoinCount }}</p>
          </div>
          <div class="p-4 bg-blue-100 border border-blue-300 rounded-xl">
            <p class="text-sm font-semibold text-blue-700">Daily Coins Amount</p>
            <p class="text-3xl font-bold text-blue-900">{{ money(totalAmount) }} LKR</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showGameModal" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
      <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <p class="text-3xl font-bold text-black">Create Game</p>
          <button @click="showGameModal = false" class="text-3xl text-gray-700">
            <i class="ri-close-line"></i>
          </button>
        </div>

        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-semibold mb-1">Game Name</label>
            <input
              v-model="newGame.name"
              type="text"
              placeholder="Enter game name"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Assign Coin Product (Category: Coins)</label>
            <select
              v-model="newGame.coin_product_id"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg"
            >
              <option value="" disabled>Select coin product</option>
              <option v-for="product in coinProducts" :key="product.id" :value="product.id">
                {{ product.name }} - {{ money(product.selling_price) }} LKR
              </option>
            </select>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button @click="showGameModal = false" class="px-4 py-2 rounded-lg border border-gray-300">Cancel</button>
            <button
              @click="createGame"
              :disabled="loadingGameCreate"
              class="px-5 py-2 text-white bg-black rounded-lg disabled:opacity-60"
            >
              {{ loadingGameCreate ? "Saving..." : "Save Game" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup>
import Banner from "@/Components/Banner.vue";
import Footer from "@/Components/custom/Footer.vue";
import Header from "@/Components/custom/Header.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import axios from "axios";
import { computed, reactive, ref } from "vue";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

const props = defineProps({
  selectedDate: { type: String, required: true },
  games: { type: Array, default: () => [] },
  coinProducts: { type: Array, default: () => [] },
  entries: { type: Array, default: () => [] },
  dailySummary: { type: Object, default: () => ({ total_coin_count: 0, total_amount: 0 }) },
});

const selectedDate = ref(props.selectedDate);
const games = ref(props.games);
const coinProducts = ref(props.coinProducts);
const showGameModal = ref(false);
const loadingGameCreate = ref(false);

const newGame = reactive({
  name: "",
  coin_product_id: "",
});

const entryDraft = reactive({});
props.games.forEach((game) => {
  const existing = props.entries.find((e) => Number(e.game_id) === Number(game.id));
  entryDraft[game.id] = existing ? Number(existing.coin_count || 0) : 0;
});

const totalCoinCount = computed(() => {
  return games.value.reduce((sum, game) => sum + Number(entryDraft[game.id] || 0), 0);
});

const totalAmount = computed(() => {
  return games.value.reduce((sum, game) => {
    const count = Number(entryDraft[game.id] || 0);
    const price = Number(game.coin_product?.selling_price || 0);
    return sum + count * price;
  }, 0);
});

const money = (value) => Number(value || 0).toFixed(2);

const reloadByDate = () => {
  router.get(route("coins.index"), { date: selectedDate.value }, { preserveScroll: true, preserveState: false });
};

const createGame = async () => {
  if (!newGame.name || !newGame.coin_product_id) {
    alert("Please fill all fields.");
    return;
  }

  loadingGameCreate.value = true;
  try {
    const response = await axios.post(route("coins.games.store"), {
      name: newGame.name,
      coin_product_id: newGame.coin_product_id,
    });

    games.value.push(response.data.game);
    entryDraft[response.data.game.id] = 0;

    newGame.name = "";
    newGame.coin_product_id = "";
    showGameModal.value = false;
  } catch (error) {
    alert(error.response?.data?.message || "Failed to create game.");
  } finally {
    loadingGameCreate.value = false;
  }
};

const saveDailyCount = async (game) => {
  try {
    await axios.post(route("coins.entries.upsert"), {
      game_id: game.id,
      entry_date: selectedDate.value,
      coin_count: Number(entryDraft[game.id] || 0),
    });
  } catch (error) {
    alert(error.response?.data?.message || "Failed to save daily count.");
  }
};

const downloadDailyReportPdf = async () => {
  try {
    const response = await axios.get(route("coins.report.daily"), {
      params: { date: selectedDate.value },
    });

    const report = response.data;

    const doc = new jsPDF({ orientation: "portrait", unit: "pt", format: "a4" });
    doc.setFontSize(18);
    doc.text("Daily Coins Report", 40, 50);

    doc.setFontSize(11);
    doc.text(`Date: ${report.date}`, 40, 72);

    const body = report.rows.map((row, index) => [
      index + 1,
      row.game_name || "-",
      row.coin_product_name || "-",
      money(row.coin_price),
      row.coin_count,
      money(row.total_amount),
    ]);

    autoTable(doc, {
      startY: 90,
      head: [["#", "Game", "Coin Product", "Coin Price", "Coin Count", "Total Amount"]],
      body,
      styles: { fontSize: 10 },
      headStyles: { fillColor: [37, 99, 235] },
    });

    const finalY = doc.lastAutoTable?.finalY || 120;
    doc.setFontSize(12);
    doc.text(`Total Coin Count: ${report.summary.total_coin_count}`, 40, finalY + 24);
    doc.text(`Total Amount: ${money(report.summary.total_amount)} LKR`, 40, finalY + 44);

    doc.save(`daily-coins-report-${report.date}.pdf`);
  } catch (error) {
    alert(error.response?.data?.message || "Failed to generate report.");
  }
};
</script>

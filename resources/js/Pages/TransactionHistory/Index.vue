<style>
/* General DataTables Pagination Container Style */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

/* Style the filter container */
#TransitionTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px; /* Add spacing below the filter */
}

/* Style the label and input field inside the filter */
#TransitionTable_filter label {
  font-size: 17px;
  color: #000000; /* Match text color of the table header */
  display: flex;
  align-items: center;
}

/* Style the input field */
#TransitionTable_filter input[type="search"] {
  font-weight: 400;
  padding: 9px 15px;
  font-size: 14px;
  color: #000000cc;
  border: 1px solid rgb(209 213 219);
  border-radius: 5px;
  background: #fff;
  outline: none;
  transition: all 0.5s ease;
}
#TransitionTable_filter input[type="search"]:focus {
  outline: none; /* Removes the default outline */
  border: 1px solid #4b5563;
  box-shadow: none; /* Removes any focus box-shadow */
}

#TransitionTable_filter {
  float: left;
}

.dataTables_wrapper {
  margin-bottom: 10px;
}
</style>

<template>
    <Head title="Order History" />
     <Banner />
     <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
        <Header />
        <div class="w-full md:w-5/6 py-12 space-y-24">
            <div class="flex items-center justify-between float-end">
                <p class="text-3xl italic font-bold text-black">
                <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{
                    totalhistoryTransactions

                }}</span>
                <span class="text-xl">/ Total History Transition</span>
                </p>
            </div>

            <div class="flex w-full">
                <div class="flex items-center w-full h-16 space-x-4 rounded-2xl">
                <Link href="/">
                    <img src="/images/back-arrow.png" class="w-14 h-14" />
                </Link>
                <p class="text-4xl font-bold tracking-wide text-black uppercase">
                    Order History
                </p>
                </div>
                <div class="flex justify-end md:inline hidden w-full"></div>
            </div>


            <template v-if="allhistoryTransactions && allhistoryTransactions.length > 0">
                <div class="overflow-x-auto">
                <table
                    id="TransitionTable" class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
                    <thead>
                    <tr class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[12px] text-white border-b border-blue-700">
                        <th class="p-4 font-semibold tracking-wide text-left uppercase">#</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase">Oredr ID</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase">Total Amount</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase"> Discount</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase">Payment Method</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase">Sale Date</th>
                        <th class="p-4 font-semibold tracking-wide text-left uppercase"> Action</th>
                    </tr>
                    </thead>
                    <tbody class="text-[13px] font-normal">
                        <tr
                            v-for="(history, index) in allhistoryTransactions"
                            :key="history.id"
                            :class="history.status === 'cancelled' ? 'bg-red-50 opacity-70' : 'transition duration-200 ease-in-out hover:bg-gray-200 hover:shadow-lg'"
                        >
                            <td class="px-6 py-3 text- first-letter:">{{ index + 1 }}</td>
                            <td class="p-4 font-bold border-gray-200">
                                {{ history.order_id || "N/A" }}
                                <span v-if="history.status === 'cancelled'" class="ml-2 px-2 py-0.5 text-[10px] font-bold text-white bg-red-500 rounded-full uppercase">Cancelled</span>
                            </td>
                            <td class="p-4 font-bold border-gray-200">{{ history.total_amount - (history.discount || 0) - (history.custom_discount || 0) || "N/A" }}</td>
                             <td class="p-4 font-bold border-gray-200">{{((parseFloat(history.discount) || 0) + (parseFloat(history.custom_discount) || 0)).toLocaleString()}}</td>
                            <td class="p-4 font-bold border-gray-200">{{ history.payment_method || "N/A" }}</td>
                            <td class="p-4 font-bold border-gray-200">{{ history.sale_date || "N/A" }}</td>
                            <td class="p-4 font-bold border-gray-200">
                                <template v-if="history.status !== 'cancelled'">
                                    <button
                                        @click="reprintReceipt(history)"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 mr-2"
                                    >
                                        Reprint
                                    </button>
                                    <button
                                        @click="openCancelModal(history)"
                                        class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 mr-2"
                                    >
                                        Cancel Bill
                                    </button>
                                    <button class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600" @click="deleteReceipt(history.order_id)">
                                        Delete
                                    </button>
                                </template>
                                <template v-else>
                                    <span class="text-red-400 text-[12px] italic">
                                        Cancelled<br/>
                                        <span v-if="history.cancel_reason" class="text-gray-400">{{ history.cancel_reason }}</span>
                                    </span>
                                </template>
                            </td>

                        </tr>
                    </tbody>
                </table>
                </div>
            </template>

            <template v-else>
                <div class="col-span-4 text-center text-blue-500">
                <p class="text-center text-red-500 text-[17px]">
                    No Stock Transition Available
                </p>
                </div>
            </template>
        </div>
     </div>
<Footer />

<!-- Cancel Bill Modal -->
<div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 space-y-4">
        <h2 class="text-xl font-bold text-gray-800">Cancel Bill</h2>
        <p class="text-sm text-gray-600">
            You are about to cancel order <span class="font-bold text-red-600">{{ cancelTarget?.order_id }}</span>.
            The stock will be restored automatically. This bill will remain in history as <span class="font-bold text-red-500">Cancelled</span> for your records.
        </p>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reason for cancellation (optional)</label>
            <textarea
                v-model="cancelReason"
                rows="3"
                placeholder="e.g. Customer changed mind, duplicate order..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
            ></textarea>
        </div>
        <div class="flex justify-end space-x-3">
            <button
                @click="closeCancelModal"
                class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
            >Close</button>
            <button
                @click="confirmCancel"
                class="px-5 py-2 rounded-lg bg-orange-500 text-white font-semibold hover:bg-orange-600"
            >Confirm Cancel</button>
        </div>
    </div>
</div>

</template>


<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import { Head, Link } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

const props = defineProps({
  allhistoryTransactions: Array,
  totalhistoryTransactions: Number,
  companyInfo: Array
});
const form = useForm({});

// ── Cancel Bill ──────────────────────────────────────────────────
const showCancelModal = ref(false);
const cancelTarget    = ref(null);
const cancelReason    = ref('');

const openCancelModal = (history) => {
    cancelTarget.value  = history;
    cancelReason.value  = '';
    showCancelModal.value = true;
};

const closeCancelModal = () => {
    showCancelModal.value = false;
    cancelTarget.value    = null;
    cancelReason.value    = '';
};

const confirmCancel = () => {
    if (!cancelTarget.value) return;
    router.post(route('transactions.cancel'), {
        order_id:      cancelTarget.value.order_id,
        cancel_reason: cancelReason.value || null,
    }, {
        onSuccess: () => closeCancelModal(),
        onError: (error) => {
            alert('Error: ' + (error.message || 'Something went wrong.'));
        },
    });
};
// ────────────────────────────────────────────────────────────────

const deleteReceipt = (orderId) => {
  if (confirm("Are you sure you want to delete this record? This action cannot be undone.")) {
    router.post(route("transactions.delete"), { order_id: orderId }, {
      onError: (error) => {
        alert("Error: " + (error.message || "Something went wrong."));
      },
    });
  }
};

// ── Reprint Final Bill ───────────────────────────────────────────
const reprintReceipt = async (history) => {
  try {
    const res = await axios.get(route('transactions.reprint', { order_id: history.order_id }));
    const { session, totals, balance, sale } = res.data;
    const f2 = (v) => Number(v ?? 0).toFixed(2);
    const barcode   = sale?.order_id || history.order_id || '';
    const payMethod = history.payment_method || 'Cash';

    // Items rows
    const itemsArr    = (session && session.items && session.items.length) ? session.items : (sale && sale.sale_items ? sale.sale_items : []);
    const productRows = itemsArr.map(function(item) {
      return '<tr><td>' + (item.product ? item.product.name : 'Item') + '</td><td style="text-align:center;">' + item.quantity + '</td><td style="text-align:right;">LKR ' + f2(item.total_price) + '</td></tr>';
    }).join('');

    // Info
    const customerName = (session && session.customer_name) ? session.customer_name : ((sale && sale.customer) ? sale.customer.name : 'Walk-in');
    const serviceName  = (session && session.package && session.package.name) ? session.package.name : '';
    const elapsedMins  = totals ? (totals.elapsed_minutes || 0) : 0;
    const extraMins    = totals ? (totals.extra_minutes || 0) : 0;

    // Totals
    let totalsHtml = '';
    if (totals) {
      if (Number(totals.package_total) > 0)
        totalsHtml += '<div class="tot-row"><span>Package</span><span>LKR ' + f2(totals.package_total) + '</span></div>';
      if (Number(totals.products_total) > 0)
        totalsHtml += '<div class="tot-row"><span>Products</span><span>LKR ' + f2(totals.products_total) + '</span></div>';
      if (Number(totals.extra_amount) > 0)
        totalsHtml += '<div class="tot-row"><span>Extra Time</span><span>LKR ' + f2(totals.extra_amount) + '</span></div>';
      totalsHtml += '<div class="grand-row"><span>GRAND TOTAL</span><span>LKR ' + f2(totals.final_total) + '</span></div>';
      totalsHtml += '<div class="tot-row"><span>Cash</span><span>LKR ' + f2(totals.cash) + '</span></div>';
      totalsHtml += '<div class="tot-row" style="font-weight:bold;"><span>Balance</span><span>LKR ' + f2(balance) + '</span></div>';
    } else {
      const grandTotal = Number(sale.total_amount) - Number(sale.discount || 0) - Number(sale.custom_discount || 0);
      const cash       = Number(sale.cash || 0);
      var disc = Number(sale.discount || 0) + Number(sale.custom_discount || 0);
      if (disc > 0)
        totalsHtml += '<div class="tot-row"><span>Discount</span><span>LKR ' + f2(disc) + '</span></div>';
      totalsHtml += '<div class="grand-row"><span>GRAND TOTAL</span><span>LKR ' + f2(grandTotal) + '</span></div>';
      totalsHtml += '<div class="tot-row"><span>Cash</span><span>LKR ' + f2(cash) + '</span></div>';
      totalsHtml += '<div class="tot-row" style="font-weight:bold;"><span>Balance</span><span>LKR ' + f2(cash - grandTotal) + '</span></div>';
    }

    const infoRows = (serviceName ? '<div class="irow"><span class="k">Service</span><span>' + serviceName + '</span></div>' : '')
      + (elapsedMins > 0 ? '<div class="irow"><span class="k">Time Used</span><span>' + elapsedMins + ' mins</span></div>' : '')
      + (extraMins > 0 ? '<div class="irow"><span class="k">Overtime</span><span>' + extraMins + ' mins</span></div>' : '');

    const itemsSection = productRows
      ? '<div class="items-sec"><table><thead><tr><th>Item</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Price</th></tr></thead><tbody>' + productRows + '</tbody></table></div>'
      : '';

    const payDisplay = payMethod.charAt(0).toUpperCase() + payMethod.slice(1);

    const html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Final Bill ' + barcode + '</title>'
      + '<script src="/js/JsBarcode.all.min.js"><\/script>'
      + '<style>'
      + '* { margin:0; padding:0; box-sizing:border-box; }'
      + '@page { margin: 0; }'
      + '@media print { html,body { width:100mm; } .receipt { width:100mm; } }'
      + 'body { background:#fff; font-family:Arial,sans-serif; font-size:13px; color:#000; }'
      + '.receipt { width:100mm; margin:0 auto; padding:6mm 5mm 10mm 5mm; }'
      + '.header { text-align:center; border-bottom:1px dashed #000; padding-bottom:8px; margin-bottom:10px; }'
      + '.header img { width:130px; height:auto; display:block; margin:0 auto 5px auto; }'
      + '.header .shop { font-size:17px; font-weight:bold; letter-spacing:2px; margin-bottom:2px; }'
      + '.header .tel { font-size:12px; }'
      + '.title-sec { text-align:center; border-bottom:1px dashed #000; padding-bottom:8px; margin-bottom:10px; }'
      + '.title-sec .lbl { font-size:17px; font-weight:bold; letter-spacing:1px; margin-bottom:4px; }'
      + '.title-sec .bill-no { font-size:15px; font-weight:bold; }'
      + '.title-sec .bill-dt { font-size:11px; color:#000; margin-top:3px; }'
      + '.info-sec { border-bottom:1px dashed #000; padding-bottom:8px; margin-bottom:10px; }'
      + '.irow { display:flex; justify-content:space-between; margin-bottom:4px; font-size:12px; }'
      + '.irow .k { font-weight:bold; }'
      + '.items-sec { margin-bottom:10px; }'
      + 'table { width:100%; border-collapse:collapse; font-size:12px; }'
      + 'th,td { padding:3px 4px; }'
      + 'th { text-align:left; border-bottom:1px solid #000; }'
      + 'td { vertical-align:top; }'
      + 'td:nth-child(2) { text-align:center; }'
      + 'td:nth-child(3) { text-align:right; white-space:nowrap; }'
      + '.tot-sec { border-top:1px dashed #000; padding-top:7px; margin-bottom:10px; }'
      + '.tot-row { display:flex; justify-content:space-between; margin-bottom:4px; font-size:13px; }'
      + '.grand-row { display:flex; justify-content:space-between; font-size:16px; font-weight:bold; border-top:1px solid #000; padding-top:5px; margin-top:5px; margin-bottom:7px; }'
      + '.pay-box { border:1px solid #ccc; border-radius:3px; padding:5px 7px; font-size:12px; margin-bottom:10px; }'
      + '.pay-box span { font-weight:bold; }'
      + '.barcode-sec { text-align:center; border-top:1px dashed #000; border-bottom:1px dashed #000; padding:8px 0 6px 0; margin-bottom:10px; }'
      + '.barcode-sec svg { width:100%; max-width:90mm; }'
      + '.barcode-sec .bc-text { font-size:13px; font-weight:bold; letter-spacing:2px; margin-top:4px; }'
      + '.footer { text-align:center; font-size:11px; color:#000; }'
      + '.footer p { margin-bottom:3px; }'
      + '<\/style></head><body><div class="receipt">'
      + '<div class="header"><img src="/images/logo.png" alt="Logo" onerror="this.style.display=\'none\'" /><div class="shop">PLAY AREA</div><div class="tel">Tel : 077 306 3000</div></div>'
      + '<div class="title-sec"><div class="lbl">FINAL BILL</div><div class="bill-no">' + barcode + '</div><div class="bill-dt">' + new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString() + '</div></div>'
      + '<div class="info-sec"><div class="irow"><span class="k">Customer</span><span>' + customerName + '</span></div>' + infoRows + '</div>'
      + itemsSection
      + '<div class="tot-sec">' + totalsHtml + '</div>'
      + '<div class="pay-box">Payment Method: &nbsp;<span>' + payDisplay + '</span></div>'
      + '<div class="barcode-sec"><svg id="reprintBarcode"></svg><div class="bc-text">' + barcode + '</div></div>'
      + '<div class="footer"><p>We hope you enjoyed the play area!</p><p>Come again for more fun times.</p><p style="font-weight:bold; margin-top:3px;">Powered by JAAN Network Ltd.</p></div>'
      + '</div>'
      + '<script>if(typeof JsBarcode!=="undefined"&&"' + barcode + '"){JsBarcode("#reprintBarcode","' + barcode + '",{format:"CODE128",width:3,height:80,displayValue:false,margin:5});}window.onload=function(){window.print();};<\/script>'
      + '</body></html>';

    const w = window.open('', '_blank', 'width=700,height=900');
    if (!w) { alert('Failed to open print window. Please allow popups.'); return; }
    w.document.write(html);
    w.document.close();
  } catch (err) {
    alert('Failed to load bill data for reprint.');
  }
};
// ────────────────────────────────────────────────────────────────



$(document).ready(function () {
  if ($.fn.DataTable.isDataTable("#TransitionTable")) {
    $("#TransitionTable").DataTable().destroy();
  }

  let table = $("#TransitionTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    buttons: [],
    columnDefs: [
      {
        targets: 2,
        searchable: false,
        orderable: false,
      },
    ],
    initComplete: function () {
      let searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
      searchInput.on("keypress", function (e) {
        if (e.which == 13) {
          table.search(this.value).draw();
        }
      });
    },
    language: {
      search: "",
    },
  });
});

 const handlePrintReceipt = () => {
  // --- Safe helpers ---
  const n = (v) => Number(v ?? 0);
  const f2 = (v) => (Number(v ?? 0)).toFixed(2);

  // --- Totals (fallbacks if props didn’t provide) ---
  const subTotal = (props.products || []).reduce(
    (sum, p) => sum + n(p.selling_price) * n(p.quantity),
    0
  );

  const totalDiscountRaw = (props.products || []).reduce((total, item) => {
    if (item.discount && n(item.discount) > 0 && item.apply_discount === true) {
      const diff = n(item.selling_price) - n(item.discounted_price);
      return total + diff * n(item.quantity);
    }
    return total;
  }, 0);

  const totalDiscount = Number(totalDiscountRaw.toFixed(2));
  const customDiscount = n(props.custom_discount);
  const total = subTotal - totalDiscount - customDiscount;

  // --- Build product rows with PACK expansion (full-width heading, aligned children) ---
  const productRows = (props.products || [])
    .map((product) => {
      const isPack = Number(product.is_promotion) === 1;

      const parentRow = `
        <tr>
          <td>
            ${product.name ?? ""}
          </td>
          <td style="text-align:center;">${n(product.quantity)}</td>
          <td>
            ${
              (n(product.discount) > 0 && product.apply_discount)
                ? `<div style="font-weight:bold;font-size:7px;background-color:black;color:white;text-align:center;">${n(product.discount)}% off</div>`
                : ``
            }
            <div>${f2(product.selling_price)}</div>
          </td>
        </tr>
      `;

      // Support both snake_case and camelCase from API
      const items = Array.isArray(product.promotion_items)
        ? product.promotion_items
        : (Array.isArray(product.promotionItems) ? product.promotionItems : []);

      let childRows = ``;
      if (isPack && items.length) {
        const headingRow = `
          <tr class="pack-heading">
            <td class="pack-heading-cell" colspan="3">Pack Include</td>
          </tr>
        `;

        const children = items.map((pi) => {
          const compName = (pi.product && pi.product.name) ? pi.product.name : `#${pi.product_id}`;
          const compQty = (n(pi.quantity) || 1) * (n(product.quantity) || 1);
          return `
            <tr class="pack-child">
              <td class="pack-child-name">* ${compName}</td>
              <td class="pack-child-qty">${compQty}</td>
              <td class="pack-child-price"></td>
            </tr>
          `;
        }).join("");

        childRows = headingRow + children;
      }

      return parentRow + childRows;
    })
    .join("");

  // --- Receipt HTML ---
  const receiptHTML = `
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Receipt</title>
<style>
  @media print {
    body { margin:0; padding:0; -webkit-print-color-adjust:exact; }
  }
  body {
    background-color:#ffffff; font-size:12px; font-family:"Arial",sans-serif;
    margin:0; padding:10px; color:#000;
  }
  .header { text-align:center; margin-bottom:16px; }
  .header h1 { font-size:20px; font-weight:bold; margin:0; }
  .header p { font-size:10px; margin:4px 0; }
  .section { margin-bottom:16px; padding-top:8px; border-top:1px solid #000; }
  .info-row { display:flex; justify-content:space-between; font-size:12px; margin-top:8px; }
  .info-row p { margin:0; font-weight:bold; }
  .info-row small { font-weight:normal; }
  table { width:100%; font-size:12px; border-collapse:collapse; margin-top:8px; table-layout:fixed; }
  table th, table td { padding:6px 8px; word-wrap:break-word; }
  table th { text-align:left; }
  table td { text-align:right; }
  table td:first-child { text-align:left; }
  .totals { border-top:1px solid #000; padding-top:8px; font-size:12px; }
  .totals div { display:flex; justify-content:space-between; margin-bottom:8px; }
  .totals div:nth-child(4) { font-size:14px; font-weight:bold; }
  .footer { text-align:center; font-size:10px; margin-top:16px; }
  .footer p { margin:6px 0; }
  .footer .italic { font-style:italic; }

  /* --- PACK styling --- */
  .pack-heading .pack-heading-cell{
    background:#e2e8f0;
    border:1px solid #ccc;
    padding:6px 8px;
    font-weight:bold;
    text-align:left;
  }
  .pack-child td{ font-size:11px; }
  .pack-child-name{
    padding-left:14px;
    background:#f1f5f9;
    border:1px solid #ccc;
    border-radius:4px;
  }
  .pack-child-qty{
    text-align:center;
    background:#f1f5f9;
    border:1px solid #ccc;
  }
  .pack-child-price{
    background:#f1f5f9;
    border:1px solid #ccc;
  }
</style>
</head>
<body>
  <div class="receipt-container">
    <div class="header">
      <img src="/images/billlogo.png" style="width:220px;height:100px;" />
      ${companyInfo?.value?.name ? `<h1>${companyInfo.value.name}</h1>` : ''}
      ${companyInfo?.value?.address ? `<p>${companyInfo.value.address}</p>` : ''}
      ${
        (companyInfo?.value?.phone || companyInfo?.value?.phone2 || companyInfo?.value?.email)
          ? `<p>${companyInfo.value.phone || ''} | ${companyInfo.value.phone2 || ''} ${companyInfo.value.email || ''}</p>`
          : ''
      }
    </div>

    <div class="section">
      <div class="info-row">
        <div><p>Date:</p><small>${new Date().toLocaleDateString()}</small></div>
        <div><p>Order No:</p><small>${props.orderid ?? ''}</small></div>
      </div>
      <div class="info-row">
        <div><p>Customer:</p><small>${props.customer?.name ?? ''}</small></div>
        <div><p>Cashier:</p><small>${props.cashier?.name ?? ''}</small></div>
      </div>
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
          ${productRows}
        </tbody>
      </table>
    </div>

    <div class="totals">
      <div><span>Sub Total</span><span>${f2(props.subTotal ?? subTotal)} LKR</span></div>
      <div><span>Discount</span><span>${f2(props.totalDiscount ?? totalDiscount)} LKR</span></div>
      <div>
        <span>Custom Discount</span>
        <span>${f2(props.custom_discount ?? customDiscount)} ${
          props.custom_discount_type === 'percent'
            ? '%'
            : props.custom_discount_type === 'fixed'
            ? 'LKR'
            : ''
        }</span>
      </div>
      <div><span>Total</span><span>${f2(props.total ?? total)} LKR</span></div>
      <div><span>Cash</span><span>${f2(props.cash)} LKR</span></div>
      <div style="font-weight:bold;"><span>Balance</span><span>${f2(props.balance)} LKR</span></div>
    </div>

    <div class="footer">
      <p>No Exchange On Purchased Items</p>
      <p>THANK YOU COME AGAIN</p>
      <p class="italic">Let the quality define its own standards</p>
      <p style="font-weight:bold;">Powered by JAAN Network Ltd.</p>
      <p>${new Date().toLocaleTimeString()}</p>
    </div>
  </div>
</body>
</html>
  `;

  const printWindow = window.open("", "_blank");
  if (!printWindow) {
    alert("Failed to open print window. Please check your browser settings.");
    return;
  }
  printWindow.document.open();
  printWindow.document.write(receiptHTML);
  printWindow.document.close();

  printWindow.onload = () => {
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };
};



</script>


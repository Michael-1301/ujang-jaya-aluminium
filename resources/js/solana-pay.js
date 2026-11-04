import { Keypair, PublicKey, Connection, clusterApiUrl } from "@solana/web3.js";
import { encodeURL, createQR } from "@solana/pay";
import BigNumber from "bignumber.js";

// ====== ATUR DI SINI ======

const RECIPIENT = "BYHRNDC2ScR86ZXvo5bre9nTKoPR5xkyZsdKXdEidsdY";
const RUPIAH_PER_SOL = 5000000; 
const STORE_LABEL = "Ujang Jaya Aluminium";
const DEBUG = false; 

const connection = new Connection(clusterApiUrl("devnet"), "confirmed");
let timer = null;
let pollCount = 0;
let sessionId = 0; 

function log(...args) {
  if (DEBUG) console.log("[SolanaPay]", ...args);
}

// Harga dasar dalam SOL (4 desimal, dibulatkan ke atas)
function rupiahToSol(rupiah) {
  return new BigNumber(rupiah)
    .dividedBy(RUPIAH_PER_SOL)
    .decimalPlaces(4, BigNumber.ROUND_UP);
}

// Hitung SOL yang diterima wallet toko dalam sebuah transaksi (lamports)
function receivedLamports(tx, recipientStr) {
  if (!tx || !tx.meta || tx.meta.err) return null;
  const keys = tx.transaction.message.accountKeys.map((k) =>
    (k.pubkey ? k.pubkey : k).toString()
  );
  const idx = keys.indexOf(recipientStr);
  if (idx === -1) return null;
  return new BigNumber(tx.meta.postBalances[idx]).minus(tx.meta.preBalances[idx]);
}

/**
 * Cek pembayaran. Mengembalikan signature kalau valid, null kalau belum ada.
 *  Jalur 1: transaksi yang membawa `reference` (standar Solana Pay).
 *  Jalur 2 (cadangan): transaksi BARU masuk ke wallet toko (setelah QR tampil)
 *           dengan nominal yang sama persis.
 */
async function checkPayment(ctx) {
  const { reference, recipient, expectedLamports, startTime, baselineOk, seen } = ctx;
  const recipientStr = recipient.toBase58();

  pollCount++;
  const verbose = pollCount % 5 === 1;

  // ---- Jalur 1: lewat reference ----
  const refSigs = await connection.getSignaturesForAddress(
    reference,
    { limit: 5 },
    "confirmed"
  );
  if (verbose) log(`cek #${pollCount}: transaksi via reference =`, refSigs.length);

  for (const info of refSigs) {
    if (info.err) continue;
    const tx = await connection.getParsedTransaction(info.signature, {
      commitment: "confirmed",
      maxSupportedTransactionVersion: 0,
    });
    const got = receivedLamports(tx, recipientStr);
    log("[reference] diterima:", got ? got.toString() : null,
        "| dibutuhkan >=", expectedLamports.toString());
    if (got && got.gte(expectedLamports)) return info.signature;
  }

  // ---- Jalur 2: cadangan lewat transaksi baru di wallet toko ----
  const recSigs = await connection.getSignaturesForAddress(
    recipient,
    { limit: 20 },
    "confirmed"
  );
  if (verbose) log("transaksi terbaru di wallet toko =", recSigs.length,
                   "| yang baru:", recSigs.filter((s) => !seen.has(s.signature)).length);

  for (const info of recSigs) {
    if (info.err) continue;
    if (seen.has(info.signature)) continue;

    // kalau baseline gagal diambil, pakai filter waktu sebagai cadangan
    if (!baselineOk && info.blockTime && info.blockTime < startTime) {
      seen.add(info.signature);
      continue;
    }

    const tx = await connection.getParsedTransaction(info.signature, {
      commitment: "confirmed",
      maxSupportedTransactionVersion: 0,
    });
    if (!tx) continue; // belum tersedia, coba lagi di polling berikutnya

    seen.add(info.signature);
    const got = receivedLamports(tx, recipientStr);
    log("[cadangan] transaksi baru:", info.signature,
        "| diterima:", got ? got.toString() : null,
        "| dibutuhkan persis:", expectedLamports.toString());

    if (got && got.eq(expectedLamports)) return info.signature;
  }

  return null;
}

window.SolanaPay = {
  // untuk tampilan harga di form (harga dasar, tanpa selisih unik)
  rupiahToSol: (rupiah) => rupiahToSol(rupiah).toString(),

  async start({ amountRupiah, orderRef, containerId, onPaid }) {
    this.stop();
    pollCount = 0;
    const mySession = ++sessionId;

    const base = rupiahToSol(amountRupiah);
    if (!base.isFinite() || base.lte(0)) throw new Error("Harga tidak valid");

    // selisih unik 1-999 lamports supaya nominal tiap pesanan berbeda
    const uniqueLamports = Math.floor(Math.random() * 999) + 1;
    const amount = base.plus(new BigNumber(uniqueLamports).dividedBy(1e9));
    const expectedLamports = amount.times(1e9).integerValue(BigNumber.ROUND_CEIL);

    const recipient = new PublicKey(RECIPIENT);
    const reference = Keypair.generate().publicKey; // unik per pembayaran
    const startTime = Math.floor(Date.now() / 1000) - 60;
    const seen = new Set();

    // Catat transaksi yang SUDAH ada di wallet toko sebelum QR tampil.
    // Yang muncul setelah ini dianggap transaksi baru (tidak bergantung jam laptop).
    let baselineOk = false;
    try {
      const existing = await connection.getSignaturesForAddress(
        recipient,
        { limit: 20 },
        "confirmed"
      );
      existing.forEach((s) => seen.add(s.signature));
      baselineOk = true;
      log("baseline:", existing.length, "transaksi lama diabaikan");
    } catch (e) {
      console.warn("Baseline gagal, pakai filter waktu:", e);
    }

    // kalau QR dibuat ulang selagi baseline diambil, hentikan sesi ini
    if (mySession !== sessionId) return;

    log("mulai | penerima:", recipient.toBase58(),
        "| jumlah SOL:", amount.toFixed(),
        "| reference:", reference.toBase58());

    const url = encodeURL({
      recipient,
      amount,
      reference,
      label: STORE_LABEL,
      message: "Pesanan " + orderRef,
      memo: orderRef,
    });

    log("URL pembayaran:", url.toString());

    const box = document.getElementById(containerId);
    box.innerHTML = "";
    createQR(url, 280).append(box);

    let checking = false;
    timer = setInterval(async () => {
      if (checking) return;
      checking = true;
      try {
        const signature = await checkPayment({
          reference, recipient, expectedLamports, startTime, baselineOk, seen,
        });
        if (signature && mySession === sessionId) {
          log("PEMBAYARAN VALID:", signature);
          window.SolanaPay.stop();
          onPaid({
            signature,
            reference: reference.toBase58(),
            amountSol: amount.toFixed(),
          });
        }
      } catch (e) {
        console.warn("Verifikasi:", e);
      }
      checking = false;
    }, 3000);
  },

  stop() {
    if (timer) clearInterval(timer);
    timer = null;
  },
};
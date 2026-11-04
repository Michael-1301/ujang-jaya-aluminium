<!-- ================= FIREBASE ================= -->
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-auth-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-storage-compat.js"></script>
<script>
/* ================= FIREBASE INIT ================= */
firebase.initializeApp({
  apiKey: "AIzaSyDsmsyt6iQsHE33gKZ-S4teyAcqlGxLVcw",
  authDomain: "sistem-produksi-aluminium.firebaseapp.com",
  projectId: "sistem-produksi-aluminium",
  storageBucket: "sistem-produksi-aluminium.firebasestorage.app",
  messagingSenderId: "834580018149",
  appId: "1:834580018149:web:3f6a0789b0dde9e5ddf50c"
});

const auth = firebase.auth();
const db   = firebase.firestore();
</script>

@php $backFallback = $backFallback ?? '/'; @endphp
<button type="button" class="btn-back" onclick="handleBackNav()" aria-label="Kembali ke halaman sebelumnya">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
    </svg>
    <span>Kembali</span>
</button>
<script>
function handleBackNav(){
    if (document.referrer && document.referrer.indexOf(window.location.host) !== -1 && window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = "{{ $backFallback }}";
    }
}
</script>

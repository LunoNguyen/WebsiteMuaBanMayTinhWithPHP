// NEXUS WMS - Script dùng chung cho module Kho

// Mở / đóng sidebar ở màn hẹp
function toggleKhoSidebar() {
  var sb = document.querySelector('.wsb');
  if (sb) sb.classList.toggle('open');
}

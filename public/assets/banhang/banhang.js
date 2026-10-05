// NEXUS Sales - Script dùng chung cho module Nhân viên bán hàng

// Mở / đóng sidebar ở màn hẹp
function toggleNvbSidebar() {
  var sb = document.querySelector('.nvb-sb');
  if (sb) sb.classList.toggle('open');
}

// Ô chọn ngày: hiển thị dd/mm/yyyy tiếng Việt, vẫn gửi Y-m-d như cũ
if (window.flatpickr) {
  flatpickr('.js-date', {
    locale: 'vn',
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    allowInput: true,
    disableMobile: true
  });
}

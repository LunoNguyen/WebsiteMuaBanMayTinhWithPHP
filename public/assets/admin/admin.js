// ================================================================
// Admin Dashboard - Main JavaScript
// ================================================================

// Toast Notification System
function showToast(message, type = 'info', duration = 4000) {
  const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${icons[type] || 'ℹ️'}</span><span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = 'toast-out 0.3s ease forwards';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// Confirm dialog helper
function confirmAction(message, callback) {
  if (confirm(message)) callback();
}

// Format number as VND
function formatVND(n) {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(n);
}

// Format large numbers short (1.2M, 318.5M...)
function formatShort(n) {
  if (n >= 1e9) return (n / 1e9).toFixed(1) + 'B';
  if (n >= 1e6) return (n / 1e6).toFixed(1) + 'M';
  if (n >= 1e3) return (n / 1e3).toFixed(1) + 'K';
  return n.toString();
}

// Sidebar toggle for mobile
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  sidebar.classList.toggle('open');
}

// Animate counter numbers on page load
function animateCounters() {
  const counters = document.querySelectorAll('[data-count]');
  counters.forEach(counter => {
    const target = parseFloat(counter.dataset.count);
    const isDecimal = counter.dataset.count.includes('.');
    const prefix = counter.dataset.prefix || '';
    const suffix = counter.dataset.suffix || '';
    const duration = 1200;
    const step = 16;
    const steps = duration / step;
    let current = 0;
    const increment = target / steps;

    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      const display = isDecimal ? current.toFixed(1) : Math.floor(current);
      counter.textContent = prefix + display.toLocaleString('vi-VN') + suffix;
    }, step);
  });
}

// Confirm delete
function deleteRecord(url, message = 'Bạn có chắc muốn xóa không?') {
  if (confirm(message)) {
    window.location.href = url;
  }
}

// Close notification panel when clicking outside
document.addEventListener('click', function(e) {
  const panel = document.getElementById('notifPanel');
  const btn = document.getElementById('notifBtn');
  if (panel && btn && !panel.contains(e.target) && !btn.contains(e.target)) {
    panel.style.display = 'none';
  }
});

// Table row click highlight
document.addEventListener('DOMContentLoaded', function () {
  animateCounters();

  // Add hover class to tr
  document.querySelectorAll('tbody tr').forEach(tr => {
    tr.style.cursor = 'default';
  });

  // Auto-dismiss alerts
  document.querySelectorAll('.alert[data-dismiss]').forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = '0.3s';
      setTimeout(() => alert.remove(), 300);
    }, 4000);
  });
});

// Chart.js default config for dark theme
if (typeof Chart !== 'undefined') {
  Chart.defaults.color = '#707070';
  Chart.defaults.borderColor = '#ececef';
  Chart.defaults.font.family = "'Inter', sans-serif";
  Chart.defaults.font.size = 12;
  Chart.defaults.plugins.legend.labels.boxWidth = 12;
  Chart.defaults.plugins.legend.labels.usePointStyle = true;
}

// Smooth scroll to section
function scrollTo(id) {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Print helper
function printPage() {
  window.print();
}

// Copy to clipboard
function copyText(text) {
  navigator.clipboard.writeText(text).then(() => showToast('Đã copy!', 'success', 2000));
}

// Export table to CSV
function exportTableCSV(tableId, filename) {
  const table = document.getElementById(tableId);
  if (!table) return;
  const rows = Array.from(table.querySelectorAll('tr'));
  const csv = rows.map(row =>
    Array.from(row.querySelectorAll('th,td')).map(cell =>
      '"' + cell.innerText.replace(/"/g, '""') + '"'
    ).join(',')
  ).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = (filename || 'export') + '.csv';
  link.click();
}

// Add CSS animation for toast-out
const style = document.createElement('style');
style.textContent = `
  @keyframes toast-out {
    to { opacity: 0; transform: translateX(100%); }
  }
`;
document.head.appendChild(style);

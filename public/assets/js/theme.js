// Đổi giao diện sáng / tối. Lưu lựa chọn vào cookie để lần sau PHP vẽ đúng ngay từ đầu.

function toggleTheme() {
  var root = document.documentElement;
  var next = root.dataset.theme === 'dark' ? 'light' : 'dark';
  root.dataset.theme = next;
  document.cookie = 'theme=' + next + ';path=/;max-age=31536000;SameSite=Lax';
  applyChartTheme();
}

// Biểu đồ Chart.js vẽ bằng canvas, không đọc CSS: cập nhật màu chữ, lưới, chú thích theo chế độ hiện tại
function applyChartTheme() {
  if (!window.Chart || !Chart.instances) return;
  var css = getComputedStyle(document.documentElement);
  var text = css.getPropertyValue('--text-muted').trim();
  var grid = css.getPropertyValue('--border').trim();
  var card = css.getPropertyValue('--bg-card').trim();
  Object.values(Chart.instances).forEach(function (chart) {
    var o = chart.options;
    Object.values(o.scales || {}).forEach(function (s) {
      if (s.ticks) s.ticks.color = text;
      if (s.grid && s.grid.drawOnChartArea !== false) s.grid.color = grid;
      if (s.border) s.border.color = grid;
    });
    if (o.plugins && o.plugins.legend && o.plugins.legend.labels) o.plugins.legend.labels.color = text;
    if (o.plugins && o.plugins.tooltip) {
      var tt = o.plugins.tooltip;
      tt.backgroundColor = card;
      tt.borderColor = grid;
      tt.titleColor = css.getPropertyValue('--text-primary').trim();
      tt.bodyColor = text;
    }
    // Viền giữa các phần biểu đồ tròn theo màu nền card
    (chart.data.datasets || []).forEach(function (d) {
      if (chart.config.type === 'doughnut' || chart.config.type === 'pie') d.borderColor = card;
    });
    chart.update('none');
  });
}

window.addEventListener('load', applyChartTheme);

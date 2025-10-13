// js/config.js
// -------------------------------
// Tự động xác định base URL cho cả GitHub Pages, localhost và host thật

const BASE_URL = window.location.hostname.includes("github.io")
  ? window.location.origin + "/web/"
  : window.location.origin + "/";

// Hàm chuẩn hóa đường dẫn tương đối thành đường dẫn đầy đủ
function resolvePath(relativePath) {
  if (relativePath.startsWith("/")) relativePath = relativePath.substring(1);
  return BASE_URL + relativePath;
}

// (Tuỳ chọn) Tự động chèn thẻ <base> cho toàn bộ trang
document.write(`<base href="${BASE_URL}">`);

// Xuất ra console để dễ debug
console.log("📁 BASE_URL:", BASE_URL);

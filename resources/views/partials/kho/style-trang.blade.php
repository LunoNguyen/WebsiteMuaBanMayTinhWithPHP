{{-- Khung trang (sidebar, nội dung, bảng, nút) dùng chung cho các trang của khu này --}}
<style>
/* Reuse WMS styles */
:root{--wc:var(--bg-card);--wc2:var(--bg-card-hover);--wb:var(--border);--wt:var(--text-primary);--wm:var(--text-muted);--wr:10px;}
*{box-sizing:border-box;}
body{background:var(--bg-main);font-family:'Inter',sans-serif;}
.wsh{display:flex;min-height:100vh;}
.wsb{width:218px;min-width:218px;background:var(--wc);border-right:1px solid var(--wb);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;}
.wlogo{padding:15px 13px 11px;border-bottom:1px solid var(--wb);}
.wlogo-r{display:flex;align-items:center;gap:9px;}
.wli{width:33px;height:33px;border-radius:8px;background:var(--blue-solid);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;color:#fff;}
.wln{font-size:14px;font-weight:800;color:var(--wt);}.wls{font-size:10px;color:var(--wm);}
.wng{padding:11px 9px 4px;}.wnl{font-size:10px;color:var(--wm);padding:0 7px;margin-bottom:4px;}
.wni{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;margin-bottom:2px;color:var(--wm);font-size:12.5px;font-weight:500;text-decoration:none;transition:all .15s;}
.wni:hover{background:rgba(0,0,0,.04);color:var(--wt);}
.wni.active{background:rgba(58,86,228,.15);color:var(--blue);font-weight:600;}
.wni .ni{font-size:14px;flex-shrink:0;}
.nbg{margin-left:auto;background:var(--red-solid);color:#fff;border-radius:10px;font-size:10px;padding:1px 6px;font-weight:700;}
.wrb{margin:9px;padding:9px 11px;border-radius:8px;background:rgba(21,128,61,.08);border:1px solid rgba(21,128,61,.2);}
.rdot{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--green-solid);margin-right:4px;animation:pl 2s infinite;}
@keyframes pl{0%,100%{opacity:1}50%{opacity:.3}}
.wmn{flex:1;display:flex;flex-direction:column;overflow:hidden;background:var(--bg-main);}
.wct{flex:1;overflow:auto;padding:20px;display:flex;flex-direction:column;gap:14px;}
.wph{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;}
.wph h1{font-size:20px;font-weight:800;color:var(--wt);margin:0;}
.wph p{font-size:12px;color:var(--wm);margin:4px 0 0;}
.wkg{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;}
.wk{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);padding:14px 16px;display:flex;align-items:center;gap:12px;}
.wki{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.wkv{font-size:26px;font-weight:900;line-height:1;}.wkl{font-size:10px;color:var(--wm);}
.wfl{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);padding:10px 14px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.wfi{flex:1;min-width:180px;display:flex;align-items:center;gap:7px;background:var(--wc2);border:1px solid var(--wb);border-radius:7px;padding:6px 9px;}
.wfi input{border:none;background:transparent;color:var(--wt);font-size:12px;outline:none;flex:1;}
.wfi input::placeholder{color:var(--wm);}
.wse{background:var(--wc2);border:1px solid var(--wb);border-radius:7px;padding:6px 8px;color:var(--wt);font-size:12px;outline:none;cursor:pointer;}
.wtw{background:var(--wc);border:1px solid var(--wb);border-radius:var(--wr);overflow:hidden;}
.wth{padding:10px 14px;border-bottom:1px solid var(--wb);display:flex;align-items:center;justify-content:space-between;}
.wth h3{font-size:13px;font-weight:700;color:var(--wt);margin:0;}
.wcnt{background:rgba(58,86,228,.15);color:var(--blue);border-radius:20px;font-size:11px;padding:2px 8px;font-weight:700;}
table.wt{width:100%;border-collapse:collapse;}
table.wt th{padding:8px 12px;font-size:10px;font-weight:700;color:var(--wm);border-bottom:1px solid var(--wb);text-align:left;}
table.wt td{padding:10px 12px;font-size:13px;color:var(--wt);border-bottom:1px solid var(--border);vertical-align:middle;}
table.wt tr:last-child td{border-bottom:none;}
table.wt tr:hover td{background:rgba(0,0,0,.04);}
.wcode{font-family:monospace;font-weight:700;color:var(--blue);}
.wpil{display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;}
.wbn{padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px;border:none;transition:all .15s;text-decoration:none;white-space:nowrap;}
.wb-pri{background:var(--blue-solid);color:#fff;}
.wb-suc{background:var(--green-solid);color:#fff;}
.wb-out{background:transparent;border:1px solid var(--wb)!important;color:var(--wm);}
.wb-out:hover{border-color:var(--blue)!important;color:var(--wt);}
.btn-ic{width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;text-decoration:none;transition:all .15s;}
.btn-ic:hover{background:rgba(0,0,0,.04);}
.alert-msg{padding:11px 16px;border-radius:8px;font-size:13px;background:rgba(21,128,61,.1);border:1px solid rgba(21,128,61,.2);color:var(--green);}
.wni.active{background:rgba(58,86,228,.15);color:var(--blue);font-weight:600;}
/* Topbar minimal */
.kho-topbar{height:52px;background:var(--wc);border-bottom:1px solid var(--wb);display:flex;align-items:center;padding:0 18px;gap:12px;flex-shrink:0;}
.kho-topbar h2{font-size:14px;font-weight:700;color:var(--wt);margin:0;}
.kto-user{display:flex;align-items:center;gap:8px;margin-left:auto;}
.kto-av{width:30px;height:30px;border-radius:50%;background:var(--blue-solid);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;}
.wpg{padding:10px 14px;border-top:1px solid var(--wb);display:flex;align-items:center;justify-content:space-between;}
.wpa{display:flex;gap:3px;}
.wpl2{width:27px;height:27px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--wm);background:transparent;border:1px solid transparent;text-decoration:none;transition:all .15s;}
.wpl2:hover{background:rgba(0,0,0,.04);color:var(--wt);}
.wpl2.active{background:rgba(58,86,228,.2);border-color:rgba(58,86,228,.4);color:var(--blue);}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:var(--wb);border-radius:2px;}
.cok{background:rgba(21,128,61,.14);color:var(--green);border:1px solid rgba(21,128,61,.3);}
.csc{background:rgba(58,86,228,.14);color:var(--blue);border:1px solid rgba(58,86,228,.3);}
.cwarn{background:rgba(180,83,9,.14);color:var(--orange);border:1px solid rgba(180,83,9,.3);}
</style>

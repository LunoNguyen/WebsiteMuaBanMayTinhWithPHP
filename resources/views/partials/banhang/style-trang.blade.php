{{-- Khung trang (sidebar, nội dung, bảng, nút) dùng chung cho các trang của khu này --}}
<style>
:root{--bg:var(--bg-main);--card:var(--bg-card);--card2:var(--bg-card-hover);--bd:var(--border);--tx:var(--text-primary);--mt:var(--text-muted);--gr:var(--green);--bl:var(--blue-solid);}
*{box-sizing:border-box;}
body{background:var(--bg);font-family:'Inter',sans-serif;color:var(--tx);}
.shell{display:flex;min-height:100vh;}
.main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
/* Topbar */
.topbar{height:54px;background:var(--card);border-bottom:1px solid var(--bd);
  display:flex;align-items:center;gap:12px;padding:0 20px;position:sticky;top:0;z-index:50;flex-shrink:0;}
.topbar h2{font-size:15px;font-weight:700;color:var(--tx);margin:0;}
.tb-search{flex:1;max-width:350px;}
.tb-search form{display:flex;gap:6px;}
.tb-search input{flex:1;background:var(--card2);border:1px solid var(--bd);border-radius:8px;
  padding:7px 12px;color:var(--tx);font-size:12px;outline:none;}
.tb-search input::placeholder{color:var(--mt);}
.tb-search button{background:var(--bl);color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer;}
.tb-right{display:flex;align-items:center;gap:10px;margin-left:auto;}
.tb-av{width:32px;height:32px;border-radius:50%;background:var(--green-solid);
  display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;}
/* Content */
.ct{flex:1;overflow:auto;padding:20px;display:flex;flex-direction:column;gap:16px;}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;}
.ph h1{font-size:20px;font-weight:800;color:var(--tx);margin:0;}
.ph p{font-size:12px;color:var(--mt);margin:4px 0 0;}
/* KPI */
.kgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;}
.kcard{background:var(--card);border:1px solid var(--bd);border-radius:10px;padding:14px 16px;
  display:flex;align-items:center;gap:12px;}
.kico{width:42px;height:42px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.kval{font-size:24px;font-weight:900;line-height:1;}
.klbl{font-size:10px;color:var(--mt);margin-top:1px;}
/* Filter */
.fbar{background:var(--card);border:1px solid var(--bd);border-radius:10px;padding:10px 14px;
  display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.fbar input,.fbar select{background:var(--card2);border:1px solid var(--bd);border-radius:7px;
  padding:7px 10px;color:var(--tx);font-size:12px;outline:none;color-scheme:dark;}
.fbar input::placeholder{color:var(--mt);}
.btn{padding:7px 14px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;
  display:flex;align-items:center;gap:5px;border:none;text-decoration:none;transition:all .15s;white-space:nowrap;}
.btn-pri{background:var(--green-solid);color:#fff;}
.btn-out{background:transparent;border:1px solid var(--bd)!important;color:var(--mt);}
.btn-out:hover{border-color:var(--bl)!important;color:var(--tx);}
.btn-sm-blue{background:rgba(58,86,228,.15);color:var(--blue);border:1px solid rgba(58,86,228,.3)!important;padding:5px 10px;font-size:11px;}
.btn-sm-red{background:rgba(200,30,30,.12);color:var(--red);border:1px solid rgba(200,30,30,.3)!important;padding:5px 10px;font-size:11px;}
/* Table */
.tcard{background:var(--card);border:1px solid var(--bd);border-radius:10px;overflow:hidden;}
.thead-row{padding:11px 14px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;}
.thead-row h3{font-size:13px;font-weight:700;color:var(--tx);margin:0;}
.cnt{background:rgba(21,128,61,.15);color:var(--green);border-radius:20px;font-size:11px;padding:2px 8px;font-weight:700;}
table.dt{width:100%;border-collapse:collapse;}
table.dt th{padding:8px 12px;font-size:10px;font-weight:700;color:var(--mt);border-bottom:1px solid var(--bd);text-align:left;}
table.dt td{padding:9px 12px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--border);vertical-align:middle;}
table.dt tr:last-child td{border-bottom:none;}
table.dt tr:hover td{background:rgba(0,0,0,.04);}
.mono{font-family:monospace;font-weight:700;color:var(--gr);}
.pil{display:inline-flex;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;}
/* Alert */
.amsg{padding:11px 16px;border-radius:8px;font-size:13px;margin-bottom:2px;}
.amsg.ok{background:rgba(21,128,61,.1);border:1px solid rgba(21,128,61,.2);color:var(--green);}
.amsg.info{background:rgba(58,86,228,.1);border:1px solid rgba(58,86,228,.2);color:var(--blue);}
/* Pagination */
.pg{padding:10px 14px;border-top:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;}
.pga{display:flex;gap:3px;}
.pga a{width:27px;height:27px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--mt);background:transparent;border:1px solid transparent;text-decoration:none;transition:all .15s;}
.pga a:hover{background:rgba(0,0,0,.04);color:var(--tx);}
.pga a.active{background:rgba(21,128,61,.2);border-color:rgba(21,128,61,.4);color:var(--gr);}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}::-webkit-scrollbar-thumb{background:var(--bd);border-radius:2px;}
@media(max-width:1100px){.kgrid{grid-template-columns:repeat(2,1fr);}}
</style>

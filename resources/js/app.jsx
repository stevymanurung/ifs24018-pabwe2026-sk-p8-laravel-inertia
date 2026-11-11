import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp, router } from '@inertiajs/react';
import '../css/app.css';
import '@toast-ui/editor/dist/toastui-editor.css';
import '@toast-ui/editor/dist/theme/toastui-editor-dark.css';
import '../css/accessibility.css';
import { useEffect, useState } from 'react';
function Progress(){const [busy,setBusy]=useState(false);useEffect(()=>{const a=router.on('start',()=>setBusy(true));const b=router.on('finish',()=>setBusy(false));return()=>{a();b();};},[]);return <>{busy&&<><div className="top-progress"/><div className="status-pill">◌ Memproses tindakan...</div></>}</>;}
const pages=import.meta.glob('./Pages/**/*.jsx');
createInertiaApp({title:title=>`${title} · BlogVerse Pro`,resolve:name=>{const page=pages[`./Pages/${name}.jsx`];if(!page)throw new Error(`Halaman ${name} tidak ditemukan`);return page();},setup({el,App,props}){createRoot(el).render(<><Progress/><App {...props}/></>);},progress:{color:'#bd6b4a'}});

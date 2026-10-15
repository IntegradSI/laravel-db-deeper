(()=>{var d={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":2,"stroke-linecap":"round","stroke-linejoin":"round"};var y=([e,a,t])=>{let r=document.createElementNS("http://www.w3.org/2000/svg",e);return Object.keys(a).forEach(o=>{r.setAttribute(o,String(a[o]))}),t?.length&&t.forEach(o=>{let u=y(o);r.appendChild(u)}),r},R=(e,a={})=>{let r={...d,...a};return y(["svg",r,e])};var T=(...e)=>e.filter((a,t,r)=>!!a&&a.trim()!==""&&r.indexOf(a)===t).join(" ").trim();var q=e=>{for(let a in e)if(a.startsWith("aria-")||a==="role"||a==="title")return!0;return!1};var b=e=>{let a="",t=!1;for(let r of e){if(r==="-"||r==="_"||r<=" "){t=a.length>0;continue}a.length===0?a+=r.toLowerCase():a+=t?r.toUpperCase():r,t=!1}return a};var U=e=>{let a=b(e);return a.charAt(0).toUpperCase()+a.slice(1)};var W=e=>Array.from(e.attributes).reduce((a,t)=>(a[t.name]=t.value,a),{}),v=e=>typeof e=="string"?e:!e||!e.class?"":e.class&&typeof e.class=="string"?e.class.split(" "):e.class&&Array.isArray(e.class)?e.class:"",x=(e,{nameAttr:a,icons:t,attrs:r})=>{let o=e.getAttribute(a);if(o==null)return;let u=U(o),f=t[u];if(!f)return console.warn(`${e.outerHTML} icon name was not found in the provided icons object.`);let l=W(e),O=q(l)?{}:{"aria-hidden":"true"},L={...d,"data-lucide":o,...O,...r,...l},G=v(l),E=v(r),F=T("lucide",`lucide-${o}`,...G,...E);F&&Object.assign(L,{class:F});let V=R(f,L);return e.parentNode?.replaceChild(V,e)};var i=[["path",{d:"M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"}]];var n=[["rect",{width:"20",height:"5",x:"2",y:"3",rx:"1"}],["path",{d:"M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"}],["path",{d:"M10 12h4"}]];var p=[["circle",{cx:"12",cy:"12",r:"10"}],["line",{x1:"12",x2:"12",y1:"8",y2:"12"}],["line",{x1:"12",x2:"12.01",y1:"16",y2:"16"}]];var s=[["circle",{cx:"12",cy:"12",r:"10"}],["path",{d:"M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"}],["path",{d:"M12 17h.01"}]];var c=[["circle",{cx:"12",cy:"12",r:"10"}],["path",{d:"M12 6v6h4"}]];var C=[["ellipse",{cx:"12",cy:"5",rx:"9",ry:"3"}],["path",{d:"M3 5V19A9 3 0 0 0 21 19V5"}],["path",{d:"M3 12A9 3 0 0 0 21 12"}]];var h=[["path",{d:"M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"}],["circle",{cx:"12",cy:"12",r:"3"}]];var S=[["path",{d:"m12 14 4-4"}],["path",{d:"M3.34 19a10 10 0 1 1 17.32 0"}]];var g=[["path",{d:"M10 16h.01"}],["path",{d:"M2.212 11.577a2 2 0 0 0-.212.896V18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5.527a2 2 0 0 0-.212-.896L18.55 5.11A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"}],["path",{d:"M21.946 12.013H2.054"}],["path",{d:"M6 16h.01"}]];var k=[["circle",{cx:"12",cy:"12",r:"10"}],["path",{d:"M12 16v-4"}],["path",{d:"M12 8h.01"}]];var m=[["path",{d:"M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z"}],["path",{d:"M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 12"}],["path",{d:"M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 17"}]];var w=[["rect",{width:"7",height:"9",x:"3",y:"3",rx:"1"}],["rect",{width:"7",height:"5",x:"14",y:"3",rx:"1"}],["rect",{width:"7",height:"9",x:"14",y:"12",rx:"1"}],["rect",{width:"7",height:"5",x:"3",y:"16",rx:"1"}]];var P=[["path",{d:"M4 5h16"}],["path",{d:"M4 12h16"}],["path",{d:"M4 19h16"}]];var A=[["path",{d:"m10.852 14.772-.383.923"}],["path",{d:"M13.148 14.772a3 3 0 1 0-2.296-5.544l-.383-.923"}],["path",{d:"m13.148 9.228.383-.923"}],["path",{d:"m13.53 15.696-.382-.924a3 3 0 1 1-2.296-5.544"}],["path",{d:"m14.772 10.852.923-.383"}],["path",{d:"m14.772 13.148.923.383"}],["path",{d:"M4.5 10H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-.5"}],["path",{d:"M4.5 14H4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2h-.5"}],["path",{d:"M6 18h.01"}],["path",{d:"M6 6h.01"}],["path",{d:"m9.228 10.852-.923-.383"}],["path",{d:"m9.228 13.148-.923.383"}]];var M=[["path",{d:"M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"}],["path",{d:"m9 12 2 2 4-4"}]];var B=[["path",{d:"M15 3v18"}],["rect",{width:"18",height:"18",x:"3",y:"3",rx:"2"}],["path",{d:"M21 9H3"}],["path",{d:"M21 15H3"}]];var D=({icons:e={},nameAttr:a="data-lucide",attrs:t={},root:r=document,inTemplates:o}={})=>{if(!Object.values(e).length)throw new Error(`Please provide an icons object.
If you want to use all the icons you can import it like:
 \`import { createIcons, icons } from 'lucide';
lucide.createIcons({icons});\``);if(typeof r>"u")throw new Error("`createIcons()` only works in a browser environment.");if(Array.from(r.querySelectorAll(`[${a}]`)).forEach(f=>x(f,{nameAttr:a,icons:e,attrs:t})),o&&Array.from(r.querySelectorAll("template")).forEach(l=>D({icons:e,nameAttr:a,attrs:t,root:l.content,inTemplates:o})),a==="data-lucide"){let f=r.querySelectorAll("[icon-name]");f.length>0&&(console.warn("[Lucide] Some icons were found with the now deprecated icon-name attribute. These will still be replaced for backwards compatibility, but will no longer be supported in v1.0 and you should switch to data-lucide"),Array.from(f).forEach(l=>x(l,{nameAttr:"icon-name",icons:e,attrs:t})))}};D({icons:{Activity:i,Archive:n,CircleAlert:p,CircleHelp:s,Clock3:c,Database:C,Eye:h,Gauge:S,HardDrive:g,Info:k,Layers3:m,LayoutDashboard:w,Menu:P,ServerCog:A,ShieldCheck:M,TableProperties:B}});var H=document.querySelector("#db-deeper-drawer");document.querySelectorAll(".drawer-side a").forEach(e=>{e.addEventListener("click",()=>{H instanceof HTMLInputElement&&(H.checked=!1)})});})();
/*! Bundled license information:

lucide/dist/esm/defaultAttributes.mjs:
lucide/dist/esm/createElement.mjs:
lucide/dist/esm/shared/src/utils/mergeClasses.mjs:
lucide/dist/esm/shared/src/utils/hasA11yProp.mjs:
lucide/dist/esm/shared/src/utils/toCamelCase.mjs:
lucide/dist/esm/shared/src/utils/toPascalCase.mjs:
lucide/dist/esm/replaceElement.mjs:
lucide/dist/esm/icons/activity.mjs:
lucide/dist/esm/icons/archive.mjs:
lucide/dist/esm/icons/circle-alert.mjs:
lucide/dist/esm/icons/circle-question-mark.mjs:
lucide/dist/esm/icons/clock-3.mjs:
lucide/dist/esm/icons/database.mjs:
lucide/dist/esm/icons/eye.mjs:
lucide/dist/esm/icons/gauge.mjs:
lucide/dist/esm/icons/hard-drive.mjs:
lucide/dist/esm/icons/info.mjs:
lucide/dist/esm/icons/layers.mjs:
lucide/dist/esm/icons/layout-dashboard.mjs:
lucide/dist/esm/icons/menu.mjs:
lucide/dist/esm/icons/server-cog.mjs:
lucide/dist/esm/icons/shield-check.mjs:
lucide/dist/esm/icons/table-properties.mjs:
lucide/dist/esm/lucide.mjs:
  (**
   * @license lucide v1.57.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   *)
*/

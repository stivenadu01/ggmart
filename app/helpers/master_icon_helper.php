<?php

/**
 * Master SVG icon catalog.
 * Satu sumber ikon untuk seluruh UI GG-Mart, mengikuti pola Master Icon BetaKos.
 */
function masterIconCatalog(): array
{
  return [
    'leaf' => '<path d="M20 4c-7-1-15 1-15 8a6 6 0 0 0 6 6c7 0 9-8 9-14Z"/><path d="M4 21 15 10"/>',
    'map-pin' => '<path d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
    'truck' => '<path d="M3 6h11v11H3V6ZM14 10h4l3 4v3h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
    'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    'cart' => '<circle cx="9" cy="20" r="1.25"/><circle cx="17" cy="20" r="1.25"/><path d="M3 4h2l2.5 11h10l2-7H6.2"/>',
    'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
    'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
    'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
    'cashier' => '<rect x="4" y="5" width="16" height="14" rx="2"/><path d="M7 9h10M7 13h4M15 13h2M7 16h10"/>',
    'orders' => '<path d="M6 7h12l1 13H5L6 7Z"/><path d="M9 7a3 3 0 0 1 6 0"/>',
    'category' => '<path d="M4 7h16M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="M8 11h8M8 15h5"/>',
    'product' => '<path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/><path d="M12 11v10"/>',
    'stock' => '<path d="M4 7.5 12 4l8 3.5v9L12 20l-8-3.5v-9Z"/><path d="M4.5 7.5 12 11l7.5-3.5M12 11v9"/>',
    'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
    'history' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/>',
    'report' => '<path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
    'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21H9.6v-.1A1.7 1.7 0 0 0 8.2 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 3.8 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H2V9.6h.1A1.7 1.7 0 0 0 3.6 8.2a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 8 3.8a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V2h4v.1A1.7 1.7 0 0 0 14.8 3.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.2 8c.2.4.6.7 1 .9.3.1.7.2 1.1.2h.1v4h-.1a1.7 1.7 0 0 0-1.9 1.9Z"/>',
    'home' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10M9 20v-6h6v6"/>',
    'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/>',
    'shield-check' => '<path d="M12 3 5 6v5c0 4.5 3 7.6 7 9 4-1.4 7-4.5 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
    'money' => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>',
    'qris' => '<path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM16 13h4v4h-4zM13 18h3M18 20h2"/>',
    'save' => '<path d="M6 3h10l3 3v15H6z"/><path d="M9 3v6h7V3M9 17h6"/>',
    'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/>',
    'trend' => '<path d="M4 16l5-5 4 4 7-8"/><path d="M15 7h5v5"/>',
    'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/>',
    'download' => '<path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M5 20h14"/>',
    'warning' => '<path d="M12 9v4m0 4h.01"/><path d="m10.3 4.6-7.5 13A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-3l-7.5-13a2 2 0 0 0-3.4 0Z"/>',
    'refresh' => '<path d="M20 11a8.1 8.1 0 0 0-14.9-4M4 5v4h4M4 13a8.1 8.1 0 0 0 14.9 4M20 19v-4h-4"/>',
    'receipt' => '<path d="M6 3h12v18l-2-1.5L14 21l-2-1.5L10 21l-2-1.5L6 21V3Z"/><path d="M9 7h6M9 11h6M9 15h4"/>',
    'warehouse' => '<path d="M3 17h18M5 17V7h14v10M8 11h2m2 0h2m-6 3h2m2 0h2"/>',
    'profit' => '<path d="M12 3v18M17 7.5c0-1.7-2.2-3-5-3s-5 1.3-5 3 2.2 3 5 3 5 1.3 5 3-2.2 3-5 3-5-1.3-5-3"/>',
    'layers' => '<path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/>',
    'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 20"/>',
    'plus' => '<path d="M12 5v14M5 12h14"/>',
    'minus' => '<path d="M5 12h14"/>',
    'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
    'arrow-right' => '<path d="m9 18 6-6-6-6"/>',
    'spinner' => '<circle cx="12" cy="12" r="9" opacity=".25"/><path d="M21 12a9 9 0 0 0-9-9"/>',
  ];
}

function masterIconSvg(string $name, string $class = 'h-5 w-5', array $attrs = []): string
{
  $catalog = masterIconCatalog();
  $body = $catalog[$name] ?? $catalog['warning'];
  $attributes = [
    'class' => trim($class),
    'viewBox' => '0 0 24 24',
    'fill' => 'none',
    'stroke' => 'currentColor',
    'stroke-width' => '1.8',
    'stroke-linecap' => 'round',
    'stroke-linejoin' => 'round',
    'aria-hidden' => 'true',
  ];
  foreach ($attrs as $key => $value) $attributes[$key] = $value;
  $out=[];
  foreach ($attributes as $key=>$value) $out[] = htmlspecialchars((string)$key,ENT_QUOTES,'UTF-8').'="'.htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8').'"';
  return '<svg '.implode(' ',$out).'>'.$body.'</svg>';
}

<?php
// Isotipo del logo: globo terráqueo que gira siempre y, cada ~12 s, un avión
// le da la vuelta en órbita (pasa por delante y luego por detrás del globo).
// Giro en css/input.css (.ms-globe); vuelo del avión con <animateMotion>.
// $id distingue cada copia en la página (header/footer): el SVG usa ids.
function render_logo_mark(string $id = 'h'): void {
    $c = 'gclip-' . $id;
    $g = 'gocean-' . $id;
    $o = 'gorbit-' . $id;
    $plane = '<g transform="scale(1.3)"><path d="M2.6 0 .6-.5-.6-2.4h-.7l.5 2.1-1.6.2-.6-.7h-.5l.4 1.3-.4 1.3h.5l.6-.7 1.6.2-.5 2.1h.7L.6.5z" fill="#ffffff" stroke="#2d4874" stroke-width=".25"/></g>';
    $fly = '<animateMotion dur="12s" repeatCount="indefinite" rotate="auto" keyPoints="0;0;1" keyTimes="0;0.72;1" calcMode="linear"><mpath href="#' . $o . '"/></animateMotion><animate attributeName="opacity" dur="12s" repeatCount="indefinite" values="0;0;1;1;0" keyTimes="0;0.71;0.74;0.98;1"/>';
    ?>
    <span class="ms-globe" aria-hidden="true">
      <svg viewBox="3.5 3.5 25 25" width="32" height="32">
        <defs>
          <radialGradient id="<?= $g ?>" cx="38%" cy="32%" r="75%">
            <stop offset="0%" stop-color="#7eafc6"/>
            <stop offset="55%" stop-color="#406e81"/>
            <stop offset="100%" stop-color="#2d4874"/>
          </radialGradient>
          <clipPath id="<?= $c ?>"><circle cx="16" cy="16" r="12"/></clipPath>
          <!-- Órbita del avión (inclinada) y sus dos mitades: atrás / adelante del globo -->
          <path id="<?= $o ?>" d="M1 16a15 6 0 1 0 30 0a15 6 0 1 0 -30 0"/>
          <clipPath id="<?= $c ?>-back" clipPathUnits="userSpaceOnUse"><rect x="-4" y="-4" width="40" height="20"/></clipPath>
          <clipPath id="<?= $c ?>-front" clipPathUnits="userSpaceOnUse"><rect x="-4" y="16" width="40" height="20"/></clipPath>
        </defs>
        <!-- Avión pasando POR DETRÁS (se dibuja antes que el globo) -->
        <g class="ms-globe-plane" transform="rotate(-18 16 16)"><g clip-path="url(#<?= $c ?>-back)"><g opacity="0"><?= $plane ?><?= $fly ?></g></g></g>
        <circle cx="16" cy="16" r="12" fill="url(#<?= $g ?>)"/>
        <g clip-path="url(#<?= $c ?>)">
          <!-- Continentes: dos copias seguidas que se desplazan = vuelta del globo -->
          <g class="ms-globe-land" fill="#a9c29b">
            <g>
              <path d="M6 9c2-2 5-2 6 0s-1 3 0 5-2 4-4 3-3-3-3-5 0-2 1-3z"/>
              <path d="M13 18c2-1 4 0 4 2s-1 4-3 4-2-2-2-3 0-2 1-3z"/>
              <path d="M19 8c2-1 5 0 6 2s0 3-2 3-2 2-4 1-1-2-1-3 0-2 1-3z"/>
              <path d="M22 16c1-1 3 0 3 1s-1 3-2 3-2-1-2-2 0-1 1-2z"/>
            </g>
            <g transform="translate(24 0)">
              <path d="M6 9c2-2 5-2 6 0s-1 3 0 5-2 4-4 3-3-3-3-5 0-2 1-3z"/>
              <path d="M13 18c2-1 4 0 4 2s-1 4-3 4-2-2-2-3 0-2 1-3z"/>
              <path d="M19 8c2-1 5 0 6 2s0 3-2 3-2 2-4 1-1-2-1-3 0-2 1-3z"/>
              <path d="M22 16c1-1 3 0 3 1s-1 3-2 3-2-1-2-2 0-1 1-2z"/>
            </g>
          </g>
          <!-- Paralelos y meridiano -->
          <g fill="none" stroke="rgb(255 255 255 / .28)" stroke-width=".7">
            <ellipse cx="16" cy="16" rx="12" ry="4.5"/>
            <path d="M4 16h24"/>
            <ellipse cx="16" cy="16" rx="5" ry="12"/>
          </g>
          <!-- Brillo -->
          <ellipse cx="12" cy="10" rx="6" ry="3.5" fill="rgb(255 255 255 / .22)" transform="rotate(-25 12 10)"/>
        </g>
        <circle cx="16" cy="16" r="12" fill="none" stroke="rgb(255 255 255 / .35)" stroke-width=".8"/>
        <!-- Avión pasando POR DELANTE (se dibuja después del globo) -->
        <g class="ms-globe-plane" transform="rotate(-18 16 16)"><g clip-path="url(#<?= $c ?>-front)"><g opacity="0"><?= $plane ?><?= $fly ?></g></g></g>
      </svg>
    </span>
    <?php
}
